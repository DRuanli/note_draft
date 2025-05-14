<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="d-md-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-3 mb-md-0">
                <i class="fas fa-tags me-2 text-primary"></i>Manage Labels
            </h2>
            <a href="<?= BASE_URL ?>/notes" class="btn btn-outline-primary rounded-pill">
                <i class="fas fa-arrow-left me-2"></i>Back to Notes
            </a>
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
        
        <div id="message-container"></div>
        
        <div class="row g-4">
            <!-- Label Form -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100 label-card">
                    <div class="card-header border-bottom-0 bg-white">
                        <h5 class="card-title mb-0" id="form-title">Create New Label</h5>
                    </div>
                    <div class="card-body">
                        <form id="label-form" method="POST" action="<?= BASE_URL ?>/labels/process">
                            <div class="mb-4 position-relative">
                                <label for="label-name" class="form-label">Label Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <input type="text" id="label-name" name="name" class="form-control" required>
                                </div>
                                <div class="invalid-feedback">Please provide a label name.</div>
                            </div>
                            
                            <input type="hidden" name="action" value="create" id="form-action">
                            <input type="hidden" name="id" value="" id="label-id">
                            
                            <div class="d-grid gap-2">
                                <button type="submit" id="submit-label" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> <span id="btn-text">Create Label</span>
                                </button>
                                <button type="button" id="cancel-label" class="btn btn-outline-secondary" style="display:none;">
                                    <i class="fas fa-times me-2"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex align-items-center">
                            <div class="tips-icon me-3">
                                <i class="fas fa-lightbulb text-warning"></i>
                            </div>
                            <div class="tips-content">
                                <h6 class="mb-2 text-muted">Tips</h6>
                                <ul class="small text-muted mb-0 ps-3">
                                    <li>Use labels to organize your notes</li>
                                    <li>Add multiple labels to a single note</li>
                                    <li>Filter notes by label from the sidebar</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Labels List -->
            <div class="col-md-8">
                <div class="card shadow-sm label-card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Your Labels</h5>
                        <span class="badge bg-primary rounded-pill"><?= count($data['labels']) ?> labels</span>
                    </div>
                    
                    <?php if (empty($data['labels'])): ?>
                        <div class="card-body text-center py-5">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <h3 class="mt-4">No labels yet</h3>
                                <p class="text-muted">Create your first label to organize your notes.</p>
                                <button id="create-first-label" class="btn btn-primary rounded-pill mt-3">
                                    <i class="fas fa-plus me-2"></i> Create Your First Label
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div id="labels-list" class="label-list">
                            <?php foreach ($data['labels'] as $label): ?>
                                <div class="label-item" data-label-id="<?= $label['id'] ?>">
                                    <div class="label-info">
                                        <div class="label-badge">
                                            <i class="fas fa-tag"></i>
                                            <span class="label-name"><?= htmlspecialchars($label['name']) ?></span>
                                        </div>
                                        <span class="badge rounded-pill note-count">
                                            <?= $label['note_count'] ?> note<?= $label['note_count'] !== 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                    <div class="label-actions">
                                        <button class="btn btn-sm btn-action btn-edit" data-id="<?= $label['id'] ?>" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-action btn-delete" data-id="<?= $label['id'] ?>" data-name="<?= htmlspecialchars($label['name']) ?>" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="modal-icon bg-danger-subtle text-danger mb-3">
                        <i class="fas fa-trash"></i>
                    </div>
                    <p>Are you sure you want to delete the label "<span id="label-to-delete" class="fw-bold"></span>"?</p>
                    <p class="text-muted small">This will not delete notes with this label.</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete">
                    <i class="fas fa-trash me-2"></i> Delete Label
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Enhanced Label Management Styles */
    :root {
        --primary-color: #4a89dc;
        --primary-hover: #3a77c5;
        --secondary-color: #6c757d;
        --danger-color: #dc3545;
        --light-bg: #f8f9fa;
        --border-radius: 12px;
        --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
    }
    
    /* Card styling */
    .label-card {
        border: none;
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--box-shadow);
        transition: var(--transition);
    }
    
    .label-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }
    
    /* Form styling */
    .form-label {
        font-weight: 500;
        color: #495057;
    }
    
    .form-control {
        border-radius: 10px;
        padding: 0.75rem 1rem;
        border: 1px solid rgba(0, 0, 0, 0.1);
        transition: var(--transition);
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(74, 137, 220, 0.2);
    }
    
    .input-group-text {
        border-radius: 10px 0 0 10px;
        background-color: #f8f9fa;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-right: none;
    }
    
    .input-group .form-control {
        border-radius: 0 10px 10px 0;
    }
    
    /* Button styling */
    .btn {
        border-radius: 10px;
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
    
    .btn-outline-primary {
        color: var(--primary-color);
        border: 1px solid var(--primary-color);
        background: transparent;
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(74, 137, 220, 0.2);
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
    
    .btn-action {
        width: 36px;
        height: 36px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: var(--transition);
    }
    
    .btn-edit {
        color: var(--primary-color);
        background-color: rgba(74, 137, 220, 0.1);
        border: none;
    }
    
    .btn-edit:hover {
        background-color: var(--primary-color);
        color: white;
        transform: translateY(-3px);
    }
    
    .btn-delete {
        color: var(--danger-color);
        background-color: rgba(220, 53, 69, 0.1);
        border: none;
    }
    
    .btn-delete:hover {
        background-color: var(--danger-color);
        color: white;
        transform: translateY(-3px);
    }
    
    /* Label list styling */
    .label-list {
        max-height: 500px;
        overflow-y: auto;
    }
    
    .label-list::-webkit-scrollbar {
        width: 6px;
    }
    
    .label-list::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .label-list::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }
    
    .label-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        transition: var(--transition);
    }
    
    .label-item:hover {
        background-color: rgba(0, 0, 0, 0.02);
        transform: translateX(5px);
        border-left: 3px solid var(--primary-color);
    }
    
    .label-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .label-badge {
        display: flex;
        align-items: center;
        background-color: #f0f5ff;
        color: #4a89dc;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
        transition: var(--transition);
    }
    
    .label-badge i {
        margin-right: 8px;
    }
    
    .label-item:hover .label-badge {
        transform: scale(1.05);
        background-color: #e0ecff;
    }
    
    .note-count {
        background-color: rgba(108, 117, 125, 0.1);
        color: #6c757d;
        font-weight: normal;
    }
    
    .label-actions {
        display: flex;
        gap: 8px;
    }
    
    /* Tips section styling */
    .tips-icon {
        width: 40px;
        height: 40px;
        background-color: rgba(255, 193, 7, 0.15);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    /* Empty state styling */
    .empty-state {
        padding: 2rem 1rem;
    }
    
    .empty-icon {
        width: 80px;
        height: 80px;
        background-color: #f0f5ff;
        color: #4a89dc;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto;
        opacity: 0.7;
    }
    
    /* Modal styling */
    .modal-content {
        border-radius: 15px;
    }
    
    .modal-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin: 0 auto;
    }
    
    /* Responsive adjustments */
    @media (max-width: 767.98px) {
        .label-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .label-info {
            margin-bottom: 1rem;
            width: 100%;
            justify-content: space-between;
        }
        
        .label-actions {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const labelForm = document.getElementById('label-form');
    const labelsList = document.getElementById('labels-list');
    const labelNameInput = document.getElementById('label-name');
    const formAction = document.getElementById('form-action');
    const labelId = document.getElementById('label-id');
    const submitButton = document.getElementById('submit-label');
    const btnText = document.getElementById('btn-text');
    const cancelButton = document.getElementById('cancel-button');
    const formTitle = document.getElementById('form-title');
    const messageContainer = document.getElementById('message-container');
    const confirmModal = document.getElementById('confirmModal') ? new bootstrap.Modal(document.getElementById('confirmModal')) : null;
    const confirmDeleteBtn = document.getElementById('confirm-delete');
    const labelToDelete = document.getElementById('label-to-delete');
    const createFirstLabel = document.getElementById('create-first-label');
    
    let currentLabelToDelete = null;
    
    // Handle "Create Your First Label" button
    if (createFirstLabel) {
        createFirstLabel.addEventListener('click', function() {
            labelNameInput.focus();
        });
    }
    
    // IMPORTANT: Handle form submission regardless of whether labels list exists
    if (labelForm) {
        // Handle form submission
        labelForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const labelName = labelNameInput.value.trim();
            
            if (!labelName) {
                labelNameInput.classList.add('is-invalid');
                return;
            }
            
            labelNameInput.classList.remove('is-invalid');
            
            // Disable submit button to prevent double submission
            submitButton.disabled = true;
            btnText.innerHTML = formAction.value === 'create' ? 'Creating...' : 'Updating...';
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ' + btnText.innerHTML;
            
            // Prepare form data
            const formData = new FormData(labelForm);
            
            // Send AJAX request
            fetch(BASE_URL + '/labels/process', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.innerHTML = formAction.value === 'create' ? '<i class="fas fa-save me-2"></i> Create Label' : '<i class="fas fa-save me-2"></i> Update Label';
                btnText.innerHTML = formAction.value === 'create' ? 'Create Label' : 'Update Label';
                
                if (data.success) {
                    if (formAction.value === 'update') {
                        // Update existing label in the list
                        const labelElement = document.querySelector(`[data-label-id="${labelId.value}"]`);
                        if (labelElement) {
                            labelElement.querySelector('.label-name').textContent = labelName;
                        }
                        
                        // Reset form
                        resetForm();
                        
                        // Show success message
                        showMessage('Label updated successfully', 'success');
                    } else {
                        // Add new label to the list or refresh page
                        window.location.reload();
                    }
                } else {
                    // Show error message
                    showMessage(data.message || 'Error saving label', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred. Please try again.', 'error');
                
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.innerHTML = formAction.value === 'create' ? '<i class="fas fa-save me-2"></i> Create Label' : '<i class="fas fa-save me-2"></i> Update Label';
                btnText.innerHTML = formAction.value === 'create' ? 'Create Label' : 'Update Label';
            });
        });
    }
    
    // Only attach these event handlers if the labels list exists
    if (labelsList) {
        // Handle edit buttons
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const labelItem = document.querySelector(`[data-label-id="${id}"]`);
                
                if (labelItem) {
                    const name = labelItem.querySelector('.label-name').textContent;
                    
                    // Animate the transition to edit mode
                    labelForm.classList.add('edit-mode');
                    
                    // Set form to edit mode
                    labelNameInput.value = name;
                    formAction.value = 'update';
                    labelId.value = id;
                    formTitle.textContent = 'Edit Label';
                    btnText.innerHTML = 'Update Label';
                    submitButton.innerHTML = '<i class="fas fa-save me-2"></i> Update Label';
                    if (cancelButton) {
                        cancelButton.style.display = 'block';
                    }
                    
                    // Scroll to form on mobile
                    if (window.innerWidth < 768) {
                        labelForm.scrollIntoView({ behavior: 'smooth' });
                    }
                    
                    // Focus the input
                    labelNameInput.focus();
                }
            });
        });
        
        // Handle delete buttons
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                
                // Set the label name in the modal
                if (labelToDelete) {
                    labelToDelete.textContent = name;
                }
                currentLabelToDelete = id;
                
                // Show the confirmation modal
                if (confirmModal) {
                    confirmModal.show();
                } else {
                    // Fallback if modal not available
                    if (confirm(`Are you sure you want to delete the label "${name}"?`)) {
                        deleteLabel(id);
                    }
                }
            });
        });
    }
    
    // Handle confirm delete
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (currentLabelToDelete) {
                deleteLabel(currentLabelToDelete);
            }
        });
    }
    
    // Cancel button
    if (cancelButton) {
        cancelButton.addEventListener('click', function(e) {
            e.preventDefault();
            resetForm();
            
            // Animate back to create mode
            if (labelForm) {
                labelForm.classList.remove('edit-mode');
            }
        });
    }
    
    // Input validation
    if (labelNameInput) {
        labelNameInput.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
    }
    
    // Function to delete a label
    function deleteLabel(labelId) {
        // Disable the button if it exists
        if (confirmDeleteBtn) {
            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Deleting...';
        }
        
        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', labelId);
        
        // Send AJAX request
        fetch(BASE_URL + '/labels/process', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Hide the modal if it exists
            if (confirmModal) {
                confirmModal.hide();
            }
            
            // Reset button if it exists
            if (confirmDeleteBtn) {
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.innerHTML = '<i class="fas fa-trash me-2"></i> Delete Label';
            }
            
            if (data.success) {
                // Remove the label from the list or refresh
                window.location.reload();
            } else {
                // Show error message
                showMessage(data.message || 'Error deleting label', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', 'error');
            
            // Hide the modal if it exists
            if (confirmModal) {
                confirmModal.hide();
            }
            
            // Reset button if it exists
            if (confirmDeleteBtn) {
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.innerHTML = '<i class="fas fa-trash me-2"></i> Delete Label';
            }
        });
    }
    
    // Reset form to create mode
    function resetForm() {
        if (!labelNameInput || !formAction || !labelId || !formTitle || !btnText || !submitButton) return;
        
        labelNameInput.value = '';
        formAction.value = 'create';
        labelId.value = '';
        formTitle.textContent = 'Create New Label';
        btnText.innerHTML = 'Create Label';
        submitButton.innerHTML = '<i class="fas fa-save me-2"></i> Create Label';
        if (cancelButton) {
            cancelButton.style.display = 'none';
        }
        labelNameInput.classList.remove('is-invalid');
    }
    
    // Show message
    function showMessage(message, type) {
        if (!messageContainer) return;
        
        messageContainer.innerHTML = `
            <div class="alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            const alert = messageContainer.querySelector('.alert');
            if (alert) {
                try {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                } catch (e) {
                    alert.remove();
                }
            }
        }, 5000);
    }
    
    // Add animation class
    document.querySelectorAll('.label-card').forEach(card => {
        // Add entry animation
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });
});
</script>