/**
 * Offline.js - Handles offline data management for Note Management App
 * Provides IndexedDB storage and synchronization with server
 */

const OfflineDB = (function() {
    // Database variables
    let db;
    const DB_NAME = 'NoteAppOfflineDB';
    const DB_VERSION = 1;
  
    // Status variables
    let isOnline = window.navigator.onLine;
    let syncInProgress = false;
    const pendingChanges = new Set();
  
    // Initialize database
    function initDatabase() {
      return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);
        
        // Handle database upgrade (first time or version change)
        request.onupgradeneeded = function(event) {
          const db = event.target.result;
          
          // Create object stores for different entities
          if (!db.objectStoreNames.contains('notes')) {
            const notesStore = db.createObjectStore('notes', { keyPath: 'id' });
            notesStore.createIndex('user_id', 'user_id', { unique: false });
            notesStore.createIndex('updated_at', 'updated_at', { unique: false });
            notesStore.createIndex('is_pinned', 'is_pinned', { unique: false });
          }
          
          if (!db.objectStoreNames.contains('labels')) {
            const labelsStore = db.createObjectStore('labels', { keyPath: 'id' });
            labelsStore.createIndex('user_id', 'user_id', { unique: false });
          }
          
          if (!db.objectStoreNames.contains('note_labels')) {
            const noteLabelStore = db.createObjectStore('note_labels', { keyPath: 'id', autoIncrement: true });
            noteLabelStore.createIndex('note_id', 'note_id', { unique: false });
            noteLabelStore.createIndex('label_id', 'label_id', { unique: false });
          }
          
          if (!db.objectStoreNames.contains('pendingActions')) {
            const pendingStore = db.createObjectStore('pendingActions', { keyPath: 'id', autoIncrement: true });
            pendingStore.createIndex('action', 'action', { unique: false });
            pendingStore.createIndex('timestamp', 'timestamp', { unique: false });
          }
        };
        
        request.onsuccess = function(event) {
          db = event.target.result;
          console.log('[OfflineDB] Database initialized successfully');
          resolve(db);
        };
        
        request.onerror = function(event) {
          console.error('[OfflineDB] Database initialization error:', event.target.error);
          reject(event.target.error);
        };
      });
    }
  
    // Network status handling
    function setupNetworkListeners() {
      // Update online status
      window.addEventListener('online', () => {
        console.log('[OfflineDB] App is online');
        isOnline = true;
        updateOfflineIndicator();
        
        // Sync pending changes when coming back online
        syncWithServer();
      });
      
      window.addEventListener('offline', () => {
        console.log('[OfflineDB] App is offline');
        isOnline = false;
        updateOfflineIndicator();
      });
      
      // Listen for sync messages from service worker
      if (navigator.serviceWorker) {
        navigator.serviceWorker.addEventListener('message', (event) => {
          if (event.data && event.data.type === 'sync-notes') {
            syncWithServer();
          }
        });
      }
    }
  
    // Update UI to show offline/online status
    function updateOfflineIndicator() {
      const offlineIndicator = document.getElementById('offline-indicator');
      
      if (!offlineIndicator) {
        // Create indicator if it doesn't exist
        const indicator = document.createElement('div');
        indicator.id = 'offline-indicator';
        indicator.className = isOnline ? 'online' : 'offline';
        indicator.innerHTML = isOnline ? 
          '<i class="fas fa-wifi"></i> Online' : 
          '<i class="fas fa-exclamation-triangle"></i> Offline';
        
        document.body.appendChild(indicator);
      } else {
        // Update existing indicator
        offlineIndicator.className = isOnline ? 'online' : 'offline';
        offlineIndicator.innerHTML = isOnline ? 
          '<i class="fas fa-wifi"></i> Online' : 
          '<i class="fas fa-exclamation-triangle"></i> Offline';
        
        // Show indicator temporarily when status changes
        offlineIndicator.style.display = 'block';
        offlineIndicator.style.opacity = '1';
        
        // Hide after 3 seconds
        setTimeout(() => {
          if (isOnline) {
            offlineIndicator.style.opacity = '0';
            setTimeout(() => {
              offlineIndicator.style.display = 'none';
            }, 300);
          }
        }, 3000);
      }
    }
  
    // Add CSS styles for offline indicator
    function addOfflineStyles() {
      if (!document.getElementById('offline-styles')) {
        const style = document.createElement('style');
        style.id = 'offline-styles';
        style.textContent = `
          #offline-indicator {
            position: fixed;
            bottom: 20px;
            left: 20px;
            padding: 10px 15px;
            border-radius: 4px;
            font-weight: bold;
            z-index: 9999;
            transition: opacity 0.3s ease;
          }
          #offline-indicator.offline {
            background-color: #f44336;
            color: white;
          }
          #offline-indicator.online {
            background-color: #4CAF50;
            color: white;
          }
          .offline-mode .note-card {
            position: relative;
          }
          .offline-mode .note-card::after {
            content: "Offline";
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(244, 67, 54, 0.8);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
          }
          .syncing .note-card::after {
            content: "Syncing...";
            background: rgba(255, 152, 0, 0.8);
          }
        `;
        document.head.appendChild(style);
      }
    }
  
    // CRUD operations for notes
    
    // Save note to IndexedDB
    function saveNote(note) {
      return new Promise((resolve, reject) => {
        if (!db) {
          reject(new Error('Database not initialized'));
          return;
        }
        
        // Add timestamp to track changes
        note.offlineUpdatedAt = new Date().toISOString();
        note.pendingSync = true;
        
        const transaction = db.transaction('notes', 'readwrite');
        const store = transaction.objectStore('notes');
        
        const request = store.put(note);
        
        request.onsuccess = function() {
          console.log('[OfflineDB] Note saved successfully:', note.id);
          
          // Add to pending changes if we're offline
          if (!isOnline) {
            addPendingAction({
              action: note.id ? 'update_note' : 'create_note',
              data: { note_id: note.id, title: note.title },
              entity_id: note.id,
              timestamp: new Date().getTime()
            });
          }
          
          resolve(note);
        };
        
        request.onerror = function(event) {
          console.error('[OfflineDB] Error saving note:', event.target.error);
          reject(event.target.error);
        };
      });
    }
    
    // Get note by ID from IndexedDB
    function getNote(noteId) {
      return new Promise((resolve, reject) => {
        if (!db) {
          reject(new Error('Database not initialized'));
          return;
        }
        
        const transaction = db.transaction('notes', 'readonly');
        const store = transaction.objectStore('notes');
        
        const request = store.get(noteId);
        
        request.onsuccess = function(event) {
          const note = event.target.result;
          resolve(note);
        };
        
        request.onerror = function(event) {
          console.error('[OfflineDB] Error getting note:', event.target.error);
          reject(event.target.error);
        };
      });
    }
    
    // Get all notes for a user
    function getNotes(userId) {
      return new Promise((resolve, reject) => {
        if (!db) {
          reject(new Error('Database not initialized'));
          return;
        }
        
        const transaction = db.transaction('notes', 'readonly');
        const store = transaction.objectStore('notes');
        const index = store.index('user_id');
        
        const request = index.getAll(userId);
        
        request.onsuccess = function(event) {
          const notes = event.target.result;
          
          // Sort by pinned status and then by updated date
          notes.sort((a, b) => {
            if (a.is_pinned && !b.is_pinned) return -1;
            if (!a.is_pinned && b.is_pinned) return 1;
            
            // If both are pinned or both are not pinned, sort by pin time or updated time
            const aTime = a.is_pinned ? a.pin_time : a.updated_at;
            const bTime = b.is_pinned ? b.pin_time : b.updated_at;
            
            return new Date(bTime) - new Date(aTime);
          });
          
          resolve(notes);
        };
        
        request.onerror = function(event) {
          console.error('[OfflineDB] Error getting notes:', event.target.error);
          reject(event.target.error);
        };
      });
    }
    
    // Delete note from IndexedDB
    function deleteNote(noteId) {
      return new Promise((resolve, reject) => {
        if (!db) {
          reject(new Error('Database not initialized'));
          return;
        }
        
        const transaction = db.transaction('notes', 'readwrite');
        const store = transaction.objectStore('notes');
        
        const request = store.delete(noteId);
        
        request.onsuccess = function() {
          console.log('[OfflineDB] Note deleted successfully:', noteId);
          
          // Add to pending changes if we're offline
          if (!isOnline) {
            addPendingAction({
              action: 'delete_note',
              entity_id: noteId,
              timestamp: new Date().getTime()
            });
          }
          
          resolve(noteId);
        };
        
        request.onerror = function(event) {
          console.error('[OfflineDB] Error deleting note:', event.target.error);
          reject(event.target.error);
        };
      });
    }
    
    // CRUD operations for labels
    
    // Save label to IndexedDB
    function saveLabel(label) {
      return new Promise((resolve, reject) => {
        if (!db) {
          reject(new Error('Database not initialized'));
          return;
        }
        
        const transaction = db.transaction('labels', 'readwrite');
        const store = transaction.objectStore('labels');
        
        const request = store.put(label);
        
        request.onsuccess = function() {
          console.log('[OfflineDB] Label saved successfully:', label.id);
          
          // Add to pending changes if we're offline
          if (!isOnline) {
            addPendingAction({
              action: label.id ? 'update_label' : 'create_label',
              data: { label_id: label.id, name: label.name },
              entity_id: label.id,
              timestamp: new Date().getTime()
            });
          }
          
          resolve(label);
        };
        
        request.onerror = function(event) {
          console.error('[OfflineDB] Error saving label:', event.target.error);
          reject(event.target.error);
        };
      });
    }
    
    // Get all labels for a user
    function getLabels(userId) {
      return new Promise((resolve, reject) => {
        if (!db) {
          reject(new Error('Database not initialized'));
          return;
        }
        
        const transaction = db.transaction('labels', 'readonly');
        const store = transaction.objectStore('labels');
        const index = store.index('user_id');
        
        const request = index.getAll(userId);
        
        request.onsuccess = function(event) {
          const labels = event.target.result;
          // Sort by name
          labels.sort((a, b) => a.name.localeCompare(b.name));
          resolve(labels);
        };
        
        request.onerror = function(event) {
          console.error('[OfflineDB] Error getting labels:', event.target.error);
          reject(event.target.error);
        };
      });
    }
    
    // Delete label from IndexedDB
    function deleteLabel(labelId) {
      return new Promise((resolve, reject) => {
        if (!db) {
          reject(new Error('Database not initialized'));
          return;
        }
        
        const transaction = db.transaction('labels', 'readwrite');
        const store = transaction.objectStore('labels');
        
        const request = store.delete(labelId);
        
        request.onsuccess = function() {
          console.log('[OfflineDB] Label deleted successfully:', labelId);
          
          // Add to pending changes if we're offline
          if (!isOnline) {
            addPendingAction({
              action: 'delete_label',
              entity_id: labelId,
              timestamp: new Date().getTime()
            });
          }
          
          resolve(labelId);
        };
        
        request.onerror = function(event) {
          console.error('[OfflineDB] Error deleting label:', event.target.error);
          reject(event.target.error);
        };
      });
    }
    
    // Note-Label relationship functions
    
    // Attach a label to a note
    function attachLabelToNote(noteId, labelId) {
      return new Promise((resolve, reject) => {
        if (!db) {
          reject(new Error('Database not initialized'));
          return;
        }
        
        const transaction = db.transaction('note_labels', 'readwrite');
        const store = transaction.objectStore('note_labels');
        
        // Check if the relationship already exists
        const index = store.index('note_id');
        const cursorRequest = index.openCursor(IDBKeyRange.only(noteId));
        
        let exists = false;
        
        cursorRequest.onsuccess = function(event) {
          const cursor = event.target.result;
          
          if (cursor) {
            if (cursor.value.label_id === labelId) {
              exists = true;
            }
            cursor.continue();
          } else {
            // Add relationship if it doesn't exist
            if (!exists) {
              const request = store.add({
                note_id: noteId,
                label_id: labelId
              });
              
              request.onsuccess = function() {
                console.log('[OfflineDB] Label attached to note:', noteId, labelId);
                
                // Add to pending changes if we're offline
                if (!isOnline) {
                  addPendingAction({
                    action: 'attach_label',
                    data: { note_id: noteId, label_id: labelId },
                    timestamp: new Date().getTime()
                  });
                }
                
                resolve();
              };
              
              request.onerror = function(event) {
                console.error('[OfflineDB] Error attaching label to note:', event.target.error);
                reject(event.target.error);
              };
            } else {
              resolve();
            }
          }
        };
      });
    }
    
    // Get all labels for a note
    function getNoteLabels(noteId) {
      return new Promise((resolve, reject) => {
        if (!db) {
          reject(new Error('Database not initialized'));
          return;
        }
        
        const transaction = db.transaction(['note_labels', 'labels'], 'readonly');
        const noteLabelsStore = transaction.objectStore('note_labels');
        const labelsStore = transaction.objectStore('labels');
        
        const index = noteLabelsStore.index('note_id');
        const request = index.getAll(noteId);
        
        request.onsuccess = function(event) {
          const noteLabels = event.target.result;
          const labelIds = noteLabels.map(nl => nl.label_id);
          
          if (labelIds.length === 0) {
            resolve([]);
            return;
          }
          
          // Get label details
          const labels = [];
          let completed = 0;
          
          labelIds.forEach(id => {
            const labelRequest = labelsStore.get(id);
            
            labelRequest.onsuccess = function(event) {
              const label = event.target.result;
              if (label) {
                labels.push(label);
              }
              
              completed++;
              if (completed === labelIds.length) {
                resolve(labels);
              }
            };
            
            labelRequest.onerror = function(event) {
              console.error('[OfflineDB] Error getting label:', event.target.error);
              completed++;
              if (completed === labelIds.length) {
                resolve(labels);
              }
            };
          });
        };
        
        request.onerror = function(event) {
          console.error('[OfflineDB] Error getting note labels:', event.target.error);
          reject(event.target.error);
        };
      });
    }
    
    // Detach all labels from a note
    function detachAllLabelsFromNote(noteId) {
      return new Promise((resolve, reject) => {
        if (!db) {
          reject(new Error('Database not initialized'));
          return;
        }
        
        const transaction = db.transaction('note_labels', 'readwrite');
        const store = transaction.objectStore('note_labels');
        
        const index = store.index('note_id');
        const cursorRequest = index.openCursor(IDBKeyRange.only(noteId));
        
        cursorRequest.onsuccess = function(event) {
          const cursor = event.target.result;
          
          if (cursor) {
            cursor.delete();
            cursor.continue();
          } else {
            console.log('[OfflineDB] All labels detached from note:', noteId);
            
            // Add to pending changes if we're offline
            if (!isOnline) {
              addPendingAction({
                action: 'detach_all_labels',
                data: { note_id: noteId },
                timestamp: new Date().getTime()
              });
            }
            
            resolve();
          }
        };
        
        cursorRequest.onerror = function(event) {
          console.error('[OfflineDB] Error detaching labels from note:', event.target.error);
          reject(event.target.error);
        };
      });
    }
    
    // Pending actions management
    
    // Add a pending action to sync later
    function addPendingAction(action) {
      return new Promise((resolve, reject) => {
        if (!db) {
          reject(new Error('Database not initialized'));
          return;
        }
        
        const transaction = db.transaction('pendingActions', 'readwrite');
        const store = transaction.objectStore('pendingActions');
        
        const request = store.add(action);
        
        request.onsuccess = function() {
          console.log('[OfflineDB] Pending action added:', action);
          
          // Register for background sync if available
          if ('serviceWorker' in navigator && 'SyncManager' in window) {
            navigator.serviceWorker.ready
              .then(registration => {
                registration.sync.register('sync-notes')
                  .then(() => console.log('Background sync registered'))
                  .catch(err => console.error('Background sync registration failed:', err));
              });
          }
          
          resolve();
        };
        
        request.onerror = function(event) {
          console.error('[OfflineDB] Error adding pending action:', event.target.error);
          reject(event.target.error);
        };
      });
    }
    
    // Get all pending actions
    function getPendingActions() {
      return new Promise((resolve, reject) => {
        if (!db) {
          reject(new Error('Database not initialized'));
          return;
        }
        
        const transaction = db.transaction('pendingActions', 'readonly');
        const store = transaction.objectStore('pendingActions');
        
        const request = store.getAll();
        
        request.onsuccess = function(event) {
          const actions = event.target.result;
          // Sort by timestamp, oldest first
          actions.sort((a, b) => a.timestamp - b.timestamp);
          resolve(actions);
        };
        
        request.onerror = function(event) {
          console.error('[OfflineDB] Error getting pending actions:', event.target.error);
          reject(event.target.error);
        };
      });
    }
    
    // Remove a pending action after it's been synced
    function removePendingAction(actionId) {
      return new Promise((resolve, reject) => {
        if (!db) {
          reject(new Error('Database not initialized'));
          return;
        }
        
        const transaction = db.transaction('pendingActions', 'readwrite');
        const store = transaction.objectStore('pendingActions');
        
        const request = store.delete(actionId);
        
        request.onsuccess = function() {
          console.log('[OfflineDB] Pending action removed:', actionId);
          resolve();
        };
        
        request.onerror = function(event) {
          console.error('[OfflineDB] Error removing pending action:', event.target.error);
          reject(event.target.error);
        };
      });
    }
    
    // Clear the pendingSync flag from notes after sync
    function clearPendingSyncFlag(noteId) {
      return new Promise((resolve, reject) => {
        if (!db) {
          reject(new Error('Database not initialized'));
          return;
        }
        
        getNote(noteId)
          .then(note => {
            if (note) {
              note.pendingSync = false;
              
              const transaction = db.transaction('notes', 'readwrite');
              const store = transaction.objectStore('notes');
              
              const request = store.put(note);
              
              request.onsuccess = function() {
                console.log('[OfflineDB] Pending sync flag cleared for note:', noteId);
                resolve();
              };
              
              request.onerror = function(event) {
                console.error('[OfflineDB] Error clearing pending sync flag:', event.target.error);
                reject(event.target.error);
              };
            } else {
              resolve();
            }
          })
          .catch(reject);
      });
    }
    
    // Data synchronization with server
    
    // Fetch and store notes from server
    function fetchAndStoreNotes() {
      if (!isOnline) {
        console.log('[OfflineDB] Cannot fetch notes while offline');
        return Promise.resolve();
      }
      
      return fetch(BASE_URL + '/api/notes/list')
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          if (data.success && data.notes) {
            const notes = data.notes;
            
            return Promise.all(notes.map(note => {
              // Don't overwrite notes with pending changes
              return getNote(note.id)
                .then(existingNote => {
                  if (!existingNote || !existingNote.pendingSync) {
                    return saveNote(note);
                  }
                  return existingNote;
                })
                .catch(err => {
                  console.error('[OfflineDB] Error checking existing note:', err);
                  return saveNote(note);
                });
            }));
          }
          return [];
        })
        .catch(error => {
          console.error('[OfflineDB] Error fetching notes:', error);
          return [];
        });
    }
    
    // Fetch and store labels from server
    function fetchAndStoreLabels() {
      if (!isOnline) {
        console.log('[OfflineDB] Cannot fetch labels while offline');
        return Promise.resolve();
      }
      
      return fetch(BASE_URL + '/api/labels/list')
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          if (data.success && data.labels) {
            const labels = data.labels;
            
            return Promise.all(labels.map(label => {
              return saveLabel(label);
            }));
          }
          return [];
        })
        .catch(error => {
          console.error('[OfflineDB] Error fetching labels:', error);
          return [];
        });
    }
    
    // Process pending actions
    function processPendingActions() {
      if (!isOnline) {
        console.log('[OfflineDB] Cannot process pending actions while offline');
        return Promise.resolve();
      }
      
      return getPendingActions()
        .then(actions => {
          if (actions.length === 0) {
            console.log('[OfflineDB] No pending actions to process');
            return;
          }
          
          console.log('[OfflineDB] Processing pending actions:', actions.length);
          
          // Process actions one by one to maintain order
          return actions.reduce((chain, action) => {
            return chain.then(() => {
              return processSingleAction(action)
                .then(() => removePendingAction(action.id))
                .catch(error => {
                  console.error('[OfflineDB] Error processing action:', action, error);
                  // Continue with next action even if this one fails
                });
            });
          }, Promise.resolve());
        });
    }
    
    // Process a single pending action
    function processSingleAction(action) {
      let url, method, data;
      
      // Set appropriate URL, method and data based on action type
      switch (action.action) {
        case 'create_note':
          return getNote(action.entity_id)
            .then(note => {
              if (!note) return Promise.resolve(); // Note no longer exists locally
              
              const formData = new FormData();
              formData.append('title', note.title);
              formData.append('content', note.content);
              
              if (note.labels && note.labels.length) {
                note.labels.forEach(labelId => {
                  formData.append('labels[]', labelId);
                });
              }
              
              return fetch(BASE_URL + '/api/notes/create', {
                method: 'POST',
                body: formData,
                headers: {
                  'X-Requested-With': 'XMLHttpRequest'
                }
              })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  return clearPendingSyncFlag(action.entity_id);
                }
                throw new Error(data.error || 'Failed to create note');
              });
            });
          
        case 'update_note':
          return getNote(action.entity_id)
            .then(note => {
              if (!note) return Promise.resolve(); // Note no longer exists locally
              
              const formData = new FormData();
              formData.append('id', note.id);
              formData.append('title', note.title);
              formData.append('content', note.content);
              
              if (note.labels && note.labels.length) {
                note.labels.forEach(labelId => {
                  formData.append('labels[]', labelId);
                });
              }
              
              return fetch(BASE_URL + '/api/notes/update/' + note.id, {
                method: 'POST',
                body: formData,
                headers: {
                  'X-Requested-With': 'XMLHttpRequest'
                }
              })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  return clearPendingSyncFlag(action.entity_id);
                }
                throw new Error(data.error || 'Failed to update note');
              });
            });
          
        case 'delete_note':
          const formData = new FormData();
          formData.append('id', action.entity_id);
          
          return fetch(BASE_URL + '/api/notes/delete/' + action.entity_id, {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (!data.success) {
              throw new Error(data.error || 'Failed to delete note');
            }
          });
        
        // Handle label actions
        case 'create_label':
        case 'update_label':
        case 'delete_label':
          // Similar implementation to note actions...
          console.log('[OfflineDB] Label action syncing not fully implemented:', action);
          return Promise.resolve();
        
        // Handle label attachment actions
        case 'attach_label':
        case 'detach_all_labels':
          console.log('[OfflineDB] Label attachment syncing not fully implemented:', action);
          return Promise.resolve();
        
        default:
          console.warn('[OfflineDB] Unknown action type:', action.action);
          return Promise.resolve();
      }
    }
    
    // Main synchronization function
    function syncWithServer() {
      if (syncInProgress || !isOnline) {
        return Promise.resolve();
      }
      
      syncInProgress = true;
      document.body.classList.add('syncing');
      
      const syncIndicator = document.getElementById('offline-indicator');
      if (syncIndicator) {
        syncIndicator.className = 'syncing';
        syncIndicator.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Syncing...';
        syncIndicator.style.display = 'block';
        syncIndicator.style.opacity = '1';
      }
      
      console.log('[OfflineDB] Starting synchronization with server');
      
      // First process any pending actions (changes made while offline)
      return processPendingActions()
        .then(() => {
          // Then fetch fresh data from server
          return Promise.all([
            fetchAndStoreNotes(),
            fetchAndStoreLabels()
          ]);
        })
        .then(() => {
          console.log('[OfflineDB] Synchronization completed');
          
          syncInProgress = false;
          document.body.classList.remove('syncing');
          
          // Update UI to show we're back online and synced
          if (syncIndicator) {
            syncIndicator.className = 'online';
            syncIndicator.innerHTML = '<i class="fas fa-check-circle"></i> Synced';
            
            // Hide after 3 seconds
            setTimeout(() => {
              syncIndicator.style.opacity = '0';
              setTimeout(() => {
                syncIndicator.style.display = 'none';
              }, 300);
            }, 3000);
          }
          
          // Refresh the page if we're on notes list to show updated data
          if (window.location.pathname.includes('/notes') && 
              !window.location.pathname.includes('/edit/') &&
              !window.location.pathname.includes('/create')) {
            window.location.reload();
          }
        })
        .catch(error => {
          console.error('[OfflineDB] Synchronization error:', error);
          
          syncInProgress = false;
          document.body.classList.remove('syncing');
          
          // Update UI to show sync failed
          if (syncIndicator) {
            syncIndicator.className = 'error';
            syncIndicator.innerHTML = '<i class="fas fa-exclamation-circle"></i> Sync Failed';
            
            // Hide after 5 seconds
            setTimeout(() => {
              syncIndicator.style.opacity = '0';
              setTimeout(() => {
                syncIndicator.style.display = 'none';
              }, 300);
            }, 5000);
          }
        });
    }
    
    // Replace fetch API with offline-aware version
    function setupFetchInterceptor() {
      const originalFetch = window.fetch;
      
      window.fetch = function(url, options = {}) {
        // Parse URL to check if it's an API request
        const urlObj = new URL(url, window.location.origin);
        const isApiRequest = urlObj.pathname.includes('/api/');
        
        // For non-API requests or when online, use original fetch
        if (!isApiRequest || isOnline) {
          return originalFetch(url, options)
            .catch(error => {
              // If network error and it's an API request, try to serve from IndexedDB
              if (isApiRequest) {
                console.log('[OfflineDB] Network error, trying IndexedDB:', url);
                return serveFromIndexedDB(urlObj.pathname, options);
              }
              throw error;
            });
        }
        
        // For API requests when offline, serve from IndexedDB
        console.log('[OfflineDB] Offline API request, serving from IndexedDB:', url);
        return serveFromIndexedDB(urlObj.pathname, options);
      };
    }
    
    // Serve API requests from IndexedDB when offline
    function serveFromIndexedDB(path, options) {
      console.log('[OfflineDB] Serving from IndexedDB:', path);
      
      const user_id = window.USER_ID;
      
      if (path.includes('/api/notes/list')) {
        return getNotes(user_id)
          .then(notes => {
            // For each note, get its labels
            return Promise.all(notes.map(note => {
              return getNoteLabels(note.id)
                .then(labels => {
                  note.labels = labels;
                  return note;
                });
            }));
          })
          .then(notes => {
            return new Response(
              JSON.stringify({ success: true, notes: notes, isOffline: true }),
              { headers: { 'Content-Type': 'application/json' } }
            );
          });
      } 
      else if (path.includes('/api/labels/list')) {
        return getLabels(user_id)
          .then(labels => {
            return new Response(
              JSON.stringify({ success: true, labels: labels, isOffline: true }),
              { headers: { 'Content-Type': 'application/json' } }
            );
          });
      }
      else if (path.includes('/api/notes/get/') && options.method === 'GET') {
        const noteId = parseInt(path.split('/').pop());
        return getNote(noteId)
          .then(note => {
            if (!note) {
              return new Response(
                JSON.stringify({ error: 'Note not found', isOffline: true }),
                { status: 404, headers: { 'Content-Type': 'application/json' } }
              );
            }
            
            return getNoteLabels(noteId)
              .then(labels => {
                note.labels = labels;
                return new Response(
                  JSON.stringify({ success: true, note: note, isOffline: true }),
                  { headers: { 'Content-Type': 'application/json' } }
                );
              });
          });
      }
      else if ((path.includes('/api/notes/create') || path.includes('/api/notes/update/')) && options.method === 'POST') {
        // For offline form submissions, we need to extract the form data
        const body = options.body;
        if (body instanceof FormData) {
          const title = body.get('title');
          const content = body.get('content');
          const labels = body.getAll('labels[]');
          
          // Create a note object
          const note = {
            id: path.includes('update') ? parseInt(path.split('/').pop()) : Date.now(),
            user_id: user_id,
            title: title,
            content: content,
            is_pinned: false,
            updated_at: new Date().toISOString(),
            pendingSync: true
          };
          
          // Save the note to IndexedDB
          return saveNote(note)
            .then(() => {
              // Handle labels if any
              if (labels.length > 0) {
                // First remove existing labels
                return detachAllLabelsFromNote(note.id)
                  .then(() => {
                    // Then attach new labels
                    return Promise.all(labels.map(labelId => {
                      return attachLabelToNote(note.id, parseInt(labelId));
                    }));
                  });
              }
              return Promise.resolve();
            })
            .then(() => {
              // Add to pending actions
              return addPendingAction({
                action: path.includes('update') ? 'update_note' : 'create_note',
                entity_id: note.id,
                data: { note_id: note.id, title: note.title },
                timestamp: new Date().getTime()
              });
            })
            .then(() => {
              return new Response(
                JSON.stringify({ 
                  success: true, 
                  message: 'Note saved locally (offline)', 
                  note_id: note.id,
                  isOffline: true
                }),
                { headers: { 'Content-Type': 'application/json' } }
              );
            });
        }
      }
      else if (path.includes('/api/notes/delete/') && options.method === 'POST') {
        const noteId = parseInt(path.split('/').pop());
        
        // Delete from IndexedDB
        return deleteNote(noteId)
          .then(() => {
            // Add to pending actions
            return addPendingAction({
              action: 'delete_note',
              entity_id: noteId,
              timestamp: new Date().getTime()
            });
          })
          .then(() => {
            return new Response(
              JSON.stringify({ 
                success: true, 
                message: 'Note deleted locally (offline)',
                isOffline: true 
              }),
              { headers: { 'Content-Type': 'application/json' } }
            );
          });
      }
      
      // Default response for unhandled API requests
      return Promise.resolve(new Response(
        JSON.stringify({ 
          error: 'Cannot perform this action while offline',
          isOffline: true 
        }),
        { status: 503, headers: { 'Content-Type': 'application/json' } }
      ));
    }
    
    // Initialize offline functionality when DOM is loaded
    function init() {
      console.log('[OfflineDB] Initializing...');
      
      // Initialize database
      return initDatabase()
        .then(() => {
          // Set up network status listeners
          setupNetworkListeners();
          
          // Add offline indicator styles
          addOfflineStyles();
          
          // Initial update of offline indicator
          updateOfflineIndicator();
          
          // Replace fetch API with offline-aware version
          setupFetchInterceptor();
          
          // If online, synchronize with server
          if (isOnline) {
            // Delay sync to allow page to load first
            setTimeout(() => {
              syncWithServer();
            }, 1000);
          } else {
            // Mark as offline mode
            document.body.classList.add('offline-mode');
          }
          
          console.log('[OfflineDB] Initialization complete. Online status:', isOnline);
        })
        .catch(error => {
          console.error('[OfflineDB] Initialization failed:', error);
        });
    }
    
    // Public API
    return {
      init,
      isOnline: () => isOnline,
      getNote,
      getNotes,
      saveNote,
      deleteNote,
      getLabels,
      saveLabel,
      deleteLabel,
      syncWithServer
    };
  })();
  
  // Initialize when DOM is loaded
  document.addEventListener('DOMContentLoaded', function() {
    // Check if offline mode is enabled
    if (typeof ENABLE_OFFLINE_MODE !== 'undefined' && ENABLE_OFFLINE_MODE) {
      OfflineDB.init()
        .then(() => {
          console.log('Offline functionality initialized');
        })
        .catch(error => {
          console.error('Failed to initialize offline functionality:', error);
        });
    }
  });