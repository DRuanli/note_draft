<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Main Card -->
            <div class="card shadow-sm rounded-3 border-0 sharing-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="share-icon-container me-3">
                            <i class="fas fa-share-alt text-primary"></i>
                        </div>
                        <h4 class="card-title mb-0 fw-bold">Share Note</h4>
                    </div>
                    <a href="<?= BASE_URL ?>/notes/edit/<?= $data['note']['id'] ?>" class="btn btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-arrow-left me-2"></i>Back to Note
                    </a>
                </div>
                
                <div class="card-body">
                    <?php if (Session::hasFlash('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center">
                                <div class="alert-icon me-3">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div><?= Session::getFlash('success') ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (Session::hasFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-center">
                                <div class="alert-icon me-3">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div><?= Session::getFlash('error') ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
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
                    
                    <!-- Note Summary Card -->
                    <div class="card mb-4 bg-light border-0 rounded-3 summary-card">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="note-icon me-3">
                                    <i class="fas fa-sticky-note"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fw-bold mb-2"><?= htmlspecialchars($data['note']['title']) ?></h5>
                                    <div class="text-muted">
                                        <i class="far fa-clock me-1"></i> Last updated: 
                                        <?php 
                                        $updated = new DateTime($data['note']['updated_at']);
                                        echo $updated->format('M j, Y g:i A');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        <!-- Share With Others Section -->
                        <div class="col-lg-6">
                            <div class="sharing-section p-4 bg-white rounded-3 shadow-sm border-start border-primary border-4">
                                <h5 class="section-title mb-4">
                                    <i class="fas fa-paper-plane text-primary me-2"></i>Share with Others
                                </h5>
                                
                                <form method="POST" class="share-form needs-validation" novalidate>
                                    <div class="mb-4">
                                        <label for="recipient_emails" class="form-label fw-medium">Recipient Email Addresses</label>
                                        <div class="form-text mb-2">
                                            <i class="fas fa-info-circle me-1 text-primary"></i>
                                            Enter one or more email addresses of registered users (one per line or comma-separated)
                                        </div>
                                        <div class="recipients-field position-relative">
                                            <textarea class="form-control rounded-3 <?= !empty($data['errors']['recipient_emails']) ? 'is-invalid' : '' ?>" 
                                                    id="recipient_emails" name="recipient_emails" rows="3" 
                                                    placeholder="user@example.com&#10;another@example.com"></textarea>
                                            <?php if (!empty($data['errors']['recipient_emails'])): ?>
                                                <div class="invalid-feedback"><?= $data['errors']['recipient_emails'] ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label fw-medium">Permission Level</label>
                                        <div class="permission-options mt-3">
                                            <div class="permission-card mb-3">
                                                <div class="form-check permission-check">
                                                    <input class="form-check-input" type="radio" name="can_edit" id="permission_read" value="0" checked>
                                                    <label class="form-check-label" for="permission_read">
                                                        <div class="d-flex align-items-center">
                                                            <div class="permission-icon view-only me-3">
                                                                <i class="fas fa-eye"></i>
                                                            </div>
                                                            <div>
                                                                <div class="permission-title">Read only</div>
                                                                <div class="permission-desc text-muted">Recipients can only view the note</div>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="permission-card">
                                                <div class="form-check permission-check">
                                                    <input class="form-check-input" type="radio" name="can_edit" id="permission_edit" value="1">
                                                    <label class="form-check-label" for="permission_edit">
                                                        <div class="d-flex align-items-center">
                                                            <div class="permission-icon can-edit me-3">
                                                                <i class="fas fa-edit"></i>
                                                            </div>
                                                            <div>
                                                                <div class="permission-title">Can edit</div>
                                                                <div class="permission-desc text-muted">Recipients can view and edit the note</div>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-share-alt me-2"></i> Share Note
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Current Shares Section -->
                        <div class="col-lg-6">
                            <div class="current-shares-section p-4 bg-white rounded-3 shadow-sm h-100 border-start border-info border-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="section-title mb-0">
                                        <i class="fas fa-user-friends text-info me-2"></i>Current Shares
                                    </h5>
                                    <span class="badge bg-info rounded-pill py-2 px-3 fw-medium">
                                        <?= count($data['current_shares']) ?>
                                    </span>
                                </div>
                                
                                <?php if (empty($data['current_shares'])): ?>
                                    <div class="empty-shares text-center py-5">
                                        <div class="empty-icon mb-3">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <h6 class="text-muted">No shares yet</h6>
                                        <p class="text-muted mb-0">This note hasn't been shared with anyone yet.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="shares-list">
                                        <?php foreach ($data['current_shares'] as $share): ?>
                                            <div class="share-item rounded-3 mb-3 p-3 transition-all">
                                                <div class="d-flex justify-content-between">
                                                    <div class="user-info">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="user-avatar me-2">
                                                                <span><?= strtoupper(substr($share['recipient_name'], 0, 1)) ?></span>
                                                            </div>
                                                            <div class="fw-bold"><?= htmlspecialchars($share['recipient_name']) ?></div>
                                                        </div>
                                                        <div class="text-muted small recipient-email">
                                                            <i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($share['recipient_email']) ?>
                                                        </div>
                                                        <div class="mt-2 d-flex align-items-center">
                                                            <?php if ($share['can_edit']): ?>
                                                                <span class="badge bg-success me-2 py-1 px-2">
                                                                    <i class="fas fa-edit me-1"></i> Can Edit
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary me-2 py-1 px-2">
                                                                    <i class="fas fa-eye me-1"></i> Read Only
                                                                </span>
                                                            <?php endif; ?>
                                                            <span class="badge bg-light text-dark border">
                                                                <i class="far fa-calendar-alt me-1"></i> <?= (new DateTime($share['shared_at']))->format('M j, Y') ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="share-actions">
                                                        <div class="action-buttons">
                                                            <?php if ($share['can_edit']): ?>
                                                                <a href="<?= BASE_URL ?>/notes/update-share/<?= $data['note']['id'] ?>/<?= $share['id'] ?>/0" 
                                                                   class="btn btn-outline-secondary btn-sm rounded-pill" 
                                                                   data-bs-toggle="tooltip" title="Change to read-only">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="<?= BASE_URL ?>/notes/update-share/<?= $data['note']['id'] ?>/<?= $share['id'] ?>/1" 
                                                                   class="btn btn-outline-primary btn-sm rounded-pill" 
                                                                   data-bs-toggle="tooltip" title="Allow editing">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <a href="<?= BASE_URL ?>/notes/remove-share/<?= $data['note']['id'] ?>/<?= $share['id'] ?>" 
                                                               class="btn btn-outline-danger btn-sm rounded-pill remove-share" 
                                                               data-name="<?= htmlspecialchars($share['recipient_name']) ?>"
                                                               data-bs-toggle="tooltip" title="Remove share">
                                                                <i class="fas fa-times"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmRemoveModal" tabindex="-1" aria-labelledby="confirmRemoveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title" id="confirmRemoveModalLabel">Remove Share</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="modal-icon mb-3">
                    <i class="fas fa-user-times"></i>
                </div>
                <p class="mb-0">Are you sure you want to remove sharing with <strong id="share-recipient-name"></strong>?</p>
                <p class="text-muted mt-2 mb-0 small">They will no longer have access to this note.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pt-0 pb-4">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    Cancel
                </button>
                <a href="#" id="confirm-remove-btn" class="btn btn-danger px-4">
                    <i class="fas fa-times me-2"></i>Remove Access
                </a>
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
    --border-radius: 12px;
    --small-radius: 8px;
    --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    --transition: all 0.3s ease;
}

/* Main sharing card styling */
.sharing-card {
    border-radius: var(--border-radius);
    overflow: hidden;
    transition: var(--transition);
    transform: translateY(0);
}

.sharing-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
}

.share-icon-container {
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

/* Summary card styling */
.summary-card {
    transition: var(--transition);
}

.summary-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.note-icon {
    width: 50px;
    height: 50px;
    background-color: rgba(74, 137, 220, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    font-size: 1.5rem;
}

/* Sharing section styling */
.sharing-section, .current-shares-section {
    border-radius: var(--border-radius);
    transition: var(--transition);
    height: 100%;
}

.sharing-section:hover, .current-shares-section:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.section-title {
    color: #343a40;
    font-weight: 600;
}

/* Form styling */
.form-label {
    font-weight: 500;
    color: #495057;
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

.recipients-field {
    position: relative;
}

.recipients-field::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    height: 2px;
    width: 0;
    background: linear-gradient(to right, var(--primary-color), #6ea8fe);
    transition: width 0.3s ease;
}

.recipients-field:focus-within::after {
    width: 100%;
}

/* Permission cards */
.permission-card {
    background-color: white;
    border-radius: var(--small-radius);
    border: 1px solid rgba(0, 0, 0, 0.1);
    transition: var(--transition);
    overflow: hidden;
}

.permission-check {
    padding: 1rem;
    margin: 0;
}

.permission-check .form-check-input {
    float: none;
    margin-left: 0;
    margin-right: 0;
    margin-top: 0;
}

.permission-check .form-check-label {
    width: 100%;
    cursor: pointer;
    padding-left: 2rem;
}

.permission-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.permission-icon.view-only {
    background: linear-gradient(135deg, #6c757d, #adb5bd);
}

.permission-icon.can-edit {
    background: linear-gradient(135deg, #28a745, #34ce57);
}

.permission-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.permission-desc {
    font-size: 0.875rem;
}

/* Permission input styling */
.form-check-input:checked + label .permission-card {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px var(--primary-color);
}

input[type="radio"]:checked + label {
    color: var(--primary-color);
}

.permission-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

/* Current shares styling */
.shares-list {
    max-height: 400px;
    overflow-y: auto;
    padding-right: 5px;
}

.shares-list::-webkit-scrollbar {
    width: 6px;
}

.shares-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.shares-list::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 10px;
}

.shares-list::-webkit-scrollbar-thumb:hover {
    background: #aaa;
}

.share-item {
    background-color: var(--light-bg);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: var(--transition);
}

.share-item:hover {
    background-color: white;
    transform: translateX(5px);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}

.user-avatar {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #17a2b8, #20c9e2);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.875rem;
}

.recipient-email {
    word-break: break-all;
}

.share-actions {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.action-buttons .btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.action-buttons .btn:hover {
    transform: translateY(-3px);
}

.action-buttons .btn-outline-danger:hover {
    background-color: var(--danger-color);
    color: white;
}

.action-buttons .btn-outline-primary:hover {
    background-color: var(--primary-color);
    color: white;
}

.action-buttons .btn-outline-secondary:hover {
    background-color: var(--secondary-color);
    color: white;
}

/* Empty state */
.empty-shares {
    padding: 1rem;
}

.empty-icon {
    font-size: 3rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

/* Alert styling */
.alert {
    border-radius: var(--small-radius);
}

.alert-success {
    background-color: rgba(40, 167, 69, 0.1);
    color: #155724;
}

.alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: #721c24;
}

.alert-icon {
    font-size: 1.5rem;
}

/* Button styling */
.btn {
    border-radius: var(--small-radius);
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

.btn-danger {
    background: linear-gradient(45deg, #dc3545, #ff4d5e);
    border: none;
}

.btn-danger:hover {
    background: linear-gradient(45deg, #c82333, #dc3545);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

/* Modal styling */
.modal-content {
    border-radius: var(--border-radius);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.modal-header {
    border-bottom: none;
    padding: 1.5rem 1.5rem 0.75rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: none;
    padding: 0.75rem 1.5rem 1.5rem;
}

.modal-icon {
    font-size: 3rem;
    color: var(--danger-color);
}

/* Helper classes */
.transition-all {
    transition: var(--transition);
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .sharing-section, .current-shares-section {
        margin-bottom: 1rem;
    }
    
    .action-buttons {
        margin-top: 1rem;
    }
    
    .share-item {
        flex-direction: column;
    }
    
    .user-info {
        margin-bottom: 1rem;
    }
}

@media (max-width: 767.98px) {
    .share-actions {
        margin-top: 1rem;
    }
    
    .section-title {
        font-size: 1.25rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable all tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Handle textarea input for emails
    const recipientTextarea = document.getElementById('recipient_emails');
    const shareForm = document.querySelector('.share-form');
    
    if (recipientTextarea && shareForm) {
        // Format emails when pasting
        recipientTextarea.addEventListener('paste', function(e) {
            // Get pasted data
            let paste = (e.clipboardData || window.clipboardData).getData('text');
            
            // Process the pasted content
            paste = paste.replace(/[,;]/g, '\n');  // Replace commas and semicolons with newlines
            paste = paste.replace(/\s+/g, ' ');    // Replace multiple spaces with a single space
            paste = paste.replace(/\s*\n\s*/g, '\n'); // Clean up spaces around newlines
            
            // Insert at cursor position
            const start = this.selectionStart;
            const end = this.selectionEnd;
            const text = this.value;
            this.value = text.substring(0, start) + paste + text.substring(end);
            
            // Set cursor position after pasted content
            this.selectionStart = this.selectionEnd = start + paste.length;
            
            // Prevent default paste
            e.preventDefault();
        });
        
        // Add validation and processing
        shareForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Get and process email addresses
            let emailText = recipientTextarea.value.trim();
            
            // Split by newlines or commas
            let emails = emailText.split(/[\n,;]+/).map(email => email.trim()).filter(email => email);
            
            if (emails.length === 0) {
                // Show error
                recipientTextarea.classList.add('is-invalid');
                if (!recipientTextarea.nextElementSibling || !recipientTextarea.nextElementSibling.classList.contains('invalid-feedback')) {
                    const errorElement = document.createElement('div');
                    errorElement.className = 'invalid-feedback';
                    errorElement.textContent = 'At least one email address is required';
                    recipientTextarea.parentNode.appendChild(errorElement);
                }
                isValid = false;
            } else {
                recipientTextarea.classList.remove('is-invalid');
                
                // Create hidden input for each email
                emails.forEach(email => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'recipient_emails[]';
                    input.value = email;
                    shareForm.appendChild(input);
                });
            }
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
            
            // Show loading indicator
            const submitBtn = shareForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sharing...';
                submitBtn.disabled = true;
            }
        });
    }
    
    // Permission cards selection
    const permissionCards = document.querySelectorAll('.permission-check');
    
    if (permissionCards.length > 0) {
        permissionCards.forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    
                    // Add visual selection
                    permissionCards.forEach(c => {
                        c.classList.remove('selected-permission');
                    });
                    this.classList.add('selected-permission');
                }
            });
        });
    }
    
    // Handle remove share confirmation
    const removeButtons = document.querySelectorAll('.remove-share');
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmRemoveModal'));
    const confirmButton = document.getElementById('confirm-remove-btn');
    const recipientNameSpan = document.getElementById('share-recipient-name');
    
    if (removeButtons.length > 0 && confirmButton && recipientNameSpan) {
        removeButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetUrl = this.getAttribute('href');
                const recipientName = this.getAttribute('data-name');
                
                // Set the recipient name and confirm button URL
                recipientNameSpan.textContent = recipientName;
                confirmButton.setAttribute('href', targetUrl);
                
                // Show the modal
                confirmModal.show();
            });
        });
        
        // Add loading state to confirm button
        confirmButton.addEventListener('click', function() {
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Removing...';
            this.classList.add('disabled');
        });
    }
    
    // Animate elements on page load
    const animateElements = document.querySelectorAll('.sharing-card, .summary-card, .sharing-section, .current-shares-section');
    
    animateElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            element.style.transition = 'all 0.5s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, 100 + (index * 150));
    });
});
</script>