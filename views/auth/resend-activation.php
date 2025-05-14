<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['title'] ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><?= APP_NAME ?></h1>
                <h2>Resend Activation Email</h2>
                <p>Enter your email to receive a new activation link</p>
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
                
                <div class="form-action">
                    <button type="submit" class="btn btn-primary">Resend Activation Email</button>
                </div>
            </form>
            
            <div class="auth-footer">
                <p>Remember your password? <a href="<?= BASE_URL ?>/login">Sign In</a></p>
            </div>
        </div>
    </div>
</body>
</html>