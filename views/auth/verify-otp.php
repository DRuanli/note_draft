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
                <h2>Verify OTP</h2>
                <p>Enter the verification code sent to your email</p>
            </div>
            
            <?php if (Session::hasFlash('success')): ?>
                <div class="alert alert-success">
                    <?= Session::getFlash('success') ?>
                </div>
            <?php endif; ?>
            
            <?php if (Session::hasFlash('error')): ?>
                <div class="alert alert-danger">
                    <?= Session::getFlash('error') ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($data['errors']['general'])): ?>
                <div class="alert alert-danger">
                    <?= $data['errors']['general'] ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($data['email']) ?>" required>
                    </div>
                    <?php if (!empty($data['errors']['email'])): ?>
                        <div class="error-message"><?= $data['errors']['email'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="otp">Verification Code (OTP)</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-key"></i></span>
                        <input type="text" name="otp" id="otp" required minlength="6" maxlength="6" 
                               pattern="[0-9]{6}" inputmode="numeric">
                    </div>
                    <?php if (!empty($data['errors']['otp'])): ?>
                        <div class="error-message"><?= $data['errors']['otp'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-action">
                    <button type="submit" class="btn btn-primary">Verify Code</button>
                </div>
            </form>
            
            <div class="auth-footer">
                <p>
                    <a href="<?= BASE_URL ?>/reset-password">
                        <i class="fas fa-arrow-left"></i> Back to Password Reset
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>