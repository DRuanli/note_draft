<?php
require_once MODELS_PATH . '/User.php';

// Default preferences
$theme = 'light';
$font_size = 'medium';
$note_color = 'white';

// Get user preferences if logged in
if (Session::isLoggedIn()) {
    $user_id = Session::getUserId();
    $userModel = new User();
    $preferences = $userModel->getUserPreferences($user_id);
    
    // Apply preferences
    $theme = $preferences['theme'] ?? 'light';
    $font_size = $preferences['font_size'] ?? 'medium';
    $note_color = $preferences['note_color'] ?? 'white';
}

// Font size classes
$font_size_class = '';
switch ($font_size) {
    case 'small':
        $font_size_class = 'font-size-small';
        break;
    case 'medium':
        $font_size_class = 'font-size-medium';
        break;
    case 'large':
        $font_size_class = 'font-size-large';
        break;
}

// Get unread notifications if user is logged in
$unread_notifications = [];
$unread_count = 0;
if (Session::isLoggedIn()) {
    require_once MODELS_PATH . '/Notification.php';
    $notificationModel = new Notification();
    $user_id = Session::getUserId();
    $unread_notifications = $notificationModel->getUnreadNotifications($user_id);
    $unread_count = count($unread_notifications);
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?><?= APP_NAME ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="<?= ASSETS_URL ?>/img/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Base CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/main.css">
    
    <!-- Page-specific CSS -->
    <?php if (isset($pageStyles) && is_array($pageStyles)): ?>
        <?php foreach ($pageStyles as $style): ?>
            <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/<?= $style ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- PWA support -->
    <?php if (defined('ENABLE_OFFLINE_MODE') && ENABLE_OFFLINE_MODE): ?>
        <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
        <meta name="theme-color" content="#4a89dc">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="<?= APP_NAME ?>">
        <link rel="apple-touch-icon" href="<?= ASSETS_URL ?>/img/icon-192x192.png">
    <?php endif; ?>
    
    <!-- Enhanced styles for header -->
    <style>
        :root {
            --primary-color: #4a89dc;
            --primary-hover: #3a77c5;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
            --border-radius: 12px;
            --button-radius: 10px;
            --small-radius: 8px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        /* Font size preferences */
        .font-size-small {
            font-size: 0.875rem !important;
        }
        .font-size-medium {
            font-size: 1rem !important;
        }
        .font-size-large {
            font-size: 1.125rem !important;
        }
        
        /* Note color preferences */
        .note-color-white .note-card,
        .note-color-white .card.h-100,
        .note-color-white .card-body {
            background-color: #ffffff !important;
        }
        .note-color-blue .note-card,
        .note-color-blue .card.h-100,
        .note-color-blue .card-body {
            background-color: #f0f5ff !important;
        }
        .note-color-green .note-card,
        .note-color-green .card.h-100,
        .note-color-green .card-body {
            background-color: #f0fff5 !important;
        }
        .note-color-yellow .note-card,
        .note-color-yellow .card.h-100,
        .note-color-yellow .card-body {
            background-color: #fffbeb !important;
        }
        .note-color-purple .note-card,
        .note-color-purple .card.h-100,
        .note-color-purple .card-body {
            background-color: #f8f0ff !important;
        }
        .note-color-pink .note-card,
        .note-color-pink .card.h-100,
        .note-color-pink .card-body {
            background-color: #fff0f7 !important;
        }

        /* Dark mode adjustments for note colors */
        [data-bs-theme="dark"] .note-color-white .note-card,
        [data-bs-theme="dark"] .note-color-white .card.h-100,
        [data-bs-theme="dark"] .note-color-white .card-body {
            background-color: #2b2b2b !important;
        }
        [data-bs-theme="dark"] .note-color-blue .note-card,
        [data-bs-theme="dark"] .note-color-blue .card.h-100,
        [data-bs-theme="dark"] .note-color-blue .card-body {
            background-color: #1a2035 !important;
        }
        [data-bs-theme="dark"] .note-color-green .note-card,
        [data-bs-theme="dark"] .note-color-green .card.h-100,
        [data-bs-theme="dark"] .note-color-green .card-body {
            background-color: #1a2e22 !important;
        }
        [data-bs-theme="dark"] .note-color-yellow .note-card,
        [data-bs-theme="dark"] .note-color-yellow .card.h-100,
        [data-bs-theme="dark"] .note-color-yellow .card-body {
            background-color: #2e2a1a !important;
        }
        [data-bs-theme="dark"] .note-color-purple .note-card,
        [data-bs-theme="dark"] .note-color-purple .card.h-100,
        [data-bs-theme="dark"] .note-color-purple .card-body {
            background-color: #25192e !important;
        }
        [data-bs-theme="dark"] .note-color-pink .note-card,
        [data-bs-theme="dark"] .note-color-pink .card.h-100,
        [data-bs-theme="dark"] .note-color-pink .card-body {
            background-color: #2e1923 !important;
        }
        
        /* Enhanced Navigation Bar */
        .navbar {
            padding: 1rem 0;
            transition: var(--transition);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-dark {
            background: linear-gradient(to right,rgb(64, 120, 193), #5a9de9) !important;
        }
        
        .navbar-brand {
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }
        
        .navbar-brand:hover {
            transform: translateY(-2px);
        }
        
        .navbar-brand i {
            font-size: 1.25rem;
            margin-right: 0.5rem;
            transition: transform 0.3s ease;
        }
        
        .navbar-brand:hover i {
            transform: rotate(-10deg);
        }
        
        .nav-item {
            margin: 0 2px;
            position: relative;
        }
        
        .nav-link {
            border-radius: var(--small-radius);
            padding: 0.6rem 1rem;
            font-weight: 500;
            position: relative;
            transition: var(--transition);
        }
        
        .navbar-dark .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.85);
        }
        
        .navbar-dark .navbar-nav .nav-link:hover {
            color: white;
        }
        
        .navbar-dark .navbar-nav .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            font-weight: 600;
        }
        
        .nav-link:hover {
            transform: translateY(-2px);
        }
        
        .nav-link.active {
            position: relative;
        }
        
        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            height: 3px;
            width: 30px;
            background-color: white;
            border-radius: 3px;
        }
        
        .nav-link i {
            margin-right: 5px;
            transition: transform 0.3s ease;
        }
        
        .nav-item:hover .nav-link i {
            transform: translateY(-2px);
        }
        
        /* Dropdown Styling */
        .dropdown-menu {
            border: none;
            border-radius: var(--small-radius);
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            padding: 0.75rem 0;
            min-width: 14rem;
            animation: dropdownFadeIn 0.3s ease;
        }
        
        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .dropdown-item {
            padding: 0.6rem 1.25rem;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }
        
        .dropdown-item:hover {
            background-color: rgba(74, 137, 220, 0.1);
            color: var(--primary-color);
            padding-left: 1.5rem;
        }
        
        .dropdown-item:active, .dropdown-item:focus {
            background-color: rgba(74, 137, 220, 0.2);
            color: var(--primary-color);
        }
        
        .dropdown-item i {
            width: 1.25rem;
            margin-right: 0.5rem;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .dropdown-item:hover i {
            transform: translateX(3px);
        }
        
        .dropdown-divider {
            margin: 0.5rem 0;
            opacity: 0.1;
        }
        
        /* Notification Badge */
        .position-relative .badge {
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }
        
        /* Notification Dropdown */
        .notification-list {
            max-height: 350px;
            overflow-y: auto;
        }
        
        .notification-list::-webkit-scrollbar {
            width: 6px;
        }
        
        .notification-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .notification-list::-webkit-scrollbar-thumb {
            background: #c1d1f0;
            border-radius: 10px;
        }
        
        .notification-list::-webkit-scrollbar-thumb:hover {
            background: #a5bae6;
        }
        
        .dropdown-header {
            font-weight: 600;
            color: var(--primary-color);
            padding: 0.5rem 1rem;
        }
        
        /* User Avatar */
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-right: 0.5rem;
        }
        
        .nav-link .user-display-name {
            max-width: 120px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Account Verification Alert */
        .alert-warning {
            background: linear-gradient(45deg, #ffc107, #ffdb7e);
            border: none;
            color: rgba(0, 0, 0, 0.7);
            font-weight: 500;
        }
        
        .alert-warning .btn-link {
            font-weight: 600;
            color: rgba(0, 0, 0, 0.8);
            text-decoration: underline;
        }
        
        /* Mobile Navigation */
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            border-radius: var(--small-radius);
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        .navbar-toggler:focus {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.25);
        }
        
        .navbar-toggler-icon {
            transition: transform 0.3s ease;
        }
        
        .navbar-toggler:hover .navbar-toggler-icon {
            transform: rotate(90deg);
        }
        
        /* Responsive adjustments */
        @media (max-width: 991px) {
            .navbar-collapse {
                background-color: var(--primary-color);
                border-radius: var(--border-radius);
                padding: 1rem;
                margin-top: 1rem;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }
            
            .nav-link:hover {
                transform: translateX(5px);
            }
            
            .dropdown-menu {
                background-color: rgba(255, 255, 255, 0.1);
                box-shadow: none;
            }
            
            .dropdown-item {
                color: white;
            }
            
            .dropdown-item:hover {
                background-color: rgba(255, 255, 255, 0.2);
                color: white;
            }
            
            .dropdown-divider {
                border-color: rgba(255, 255, 255, 0.1);
            }
        }
    </style>
</head>
<script>
    // Make PHP constants available to JavaScript
    const BASE_URL = "<?= BASE_URL ?>";
    
    // Make user preferences available to JavaScript
    const USER_PREFERENCES = {
        theme: "<?= $theme ?>",
        font_size: "<?= $font_size ?>",
        note_color: "<?= $note_color ?>"
    };
    
    <?php if (Session::isLoggedIn()): ?>
    const USER_ID = <?= Session::getUserId() ?>;
    const ENABLE_WEBSOCKETS = <?= defined('ENABLE_WEBSOCKETS') && ENABLE_WEBSOCKETS ? 'true' : 'false' ?>;
    <?php endif; ?>
</script>
<body class="d-flex flex-column min-vh-100 bg-light <?= $font_size_class ?> note-color-<?= $note_color ?>" data-bs-theme="<?= $theme ?>">
    <?php if (Session::isLoggedIn()): ?>
        <header>
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
                <div class="container">
                    <a class="navbar-brand" href="<?= BASE_URL ?>">
                        <i class="fas fa-sticky-note"></i><?= APP_NAME ?>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarMain">
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/notes') !== false && !strpos($_SERVER['REQUEST_URI'], '/notes/shared') ? 'active' : '' ?>" href="<?= BASE_URL ?>/notes">
                                    <i class="fas fa-sticky-note"></i> My Notes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/notes/shared') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/notes/shared">
                                    <i class="fas fa-share-alt"></i> Shared Notes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/labels') !== false ? 'active' : '' ?>" href="<?= BASE_URL ?>/labels">
                                    <i class="fas fa-tags"></i> Labels
                                </a>
                            </li>
                        </ul>
                        <ul class="navbar-nav">
                            <!-- Include the notification dropdown -->
                            <?php include VIEWS_PATH . '/components/notification-dropdown.php'; ?>
                            
                            <!-- User Profile Dropdown -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php
                                    // Get user info
                                    $userObj = (new User())->getUserById(Session::getUserId());
                                    $hasAvatar = !empty($userObj['avatar_path']);
                                    ?>
                                    
                                    <?php if ($hasAvatar): ?>
                                        <img src="<?= BASE_URL ?>/uploads/avatars/<?= $userObj['avatar_path'] ?>?v=<?= time() ?>" alt="Avatar" class="user-avatar">
                                    <?php else: ?>
                                        <div class="user-avatar d-flex align-items-center justify-content-center bg-light text-primary">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <span class="user-display-name"><?= htmlspecialchars(Session::get('user_display_name')) ?></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/profile">
                                            <i class="fas fa-user-circle"></i> My Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/profile/edit">
                                            <i class="fas fa-edit"></i> Edit Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= BASE_URL ?>/profile/preferences">
                                            <i class="fas fa-cog"></i> Preferences
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout">
                                            <i class="fas fa-sign-out-alt"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
    <?php endif; ?>
    
    <?php 
    // Notification for unverified accounts
    if (Session::isLoggedIn()): 
        $user = (new User())->getUserById(Session::getUserId());
        if ($user && !$user['is_activated']):
    ?>
    <div class="alert alert-warning text-center mb-0 rounded-0 border-0 py-3">
        <div class="container d-flex align-items-center justify-content-center">
            <i class="fas fa-exclamation-circle me-2 fa-lg"></i>
            <span>Your account is not verified. Please check your email to complete the activation process.</span>
            <form action="<?= BASE_URL ?>/resend-activation" method="POST" class="d-inline ms-2">
                <input type="hidden" name="resend" value="1">
                <button type="submit" class="btn btn-link alert-link p-0 d-inline">Resend activation email</button>
            </form>
        </div>
    </div>
    <?php 
        endif;
    endif;
    ?>
    
    <main class="flex-grow-1 py-4">
        <div class="container"><?= PHP_EOL ?>