<?php
// Check if note is shared with current user
$user_id = Session::getUserId();
$is_shared = isset($data['note']['user_id']) && $data['note']['user_id'] != $user_id;
$can_edit = !$is_shared || (isset($data['note']['can_edit']) && $data['note']['can_edit']);
$is_new = !isset($data['note']['id']);
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Main Editor Card -->
            <div class="card shadow-sm border-0 rounded-4 note-editor-card">
                <!-- Header with actions -->
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="editor-title-section d-flex align-items-center">
                            <div class="editor-icon me-3">
                                <?php if ($is_new): ?>
                                    <i class="fas fa-plus-circle"></i>
                                <?php elseif ($is_shared): ?>
                                    <?php if ($can_edit): ?>
                                        <i class="fas fa-edit"></i>
                                    <?php else: ?>
                                        <i class="fas fa-eye"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fas fa-edit"></i>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <?php if ($is_new): ?>
                                        Create Note
                                    <?php elseif ($is_shared): ?>
                                        <?php if ($can_edit): ?>
                                            Edit Shared Note
                                            <span class="badge bg-success ms-2">Can Edit</span>
                                        <?php else: ?>
                                            View Shared Note
                                            <span class="badge bg-secondary ms-2">Read Only</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Edit Note
                                    <?php endif; ?>
                                </h4>
                                
                                <?php if ($is_shared): ?>
                                <div class="mt-1 small text-muted">
                                    <span class="me-2">Shared by:</span> <?= htmlspecialchars($data['note']['owner_name'] ?? 'Unknown User') ?> · 
                                    <span class="me-2">Shared on:</span> <?php 
                                        if (isset($data['note']['shared_at'])) {
                                            $shared_at = new DateTime($data['note']['shared_at']);
                                            echo $shared_at->format('M j, Y g:i A');
                                        } else {
                                            echo 'Unknown date';
                                        }
                                    ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="editor-actions d-flex flex-wrap gap-2">
                            <?php if (isset($data['note']['id']) && !$is_shared): ?>
                                <div class="btn-group me-2">
                                    <a href="<?= BASE_URL ?>/notes/share/<?= $data['note']['id'] ?>" class="btn btn-outline-info rounded-pill">
                                        <i class="fas fa-share-alt me-2"></i>Share
                                    </a>
                                    <a href="<?= BASE_URL ?>/notes/toggle-password/<?= $data['note']['id'] ?>" class="btn btn-outline-warning rounded-pill">
                                        <?php if (isset($data['note']['is_password_protected']) && $data['note']['is_password_protected']): ?>
                                            <i class="fas fa-unlock me-2"></i>Remove Password
                                        <?php else: ?>
                                            <i class="fas fa-lock me-2"></i>Add Password
                                        <?php endif; ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/notes" class="btn btn-outline-secondary rounded-pill">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>
                
                <form id="note-form" method="POST" action="<?= isset($data['note']['id']) ? BASE_URL . '/notes/update/' . $data['note']['id'] : BASE_URL . '/notes/store' ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
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
                        
                        <!-- Title Field -->
                        <div class="form-group title-field mb-4">
                            <input type="text" name="title" id="note-title" 
                                   class="form-control form-control-lg border <?= !empty($data['errors']['title']) ? 'is-invalid' : '' ?>" 
                                   placeholder="Note title" 
                                   value="<?= htmlspecialchars($data['note']['title'] ?? '') ?>" 
                                   required
                                   <?= (!$can_edit) ? 'readonly' : '' ?>>
                            <?php if (!empty($data['errors']['title'])): ?>
                                <div class="invalid-feedback"><?= $data['errors']['title'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="row gx-4">
                            <div class="col-md-8 mb-4 order-2 order-md-1">
                                <!-- Note Content Section -->
                                <div class="content-section">
                                    <div class="section-label d-flex align-items-center mb-3">
                                        <div class="section-icon">
                                            <i class="fas fa-align-left"></i>
                                        </div>
                                        <h5 class="mb-0">Note Content</h5>
                                    </div>
                                    
                                    <div class="content-field">
                                        <textarea name="content" id="note-content" 
                                                  class="form-control" 
                                                  placeholder="Write your note here..." 
                                                  rows="12"
                                                  <?= (!$can_edit) ? 'readonly' : '' ?>><?= htmlspecialchars($data['note']['content'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4 order-1 order-md-2">
                                <div class="note-sidebar">
                                    <?php if ($can_edit): ?>
                                    <!-- Image Attachment Section -->
                                    <div class="sidebar-section mb-4">
                                        <div class="section-label d-flex align-items-center mb-3">
                                            <div class="section-icon">
                                                <i class="fas fa-image"></i>
                                            </div>
                                            <h5 class="mb-0">Images</h5>
                                            <label for="note-images" class="btn btn-sm btn-outline-primary ms-auto rounded-pill px-3">
                                                <i class="fas fa-plus me-1"></i> Add
                                            </label>
                                            <input type="file" name="images[]" id="note-images" class="d-none" multiple accept="image/*">
                                        </div>
                                        
                                        <div id="dropzone" class="dropzone border rounded-3 border-dashed d-flex flex-column align-items-center justify-content-center p-4 text-center mb-3 <?= !empty($data['note']['images']) ? 'd-none' : '' ?>">
                                            <div class="dropzone-icon mb-2">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <div class="text-muted">Drag and drop images here<br>or click "Add" to browse</div>
                                        </div>
                                        
                                        <!-- Preview of images to be uploaded -->
                                        <div id="image-preview-container" class="d-none mb-3">
                                            <div class="preview-header d-flex justify-content-between align-items-center mb-2">
                                                <small class="text-muted">Images to upload</small>
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0" id="clear-images">
                                                    <i class="fas fa-times"></i> Clear
                                                </button>
                                            </div>
                                            <div class="row row-cols-2 g-2" id="image-previews"></div>
                                        </div>
                                        
                                        <!-- Display existing images -->
                                        <?php if (!empty($data['note']['images'])): ?>
                                            <div class="image-gallery">
                                                <div class="gallery-header d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">Attached images (<?= count($data['note']['images']) ?>)</small>
                                                </div>
                                                <div class="row row-cols-2 g-2">
                                                    <?php foreach ($data['note']['images'] as $image): ?>
                                                        <div class="col">
                                                            <div class="position-relative image-card">
                                                                <img src="<?= UPLOADS_URL . '/' . $image['file_path'] ?>" 
                                                                     alt="<?= htmlspecialchars($image['file_name']) ?>"
                                                                     class="img-fluid rounded">
                                                                <div class="image-overlay">
                                                                    <div class="image-name"><?= htmlspecialchars($image['file_name']) ?></div>
                                                                    <a href="<?= BASE_URL ?>/notes/delete-image/<?= $image['id'] ?>" 
                                                                       class="btn btn-sm btn-danger rounded-circle m-1 delete-image" 
                                                                       data-id="<?= $image['id'] ?>">
                                                                        <i class="fas fa-times"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($can_edit): ?>
                                    <!-- Labels Section -->
                                    <div class="sidebar-section mb-4">
                                        <div class="section-label d-flex align-items-center mb-3">
                                            <div class="section-icon">
                                                <i class="fas fa-tag"></i>
                                            </div>
                                            <h5 class="mb-0">Labels</h5>
                                            <a href="<?= BASE_URL ?>/labels" class="btn btn-sm btn-outline-primary ms-auto rounded-pill px-3">
                                                <i class="fas fa-cog me-1"></i> Manage
                                            </a>
                                        </div>
                                        
                                        <?php if (empty($data['labels'])): ?>
                                            <div class="alert alert-info mb-0">
                                                <div class="d-flex">
                                                    <div class="me-3">
                                                        <i class="fas fa-info-circle"></i>
                                                    </div>
                                                    <div>
                                                        <p class="mb-1">No labels available.</p>
                                                        <a href="<?= BASE_URL ?>/labels" class="alert-link">Create labels</a> to organize your notes.
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="labels-container">
                                                <?php foreach ($data['labels'] as $label): ?>
                                                    <div class="form-check custom-label-check mb-2">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="labels[]" 
                                                               id="label-<?= $label['id'] ?>" 
                                                               value="<?= $label['id'] ?>"
                                                               <?= isset($data['note']['labels']) && in_array($label['id'], $data['note']['labels']) ? 'checked' : '' ?>>
                                                        <label class="form-check-label" for="label-<?= $label['id'] ?>">
                                                            <span class="label-name"><?= htmlspecialchars($label['name']) ?></span>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Note Information Section for existing notes -->
                                    <?php if (isset($data['note']['id'])): ?>
                                    <div class="sidebar-section mb-4">
                                        <div class="section-label d-flex align-items-center mb-3">
                                            <div class="section-icon">
                                                <i class="fas fa-info-circle"></i>
                                            </div>
                                            <h5 class="mb-0">Note Info</h5>
                                        </div>
                                        
                                        <div class="note-info-list">
                                            <div class="info-item d-flex">
                                                <div class="info-label">Created:</div>
                                                <div class="info-value">
                                                    <?php 
                                                    $created = new DateTime($data['note']['created_at']);
                                                    echo $created->format('M j, Y g:i A');
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="info-item d-flex">
                                                <div class="info-label">Updated:</div>
                                                <div class="info-value">
                                                    <?php 
                                                    $updated = new DateTime($data['note']['updated_at']);
                                                    echo $updated->format('M j, Y g:i A');
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="info-item d-flex">
                                                <div class="info-label">Status:</div>
                                                <div class="info-value">
                                                    <?php if (isset($data['note']['is_pinned']) && $data['note']['is_pinned']): ?>
                                                        <span class="badge bg-primary">Pinned</span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (isset($data['note']['is_password_protected']) && $data['note']['is_password_protected']): ?>
                                                        <span class="badge bg-warning text-dark">Protected</span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (isset($data['note']['is_shared']) && $data['note']['is_shared']): ?>
                                                        <span class="badge bg-info">Shared</span>
                                                    <?php endif; ?>
                                                    
                                                    <?php 
                                                    if (!isset($data['note']['is_pinned']) && 
                                                        !isset($data['note']['is_password_protected']) && 
                                                        !isset($data['note']['is_shared'])): 
                                                    ?>
                                                        <span class="badge bg-secondary">Standard</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($is_shared && $can_edit): ?>
                                    <!-- Collaboration Information -->
                                    <div class="sidebar-section mb-4">
                                        <div class="section-label d-flex align-items-center mb-3">
                                            <div class="section-icon">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <h5 class="mb-0">Collaboration</h5>
                                        </div>
                                        
                                        <div class="alert alert-info mb-0">
                                            <p class="mb-0">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Real-time Collaboration:</strong> Changes you make will be visible to other users in real-time.
                                            </p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                            
                        <!-- Save Button Section (Footer) -->
                        <div class="save-section mt-3">
                            <?php if ($can_edit): ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <div id="autosave-status" class="text-muted"></div>
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5" id="save-button">
                                    <?php if ($is_new): ?>
                                        <i class="fas fa-plus me-2"></i> Create Note
                                    <?php else: ?>
                                        <i class="fas fa-save me-2"></i> Save Changes
                                    <?php endif; ?>
                                </button>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-secondary">
                                <i class="fas fa-lock me-2"></i>
                                <strong>Read-only:</strong> You don't have permission to edit this note.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Remote Cursors Container (for real-time collaboration) -->
            <div id="remote-cursors-container"></div>
        </div>
    </div>
</div>

<!-- Collaborators Panel - For real-time collaboration -->
<div id="collaborators-panel" class="position-fixed top-0 end-0 p-3 d-none" style="z-index: 1050; margin-top: 80px;">
    <div class="card shadow">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="m-0"><i class="fas fa-users me-2"></i>Active Collaborators</h6>
        </div>
        <div class="card-body p-0">
            <ul id="collaborators-list" class="list-group list-group-flush"></ul>
        </div>
    </div>
</div>

<!-- Auto-save indicator -->
<div id="save-status" class="position-fixed bottom-0 end-0 m-3 p-2 px-3 rounded toast align-items-center" role="alert" aria-live="assertive" aria-atomic="true" style="display: none;">
    <div class="d-flex">
        <div class="toast-body d-flex align-items-center">
            <span id="saving-icon" class="me-2"><i class="fas fa-circle-notch fa-spin"></i></span>
            <span id="saved-icon" class="me-2" style="display: none;"><i class="fas fa-check text-success"></i></span>
            <span id="save-message">Saving...</span>
        </div>
    </div>
</div>

<!-- Real-time collaboration notification toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050; margin-bottom: 60px;">
    <div id="collaborationToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="fas fa-users me-2 text-primary"></i>
            <strong class="me-auto">Collaboration</strong>
            <small>Just now</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="collaborationToastBody">
            Someone is editing this note.
        </div>
    </div>
</div>

<!-- Confirmation Modal for image deletion -->
<div class="modal fade" id="deleteImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title">Delete Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="modal-icon mb-3">
                    <i class="fas fa-image"></i>
                </div>
                <p>Are you sure you want to delete this image?</p>
                <p class="text-muted mb-0 small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pt-0 pb-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirm-delete-image" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i>Delete Image
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
    --border-radius: 16px;
    --small-radius: 8px;
    --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    --transition: all 0.3s ease;
}

/* Main editor card styling */
.note-editor-card {
    transition: var(--transition);
    border-radius: var(--border-radius) !important;
    overflow: hidden;
}

.note-editor-card:hover {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
}

/* Editor icon styling */
.editor-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #4a89dc, #6ea8fe);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    box-shadow: 0 4px 10px rgba(74, 137, 220, 0.3);
}

/* Title field styling */
.title-field {
    position: relative;
    margin-bottom: 1.5rem;
}

.title-field::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    height: 2px;
    width: 0;
    background: linear-gradient(to right, var(--primary-color), #6ea8fe);
    transition: width 0.3s ease;
}

.title-field:focus-within::after {
    width: 100%;
}

.title-field .form-control {
    font-size: 1.5rem;
    font-weight: 600;
    padding: 0.75rem 1rem;
    border-radius: var(--small-radius);
    transition: var(--transition);
    background-color: #f9f9f9;
    border-width: 1px;
}

.title-field .form-control:focus {
    background-color: white;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    border-color: var(--primary-color);
}

/* Content field styling */
.content-field {
    position: relative;
}

.content-field textarea {
    border-radius: var(--small-radius);
    padding: 1rem;
    min-height: 350px;
    resize: vertical;
    transition: var(--transition);
    border-width: 1px;
}

.content-field textarea:focus {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    border-color: var(--primary-color);
}

/* Section styling */
.section-label {
    margin-bottom: 1rem;
}

.section-icon {
    width: 32px;
    height: 32px;
    background-color: rgba(74, 137, 220, 0.1);
    color: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
}

.section-label h5 {
    font-weight: 600;
    color: #343a40;
    margin-bottom: 0;
}

/* Sidebar styling */
.note-sidebar {
    background-color: #f9f9f9;
    border-radius: var(--small-radius);
    padding: 1.5rem;
}

.sidebar-section {
    margin-bottom: 2rem;
}

.sidebar-section:last-child {
    margin-bottom: 0;
}

/* Dropzone styling */
.dropzone {
    transition: var(--transition);
    min-height: 120px;
    border: 2px dashed rgba(0, 0, 0, 0.1) !important;
    cursor: pointer;
}

.dropzone:hover, .dropzone.dragover {
    background-color: rgba(0, 123, 255, 0.05);
    border-color: var(--primary-color) !important;
}

.dropzone-icon {
    font-size: 2rem;
    color: #adb5bd;
    transition: var(--transition);
}

.dropzone:hover .dropzone-icon {
    color: var(--primary-color);
    transform: translateY(-5px);
}

/* Image gallery styling */
.image-card {
    position: relative;
    overflow: hidden;
    border-radius: var(--small-radius);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
}

.image-card img {
    width: 100%;
    height: 100px;
    object-fit: cover;
    transition: var(--transition);
}

.image-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.image-card:hover img {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
    padding: 1.5rem 0.5rem 0.5rem;
    transition: var(--transition);
    opacity: 0;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}

.image-card:hover .image-overlay {
    opacity: 1;
}

.image-name {
    color: white;
    font-size: 0.75rem;
    max-width: 70%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Label styling */
.custom-label-check {
    position: relative;
    padding-left: 0;
}

.custom-label-check input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.custom-label-check label {
    display: inline-block;
    cursor: pointer;
    padding: 0.5rem 1rem 0.5rem 2.5rem;
    border-radius: 30px;
    background-color: #f0f0f0;
    position: relative;
    transition: var(--transition);
    overflow: hidden;
    width: 100%;
}

.custom-label-check label:before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    border-radius: 4px;
    border: 2px solid #ccc;
    background-color: white;
    transition: var(--transition);
}

.custom-label-check input[type="checkbox"]:checked + label {
    background-color: rgba(74, 137, 220, 0.1);
    color: var(--primary-color);
}

.custom-label-check input[type="checkbox"]:checked + label:before {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.custom-label-check input[type="checkbox"]:checked + label:after {
    content: '✓';
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    text-align: center;
    line-height: 18px;
    color: white;
    font-size: 0.75rem;
}

.custom-label-check label:hover {
    background-color: #e9ecef;
    transform: translateX(3px);
}

/* Note info list styling */
.note-info-list {
    background-color: white;
    border-radius: var(--small-radius);
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.info-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    width: 80px;
    font-weight: 500;
    color: #495057;
}

.info-value {
    flex: 1;
    color: #343a40;
}

/* Save button section */
.save-section {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
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

.btn-primary {
    background: linear-gradient(45deg, #4a89dc, #6ea8fe);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(45deg, #3a77c5, #4a89dc);
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(74, 137, 220, 0.3);
}

.btn-outline-info {
    color: var(--info-color);
    border: 1px solid var(--info-color);
}

.btn-outline-info:hover {
    background-color: var(--info-color);
    color: white;
    transform: translateY(-3px);
}

.btn-outline-warning {
    color: var(--warning-color);
    border: 1px solid var(--warning-color);
}

.btn-outline-warning:hover {
    background-color: var(--warning-color);
    color: #212529;
    transform: translateY(-3px);
}

.btn-outline-secondary {
    color: var(--secondary-color);
    border: 1px solid var(--secondary-color);
}

.btn-outline-secondary:hover {
    background-color: var(--secondary-color);
    color: white;
    transform: translateY(-3px);
}

.btn-outline-primary {
    color: var(--primary-color);
    border: 1px solid var(--primary-color);
}

.btn-outline-primary:hover {
    background-color: var(--primary-color);
    color: white;
    transform: translateY(-3px);
}

/* Remote cursor styles */
.remote-cursor {
    position: absolute;
    z-index: 100;
    pointer-events: none;
}

.remote-cursor-label {
    position: absolute;
    top: -20px;
    left: 0;
    background-color: #3498db;
    color: white;
    padding: 2px 5px;
    border-radius: 3px;
    font-size: 12px;
    white-space: nowrap;
}

.remote-cursor-caret {
    width: 2px;
    height: 20px;
    background-color: #3498db;
    animation: blink 1s infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0; }
}

/* Alert styling */
.alert {
    border-radius: var(--small-radius);
}

.alert-danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: #721c24;
}

.alert-info {
    background-color: rgba(23, 162, 184, 0.1);
    color: #0c5460;
}

.alert-icon {
    font-size: 1.5rem;
}

/* Toast styling */
.toast {
    border-radius: var(--small-radius);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

/* Modal styling */
.modal-content {
    border-radius: var(--border-radius) !important;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.modal-icon {
    width: 70px;
    height: 70px;
    background-color: rgba(220, 53, 69, 0.1);
    color: var(--danger-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto 1rem;
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
    .editor-actions {
        margin-top: 1rem;
    }
    
    .note-sidebar {
        margin-bottom: 1rem;
    }
}

@media (max-width: 767.98px) {
    .editor-title-section {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .editor-icon {
        margin-bottom: 1rem;
    }
    
    .editor-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .editor-actions .btn-group {
        display: flex;
        width: 100%;
        margin-right: 0 !important;
        margin-bottom: 0.5rem;
    }
    
    .editor-actions .btn-group .btn {
        flex: 1;
    }
    
    .editor-actions .btn {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const noteForm = document.getElementById('note-form');
    const titleInput = document.getElementById('note-title');
    const contentInput = document.getElementById('note-content');
    const saveStatus = document.getElementById('save-status');
    const savingIcon = document.getElementById('saving-icon');
    const savedIcon = document.getElementById('saved-icon');
    const saveMessage = document.getElementById('save-message');
    const imageInput = document.getElementById('note-images');
    const previewContainer = document.getElementById('image-preview-container');
    const previewsDiv = document.getElementById('image-previews');
    const dropzone = document.getElementById('dropzone');
    const clearImagesBtn = document.getElementById('clear-images');
    const saveButton = document.getElementById('save-button');
    const autosaveStatus = document.getElementById('autosave-status');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteImageModal'));
    const confirmDeleteBtn = document.getElementById('confirm-delete-image');
    
    // Variables for auto-save
    let saveTimeout;
    let lastSavedContent = contentInput ? contentInput.value : '';
    let lastSavedTitle = titleInput ? titleInput.value : '';
    let autoSaveEnabled = true;
    let currentDeleteImageUrl = '';
    
    // Show saving status
    function showSaveStatus(status, message) {
        if (!saveStatus) return;
        
        saveStatus.style.display = 'block';
        
        if (status === 'saving') {
            savingIcon.style.display = 'inline-block';
            savedIcon.style.display = 'none';
            saveMessage.textContent = message || 'Saving...';
            saveStatus.classList.add('bg-dark', 'text-white');
            saveStatus.classList.remove('bg-success', 'bg-danger');
            
            if (autosaveStatus) {
                autosaveStatus.innerHTML = '<i class="fas fa-circle-notch fa-spin me-1"></i> Autosaving...';
            }
        } else if (status === 'saved') {
            savingIcon.style.display = 'none';
            savedIcon.style.display = 'inline-block';
            saveMessage.textContent = message || 'Saved';
            saveStatus.classList.remove('bg-dark', 'bg-danger');
            saveStatus.classList.add('bg-success', 'text-white');
            
            if (autosaveStatus) {
                autosaveStatus.innerHTML = '<i class="fas fa-check text-success me-1"></i> Autosaved at ' + new Date().toLocaleTimeString();
            }
            
            // Hide after 2 seconds
            setTimeout(() => {
                saveStatus.style.opacity = '0';
                setTimeout(() => {
                    saveStatus.style.display = 'none';
                    saveStatus.style.opacity = '1';
                }, 300);
            }, 2000);
        } else if (status === 'error') {
            savingIcon.style.display = 'none';
            savedIcon.style.display = 'none';
            saveMessage.textContent = message || 'Error saving';
            saveStatus.classList.remove('bg-dark', 'bg-success');
            saveStatus.classList.add('bg-danger', 'text-white');
            
            if (autosaveStatus) {
                autosaveStatus.innerHTML = '<i class="fas fa-exclamation-circle text-danger me-1"></i> Error saving';
            }
            
            // Hide after 3 seconds
            setTimeout(() => {
                saveStatus.style.opacity = '0';
                setTimeout(() => {
                    saveStatus.style.display = 'none';
                    saveStatus.style.opacity = '1';
                }, 300);
            }, 3000);
        }
    }
    
    // Save changes
    function saveChanges() {
        // Auto-save if content has changed and title is not empty
        if (titleInput && contentInput && 
            (lastSavedContent !== contentInput.value || lastSavedTitle !== titleInput.value) &&
            titleInput.value.trim() !== '') {
            
            // Show saving indicator
            showSaveStatus('saving');
            
            const formData = new FormData(noteForm);
            
            // Send AJAX request
            fetch(noteForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update last saved content
                    lastSavedContent = contentInput.value;
                    lastSavedTitle = titleInput.value;
                    
                    // Show saved indicator
                    showSaveStatus('saved');
                    
                    // If this was a new note, redirect to edit page for this note
                    if (data.note_id && !window.location.href.includes('/edit/')) {
                        window.location.href = BASE_URL + '/notes/edit/' + data.note_id;
                    }
                } else {
                    // Show error indicator
                    showSaveStatus('error', data.errors?.general || 'Error saving');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showSaveStatus('error', 'Network error');
            });
        }
    }
    
    // Auto-save functionality
    function autoSave() {
        // Clear any existing timeout
        clearTimeout(saveTimeout);
        
        // Set a new timeout to save after 1.5 seconds of inactivity
        saveTimeout = setTimeout(saveChanges, 1500);
    }
    
    // Add event listeners for auto-save if we're in edit mode
    if (titleInput && contentInput && saveButton) {
        const isReadOnly = contentInput.hasAttribute('readonly');
        
        if (!isReadOnly) {
            titleInput.addEventListener('input', autoSave);
            contentInput.addEventListener('input', autoSave);
            
            // Add event listeners for label checkboxes
            const labelCheckboxes = document.querySelectorAll('input[name="labels[]"]');
            labelCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', autoSave);
            });
            
            // Add loading state to save button on manual save
            noteForm.addEventListener('submit', function() {
                saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Saving...';
                saveButton.disabled = true;
            });
        }
    }
    
    // Image upload handling
    if (imageInput) {
        // Preview uploaded images
        imageInput.addEventListener('change', handleFileSelect);
        
        // Clear images button
        if (clearImagesBtn) {
            clearImagesBtn.addEventListener('click', function() {
                imageInput.value = '';
                previewsDiv.innerHTML = '';
                previewContainer.classList.add('d-none');
                if (dropzone) dropzone.classList.remove('d-none');
            });
        }
        
        // Delete image confirmation
        const deleteImageLinks = document.querySelectorAll('.delete-image');
        deleteImageLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                currentDeleteImageUrl = this.href;
                deleteModal.show();
            });
        });
        
        // Confirm delete image
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                if (currentDeleteImageUrl) {
                    confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Deleting...';
                    confirmDeleteBtn.disabled = true;
                    
                    fetch(currentDeleteImageUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        deleteModal.hide();
                        
                        // Reset button state
                        confirmDeleteBtn.innerHTML = '<i class="fas fa-trash me-2"></i>Delete Image';
                        confirmDeleteBtn.disabled = false;
                        
                        if (data.success) {
                            // Reload page to show updated images
                            window.location.reload();
                        } else {
                            alert('Error deleting image: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Network error while deleting image');
                        
                        // Reset button state
                        confirmDeleteBtn.innerHTML = '<i class="fas fa-trash me-2"></i>Delete Image';
                        confirmDeleteBtn.disabled = false;
                        deleteModal.hide();
                    });
                }
            });
        }
    }
    
    // Handle file selection
    function handleFileSelect(event) {
        const files = event.target.files;
        
        if (files.length > 0) {
            previewContainer.classList.remove('d-none');
            if (dropzone) dropzone.classList.add('d-none');
            
            previewsDiv.innerHTML = '';
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    const preview = document.createElement('div');
                    preview.className = 'col';
                    
                    reader.onload = function(e) {
                        preview.innerHTML = `
                            <div class="position-relative image-card">
                                <img src="${e.target.result}" class="img-fluid rounded" alt="${file.name}">
                                <div class="image-overlay">
                                    <div class="image-name">${file.name}</div>
                                    <button type="button" class="btn btn-sm btn-danger rounded-circle remove-preview">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        
                        // Add event listener for remove button
                        const removeBtn = preview.querySelector('.remove-preview');
                        removeBtn.addEventListener('click', function() {
                            preview.remove();
                            
                            // Show dropzone if no more previews
                            if (previewsDiv.children.length === 0) {
                                previewContainer.classList.add('d-none');
                                if (dropzone) dropzone.classList.remove('d-none');
                            }
                        });
                    };
                    
                    reader.readAsDataURL(file);
                    previewsDiv.appendChild(preview);
                }
            }
            
            // Auto-save after image upload
            autoSave();
        }
    }
    
    // Initialize drag and drop
    if (dropzone) {
        // Prevent default behavior to allow drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });
        
        // Highlight drop area when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, unhighlight, false);
        });
        
        // Handle drop
        dropzone.addEventListener('drop', handleDrop, false);
        
        // Click on dropzone to select files
        dropzone.addEventListener('click', function() {
            imageInput.click();
        });
    }
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight() {
        dropzone.classList.add('dragover');
    }
    
    function unhighlight() {
        dropzone.classList.remove('dragover');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        // Create a new FileList-like object
        const dataTransfer = new DataTransfer();
        
        // Add the dropped files
        for (let i = 0; i < files.length; i++) {
            if (files[i].type.startsWith('image/')) {
                dataTransfer.items.add(files[i]);
            }
        }
        
        // Set the files in the input element
        imageInput.files = dataTransfer.files;
        
        // Handle the file selection
        handleFileSelect({target: {files: dataTransfer.files}});
    }
    
    // Animate elements on page load
    const animateElements = [
        document.querySelector('.note-editor-card'),
        document.querySelector('.title-field'),
        document.querySelector('.content-section'),
        document.querySelector('.note-sidebar')
    ];
    
    animateElements.forEach((element, index) => {
        if (element) {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.5s ease';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, 100 + (index * 150));
        }
    });
    
    // Initialize WebSocket connection for real-time collaboration
    let noteWebsocket;
    let isCollaborationEnabled = <?= ($is_shared && $can_edit) ? 'true' : 'false' ?>;
    let remoteCursors = {};
    let lastCursorPositions = {};
    let currentCollaborators = {};
    let noteId = <?= isset($data['note']['id']) ? $data['note']['id'] : '0' ?>;
    
    if (isCollaborationEnabled && typeof ENABLE_WEBSOCKETS !== 'undefined' && ENABLE_WEBSOCKETS) {
        initWebsocket();
    }

    function initWebsocket() {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsPort = 8080; // WebSocket server port
        const hostName = window.location.hostname;
        const wsUrl = `${protocol}//${hostName}:${wsPort}`;
        
        noteWebsocket = new WebSocket(wsUrl);
        
        noteWebsocket.onopen = function() {
            console.log('WebSocket connection established');
            
            // Authenticate with the server
            noteWebsocket.send(JSON.stringify({
                type: 'auth',
                user_id: USER_ID
            }));
            
            // Subscribe to this note's updates
            setTimeout(() => {
                if (noteId > 0) {
                    noteWebsocket.send(JSON.stringify({
                        type: 'subscribe',
                        note_id: noteId
                    }));
                }
            }, 500); // Short delay to ensure authentication completes
        };
        
        noteWebsocket.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                
                switch (data.type) {
                    case 'auth_response':
                        if (data.success) {
                            console.log('Authenticated with WebSocket server');
                        } else {
                            console.error('Authentication failed:', data.message);
                        }
                        break;
                        
                    case 'note_updated':
                        if (data.user_id !== USER_ID) {
                            handleRemoteUpdate(data);
                        }
                        break;
                        
                    case 'cursor_position':
                        if (data.user_id !== USER_ID) {
                            updateRemoteCursor(data);
                        }
                        break;
                        
                    case 'user_joined':
                        if (data.user_id !== USER_ID) {
                            showCollaborationToast(`${data.user_name} joined the note`);
                            currentCollaborators[data.user_id] = data.user_name;
                            updateCollaboratorsList();
                        }
                        break;
                        
                    case 'user_left':
                        if (data.user_id !== USER_ID) {
                            showCollaborationToast(`${data.user_name} left the note`);
                            delete currentCollaborators[data.user_id];
                            removeRemoteCursor(data.user_id);
                            updateCollaboratorsList();
                        }
                        break;
                }
            } catch (error) {
                console.error('Error processing WebSocket message:', error);
            }
        };
        
        noteWebsocket.onclose = function() {
            console.log('WebSocket connection closed');
            
            // Attempt to reconnect after 5 seconds
            setTimeout(initWebsocket, 5000);
        };
        
        noteWebsocket.onerror = function(error) {
            console.error('WebSocket error:', error);
        };
        
        // Send updates when the content changes
        if (titleInput && contentInput) {
            const sendUpdate = debounce(function() {
                if (noteWebsocket && noteWebsocket.readyState === WebSocket.OPEN && noteId > 0) {
                    const title = titleInput.value;
                    const content = contentInput.value;
                    
                    noteWebsocket.send(JSON.stringify({
                        type: 'note_update',
                        note_id: noteId,
                        title: title,
                        content: content
                    }));
                }
            }, 500);
            
            // Listen for changes in the editor
            titleInput.addEventListener('input', sendUpdate);
            contentInput.addEventListener('input', sendUpdate);
            
            // Send cursor position updates
            contentInput.addEventListener('click', sendCursorPosition);
            contentInput.addEventListener('keyup', sendCursorPosition);
            contentInput.addEventListener('select', sendCursorPosition);
        }
    }

    // Handle remote updates to the note
    function handleRemoteUpdate(data) {
        if (!isCollaborationEnabled) return;
        
        // Don't apply updates if the user is actively typing
        const isUserActiveInTitle = document.activeElement === titleInput;
        const isUserActiveInContent = document.activeElement === contentInput;
        
        // Update the title if provided and not currently being edited
        if (data.title && !isUserActiveInTitle) {
            titleInput.value = data.title;
        }
        
        // Update the content if not currently being edited
        if (data.content && !isUserActiveInContent) {
            // Save current scroll position
            const scrollTop = contentInput.scrollTop;
            
            // Update content
            contentInput.value = data.content;
            
            // Restore scroll position
            contentInput.scrollTop = scrollTop;
            
            // Show toast notification
            showCollaborationToast(`${data.user_name} updated the note`);
        }
    }

    // Send cursor position to server
    function sendCursorPosition() {
        if (!isCollaborationEnabled || !noteWebsocket) return;
        
        const position = contentInput.selectionStart;
        
        // Only send if position changed
        if (lastCursorPositions[USER_ID] !== position) {
            lastCursorPositions[USER_ID] = position;
            
            if (noteWebsocket.readyState === WebSocket.OPEN && noteId > 0) {
                noteWebsocket.send(JSON.stringify({
                    type: 'cursor_position',
                    note_id: noteId,
                    position: position
                }));
            }
        }
    }

    // Update remote cursor position
    function updateRemoteCursor(data) {
        if (!isCollaborationEnabled) return;
        
        const userId = data.user_id;
        const position = data.position;
        const userName = data.user_name;
        const userColor = getColorForUser(userId);
        
        // Create or update cursor element
        let cursor = remoteCursors[userId];
        if (!cursor) {
            cursor = document.createElement('div');
            cursor.className = 'remote-cursor';
            cursor.innerHTML = `
                <div class="remote-cursor-label" style="background-color: ${userColor}">${userName}</div>
                <div class="remote-cursor-caret" style="background-color: ${userColor}"></div>
            `;
            document.getElementById('remote-cursors-container').appendChild(cursor);
            remoteCursors[userId] = cursor;
        }
        
        // Position the cursor at the right location in the textarea
        const coords = getCaretCoordinates(contentInput, position);
        const rect = contentInput.getBoundingClientRect();
        cursor.style.left = (rect.left + coords.left) + 'px';
        cursor.style.top = (rect.top + coords.top) + 'px';
        
        // Remember the position
        lastCursorPositions[userId] = position;
        
        // Add to current collaborators
        if (!currentCollaborators[userId]) {
            currentCollaborators[userId] = userName;
            updateCollaboratorsList();
        }
        
        // Set a timeout to remove cursor after inactivity
        if (cursor.timeout) clearTimeout(cursor.timeout);
        cursor.timeout = setTimeout(() => {
            removeRemoteCursor(userId);
        }, 10000); // 10 seconds
    }

    // Remove a remote cursor
    function removeRemoteCursor(userId) {
        if (remoteCursors[userId]) {
            remoteCursors[userId].remove();
            delete remoteCursors[userId];
        }
    }

    // Generate a consistent color for a user
    function getColorForUser(userId) {
        // List of distinctive colors
        const colors = [
            '#e6194B', '#3cb44b', '#ffe119', '#4363d8', '#f58231', 
            '#911eb4', '#42d4f4', '#f032e6', '#bfef45', '#fabed4'
        ];
        
        // Use modulo to ensure we always get a valid index
        const colorIndex = parseInt(userId, 10) % colors.length;
        return colors[colorIndex];
    }

    // Update the collaborators list panel
    function updateCollaboratorsList() {
        if (!isCollaborationEnabled) return;
        
        const collaboratorsPanel = document.getElementById('collaborators-panel');
        const collaboratorsList = document.getElementById('collaborators-list');
        
        if (!collaboratorsPanel || !collaboratorsList) return;
        
        // Show the panel if there are collaborators
        const collabCount = Object.keys(currentCollaborators).length;
        if (collabCount > 0) {
            collaboratorsPanel.classList.remove('d-none');
            
            // Update the list
            collaboratorsList.innerHTML = '';
            
            for (const [userId, userName] of Object.entries(currentCollaborators)) {
                const userColor = getColorForUser(userId);
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex align-items-center py-2';
                
                // Get first letter of user's name for avatar
                const firstLetter = userName.charAt(0).toUpperCase();
                
                li.innerHTML = `
                    <div class="user-avatar" style="background-color: ${userColor}">${firstLetter}</div>
                    <span>${userName}</span>
                `;
                
                collaboratorsList.appendChild(li);
            }
        } else {
            collaboratorsPanel.classList.add('d-none');
        }
    }

    // Show toast notification for collaboration events
    function showCollaborationToast(message) {
        const toast = document.getElementById('collaborationToast');
        const toastBody = document.getElementById('collaborationToastBody');
        
        if (toast && toastBody) {
            toastBody.textContent = message;
            
            // Use Bootstrap's toast API if available, otherwise manually show/hide
            if (typeof bootstrap !== 'undefined') {
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
            } else {
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }
        }
    }

    // Utility function to get caret coordinates in a textarea
    function getCaretCoordinates(element, position) {
        // Create a dummy element to measure text dimensions
        const div = document.createElement('div');
        const styles = window.getComputedStyle(element);
        
        // Copy styles from textarea
        div.style.position = 'absolute';
        div.style.visibility = 'hidden';
        div.style.whiteSpace = 'pre-wrap';
        div.style.height = 'auto';
        div.style.width = element.offsetWidth + 'px';
        div.style.font = styles.font;
        div.style.padding = styles.padding;
        div.style.border = styles.border;
        div.style.boxSizing = styles.boxSizing;
        div.style.lineHeight = styles.lineHeight;
        
        document.body.appendChild(div);
        
        // Set content up to cursor position
        div.textContent = element.value.substring(0, position);
        
        // Add a span to mark cursor position
        const span = document.createElement('span');
        span.textContent = '.'; // Just need something to measure
        div.appendChild(span);
        
        // Get position
        const coordinates = {
            left: span.offsetLeft,
            top: span.offsetTop,
            height: span.offsetHeight
        };
        
        // Clean up
        document.body.removeChild(div);
        
        return coordinates;
    }

    // Utility function to debounce function calls
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            const later = function() {
                timeout = null;
                func.apply(context, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});
</script>