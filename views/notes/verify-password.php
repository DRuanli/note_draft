<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="auth-container">
                <div class="auth-card shadow-lg border-0 rounded-4 verify-password-card">
                    <div class="card-header text-center border-0 py-4 bg-white">
                        <div class="lock-animation mb-3">
                            <div class="lock-circle">
                                <div class="lock-icon">
                                    <i class="fas fa-lock"></i>
                                </div>
                            </div>
                        </div>
                        <h3 class="mb-2 fw-bold">Password Protected Note</h3>
                        <p class="text-muted mb-0">Enter the password to unlock this note</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if (!empty($data['errors']['general'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                                <div class="d-flex align-items-center">
                                    <div class="alert-icon me-3">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                    <div><?= $data['errors']['general'] ?></div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="note-info-card mb-4">
                            <div class="note-info-icon">
                                <i class="fas fa-sticky-note"></i>
                            </div>
                            <div class="note-details">
                                <h5 class="note-title"><?= htmlspecialchars($data['note']['title']) ?></h5>
                                <div class="note-meta">
                                    <div class="meta-item">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?php 
                                        $updated = new DateTime($data['note']['updated_at']);
                                        echo $updated->format('M j, Y');
                                        ?>
                                    </div>
                                    <div class="meta-item">
                                        <i class="far fa-clock me-1"></i>
                                        <?= $updated->format('g:i A') ?>
                                    </div>
                                    
                                    <?php 
                                    // Show some note details to help user identify the note
                                    if (isset($data['note']['image_count']) && $data['note']['image_count'] > 0): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-image me-1"></i> 
                                            <?= $data['note']['image_count'] ?> image<?= $data['note']['image_count'] > 1 ? 's' : '' ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($data['note']['is_shared']) && $data['note']['is_shared']): ?>
                                        <div class="meta-item">
                                            <i class="fas fa-share-alt me-1"></i> Shared
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="security-info-wrapper mb-4">
                            <div class="security-info">
                                <div class="security-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <p class="mb-0">This note is password protected. Please enter the password to continue.</p>
                            </div>
                        </div>
                        
                        <form method="POST" class="password-form" id="verify-password-form">
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($data['redirect']) ?>">
                            
                            <div class="form-group mb-4">
                                <div class="password-field">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="fas fa-key text-primary"></i>
                                        </span>
                                        <input type="password" 
                                               class="form-control form-control-lg password-input border-start-0 shadow-none <?= !empty($data['errors']['password']) ? 'is-invalid' : '' ?>" 
                                               id="password" name="password" placeholder="Enter password" required autofocus>
                                        <button type="button" class="btn btn-outline-secondary border-start-0 toggle-password" tabindex="-1">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <?php if (!empty($data['errors']['password'])): ?>
                                        <div class="invalid-feedback d-block mt-2">
                                            <i class="fas fa-exclamation-circle me-1"></i> <?= $data['errors']['password'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-actions d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="unlock-button">
                                    <i class="fas fa-unlock-alt me-2"></i> Unlock Note
                                </button>
                                <a href="<?= BASE_URL ?>/notes" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-footer bg-white border-0 text-center py-3">
                        <div class="security-reminder">
                            <i class="fas fa-lock me-1 text-muted"></i>
                            <small class="text-muted">Your note's contents are securely encrypted</small>
                        </div>
                    </div>
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

/* Container styling */
.auth-container {
    max-width: 100%;
    margin: 0 auto;
}

/* Card styling */
.verify-password-card {
    border-radius: var(--border-radius) !important;
    overflow: hidden;
    transition: var(--transition);
    transform: translateY(0);
    border: none !important;
}

.verify-password-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
}

/* Lock animation and styling */
.lock-animation {
    position: relative;
    width: 80px;
    height: 80px;
    margin: 0 auto;
}

.lock-circle {
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(74, 137, 220, 0.1), rgba(110, 168, 254, 0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(74, 137, 220, 0.4);
    }
    70% {
        transform: scale(1.05);
        box-shadow: 0 0 0 10px rgba(74, 137, 220, 0);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(74, 137, 220, 0);
    }
}

.lock-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(45deg, #4a89dc, #6ea8fe);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    box-shadow: 0 5px 15px rgba(74, 137, 220, 0.4);
}

/* Note info card styling */
.note-info-card {
    display: flex;
    align-items: center;
    background-color: var(--light-bg);
    border-radius: var(--small-radius);
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: var(--transition);
}

.note-info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.note-info-icon {
    width: 48px;
    height: 48px;
    background-color: rgba(74, 137, 220, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    font-size: 1.5rem;
    margin-right: 16px;
    flex-shrink: 0;
}

.note-details {
    flex: 1;
}

.note-title {
    font-weight: 600;
    margin-bottom: 8px;
    color: #343a40;
    font-size: 1.1rem;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.note-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.meta-item {
    color: #6c757d;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
}

.meta-item i {
    font-size: 0.9rem;
    margin-right: 4px;
}

/* Security info styling */
.security-info-wrapper {
    border-left: 4px solid var(--primary-color);
    background-color: rgba(74, 137, 220, 0.05);
    border-radius: 0 var(--small-radius) var(--small-radius) 0;
    padding: 16px;
    transition: var(--transition);
}

.security-info-wrapper:hover {
    transform: translateX(5px);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
}

.security-info {
    display: flex;
    align-items: center;
}

.security-icon {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #4a89dc, #6ea8fe);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    margin-right: 16px;
    flex-shrink: 0;
}

/* Form styling */
.password-form {
    margin-top: 1.5rem;
}

.password-field {
    position: relative;
}

.form-control {
    border-radius: var(--small-radius) !important;
    padding: 0.75rem 1rem;
    border: 1px solid rgba(0, 0, 0, 0.1);
    transition: var(--transition);
}

.form-control:focus {
    border-color: var(--primary-color) !important;
    box-shadow: 0 0 0 0.25rem rgba(74, 137, 220, 0.2) !important;
}

.input-group-text {
    border-top-left-radius: var(--small-radius) !important;
    border-bottom-left-radius: var(--small-radius) !important;
    border-right: none !important;
}

.toggle-password {
    cursor: pointer;
    border-top-right-radius: var(--small-radius) !important;
    border-bottom-right-radius: var(--small-radius) !important;
    background-color: white;
    transition: var(--transition);
}

.toggle-password:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.invalid-feedback {
    font-size: 0.875rem;
    color: var(--danger-color);
    margin-top: 0.5rem;
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
    border: none;
}

.alert-icon {
    font-size: 1.5rem;
    color: #dc3545;
}

/* Footer styling */
.security-reminder {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    background-color: var(--light-bg);
    border-radius: 20px;
}

/* Animation keyframes */
@keyframes fadeIn {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .note-meta {
        flex-direction: column;
        gap: 6px;
    }
    
    .security-info {
        flex-direction: column;
        text-align: center;
    }
    
    .security-icon {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .card-header {
        padding: 1.5rem 1rem;
    }
    
    .card-body {
        padding: 1.5rem 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Apply animations on page load
    const card = document.querySelector('.verify-password-card');
    if (card) {
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
    const toggleButton = document.querySelector('.toggle-password');
    const passwordInput = document.getElementById('password');
    
    if (toggleButton && passwordInput) {
        toggleButton.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
            // Focus back on password input
            passwordInput.focus();
        });
    }
    
    // Automatically focus on password field
    if (passwordInput) {
        setTimeout(() => {
            passwordInput.focus();
        }, 500);
    }
    
    // Show loading state on form submit
    const form = document.getElementById('verify-password-form');
    const unlockButton = document.getElementById('unlock-button');
    
    if (form && unlockButton) {
        form.addEventListener('submit', function() {
            const isValid = form.checkValidity();
            
            if (isValid) {
                unlockButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Unlocking...';
                unlockButton.disabled = true;
            }
        });
    }
    
    // Animate elements sequentially
    const elements = [
        document.querySelector('.lock-animation'),
        document.querySelector('.note-info-card'),
        document.querySelector('.security-info-wrapper'),
        document.querySelector('.password-form')
    ];
    
    elements.forEach((element, index) => {
        if (element) {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.4s ease';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, 200 + (index * 150));
        }
    });
    
    // Keyboard shortcut: Ctrl+Enter to submit form
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            if (form) {
                form.dispatchEvent(new Event('submit'));
            }
        }
    });
});
</script>