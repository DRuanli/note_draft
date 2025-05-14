/**
 * Main JavaScript file
 */
 
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle for password fields
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const passwordField = document.getElementById(this.dataset.target);
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                this.innerHTML = '<i class="fa fa-eye-slash"></i>';
            } else {
                passwordField.type = 'password';
                this.innerHTML = '<i class="fa fa-eye"></i>';
            }
        });
    });
    
    // Auto-dismiss alerts
    const autoDismissAlerts = document.querySelectorAll('.alert[data-auto-dismiss]');
    
    autoDismissAlerts.forEach(function(alert) {
        const dismissTime = parseInt(alert.dataset.autoDismiss) || 5000;
        
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 300);
        }, dismissTime);
    });
});

/**
 * Theme and Preferences Manager
 */
class PreferencesManager {
    constructor() {
        this.init();
    }
    
    init() {
        // Apply theme from localStorage if available
        this.applyTheme();
        
        // Listen for theme changes
        window.addEventListener('storage', (event) => {
            if (event.key === 'user_theme') {
                this.applyTheme();
            }
        });
    }
    
    applyTheme() {
        // Get theme from localStorage or default to light
        const theme = localStorage.getItem('user_theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
        document.body.setAttribute('data-bs-theme', theme);
    }
    
    // Set theme and save to localStorage
    setTheme(theme) {
        localStorage.setItem('user_theme', theme);
        this.applyTheme();
    }
}

// Initialize preferences manager
document.addEventListener('DOMContentLoaded', function() {
    window.preferencesManager = new PreferencesManager();
    
    // If on preferences page, set up form handling
    const preferencesForm = document.querySelector('.preferences-form');
    if (preferencesForm) {
        const themeRadios = document.querySelectorAll('input[name="theme"]');
        
        // Set up real-time theme preview
        themeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                window.preferencesManager.setTheme(this.value);
            });
        });
    }
});