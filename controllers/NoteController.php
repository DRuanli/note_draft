<?php
require_once MODELS_PATH . '/Note.php';
require_once MODELS_PATH . '/Label.php';
require_once MODELS_PATH . '/SharedNote.php';

class NoteController {
    private $note;
    private $label;
    private $sharedNote;
    private $user;
    
    public function __construct() {
        $this->note = new Note();
        $this->label = new Label();
        $this->sharedNote = new SharedNote();
        require_once MODELS_PATH . '/User.php';
        $this->user = new User();
    }

    /**
     * Display a single note (read-only view)
     * This method should be added to the NoteController class
     */
    public function view($id) {
        $user_id = Session::getUserId();
        
        // Get note
        $note = $this->note->getById($id);
        
        // Check if note exists and belongs to the user or is shared with them
        if (!$note || ($note['user_id'] != $user_id && !$this->sharedNote->isSharedWithUser($id, $user_id))) {
            Session::setFlash('error', 'Note not found or access denied');
            header('Location: ' . BASE_URL . '/notes');
            exit;
        }
        
        // Check if note is password protected
        if (isset($note['is_password_protected']) && $note['is_password_protected']) {
            // Handle password protection - check if already verified this session
            $verified_notes = Session::get('verified_notes', []);
            
            if (!in_array($id, $verified_notes)) {
                // Redirect to password verification page
                header('Location: ' . BASE_URL . '/notes/verify-password/' . $id . '?redirect=view');
                exit;
            }
        }
        
        // Get note labels
        $note_labels = $this->note->getNoteLabels($id);
        $note['labels'] = $note_labels;
        
        // Get note images
        $note['images'] = $this->note->getNoteImages($id);
        
        // Determine if the user can edit (owner or has edit permission)
        $isOwner = $note['user_id'] == $user_id;
        $can_edit = $isOwner || $this->sharedNote->canEditSharedNote($id, $user_id);
        
        // Get owner information if shared
        if (!$isOwner) {
            $owner = $this->user->getUserById($note['user_id']);
            $note['owner_name'] = $owner ? $owner['display_name'] : 'Unknown User';
        }
        
        // Set page data
        $data = [
            'pageTitle' => $note['title'],
            'pageStyles' => ['notes'],
            'pageScripts' => ['notes'],
            'note' => $note,
            'isOwner' => $isOwner,
            'can_edit' => $can_edit
        ];
        
        // Load view
        include VIEWS_PATH . '/components/header.php';
        include VIEWS_PATH . '/notes/view.php';
        include VIEWS_PATH . '/components/footer.php';
    }
    
    public function index() {
        // Get user ID from session
        $user_id = Session::getUserId();
        
        // Get view preference (default to grid)
        $view = isset($_GET['view']) && $_GET['view'] === 'list' ? 'list' : 'grid';
        
        // Store view preference in session
        if (isset($_GET['view'])) {
            Session::set('notes_view_' . $user_id, $view);
        } else {
            // Use saved preference if available
            $savedView = Session::get('notes_view_' . $user_id);
            if ($savedView) {
                $view = $savedView;
            }
        }
        
        $label_filter = isset($_GET['label']) ? intval($_GET['label']) : null;
        $search = $_GET['search'] ?? '';
        
        // Get user's notes
        $notes = $this->note->getUserNotes($user_id, $label_filter, $search);
    
        // Deduplicate notes by ID
        $uniqueNotes = [];
        $seenIds = [];
        
        foreach ($notes as $note) {
            if (!in_array($note['id'], $seenIds)) {
                $seenIds[] = $note['id'];
                $uniqueNotes[] = $note;
            }
        }
        
        // Replace with deduplicated notes
        $notes = $uniqueNotes;
        
        // Add images to notes
        foreach ($notes as &$note) {
            // If the note has images, get the first one
            if (isset($note['image_count']) && $note['image_count'] > 0) {
                $note['images'] = $this->note->getNoteImages($note['id']);
            }
        }
        // Important: unset reference to prevent accidental modification
        unset($note);
        
        // Get shared notes with user
        $shared_notes = $this->sharedNote->getNotesSharedWithUser($user_id);
        
        // Get all labels for the user
        $labels = $this->label->getUserLabels($user_id);
        
        // Set page data with all required keys
        $data = [
            'pageTitle' => 'My Notes',
            'pageStyles' => ['notes'],
            'pageScripts' => ['notes'],
            'view' => $view,
            'notes' => $notes,
            'shared_notes' => $shared_notes,
            'labels' => $labels,
            'current_label' => $label_filter,
            'search' => $search
        ];
        
        // Load view
        include VIEWS_PATH . '/components/header.php';
        include VIEWS_PATH . '/notes/index.php';
        include VIEWS_PATH . '/components/footer.php';
    }
    
    // Display note creation form
    public function create() {
        // Set page data
        $data = [
            'pageTitle' => 'Create Note',
            'pageStyles' => ['notes'],
            'pageScripts' => ['notes'],
            'note' => [
                'title' => '',
                'content' => '',
                'labels' => []
            ],
            'labels' => $this->label->getUserLabels(Session::getUserId()),
            'errors' => []
        ];
        
        // Load view
        include VIEWS_PATH . '/components/header.php';
        include VIEWS_PATH . '/notes/edit.php';
        include VIEWS_PATH . '/components/footer.php';
    }
    
    // Process and save a new note
    public function store() {
        // Check if request is AJAX
        $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        $user_id = Session::getUserId();
        $title = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $label_ids = isset($_POST['labels']) && is_array($_POST['labels']) ? $_POST['labels'] : [];
        
        // Validate input
        $errors = [];
        if (empty($title)) {
            $errors['title'] = 'Title is required';
        }
        
        if (empty($errors)) {
            // Save note
            $result = $this->note->create($user_id, $title, $content);
            
            if ($result['success']) {
                $note_id = $result['note_id'];
                
                // Attach labels
                if (!empty($label_ids)) {
                    foreach ($label_ids as $label_id) {
                        $this->note->attachLabel($note_id, $label_id);
                    }
                }
                
                // Process uploaded images
                if (!empty($_FILES['images']['name'][0])) {
                    $this->processImageUploads($note_id);
                }
                
                if ($is_ajax) {
                    // Return JSON response for AJAX requests
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Note created successfully',
                        'note_id' => $note_id
                    ]);
                    exit;
                } else {
                    // Redirect to notes page
                    Session::setFlash('success', 'Note created successfully');
                    header('Location: ' . BASE_URL . '/notes');
                    exit;
                }
            } else {
                $errors['general'] = 'Failed to create note: ' . $result['message'];
            }
        }
        
        if ($is_ajax) {
            // Return JSON response for AJAX requests
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'errors' => $errors
            ]);
            exit;
        } else {
            // If not AJAX, show form again with errors
            $data = [
                'pageTitle' => 'Create Note',
                'pageStyles' => ['notes'],
                'pageScripts' => ['notes'],
                'note' => [
                    'title' => $title,
                    'content' => $content,
                    'labels' => $label_ids
                ],
                'labels' => $this->label->getUserLabels($user_id),
                'errors' => $errors
            ];
            
            // Load view
            include VIEWS_PATH . '/components/header.php';
            include VIEWS_PATH . '/notes/edit.php';
            include VIEWS_PATH . '/components/footer.php';
        }
    }
    
    // Display note for editing
    public function edit($id) {
        $user_id = Session::getUserId();
        
        // Get note
        $note = $this->note->getById($id);
        
        // Check if note exists and belongs to the user or is shared with edit permission
        if (!$note || ($note['user_id'] != $user_id && !$this->sharedNote->isSharedWithUser($id, $user_id))) {
            Session::setFlash('error', 'Note not found or access denied');
            header('Location: ' . BASE_URL . '/notes');
            exit;
        }
        
        // Check if note is password protected
        if ($note['is_password_protected']) {
            // Handle password protection - check if already verified this session
            $verified_notes = Session::get('verified_notes', []);
            
            if (!in_array($id, $verified_notes)) {
                // Redirect to password verification page
                header('Location: ' . BASE_URL . '/notes/verify-password/' . $id . '?redirect=edit');
                exit;
            }
        }
        
        // Get note labels
        $note_labels = $this->note->getNoteLabels($id);
        $note['labels'] = array_column($note_labels, 'id');
        
        // Get note images
        $note['images'] = $this->note->getNoteImages($id);
        
        // If note is shared (not owned by current user), add sharing permissions to note data
        if ($note['user_id'] != $user_id) {
            $note['can_edit'] = $this->sharedNote->canEditSharedNote($id, $user_id);
            
            // Get owner information
            $owner = $this->user->getUserById($note['user_id']);
            $note['owner_name'] = $owner ? $owner['display_name'] : 'Unknown User';
        }
        
        // Set page data
        $data = [
            'pageTitle' => 'Edit Note',
            'pageStyles' => ['notes'],
            'pageScripts' => ['notes'],
            'note' => $note,
            'labels' => $this->label->getUserLabels($user_id),
            'errors' => []
        ];
        
        // Load view
        include VIEWS_PATH . '/components/header.php';
        include VIEWS_PATH . '/notes/edit.php';
        include VIEWS_PATH . '/components/footer.php';
    }
    
    // Update existing note
    public function update($id) {
        // Check if request is AJAX
        $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        $user_id = Session::getUserId();
        
        // Get current note
        $note = $this->note->getById($id);
        
        // Check if note exists and belongs to the user OR user has edit permission
        if (!$note || ($note['user_id'] != $user_id && !$this->sharedNote->canEditSharedNote($id, $user_id))) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Note not found or access denied'
                ]);
                exit;
            } else {
                Session::setFlash('error', 'Note not found or access denied');
                header('Location: ' . BASE_URL . '/notes');
                exit;
            }
        }
        
        $title = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $label_ids = isset($_POST['labels']) && is_array($_POST['labels']) ? $_POST['labels'] : [];
        
        // Validate input
        $errors = [];
        if (empty($title)) {
            $errors['title'] = 'Title is required';
        }
        
        if (empty($errors)) {
            // Update note
            $result = $this->note->update($id, $title, $content);
            
            if ($result['success']) {
                // Update labels
                $this->note->detachAllLabels($id);
                if (!empty($label_ids)) {
                    foreach ($label_ids as $label_id) {
                        $this->note->attachLabel($id, $label_id);
                    }
                }
                
                // Process uploaded images
                if (!empty($_FILES['images']['name'][0])) {
                    $this->processImageUploads($id);
                }
                
                if ($is_ajax) {
                    // Return JSON response for AJAX requests
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Note updated successfully'
                    ]);
                    exit;
                } else {
                    // Redirect to notes page
                    Session::setFlash('success', 'Note updated successfully');
                    header('Location: ' . BASE_URL . '/notes');
                    exit;
                }
            } else {
                $errors['general'] = 'Failed to update note: ' . $result['message'];
            }
        }
        
        if ($is_ajax) {
            // Return JSON response for AJAX requests
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'errors' => $errors
            ]);
            exit;
        } else {
            // If not AJAX, show form again with errors
            $data = [
                'pageTitle' => 'Edit Note',
                'pageStyles' => ['notes'],
                'pageScripts' => ['notes'],
                'note' => [
                    'id' => $id,
                    'title' => $title,
                    'content' => $content,
                    'labels' => $label_ids,
                    'images' => $this->note->getNoteImages($id)
                ],
                'labels' => $this->label->getUserLabels($user_id),
                'errors' => $errors
            ];
            
            // Load view
            include VIEWS_PATH . '/components/header.php';
            include VIEWS_PATH . '/notes/edit.php';
            include VIEWS_PATH . '/components/footer.php';
        }
    }
    
    // Delete note with confirmation
    public function delete($id) {
        $user_id = Session::getUserId();
        
        // Check if confirmed
        if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            // Get note
            $note = $this->note->getById($id);
            
            // Check if note exists and belongs to the user
            if (!$note || $note['user_id'] != $user_id) {
                Session::setFlash('error', 'Note not found or access denied');
                header('Location: ' . BASE_URL . '/notes');
                exit;
            }
            
            // Delete note
            $result = $this->note->delete($id);
            
            if ($result['success']) {
                Session::setFlash('success', 'Note deleted successfully');
            } else {
                Session::setFlash('error', 'Failed to delete note: ' . $result['message']);
            }
            
            // Redirect to notes page
            header('Location: ' . BASE_URL . '/notes');
            exit;
        } else {
            // Show confirmation page
            $note = $this->note->getById($id);
            
            // Check if note exists and belongs to the user
            if (!$note || $note['user_id'] != $user_id) {
                Session::setFlash('error', 'Note not found or access denied');
                header('Location: ' . BASE_URL . '/notes');
                exit;
            }
            
            // Check if note is password protected
            if ($note['is_password_protected']) {
                // Handle password protection - check if already verified this session
                $verified_notes = Session::get('verified_notes', []);
                
                if (!in_array($id, $verified_notes)) {
                    // Redirect to password verification page
                    header('Location: ' . BASE_URL . '/notes/verify-password/' . $id . '?redirect=delete');
                    exit;
                }
            }
            
            // Set page data
            $data = [
                'pageTitle' => 'Delete Note',
                'pageStyles' => ['notes'],
                'note' => $note
            ];
            
            // Load view
            include VIEWS_PATH . '/components/header.php';
            include VIEWS_PATH . '/notes/delete.php';
            include VIEWS_PATH . '/components/footer.php';
        }
    }
    
    // Toggle pin status for a note
    public function togglePin($id) {
        $user_id = Session::getUserId();
        
        // Get note
        $note = $this->note->getById($id);
        
        // Check if note exists and belongs to the user
        if (!$note || $note['user_id'] != $user_id) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                // AJAX request
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Note not found or access denied'
                ]);
                exit;
            } else {
                Session::setFlash('error', 'Note not found or access denied');
                header('Location: ' . BASE_URL . '/notes');
                exit;
            }
        }
        
        // Check if note is password protected
        if (isset($note['is_password_protected']) && $note['is_password_protected']) {
            // Handle password protection - check if already verified this session
            $verified_notes = Session::get('verified_notes', []);
            
            if (!in_array($id, $verified_notes)) {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    // AJAX request
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Password verification required',
                        'redirect' => BASE_URL . '/notes/verify-password/' . $id . '?redirect=toggle-pin'
                    ]);
                    exit;
                } else {
                    // Redirect to password verification page
                    header('Location: ' . BASE_URL . '/notes/verify-password/' . $id . '?redirect=toggle-pin');
                    exit;
                }
            }
        }
        
        // Toggle pin status
        $result = $this->note->togglePin($id);
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            // AJAX request
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $result['success'],
                'message' => $result['message'],
                'is_pinned' => $result['is_pinned'] ?? false
            ]);
            exit;
        } else {
            if ($result['success']) {
                $action = isset($result['is_pinned']) && $result['is_pinned'] ? 'pinned' : 'unpinned';
                Session::setFlash('success', 'Note ' . $action . ' successfully');
            } else {
                Session::setFlash('error', 'Failed to update note: ' . $result['message']);
            }
            
            // Redirect back to notes page
            header('Location: ' . BASE_URL . '/notes');
            exit;
        }
    }
    
    // Toggle password protection for a note
    public function togglePasswordProtection($id) {
        $user_id = Session::getUserId();
        
        // Get note
        $note = $this->note->getById($id);
        
        // Check if note exists and belongs to the user
        if (!$note || $note['user_id'] != $user_id) {
            Session::setFlash('error', 'Note not found or access denied');
            header('Location: ' . BASE_URL . '/notes');
            exit;
        }
        
        if ($note['is_password_protected']) {
            // Disable password protection - require current password
            $data = [
                'pageTitle' => 'Disable Password Protection',
                'pageStyles' => ['notes'],
                'note' => $note,
                'action' => 'disable',
                'errors' => []
            ];
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['current_password'] ?? '';
                
                if (empty($password)) {
                    $data['errors']['current_password'] = 'Current password is required';
                } elseif (!$this->note->verifyNotePassword($id, $password)) {
                    $data['errors']['current_password'] = 'Incorrect password';
                } else {
                    // Disable password protection
                    $result = $this->note->disablePasswordProtection($id);
                    
                    if ($result['success']) {
                        Session::setFlash('success', 'Password protection disabled successfully');
                        header('Location: ' . BASE_URL . '/notes/edit/' . $id);
                        exit;
                    } else {
                        $data['errors']['general'] = 'Failed to disable password protection: ' . $result['message'];
                    }
                }
            }
        } else {
            // Enable password protection - set new password
            $data = [
                'pageTitle' => 'Enable Password Protection',
                'pageStyles' => ['notes'],
                'note' => $note,
                'action' => 'enable',
                'errors' => []
            ];
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if (empty($password)) {
                    $data['errors']['password'] = 'Password is required';
                } elseif (strlen($password) < 4) {
                    $data['errors']['password'] = 'Password must be at least 4 characters';
                }
                
                if ($password !== $confirm_password) {
                    $data['errors']['confirm_password'] = 'Passwords do not match';
                }
                
                if (empty($data['errors'])) {
                    // Enable password protection
                    $result = $this->note->enablePasswordProtection($id, $password);
                    
                    if ($result['success']) {
                        Session::setFlash('success', 'Password protection enabled successfully');
                        header('Location: ' . BASE_URL . '/notes/edit/' . $id);
                        exit;
                    } else {
                        $data['errors']['general'] = 'Failed to enable password protection: ' . $result['message'];
                    }
                }
            }
        }
        
        // Load view
        include VIEWS_PATH . '/components/header.php';
        include VIEWS_PATH . '/notes/password-protection.php';
        include VIEWS_PATH . '/components/footer.php';
    }

    // Verify password for a protected note
    public function verifyPassword($id) {
        $user_id = Session::getUserId();
        
        // Get note
        $note = $this->note->getById($id);
        
        // Check if note exists and belongs to the user or is shared with the user
        if (!$note || ($note['user_id'] != $user_id && !$this->sharedNote->isSharedWithUser($id, $user_id))) {
            Session::setFlash('error', 'Note not found or access denied');
            header('Location: ' . BASE_URL . '/notes');
            exit;
        }
        
        // Check if note is actually password protected
        if (!$note['is_password_protected']) {
            // Redirect to the intended page
            $redirect = $_GET['redirect'] ?? 'view';
            header('Location: ' . BASE_URL . '/notes/' . $redirect . '/' . $id);
            exit;
        }
        
        $data = [
            'pageTitle' => 'Verify Password',
            'pageStyles' => ['notes'],
            'note' => $note,
            'redirect' => $_GET['redirect'] ?? 'view',
            'errors' => []
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            
            if (empty($password)) {
                $data['errors']['password'] = 'Password is required';
            } elseif (!$this->note->verifyNotePassword($id, $password)) {
                $data['errors']['password'] = 'Incorrect password';
            } else {
                // Password verified - store in session and redirect
                $verified_notes = Session::get('verified_notes', []);
                $verified_notes[] = $id;
                Session::set('verified_notes', $verified_notes);
                
                // Redirect to the intended page
                $redirect = $_POST['redirect'] ?? 'view';
                
                // Handle special case for toggle-pin
                if ($redirect === 'toggle-pin') {
                    // Perform toggle pin operation directly
                    $result = $this->note->togglePin($id);
                    if ($result['success']) {
                        $action = isset($result['is_pinned']) && $result['is_pinned'] ? 'pinned' : 'unpinned';
                        Session::setFlash('success', 'Note ' . $action . ' successfully');
                    } else {
                        Session::setFlash('error', 'Failed to update note: ' . $result['message']);
                    }
                    header('Location: ' . BASE_URL . '/notes');
                    exit;
                } else {
                    header('Location: ' . BASE_URL . '/notes/' . $redirect . '/' . $id);
                    exit;
                }
            }
        }
        
        // Load view
        include VIEWS_PATH . '/components/header.php';
        include VIEWS_PATH . '/notes/verify-password.php';
        include VIEWS_PATH . '/components/footer.php';
    }

    // Share a note with other users
    public function share($id) {
        $user_id = Session::getUserId();
        
        // Get note
        $note = $this->note->getById($id);
        
        // Check if note exists and belongs to the user
        if (!$note || $note['user_id'] != $user_id) {
            Session::setFlash('error', 'Note not found or access denied');
            header('Location: ' . BASE_URL . '/notes');
            exit;
        }
        
        // Check if note is password protected
        if ($note['is_password_protected']) {
            // Handle password protection - check if already verified this session
            $verified_notes = Session::get('verified_notes', []);
            
            if (!in_array($id, $verified_notes)) {
                // Redirect to password verification page
                header('Location: ' . BASE_URL . '/notes/verify-password/' . $id . '?redirect=share');
                exit;
            }
        }
        
        // Get current shares
        $current_shares = $this->sharedNote->getNoteShares($id);
        
        $data = [
            'pageTitle' => 'Share Note',
            'pageStyles' => ['notes'],
            'pageScripts' => ['notes'],
            'note' => $note,
            'current_shares' => $current_shares,
            'errors' => []
        ];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $can_edit = isset($_POST['can_edit']) && $_POST['can_edit'] == '1';
            
            // Process recipient emails
            $recipient_emails = [];
            
            // Handle array of emails
            if (isset($_POST['recipient_emails']) && is_array($_POST['recipient_emails'])) {
                $recipient_emails = $_POST['recipient_emails'];
            } 
            // Handle single string (comma or newline separated)
            else if (isset($_POST['recipient_emails']) && is_string($_POST['recipient_emails'])) {
                $email_text = trim($_POST['recipient_emails']);
                if (!empty($email_text)) {
                    // Split by newlines, commas, or semicolons
                    $recipient_emails = preg_split('/[\n,;]+/', $email_text);
                    // Clean up
                    $recipient_emails = array_map('trim', $recipient_emails);
                    $recipient_emails = array_filter($recipient_emails);
                }
            }
            
            if (empty($recipient_emails)) {
                $data['errors']['recipient_emails'] = 'At least one recipient email is required';
            }
            
            if (empty($data['errors'])) {
                $success_count = 0;
                $failed_emails = [];
                
                // Try to share with each recipient
                foreach ($recipient_emails as $email) {
                    $email = trim($email);
                    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $failed_emails[] = $email . ' (invalid format)';
                        continue;
                    }
                    
                    $result = $this->sharedNote->shareNote($id, $user_id, $email, $can_edit);
                    
                    if ($result['success']) {
                        $success_count++;
                    } else {
                        $failed_emails[] = $email . ' (' . $result['message'] . ')';
                    }
                }
                
                // Prepare result message
                if ($success_count > 0) {
                    $message = "Note shared successfully with {$success_count} recipient(s)";
                    if (!empty($failed_emails)) {
                        $message .= ". Failed to share with: " . implode(", ", $failed_emails);
                    }
                    Session::setFlash('success', $message);
                } else {
                    Session::setFlash('error', "Failed to share note with any recipients: " . implode(", ", $failed_emails));
                }
                
                header('Location: ' . BASE_URL . '/notes/share/' . $id);
                exit;
            }
        }
        
        // Load view
        include VIEWS_PATH . '/components/header.php';
        include VIEWS_PATH . '/notes/share.php';
        include VIEWS_PATH . '/components/footer.php';
    }

    // Update share permissions
    public function updateShare($id, $share_id, $can_edit) {
        $user_id = Session::getUserId();
        
        // Get note
        $note = $this->note->getById($id);
        
        // Check if note exists and belongs to the user
        if (!$note || $note['user_id'] != $user_id) {
            Session::setFlash('error', 'Note not found or access denied');
            header('Location: ' . BASE_URL . '/notes');
            exit;
        }
        
        // Update share permissions
        $result = $this->sharedNote->updateSharePermissions($share_id, $user_id, $can_edit);
        
        if ($result['success']) {
            Session::setFlash('success', 'Sharing permissions updated successfully');
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        // Redirect back to share page
        header('Location: ' . BASE_URL . '/notes/share/' . $id);
        exit;
    }
    
    // Remove sharing for a user
    public function removeShare($id, $share_id) {
        $user_id = Session::getUserId();
        
        // Get note
        $note = $this->note->getById($id);
        
        // Check if note exists and belongs to the user
        if (!$note || $note['user_id'] != $user_id) {
            Session::setFlash('error', 'Note not found or access denied');
            header('Location: ' . BASE_URL . '/notes');
            exit;
        }
        
        // Remove share
        $result = $this->sharedNote->removeShare($share_id, $user_id);
        
        if ($result['success']) {
            Session::setFlash('success', 'Sharing removed successfully');
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        // Redirect back to share page
        header('Location: ' . BASE_URL . '/notes/share/' . $id);
        exit;
    }
    
    // View shared notes
    public function shared() {
        $user_id = Session::getUserId();
        
        // Get notes shared with the user
        $notes = $this->sharedNote->getNotesSharedWithUser($user_id);
        
        // Set page data
        $data = [
            'pageTitle' => 'Shared Notes',
            'pageStyles' => ['notes'],
            'pageScripts' => ['notes'],
            'notes' => $notes
        ];
        
        // Load view
        include VIEWS_PATH . '/components/header.php';
        include VIEWS_PATH . '/notes/shared.php';
        include VIEWS_PATH . '/components/footer.php';
    }
    
    // Helper methods
    
    // Process image uploads for a note
    private function processImageUploads($note_id) {
        // Check if upload directory exists, create if not
        if (!file_exists(UPLOADS_PATH)) {
            mkdir(UPLOADS_PATH, 0755, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $uploaded_files = [];
        $errors = [];
        
        foreach ($_FILES['images']['name'] as $key => $name) {
            if (empty($name)) continue;
            
            $tmp_name = $_FILES['images']['tmp_name'][$key];
            $type = $_FILES['images']['type'][$key];
            $size = $_FILES['images']['size'][$key];
            $error = $_FILES['images']['error'][$key];
            
            // Check for errors
            if ($error !== UPLOAD_ERR_OK) {
                $errors[] = "Upload error for file $name: " . $this->getUploadErrorMessage($error);
                continue;
            }
            
            // Check file type
            if (!in_array($type, $allowed_types)) {
                $errors[] = "Invalid file type for $name. Allowed types: JPEG, PNG, GIF";
                continue;
            }
            
            // Check file size
            if ($size > $max_size) {
                $errors[] = "File $name is too large. Maximum size: 5MB";
                continue;
            }
            
            // Generate unique filename
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $new_filename = uniqid('note_' . $note_id . '_') . '.' . $ext;
            $destination = UPLOADS_PATH . '/' . $new_filename;
            
            // Move the file
            if (move_uploaded_file($tmp_name, $destination)) {
                $uploaded_files[] = [
                    'note_id' => $note_id,
                    'file_name' => $name,
                    'file_path' => $new_filename
                ];
            } else {
                $errors[] = "Failed to save file $name";
            }
        }
        
        // Save files to database
        if (!empty($uploaded_files)) {
            foreach ($uploaded_files as $file) {
                $this->note->addImage($file['note_id'], $file['file_name'], $file['file_path']);
            }
        }
        
        return [
            'success' => empty($errors),
            'files' => $uploaded_files,
            'errors' => $errors
        ];
    }
    
    // Get upload error message
    private function getUploadErrorMessage($error_code) {
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