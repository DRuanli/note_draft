<div class="delete-confirmation">
    <div class="confirmation-header">
        <h2>Delete Note</h2>
    </div>
    
    <div class="confirmation-content">
        <div class="warning-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <p class="confirmation-message">
            Are you sure you want to delete the note "<strong><?= htmlspecialchars($data['note']['title']) ?></strong>"?
        </p>
        
        <p class="warning-text">
            This action cannot be undone. The note and all its content will be permanently deleted.
        </p>
    </div>
    
    <div class="confirmation-actions">
        <form method="POST">
            <input type="hidden" name="confirm" value="yes">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Delete Note
            </button>
            <a href="<?= BASE_URL ?>/notes" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancel
            </a>
        </form>
    </div>
</div>

<style>
.delete-confirmation {
    max-width: 600px;
    margin: 0 auto;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.confirmation-header {
    padding: 1.5rem;
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.confirmation-header h2 {
    margin: 0;
    color: #dc3545;
    font-size: 1.5rem;
}

.confirmation-content {
    padding: 2rem;
    text-align: center;
}

.warning-icon {
    font-size: 4rem;
    color: #dc3545;
    margin-bottom: 1.5rem;
}

.confirmation-message {
    font-size: 1.2rem;
    margin-bottom: 1rem;
}

.warning-text {
    color: #6c757d;
    margin-bottom: 0;
}

.confirmation-actions {
    padding: 1.5rem;
    background-color: #f8f9fa;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: center;
}

.confirmation-actions form {
    display: flex;
    gap: 1rem;
}

@media (max-width: 768px) {
    .confirmation-actions form {
        flex-direction: column;
    }
}
</style>