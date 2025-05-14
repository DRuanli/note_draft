/**
 * Notifications JavaScript functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Handle notification badge updates
    const updateNotificationBadge = function(count) {
        const badge = document.getElementById('notificationsDropdown');
        if (badge) {
            if (count > 0) {
                badge.setAttribute('data-count', count);
            } else {
                badge.removeAttribute('data-count');
            }
        }
    };
    
    // Mark notification as read via AJAX
    const markAsRead = function(notificationId, element) {
        fetch(BASE_URL + '/notifications/mark-read/' + notificationId, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the unread indicator
                if (element) {
                    element.classList.remove('unread');
                }
                
                // Update the badge count
                const currentCount = parseInt(document.getElementById('notificationsDropdown').getAttribute('data-count') || '0');
                if (currentCount > 0) {
                    updateNotificationBadge(currentCount - 1);
                }
            }
        })
        .catch(error => console.error('Error:', error));
    };
    
    // Mark all as read via AJAX
    const markAllAsRead = function() {
        fetch(BASE_URL + '/notifications/mark-all-read', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove all unread indicators
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                });
                
                // Update the badge count
                updateNotificationBadge(0);
            }
        })
        .catch(error => console.error('Error:', error));
    };
    
    // Setup event listeners for mark as read buttons
    document.querySelectorAll('.mark-notification-read').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const notificationId = this.getAttribute('data-id');
            const notificationItem = this.closest('.notification-item');
            
            markAsRead(notificationId, notificationItem);
        });
    });
    
    // Setup event listener for mark all as read button
    const markAllReadBtn = document.querySelector('.mark-all-read');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            markAllAsRead();
        });
    }
    
    // Optional: Poll for new notifications
    let notificationCheckInterval;
    
    const checkForNewNotifications = function() {
        fetch(BASE_URL + '/api/notifications/check', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count > 0) {
                // Update badge
                updateNotificationBadge(data.count);
                
                // Show notification alert if specified
                if (data.new_notifications && data.new_notifications.length > 0 && 
                    Notification.permission === 'granted') {
                    // Browser notification
                    const latestNotification = data.new_notifications[0];
                    new Notification('New Notification', {
                        body: latestNotification.message,
                        icon: BASE_URL + '/assets/img/notification-icon.png'
                    });
                }
            }
        })
        .catch(error => console.error('Error checking notifications:', error));
    };
    
    // Request permission for browser notifications
    if ('Notification' in window && Notification.permission !== 'denied') {
        Notification.requestPermission();
    }
    
    // Start polling (every 60 seconds)
    if (typeof ENABLE_NOTIFICATION_POLLING !== 'undefined' && ENABLE_NOTIFICATION_POLLING) {
        notificationCheckInterval = setInterval(checkForNewNotifications, 60000);
    }
    
    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        if (notificationCheckInterval) {
            clearInterval(notificationCheckInterval);
        }
    });
});