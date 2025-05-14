<?php
require_once MODELS_PATH . '/User.php';
require_once 'utils/Mailer.php';

class AuthController {
    private $user;
    
    public function __construct() {
        $this->user = new User();
    }
    
    // Display and process login form
    public function login() {
        // Check if already logged in
        if (Session::isLoggedIn()) {
            // Don't redirect if already on BASE_URL to prevent redirect loops
            $current_url = $_SERVER['REQUEST_URI'];
            $base_path = parse_url(BASE_URL, PHP_URL_PATH);
            
            if ($current_url !== $base_path) {
                header('Location: ' . BASE_URL);
                exit;
            }
        }
        
        $data = [
            'title' => 'Login',
            'email' => '',
            'errors' => []
        ];
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            // Validate input
            if (empty($email)) {
                $data['errors']['email'] = 'Email is required';
            }
            
            if (empty($password)) {
                $data['errors']['password'] = 'Password is required';
            }
            
            // If no errors, try to login
            if (empty($data['errors'])) {
                $result = $this->user->login($email, $password);
                
                if ($result['success']) {
                    // Set auth session
                    Session::setAuth($result['user_id'], $email, $result['display_name']);
                    
                    // Extend session if "remember me" is checked
                    if ($remember) {
                        ini_set('session.cookie_lifetime', 30 * 24 * 60 * 60); // 30 days
                    }
                    
                    // Redirect to intended page or home
                    $redirect_url = Session::get('redirect_url', BASE_URL);
                    Session::remove('redirect_url');
                    
                    header('Location: ' . $redirect_url);
                    exit;
                } else {
                    $data['errors']['general'] = $result['message'];
                }
            }
            
            // Keep email value for form
            $data['email'] = $email;
        }
        
        // Load view
        include VIEWS_PATH . '/auth/login.php';
    }
    
    // Display and process registration form
    public function register() {
        // Check if already logged in
        if (Session::isLoggedIn()) {
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $data = [
            'title' => 'Register',
            'email' => '',
            'display_name' => '',
            'errors' => []
        ];
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $email = trim($_POST['email'] ?? '');
            $display_name = trim($_POST['display_name'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validate input
            if (empty($email)) {
                $data['errors']['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data['errors']['email'] = 'Invalid email format';
            }
            
            if (empty($display_name)) {
                $data['errors']['display_name'] = 'Display name is required';
            }
            
            if (empty($password)) {
                $data['errors']['password'] = 'Password is required';
            } elseif (strlen($password) < 8) {
                $data['errors']['password'] = 'Password must be at least 8 characters';
            }
            
            if ($password !== $confirm_password) {
                $data['errors']['confirm_password'] = 'Passwords do not match';
            }
            
            // If no errors, try to register
            if (empty($data['errors'])) {
                $result = $this->user->register($email, $display_name, $password);
                
                if ($result['success']) {
                    // Send activation email
                    $send_result = sendActivationEmail($email, $display_name, $result['activation_token']);
                    
                    // Set auth session (auto-login)
                    Session::setAuth($result['user_id'], $email, $display_name);
                    
                    // Set flash message
                    Session::setFlash('success', 'Registration successful! Please check your email to activate your account.');
                    
                    // Redirect to home
                    header('Location: ' . BASE_URL);
                    exit;
                } else {
                    $data['errors']['general'] = $result['message'];
                }
            }
            
            // Keep form values
            $data['email'] = $email;
            $data['display_name'] = $display_name;
        }
        
        // Load view
        include VIEWS_PATH . '/auth/register.php';
    }

    // Resend activation email
    public function resendActivation() {
        // Check if user is logged in
        if (!Session::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $user_id = Session::getUserId();
        $email = Session::get('user_email');
        
        // Regenerate activation token
        $result = $this->user->regenerateActivationToken($email);
        
        if ($result['success']) {
            // Send activation email
            $send_result = sendActivationEmail($email, $result['display_name'], $result['activation_token']);
            
            // Set flash message
            Session::setFlash('success', 'Activation email has been resent. Please check your inbox.');
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        // Redirect back to previous page or home
        if (isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: ' . BASE_URL);
        }
        exit;
    }
    
    // Process account activation
    public function activate() {
        $email = $_GET['email'] ?? '';
        $token = $_GET['token'] ?? '';
        
        $data = [
            'title' => 'Account Activation',
            'success' => false,
            'message' => ''
        ];
        
        if (!empty($email) && !empty($token)) {
            $result = $this->user->activateAccount($email, $token);
            
            if ($result['success']) {
                $data['success'] = true;
                $data['message'] = 'Your account has been activated successfully! You can now use all features.';
                
                // Update session to reflect activation
                if (Session::isLoggedIn() && Session::get('user_email') === $email) {
                    Session::set('is_activated', 1);
                }
            } else {
                $data['message'] = $result['message'];
            }
        } else {
            $data['message'] = 'Invalid activation link. Please check your email or contact support.';
        }
        
        // Load view
        include VIEWS_PATH . '/auth/activate.php';
    }
    
    // Log out user
    public function logout() {
        // Clear auth session
        Session::clearAuth();
        
        // Set flash message
        Session::setFlash('success', 'You have been logged out successfully.');
        
        // Redirect to login page
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
    
    // Display and process password reset request form
    public function resetPassword() {
        // Check if already logged in
        if (Session::isLoggedIn()) {
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $data = [
            'title' => 'Reset Password',
            'email' => '',
            'errors' => [],
            'token' => $_GET['token'] ?? '',
            'step' => isset($_GET['token']) ? 'new_password' : 'request'
        ];
        
        // Process request form
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $data['step'] === 'request') {
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email)) {
                $data['errors']['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data['errors']['email'] = 'Invalid email format';
            }
            
            if (empty($data['errors'])) {
                $result = $this->user->createPasswordResetToken($email);
                
                if ($result['success']) {
                    // Send reset email
                    sendPasswordResetEmail($email, $result['display_name'], $result['reset_token']);
                    
                    // Set flash message
                    Session::setFlash('success', 'Password reset link has been sent to your email!');
                    
                    // Redirect to prevent re-submission
                    header('Location: ' . BASE_URL . '/login');
                    exit;
                } else {
                    $data['errors']['general'] = $result['message'];
                }
            }
            
            $data['email'] = $email;
        }
        
        // Process new password form
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $data['step'] === 'new_password') {
            $email = trim($_GET['email'] ?? '');
            $token = $_GET['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($password)) {
                $data['errors']['password'] = 'Password is required';
            } elseif (strlen($password) < 8) {
                $data['errors']['password'] = 'Password must be at least 8 characters';
            }
            
            if ($password !== $confirm_password) {
                $data['errors']['confirm_password'] = 'Passwords do not match';
            }
            
            if (empty($data['errors'])) {
                $result = $this->user->resetPassword($email, $token, $password);
                
                if ($result['success']) {
                    // Set flash message
                    Session::setFlash('success', 'Your password has been reset successfully. You can now login with your new password.');
                    
                    // Redirect to login
                    header('Location: ' . BASE_URL . '/login');
                    exit;
                } else {
                    $data['errors']['general'] = $result['message'];
                }
            }
        }
        
        // Verify token if on new password step
        if ($data['step'] === 'new_password') {
            $email = $_GET['email'] ?? '';
            $token = $_GET['token'] ?? '';
            
            if (empty($email) || empty($token) || !$this->user->verifyResetToken($email, $token)) {
                $data['errors']['general'] = 'Invalid or expired reset token. Please request a new password reset.';
                $data['step'] = 'request';
            }
        }
        
        // Load view
        include VIEWS_PATH . '/auth/reset-password.php';
    }

    // Process OTP verification for password reset
    public function verifyOTP() {
        // Check if already logged in
        if (Session::isLoggedIn()) {
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $data = [
            'title' => 'Verify OTP',
            'email' => $_GET['email'] ?? '',
            'errors' => [],
            'step' => 'verify_otp'
        ];
        
        // Process OTP form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $otp = trim($_POST['otp'] ?? '');
            
            if (empty($email)) {
                $data['errors']['email'] = 'Email is required';
            }
            
            if (empty($otp)) {
                $data['errors']['otp'] = 'OTP is required';
            }
            
            if (empty($data['errors'])) {
                $result = $this->user->verifyOTP($email, $otp);
                
                if ($result) {
                    // Redirect to new password page
                    Session::set('reset_verified', true);
                    Session::set('reset_email', $email);
                    Session::set('reset_method', 'otp');
                    
                    header('Location: ' . BASE_URL . '/reset-password/new-password');
                    exit;
                } else {
                    $data['errors']['general'] = 'Invalid or expired OTP';
                }
            }
            
            $data['email'] = $email;
        }
        
        // Load view
        include VIEWS_PATH . '/auth/verify-otp.php';
    }
}