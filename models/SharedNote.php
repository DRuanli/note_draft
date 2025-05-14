<?php
class SharedNote {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
        require_once ROOT_PATH . '/utils/Mailer.php';
    }
    
    // Share a note with another user
    public function shareNote($note_id, $owner_id, $recipient_email, $can_edit = false) {
        // First, check if the note exists and belongs to the owner
        $stmt = $this->db->prepare("SELECT id, title FROM notes WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $note_id, $owner_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return [
                'success' => false,
                'message' => 'Note not found or you are not the owner'
            ];
        }
        
        $note = $result->fetch_assoc();
        
        // Find the recipient user by email
        $stmt = $this->db->prepare("SELECT id, display_name, email FROM users WHERE email = ?");
        $stmt->bind_param("s", $recipient_email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return [
                'success' => false,
                'message' => 'Recipient email not found. Please ensure you are sharing with a registered user.'
            ];
        }
        
        $recipient = $result->fetch_assoc();
        $recipient_id = $recipient['id'];
        $recipient_name = $recipient['display_name'];
        
        // Cannot share with yourself
        if ($recipient_id == $owner_id) {
            return [
                'success' => false,
                'message' => 'You cannot share a note with yourself'
            ];
        }
        
        // Check if already shared with this user
        $stmt = $this->db->prepare("SELECT id FROM shared_notes WHERE note_id = ? AND recipient_id = ?");
        $stmt->bind_param("ii", $note_id, $recipient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update sharing permissions
            $share = $result->fetch_assoc();
            $share_id = $share['id'];
            
            $stmt = $this->db->prepare("UPDATE shared_notes SET can_edit = ? WHERE id = ?");
            $can_edit_int = $can_edit ? 1 : 0;
            $stmt->bind_param("ii", $can_edit_int, $share_id);
            
            if ($stmt->execute()) {
                // Get owner's name
                $stmt = $this->db->prepare("SELECT display_name FROM users WHERE id = ?");
                $stmt->bind_param("i", $owner_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $owner = $result->fetch_assoc();
                
                // Send email notification about permission change
                sendSharePermissionChangedEmail(
                    $recipient['email'],
                    $recipient_name,
                    $owner['display_name'],
                    $note['title'],
                    $can_edit ? 'edit' : 'view'
                );
                
                // Create a notification in database
                $this->createNotification($recipient_id, 'share_permission_changed', [
                    'note_id' => $note_id,
                    'note_title' => $note['title'],
                    'owner_name' => $owner['display_name'],
                    'permission' => $can_edit ? 'edit' : 'view-only'
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Sharing permissions updated successfully',
                    'share_id' => $share_id
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update sharing permissions: ' . $stmt->error
                ];
            }
        }
        
        // Insert new share
        $stmt = $this->db->prepare("INSERT INTO shared_notes (note_id, owner_id, recipient_id, can_edit) VALUES (?, ?, ?, ?)");
        $can_edit_int = $can_edit ? 1 : 0;
        $stmt->bind_param("iiii", $note_id, $owner_id, $recipient_id, $can_edit_int);
        
        if ($stmt->execute()) {
            $share_id = $stmt->insert_id;
            
            // Get owner's name
            $stmt = $this->db->prepare("SELECT display_name FROM users WHERE id = ?");
            $stmt->bind_param("i", $owner_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $owner = $result->fetch_assoc();
            
            // Send email notification
            sendNoteSharedEmail(
                $recipient['email'],
                $recipient_name,
                $owner['display_name'],
                $note['title'],
                $can_edit ? 'edit' : 'view'
            );
            
            // Create a notification in database
            $this->createNotification($recipient_id, 'new_shared_note', [
                'note_id' => $note_id,
                'note_title' => $note['title'], 
                'owner_name' => $owner['display_name'],
                'permission' => $can_edit ? 'edit' : 'view-only'
            ]);
            
            return [
                'success' => true,
                'share_id' => $share_id
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to share note: ' . $stmt->error
        ];
    }
    
    // Get all shares for a note
    public function getNoteShares($note_id) {
        $stmt = $this->db->prepare("
            SELECT s.*, u.email as recipient_email, u.display_name as recipient_name
            FROM shared_notes s
            JOIN users u ON s.recipient_id = u.id
            WHERE s.note_id = ?
            ORDER BY s.shared_at DESC
        ");
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $shares = [];
        while ($row = $result->fetch_assoc()) {
            $shares[] = $row;
        }
        
        return $shares;
    }

    private function createNotification($user_id, $type, $data, $entity_id = null) {
        require_once MODELS_PATH . '/Notification.php';
        $notification = new Notification();
        return $notification->create($user_id, $type, $data, $entity_id);
    }

    private function ensureNotificationsTable() {
        // Check if table exists
        $result = $this->db->query("SHOW TABLES LIKE 'notifications'");
        if ($result->num_rows == 0) {
            // Create notifications table
            $this->db->query("
                CREATE TABLE notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    type VARCHAR(50) NOT NULL,
                    data TEXT NOT NULL,
                    is_read TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
        }
    }
    
    
    // Remove sharing
    public function removeShare($share_id, $owner_id) {
        // First check if the share exists and user is the owner
        $stmt = $this->db->prepare("
            SELECT s.id, s.note_id, s.recipient_id, n.title, u.email, u.display_name 
            FROM shared_notes s
            JOIN notes n ON s.note_id = n.id
            JOIN users u ON s.recipient_id = u.id
            WHERE s.id = ? AND s.owner_id = ?
        ");
        $stmt->bind_param("ii", $share_id, $owner_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return [
                'success' => false,
                'message' => 'Share not found or you are not the owner'
            ];
        }
        
        $share = $result->fetch_assoc();
        
        // Remove the share
        $stmt = $this->db->prepare("DELETE FROM shared_notes WHERE id = ?");
        $stmt->bind_param("i", $share_id);
        
        if ($stmt->execute()) {
            // Get owner's name
            $stmt = $this->db->prepare("SELECT display_name FROM users WHERE id = ?");
            $stmt->bind_param("i", $owner_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $owner = $result->fetch_assoc();
            
            // Optionally notify user that sharing was removed
            // sendShareRemovedEmail($share['email'], $share['display_name'], $owner['display_name'], $share['title']);
            
            return [
                'success' => true
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to remove share: ' . $stmt->error
        ];
    }
    
    // Get notes shared with user
    public function getNotesSharedWithUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT n.*, u.display_name as owner_name, u.email as owner_email, s.shared_at, s.can_edit  
            FROM notes n
            JOIN shared_notes s ON n.id = s.note_id
            JOIN users u ON s.owner_id = u.id
            WHERE s.recipient_id = ?
            ORDER BY s.shared_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notes = [];
        while ($row = $result->fetch_assoc()) {
            $notes[] = $row;
        }
        
        return $notes;
    }
    
    // Check if a note is shared with a user
    public function isSharedWithUser($note_id, $user_id) {
        $stmt = $this->db->prepare("SELECT 1 FROM shared_notes WHERE note_id = ? AND recipient_id = ?");
        $stmt->bind_param("ii", $note_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    // Check if a user can edit a shared note
    public function canEditSharedNote($note_id, $user_id) {
        // First check if user is the owner
        $stmt = $this->db->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $note_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return true; // User is the owner, can always edit
        }
        
        // Then check if shared with edit permissions
        $stmt = $this->db->prepare("SELECT can_edit FROM shared_notes WHERE note_id = ? AND recipient_id = ?");
        $stmt->bind_param("ii", $note_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return (bool) $row['can_edit'];
        }
        
        return false;
    }    

    // Update sharing permissions
    public function updateSharePermissions($share_id, $owner_id, $can_edit) {
        try {
            // First check if the share exists and user is the owner
            $stmt = $this->db->prepare("
                SELECT s.note_id, s.recipient_id, n.title, u.email, u.display_name 
                FROM shared_notes s
                JOIN notes n ON s.note_id = n.id
                JOIN users u ON s.recipient_id = u.id
                WHERE s.id = ? AND n.user_id = ?
            ");
            $stmt->bind_param("ii", $share_id, $owner_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Share not found or you are not the owner'
                ];
            }
            
            $share = $result->fetch_assoc();
            $can_edit_int = $can_edit ? 1 : 0;
            
            // Check current permission to avoid creating unnecessary notifications
            $checkStmt = $this->db->prepare("SELECT can_edit FROM shared_notes WHERE id = ?");
            $checkStmt->bind_param("i", $share_id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $currentSetting = $checkResult->fetch_assoc();
            
            // Only update if permission is actually changing
            if ((int)$currentSetting['can_edit'] !== $can_edit_int) {
                // Update the permissions
                $stmt = $this->db->prepare("UPDATE shared_notes SET can_edit = ? WHERE id = ?");
                $stmt->bind_param("ii", $can_edit_int, $share_id);
                
                if ($stmt->execute()) {
                    // Get owner information for notification
                    $stmt = $this->db->prepare("SELECT display_name FROM users WHERE id = ?");
                    $stmt->bind_param("i", $owner_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $owner = $result->fetch_assoc();
                    
                    // Send email notification about permission change
                    sendSharePermissionChangedEmail(
                        $share['email'],
                        $share['display_name'],
                        $owner['display_name'],
                        $share['title'],
                        $can_edit ? 'edit' : 'view'
                    );
                    
                    // Create notification for recipient
                    $this->createNotification($share['recipient_id'], 'share_permission_changed', [
                        'note_id' => $share['note_id'],
                        'note_title' => $share['title'],
                        'owner_name' => $owner['display_name'],
                        'permission' => $can_edit ? 'edit' : 'view-only'
                    ], $share['note_id']);
                    
                    return [
                        'success' => true,
                        'message' => 'Share permissions updated successfully'
                    ];
                }
            } else {
                // Permission not changed, but still return success
                return [
                    'success' => true,
                    'message' => 'Share permissions already set to this level'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to update permissions: ' . $stmt->error
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }
}