<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/main.css">
    <link rel="manifest" href="/manifest.json">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .offline-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .offline-card {
            max-width: 500px;
            text-align: center;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        .offline-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .cached-notes {
            margin-top: 30px;
            text-align: left;
        }
        .note-list {
            max-height: 200px;
            overflow-y: auto;
            margin-top: 10px;
        }
        .retry-btn {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-card">
            <div class="offline-icon">
                <i class="fas fa-wifi-slash"></i>
            </div>
            <h2>You're Offline</h2>
            <p class="lead">It seems you've lost your internet connection.</p>
            <p>Don't worry! You can still access your previously loaded notes.</p>
            
            <div id="cached-notes" class="cached-notes d-none">
                <h5><i class="fas fa-sticky-note me-2"></i>Available Offline Notes:</h5>
                <div id="note-list" class="note-list list-group">
                    <!-- Notes will be populated by JavaScript -->
                </div>
            </div>
            
            <button id="retry-btn" class="btn btn-primary retry-btn">
                <i class="fas fa-sync-alt me-2"></i>Try Again
            </button>
        </div>
    </div>

    <script>
        // Check if IndexedDB is supported
        if ('indexedDB' in window) {
            const DB_NAME = 'NoteAppOfflineDB';
            const DB_VERSION = 1;
            
            // Open the database
            const request = indexedDB.open(DB_NAME, DB_VERSION);
            
            request.onsuccess = function(event) {
                const db = event.target.result;
                
                // Get user ID from URL or session storage if available
                let userId = null;
                
                try {
                    if (window.USER_ID) {
                        userId = window.USER_ID;
                    } else if (sessionStorage.getItem('user_id')) {
                        userId = parseInt(sessionStorage.getItem('user_id'));
                    }
                } catch (e) {
                    console.error('Error getting user ID:', e);
                }
                
                if (userId && db.objectStoreNames.contains('notes')) {
                    // Get all notes for the user
                    const transaction = db.transaction('notes', 'readonly');
                    const store = transaction.objectStore('notes');
                    const index = store.index('user_id');
                    
                    const request = index.getAll(userId);
                    
                    request.onsuccess = function(event) {
                        const notes = event.target.result;
                        
                        if (notes && notes.length > 0) {
                            // Sort by updated date, newest first
                            notes.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));
                            
                            // Show the cached notes section
                            const cachedNotesSection = document.getElementById('cached-notes');
                            cachedNotesSection.classList.remove('d-none');
                            
                            // Populate the note list
                            const noteList = document.getElementById('note-list');
                            noteList.innerHTML = '';
                            
                            notes.forEach(note => {
                                const noteItem = document.createElement('a');
                                noteItem.className = 'list-group-item list-group-item-action';
                                noteItem.href = `/notes/edit/${note.id}`;
                                
                                // Format date
                                let dateDisplay = '';
                                try {
                                    const date = new Date(note.updated_at);
                                    dateDisplay = date.toLocaleDateString() + ' ' + 
                                                 date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                                } catch(e) {
                                    dateDisplay = 'Unknown date';
                                }
                                
                                noteItem.innerHTML = `
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">${escapeHtml(note.title)}</h5>
                                        <small>${dateDisplay}</small>
                                    </div>
                                    <p class="mb-1 text-truncate">${escapeHtml(note.content)}</p>
                                `;
                                
                                noteList.appendChild(noteItem);
                            });
                        }
                    };
                    
                    request.onerror = function(event) {
                        console.error('Error getting notes:', event.target.error);
                    };
                }
            };
            
            request.onerror = function(event) {
                console.error('Error opening database:', event.target.error);
            };
        }
        
        // Helper function to escape HTML
        function escapeHtml(unsafe) {
            return unsafe
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }
        
        // Retry button handler
        document.getElementById('retry-btn').addEventListener('click', function() {
            // Reload the page to check for internet connection
            window.location.reload();
        });
        
        // Listen for online events
        window.addEventListener('online', function() {
            // Redirect to the homepage when back online
            window.location.href = '/';
        });
    </script>
</body>
</html>