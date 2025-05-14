<?php
class OfflineController {
    // Display offline page
    public function index() {
        // Define the app name since we're not using the normal header
        define('APP_NAME', 'Note Management App');
        define('ASSETS_URL', BASE_URL . '/assets');
        
        include VIEWS_PATH . '/offline.php';
    }
}