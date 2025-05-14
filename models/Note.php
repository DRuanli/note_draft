<?php
class Note {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Get all notes for a user
    public function getUserNotes($user_id, $label_id = null, $search = '') {
        $query = "SELECT DISTINCT n.id, n.* FROM notes n WHERE n.user_id = ?";
        $params = [$user_id];
        $types = "i";
        
        // Add label filter if provided
        if ($label_id !== null) {
            $query .= " AND n.id IN (SELECT note_id FROM note_labels WHERE label_id = ?)";
            $params[] = $label_id;
            $types .= "i";
        }
        
        // Add search condition if provided
        if (!empty($search)) {
            $query .= " AND (n.title LIKE ? OR n.content LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= "ss";
        }
        
        // Order by pinned status first, then by pin time (most recent pins first), then by last modified date
        $query .= " ORDER BY n.is_pinned DESC, n.pin_time DESC, n.updated_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notes = [];
        while ($row = $result->fetch_assoc()) {
            // Get labels for each note
            $row['labels'] = $this->getNoteLabels($row['id']);
            
            // Get image count for each note
            $row['image_count'] = $this->getNoteImageCount($row['id']);
            
            // Add shared status to note data
            $row['is_shared'] = $this->isSharedWithOthers($row['id']);
            
            $notes[] = $row;
        }
        
        return $notes;
    }
    
    // Get a note by ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM notes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $note = $result->fetch_assoc();
            // Add shared status
            $note['is_shared'] = $this->isSharedWithOthers($id);
            return $note;
        }
        
        return null;
    }
    
    // Create a new note
    public function create($user_id, $title, $content) {
        $stmt = $this->db->prepare("INSERT INTO notes (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $title, $content);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'note_id' => $stmt->insert_id
            ];
        }
        
        return [
            'success' => false,
            'message' => $stmt->error
        ];
    }
    
    // Update a note
    public function update($id, $title, $content) {
        $stmt = $this->db->prepare("UPDATE notes SET title = ?, content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $id);
        
        if ($stmt->execute()) {
            return [
                'success' => true
            ];
        }
        
        return [
            'success' => false,
            'message' => $stmt->error
        ];
    }
    
    // Delete a note
    public function delete($id) {
        // First delete all images associated with the note
        $this->deleteAllImages($id);
        
        // Then delete the note
        $stmt = $this->db->prepare("DELETE FROM notes WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return [
                'success' => true
            ];
        }
        
        return [
            'success' => false,
            'message' => $stmt->error
        ];
    }
    
    // Toggle pin status
    public function togglePin($id) {
        // First get current pin status
        $stmt = $this->db->prepare("SELECT is_pinned FROM notes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $current_status = $row['is_pinned'];
        $new_status = $current_status ? 0 : 1;
        $pin_time = null;
        
        if ($new_status) {
            // If pinning, set current time as pin_time
            $pin_time = date('Y-m-d H:i:s');
            $stmt = $this->db->prepare("UPDATE notes SET is_pinned = ?, pin_time = ? WHERE id = ?");
            $stmt->bind_param("isi", $new_status, $pin_time, $id);
        } else {
            // If unpinning, set pin_time to NULL
            $stmt = $this->db->prepare("UPDATE notes SET is_pinned = ?, pin_time = NULL WHERE id = ?");
            $stmt->bind_param("ii", $new_status, $id);
        }
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'is_pinned' => (bool) $new_status,
                'message' => $new_status ? 'Note pinned successfully' : 'Note unpinned successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => $stmt->error
        ];
    }
    
    // Check if a note is shared with others
    public function isSharedWithOthers($note_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM shared_notes WHERE note_id = ?");
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] > 0;
    }
    
    // Label related methods
    
    // Get all labels for a note
    public function getNoteLabels($note_id) {
        $stmt = $this->db->prepare("
            SELECT l.id, l.name, l.user_id
            FROM labels l
            JOIN note_labels nl ON l.id = nl.label_id
            WHERE nl.note_id = ?
            ORDER BY l.name
        ");
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $labels = [];
        while ($row = $result->fetch_assoc()) {
            $labels[] = $row;
        }
        
        return $labels;
    }
    
    // Attach a label to a note
    public function attachLabel($note_id, $label_id) {
        // Check if already attached
        $check = $this->db->prepare("SELECT 1 FROM note_labels WHERE note_id = ? AND label_id = ?");
        $check->bind_param("ii", $note_id, $label_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            return [
                'success' => true,
                'message' => 'Label already attached'
            ];
        }
        
        // Attach label
        $stmt = $this->db->prepare("INSERT INTO note_labels (note_id, label_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $note_id, $label_id);
        
        if ($stmt->execute()) {
            return [
                'success' => true
            ];
        }
        
        return [
            'success' => false,
            'message' => $stmt->error
        ];
    }
    
    // Detach a label from a note
    public function detachLabel($note_id, $label_id) {
        $stmt = $this->db->prepare("DELETE FROM note_labels WHERE note_id = ? AND label_id = ?");
        $stmt->bind_param("ii", $note_id, $label_id);
        
        if ($stmt->execute()) {
            return [
                'success' => true
            ];
        }
        
        return [
            'success' => false,
            'message' => $stmt->error
        ];
    }
    
    // Detach all labels from a note
    public function detachAllLabels($note_id) {
        $stmt = $this->db->prepare("DELETE FROM note_labels WHERE note_id = ?");
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        
        return [
            'success' => true
        ];
    }
    
    // Image related methods
    
    // Add an image to a note
    public function addImage($note_id, $file_name, $file_path) {
        $stmt = $this->db->prepare("INSERT INTO images (note_id, file_name, file_path) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $note_id, $file_name, $file_path);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'image_id' => $stmt->insert_id
            ];
        }
        
        return [
            'success' => false,
            'message' => $stmt->error
        ];
    }
    
    // Get all images for a note
    public function getNoteImages($note_id) {
        $stmt = $this->db->prepare("SELECT * FROM images WHERE note_id = ? ORDER BY id");
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
        
        return $images;
    }
    
    // Get image count for a note
    public function getNoteImageCount($note_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM images WHERE note_id = ?");
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }
    
    // Delete an image
    public function deleteImage($image_id) {
        // First get the image to delete the file
        $stmt = $this->db->prepare("SELECT file_path FROM images WHERE id = ?");
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $image = $result->fetch_assoc();
            $file_path = UPLOADS_PATH . '/' . $image['file_path'];
            
            // Delete file if it exists
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Delete from database
            $stmt = $this->db->prepare("DELETE FROM images WHERE id = ?");
            $stmt->bind_param("i", $image_id);
            
            if ($stmt->execute()) {
                return [
                    'success' => true
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Image not found or could not be deleted'
        ];
    }
    
    // Delete all images for a note
    public function deleteAllImages($note_id) {
        // First get all images to delete the files
        $images = $this->getNoteImages($note_id);
        
        foreach ($images as $image) {
            $file_path = UPLOADS_PATH . '/' . $image['file_path'];
            
            // Delete file if it exists
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Delete from database
        $stmt = $this->db->prepare("DELETE FROM images WHERE note_id = ?");
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        
        return [
            'success' => true
        ];
    }
    
    // Password protection methods
    
    // Enable password protection for a note
    public function enablePasswordProtection($note_id, $password) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
        
        $stmt = $this->db->prepare("UPDATE notes SET is_password_protected = 1, note_password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $note_id);
        
        if ($stmt->execute()) {
            return [
                'success' => true
            ];
        }
        
        return [
            'success' => false,
            'message' => $stmt->error
        ];
    }
    
    // Disable password protection for a note
    public function disablePasswordProtection($note_id) {
        $stmt = $this->db->prepare("UPDATE notes SET is_password_protected = 0, note_password = NULL WHERE id = ?");
        $stmt->bind_param("i", $note_id);
        
        if ($stmt->execute()) {
            return [
                'success' => true
            ];
        }
        
        return [
            'success' => false,
            'message' => $stmt->error
        ];
    }
    
    // Verify a note password
    public function verifyNotePassword($note_id, $password) {
        $stmt = $this->db->prepare("SELECT note_password FROM notes WHERE id = ? AND is_password_protected = 1");
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            return password_verify($password, $row['note_password']);
        }
        
        return false;
    }
    
    // Change the password for a note
    public function changeNotePassword($note_id, $new_password) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
        
        $stmt = $this->db->prepare("UPDATE notes SET note_password = ? WHERE id = ? AND is_password_protected = 1");
        $stmt->bind_param("si", $hashed_password, $note_id);
        
        if ($stmt->execute()) {
            return [
                'success' => true
            ];
        }
        
        return [
            'success' => false,
            'message' => $stmt->error
        ];
    }
}