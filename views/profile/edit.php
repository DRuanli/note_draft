<?php
// views/profile/edit.php
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
                            <a class="nav-item nav-link active" href="<?= BASE_URL ?>/profile/edit">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                            <a class="nav-item nav-link" href="<?= BASE_URL ?>/profile/change-password">
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
                <!-- Profile Photo Section -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm profile-card h-100">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0">Profile Photo</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="avatar-container mb-3">
                                <?php if (isset($data['user']['avatar_path']) && !empty($data['user']['avatar_path'])): ?>
                                    <img src="<?= BASE_URL ?>/uploads/avatars/<?= $data['user']['avatar_path'] ?>?v=<?= time() ?>" 
                                        alt="Avatar" class="img-fluid rounded-circle profile-avatar" 
                                        style="width: 150px; height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="avatar-placeholder rounded-circle mx-auto d-flex align-items-center justify-content-center bg-light" 
                                         style="width: 150px; height: 150px;">
                                        <i class="fas fa-user fa-4x text-secondary"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <form method="POST" action="<?= BASE_URL ?>/profile/upload-avatar" enctype="multipart/form-data" id="avatar-form">
                                <div class="mb-3">
                                    <label for="avatar" class="form-label visually-hidden">Choose Image</label>
                                    <input class="form-control form-control-sm" id="avatar" name="avatar" type="file" accept="image/jpeg,image/png">
                                    <div class="form-text">Max file size: 2MB. JPG or PNG only.</div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm" id="upload-avatar-btn">
                                        <i class="fas fa-upload me-1"></i> Upload Photo
                                    </button>
                                    
                                    <?php if (isset($data['user']['avatar_path']) && !empty($data['user']['avatar_path'])): ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm" name="remove_avatar" value="1" id="remove-avatar-btn">
                                            <i class="fas fa-trash me-1"></i> Remove Photo
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Details Section -->
                <div class="col-md-8 mb-4">
                    <div class="card shadow-sm profile-card h-100">
                        <div class="card-header bg-transparent">
                            <h5 class="card-title mb-0">Account Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>/profile/edit" id="profile-form">
                                <div class="mb-3">
                                    <label for="display_name" class="form-label">Display Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control <?= !empty($data['errors']['display_name']) ? 'is-invalid' : '' ?>" 
                                               id="display_name" name="display_name" 
                                               value="<?= htmlspecialchars($data['user']['display_name']) ?>" required>
                                        <?php if (!empty($data['errors']['display_name'])): ?>
                                            <div class="invalid-feedback"><?= $data['errors']['display_name'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control bg-light" id="email" 
                                               value="<?= htmlspecialchars($data['user']['email']) ?>" disabled>
                                    </div>
                                    <div class="form-text">Email address cannot be changed.</div>
                                </div>
                                
                                <?php if (isset($data['user']['is_activated']) && !$data['user']['is_activated']): ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Your account is not verified.
                                        <form action="<?= BASE_URL ?>/resend-activation" method="POST" class="d-inline">
                                            <input type="hidden" name="resend" value="1">
                                            <button type="submit" class="btn btn-link p-0 d-inline">Resend activation email</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                    <a href="<?= BASE_URL ?>/profile/change-password" class="btn btn-outline-secondary">
                                        <i class="fas fa-key me-1"></i> Change Password
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="save-profile-btn">
                                        <i class="fas fa-save me-1"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced CSS for Profile Pages -->
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

/* Profile Cards */
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

/* Avatar Styling */
.avatar-container {
    position: relative;
    margin: 0 auto 25px;
}

.profile-avatar, .avatar-placeholder {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 50%;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border: 5px solid white;
    transition: var(--transition);
}

.avatar-placeholder {
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-container:hover .profile-avatar, 
.avatar-container:hover .avatar-placeholder {
    transform: scale(1.05);
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
    // Avatar preview functionality
    const avatarInput = document.getElementById('avatar');
    const avatarContainer = document.querySelector('.avatar-container');
    const avatarForm = document.getElementById('avatar-form');
    const uploadAvatarBtn = document.getElementById('upload-avatar-btn');
    const profileForm = document.getElementById('profile-form');
    const saveProfileBtn = document.getElementById('save-profile-btn');
    
    // Handle avatar file selection
    if (avatarInput && avatarContainer) {
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Validate file size
                if (file.size > 2 * 1024 * 1024) {
                    alert('File is too large. Maximum size is 2MB.');
                    this.value = ''; // Clear the input
                    return;
                }
                
                // Validate file type
                if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
                    alert('Invalid file type. Only JPG and PNG are allowed.');
                    this.value = ''; // Clear the input
                    return;
                }
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Create or update preview image
                    let avatarPreview = avatarContainer.querySelector('img');
                    
                    if (!avatarPreview) {
                        // Remove placeholder if it exists
                        const placeholder = avatarContainer.querySelector('.avatar-placeholder');
                        if (placeholder) {
                            avatarContainer.removeChild(placeholder);
                        }
                        
                        // Create new image element
                        avatarPreview = document.createElement('img');
                        avatarPreview.className = 'img-fluid rounded-circle profile-avatar';
                        avatarPreview.style.width = '150px';
                        avatarPreview.style.height = '150px';
                        avatarPreview.style.objectFit = 'cover';
                        avatarContainer.appendChild(avatarPreview);
                    }
                    
                    // Set the preview source
                    avatarPreview.src = e.target.result;
                    avatarPreview.alt = 'Avatar Preview';
                };
                
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Add loading state to buttons when clicked
    if (uploadAvatarBtn) {
        avatarForm.addEventListener('submit', function() {
            if (avatarInput.files.length > 0 || document.querySelector('button[name="remove_avatar"]')) {
                uploadAvatarBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Uploading...';
                uploadAvatarBtn.disabled = true;
            }
        });
    }
    
    if (saveProfileBtn) {
        profileForm.addEventListener('submit', function() {
            saveProfileBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';
            saveProfileBtn.disabled = true;
        });
    }
});
</script>