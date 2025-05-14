<?php
/**
 * WebSocket Server for Note Management Application
 * 
 * This file implements a WebSocket server for real-time collaboration on notes.
 * To run this server, you need to have the Ratchet library installed:
 * 
 * composer require cboden/ratchet
 * 
 * Then run the server from command line:
 * php websocket/server.php
 */

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class NoteServer implements MessageComponentInterface {
    protected $clients;
    protected $userConnections;
    protected $noteSubscriptions;
    protected $db;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
        $this->noteSubscriptions = [];
        $this->db = getDB();
        
        echo "WebSocket server initialized\n";
    }
    
    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            
            if (!isset($data['type'])) {
                echo "Received message with no type\n";
                return;
            }
            
            echo "Received message of type: {$data['type']}\n";
            
            switch ($data['type']) {
                case 'auth':
                    $this->handleAuth($from, $data);
                    break;
                    
                case 'subscribe':
                    $this->handleSubscribe($from, $data);
                    break;
                    
                case 'unsubscribe':
                    $this->handleUnsubscribe($from, $data);
                    break;
                    
                case 'note_update':
                    $this->handleNoteUpdate($from, $data);
                    break;
                    
                case 'cursor_position':
                    $this->handleCursorPosition($from, $data);
                    break;
                    
                default:
                    echo "Unknown message type: {$data['type']}\n";
                    break;
            }
        } catch (\Exception $e) {
            echo "Error processing message: {$e->getMessage()}\n";
        }
    }
    
    public function onClose(ConnectionInterface $conn) {
        // Remove the connection
        $this->clients->detach($conn);
        
        // Remove from user connections
        foreach ($this->userConnections as $userId => $connection) {
            if ($connection === $conn) {
                unset($this->userConnections[$userId]);
                echo "User {$userId} disconnected\n";
                break;
            }
        }
        
        // Remove from note subscriptions
        foreach ($this->noteSubscriptions as $noteId => $subscribers) {
            if (($key = array_search($conn, $subscribers)) !== false) {
                unset($this->noteSubscriptions[$noteId][$key]);
                echo "Connection {$conn->resourceId} unsubscribed from note {$noteId}\n";
                
                // If no more subscribers, remove the note
                if (count($this->noteSubscriptions[$noteId]) === 0) {
                    unset($this->noteSubscriptions[$noteId]);
                }
            }
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
    
    // Handle authentication
    protected function handleAuth($conn, $data) {
        if (!isset($data['user_id']) || !isset($data['token'])) {
            echo "Auth failed: missing user_id or token\n";
            return;
        }
        
        $userId = $data['user_id'];
        $token = $data['token'];
        
        // Verify the token (simple implementation - in production use a more secure method)
        $isValid = $this->verifyAuthToken($userId, $token);
        
        if ($isValid) {
            $this->userConnections[$userId] = $conn;
            $conn->userId = $userId; // Store user ID in connection for easy access
            
            echo "User {$userId} authenticated\n";
            
            // Send success response
            $conn->send(json_encode([
                'type' => 'auth_response',
                'success' => true,
                'message' => 'Authentication successful'
            ]));
            
            // Check for any shared notes with this user
            $this->notifyOfSharedNotes($userId);
        } else {
            echo "Auth failed for user {$userId}: invalid token\n";
            
            // Send failure response
            $conn->send(json_encode([
                'type' => 'auth_response',
                'success' => false,
                'message' => 'Authentication failed'
            ]));
        }
    }
    
    // Simple token verification (enhance this in production)
    protected function verifyAuthToken($userId, $token) {
        // In a real implementation, you would validate against a stored token
        // For now, we'll accept any non-empty token
        return !empty($token);
    }
    
    // Handle note subscription
    protected function handleSubscribe($conn, $data) {
        if (!isset($data['note_id']) || !isset($conn->userId)) {
            echo "Subscribe failed: missing note_id or not authenticated\n";
            return;
        }
        
        $noteId = $data['note_id'];
        $userId = $conn->userId;
        
        // Check if user has access to this note
        if (!$this->checkNoteAccess($userId, $noteId)) {
            echo "Subscribe failed: user {$userId} does not have access to note {$noteId}\n";
            
            $conn->send(json_encode([
                'type' => 'subscribe_response',
                'success' => false,
                'message' => 'Access denied'
            ]));
            
            return;
        }
        
        // Add to subscriptions
        if (!isset($this->noteSubscriptions[$noteId])) {
            $this->noteSubscriptions[$noteId] = [];
        }
        
        $this->noteSubscriptions[$noteId][] = $conn;
        
        echo "User {$userId} subscribed to note {$noteId}\n";
        
        // Send success response
        $conn->send(json_encode([
            'type' => 'subscribe_response',
            'success' => true,
            'note_id' => $noteId,
            'message' => 'Subscribed to note'
        ]));
        
        // Notify other subscribers
        $this->broadcastToNote($noteId, [
            'type' => 'user_joined',
            'user_id' => $userId,
            'user_name' => $this->getUserName($userId),
            'note_id' => $noteId,
            'timestamp' => time()
        ], [$conn]);
    }
    
    // Handle unsubscribe
    protected function handleUnsubscribe($conn, $data) {
        if (!isset($data['note_id']) || !isset($conn->userId)) {
            echo "Unsubscribe failed: missing note_id or not authenticated\n";
            return;
        }
        
        $noteId = $data['note_id'];
        $userId = $conn->userId;
        
        // Remove from subscriptions
        if (isset($this->noteSubscriptions[$noteId])) {
            if (($key = array_search($conn, $this->noteSubscriptions[$noteId])) !== false) {
                unset($this->noteSubscriptions[$noteId][$key]);
                
                echo "User {$userId} unsubscribed from note {$noteId}\n";
                
                // If no more subscribers, remove the note
                if (count($this->noteSubscriptions[$noteId]) === 0) {
                    unset($this->noteSubscriptions[$noteId]);
                } else {
                    // Notify other subscribers
                    $this->broadcastToNote($noteId, [
                        'type' => 'user_left',
                        'user_id' => $userId,
                        'user_name' => $this->getUserName($userId),
                        'note_id' => $noteId,
                        'timestamp' => time()
                    ], [$conn]);
                }
            }
        }
        
        // Send success response
        $conn->send(json_encode([
            'type' => 'unsubscribe_response',
            'success' => true,
            'note_id' => $noteId,
            'message' => 'Unsubscribed from note'
        ]));
    }
    
    // Handle note update
    protected function handleNoteUpdate($conn, $data) {
        if (!isset($data['note_id']) || !isset($conn->userId) || !isset($data['content'])) {
            echo "Note update failed: missing required fields\n";
            return;
        }
        
        $noteId = $data['note_id'];
        $userId = $conn->userId;
        $content = $data['content'];
        $title = isset($data['title']) ? $data['title'] : null;
        
        // Check if user has edit access
        if (!$this->checkNoteEditAccess($userId, $noteId)) {
            echo "Note update failed: user {$userId} does not have edit access to note {$noteId}\n";
            
            $conn->send(json_encode([
                'type' => 'update_response',
                'success' => false,
                'message' => 'Edit access denied'
            ]));
            
            return;
        }
        
        // Update note in database if content or title is valid
        if (!empty($content) || ($title !== null && !empty($title))) {
            $updateSuccess = $this->updateNoteInDatabase($noteId, $title, $content);
            
            if ($updateSuccess) {
                echo "Note {$noteId} updated by user {$userId}\n";
                
                // Broadcast to all subscribers except sender
                $this->broadcastToNote($noteId, [
                    'type' => 'note_updated',
                    'note_id' => $noteId,
                    'user_id' => $userId,
                    'user_name' => $this->getUserName($userId),
                    'title' => $title,
                    'content' => $content,
                    'timestamp' => time()
                ], [$conn]);
                
                // Send success response to sender
                $conn->send(json_encode([
                    'type' => 'update_response',
                    'success' => true,
                    'note_id' => $noteId,
                    'message' => 'Note updated successfully'
                ]));
            } else {
                echo "Note update failed: Database error\n";
                
                // Send failure response
                $conn->send(json_encode([
                    'type' => 'update_response',
                    'success' => false,
                    'message' => 'Failed to update note in database'
                ]));
            }
        } else {
            echo "Note update failed: Empty content or title\n";
            
            // Send failure response
            $conn->send(json_encode([
                'type' => 'update_response',
                'success' => false,
                'message' => 'Content or title cannot be empty'
            ]));
        }
    }
    
    // Handle cursor position updates
    protected function handleCursorPosition($conn, $data) {
        if (!isset($data['note_id']) || !isset($conn->userId) || !isset($data['position'])) {
            echo "Cursor position update failed: missing required fields\n";
            return;
        }
        
        $noteId = $data['note_id'];
        $userId = $conn->userId;
        $position = $data['position'];
        
        // Broadcast to all subscribers except sender
        $this->broadcastToNote($noteId, [
            'type' => 'cursor_position',
            'note_id' => $noteId,
            'user_id' => $userId,
            'user_name' => $this->getUserName($userId),
            'position' => $position,
            'timestamp' => time()
        ], [$conn]);
    }
    
    // Check if user has access to a note
    protected function checkNoteAccess($userId, $noteId) {
        try {
            // Check if user owns the note
            $stmt = $this->db->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $noteId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return true; // User is the owner
            }
            
            // Check if note is shared with user
            $stmt = $this->db->prepare("SELECT id FROM shared_notes WHERE note_id = ? AND recipient_id = ?");
            $stmt->bind_param("ii", $noteId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->num_rows > 0; // Note is shared with user
        } catch (\Exception $e) {
            echo "Error checking note access: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    // Check if user has edit access to a note
    protected function checkNoteEditAccess($userId, $noteId) {
        try {
            // Check if user owns the note
            $stmt = $this->db->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $noteId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return true; // User is the owner
            }
            
            // Check if note is shared with user and has edit permissions
            $stmt = $this->db->prepare("SELECT id FROM shared_notes WHERE note_id = ? AND recipient_id = ? AND can_edit = 1");
            $stmt->bind_param("ii", $noteId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->num_rows > 0; // User has edit permissions
        } catch (\Exception $e) {
            echo "Error checking note edit access: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    
    // Update note in database
    protected function updateNoteInDatabase($noteId, $title, $content) {
        try {
            if ($title !== null) {
                // Update both title and content
                $stmt = $this->db->prepare("UPDATE notes SET title = ?, content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->bind_param("ssi", $title, $content, $noteId);
            } else {
                // Update content only
                $stmt = $this->db->prepare("UPDATE notes SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->bind_param("si", $content, $noteId);
            }
            
            return $stmt->execute();
        } catch (\Exception $e) {
            echo "Database error: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    // Get user name from database
    protected function getUserName($userId) {
        $stmt = $this->db->prepare("SELECT display_name FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['display_name'];
        }
        
        return "Unknown User";
    }
    
    // Broadcast message to all subscribers of a note
    protected function broadcastToNote($noteId, $message, $exclude = []) {
        if (!isset($this->noteSubscriptions[$noteId])) {
            return;
        }
        
        $encoded = json_encode($message);
        
        foreach ($this->noteSubscriptions[$noteId] as $subscriber) {
            if (!in_array($subscriber, $exclude)) {
                $subscriber->send($encoded);
            }
        }
    }
    
    // Check and notify user of any new shared notes
    protected function notifyOfSharedNotes($userId) {
        if (!isset($this->userConnections[$userId])) {
            return;
        }
        
        // Get any recent shared notes (last 24 hours)
        $stmt = $this->db->prepare("
            SELECT n.id, n.title, u.display_name as owner_name, s.shared_at, s.can_edit
            FROM shared_notes s
            JOIN notes n ON s.note_id = n.id
            JOIN users u ON s.owner_id = u.id
            WHERE s.recipient_id = ? AND s.shared_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
            ORDER BY s.shared_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sharedNotes = [];
        while ($row = $result->fetch_assoc()) {
            $sharedNotes[] = $row;
        }
        
        if (count($sharedNotes) > 0) {
            $this->userConnections[$userId]->send(json_encode([
                'type' => 'new_shared_notes',
                'notes' => $sharedNotes,
                'count' => count($sharedNotes)
            ]));
            
            echo "Notified user {$userId} of " . count($sharedNotes) . " new shared notes\n";
        }
    }
}

// Create and run the server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new NoteServer()
        )
    ),
    8080, // Port
    '0.0.0.0' // Host (0.0.0.0 allows connections from any IP)
);

echo "WebSocket server started on port 8080\n";
$server->run();