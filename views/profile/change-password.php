<?php
// This file replaces views/profile/change-password.php
?>
<div class="container py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Profile Navigation Tabs -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <nav class="profile-nav">
                        <div class="nav nav-tabs nav-fill" id="profile-tabs">
                            <a class="nav-item nav-link" href="<?= BASE_URL ?>/profile">
                                <i class="fas fa-user-circle me-2"></i>Profile
                            </a>
                            <a class="nav-item nav-link" href="<?= BASE_URL ?>/profile/edit">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                            <a class="nav-item nav-link active" href="<?= BASE_URL ?>/profile/change-password">
                                <i class="fas fa-key me-2"></i>Security
                            </a>
                            <a class="nav-item nav-link" href="<?= BASE_URL ?>/profile/preferences">
                                <i class="fas fa-cog me-2"></i>Preferences
                            </a>
                        </div>
                    </nav>
                </div>
            </div>
            
            <?php if (Session::hasFlash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= Session::getFlash('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (Session::hasFlash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= Session::getFlash('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div class="card shadow-sm profile-card">
                        <div class="card-header d-flex align-items-center">
                            <div class="security-icon me-3">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h5 class="card-title mb-0">Change Password</h5>
                        </div>
                        
                        <div class="card-body">
                            <?php if (!empty($data['errors']['general'])): ?>
                                <div class="alert alert-danger">
                                    <?= $data['errors']['general'] ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="password-info mb-4">
                                <div class="alert alert-info" role="alert">
                                    <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Password Requirements</h6>
                                    <ul class="mb-0 ps-3">
                                        <li>Minimum 8 characters in length</li>
                                        <li>Include at least one uppercase letter</li>
                                        <li>Include at least one number</li>
                                        <li>Include at least one special character</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <form method="POST" class="password-form">
                                <div class="mb-4">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" id="current_password" name="current_password" 
                                               class="form-control <?= !empty($data['errors']['current_password']) ? 'is-invalid' : '' ?>" required>
                                        <span class="input-group-text toggle-password" data-target="current_password" style="cursor:pointer">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                        <?php if (!empty($data['errors']['current_password'])): ?>
                                            <div class="invalid-feedback"><?= $data['errors']['current_password'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        <input type="password" id="new_password" name="new_password" 
                                               class="form-control <?= !empty($data['errors']['new_password']) ? 'is-invalid' : '' ?>" 
                                               required minlength="8">
                                        <span class="input-group-text toggle-password" data-target="new_password" style="cursor:pointer">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                        <?php if (!empty($data['errors']['new_password'])): ?>
                                            <div class="invalid-feedback"><?= $data['errors']['new_password'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="password-strength mt-2" id="password-strength">
                                        <div class="strength-bar"></div>
                                        <div class="strength-bar"></div>
                                        <div class="strength-bar"></div>
                                        <div class="strength-bar"></div>
                                        <div class="strength-bar"></div>
                                    </div>
                                    <div class="strength-text small mt-1" id="strength-text">Password strength: Too weak</div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                                        <input type="password" id="confirm_password" name="confirm_password" 
                                               class="form-control <?= !empty($data['errors']['confirm_password']) ? 'is-invalid' : '' ?>" 
                                               required minlength="8">
                                        <span class="input-group-text toggle-password" data-target="confirm_password" style="cursor:pointer">
                                            <i class="fas fa-eye"></i>
                                        </span>
                                        <?php if (!empty($data['errors']['confirm_password'])): ?>
                                            <div class="invalid-feedback"><?= $data['errors']['confirm_password'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div id="password-match" class="form-text"></div>
                                </div>
                                
                                <div class="d-grid gap-2 mt-4 pt-3 border-top">
                                    <button type="submit" class="btn btn-primary" id="change-pwd-btn">
                                        <i class="fas fa-shield-alt me-1"></i> Change Password
                                    </button>
                                    <a href="<?= BASE_URL ?>/profile" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced CSS for Change Password Page -->
<style>
:root {
    --primary-color: #4a89dc;
    --primary-hover: #3a77c5;
    --secondary-color: #6c757d;
    --light-bg: #f8f9fa;
    --border-radius: 12px;
    --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    --transition: all 0.3s ease;
}

/* Profile Navigation */
.profile-nav {
    background-color: white;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.profile-nav .nav-tabs {
    border: none;
    padding: 0;
}

.profile-nav .nav-link {
    border: none;
    padding: 1.25rem 1rem;
    font-weight: 600;
    color: var(--secondary-color);
    transition: var(--transition);
    position: relative;
}

.profile-nav .nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 3px;
    background-color: var(--primary-color);
    transition: all 0.3s ease;
    transform: translateX(-50%);
    opacity: 0;
}

.profile-nav .nav-link.active::after {
    width: 80%;
    opacity: 1;
}

.profile-nav .nav-link.active {
    color: var(--primary-color);
    background-color: rgba(74, 137, 220, 0.05);
}

.profile-nav .nav-link:hover:not(.active) {
    background-color: rgba(0, 0, 0, 0.02);
    color: #495057;
}

.profile-nav .nav-link i {
    transition: transform 0.3s ease;
}

.profile-nav .nav-link:hover i {
    transform: translateY(-2px);
}

/* Profile Card */
.profile-card {
    border: none;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    background-color: white;
}

.profile-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
}

.profile-card .card-header {
    background-color: white;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 20px;
}

.profile-card .card-body {
    padding: 25px;
}

/* Security Icon */
.security-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #4a89dc, #6ea8fe);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

/* Input Groups */
.input-group {
    margin-bottom: 0.5rem;
}

.input-group-text {
    background-color: var(--light-bg);
    border-color: rgba(0, 0, 0, 0.1);
    color: var(--secondary-color);
}

.form-control {
    border-color: rgba(0, 0, 0, 0.1);
    transition: var(--transition);
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(74, 137, 220, 0.2);
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #495057;
}

/* Password Strength Meter */
.password-strength {
    display: flex;
    height: 6px;
    gap: 4px;
    border-radius: 3px;
    overflow: hidden;
}

.strength-bar {
    flex: 1;
    height: 100%;
    background-color: #ecf0f1;
    border-radius: 3px;
    transition: all 0.3s ease;
}

.strength-bar.weak {
    background: linear-gradient(45deg, #e74c3c, #ff7675);
}

.strength-bar.medium {
    background: linear-gradient(45deg, #f39c12, #fdcb6e);
}

.strength-bar.strong {
    background: linear-gradient(45deg, #2ecc71, #55efc4);
}

/* Toggle Password Visibility */
.toggle-password {
    cursor: pointer;
    background-color: var(--light-bg);
    transition: var(--transition);
}

.toggle-password:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

/* Buttons */
.btn {
    border-radius: 10px;
    padding: 0.6rem 1.25rem;
    font-weight: 500;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.btn::after {
    content: '';
    position: absolute;
    height: 100%;
    width: 100%;
    top: 0;
    left: -100%;
    background: linear-gradient(90deg, rgba(255,255,255,0.2), transparent);
    transition: 0.3s;
}

.btn:hover::after {
    left: 100%;
}

.btn-primary {
    background: linear-gradient(45deg, #4a89dc, #6ea8fe);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(45deg, #3a77c5, #4a89dc);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(74, 137, 220, 0.3);
}

.btn-outline-secondary {
    color: var(--secondary-color);
    border: 1px solid var(--secondary-color);
    background: transparent;
}

.btn-outline-secondary:hover {
    background-color: var(--secondary-color);
    color: white;
    transform: translateY(-2px);
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .profile-nav .nav-link {
        padding: 1rem 0.5rem;
        font-size: 0.9rem;
    }
    
    .profile-nav .nav-link i {
        display: block;
        margin: 0 auto 5px;
        font-size: 1.1rem;
    }
    
    .profile-card .card-body {
        padding: 20px 15px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });
    
    // Password strength meter
    const passwordInput = document.getElementById('new_password');
    const strengthBars = document.querySelectorAll('#password-strength .strength-bar');
    const strengthText = document.getElementById('strength-text');
    
    if (passwordInput && strengthBars.length > 0) {
        passwordInput.addEventListener('input', function() {
            const value = passwordInput.value;
            let strength = 0;
            
            // Clear all classes first
            strengthBars.forEach(bar => {
                bar.className = 'strength-bar';
            });
            
            if (value.length === 0) {
                strengthText.textContent = 'Password strength: Enter password';
                strengthText.className = 'strength-text small mt-1 text-muted';
                return;
            }
            
            // Length check
            if (value.length >= 8) strength += 1;
            if (value.length >= 12) strength += 1;
            
            // Character type checks
            if (/[0-9]/.test(value)) strength += 1;
            if (/[a-z]/.test(value)) strength += 1;
            if (/[A-Z]/.test(value)) strength += 1;
            if (/[^A-Za-z0-9]/.test(value)) strength += 1;
            
            // Update strength text
            if (strength <= 2) {
                strengthText.textContent = 'Password strength: Too weak';
                strengthText.className = 'strength-text small mt-1 text-danger';
            } else if (strength <= 4) {
                strengthText.textContent = 'Password strength: Medium';
                strengthText.className = 'strength-text small mt-1 text-warning';
            } else {
                strengthText.textContent = 'Password strength: Strong';
                strengthText.className = 'strength-text small mt-1 text-success';
            }
            
            // Update bars
            for (let i = 0; i < strength && i < strengthBars.length; i++) {
                if (strength <= 2) {
                    strengthBars[i].classList.add('weak');
                } else if (strength <= 4) {
                    strengthBars[i].classList.add('medium');
                } else {
                    strengthBars[i].classList.add('strong');
                }
            }
        });
    }
    
    // Password matching check
    const confirmInput = document.getElementById('confirm_password');
    const passwordMatch = document.getElementById('password-match');
    
    if (confirmInput && passwordInput && passwordMatch) {
        const checkPasswordMatch = function() {
            if (confirmInput.value.length === 0) {
                passwordMatch.textContent = '';
                passwordMatch.className = 'form-text';
                return;
            }
            
            if (passwordInput.value === confirmInput.value) {
                passwordMatch.textContent = 'Passwords match';
                passwordMatch.className = 'form-text text-success';
            } else {
                passwordMatch.textContent = 'Passwords do not match';
                passwordMatch.className = 'form-text text-danger';
            }
        };
        
        confirmInput.addEventListener('input', checkPasswordMatch);
        passwordInput.addEventListener('input', function() {
            if (confirmInput.value.length > 0) {
                checkPasswordMatch();
            }
        });
    }
    
    // Loading state for submit button
    const changePasswordForm = document.querySelector('.password-form');
    const submitBtn = document.getElementById('change-pwd-btn');
    
    if (changePasswordForm && submitBtn) {
        changePasswordForm.addEventListener('submit', function() {
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processing...';
            submitBtn.disabled = true;
        });
    }
});
</script>