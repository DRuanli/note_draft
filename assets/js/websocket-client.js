/**
 * WebSocket Client for Note Management Application
 * This script handles real-time collaboration on notes using WebSockets
 */

class NoteWebSocket {
    constructor(options = {}) {
        this.options = Object.assign({
            url: this.getWebSocketUrl(),
            reconnectDelay: 5000,
            debug: false,
            autoReconnect: true,
            userId: null,
            authToken: null,
            onConnect: null,
            onDisconnect: null,
            onReconnect: null,
            onMessage: null,
            onError: null
        }, options);

        this.socket = null;
        this.connected = false;
        this.authenticated = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectTimer = null;
        this.currentSubscriptions = new Set();
        this.pendingMessages = [];
        
        this.cursorColors = [
            '#FF5733', '#33FF57', '#3357FF', '#FF33F5', '#F5FF33', 
            '#33FFF5', '#FF8033', '#8033FF', '#33FF80', '#FF3380'
        ];
        
        this.userColors = {};
        this.userCursors = {};
        
        // Initialize
        if (this.options.debug) {
            console.log('WebSocket client initialized');
        }
    }
    
    // Get WebSocket URL based on current protocol and host
    getWebSocketUrl() {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const host = window.location.host;
        return `${protocol}//${host}:8080`;
    }
    
    // Connect to WebSocket server
    connect() {
        if (this.socket) {
            this.disconnect();
        }
        
        try {
            this.log('Connecting to WebSocket server...');
            this.socket = new WebSocket(this.options.url);
            
            this.socket.onopen = () => this.handleOpen();
            this.socket.onmessage = (event) => this.handleMessage(event);
            this.socket.onclose = (event) => this.handleClose(event);
            this.socket.onerror = (error) => this.handleError(error);
        } catch (error) {
            this.log('Error connecting to WebSocket server:', error);
            this.scheduleReconnect();
        }
        
        return this;
    }
    
    // Disconnect from WebSocket server
    disconnect() {
        if (this.socket) {
            this.log('Disconnecting from WebSocket server...');
            this.socket.close();
            this.socket = null;
            this.connected = false;
            this.authenticated = false;
            this.currentSubscriptions.clear();
        }
        
        return this;
    }
    
    // Handle WebSocket open event
    handleOpen() {
        this.connected = true;
        this.reconnectAttempts = 0;
        this.log('Connected to WebSocket server');
        
        // Authenticate if user ID and token are provided
        if (this.options.userId && this.options.authToken) {
            this.authenticate();
        }
        
        // Call onConnect callback
        if (typeof this.options.onConnect === 'function') {
            this.options.onConnect();
        }
        
        // Send any pending messages
        this.sendPendingMessages();
    }
    
    // Handle WebSocket message event
    handleMessage(event) {
        try {
            const data = JSON.parse(event.data);
            this.log('Received message:', data);
            
            // Handle authentication response
            if (data.type === 'auth_response') {
                this.authenticated = data.success;
                
                if (data.success) {
                    this.log('Authenticated successfully');
                    
                    // Resubscribe to previously subscribed notes
                    this.currentSubscriptions.forEach(noteId => {
                        this.subscribe(noteId);
                    });
                } else {
                    this.log('Authentication failed:', data.message);
                }
            }
            
            // Call onMessage callback
            if (typeof this.options.onMessage === 'function') {
                this.options.onMessage(data);
            }
        } catch (error) {
            this.log('Error parsing message:', error);
        }
    }
    
    // Handle WebSocket close event
    handleClose(event) {
        this.connected = false;
        this.authenticated = false;
        this.log(`WebSocket connection closed: ${event.code} ${event.reason}`);
        
        // Call onDisconnect callback
        if (typeof this.options.onDisconnect === 'function') {
            this.options.onDisconnect(event);
        }
        
        // Attempt to reconnect
        if (this.options.autoReconnect) {
            this.scheduleReconnect();
        }
    }
    
    // Handle WebSocket error event
    handleError(error) {
        this.log('WebSocket error:', error);
        
        // Call onError callback
        if (typeof this.options.onError === 'function') {
            this.options.onError(error);
        }
    }
    
    // Schedule reconnection
    scheduleReconnect() {
        if (this.reconnectTimer) {
            clearTimeout(this.reconnectTimer);
        }
        
        this.reconnectAttempts++;
        
        if (this.reconnectAttempts <= this.maxReconnectAttempts) {
            const delay = this.options.reconnectDelay * Math.pow(1.5, this.reconnectAttempts - 1);
            this.log(`Scheduling reconnect in ${delay}ms (attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
            
            this.reconnectTimer = setTimeout(() => {
                this.log('Attempting to reconnect...');
                this.connect();
                
                // Call onReconnect callback
                if (typeof this.options.onReconnect === 'function') {
                    this.options.onReconnect(this.reconnectAttempts);
                }
            }, delay);
        } else {
            this.log('Max reconnect attempts reached');
        }
    }
    
    // Authenticate with WebSocket server
    authenticate() {
        this.send({
            type: 'auth',
            user_id: this.options.userId,
            token: this.options.authToken
        });
    }
    
    // Subscribe to note updates
    subscribe(noteId) {
        this.log(`Subscribing to note ${noteId}`);
        this.currentSubscriptions.add(noteId);
        
        if (this.connected && this.authenticated) {
            this.send({
                type: 'subscribe',
                note_id: noteId
            });
        }
    }
    
    // Unsubscribe from note updates
    unsubscribe(noteId) {
        this.log(`Unsubscribing from note ${noteId}`);
        this.currentSubscriptions.delete(noteId);
        
        if (this.connected && this.authenticated) {
            this.send({
                type: 'unsubscribe',
                note_id: noteId
            });
        }
    }
    
    // Send note updates
    sendNoteUpdate(noteId, content, title = null) {
        const data = {
            type: 'note_update',
            note_id: noteId,
            content: content
        };
        
        if (title !== null) {
            data.title = title;
        }
        
        this.send(data);
    }
    
    // Send cursor position updates
    sendCursorPosition(noteId, position) {
        this.send({
            type: 'cursor_position',
            note_id: noteId,
            position: position
        });
    }
    
    // Send a message to the WebSocket server
    send(data) {
        if (this.connected && this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify(data));
            this.log('Sent message:', data);
        } else {
            this.log('Socket not connected, adding message to pending queue:', data);
            this.pendingMessages.push(data);
        }
    }
    
    // Send any pending messages
    sendPendingMessages() {
        if (this.pendingMessages.length > 0) {
            this.log(`Sending ${this.pendingMessages.length} pending messages`);
            
            const messages = [...this.pendingMessages];
            this.pendingMessages = [];
            
            messages.forEach(data => this.send(data));
        }
    }
    
    // Show notification for new shared notes
    showSharedNotesNotification(notes) {
        // Create a notification container if it doesn't exist
        let container = document.getElementById('shared-notes-notifications');
        if (!container) {
            container = document.createElement('div');
            container.id = 'shared-notes-notifications';
            container.className = 'position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1050';
            document.body.appendChild(container);
        }
        
        // Create a toast for each note
        notes.forEach(note => {
            const toastId = `note-toast-${note.id}`;
            
            if (document.getElementById(toastId)) {
                return; // Skip duplicate notifications
            }
            
            const toast = document.createElement('div');
            toast.id = toastId;
            toast.className = 'toast show';
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            toast.innerHTML = `
                <div class="toast-header">
                    <strong class="me-auto">New Shared Note</strong>
                    <small>${this.formatTimestamp(note.shared_at)}</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    <p>${note.owner_name} shared "${note.title}" with you.</p>
                    <p class="mb-0">Access: ${note.can_edit ? 'Edit' : 'View only'}</p>
                    <div class="mt-2 pt-2 border-top">
                        <a href="${BASE_URL}/notes/shared" class="btn btn-primary btn-sm">View Note</a>
                    </div>
                </div>
            `;
            
            container.appendChild(toast);
            
            // Auto-dismiss after 10 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 10000);
            
            // Add click handler for close button
            const closeBtn = toast.querySelector('.btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                });
            }
        });
        
        // Play notification sound if available
        const notificationSound = document.getElementById('notification-sound');
        if (notificationSound) {
            notificationSound.play().catch(e => console.log('Could not play notification sound:', e));
        }
    }
    
    // Format a timestamp for display
    formatTimestamp(timestamp) {
        try {
            const date = new Date(timestamp);
            
            // Check if it's today
            const today = new Date();
            if (date.toDateString() === today.toDateString()) {
                return `Today at ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
            }
            
            // Check if it's yesterday
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            if (date.toDateString() === yesterday.toDateString()) {
                return `Yesterday at ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
            }
            
            // Otherwise show full date
            return date.toLocaleString();
        } catch (e) {
            return timestamp;
        }
    }
    
    // Get a consistent color for a user
    getUserColor(userId) {
        if (!this.userColors[userId]) {
            const colorIndex = Object.keys(this.userColors).length % this.cursorColors.length;
            this.userColors[userId] = this.cursorColors[colorIndex];
        }
        
        return this.userColors[userId];
    }
    
    // Create or update a user cursor
    updateUserCursor(userId, userName, position, elementId) {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        if (!this.userCursors[userId]) {
            // Create new cursor element
            const cursor = document.createElement('div');
            cursor.className = 'remote-cursor';
            cursor.style.position = 'absolute';
            cursor.style.pointerEvents = 'none';
            cursor.style.zIndex = '100';
            cursor.style.transition = 'transform 0.2s ease';
            
            const userColor = this.getUserColor(userId);
            
            cursor.innerHTML = `
                <div style="position:relative;">
                    <div style="
                        position: absolute;
                        bottom: 100%;
                        left: 0;
                        background-color: ${userColor};
                        color: white;
                        padding: 2px 6px;
                        border-radius: 4px;
                        font-size: 12px;
                        white-space: nowrap;
                        transform: translateY(-4px);
                    ">${userName}</div>
                    <div style="
                        width: 2px;
                        height: 20px;
                        background-color: ${userColor};
                        animation: cursorBlink 1s infinite;
                    "></div>
                </div>
            `;
            
            // Add CSS animation for blinking
            if (!document.getElementById('cursor-animations')) {
                const style = document.createElement('style');
                style.id = 'cursor-animations';
                style.textContent = `
                    @keyframes cursorBlink {
                        0%, 100% { opacity: 1; }
                        50% { opacity: 0.5; }
                    }
                `;
                document.head.appendChild(style);
            }
            
            document.body.appendChild(cursor);
            this.userCursors[userId] = cursor;
        }
        
        // Position the cursor
        const cursor = this.userCursors[userId];
        
        try {
            // Calculate cursor position based on textarea or contenteditable
            if (element.tagName === 'TEXTAREA') {
                this.positionCursorInTextarea(element, cursor, position);
            } else if (element.isContentEditable) {
                this.positionCursorInContentEditable(element, cursor, position);
            }
        } catch (e) {
            console.error('Error positioning cursor:', e);
        }
        
        // Set timeout to remove cursor after inactivity
        if (this.cursorTimeouts && this.cursorTimeouts[userId]) {
            clearTimeout(this.cursorTimeouts[userId]);
        } else {
            this.cursorTimeouts = {};
        }
        
        this.cursorTimeouts[userId] = setTimeout(() => {
            if (cursor.parentNode) {
                cursor.style.opacity = '0';
                setTimeout(() => {
                    if (cursor.parentNode) {
                        cursor.parentNode.removeChild(cursor);
                        delete this.userCursors[userId];
                    }
                }, 300);
            }
        }, 10000); // Remove after 10 seconds of inactivity
    }
    
    // Position cursor in a textarea
    positionCursorInTextarea(textarea, cursor, position) {
        // Create a temporary div to measure text dimensions
        const div = document.createElement('div');
        div.style.position = 'absolute';
        div.style.visibility = 'hidden';
        div.style.whiteSpace = 'pre-wrap';
        div.style.wordWrap = 'break-word';
        div.style.width = textarea.offsetWidth + 'px';
        div.style.font = window.getComputedStyle(textarea).font;
        div.style.paddingLeft = window.getComputedStyle(textarea).paddingLeft;
        div.style.paddingTop = window.getComputedStyle(textarea).paddingTop;
        
        // Get text before cursor
        const text = textarea.value;
        const beforeCursor = text.substring(0, position);
        const lines = beforeCursor.split('\n');
        
        // Calculate cursor position
        div.textContent = lines[lines.length - 1];
        document.body.appendChild(div);
        
        const rect = textarea.getBoundingClientRect();
        const cursorLeft = div.offsetWidth + textarea.scrollLeft;
        
        // Calculate line height and cursor top position
        const lineHeight = parseInt(window.getComputedStyle(textarea).lineHeight);
        const paddingTop = parseInt(window.getComputedStyle(textarea).paddingTop);
        const scrollTop = textarea.scrollTop;
        
        let cursorTop = 0;
        if (lines.length > 1) {
            for (let i = 0; i < lines.length - 1; i++) {
                // For each line, calculate its height including wrapping
                div.textContent = lines[i];
                const lineWrapCount = Math.ceil(div.offsetWidth / textarea.offsetWidth);
                cursorTop += lineHeight * lineWrapCount;
            }
        }
        
        // Position the cursor
        cursor.style.left = (rect.left + cursorLeft) + 'px';
        cursor.style.top = (rect.top + cursorTop + paddingTop - scrollTop) + 'px';
        
        // Clean up
        document.body.removeChild(div);
    }
    
    // Position cursor in a contenteditable element
    positionCursorInContentEditable(element, cursor, position) {
        // This is a simplified implementation
        // For a proper implementation, you would need to find the exact DOM node and offset
        const range = document.createRange();
        const textNodes = this.getTextNodes(element);
        let currentPos = 0;
        let found = false;
        
        for (let i = 0; i < textNodes.length; i++) {
            const node = textNodes[i];
            const nodeLength = node.textContent.length;
            
            if (currentPos + nodeLength >= position) {
                const offset = position - currentPos;
                range.setStart(node, offset);
                range.setEnd(node, offset);
                found = true;
                break;
            }
            
            currentPos += nodeLength;
        }
        
        if (!found && textNodes.length > 0) {
            const lastNode = textNodes[textNodes.length - 1];
            range.setStart(lastNode, lastNode.textContent.length);
            range.setEnd(lastNode, lastNode.textContent.length);
        }
        
        // Get coordinates
        const rect = range.getBoundingClientRect();
        cursor.style.left = rect.left + 'px';
        cursor.style.top = rect.top + 'px';
    }
    
    // Get all text nodes in an element
    getTextNodes(element) {
        const textNodes = [];
        const walk = document.createTreeWalker(element, NodeFilter.SHOW_TEXT, null, false);
        let node;
        while (node = walk.nextNode()) {
            textNodes.push(node);
        }
        return textNodes;
    }
    
    // Remove all user cursors
    clearUserCursors() {
        Object.values(this.userCursors).forEach(cursor => {
            if (cursor.parentNode) {
                cursor.parentNode.removeChild(cursor);
            }
        });
        
        this.userCursors = {};
    }
    
    // Log message if debug is enabled
    log(...args) {
        if (this.options.debug) {
            console.log('[WebSocket]', ...args);
        }
    }
}

// Initialize WebSocket connection when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if WebSocket functionality is enabled
    if (typeof ENABLE_WEBSOCKETS !== 'undefined' && ENABLE_WEBSOCKETS) {
        // Check if user is logged in
        if (typeof USER_ID !== 'undefined') {
            // Create WebSocket client
            window.noteWebSocket = new NoteWebSocket({
                userId: USER_ID,
                authToken: 'simple-auth-token', // In a real app, use a secure token
                debug: true,
                onConnect: function() {
                    console.log('Connected to WebSocket server');
                },
                onMessage: function(data) {
                    // Handle incoming messages
                    if (data.type === 'note_updated') {
                        // Handle note update
                        const noteEditor = document.getElementById('note-content');
                        const titleInput = document.getElementById('note-title');
                        
                        if (noteEditor && data.content) {
                            // Only update if we're not currently focused on the editor
                            if (document.activeElement !== noteEditor) {
                                noteEditor.value = data.content;
                                
                                // Show notification
                                showCollaborationToast(`${data.user_name} updated the note`);
                            }
                        }
                        
                        if (titleInput && data.title) {
                            // Only update if we're not currently focused on the title
                            if (document.activeElement !== titleInput) {
                                titleInput.value = data.title;
                            }
                        }
                    } else if (data.type === 'cursor_position') {
                        // Update remote cursor position
                        window.noteWebSocket.updateUserCursor(
                            data.user_id,
                            data.user_name,
                            data.position,
                            'note-content'
                        );
                    } else if (data.type === 'user_joined') {
                        // Show notification that a user joined
                        showCollaborationToast(`${data.user_name} joined the note`);
                    } else if (data.type === 'user_left') {
                        // Show notification that a user left
                        showCollaborationToast(`${data.user_name} left the note`);
                    } else if (data.type === 'new_shared_notes') {
                        // Show notification for new shared notes
                        window.noteWebSocket.showSharedNotesNotification(data.notes);
                    }
                }
            });
            
            // Connect to WebSocket server
            window.noteWebSocket.connect();
            
            // Check if we're on a note editing page
            const noteEditor = document.getElementById('note-content');
            const noteId = getNoteIdFromUrl();
            
            if (noteEditor && noteId) {
                // Subscribe to note updates
                window.noteWebSocket.subscribe(noteId);
                
                // Set up event listeners
                let lastContent = noteEditor.value;
                let lastCursorPos = 0;
                let updateTimeout = null;
                let cursorTimeout = null;
                
                // Handle input changes
                noteEditor.addEventListener('input', function() {
                    const currentContent = this.value;
                    
                    // Clear any pending updates
                    if (updateTimeout) clearTimeout(updateTimeout);
                    
                    // Schedule update after a short delay (for performance)
                    updateTimeout = setTimeout(function() {
                        if (currentContent !== lastContent) {
                            const titleInput = document.getElementById('note-title');
                            const title = titleInput ? titleInput.value : null;
                            
                            window.noteWebSocket.sendNoteUpdate(noteId, currentContent, title);
                            lastContent = currentContent;
                        }
                    }, 500);
                });
                
                // Handle cursor position changes
                noteEditor.addEventListener('click', updateCursorPosition);
                noteEditor.addEventListener('keyup', updateCursorPosition);
                
                function updateCursorPosition() {
                    const cursorPos = noteEditor.selectionStart;
                    
                    // Only send if position changed
                    if (cursorPos !== lastCursorPos) {
                        // Clear any pending cursor updates
                        if (cursorTimeout) clearTimeout(cursorTimeout);
                        
                        // Schedule cursor update after a short delay
                        cursorTimeout = setTimeout(function() {
                            window.noteWebSocket.sendCursorPosition(noteId, cursorPos);
                            lastCursorPos = cursorPos;
                        }, 100);
                    }
                }
                
                // Unsubscribe when leaving the page
                window.addEventListener('beforeunload', function() {
                    window.noteWebSocket.unsubscribe(noteId);
                });
            }
        }
    }
});

// Helper to show collaboration toast notification
function showCollaborationToast(message) {
    // Create a toast container if it doesn't exist
    let container = document.getElementById('collaboration-toasts');
    if (!container) {
        container = document.createElement('div');
        container.id = 'collaboration-toasts';
        container.className = 'position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '1050';
        document.body.appendChild(container);
    }
    
    // Create a new toast
    const toast = document.createElement('div');
    toast.className = 'toast show';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="toast-header">
            <i class="fas fa-users me-2 text-primary"></i>
            <strong class="me-auto">Collaboration</strong>
            <small>Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            ${message}
        </div>
    `;
    
    container.appendChild(toast);
    
    // Auto-dismiss after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
    
    // Add click handler for close button
    const closeBtn = toast.querySelector('.btn-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        });
    }
}

// Helper function to get the note ID from the URL
function getNoteIdFromUrl() {
    const path = window.location.pathname;
    const matches = path.match(/\/notes\/edit\/(\d+)/);
    
    if (matches && matches[1]) {
        return matches[1];
    }
    
    return null;
}