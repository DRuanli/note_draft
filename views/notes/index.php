<div class="notes-dashboard">
    <div class="row mb-4">
        <div class="col">
            <div class="d-sm-flex justify-content-between align-items-center">
                <h2 class="h3 mb-3 mb-sm-0 d-flex align-items-center">
                    <i class="fas fa-sticky-note me-2 text-primary"></i>My Notes
                </h2>
                <div class="d-flex flex-wrap gap-2">
                    <div class="search-container">
                        <div class="input-group">
                            <input type="text" id="search-input" class="form-control" placeholder="Search notes..." 
                                   value="<?= htmlspecialchars($data['search']) ?>">
                            <button id="clear-search" class="btn btn-outline-secondary" type="button" 
                                    <?= empty($data['search']) ? 'style="display:none"' : '' ?>>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="btn-group view-toggle">
                        <a href="<?= BASE_URL ?>/notes?view=grid<?= isset($_GET['label']) ? '&label=' . $_GET['label'] : '' ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" 
                           class="btn <?= $data['view'] === 'grid' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="fas fa-th-large"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/notes?view=list<?= isset($_GET['label']) ? '&label=' . $_GET['label'] : '' ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>" 
                           class="btn <?= $data['view'] === 'list' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="fas fa-list"></i>
                        </a>
                    </div>
                    <a href="<?= BASE_URL ?>/notes/create" class="btn btn-success new-note-btn">
                        <i class="fas fa-plus me-1"></i> New Note
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar with Labels -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm labels-sidebar">
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <h5 class="card-title mb-0">Labels</h5>
                    <a href="<?= BASE_URL ?>/labels" class="btn btn-sm btn-outline-primary rounded-circle">
                        <i class="fas fa-cog"></i>
                    </a>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?= BASE_URL ?>/notes?view=<?= $data['view'] ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>"
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= empty($data['current_label']) ? 'active' : '' ?>">
                        <span><i class="fas fa-sticky-note me-2"></i> All Notes</span>
                        <span class="badge bg-primary rounded-pill"><?= count($data['notes']) ?></span>
                    </a>
                    
                    <?php if(isset($data['labels']) && is_array($data['labels'])): ?>
                        <?php foreach ($data['labels'] as $label): ?>
                            <a href="<?= BASE_URL ?>/notes?view=<?= $data['view'] ?>&label=<?= $label['id'] ?><?= isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>"
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= isset($data['current_label']) && $data['current_label'] == $label['id'] ? 'active' : '' ?>">
                                <span><i class="fas fa-tag me-2"></i> <?= htmlspecialchars($label['name']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <?php if(isset($data['shared_notes']) && is_array($data['shared_notes']) && count($data['shared_notes']) > 0): ?>
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Shared</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="<?= BASE_URL ?>/notes/shared"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-share-alt me-2"></i> Shared with me</span>
                            <span class="badge bg-info rounded-pill"><?= count($data['shared_notes']) ?></span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Main Notes Content -->
        <div class="col-md-9">
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
            
            <?php if (empty($data['notes'])): ?>
                <div class="card shadow-sm empty-state">
                    <div class="card-body text-center py-5">
                        <?php if (!empty($data['search'])): ?>
                            <div class="empty-state-icon mb-3">
                                <i class="fas fa-search"></i>
                            </div>
                            <h3>No notes found</h3>
                            <p class="text-muted">No notes match your search "<?= htmlspecialchars($data['search']) ?>"</p>
                            <a href="<?= BASE_URL ?>/notes" class="btn btn-primary mt-3">Clear Search</a>
                        <?php elseif (!empty($data['current_label'])): ?>
                            <div class="empty-state-icon mb-3">
                                <i class="fas fa-tag"></i>
                            </div>
                            <h3>No notes with this label</h3>
                            <p class="text-muted">You don't have any notes with this label yet</p>
                            <a href="<?= BASE_URL ?>/notes/create" class="btn btn-primary mt-3">Create a Note</a>
                        <?php else: ?>
                            <div class="empty-state-icon mb-3">
                                <i class="fas fa-sticky-note"></i>
                            </div>
                            <h3>No notes yet</h3>
                            <p class="text-muted">Create your first note to get started</p>
                            <a href="<?= BASE_URL ?>/notes/create" class="btn btn-primary mt-3">Create a Note</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <?php if ($data['view'] === 'grid'): ?>
                    <!-- Grid View for Notes -->
                    <div class="notes-grid">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                            <?php foreach ($data['notes'] as $note): ?>
                                <?php 
                                $hasImages = isset($note['image_count']) && $note['image_count'] > 0; 
                                $isProtected = isset($note['is_password_protected']) && $note['is_password_protected'];
                                
                                // Get the URL for the note - either password verification or direct edit
                                $noteUrl = $isProtected 
                                    ? BASE_URL . '/notes/verify-password/' . $note['id'] 
                                    : BASE_URL . '/notes/edit/' . $note['id'];
                                ?>
                                <div class="col note-wrapper">
                                    <div class="card h-100 note-card <?= isset($note['is_pinned']) && $note['is_pinned'] ? 'pinned' : '' ?>">
                                        <?php if (isset($note['is_pinned']) && $note['is_pinned']): ?>
                                            <div class="pin-badge">
                                                <i class="fas fa-thumbtack"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Note image thumbnail if available -->
                                        <?php if ($hasImages && isset($note['images']) && !empty($note['images'])): 
                                            $firstImage = $note['images'][0];
                                        ?>
                                        <div class="card-img-top position-relative note-thumbnail-container">
                                            <a href="<?= $noteUrl ?>">
                                                <img src="<?= UPLOADS_URL . '/' . $firstImage['file_path'] ?>" 
                                                     class="note-thumbnail" alt="Note image">
                                                
                                                <?php if ($note['image_count'] > 1): ?>
                                                    <div class="image-count-badge">
                                                        <i class="fas fa-images me-1"></i> <?= $note['image_count'] ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($isProtected): ?>
                                                    <div class="image-lock-overlay">
                                                        <i class="fas fa-lock"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="card-header pb-0 bg-transparent d-flex justify-content-between align-items-center">
                                            <h5 class="card-title mb-0 text-truncate">
                                                <a href="<?= $noteUrl ?>" class="note-title-link">
                                                    <?php if ($isProtected): ?>
                                                        <i class="fas fa-lock me-1 text-warning"></i>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($note['title']) ?>
                                                </a>
                                            </h5>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link text-muted note-actions-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    <li>
                                                        <button class="dropdown-item pin-note" data-id="<?= $note['id'] ?>">
                                                            <i class="fas fa-thumbtack me-2 <?= isset($note['is_pinned']) && $note['is_pinned'] ? 'text-primary' : '' ?>"></i>
                                                            <?= isset($note['is_pinned']) && $note['is_pinned'] ? 'Unpin' : 'Pin' ?>
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="<?= $noteUrl ?>">
                                                            <i class="fas fa-edit me-2"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="<?= BASE_URL ?>/notes/share/<?= $note['id'] ?>">
                                                            <i class="fas fa-share-alt me-2"></i> Share
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="<?= BASE_URL ?>/notes/toggle-password/<?= $note['id'] ?>">
                                                            <?php if ($isProtected): ?>
                                                                <i class="fas fa-unlock me-2"></i> Remove Password
                                                            <?php else: ?>
                                                                <i class="fas fa-lock me-2"></i> Add Password
                                                            <?php endif; ?>
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger delete-note" href="<?= BASE_URL ?>/notes/delete/<?= $note['id'] ?>">
                                                            <i class="fas fa-trash me-2"></i> Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <div class="card-body">
                                            <div class="card-text note-content">
                                                <?php 
                                                if ($isProtected) {
                                                    // Show protected content placeholder
                                                    echo '<div class="protected-content text-center p-3">';
                                                    echo '<i class="fas fa-lock text-warning mb-2"></i>';
                                                    echo '<p class="mb-0">This note is password protected</p>';
                                                    echo '<a href="' . $noteUrl . '" class="btn btn-sm btn-outline-warning mt-2">Unlock</a>';
                                                    echo '</div>';
                                                } else {
                                                    // Show actual content preview
                                                    $content = isset($note['content']) ? $note['content'] : '';
                                                    $preview = strip_tags($content);
                                                    $preview = substr($preview, 0, 150);
                                                    if (strlen($content) > 150) $preview .= '...';
                                                    echo nl2br(htmlspecialchars($preview));
                                                }
                                                ?>
                                            </div>
                                            
                                            <?php if (isset($note['labels']) && !empty($note['labels'])): ?>
                                                <div class="note-labels mt-3">
                                                    <?php foreach ($note['labels'] as $label): ?>
                                                        <span class="note-label">
                                                            <?= htmlspecialchars($label['name'] ?? '') ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                                            <div class="note-indicators">
                                                <?php if (isset($note['is_pinned']) && $note['is_pinned']): ?>
                                                    <span class="note-indicator" title="Pinned">
                                                        <i class="fas fa-thumbtack"></i>
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if ($isProtected): ?>
                                                    <span class="note-indicator text-warning" title="Password Protected">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if (isset($note['is_shared']) && $note['is_shared']): ?>
                                                    <span class="note-indicator text-info" title="Shared with others">
                                                        <i class="fas fa-share-alt"></i>
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if ($hasImages): ?>
                                                    <span class="note-indicator" title="<?= $note['image_count'] ?> image(s) attached">
                                                        <i class="fas fa-image"></i> <?= $note['image_count'] ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="note-date">
                                                <?php 
                                                if (isset($note['updated_at'])) {
                                                    $updated = new DateTime($note['updated_at']);
                                                    
                                                    if ($updated->format('Y-m-d') === date('Y-m-d')) {
                                                        // Today, show time
                                                        echo 'Today at ' . $updated->format('g:i A');
                                                    } else if ($updated->format('Y-m-d') === date('Y-m-d', strtotime('-1 day'))) {
                                                        // Yesterday
                                                        echo 'Yesterday at ' . $updated->format('g:i A');
                                                    } else {
                                                        // Another day
                                                        echo $updated->format('M j, Y');
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- List View for Notes -->
                    <div class="card shadow-sm notes-list">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px"></th>
                                        <th>Title</th>
                                        <th>Content</th>
                                        <th>Labels</th>
                                        <th style="width: 180px">Last Modified</th>
                                        <th style="width: 120px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['notes'] as $note): ?>
                                        <?php 
                                        $isProtected = isset($note['is_password_protected']) && $note['is_password_protected'];
                                        $hasImages = isset($note['image_count']) && $note['image_count'] > 0;
                                        
                                        // Get the URL for the note - either password verification or direct edit
                                        $noteUrl = $isProtected 
                                            ? BASE_URL . '/notes/verify-password/' . $note['id'] 
                                            : BASE_URL . '/notes/edit/' . $note['id'];
                                        ?>
                                        <tr class="note-list-item <?= isset($note['is_pinned']) && $note['is_pinned'] ? 'table-pinned' : '' ?>">
                                            <td class="text-center note-list-icons">
                                                <?php if (isset($note['is_pinned']) && $note['is_pinned']): ?>
                                                    <i class="fas fa-thumbtack text-primary me-1" title="Pinned"></i>
                                                <?php endif; ?>
                                                
                                                <?php if ($isProtected): ?>
                                                    <i class="fas fa-lock text-warning me-1" title="Password Protected"></i>
                                                <?php endif; ?>
                                                
                                                <?php if (isset($note['is_shared']) && $note['is_shared']): ?>
                                                    <i class="fas fa-share-alt text-info me-1" title="Shared with others"></i>
                                                <?php endif; ?>
                                                
                                                <?php if ($hasImages): ?>
                                                    <i class="fas fa-image text-secondary me-1" title="<?= $note['image_count'] ?> image(s) attached"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($hasImages && isset($note['images']) && !empty($note['images'])): ?>
                                                    <div class="list-view-thumbnail me-2">
                                                        <a href="<?= $noteUrl ?>">
                                                            <img src="<?= UPLOADS_URL . '/' . $note['images'][0]['file_path'] ?>" 
                                                                 alt="Note thumbnail" class="rounded">
                                                            <?php if ($isProtected): ?>
                                                                <div class="image-lock-overlay-small">
                                                                    <i class="fas fa-lock"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </a>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <strong>
                                                        <a href="<?= $noteUrl ?>" class="note-title-link">
                                                            <?= htmlspecialchars($note['title']) ?>
                                                        </a>
                                                    </strong>
                                                </div>
                                            </td>
                                            <td class="text-truncate" style="max-width: 250px;">
                                                <?php 
                                                if ($isProtected) {
                                                    echo '<span class="text-warning"><i class="fas fa-lock me-1"></i>Protected content</span>';
                                                } else {
                                                    $content = isset($note['content']) ? $note['content'] : '';
                                                    $preview = strip_tags($content);
                                                    $preview = substr($preview, 0, 100);
                                                    if (strlen($content) > 100) $preview .= '...';
                                                    echo htmlspecialchars($preview);
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if (isset($note['labels']) && !empty($note['labels'])): ?>
                                                    <div class="note-labels-list">
                                                        <?php foreach ($note['labels'] as $label): ?>
                                                            <span class="note-label">
                                                                <?= htmlspecialchars($label['name'] ?? '') ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <small class="text-muted">None</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="note-date">
                                                    <?php 
                                                    if (isset($note['updated_at'])) {
                                                        $updated = new DateTime($note['updated_at']);
                                                        
                                                        if ($updated->format('Y-m-d') === date('Y-m-d')) {
                                                            // Today, show time
                                                            echo 'Today at ' . $updated->format('g:i A');
                                                        } else if ($updated->format('Y-m-d') === date('Y-m-d', strtotime('-1 day'))) {
                                                            // Yesterday
                                                            echo 'Yesterday at ' . $updated->format('g:i A');
                                                        } else {
                                                            // Another day
                                                            echo $updated->format('M j, Y g:i A');
                                                        }
                                                    }
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                            <div class="note-actions">
                                                <button class="btn btn-action pin-note" data-id="<?= $note['id'] ?>" title="<?= isset($note['is_pinned']) && $note['is_pinned'] ? 'Unpin' : 'Pin' ?>">
                                                    <i class="fas fa-thumbtack <?= isset($note['is_pinned']) && $note['is_pinned'] ? 'text-primary' : '' ?>"></i>
                                                </button>
                                                
                                                <a href="<?= $noteUrl ?>" class="btn btn-action" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <a href="<?= BASE_URL ?>/notes/share/<?= $note['id'] ?>" class="btn btn-action" title="Share">
                                                    <i class="fas fa-share-alt"></i>
                                                </a>
                                                
                                                <a href="<?= BASE_URL ?>/notes/toggle-password/<?= $note['id'] ?>" class="btn btn-action" title="<?= $isProtected ? 'Remove Password' : 'Add Password' ?>">
                                                    <i class="fas fa-<?= $isProtected ? 'unlock' : 'lock' ?>"></i>
                                                </a>
                                                
                                                <a href="<?= BASE_URL ?>/notes/delete/<?= $note['id'] ?>" class="btn btn-action delete-note" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
:root {
    --primary-color: #4a89dc;
    --primary-hover: #3a77c5;
    --secondary-color: #6c757d;
    --light-bg: #f8f9fa;
    --border-radius: 12px;
    --small-radius: 8px;
    --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    --box-shadow-hover: 0 10px 25px rgba(0, 0, 0, 0.12);
    --transition: all 0.3s ease;
}

/* Notes Dashboard Styling */
.notes-dashboard {
    margin-bottom: 30px;
}

/* Enhanced Search Box */
.search-container {
    position: relative;
    min-width: 250px;
}

.search-container .input-group {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border-radius: var(--small-radius);
    transition: var(--transition);
}

.search-container .input-group:focus-within {
    box-shadow: 0 5px 15px rgba(74, 137, 220, 0.2);
}

.search-container .form-control {
    border-radius: var(--small-radius) 0 0 var(--small-radius);
    border: 1px solid rgba(0, 0, 0, 0.1);
    padding: 0.6rem 1rem;
}

.search-container .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: none;
}

.search-container .btn {
    border-radius: 0 var(--small-radius) var(--small-radius) 0;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-left: none;
}

/* View Toggle Buttons */
.view-toggle .btn {
    padding: 0.6rem 0.8rem;
    border-radius: 0;
}

.view-toggle .btn:first-child {
    border-radius: var(--small-radius) 0 0 var(--small-radius);
}

.view-toggle .btn:last-child {
    border-radius: 0 var(--small-radius) var(--small-radius) 0;
}

/* New Note Button */
.new-note-btn {
    border-radius: var(--small-radius);
    padding: 0.6rem 1.25rem;
    transition: var(--transition);
    font-weight: 500;
    box-shadow: 0 4px 10px rgba(40, 167, 69, 0.2);
    background: linear-gradient(45deg, #28a745, #34ce57);
    border: none;
}

.new-note-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(40, 167, 69, 0.3);
    background: linear-gradient(45deg, #218838, #2aba4e);
}

/* Labels Sidebar Styling */
.labels-sidebar {
    border-radius: var(--border-radius);
    overflow: hidden;
    transition: var(--transition);
    box-shadow: var(--box-shadow);
    border: none;
}

.labels-sidebar:hover {
    box-shadow: var(--box-shadow-hover);
}

.labels-sidebar .card-header {
    padding: 15px;
    background-color: rgba(248, 249, 250, 0.5);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.labels-sidebar .list-group-item {
    padding: 0.75rem 1rem;
    border-left: 3px solid transparent;
    transition: var(--transition);
    border-right: none;
    border-left: none;
    border-color: rgba(0, 0, 0, 0.05);
}

.labels-sidebar .list-group-item:hover {
    background-color: rgba(0, 0, 0, 0.02);
    transform: translateX(5px);
}

.labels-sidebar .list-group-item.active {
    background-color: rgba(74, 137, 220, 0.1);
    color: var(--primary-color);
    font-weight: 500;
    border-right: none;
    border-left: 3px solid var(--primary-color);
}

.labels-sidebar .badge {
    transition: var(--transition);
}

.labels-sidebar .list-group-item:hover .badge {
    transform: scale(1.1);
}

/* Empty State */
.empty-state {
    border-radius: var(--border-radius);
    overflow: hidden;
    transition: var(--transition);
    box-shadow: var(--box-shadow);
    border: none;
}

.empty-state:hover {
    box-shadow: var(--box-shadow-hover);
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    font-size: 2.5rem;
    margin: 0 auto;
    color: #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #f8f9fa;
    transition: var(--transition);
}

.empty-state:hover .empty-state-icon {
    color: var(--primary-color);
    transform: scale(1.1);
}

/* Notes Grid Styling */
.notes-grid .note-wrapper {
    transition: var(--transition);
}

.notes-grid .note-card {
    border-radius: var(--border-radius);
    overflow: hidden;
    transition: var(--transition);
    box-shadow: var(--box-shadow);
    border: none;
    position: relative;
}

.notes-grid .note-wrapper:hover {
    transform: translateY(-5px);
}

.notes-grid .note-card:hover {
    box-shadow: var(--box-shadow-hover);
}

.notes-grid .note-card.pinned {
    border-top: 3px solid var(--primary-color);
}

.notes-grid .card-header {
    padding: 15px 15px 0;
    border-bottom: none;
}

.notes-grid .card-body {
    padding: 10px 15px;
}

.notes-grid .card-footer {
    padding: 10px 15px;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    font-size: 0.8rem;
}

.pin-badge {
    position: absolute;
    top: 0;
    right: 20px;
    background-color: var(--primary-color);
    color: white;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0 0 15px 15px;
    transition: var(--transition);
    transform-origin: top center;
}

.notes-grid .note-card:hover .pin-badge {
    height: 35px;
}

/* Note Thumbnail */
.note-thumbnail-container {
    height: 180px;
    overflow: hidden;
    position: relative;
}

.note-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.note-thumbnail-container:hover .note-thumbnail {
    transform: scale(1.05);
}

.image-count-badge {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background-color: rgba(0, 0, 0, 0.6);
    color: white;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 0.75rem;
    transition: var(--transition);
}

.note-thumbnail-container:hover .image-count-badge {
    background-color: var(--primary-color);
}

.image-lock-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    opacity: 0.8;
    transition: var(--transition);
}

.note-thumbnail-container:hover .image-lock-overlay {
    opacity: 0.9;
    background-color: rgba(0, 0, 0, 0.6);
}

/* Note Content */
.note-title-link {
    color: inherit;
    text-decoration: none;
    transition: var(--transition);
}

.note-title-link:hover {
    color: var(--primary-color);
}

.note-content {
    color: #555;
    font-size: 0.9rem;
    line-height: 1.5;
    max-height: 120px;
    overflow: hidden;
}

.protected-content {
    background-color: rgba(255, 193, 7, 0.1);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px !important;
}

.protected-content i {
    font-size: 2rem;
    margin-bottom: 10px;
}

/* Note Labels */
.note-labels {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 10px;
}

.note-label {
    background-color: rgba(74, 137, 220, 0.1);
    color: var(--primary-color);
    border-radius: 20px;
    padding: 2px 10px;
    font-size: 0.75rem;
    transition: var(--transition);
}

.note-label:hover {
    background-color: rgba(74, 137, 220, 0.2);
    transform: translateY(-2px);
}

/* Note Indicators */
.note-indicators {
    display: flex;
    gap: 10px;
}

.note-indicator {
    color: #6c757d;
    font-size: 0.8rem;
    transition: var(--transition);
}

.note-indicator:hover {
    color: var(--primary-color);
}

.note-indicator.text-warning:hover {
    color: #ffc107 !important;
}

.note-indicator.text-info:hover {
    color: #17a2b8 !important;
}

.note-date {
    color: #6c757d;
    font-size: 0.8rem;
}

/* Note Actions */
.note-actions-toggle {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    padding: 0;
    margin-right: -10px;
    transition: var(--transition);
}

.note-actions-toggle:hover {
    background-color: rgba(0, 0, 0, 0.05);
    color: var(--primary-color) !important;
}

/* List View Styling */
.notes-list {
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    border: none;
    transition: var(--transition);
}

.notes-list:hover {
    box-shadow: var(--box-shadow-hover);
}

.notes-list .table {
    margin-bottom: 0;
}

.notes-list .table th {
    font-weight: 600;
    color: #495057;
}

.note-list-item {
    transition: var(--transition);
}

.note-list-item:hover {
    background-color: rgba(74, 137, 220, 0.05);
}

.note-list-item.table-pinned {
    background-color: rgba(74, 137, 220, 0.05);
}

.note-list-item.table-pinned:hover {
    background-color: rgba(74, 137, 220, 0.1);
}

.note-list-item td {
    vertical-align: middle;
    padding: 12px 8px;
}

/* List View Thumbnail */
.list-view-thumbnail {
    position: relative;
    width: 50px;
    height: 50px;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
}

.list-view-thumbnail:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
}

.list-view-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-lock-overlay-small {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.8rem;
}

/* List View Labels */
.note-labels-list {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

/* List View Actions */
.note-actions {
    display: flex;
    gap: 5px;
}

.btn-action {
    width: 30px;
    height: 30px;
    padding: 0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: transparent;
    color: #6c757d;
    border: none;
    transition: var(--transition);
}

.btn-action:hover {
    background-color: rgba(0, 0, 0, 0.05);
    color: var(--primary-color);
    transform: translateY(-2px);
}

.btn-action:focus {
    box-shadow: none;
}

.btn-action .fa-trash:hover {
    color: #dc3545;
}

/* Responsive Adjustments */
@media (max-width: 767px) {
    .search-container {
        width: 100%;
    }
    
    .view-toggle, .new-note-btn {
        margin-top: 10px;
    }
    
    .notes-list th, .notes-list td:nth-child(3), .notes-list td:nth-child(4) {
        display: none;
    }
    
    .notes-list td:nth-child(5) {
        font-size: 0.7rem;
    }
    
    .note-list-icons {
        display: flex;
        gap: 5px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('search-input');
    const clearSearchBtn = document.getElementById('clear-search');
    
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Show/hide clear button
            if (clearSearchBtn) {
                clearSearchBtn.style.display = this.value ? 'block' : 'none';
            }
            
            // Set timeout for search
            searchTimeout = setTimeout(function() {
                // Get current URL and update search parameter
                const url = new URL(window.location.href);
                if (searchInput.value) {
                    url.searchParams.set('search', searchInput.value);
                } else {
                    url.searchParams.delete('search');
                }
                
                // Show loading indicator (if you have one)
                document.body.classList.add('searching');
                
                // Navigate to the URL
                window.location.href = url.toString();
            }, 500); // 500ms delay for typing
        });
        
        // Add focus animation
        searchInput.addEventListener('focus', function() {
            this.closest('.search-container').classList.add('focused');
        });
        
        searchInput.addEventListener('blur', function() {
            this.closest('.search-container').classList.remove('focused');
        });
        
        // Clear search button
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                this.style.display = 'none';
                
                // Remove search parameter and reload
                const url = new URL(window.location.href);
                url.searchParams.delete('search');
                window.location.href = url.toString();
            });
        }
    }
    
    // Modified pin/unpin note functionality without animations
    const pinButtons = document.querySelectorAll('.pin-note');
    
    if (pinButtons.length > 0) {
        pinButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const noteId = this.getAttribute('data-id');
                const icon = this.querySelector('i');
                const noteCard = this.closest('.note-card') || this.closest('.note-list-item');
                
                // Update button appearance immediately (optimistic UI)
                const isPinned = icon.classList.contains('text-primary');
                
                // Update icon state without animation
                if (isPinned) {
                    icon.classList.remove('text-primary');
                } else {
                    icon.classList.add('text-primary');
                }
                
                // Update button text if it exists
                const buttonText = this.textContent.trim();
                if (buttonText) {
                    this.innerHTML = `<i class="fas fa-thumbtack ${!isPinned ? 'text-primary' : ''}"></i> ${isPinned ? 'Pin' : 'Unpin'}`;
                }
                
                // Update note card styling for grid view
                if (noteCard && noteCard.classList.contains('note-card')) {
                    if (isPinned) {
                        noteCard.classList.remove('pinned');
                    } else {
                        noteCard.classList.add('pinned');
                    }
                }
                
                // Update row styling for list view
                if (noteCard && noteCard.classList.contains('note-list-item')) {
                    if (isPinned) {
                        noteCard.classList.remove('table-pinned');
                    } else {
                        noteCard.classList.add('table-pinned');
                    }
                }
                
                // Send AJAX request
                fetch(BASE_URL + '/notes/toggle-pin/' + noteId, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        if (data.redirect) {
                            // Redirect to password verification if needed
                            window.location.href = data.redirect;
                        } else {
                            // Revert changes if request failed
                            if (isPinned) {
                                icon.classList.add('text-primary');
                            } else {
                                icon.classList.remove('text-primary');
                            }
                            
                            // Revert note card/row styling
                            if (noteCard && noteCard.classList.contains('note-card')) {
                                if (isPinned) {
                                    noteCard.classList.add('pinned');
                                } else {
                                    noteCard.classList.remove('pinned');
                                }
                            }
                            
                            if (noteCard && noteCard.classList.contains('note-list-item')) {
                                if (isPinned) {
                                    noteCard.classList.add('table-pinned');
                                } else {
                                    noteCard.classList.remove('table-pinned');
                                }
                            }
                            
                            console.error('Error:', data.message);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Revert changes if request failed
                    if (isPinned) {
                        icon.classList.add('text-primary');
                    } else {
                        icon.classList.remove('text-primary');
                    }
                    
                    // Revert note card/row styling
                    if (noteCard && noteCard.classList.contains('note-card')) {
                        if (isPinned) {
                            noteCard.classList.add('pinned');
                        } else {
                            noteCard.classList.remove('pinned');
                        }
                    }
                    
                    if (noteCard && noteCard.classList.contains('note-list-item')) {
                        if (isPinned) {
                            noteCard.classList.add('table-pinned');
                        } else {
                            noteCard.classList.remove('table-pinned');
                        }
                    }
                });
            });
        });
    }
    
    // Delete confirmation with fancy effects
    const deleteLinks = document.querySelectorAll('.delete-note');
    
    if (deleteLinks.length > 0) {
        deleteLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Create a fancy confirmation dialog
                const dialogOverlay = document.createElement('div');
                dialogOverlay.className = 'delete-dialog-overlay';
                
                const dialogBox = document.createElement('div');
                dialogBox.className = 'delete-dialog';
                
                dialogBox.innerHTML = `
                    <div class="delete-dialog-icon">
                        <i class="fas fa-trash"></i>
                    </div>
                    <h3>Delete Note?</h3>
                    <p>This action cannot be undone.</p>
                    <div class="delete-dialog-buttons">
                        <button class="btn btn-outline-secondary cancel-delete">Cancel</button>
                        <button class="btn btn-danger confirm-delete">Delete</button>
                    </div>
                `;
                
                dialogOverlay.appendChild(dialogBox);
                document.body.appendChild(dialogOverlay);
                
                // Animate dialog appearance
                setTimeout(() => {
                    dialogOverlay.classList.add('active');
                    dialogBox.classList.add('active');
                }, 10);
                
                // Handle cancel button
                dialogBox.querySelector('.cancel-delete').addEventListener('click', function() {
                    dialogBox.classList.remove('active');
                    dialogOverlay.classList.remove('active');
                    
                    setTimeout(() => {
                        document.body.removeChild(dialogOverlay);
                    }, 300);
                });
                
                // Handle confirm button
                dialogBox.querySelector('.confirm-delete').addEventListener('click', function() {
                    dialogBox.classList.add('loading');
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';
                    
                    // Navigate to the original delete URL
                    window.location.href = link.href;
                });
            });
        });
    }

    // Add CSS for delete dialog
    const styleSheet = document.createElement('style');
    styleSheet.innerHTML = `
        .delete-dialog-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .delete-dialog-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .delete-dialog {
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            transform: translateY(30px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .delete-dialog.active {
            transform: translateY(0);
            opacity: 1;
        }
        
        .delete-dialog-icon {
            width: 70px;
            height: 70px;
            background-color: #f8d7da;
            color: #dc3545;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 20px;
        }
        
        .delete-dialog h3 {
            margin-bottom: 10px;
            color: #343a40;
        }
        
        .delete-dialog p {
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .delete-dialog-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .delete-dialog.loading {
            pointer-events: none;
            opacity: 0.7;
        }
    `;
    document.head.appendChild(styleSheet);
});
</script>