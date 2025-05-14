<?php
// Ensure we're in the API context
header('Content-Type: application/json');

// Get the action from URL
$action = isset(explode('/', $url)[2]) ? explode('/', $url)[2] : '';

// Include required model
require_once MODELS_PATH . '/Notification.php';

// Initialize model
$notification = new Notification();

// Check if user is logged in
if (!Session::isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$user_id = Session::getUserId();

// Handle different API actions
switch ($action) {
    case 'check':
        // Check for new notifications
        $unread = $notification->getUnreadNotifications($user_id);
        $count = count($unread);
        
        // Get any notifications newer than the last check time
        $last_check = Session::get('last_notification_check');
        $new_notifications = [];
        
        if ($last_check) {
            foreach ($unread as $n) {
                if (strtotime($n['created_at']) > strtotime($last_check)) {
                    switch ($n['type']) {
                        case 'new_shared_note':
                            $new_notifications[] = [
                                'id' => $n['id'],
                                'type' => $n['type'],
                                'message' => $n['data']['owner_name'] . ' shared a note with you',
                                'created_at' => $n['created_at']
                            ];
                            break;
                        case 'share_permission_changed':
                            $new_notifications[] = [
                                'id' => $n['id'],
                                'type' => $n['type'],
                                'message' => 'Your access to "' . $n['data']['note_title'] . '" has been updated',
                                'created_at' => $n['created_at']
                            ];
                            break;
                    }
                }
            }
        }
        
        // Update last check time
        Session::set('last_notification_check', date('Y-m-d H:i:s'));
        
        echo json_encode([
            'success' => true,
            'count' => $count,
            'new_notifications' => $new_notifications
        ]);
        break;
        
    case 'mark-read':
        // Mark a notification as read via AJAX
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if ($id > 0) {
            $success = $notification->markAsRead($id, $user_id);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
        }
        break;
        
    case 'mark-all-read':
        // Mark all notifications as read via AJAX
        $success = $notification->markAllAsRead($user_id);
        echo json_encode(['success' => $success]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid API action']);
        break;
}