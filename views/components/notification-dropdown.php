<!-- Notifications Dropdown -->
<li class="nav-item dropdown">
    <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <?php if ($unread_count > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unread_count ?>
                <span class="visually-hidden">unread notifications</span>
            </span>
        <?php endif; ?>
    </a>
    <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notificationsDropdown" style="width: 300px; max-height: 500px; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
            <h6 class="dropdown-header m-0 p-0">Notifications</h6>
            <?php if ($unread_count > 0): ?>
                <a href="<?= BASE_URL ?>/notifications/mark-all-read" class="btn btn-sm btn-link text-decoration-none">Mark all read</a>
            <?php endif; ?>
        </div>
        
        <?php if (empty($unread_notifications)): ?>
            <div class="p-3 text-center text-muted">
                <p class="mb-0">No new notifications</p>
            </div>
        <?php else: ?>
            <div class="notification-list">
                <?php 
                // Group notifications by entity (note) to avoid duplicates
                $grouped_notifications = [];
                foreach ($unread_notifications as $notification) {
                    if (!empty($notification['data']['note_id'])) {
                        $key = $notification['type'] . '-' . $notification['data']['note_id'];
                        // Keep only the most recent notification per note and type
                        if (!isset($grouped_notifications[$key]) || 
                            strtotime($notification['created_at']) > strtotime($grouped_notifications[$key]['created_at'])) {
                            $grouped_notifications[$key] = $notification;
                        }
                    } else {
                        // For notifications without note_id, keep as is
                        $grouped_notifications[] = $notification;
                    }
                }
                
                // Display the grouped notifications
                foreach ($grouped_notifications as $notification): 
                ?>
                    <div class="dropdown-item p-2 border-bottom">
                        <?php if ($notification['type'] === 'new_shared_note'): ?>
                            <div class="d-flex">
                                <div class="me-2 text-primary">
                                    <i class="fas fa-share-alt"></i>
                                </div>
                                <div>
                                    <p class="mb-1 fw-bold">Note Shared With You</p>
                                    <p class="mb-1 small">
                                        <strong><?= htmlspecialchars($notification['data']['owner_name']) ?></strong> shared 
                                        "<strong><?= htmlspecialchars($notification['data']['note_title']) ?></strong>"
                                    </p>
                                    <p class="text-muted small mb-1">
                                        <?= formatTimeAgo($notification['created_at']) ?>
                                    </p>
                                    <div class="d-flex mt-1">
                                        <a href="<?= BASE_URL ?>/notes/shared" class="btn btn-sm btn-primary me-2">View</a>
                                        <a href="<?= BASE_URL ?>/notifications/mark-read/<?= $notification['id'] ?>" class="btn btn-sm btn-link">
                                            Dismiss
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php elseif ($notification['type'] === 'share_permission_changed'): ?>
                            <div class="d-flex">
                                <div class="me-2 text-info">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div>
                                    <p class="mb-1 fw-bold">Permissions Updated</p>
                                    <p class="mb-1 small">
                                        Your access to "<strong><?= htmlspecialchars($notification['data']['note_title']) ?></strong>" 
                                        is now <span class="<?= $notification['data']['permission'] === 'edit' ? 'text-success' : 'text-secondary' ?>">
                                            <?= $notification['data']['permission'] ?>
                                        </span>
                                    </p>
                                    <p class="text-muted small mb-1">
                                        <?= formatTimeAgo($notification['created_at']) ?>
                                    </p>
                                    <div class="d-flex mt-1">
                                        <a href="<?= BASE_URL ?>/notes/shared" class="btn btn-sm btn-primary me-2">View</a>
                                        <a href="<?= BASE_URL ?>/notifications/mark-read/<?= $notification['id'] ?>" class="btn btn-sm btn-link">
                                            Dismiss
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center p-2 border-top">
                <a href="<?= BASE_URL ?>/notifications" class="btn btn-sm btn-link text-decoration-none">
                    View all notifications
                </a>
            </div>
        <?php endif; ?>
    </div>
</li>

<?php
// Helper function to format time ago
function formatTimeAgo($timestamp) {
    $now = new DateTime();
    $past = new DateTime($timestamp);
    $interval = $past->diff($now);
    
    if ($interval->y > 0) {
        return $interval->y . ' year' . ($interval->y != 1 ? 's' : '') . ' ago';
    }
    
    if ($interval->m > 0) {
        return $interval->m . ' month' . ($interval->m != 1 ? 's' : '') . ' ago';
    }
    
    if ($interval->d > 0) {
        if ($interval->d == 1) {
            return 'Yesterday';
        }
        return $interval->d . ' days ago';
    }
    
    if ($interval->h > 0) {
        return $interval->h . ' hour' . ($interval->h != 1 ? 's' : '') . ' ago';
    }
    
    if ($interval->i > 0) {
        return $interval->i . ' min ago';
    }
    
    return 'Just now';
}
?>