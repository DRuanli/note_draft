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
                <h2>Create Account</h2>
                <p>Sign up to start managing your notes</p>
            </div>
            
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
                    <label for="display_name">Display Name</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                        <input type="text" name="display_name" id="display_name" value="<?= htmlspecialchars($data['display_name']) ?>" required>
                    </div>
                    <?php if (!empty($data['errors']['display_name'])): ?>
                        <div class="error-message"><?= $data['errors']['display_name'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
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
                    <label for="confirm_password">Confirm Password</label>
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
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="<?= BASE_URL ?>/login">Sign In</a></p>
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
    </script>
</body>
</html>