<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['title'] ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/auth.css">
    <!-- Add FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><?= APP_NAME ?></h1>
                <h2>Account Activation</h2>
            </div>
            
            <div class="activation-result">
                <?php if ($data['success']): ?>
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="success-message">
                        <h3>Success!</h3>
                        <p><?= $data['message'] ?></p>
                    </div>
                <?php else: ?>
                    <div class="error-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="error-message">
                        <h3>Activation Failed</h3>
                        <p><?= $data['message'] ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="action-buttons">
                <?php if (Session::isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>" class="btn btn-primary">Go to Dashboard</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login" class="btn btn-primary">Go to Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>