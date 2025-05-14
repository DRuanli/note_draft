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
                <h2>Sign In</h2>
                <p>Welcome back! Please login to your account</p>
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
                    <label for="password">Password</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" id="password" required>
                        <span class="toggle-password" onclick="togglePasswordVisibility()">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <?php if (!empty($data['errors']['password'])): ?>
                        <div class="error-message"><?= $data['errors']['password'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <div class="forgot-password">
                        <a href="<?= BASE_URL ?>/reset-password">Forgot Password?</a>
                    </div>
                </div>
                
                <div class="form-action">
                    <button type="submit" class="btn btn-primary">Sign In</button>
                </div>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="<?= BASE_URL ?>/register">Register</a></p>
            </div>
        </div>
    </div>
    
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>