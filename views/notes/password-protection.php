<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm border-0 rounded-4 password-protection-card">
                <div class="card-header bg-white p-4 border-bottom d-flex align-items-center">
                    <div class="security-icon me-3">
                        <?php if ($data['action'] === 'enable'): ?>
                            <i class="fas fa-lock"></i>
                        <?php else: ?>
                            <i class="fas fa-unlock"></i>
                        <?php endif; ?>
                    </div>
                    <h4 class="card-title mb-0 fw-bold">
                        <?php if ($data['action'] === 'enable'): ?>
                            Add Password Protection
                        <?php else: ?>
                            Remove Password Protection
                        <?php endif; ?>
                    </h4>
                    <a href="<?= BASE_URL ?>/notes/edit/<?= $data['note']['id'] ?>" class="btn btn-outline-secondary rounded-pill ms-auto">
                        <i class="fas fa-arrow-left me-2"></i>Back to Note
                    </a>
                </div>
                
                <div class="card-body p-4">
                    <?php if (!empty($data['errors']['general'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center">
                                <div class="alert-icon me-3">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div><?= $data['errors']['general'] ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="note-summary mb-4 p-3 bg-light rounded-3 border-0 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="note-icon me-3">
                                <i class="fas fa-sticky-note"></i>
                            </div>
                            <div>
                                <div class="fw-bold mb-1 fs-5"><?= htmlspecialchars($data['note']['title']) ?></div>
                                <div class="text-muted small">
                                    <i class="far fa-clock me-1"></i> Last updated: 
                                    <?php 
                                    $updated = new DateTime($data['note']['updated_at']);
                                    echo $updated->format('M j, Y g:i A');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($data['action'] === 'enable'): ?>
                        <!-- Enable Password Protection Form -->
                        <div class="security-info-card mb-4">
                            <div class="info-header">
                                <div class="info-header-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <h5 class="info-title">About Password Protection</h5>
                            </div>
                            <div class="info-body">
                                <p class="mb-0">Adding a password will protect this note from unauthorized access. You'll need to enter this password every time you want to view, edit, or delete this note.</p>
                                <p class="mt-2 mb-0 text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i> 
                                    <strong>Important:</strong> Make sure to remember this password. If you forget it, you won't be able to access your note.
                                </p>
                            </div>
                        </div>
                        
                        <form method="POST" class="password-form needs-validation" novalidate>
                            <div class="password-input-group mb-4">
                                <label for="password" class="form-label fw-medium">Create Password</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-lock text-primary"></i>
                                    </span>
                                    <input type="password" class="form-control form-control-lg border-start-0 shadow-none <?= !empty($data['errors']['password']) ? 'is-invalid' : '' ?>" 
                                           id="password" name="password" placeholder="Enter password" required minlength="4">
                                    <button type="button" class="btn btn-outline-secondary border-start-0 toggle-password" data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if (!empty($data['errors']['password'])): ?>
                                        <div class="invalid-feedback"><?= $data['errors']['password'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="password-strength mt-2" id="password-strength">
                                    <div class="strength-bar"></div>
                                    <div class="strength-bar"></div>
                                    <div class="strength-bar"></div>
                                    <div class="strength-bar"></div>
                                </div>
                                <div class="strength-text small mt-1" id="strength-text">Password strength</div>
                            </div>
                            
                            <div class="password-input-group mb-4">
                                <label for="confirm_password" class="form-label fw-medium">Confirm Password</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-check-circle text-success"></i>
                                    </span>
                                    <input type="password" class="form-control form-control-lg border-start-0 shadow-none <?= !empty($data['errors']['confirm_password']) ? 'is-invalid' : '' ?>" 
                                           id="confirm_password" name="confirm_password" placeholder="Confirm password" required minlength="4">
                                    <button type="button" class="btn btn-outline-secondary border-start-0 toggle-password" data-target="confirm_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if (!empty($data['errors']['confirm_password'])): ?>
                                        <div class="invalid-feedback"><?= $data['errors']['confirm_password'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div id="password-match" class="form-text"></div>
                            </div>
                            
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-lock me-2"></i> Enable Password Protection
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <!-- Disable Password Protection Form -->
                        <div class="security-info-card warning mb-4">
                            <div class="info-header">
                                <div class="info-header-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <h5 class="info-title">Remove Password Protection</h5>
                            </div>
                            <div class="info-body">
                                <p class="mb-0">You are about to remove password protection from this note. After this action, anyone with access to your account will be able to view this note.</p>
                                <p class="mt-2 mb-0 text-danger">
                                    <i class="fas fa-shield-alt me-1"></i> 
                                    <strong>Verification Required:</strong> Please enter the current password to confirm this action.
                                </p>
                            </div>
                        </div>
                        
                        <form method="POST" class="password-form needs-validation" novalidate>
                            <div class="password-input-group mb-4">
                                <label for="current_password" class="form-label fw-medium">Current Password</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-key text-warning"></i>
                                    </span>
                                    <input type="password" class="form-control form-control-lg border-start-0 shadow-none <?= !empty($data['errors']['current_password']) ? 'is-invalid' : '' ?>" 
                                           id="current_password" name="current_password" placeholder="Enter current password" required>
                                    <button type="button" class="btn btn-outline-secondary border-start-0 toggle-password" data-target="current_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if (!empty($data['errors']['current_password'])): ?>
                                        <div class="invalid-feedback"><?= $data['errors']['current_password'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-warning btn-lg">
                                    <i class="fas fa-unlock me-2"></i> Remove Password Protection
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary-color: #4a89dc;
    --primary-hover: #3a77c5;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --info-color: #17a2b8;
    --secondary-color: #6c757d;
    --light-bg: #f8f9fa;
    --border-radius: 16px;
    --small-radius: 8px;
    --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    --transition: all 0.3s ease;
}

/* Main card styling */
.password-protection-card {
    transition: var(--transition);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    border-radius: var(--border-radius) !important;
    overflow: hidden;
    border: none !important;
    transform: translateY(0);
}

.password-protection-card:hover {
    transform: translateY(-5px);
}

/* Security icon in header */
.security-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.password-protection-card .card-header .security-icon {
    background: linear-gradient(135deg, #4a89dc, #6ea8fe);
}

.password-protection-card[data-action="disable"] .card-header .security-icon {
    background: linear-gradient(135deg, #ffc107, #ffe082);
}

/* Note summary styling */
.note-summary {
    transition: var(--transition);
}

.note-summary:hover {
    transform: translateY(-3px);
}

.note-icon {
    width: 48px;
    height: 48px;
    background-color: rgba(74, 137, 220, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    font-size: 1.5rem;
}

/* Security info card */
.security-info-card {
    background-color: rgba(74, 137, 220, 0.05);
    border-radius: var(--small-radius);
    overflow: hidden;
    transition: var(--transition);
}

.security-info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.security-info-card.warning {
    background-color: rgba(255, 193, 7, 0.05);
}

.info-header {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    background-color: rgba(74, 137, 220, 0.1);
}

.security-info-card.warning .info-header {
    background-color: rgba(255, 193, 7, 0.1);
}

.info-header-icon {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #4a89dc, #6ea8fe);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    margin-right: 15px;
}

.security-info-card.warning .info-header-icon {
    background: linear-gradient(135deg, #ffc107, #ffe082);
    color: #856404;
}

.info-title {
    margin: 0;
    font-weight: 600;
    color: #343a40;
}

.info-body {
    padding: 20px;
    color: #495057;
}

/* Form styling */
.password-form {
    margin-top: 2rem;
}

.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.5rem;
}

.form-control {
    border-radius: var(--small-radius);
    padding: 0.75rem 1rem;
    border: 1px solid rgba(0, 0, 0, 0.1);
    transition: var(--transition);
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(74, 137, 220, 0.2);
}

/* Password visibility toggle */
.toggle-password {
    cursor: pointer;
    background-color: white;
    border-top-right-radius: var(--small-radius) !important;
    border-bottom-right-radius: var(--small-radius) !important;
    transition: var(--transition);
}

.toggle-password:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

/* Password strength meter */
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
    background-color: #e9ecef;
    border-radius: 3px;
    transition: all 0.3s ease;
}

.strength-bar.weak {
    background: linear-gradient(45deg, #dc3545, #ff6b6b);
}

.strength-bar.medium {
    background: linear-gradient(45deg, #ffc107, #ffe082);
}

.strength-bar.strong {
    background: linear-gradient(45deg, #28a745, #5dd879);
}

.strength-text {
    color: #6c757d;
}

/* Button styling */
.btn {
    border-radius: var(--small-radius);
    padding: 0.7rem 1.5rem;
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
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(74, 137, 220, 0.3);
}

.btn-warning {
    background: linear-gradient(45deg, #ffc107, #ffe082);
    border: none;
    color: #856404;
}

.btn-warning:hover {
    background: linear-gradient(45deg, #e0a800, #ffc107);
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(255, 193, 7, 0.3);
    color: #856404;
}

.btn-outline-secondary {
    color: var(--secondary-color);
    border: 1px solid var(--secondary-color);
    background: transparent;
}

.btn-outline-secondary:hover {
    background-color: var(--secondary-color);
    color: white;
    transform: translateY(-3px);
}

/* Alert styling */
.alert {
    border-radius: var(--small-radius);
}

.alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: #721c24;
}

.alert-icon {
    font-size: 1.5rem;
    color: #dc3545;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .card-header .btn {
        margin-top: 1rem;
        margin-left: 0 !important;
    }
    
    .security-icon {
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Apply animation on page load
    const card = document.querySelector('.password-protection-card');
    if (card) {
        card.setAttribute('data-action', '<?= $data['action'] ?>');
        
        // Animate card entry
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    }
    
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
    const passwordInput = document.getElementById('password');
    const strengthBars = document.querySelectorAll('.strength-bar');
    const strengthText = document.getElementById('strength-text');
    
    if (passwordInput && strengthBars.length > 0 && strengthText) {
        passwordInput.addEventListener('input', function() {
            const value = passwordInput.value;
            let strength = 0;
            
            // Clear all classes first
            strengthBars.forEach(bar => {
                bar.className = 'strength-bar';
            });
            
            if (value.length === 0) {
                strengthText.textContent = 'Password strength';
                strengthText.className = 'strength-text small mt-1 text-muted';
                return;
            }
            
            // Length check
            if (value.length >= 4) strength += 1;
            if (value.length >= 8) strength += 1;
            
            // Character type checks
            if (/[0-9]/.test(value)) strength += 1;
            if (/[a-z]/.test(value)) strength += 1;
            if (/[A-Z]/.test(value)) strength += 1;
            if (/[^A-Za-z0-9]/.test(value)) strength += 1;
            
            // Update strength text
            if (strength <= 2) {
                strengthText.textContent = 'Password strength: Weak';
                strengthText.className = 'strength-text small mt-1 text-danger';
            } else if (strength <= 4) {
                strengthText.textContent = 'Password strength: Medium';
                strengthText.className = 'strength-text small mt-1 text-warning';
            } else {
                strengthText.textContent = 'Password strength: Strong';
                strengthText.className = 'strength-text small mt-1 text-success';
            }
            
            // Update bars
            for (let i = 0; i < strengthBars.length; i++) {
                if (i < strength) {
                    if (strength <= 2) {
                        strengthBars[i].classList.add('weak');
                    } else if (strength <= 4) {
                        strengthBars[i].classList.add('medium');
                    } else {
                        strengthBars[i].classList.add('strong');
                    }
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
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                // Show loading state on button
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
                    submitBtn.disabled = true;
                    
                    // Store original text for potential recovery
                    submitBtn.setAttribute('data-original-text', originalText);
                }
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Focus first input field
    const firstInput = document.querySelector('.password-form input:first-of-type');
    if (firstInput) {
        firstInput.focus();
    }
});
</script>