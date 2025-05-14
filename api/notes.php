<?php
// Ensure we're in the API context
header('Content-Type: application/json');

// Get the action from URL
$action = isset(explode('/', $url)[2]) ? explode('/', $url)[2] : '';

// Include required models
require_once MODELS_PATH . '/Note.php';
require_once MODELS_PATH . '/Label.php';
require_once MODELS_PATH . '/SharedNote.php';

// Initialize models
$note = new Note();
$label = new Label();
$sharedNote = new SharedNote();

// Check if user is logged in
if (!Session::isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$user_id = Session::getUserId();

// Handle different API actions
switch ($action) {
    case 'get':
        // Get note by ID
        $note_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $note_data = $note->getById($note_id);
        
        if ($note_data && ($note_data['user_id'] == $user_id || $sharedNote->isSharedWithUser($note_id, $user_id))) {
            // Check if note is password protected
            if (isset($note_data['is_password_protected']) && $note_data['is_password_protected']) {
                // Check if user has verified the password in this session
                $verified_notes = Session::get('verified_notes', []);
                if (!in_array($note_id, $verified_notes)) {
                    echo json_encode([
                        'error' => 'Password verification required',
                        'requires_password' => true,
                        'note_id' => $note_id
                    ]);
                    exit;
                }
            }
            
            // Add labels
            $note_data['labels'] = $note->getNoteLabels($note_id);
            echo json_encode(['success' => true, 'note' => $note_data]);
        } else {
            echo json_encode(['error' => 'Note not found or access denied']);
        }
        break;
        
    case 'list':
        // Get list of notes
        $label_filter = isset($_GET['label']) ? intval($_GET['label']) : null;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        $notes = $note->getUserNotes($user_id, $label_filter, $search);
        echo json_encode(['success' => true, 'notes' => $notes]);
        break;
        
    case 'create':
        // Create a new note
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        $label_ids = isset($_POST['labels']) && is_array($_POST['labels']) ? $_POST['labels'] : [];
        
        if (empty($title)) {
            echo json_encode(['error' => 'Title is required']);
            exit;
        }
        
        $result = $note->create($user_id, $title, $content);
        
        if ($result['success']) {
            $note_id = $result['note_id'];
            
            // Attach labels
            if (!empty($label_ids)) {
                foreach ($label_ids as $label_id) {
                    $note->attachLabel($note_id, $label_id);
                }
            }
            
            echo json_encode(['success' => true, 'note_id' => $note_id]);
        } else {
            echo json_encode(['error' => $result['message']]);
        }
        break;
        
    case 'update':
        // Update an existing note
        $note_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        $label_ids = isset($_POST['labels']) && is_array($_POST['labels']) ? $_POST['labels'] : [];
        
        // Check if note exists and user has access
        $note_data = $note->getById($note_id);
        
        if (!$note_data || ($note_data['user_id'] != $user_id && !$sharedNote->canEditSharedNote($note_id, $user_id))) {
            echo json_encode(['error' => 'Note not found or access denied']);
            exit;
        }
        
        // Check if note is password protected
        if (isset($note_data['is_password_protected']) && $note_data['is_password_protected']) {
            // Check if user has verified the password in this session
            $verified_notes = Session::get('verified_notes', []);
            if (!in_array($note_id, $verified_notes)) {
                echo json_encode([
                    'error' => 'Password verification required',
                    'requires_password' => true,
                    'note_id' => $note_id
                ]);
                exit;
            }
        }
        
        if (empty($title)) {
            echo json_encode(['error' => 'Title is required']);
            exit;
        }
        
        $result = $note->update($note_id, $title, $content);
        
        if ($result['success']) {
            // Update labels
            $note->detachAllLabels($note_id);
            if (!empty($label_ids)) {
                foreach ($label_ids as $label_id) {
                    $note->attachLabel($note_id, $label_id);
                }
            }
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $result['message']]);
        }
        break;
        
    case 'delete':
        // Delete a note
        $note_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        // Check if note exists and user has access
        $note_data = $note->getById($note_id);
        
        if (!$note_data || $note_data['user_id'] != $user_id) {
            echo json_encode(['error' => 'Note not found or access denied']);
            exit;
        }
        
        // Check if note is password protected
        if (isset($note_data['is_password_protected']) && $note_data['is_password_protected']) {
            // Check if user has verified the password in this session
            $verified_notes = Session::get('verified_notes', []);
            if (!in_array($note_id, $verified_notes)) {
                echo json_encode([
                    'error' => 'Password verification required',
                    'requires_password' => true,
                    'note_id' => $note_id
                ]);
                exit;
            }
        }
        
        $result = $note->delete($note_id);
        
        if ($result['success']) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $result['message']]);
        }
        break;
        
    case 'toggle-pin':
        // Toggle pin status
        $note_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        // Check if note exists and user has access
        $note_data = $note->getById($note_id);
        
        if (!$note_data || $note_data['user_id'] != $user_id) {
            echo json_encode(['error' => 'Note not found or access denied']);
            exit;
        }
        
        // Check if note is password protected
        if (isset($note_data['is_password_protected']) && $note_data['is_password_protected']) {
            // Check if user has verified the password in this session
            $verified_notes = Session::get('verified_notes', []);
            if (!in_array($note_id, $verified_notes)) {
                echo json_encode([
                    'error' => 'Password verification required',
                    'requires_password' => true,
                    'note_id' => $note_id
                ]);
                exit;
            }
        }
        
        // Toggle pin status
        $result = $note->togglePin($note_id);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true, 
                'is_pinned' => $result['is_pinned'],
                'message' => $result['message']
            ]);
        } else {
            echo json_encode(['error' => $result['message']]);
        }
        break;
        
    case 'verify-password':
        // Verify password for a protected note
        $note_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Check if note exists and user has access
        $note_data = $note->getById($note_id);
        
        if (!$note_data || ($note_data['user_id'] != $user_id && !$sharedNote->isSharedWithUser($note_id, $user_id))) {
            echo json_encode(['error' => 'Note not found or access denied']);
            exit;
        }
        
        // Check if note is actually password protected
        if (!isset($note_data['is_password_protected']) || !$note_data['is_password_protected']) {
            echo json_encode(['success' => true, 'message' => 'Note is not password protected']);
            exit;
        }
        
        // Verify password
        if (empty($password)) {
            echo json_encode(['error' => 'Password is required']);
            exit;
        }
        
        if ($note->verifyNotePassword($note_id, $password)) {
            // Password verified - store in session
            $verified_notes = Session::get('verified_notes', []);
            $verified_notes[] = $note_id;
            Session::set('verified_notes', $verified_notes);
            
            echo json_encode(['success' => true, 'message' => 'Password verified successfully']);
        } else {
            echo json_encode(['error' => 'Incorrect password']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Invalid API action']);
        break;
}