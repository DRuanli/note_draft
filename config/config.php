<?php
// Application configuration

// Base URL - automatically detect
$base_url = isset($_SERVER['HEROKU_APP_NAME']) 
    ? 'https://' . $_SERVER['HEROKU_APP_NAME'] . '.herokuapp.com' 
    : 'http://' . $_SERVER['HTTP_HOST'];

// App settings
define('APP_NAME', 'Note Management App');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'Asia/Ho_Chi_Minh');
date_default_timezone_set(TIMEZONE);

// Path definitions
define('ROOT_PATH', dirname(__DIR__));
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('UPLOADS_PATH', ROOT_PATH . '/uploads/note_images');
define('ASSETS_PATH', $base_url . '/assets');

// URL definitions
define('BASE_URL', $base_url);
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads/note_images');

// Security settings
define('SESSION_LIFETIME', 60 * 60 * 24); // 24 hours
define('PASSWORD_HASH_COST', 10); // bcrypt cost parameter
define('ACTIVATION_TOKEN_EXPIRY', 60 * 60 * 24 * 3); // 3 days
define('RESET_TOKEN_EXPIRY', 60 * 60 * 1); // 1 hour

// Feature flags
define('ENABLE_WEBSOCKETS', true);
define('ENABLE_OFFLINE_MODE', true);

// Error reporting in development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// In production, you should disable error display
// ini_set('display_errors', 0);
// ini_set('display_startup_errors', 0);
// error_reporting(E_ERROR | E_PARSE);