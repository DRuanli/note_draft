<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="d-md-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-3 mb-md-0">
                <i class="fas fa-bell me-2 text-primary"></i>My Notifications
            </h2>
            <?php if (!empty($data['notifications'])): ?>
            <a href="<?= BASE_URL ?>/notifications/mark-all-read" class="btn btn-outline-primary">
                <i class="fas fa-check-double me-1"></i> Mark All as Read
            </a>
            <?php endif; ?>
        </div>
        
        <?php if (empty($data['notifications'])): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="display-1 text-muted mb-3"><i class="fas fa-bell-slash"></i></div>
                    <h3>No notifications</h3>
                    <p class="text-muted">You don't have any notifications at the moment.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="list-group list-group-flush">
                    <?php foreach ($data['notifications'] as $notification): ?>
                        <div class="list-group-item list-group-item-action p-3 <?= !$notification['is_read'] ? 'bg-light' : '' ?>">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <?php if ($notification['type'] === 'new_shared_note'): ?>
                                    <div class="d-flex">
                                        <div class="me-3 text-primary fs-4">
                                            <i class="fas fa-share-alt"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">Note Shared With You</h5>
                                            <p class="mb-1">
                                                <strong><?= htmlspecialchars($notification['data']['owner_name']) ?></strong> shared the note 
                                                "<strong><?= htmlspecialchars($notification['data']['note_title']) ?></strong>" with you
                                                (<?= $notification['data']['permission'] ?>)
                                            </p>
                                            <div class="mt-2">
                                                <a href="<?= BASE_URL ?>/notes/shared" class="btn btn-sm btn-primary">View Note</a>
                                                <?php if (!$notification['is_read']): ?>
                                                    <a href="<?= BASE_URL ?>/notifications/mark-read/<?= $notification['id'] ?>" class="btn btn-sm btn-link">
                                                        Mark as Read
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif ($notification['type'] === 'share_permission_changed'): ?>
                                    <div class="d-flex">
                                        <div class="me-3 text-info fs-4">
                                            <i class="fas fa-edit"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">Sharing Permissions Updated</h5>
                                            <p class="mb-1">
                                                <strong><?= htmlspecialchars($notification['data']['owner_name']) ?></strong> changed your access to 
                                                "<strong><?= htmlspecialchars($notification['data']['note_title']) ?></strong>" 
                                                to <?= $notification['data']['permission'] ?>
                                            </p>
                                            <div class="mt-2">
                                                <a href="<?= BASE_URL ?>/notes/shared" class="btn btn-sm btn-primary">View Note</a>
                                                <?php if (!$notification['is_read']): ?>
                                                    <a href="<?= BASE_URL ?>/notifications/mark-read/<?= $notification['id'] ?>" class="btn btn-sm btn-link">
                                                        Mark as Read
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <small class="text-muted ms-3 text-end" style="min-width: 100px;">
                                    <?php
                                    $created_at = new DateTime($notification['created_at']);
                                    $now = new DateTime();
                                    $interval = $created_at->diff($now);

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
                                        echo $created_at->format('M j, Y');
                                    }
                                    ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.list-group-item.bg-light {
    position: relative;
}

.list-group-item.bg-light::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background-color: #4a89dc;
}
</style>