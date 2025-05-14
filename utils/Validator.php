<?php
class Validator {
    /**
     * Array to store validation errors
     */
    private $errors = [];
    
    /**
     * Get all validation errors
     * 
     * @return array Validation errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Check if validation has errors
     * 
     * @return bool True if has errors, false otherwise
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Validate email
     * 
     * @param string $email Email to validate
     * @param string $field Field name for error message
     * @return bool Validation result
     */
    public function validateEmail($email, $field = 'email') {
        if (empty($email)) {
            $this->errors[$field] = 'Email address is required';
            return false;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Please enter a valid email address';
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate required field
     * 
     * @param string $value Value to check
     * @param string $field Field name for error message
     * @param string $label Field label for error message
     * @return bool Validation result
     */
    public function required($value, $field, $label = null) {
        $fieldName = $label ?? ucfirst(str_replace('_', ' ', $field));
        
        if (empty($value)) {
            $this->errors[$field] = "$fieldName is required";
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate string length
     * 
     * @param string $value Value to check
     * @param string $field Field name for error message
     * @param int $min Minimum length
     * @param int $max Maximum length
     * @param string $label Field label for error message
     * @return bool Validation result
     */
    public function length($value, $field, $min = null, $max = null, $label = null) {
        $fieldName = $label ?? ucfirst(str_replace('_', ' ', $field));
        $length = strlen($value);
        
        if ($min !== null && $length < $min) {
            $this->errors[$field] = "$fieldName must be at least $min characters";
            return false;
        }
        
        if ($max !== null && $length > $max) {
            $this->errors[$field] = "$fieldName cannot exceed $max characters";
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate password
     * 
     * @param string $password Password to validate
     * @param string $field Field name for error message
     * @param int $minLength Minimum password length
     * @return bool Validation result
     */
    public function validatePassword($password, $field = 'password', $minLength = 8) {
        if (empty($password)) {
            $this->errors[$field] = 'Password is required';
            return false;
        }
        
        if (strlen($password) < $minLength) {
            $this->errors[$field] = "Password must be at least $minLength characters";
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate password confirmation
     * 
     * @param string $password Password
     * @param string $confirmation Password confirmation
     * @param string $field Field name for error message
     * @return bool Validation result
     */
    public function passwordsMatch($password, $confirmation, $field = 'confirm_password') {
        if ($password !== $confirmation) {
            $this->errors[$field] = 'Passwords do not match';
            return false;
        }
        
        return true;
    }
}