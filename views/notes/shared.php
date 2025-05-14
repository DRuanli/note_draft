<div class="container-fluid py-4">
    <div class="row">
        <!-- Left Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm rounded-3 border-0 h-100">
                <div class="card-header bg-gradient-primary text-white rounded-top-3 border-0">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-share-alt me-2"></i>Shared Notes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="badge bg-primary rounded-pill me-2 fs-6 px-3 py-2">
                            <?= count($data['notes']) ?>
                        </div>
                        <span class="fs-5">Notes shared with you</span>
                    </div>
                    
                    <div class="alert alert-info border-0 rounded-3 shadow-sm">
                        <div class="d-flex">
                            <div class="me-3 fs-4">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <div>
                                <h6 class="alert-heading">About Shared Notes</h6>
                                <p class="mb-0 small">Notes shared by others are organized by recency. Files with <span class="badge bg-success">Can Edit</span> support real-time collaboration.</p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if(!empty($data['notes'])): ?>
                    <div class="mt-4">
                        <div class="d-grid">
                            <a href="<?= BASE_URL ?>/notes" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to My Notes
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="col-lg-9">
            <?php if (Session::hasFlash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                    <div class="d-flex">
                        <div class="me-3"><i class="fas fa-check-circle fa-lg"></i></div>
                        <div><?= Session::getFlash('success') ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (Session::hasFlash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                    <div class="d-flex">
                        <div class="me-3"><i class="fas fa-exclamation-circle fa-lg"></i></div>
                        <div><?= Session::getFlash('error') ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (empty($data['notes'])): ?>
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body text-center p-5">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-share-alt"></i>
                            </div>
                            <h3 class="mt-4">No shared notes yet</h3>
                            <p class="text-muted mx-auto" style="max-width: 500px;">When someone shares a note with you, it will appear here. Shared notes enable seamless collaboration with your team.</p>
                            <a href="<?= BASE_URL ?>/notes" class="btn btn-primary mt-3">
                                <i class="fas fa-sticky-note me-2"></i>Go to My Notes
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    <?php foreach ($data['notes'] as $note): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 note-card">
                                <div class="card-header border-0 bg-transparent position-relative py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0 text-truncate">
                                            <?php if (isset($note['is_password_protected']) && $note['is_password_protected']): ?>
                                                <a href="<?= BASE_URL ?>/notes/verify-password/<?= $note['id'] ?>" class="text-decoration-none stretched-link">
                                                    <i class="fas fa-lock text-warning me-2"></i><?= htmlspecialchars($note['title']) ?>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= BASE_URL ?>/notes/<?= isset($note['can_edit']) && $note['can_edit'] ? 'edit' : 'view' ?>/<?= $note['id'] ?>" class="text-decoration-none stretched-link">
                                                    <?= htmlspecialchars($note['title']) ?>
                                                </a>
                                            <?php endif; ?>
                                        </h5>
                                    </div>
                                    <div class="mt-2">
                                        <?php if (isset($note['can_edit']) && $note['can_edit']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-edit me-1"></i>Can Edit
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-eye me-1"></i>Read Only
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($note['is_password_protected']) && $note['is_password_protected']): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-lock me-1"></i>Protected
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <div class="card-text note-content">
                                        <?php 
                                        $content = isset($note['content']) ? $note['content'] : '';
                                        $preview = strip_tags($content);
                                        $preview = substr($preview, 0, 150);
                                        if (strlen($content) > 150) $preview .= '...';
                                        echo nl2br(htmlspecialchars($preview));
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="card-footer bg-transparent border-top border-light">
                                    <div class="d-flex align-items-center">
                                        <div class="owner-avatar">
                                            <?= strtoupper(substr($note['owner_name'], 0, 1)) ?>
                                        </div>
                                        <div class="ms-2 flex-grow-1">
                                            <div class="small fw-bold"><?= htmlspecialchars($note['owner_name']) ?></div>
                                            <div class="small text-muted">
                                                <?php 
                                                $shared_at = new DateTime($note['shared_at']);
                                                $now = new DateTime();
                                                $interval = $shared_at->diff($now);
                                                
                                                if ($interval->days == 0) {
                                                    if ($interval->h == 0) {
                                                        if ($interval->i == 0) {
                                                            echo 'Just now';
                                                        } else {
                                                            echo $interval->i . ' min ago';
                                                        }
                                                    } else {
                                                        echo $interval->h . ' hours ago';
                                                    }
                                                } elseif ($interval->days == 1) {
                                                    echo 'Yesterday';
                                                } else {
                                                    echo $shared_at->format('M j, Y');
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        
                                        <?php if (isset($note['can_edit']) && $note['can_edit']): ?>
                                            <a href="<?= BASE_URL ?>/notes/edit/<?= $note['id'] ?>" class="btn btn-primary btn-sm stretched-link">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= BASE_URL ?>/notes/view/<?= $note['id'] ?>" class="btn btn-outline-secondary btn-sm stretched-link">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Add pagination controls if needed -->
                <?php if (count($data['notes']) > 12): ?>
                <div class="d-flex justify-content-center mt-4">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Real-time collaboration notification toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
    <div id="collaborationToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <i class="fas fa-users me-2"></i>
            <strong class="me-auto">Collaboration</strong>
            <small>Just now</small>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="collaborationToastBody">
            Someone is editing this note.
        </div>
    </div>
</div>

<!-- Enhanced styles -->
<style>
/* Card hover effect */
.note-card {
    transition: all 0.2s ease-in-out;
    border-radius: 0.75rem;
    overflow: hidden;
}

.note-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

/* Note content styling */
.note-content {
    font-size: 0.9rem;
    line-height: 1.5;
    color: #495057;
    max-height: 120px;
    overflow: hidden;
    position: relative;
}

.note-content::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 30px;
    background: linear-gradient(to bottom, rgba(255,255,255,0) 0%, rgba(255,255,255,1) 100%);
}

/* Owner avatar styling */
.owner-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: #4a89dc;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

/* Empty state styling */
.empty-state {
    padding: 2rem 1rem;
}

.empty-state-icon {
    font-size: 5rem;
    color: #e9ecef;
    background-color: #f8f9fa;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

/* Badge styling */
.badge {
    font-weight: 500;
    padding: 0.4em 0.6em;
    border-radius: 0.375rem;
}

/* Custom background gradient */
.bg-gradient-primary {
    background: linear-gradient(to right, #4a89dc, #5a9de9);
}

/* Remote cursor styles - enhanced */
.remote-cursor {
    position: absolute;
    z-index: 100;
    pointer-events: none;
}

.remote-cursor-label {
    position: absolute;
    top: -24px;
    left: 0;
    background-color: #3498db;
    color: white;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.remote-cursor-caret {
    width: 2px;
    height: 20px;
    background-color: #3498db;
    animation: blink 1s infinite;
    box-shadow: 0 0 4px rgba(52, 152, 219, 0.6);
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
</style>

<!-- Optional: Enhanced Toast JS -->
<script>
// Show a nice toast notification when page loads if there are new shared notes
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($data['notes'])): ?>
    const toastEl = document.getElementById('collaborationToast');
    const toastBody = document.getElementById('collaborationToastBody');
    
    if (toastEl && toastBody) {
        // Only show welcome message for shared notes
        toastBody.textContent = 'You have <?= count($data['notes']) ?> shared note(s) available';
        
        const toast = new bootstrap.Toast(toastEl, {
            animation: true,
            autohide: true,
            delay: 5000
        });
        
        setTimeout(() => toast.show(), 1000);
    }
    <?php endif; ?>
});
</script>