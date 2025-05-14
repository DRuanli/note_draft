<?php
// This file replaces views/profile/preferences.php
// Get current preferences
$current_theme = $data['preferences']['theme'] ?? 'light';
$current_font_size = $data['preferences']['font_size'] ?? 'medium';
$current_note_color = $data['preferences']['note_color'] ?? 'white';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- Profile Navigation Tabs -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <nav class="profile-nav">
                        <div class="nav nav-tabs nav-fill" id="profile-tabs">
                            <a class="nav-item nav-link" href="<?= BASE_URL ?>/profile">
                                <i class="fas fa-user-circle me-2"></i>Profile
                            </a>
                            <a class="nav-item nav-link" href="<?= BASE_URL ?>/profile/edit">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                            <a class="nav-item nav-link" href="<?= BASE_URL ?>/profile/change-password">
                                <i class="fas fa-key me-2"></i>Security
                            </a>
                            <a class="nav-item nav-link active" href="<?= BASE_URL ?>/profile/preferences">
                                <i class="fas fa-cog me-2"></i>Preferences
                            </a>
                        </div>
                    </nav>
                </div>
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
            
            <div class="card shadow-sm profile-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">User Preferences</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/profile/save-preferences" class="preferences-form">
                        <ul class="nav nav-pills mb-4" id="preferencesTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="appearance-tab" data-bs-toggle="pill" data-bs-target="#appearance" 
                                        type="button" role="tab" aria-controls="appearance" aria-selected="true">
                                    <i class="fas fa-palette me-2"></i>Appearance
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="notes-tab" data-bs-toggle="pill" data-bs-target="#notes" 
                                        type="button" role="tab" aria-controls="notes" aria-selected="false">
                                    <i class="fas fa-sticky-note me-2"></i>Notes Display
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="preferencesTabContent">
                            <!-- Appearance Tab -->
                            <div class="tab-pane fade show active" id="appearance" role="tabpanel" aria-labelledby="appearance-tab">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Theme</label>
                                    <div class="row row-cols-1 row-cols-md-2 g-3">
                                        <!-- Light Theme Option -->
                                        <div class="col">
                                            <div class="theme-option-card card <?= $current_theme === 'light' ? 'selected' : '' ?>">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="theme" id="theme-light" value="light"
                                                                <?= $current_theme === 'light' ? 'checked' : '' ?>>
                                                        </div>
                                                        <label class="theme-card-label ms-2" for="theme-light">
                                                            <i class="fas fa-sun me-2 text-warning"></i>
                                                            <strong>Light Theme</strong>
                                                        </label>
                                                    </div>
                                                    <div class="theme-preview bg-light border p-3 text-center rounded">
                                                        <div class="bg-white border mb-2 p-2">Light Mode</div>
                                                        <small class="text-dark">Default bright theme</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Dark Theme Option -->
                                        <div class="col">
                                            <div class="theme-option-card card <?= $current_theme === 'dark' ? 'selected' : '' ?>">
                                                <div class="card-body p-3">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="theme" id="theme-dark" value="dark"
                                                                <?= $current_theme === 'dark' ? 'checked' : '' ?>>
                                                        </div>
                                                        <label class="theme-card-label ms-2" for="theme-dark">
                                                            <i class="fas fa-moon me-2 text-primary"></i>
                                                            <strong>Dark Theme</strong>
                                                        </label>
                                                    </div>
                                                    <div class="theme-preview bg-dark border p-3 text-center rounded">
                                                        <div class="bg-secondary border mb-2 p-2 text-white">Dark Mode</div>
                                                        <small class="text-white">Easier on the eyes at night</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Font Size</label>
                                    <div class="row row-cols-1 row-cols-md-3 g-3">
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="font_size" id="font-small" value="small"
                                                      <?= $current_font_size === 'small' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="font-small">
                                                    <span style="font-size: 0.875rem;">Small</span>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="font_size" id="font-medium" value="medium"
                                                      <?= $current_font_size === 'medium' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="font-medium">
                                                    <span style="font-size: 1rem;">Medium</span>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="font_size" id="font-large" value="large"
                                                      <?= $current_font_size === 'large' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="font-large">
                                                    <span style="font-size: 1.125rem;">Large</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Notes Settings Tab -->
                            <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Note Color</label>
                                    <div class="d-flex flex-wrap gap-4 mt-3">
                                        <div class="form-check">
                                            <input class="form-check-input visually-hidden" type="radio" name="note_color" id="color-white" value="white"
                                                  <?= $current_note_color === 'white' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="color-white">
                                                <div class="color-swatch bg-white border rounded-circle" style="width: 40px; height: 40px;"></div>
                                                <div class="text-center mt-1">White</div>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input visually-hidden" type="radio" name="note_color" id="color-blue" value="blue"
                                                  <?= $current_note_color === 'blue' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="color-blue">
                                                <div class="color-swatch rounded-circle" style="width: 40px; height: 40px; background-color: #f0f5ff;"></div>
                                                <div class="text-center mt-1">Blue</div>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input visually-hidden" type="radio" name="note_color" id="color-green" value="green"
                                                  <?= $current_note_color === 'green' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="color-green">
                                                <div class="color-swatch rounded-circle" style="width: 40px; height: 40px; background-color: #f0fff5;"></div>
                                                <div class="text-center mt-1">Green</div>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input visually-hidden" type="radio" name="note_color" id="color-yellow" value="yellow"
                                                  <?= $current_note_color === 'yellow' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="color-yellow">
                                                <div class="color-swatch rounded-circle" style="width: 40px; height: 40px; background-color: #fffbeb;"></div>
                                                <div class="text-center mt-1">Yellow</div>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input visually-hidden" type="radio" name="note_color" id="color-purple" value="purple"
                                                  <?= $current_note_color === 'purple' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="color-purple">
                                                <div class="color-swatch rounded-circle" style="width: 40px; height: 40px; background-color: #f8f0ff;"></div>
                                                <div class="text-center mt-1">Purple</div>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input visually-hidden" type="radio" name="note_color" id="color-pink" value="pink"
                                                  <?= $current_note_color === 'pink' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="color-pink">
                                                <div class="color-swatch rounded-circle" style="width: 40px; height: 40px; background-color: #fff0f7;"></div>
                                                <div class="text-center mt-1">Pink</div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card mt-4 bg-light border-0">
                                    <div class="card-body">
                                        <h6><i class="fas fa-lightbulb text-warning me-2"></i>Preview</h6>
                                        <p class="small text-muted mb-3">This is how your notes will appear with the selected color.</p>
                                        
                                        <div class="preview-note rounded shadow-sm p-3" id="note-preview">
                                            <h5 class="mb-2">Sample Note</h5>
                                            <p class="mb-0">This is a preview of how your notes will look with the selected color scheme.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-1"></i> Reset to Defaults
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced CSS for Preferences Page -->
<style>
:root {
    --primary-color: #4a89dc;
    --primary-hover: #3a77c5;
    --secondary-color: #6c757d;
    --light-bg: #f8f9fa;
    --border-radius: 12px;
    --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    --transition: all 0.3s ease;
}

/* Profile Navigation */
.profile-nav {
    background-color: white;
    border-radius: var(--border-radius);
    overflow: hidden;
}

.profile-nav .nav-tabs {
    border: none;
    padding: 0;
}

.profile-nav .nav-link {
    border: none;
    padding: 1.25rem 1rem;
    font-weight: 600;
    color: var(--secondary-color);
    transition: var(--transition);
    position: relative;
}

.profile-nav .nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 3px;
    background-color: var(--primary-color);
    transition: all 0.3s ease;
    transform: translateX(-50%);
    opacity: 0;
}

.profile-nav .nav-link.active::after {
    width: 80%;
    opacity: 1;
}

.profile-nav .nav-link.active {
    color: var(--primary-color);
    background-color: rgba(74, 137, 220, 0.05);
}

.profile-nav .nav-link:hover:not(.active) {
    background-color: rgba(0, 0, 0, 0.02);
    color: #495057;
}

.profile-nav .nav-link i {
    transition: transform 0.3s ease;
}

.profile-nav .nav-link:hover i {
    transform: translateY(-2px);
}

/* Profile Card */
.profile-card {
    border: none;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    background-color: white;
}

.profile-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
}

.profile-card .card-header {
    background-color: white;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 20px;
}

.profile-card .card-body {
    padding: 25px;
}

/* Nav Pills for Settings Tabs */
.nav-pills {
    margin-bottom: 30px;
    gap: 10px;
}

.nav-pills .nav-link {
    border-radius: 50px;
    padding: 10px 20px;
    font-weight: 500;
    color: var(--secondary-color);
    transition: var(--transition);
}

.nav-pills .nav-link.active {
    background-color: var(--primary-color);
    color: white;
    box-shadow: 0 4px 15px rgba(74, 137, 220, 0.3);
}

.nav-pills .nav-link:hover:not(.active) {
    background-color: rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}

/* Theme Previews */
.theme-preview {
    border-radius: 10px;
    transition: var(--transition);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    margin-top: 10px;
}

.theme-preview:hover {
    transform: translateY(-3px);
}

input[type="radio"]:checked + label .theme-preview {
    box-shadow: 0 0 0 2px var(--primary-color), 0 4px 10px rgba(0, 0, 0, 0.1);
}

/* Color Swatches */
.color-swatch {
    cursor: pointer;
    transition: transform 0.3s ease;
    position: relative;
    border: 2px solid rgba(0, 0, 0, 0.1);
}

.color-swatch:hover {
    transform: scale(1.15);
}

input[type="radio"]:checked + label .color-swatch {
    border: 2px solid var(--primary-color);
    box-shadow: 0 0 10px rgba(74, 137, 220, 0.3);
}

input[type="radio"]:checked + label .color-swatch::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: rgba(0, 0, 0, 0.6);
    text-shadow: 0 0 2px white;
}

/* Preview Note */
.preview-note {
    background-color: white;
    transition: var(--transition);
}

.preview-note h5 {
    color: #343a40;
}

.preview-note p {
    color: #495057;
}

/* Buttons */
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

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .profile-nav .nav-link {
        padding: 1rem 0.5rem;
        font-size: 0.9rem;
    }
    
    .profile-nav .nav-link i {
        display: block;
        margin: 0 auto 5px;
        font-size: 1.1rem;
    }
    
    .nav-pills {
        flex-wrap: wrap;
    }
    
    .nav-pills .nav-link {
        width: 100%;
        margin-bottom: 5px;
        text-align: center;
    }
    
    .profile-card .card-body {
        padding: 20px 15px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview note color changes
    const noteColorRadios = document.querySelectorAll('input[name="note_color"]');
    const notePreview = document.getElementById('note-preview');
    
    if (noteColorRadios.length > 0 && notePreview) {
        // Set initial color based on selected value
        setNotePreviewColor();
        
        noteColorRadios.forEach(radio => {
            radio.addEventListener('change', setNotePreviewColor);
        });
    }
    
    function setNotePreviewColor() {
        const selectedColor = document.querySelector('input[name="note_color"]:checked').value;
        
        // Reset preview note style
        notePreview.style.backgroundColor = '';
        
        // Apply selected color
        switch (selectedColor) {
            case 'white':
                notePreview.style.backgroundColor = '#ffffff';
                break;
            case 'blue':
                notePreview.style.backgroundColor = '#f0f5ff';
                break;
            case 'green':
                notePreview.style.backgroundColor = '#f0fff5';
                break;
            case 'yellow':
                notePreview.style.backgroundColor = '#fffbeb';
                break;
            case 'purple':
                notePreview.style.backgroundColor = '#f8f0ff';
                break;
            case 'pink':
                notePreview.style.backgroundColor = '#fff0f7';
                break;
        }
    }
    
    // Theme preview
    const themeRadios = document.querySelectorAll('input[name="theme"]');
    
    themeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            document.documentElement.setAttribute('data-bs-theme', this.value);
        });
    });
    
    // Reset button functionality
    const resetButton = document.querySelector('button[type="reset"]');
    if (resetButton) {
        resetButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Reset to default values
            document.getElementById('theme-light').checked = true;
            document.getElementById('font-medium').checked = true;
            document.getElementById('color-white').checked = true;
            
            // Update previews
            document.documentElement.setAttribute('data-bs-theme', 'light');
            setNotePreviewColor();
        });
    }
});
</script>