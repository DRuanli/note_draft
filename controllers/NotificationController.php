<?php
require_once MODELS_PATH . '/Notification.php';

class NotificationController {
    private $notification;
    
    public function __construct() {
        $this->notification = new Notification();
    }
    
    // Display all notifications
    public function index() {
        $user_id = Session::getUserId();
        
        // Get all notifications for the user
        $notifications = $this->notification->getAllNotifications($user_id);
        
        // Set page data
        $data = [
            'pageTitle' => 'My Notifications',
            'pageStyles' => ['notifications'],
            'notifications' => $notifications
        ];
        
        // Load view
        include VIEWS_PATH . '/components/header.php';
        include VIEWS_PATH . '/notifications/index.php';
        include VIEWS_PATH . '/components/footer.php';
    }
    
    // Mark notification as read
    public function markRead($id) {
        $user_id = Session::getUserId();
        
        // Mark as read
        $this->notification->markAsRead($id, $user_id);
        
        // If referrer exists, redirect back to it
        if (isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: ' . BASE_URL . '/notifications');
        }
        exit;
    }
    
    // Mark all notifications as read
    public function markAllRead() {
        $user_id = Session::getUserId();
        
        // Mark all as read
        $this->notification->markAllAsRead($user_id);
        
        // If referrer exists, redirect back to it
        if (isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: ' . BASE_URL . '/notifications');
        }
        exit;
    }
}