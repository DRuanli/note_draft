<?php
class Notification {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
        // Ensure notifications table exists
        $this->ensureNotificationsTable();
    }
    
    // Create notifications table if it doesn't exist
    private function ensureNotificationsTable() {
        // Check if table exists
        $result = $this->db->query("SHOW TABLES LIKE 'notifications'");
        if ($result->num_rows == 0) {
            // Create notifications table
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `notifications` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `user_id` INT NOT NULL,
                  `type` VARCHAR(50) NOT NULL,
                  `data` TEXT NOT NULL,
                  `is_read` TINYINT(1) DEFAULT 0,
                  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` TIMESTAMP NULL DEFAULT NULL,
                  `entity_id` INT NULL DEFAULT NULL,
                  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
                )
            ");
        }
    }
    
    // Get unread notifications for a user
    public function getUnreadNotifications($user_id) {
        // Check if table exists, create if not
        $this->ensureNotificationsTable();
        
        // For initial setup, return empty array if table is new
        $tableExists = $this->db->query("SHOW TABLES LIKE 'notifications'")->num_rows > 0;
        if (!$tableExists) {
            return [];
        }
        
        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? AND is_read = 0 
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $row['data'] = json_decode($row['data'], true);
            $notifications[] = $row;
        }
        
        return $notifications;
    }
    
    // Mark a notification as read
    public function markAsRead($notification_id, $user_id) {
        // Check if table exists, create if not
        $this->ensureNotificationsTable();
        
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE id = ? AND user_id = ?
        ");
        $stmt->bind_param("ii", $notification_id, $user_id);
        return $stmt->execute();
    }
    
    // Mark all notifications as read for a user
    public function markAllAsRead($user_id) {
        // Check if table exists, create if not
        $this->ensureNotificationsTable();
        
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }
    
    // Count unread notifications for a user
    public function countUnread($user_id) {
        // Check if table exists, create if not
        $this->ensureNotificationsTable();
        
        // For initial setup, return 0 if table is new
        $tableExists = $this->db->query("SHOW TABLES LIKE 'notifications'")->num_rows > 0;
        if (!$tableExists) {
            return 0;
        }
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM notifications
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }
    
    // Get all notifications for a user (for notifications page)
    public function getAllNotifications($user_id) {
        // Check if table exists, create if not
        $this->ensureNotificationsTable();
        
        // For initial setup, return empty array if table is new
        $tableExists = $this->db->query("SHOW TABLES LIKE 'notifications'")->num_rows > 0;
        if (!$tableExists) {
            return [];
        }
        
        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $row['data'] = json_decode($row['data'], true);
            $notifications[] = $row;
        }
        
        return $notifications;
    }
    
    // Create a notification
    public function create($user_id, $type, $data, $entity_id = null) {
        // Check if table exists, create if not
        $this->ensureNotificationsTable();
        
        // For share permission changes, check if we should update existing notification
        if ($type === 'share_permission_changed' && $entity_id) {
            // Check if we already have a recent notification (last 1 hour) for this entity
            $stmt = $this->db->prepare("
                SELECT id, data FROM notifications 
                WHERE user_id = ? AND type = ? AND entity_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->bind_param("isi", $user_id, $type, $entity_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $existing = $result->fetch_assoc();
                $existingId = $existing['id'];
                
                // Update the existing notification instead of creating a new one
                $serialized_data = json_encode($data);
                $updateStmt = $this->db->prepare("
                    UPDATE notifications 
                    SET data = ?, updated_at = NOW(), is_read = 0
                    WHERE id = ?
                ");
                $updateStmt->bind_param("si", $serialized_data, $existingId);
                return $updateStmt->execute();
            }
        }
        
        // For new shared note notifications, prevent duplicates
        if ($type === 'new_shared_note' && $entity_id) {
            // Check for an existing notification for this note
            $stmt = $this->db->prepare("
                SELECT id FROM notifications 
                WHERE user_id = ? AND type = ? AND entity_id = ?
            ");
            $stmt->bind_param("isi", $user_id, $type, $entity_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $existing = $result->fetch_assoc();
                $existingId = $existing['id'];
                
                // Update the existing notification
                $serialized_data = json_encode($data);
                $updateStmt = $this->db->prepare("
                    UPDATE notifications 
                    SET data = ?, updated_at = NOW(), is_read = 0
                    WHERE id = ?
                ");
                $updateStmt->bind_param("si", $serialized_data, $existingId);
                return $updateStmt->execute();
            }
        }
        
        // Serialize data
        $serialized_data = json_encode($data);
        
        // Insert new notification
        if ($entity_id) {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, type, data, is_read, created_at, entity_id) 
                VALUES (?, ?, ?, 0, NOW(), ?)
            ");
            $stmt->bind_param("issi", $user_id, $type, $serialized_data, $entity_id);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, type, data, is_read, created_at) 
                VALUES (?, ?, ?, 0, NOW())
            ");
            $stmt->bind_param("iss", $user_id, $type, $serialized_data);
        }
        
        return $stmt->execute();
    }
}