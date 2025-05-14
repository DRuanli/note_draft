<div class="container py-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <!-- Note View Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="card-title h4 mb-0">
                            <?= htmlspecialchars($data['note']['title']) ?>
                        </h2>
                        <div class="text-muted small mt-1">
                            <?php if (!$data['isOwner'] && isset($data['note']['owner_name'])): ?>
                                Shared by <?= htmlspecialchars($data['note']['owner_name']) ?> · 
                            <?php endif; ?>
                            
                            <?php 
                            $updated = new DateTime($data['note']['updated_at']);
                            echo 'Last updated: ' . $updated->format('M j, Y g:i A');
                            ?>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if ($data['can_edit']): ?>
                            <a href="<?= BASE_URL ?>/notes/edit/<?= $data['note']['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/notes" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Notes
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Note labels -->
                    <?php if (!empty($data['note']['labels'])): ?>
                        <div class="mb-3">
                            <?php foreach ($data['note']['labels'] as $label): ?>
                                <span class="badge bg-light text-dark border me-1 py-1 px-2">
                                    <i class="fas fa-tag me-1 text-muted"></i>
                                    <?= htmlspecialchars($label['name']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Note images -->
                    <?php if (!empty($data['note']['images'])): ?>
                        <div class="note-images mb-4">
                            <div class="row row-cols-2 row-cols-md-4 g-3">
                                <?php foreach ($data['note']['images'] as $image): ?>
                                    <div class="col">
                                        <div class="image-card">
                                            <img src="<?= UPLOADS_URL . '/' . $image['file_path'] ?>" 
                                                alt="<?= htmlspecialchars($image['file_name']) ?>"
                                                class="img-fluid rounded shadow-sm">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Note content -->
                    <div class="note-content mt-3">
                        <?= nl2br(htmlspecialchars($data['note']['content'])) ?>
                    </div>
                </div>
                
                <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <!-- Note indicators -->
                        <div class="note-indicators d-flex gap-2 me-3">
                            <?php if (isset($data['note']['is_pinned']) && $data['note']['is_pinned']): ?>
                                <span class="badge bg-light text-primary border">
                                    <i class="fas fa-thumbtack me-1"></i> Pinned
                                </span>
                            <?php endif; ?>
                            
                            <?php if (isset($data['note']['is_password_protected']) && $data['note']['is_password_protected']): ?>
                                <span class="badge bg-light text-warning border">
                                    <i class="fas fa-lock me-1"></i> Protected
                                </span>
                            <?php endif; ?>
                            
                            <?php if (isset($data['note']['is_shared']) && $data['note']['is_shared']): ?>
                                <span class="badge bg-light text-info border">
                                    <i class="fas fa-share-alt me-1"></i> Shared
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Created date -->
                        <div class="text-muted small">
                            Created: 
                            <?php 
                            $created = new DateTime($data['note']['created_at']);
                            echo $created->format('M j, Y');
                            ?>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div>
                        <?php if ($data['isOwner']): ?>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-light pin-note" data-id="<?= $data['note']['id'] ?>" title="<?= isset($data['note']['is_pinned']) && $data['note']['is_pinned'] ? 'Unpin' : 'Pin' ?>">
                                    <i class="fas fa-thumbtack <?= isset($data['note']['is_pinned']) && $data['note']['is_pinned'] ? 'text-primary' : '' ?>"></i>
                                </button>
                                <a href="<?= BASE_URL ?>/notes/share/<?= $data['note']['id'] ?>" class="btn btn-light" title="Share">
                                    <i class="fas fa-share-alt"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/notes/delete/<?= $data['note']['id'] ?>" class="btn btn-light delete-note" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Image card styling */
.image-card {
    position: relative;
    overflow: hidden;
    transition: transform 0.2s ease;
    cursor: pointer;
}

.image-card:hover {
    transform: scale(1.03);
}

.note-content {
    font-size: 1rem;
    line-height: 1.6;
    white-space: pre-wrap;
}

/* Make note content look better */
.note-content {
    max-width: 100%;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pin/unpin note functionality
    const pinButton = document.querySelector('.pin-note');
    
    if (pinButton) {
        pinButton.addEventListener('click', function() {
            const noteId = this.getAttribute('data-id');
            const icon = this.querySelector('i');
            
            // Show loading state
            icon.className = 'fas fa-spinner fa-spin';
            
            // Send AJAX request
            fetch(`${BASE_URL}/notes/toggle-pin/${noteId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh the page to reflect changes
                    window.location.reload();
                } else if (data.redirect) {
                    // Redirect to password verification if needed
                    window.location.href = data.redirect;
                } else {
                    // Reset icon on error
                    icon.className = 'fas fa-thumbtack';
                    console.error('Error:', data.message);
                }
            })
            .catch(error => {
                // Reset icon on error
                icon.className = 'fas fa-thumbtack';
                console.error('Error:', error);
            });
        });
    }
    
    // Delete confirmation
    const deleteLink = document.querySelector('.delete-note');
    
    if (deleteLink) {
        deleteLink.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this note? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    }
    
    // Make images clickable to view in full size
    const imageCards = document.querySelectorAll('.image-card');
    
    imageCards.forEach(card => {
        card.addEventListener('click', function() {
            const img = this.querySelector('img');
            const src = img.src;
            
            // Create modal to display full image
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = 'imageModal';
            modal.tabIndex = '-1';
            modal.setAttribute('aria-hidden', 'true');
            
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0">
                        <div class="modal-body p-0">
                            <img src="${src}" class="img-fluid w-100" alt="Full size image">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Show the modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            // Remove modal from DOM after it's hidden
            modal.addEventListener('hidden.bs.modal', function() {
                document.body.removeChild(modal);
            });
        });
    });
});
</script>