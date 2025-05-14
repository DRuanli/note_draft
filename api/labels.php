<?php
// Ensure we're in the API context
header('Content-Type: application/json');

// Get the action from URL
$action = isset(explode('/', $url)[2]) ? explode('/', $url)[2] : '';

// Include required model
require_once MODELS_PATH . '/Label.php';

// Initialize model
$label = new Label();

// Check if user is logged in
if (!Session::isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$user_id = Session::getUserId();

// Handle different API actions
switch ($action) {
    case 'list':
        // Get all labels for user
        $labels = $label->getUserLabels($user_id);
        echo json_encode(['success' => true, 'labels' => $labels]);
        break;
        
    case 'create':
        // Create a new label
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        
        if (empty($name)) {
            echo json_encode(['error' => 'Label name is required']);
            exit;
        }
        
        $result = $label->create($user_id, $name);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true, 
                'label_id' => $result['label_id'],
                'name' => $name
            ]);
        } else {
            echo json_encode(['error' => $result['message']]);
        }
        break;
        
    case 'update':
        // Update an existing label
        $label_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        
        if (empty($name)) {
            echo json_encode(['error' => 'Label name is required']);
            exit;
        }
        
        // Check if label exists and belongs to user
        $label_data = $label->getById($label_id);
        
        if (!$label_data || $label_data['user_id'] != $user_id) {
            echo json_encode(['error' => 'Label not found or access denied']);
            exit;
        }
        
        $result = $label->update($label_id, $name);
        
        if ($result['success']) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $result['message']]);
        }
        break;
        
    case 'delete':
        // Delete a label
        $label_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        // Check if label exists and belongs to user
        $label_data = $label->getById($label_id);
        
        if (!$label_data || $label_data['user_id'] != $user_id) {
            echo json_encode(['error' => 'Label not found or access denied']);
            exit;
        }
        
        $result = $label->delete($label_id);
        
        if ($result['success']) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $result['message']]);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Invalid API action']);
        break;
}