/**
 * Notes JavaScript functionality
 */
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
            clearSearchBtn.style.display = this.value ? 'block' : 'none';
            
            // Set timeout for search
            searchTimeout = setTimeout(function() {
                // Get current URL and update search parameter
                const url = new URL(window.location.href);
                if (searchInput.value) {
                    url.searchParams.set('search', searchInput.value);
                } else {
                    url.searchParams.delete('search');
                }
                
                // Navigate to the URL
                window.location.href = url.toString();
            }, 300); // 300ms delay for typing
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
    
    // Pin/unpin note functionality
    const pinButtons = document.querySelectorAll('.pin-note');
    
    if (pinButtons.length > 0) {
        pinButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const noteId = this.getAttribute('data-id');
                const icon = this.querySelector('i');
                
                // Send AJAX request
                fetch(BASE_URL + '/notes/toggle-pin/' + noteId, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Toggle pinned state visually
                        if (data.is_pinned) {
                            icon.classList.add('pinned');
                            this.setAttribute('title', 'Unpin');
                            
                            // Add pinned class to parent note card/row
                            if (this.closest('.note-card')) {
                                this.closest('.note-card').classList.add('pinned');
                            } else if (this.closest('tr')) {
                                this.closest('tr').classList.add('pinned');
                            }
                        } else {
                            icon.classList.remove('pinned');
                            this.setAttribute('title', 'Pin');
                            
                            // Remove pinned class from parent note card/row
                            if (this.closest('.note-card')) {
                                this.closest('.note-card').classList.remove('pinned');
                            } else if (this.closest('tr')) {
                                this.closest('tr').classList.remove('pinned');
                            }
                        }
                        
                        // Show success message
                        const successMsg = document.createElement('div');
                        successMsg.className = 'alert alert-success';
                        successMsg.setAttribute('data-auto-dismiss', '3000');
                        successMsg.textContent = data.message;
                        
                        // Insert at the top of notes-content
                        const notesContent = document.querySelector('.notes-content');
                        if (notesContent) {
                            notesContent.insertBefore(successMsg, notesContent.firstChild);
                            
                            // Auto dismiss
                            setTimeout(function() {
                                successMsg.style.opacity = '0';
                                setTimeout(function() {
                                    if (successMsg.parentNode) {
                                        successMsg.parentNode.removeChild(successMsg);
                                    }
                                }, 300);
                            }, 3000);
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    }
    
    // Delete confirmation
    const deleteLinks = document.querySelectorAll('a.delete-note');
    
    if (deleteLinks.length > 0) {
        deleteLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this note? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    }
    
    // Delete image
    const deleteImageLinks = document.querySelectorAll('a.delete-image');
    
    if (deleteImageLinks.length > 0) {
        deleteImageLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!confirm('Are you sure you want to delete this image?')) {
                    return;
                }
                
                const imageId = this.getAttribute('data-id');
                const imagePreview = this.closest('.image-preview');
                
                // Send AJAX request
                fetch(this.getAttribute('href'), {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the image preview
                        if (imagePreview) {
                            imagePreview.remove();
                        }
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    }
});