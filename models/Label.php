<?php
class Label {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Get all labels for a user
    public function getUserLabels($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM labels WHERE user_id = ? ORDER BY name");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $labels = [];
        while ($row = $result->fetch_assoc()) {
            $labels[] = $row;
        }
        
        return $labels;
    }
    
    // Get a label by ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM labels WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    // Check if label name already exists for user
    public function nameExists($user_id, $name, $exclude_id = null) {
        $query = "SELECT id FROM labels WHERE user_id = ? AND name = ?";
        $params = [$user_id, $name];
        $types = "is";
        
        if ($exclude_id !== null) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
            $types .= "i";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    // Create a new label
    public function create($user_id, $name) {
        // Check if name already exists
        if ($this->nameExists($user_id, $name)) {
            return [
                'success' => false,
                'message' => 'Label name already exists'
            ];
        }
        
        $stmt = $this->db->prepare("INSERT INTO labels (user_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $name);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'label_id' => $stmt->insert_id
            ];
        }
        
        return [
            'success' => false,
            'message' => $stmt->error
        ];
    }
    
    // Update a label
    public function update($id, $name) {
        // Get the label to check user_id
        $label = $this->getById($id);
        
        if (!$label) {
            return [
                'success' => false,
                'message' => 'Label not found'
            ];
        }
        
        // Check if name already exists
        if ($this->nameExists($label['user_id'], $name, $id)) {
            return [
                'success' => false,
                'message' => 'Label name already exists'
            ];
        }
        
        $stmt = $this->db->prepare("UPDATE labels SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        
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
    
    // Delete a label
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM labels WHERE id = ?");
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
    
    // Get note count for a label
    public function getNoteCount($label_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM note_labels WHERE label_id = ?");
        $stmt->bind_param("i", $label_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }
}