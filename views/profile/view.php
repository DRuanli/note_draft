<?php
// This file replaces views/profile/view.php
?>
<div class="container py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Profile Navigation Tabs -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <nav class="profile-nav">
                        <div class="nav nav-tabs nav-fill" id="profile-tabs">
                            <a class="nav-item nav-link active" href="<?= BASE_URL ?>/profile">
                                <i class="fas fa-user-circle me-2"></i>Profile
                            </a>
                            <a class="nav-item nav-link" href="<?= BASE_URL ?>/profile/edit">
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
                <!-- User Summary Card -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100 profile-card">
                        <div class="card-body text-center">
                            <div class="avatar-container mb-3">
                                <?php if (!empty($data['user']['avatar_path'])): ?>
                                    <img src="<?= BASE_URL ?>/uploads/avatars/<?= $data['user']['avatar_path'] ?>?v=<?= time() ?>" 
                                        alt="Avatar" class="img-fluid rounded-circle" 
                                        style="width: 150px; height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center bg-light mx-auto">
                                        <i class="fas fa-user fa-4x text-secondary"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <h3 class="h4 mb-1"><?= htmlspecialchars($data['user']['display_name']) ?></h3>
                            <p class="text-muted mb-3"><?= htmlspecialchars($data['user']['email']) ?></p>
                            
                            <?php if ($data['user']['is_activated']): ?>
                                <div class="badge bg-success-subtle text-success mb-3 p-2">
                                    <i class="fas fa-check-circle me-1"></i> Verified Account
                                </div>
                            <?php else: ?>
                                <div class="badge bg-warning-subtle text-warning mb-3 p-2">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Pending Verification
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2">
                                <a href="<?= BASE_URL ?>/profile/edit" class="btn btn-primary">
                                    <i class="fas fa-pen me-1"></i> Update Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- User Details Card -->
                <div class="col-md-8">
                    <div class="card shadow-sm h-100 profile-card">
                        <div class="card-header bg-transparent border-bottom-0">
                            <h5 class="card-title mb-0">Account Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="info-list">
                                <div class="info-item">
                                    <div class="info-label">Display Name</div>
                                    <div class="info-value"><?= htmlspecialchars($data['user']['display_name']) ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Email Address</div>
                                    <div class="info-value"><?= htmlspecialchars($data['user']['email']) ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Account Status</div>
                                    <div class="info-value">
                                        <?php if ($data['user']['is_activated']): ?>
                                            <span class="text-success"><i class="fas fa-check-circle me-1"></i> Verified</span>
                                        <?php else: ?>
                                            <span class="text-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i> Pending Verification
                                                <form action="<?= BASE_URL ?>/resend-activation" method="POST" class="d-inline">
                                                    <input type="hidden" name="resend" value="1">
                                                    <button type="submit" class="btn btn-link text-warning p-0 d-inline text-decoration-underline">
                                                        Resend activation email
                                                    </button>
                                                </form>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Member Since</div>
                                    <div class="info-value">
                                        <?php 
                                        $created_at = new DateTime($data['user']['created_at']);
                                        echo $created_at->format('F j, Y');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Stats Card -->
                <div class="col-12 mt-4">
                    <div class="card shadow-sm profile-card stats-card">
                        <div class="card-header bg-transparent border-bottom-0">
                            <h5 class="card-title mb-0">Account Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row row-cols-1 row-cols-md-4 g-4">
                                <div class="col">
                                    <div class="stat-box">
                                        <div class="stat-icon">
                                            <i class="fas fa-sticky-note"></i>
                                        </div>
                                        <div class="stat-details">
                                            <div class="stat-value"><?= $data['stats']['total_notes'] ?></div>
                                            <div class="stat-label">Total Notes</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col">
                                    <div class="stat-box">
                                        <div class="stat-icon">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <div class="stat-details">
                                            <div class="stat-value"><?= $data['stats']['total_labels'] ?></div>
                                            <div class="stat-label">Total Labels</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col">
                                    <div class="stat-box">
                                        <div class="stat-icon">
                                            <i class="fas fa-share-alt"></i>
                                        </div>
                                        <div class="stat-details">
                                            <div class="stat-value"><?= $data['stats']['shared_notes'] ?></div>
                                            <div class="stat-label">Shared Notes</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col">
                                    <div class="stat-box">
                                        <div class="stat-icon">
                                            <i class="fas fa-image"></i>
                                        </div>
                                        <div class="stat-details">
                                            <div class="stat-value"><?= $data['stats']['uploaded_images'] ?></div>
                                            <div class="stat-label">Uploaded Images</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    margin-bottom: 25px;
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

/* Information List */
.info-list {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.info-item {
    padding-bottom: 1.25rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    transition: var(--transition);
}

.info-item:hover {
    background-color: rgba(0, 0, 0, 0.01);
    transform: translateX(5px);
    padding-left: 10px;
    border-left: 3px solid var(--primary-color);
}

.info-label {
    flex: 0 0 180px;
    font-weight: 600;
    color: #343a40;
}

.info-value {
    flex: 1;
    color: #212529;
}

/* Stats Cards with Animations */
.stats-card {
    margin-top: 25px;
}

.stat-box {
    display: flex;
    align-items: center;
    background-color: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    height: 100%;
    transition: var(--transition);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.stat-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: var(--primary-color);
    opacity: 0;
    z-index: -1;
    transition: var(--transition);
}

.stat-box:hover {
    transform: translateY(-7px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.stat-box:hover::before {
    opacity: 0.05;
}

.stat-icon {
    font-size: 2.25rem;
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 20px;
    margin-right: 1.25rem;
    color: white;
    transition: var(--transition);
}

.stat-box:hover .stat-icon {
    transform: scale(1.1) rotate(5deg);
}

.stat-details {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.5rem;
    background: linear-gradient(45deg, var(--primary-color), #6ea8fe);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    transition: var(--transition);
}

.stat-box:hover .stat-value {
    transform: scale(1.05);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--secondary-color);
    font-weight: 500;
}

/* Individual stat styling with gradients */
.col:nth-child(1) .stat-icon {
    background: linear-gradient(135deg, #4a89dc, #5a9cef);
}

.col:nth-child(2) .stat-icon {
    background: linear-gradient(135deg, #28a745, #34ce57);
}

.col:nth-child(3) .stat-icon {
    background: linear-gradient(135deg, #17a2b8, #1fc8e3);
}

.col:nth-child(4) .stat-icon {
    background: linear-gradient(135deg, #ffc107, #ffce3a);
}

/* Status Badges */
.badge {
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 500;
    letter-spacing: 0.3px;
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
    
    .info-label {
        flex: 0 0 100%;
        margin-bottom: 0.5rem;
    }
    
    .info-value {
        flex: 0 0 100%;
    }
    
    .info-item:hover {
        transform: none;
        padding-left: 5px;
    }
    
    .stat-box {
        flex-direction: column;
        text-align: center;
        padding: 1.25rem 1rem;
    }
    
    .stat-icon {
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .avatar-container {
        margin-top: 15px;
    }
    
    .profile-card .card-body {
        padding: 20px 15px;
    }
}
</style>