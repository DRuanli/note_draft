<?php
// Database configuration

// Check if running on Heroku with JawsDB or ClearDB
$jawsdb_url = getenv('JAWSDB_URL');
$jawsdb_black_url = getenv('JAWSDB_BLACK_URL');

if ($jawsdb_url) {
    $url = parse_url($jawsdb_url);
    define('DB_HOST', $url['host']);
    define('DB_USER', $url['user']);
    define('DB_PASS', $url['pass']);
    define('DB_NAME', ltrim($url['path'], '/'));
} elseif ($jawsdb_black_url) {
    $url = parse_url($jawsdb_black_url);
    define('DB_HOST', $url['host']);
    define('DB_USER', $url['user']);
    define('DB_PASS', $url['pass']);
    define('DB_NAME', ltrim($url['path'], '/'));
} else {
    // Local database
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'note_management');
}

// Database connection helper function
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to ensure proper character encoding
    $conn->set_charset("utf8mb4");
    
    return $conn;
}


// Get database connection
function getDB() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = connectDB();
    }
    
    return $conn;
}

// Close database connection
function closeDB($conn) {
    if ($conn) {
        $conn->close();
    }
}