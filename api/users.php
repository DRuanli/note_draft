<?php
// Ensure we're in the API context
header('Content-Type: application/json');

// Get the action from URL
$action = isset(explode('/', $url)[2]) ? explode('/', $url)[2] : '';

// Include required model
require_once MODELS_PATH . '/User.php';

// Initialize model
$user = new User();

// Some actions require authentication
$public_actions = ['check-email', 'reset-password'];

if (!in_array($action, $public_actions) && !Session::isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Handle different API actions
switch ($action) {
    case 'check-email':
        // Check if email already exists (for registration)
        $email = isset($_GET['email']) ? trim($_GET['email']) : '';
        
        if (empty($email)) {
            echo json_encode(['error' => 'Email is required']);
            exit;
        }
        
        $exists = $user->emailExists($email);
        echo json_encode(['exists' => $exists]);
        break;
        
    case 'profile':
        // Get user profile
        $user_id = Session::getUserId();
        $profile = $user->getUserById($user_id);
        
        if ($profile) {
            // Remove sensitive data
            unset($profile['password']);
            unset($profile['activation_token']);
            unset($profile['reset_token']);
            unset($profile['reset_token_expiry']);
            
            echo json_encode(['success' => true, 'profile' => $profile]);
        } else {
            echo json_encode(['error' => 'User not found']);
        }
        break;
        
    case 'update-profile':
        // Update user profile
        $user_id = Session::getUserId();
        $display_name = isset($_POST['display_name']) ? trim($_POST['display_name']) : '';
        
        if (empty($display_name)) {
            echo json_encode(['error' => 'Display name is required']);
            exit;
        }
        
        // Update display name
        $result = $user->updateProfile($user_id, $display_name);
        
        if ($result['success']) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $result['message']]);
        }
        break;
        
    case 'change-password':
        // Change user password
        $user_id = Session::getUserId();
        $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        if (empty($current_password)) {
            echo json_encode(['error' => 'Current password is required']);
            exit;
        }
        
        if (empty($new_password)) {
            echo json_encode(['error' => 'New password is required']);
            exit;
        }
        
        if ($new_password !== $confirm_password) {
            echo json_encode(['error' => 'Passwords do not match']);
            exit;
        }
        
        // Change password
        $result = $user->changePassword($user_id, $current_password, $new_password);
        
        if ($result['success']) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $result['message']]);
        }
        break;
        
    case 'reset-password':
        // Request password reset
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        
        if (empty($email)) {
            echo json_encode(['error' => 'Email is required']);
            exit;
        }
        
        $result = $user->createPasswordResetToken($email);
        
        if ($result['success']) {
            // Send reset email
            sendPasswordResetEmail($email, $result['display_name'], $result['reset_token']);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $result['message']]);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Invalid API action']);
        break;
}