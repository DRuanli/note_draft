<?php
require_once MODELS_PATH . '/Label.php';

class LabelController {
    private $label;
    
    public function __construct() {
        $this->label = new Label();
    }
    
    // List all labels
    public function index() {
        $user_id = Session::getUserId();
        
        // Get all labels for the user
        $labels = $this->label->getUserLabels($user_id);
        
        // Deduplicate labels by ID (preventive measure)
        $uniqueLabels = [];
        $seenIds = [];
        
        foreach ($labels as $label) {
            if (!in_array($label['id'], $seenIds)) {
                $seenIds[] = $label['id'];
                $uniqueLabels[] = $label;
            }
        }
        
        // Replace with deduplicated labels
        $labels = $uniqueLabels;
        
        // Count notes for each label
        foreach ($labels as &$label) {
            $label['note_count'] = $this->label->getNoteCount($label['id']);
        }
        // Important: unset reference to prevent accidental modification
        unset($label);
        
        // Set page data
        $data = [
            'pageTitle' => 'My Labels',
            'pageStyles' => ['notes'],
            'pageScripts' => ['labels'],
            'labels' => $labels
        ];
        
        // Load view
        include VIEWS_PATH . '/components/header.php';
        include VIEWS_PATH . '/labels/manage.php';
        include VIEWS_PATH . '/components/footer.php';
    }
    
    // Process AJAX label requests
    public function processRequest() {
        // Check if AJAX request
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'AJAX requests only']);
            exit;
        }
        
        $user_id = Session::getUserId();
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
                $this->createLabel($user_id);
                break;
                
            case 'update':
                $this->updateLabel($user_id);
                break;
                
            case 'delete':
                $this->deleteLabel($user_id);
                break;
                
            default:
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid action'
                ]);
                break;
        }
    }
    
    // Create a new label (AJAX)
    protected function createLabel($user_id) {
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Label name is required'
            ]);
            return;
        }
        
        $result = $this->label->create($user_id, $name);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = Session::getUserId();
            $name = trim($_POST['name'] ?? '');
            
            if (empty($name)) {
                Session::setFlash('error', 'Label name is required');
                header('Location: ' . BASE_URL . '/labels');
                exit;
            }
            
            $result = $this->label->create($user_id, $name);
            
            if ($result['success']) {
                Session::setFlash('success', 'Label created successfully');
            } else {
                Session::setFlash('error', $result['message']);
            }
            
            header('Location: ' . BASE_URL . '/labels');
            exit;
        }
        
        // If not a POST request, redirect to labels page
        header('Location: ' . BASE_URL . '/labels');
        exit;
    }
    
    // Update a label (AJAX)
    private function updateLabel($user_id) {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Label name is required'
            ]);
            return;
        }
        
        // Check if label belongs to user
        $label = $this->label->getById($id);
        if (!$label || $label['user_id'] != $user_id) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Label not found or access denied'
            ]);
            return;
        }
        
        $result = $this->label->update($id, $name);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
    // Delete a label (AJAX)
    private function deleteLabel($user_id) {
        $id = intval($_POST['id'] ?? 0);
        
        // Check if label belongs to user
        $label = $this->label->getById($id);
        if (!$label || $label['user_id'] != $user_id) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Label not found or access denied'
            ]);
            return;
        }
        
        $result = $this->label->delete($id);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
}