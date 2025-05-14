<?php
require_once MODELS_PATH . '/User.php';
require_once MODELS_PATH . '/Note.php';
require_once MODELS_PATH . '/Label.php';
require_once MODELS_PATH . '/SharedNote.php';

class ProfileController {
    private $user;
    private $note;
    private $label;
    private $sharedNote;
    
    public function __construct() {
        $this->user = new User();
        $this->note = new Note();
        $this->label = new Label();
        $this->sharedNote = new SharedNote();
    }
    
    // Display user profile
    public function index() {
        // Get user data
        $user_id = Session::getUserId();
        $user = $this->user->getUserById($user_id);
        
        if (!$user) {
            Session::setFlash('error', 'User not found');
            header('Location: ' . BASE_URL);
            exit;
        }
        
        // Get actual statistics
        $stats = $this->getUserStatistics($user_id);
        
        // Set page data
        $data = [
            'pageTitle' => 'My Profile',
            'user' => $user,
            'stats' => $stats
        ];
        
        // Load view
        include VIEWS_PATH . '/components/header.php';
        include VIEWS_PATH . '/profile/view.php';
        include VIEWS_PATH . '/components/footer.php';
    }
    
    // Get user statistics from database
    private function getUserStatistics($user_id) {
        // Count total notes
        $totalNotes = $this->countUserNotes($user_id);
        
        // Count total labels
        $totalLabels = $this->countUserLabels($user_id);
        
        // Count shared notes
        $sharedNotes = $this->countSharedNotes($user_id);
        
        // Count uploaded images
        $uploadedImages = $this->countUploadedImages($user_id);
        
        return [
            'total_notes' => $totalNotes,
            'total_labels' => $totalLabels,
            'shared_notes' => $sharedNotes,
            'uploaded_images' => $uploadedImages
        ];
    }
    
    // Count user's notes
    private function countUserNotes($user_id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM notes WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    // Count user's labels
    private function countUserLabels($user_id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM labels WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    // Count notes shared with user
    private function countSharedNotes($user_id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM shared_notes WHERE recipient_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    // Count images uploaded by user
    private function countUploadedImages($user_id) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT COUNT(*) as count FROM images 
            WHERE note_id IN (SELECT id FROM notes WHERE user_id = ?)
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    // Display and process profile edit form
    public function edit() {
        // Get user data
        $user_id = Session::getUserId();
        $user = $this->user->getUserById($user_id);
        
        if (!$user) {
            Session::setFlash('error', 'User not found');
            header('Location: ' . BASE_URL);
            exit;
        }
        
        // Set default data
        $data = [
            'pageTitle' => 'Edit Profile',
            'user' => $user,
            'errors' => []
        ];
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $display_name = trim($_POST['display_name'] ?? '');
            
            // Validate input
            if (empty($display_name)) {
                $data['errors']['display_name'] = 'Display name is required';
            }
            
            // If no errors, update profile
            if (empty($data['errors'])) {
                // Update display name function would need to be added to User model
                $result = $this->user->updateProfile($user_id, $display_name);
                
                if ($result['success']) {
                    // Update session display name
                    Session::set('user_display_name', $display_name);
                    
                    Session::setFlash('success', 'Profile updated successfully');
                    header('Location: ' . BASE_URL . '/profile');
                    exit;
                } else {
                    $data['errors']['general'] = $result['message'];
                }
            }
            
            // Update user data for form
            $data['user']['display_name'] = $display_name;
        }
        
        // Load view
        include VIEWS_PATH . '/components/header.php';
        include VIEWS_PATH . '/profile/edit.php';
        include VIEWS_PATH . '/components/footer.php';
    }
    
    // Display and process change password form
    public function changePassword() {
        // Get user data
        $user_id = Session::getUserId();
        
        // Set default data
        $data = [
            'pageTitle' => 'Change Password',
            'errors' => []
        ];
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validate input
            if (empty($current_password)) {
                $data['errors']['current_password'] = 'Current password is required';
            }
            
            if (empty($new_password)) {
                $data['errors']['new_password'] = 'New password is required';
            } elseif (strlen($new_password) < 8) {
                $data['errors']['new_password'] = 'Password must be at least 8 characters';
            }
            
            if ($new_password !== $confirm_password) {
                $data['errors']['confirm_password'] = 'Passwords do not match';
            }
            
            // If no errors, change password
            if (empty($data['errors'])) {
                // Need to add changePassword method to User model
                $result = $this->user->changePassword($user_id, $current_password, $new_password);
                
                if ($result['success']) {
                    Session::setFlash('success', 'Password changed successfully');
                    header('Location: ' . BASE_URL . '/profile');
                    exit;
                } else {
                    $data['errors']['general'] = $result['message'];
                }
            }
        }
        
        // Load view
        include VIEWS_PATH . '/components/header.php';
        include VIEWS_PATH . '/profile/change-password.php';
        include VIEWS_PATH . '/components/footer.php';
    }
    
    // Display and process user preferences form
    public function preferences() {
        $user_id = Session::getUserId();
        $preferences = $this->user->getUserPreferences($user_id);
        
        // Set default data
        $data = [
            'pageTitle' => 'Preferences',
            'preferences' => $preferences,
            'errors' => []
        ];
        
        // Load view
        include VIEWS_PATH . '/components/header.php';
        include VIEWS_PATH . '/profile/preferences.php';
        include VIEWS_PATH . '/components/footer.php';
    }

    public function savePreferences() {
        $user_id = Session::getUserId();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get preferences from form
            $theme = isset($_POST['theme']) ? $_POST['theme'] : 'light';
            $font_size = isset($_POST['font_size']) ? $_POST['font_size'] : 'medium';
            $note_color = isset($_POST['note_color']) ? $_POST['note_color'] : 'white';
            
            // Create preferences array
            $preferences = [
                'theme' => $theme,
                'font_size' => $font_size,
                'note_color' => $note_color
            ];
            
            // Save preferences
            $result = $this->user->updatePreferences($user_id, $preferences);
            
            if ($result['success']) {
                Session::setFlash('success', 'Preferences updated successfully');
            } else {
                Session::setFlash('error', $result['message']);
            }
            
            // Redirect back to preferences page
            header('Location: ' . BASE_URL . '/profile/preferences');
            exit;
        }
    }
    
    public function uploadAvatar() {
        $user_id = Session::getUserId();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Define the main uploads directory path
            $uploads_dir = ROOT_PATH . '/uploads';
            $avatars_dir = $uploads_dir . '/avatars';
            
            // Create directory structure if needed
            if (!file_exists($uploads_dir)) {
                @mkdir($uploads_dir, 0755, true);
            }
            
            if (!file_exists($avatars_dir)) {
                @mkdir($avatars_dir, 0755, true);
            }
            
            // Verify directory is writable and use fallback if needed
            $avatars_dir_is_writable = is_writable($avatars_dir);
            if (!$avatars_dir_is_writable) {
                // Try to set permissions
                @chmod($avatars_dir, 0755);
                $avatars_dir_is_writable = is_writable($avatars_dir);
                
                // If still not writable, use system temp directory as fallback
                if (!$avatars_dir_is_writable) {
                    $temp_dir = sys_get_temp_dir() . '/note_avatars';
                    if (!file_exists($temp_dir)) {
                        @mkdir($temp_dir, 0777, true);
                    }
                    $avatars_dir = $temp_dir;
                    $avatars_dir_is_writable = is_writable($avatars_dir);
                    
                    if (!$avatars_dir_is_writable) {
                        Session::setFlash('error', 'Cannot find a writable directory for uploads');
                        header('Location: ' . BASE_URL . '/profile/edit');
                        exit;
                    }
                }
            }
            
            // Handle avatar upload
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['avatar'];
                $tmp_name = $file['tmp_name'];
                
                // Verify file type using multiple methods
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime_type = $finfo->file($tmp_name);
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
                
                if (!in_array($mime_type, $allowed_types)) {
                    Session::setFlash('error', 'Invalid file type. Only JPEG and PNG are allowed.');
                    header('Location: ' . BASE_URL . '/profile/edit');
                    exit;
                }
                
                // Validate file size
                $max_size = 2 * 1024 * 1024; // 2MB
                if ($file['size'] > $max_size) {
                    Session::setFlash('error', 'File is too large. Maximum size is 2MB.');
                    header('Location: ' . BASE_URL . '/profile/edit');
                    exit;
                }
                
                // Generate unique filename
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    $extension = ($mime_type === 'image/png') ? 'png' : 'jpg';
                }
                
                $new_filename = 'avatar_' . $user_id . '_' . uniqid() . '.' . $extension;
                $destination = $avatars_dir . '/' . $new_filename;
                
                // Get user data before updating
                $user = $this->user->getUserById($user_id);
                $old_avatar_path = null;
                
                if ($user && !empty($user['avatar_path'])) {
                    // Try both possible locations for the old avatar
                    $old_avatar_main = $uploads_dir . '/avatars/' . $user['avatar_path'];
                    $old_avatar_temp = sys_get_temp_dir() . '/note_avatars/' . $user['avatar_path'];
                    
                    if (file_exists($old_avatar_main)) {
                        $old_avatar_path = $old_avatar_main;
                    } elseif (file_exists($old_avatar_temp)) {
                        $old_avatar_path = $old_avatar_temp;
                    }
                }
                
                // Move uploaded file
                if (move_uploaded_file($tmp_name, $destination)) {
                    // Delete old avatar if it exists
                    if ($old_avatar_path && file_exists($old_avatar_path)) {
                        @unlink($old_avatar_path);
                    }
                    
                    // Update database
                    $result = $this->user->updateAvatar($user_id, $new_filename);
                    
                    if ($result['success']) {
                        Session::setFlash('success', 'Avatar updated successfully');
                    } else {
                        Session::setFlash('error', $result['message']);
                    }
                } else {
                    Session::setFlash('error', 'Failed to upload avatar. Please try again.');
                }
            } else if (isset($_POST['remove_avatar']) && $_POST['remove_avatar'] === '1') {
                // Remove avatar - check both possible locations
                $user = $this->user->getUserById($user_id);
                if ($user && !empty($user['avatar_path'])) {
                    $main_path = $uploads_dir . '/avatars/' . $user['avatar_path'];
                    $temp_path = sys_get_temp_dir() . '/note_avatars/' . $user['avatar_path'];
                    
                    if (file_exists($main_path)) {
                        @unlink($main_path);
                    }
                    if (file_exists($temp_path)) {
                        @unlink($temp_path);
                    }
                }
                
                // Update database
                $result = $this->user->removeAvatar($user_id);
                
                if ($result['success']) {
                    Session::setFlash('success', 'Avatar removed successfully');
                } else {
                    Session::setFlash('error', $result['message']);
                }
            } else if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_OK && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Handle upload errors with detailed message
                $error_message = $this->getFileUploadErrorMessage($_FILES['avatar']['error']);
                Session::setFlash('error', 'Avatar upload failed: ' . $error_message);
            }
            
            header('Location: ' . BASE_URL . '/profile/edit');
            exit;
        }
    }
    
    // Helper function to get file upload error messages
    private function getFileUploadErrorMessage($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
            case UPLOAD_ERR_FORM_SIZE:
                return "The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form";
            case UPLOAD_ERR_PARTIAL:
                return "The uploaded file was only partially uploaded";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing a temporary folder";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk";
            case UPLOAD_ERR_EXTENSION:
                return "A PHP extension stopped the file upload";
            default:
                return "Unknown upload error";
        }
    }
}