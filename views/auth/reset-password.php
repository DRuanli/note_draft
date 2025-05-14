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
                <h2><?= $data['step'] === 'request' ? 'Reset Password' : 'Set New Password' ?></h2>
                <p><?= $data['step'] === 'request' ? 'Enter your email to receive a password reset link' : 'Create a new password for your account' ?></p>
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
            
            <?php if ($data['step'] === 'request'): ?>
                <!-- Password Reset Request Form -->
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
                        <button type="submit" class="btn btn-primary">Send Reset Link</button>
                    </div>
                </form>
            <?php else: ?>
                <!-- New Password Form -->
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="input-group">
                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" id="password" required minlength="8">
                            <span class="toggle-password" onclick="togglePasswordVisibility('password')">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <?php if (!empty($data['errors']['password'])): ?>
                            <div class="error-message"><?= $data['errors']['password'] ?></div>
                        <?php endif; ?>
                        <div class="password-strength" id="password-strength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="input-group">
                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                            <input type="password" name="confirm_password" id="confirm_password" required minlength="8">
                            <span class="toggle-password" onclick="togglePasswordVisibility('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <?php if (!empty($data['errors']['confirm_password'])): ?>
                            <div class="error-message"><?= $data['errors']['confirm_password'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-action">
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>
            <?php endif; ?>
            
            <div class="auth-footer">
                <p>
                    <a href="<?= BASE_URL ?>/login">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        function togglePasswordVisibility(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = passwordInput.parentElement.querySelector('.toggle-password i');
            
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
        
        <?php if ($data['step'] === 'new_password'): ?>
        // Password strength meter
        const passwordInput = document.getElementById('password');
        const strengthMeter = document.getElementById('password-strength');
        
        passwordInput.addEventListener('input', function() {
            const value = passwordInput.value;
            let strength = 0;
            
            // Length check
            if (value.length >= 8) strength += 1;
            if (value.length >= 12) strength += 1;
            
            // Character type checks
            if (/[0-9]/.test(value)) strength += 1;
            if (/[a-z]/.test(value)) strength += 1;
            if (/[A-Z]/.test(value)) strength += 1;
            if (/[^A-Za-z0-9]/.test(value)) strength += 1;
            
            // Update the strength meter
            strengthMeter.innerHTML = '';
            for (let i = 0; i < 5; i++) {
                const bar = document.createElement('div');
                bar.className = 'strength-bar';
                if (i < strength) {
                    if (strength <= 2) {
                        bar.classList.add('weak');
                    } else if (strength <= 4) {
                        bar.classList.add('medium');
                    } else {
                        bar.classList.add('strong');
                    }
                }
                strengthMeter.appendChild(bar);
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>