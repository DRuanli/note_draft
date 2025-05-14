<?php
/**
 * Security utility class
 */
class Security {
    /**
     * Generate a random token
     * 
     * @param int $length Token length
     * @return string Random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Hash a password using bcrypt
     * 
     * @param string $password Password to hash
     * @return string Hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
    }
    
    /**
     * Verify a password against a hash
     * 
     * @param string $password Password to verify
     * @param string $hash Hash to check against
     * @return bool Whether password matches hash
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Clean/sanitize input data
     * 
     * @param string $data Data to sanitize
     * @return string Sanitized data
     */
    public static function sanitize($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    /**
     * Generate CSRF token and store in session
     * 
     * @param string $formName Form name for specific tokens
     * @return string CSRF token
     */
    public static function generateCsrfToken($formName = 'default') {
        $token = self::generateToken();
        Session::set('csrf_' . $formName, $token);
        return $token;
    }
    
    /**
     * Verify CSRF token
     * 
     * @param string $token Token to verify
     * @param string $formName Form name for specific tokens
     * @return bool Whether token is valid
     */
    public static function verifyCsrfToken($token, $formName = 'default') {
        $storedToken = Session::get('csrf_' . $formName);
        
        if (!$storedToken || $token !== $storedToken) {
            return false;
        }
        
        // Remove token after verification (one-time use)
        Session::remove('csrf_' . $formName);
        return true;
    }
}