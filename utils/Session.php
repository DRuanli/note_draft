<?php
class Session {
    // Start or resume session
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure secure session
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);
            
            // Cookie settings
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'domain' => '',
                'secure' => false, // Set to true if using HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            session_start();
            
            // Regenerate session ID periodically to prevent fixation
            if (!isset($_SESSION['last_regeneration'])) {
                self::regenerateId();
            } else {
                // Regenerate session ID every 30 minutes
                $regeneration_time = $_SESSION['last_regeneration'] + (30 * 60);
                if (time() > $regeneration_time) {
                    self::regenerateId();
                }
            }
        }
    }
    
    // Regenerate session ID
    public static function regenerateId() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Set session variable
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    // Get session variable
    public static function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    // Check if session variable exists
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    // Remove session variable
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }
    
    // Clear all session data
    public static function clear() {
        session_unset();
    }
    
    // Destroy session
    public static function destroy() {
        self::clear();
        session_destroy();
        
        // Clear session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"],
                $params["domain"], $params["secure"], $params["httponly"]);
        }
    }
    
    // Set flash message (available only for the next request)
    public static function setFlash($key, $value) {
        $_SESSION['flash'][$key] = $value;
    }
    
    // Get flash message and remove it
    public static function getFlash($key, $default = null) {
        if (isset($_SESSION['flash'][$key])) {
            $value = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $value;
        }
        return $default;
    }
    
    // Check if flash message exists
    public static function hasFlash($key) {
        return isset($_SESSION['flash'][$key]);
    }
    
    // Check if user is logged in
    public static function isLoggedIn() {
        return self::has('user_id');
    }
    
    // Get current user ID
    public static function getUserId() {
        return self::get('user_id');
    }
    
    // Set authentication
    public static function setAuth($user_id, $email, $display_name) {
        self::set('user_id', $user_id);
        self::set('user_email', $email);
        self::set('user_display_name', $display_name);
        self::set('is_authenticated', true);
        self::set('auth_time', time());
    }
    
    // Clear authentication
    public static function clearAuth() {
        self::remove('user_id');
        self::remove('user_email');
        self::remove('user_display_name');
        self::remove('is_authenticated');
        self::remove('auth_time');
    }
}