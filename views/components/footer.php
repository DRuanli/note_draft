</div><!-- /.container -->
    </main>
    
    <footer class="py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="footer-content bg-white rounded-3 shadow-sm p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                            <div class="mb-3 mb-md-0">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-sticky-note text-primary me-2"></i>
                                    <span class="h5 mb-0"><?= APP_NAME ?></span>
                                </div>
                                <p class="text-muted mb-0 small">&copy; <?= date('Y') ?> All rights reserved.</p>
                            </div>
                            
                            <div class="d-flex gap-3 social-links">
                                <a href="#" class="social-link" title="GitHub">
                                    <i class="fab fa-github"></i>
                                </a>
                                <a href="#" class="social-link" title="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="social-link" title="LinkedIn">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Loading Indicator -->
    <div class="loading-indicator" id="globalLoadingIndicator">
        <div class="spinner"></div>
    </div>
    
    <!-- PWA installation prompt -->
    <div class="pwa-install-prompt" id="pwaInstallPrompt">
        <div class="prompt-content">
            <div class="prompt-icon">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="prompt-text">
                <strong>Install App</strong>
                <span>Add to your home screen</span>
            </div>
            <button id="install-button" class="btn btn-sm btn-primary">Install</button>
            <button id="dismiss-prompt" class="btn-close"></button>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core JavaScript -->
    <script src="<?= ASSETS_URL ?>/js/main.js"></script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($pageScripts) && is_array($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?= ASSETS_URL ?>/js/<?= $script ?>.js"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Footer Enhancement Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Global loading indicator
            const loadingIndicator = document.getElementById('globalLoadingIndicator');
            const showLoading = () => loadingIndicator.classList.add('active');
            const hideLoading = () => loadingIndicator.classList.remove('active');
            
            // Intercept form submissions to show loading indicator
            document.querySelectorAll('form:not([data-no-loading])').forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.classList.contains('btn-link')) {
                        const originalText = submitBtn.innerHTML;
                        const loadingSpinner = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>';
                        submitBtn.innerHTML = loadingSpinner + originalText;
                        submitBtn.disabled = true;
                    }
                    showLoading();
                });
            });
            
            // Animate links for smooth page transitions
            document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([data-bs-toggle])').forEach(link => {
                link.addEventListener('click', function(e) {
                    if (link.hostname === window.location.hostname) {
                        e.preventDefault();
                        showLoading();
                        
                        setTimeout(() => {
                            window.location.href = link.href;
                        }, 200);
                    }
                });
            });
            
            // Hide loading indicator when page is fully loaded
            window.addEventListener('load', hideLoading);
            
            // Social link hover effects
            document.querySelectorAll('.social-link').forEach(link => {
                link.addEventListener('mouseover', function() {
                    this.classList.add('social-link-hover');
                });
                
                link.addEventListener('mouseout', function() {
                    this.classList.remove('social-link-hover');
                });
            });
        });
    </script>
    
    <!-- PWA support -->
    <?php if (defined('ENABLE_OFFLINE_MODE') && ENABLE_OFFLINE_MODE): ?>
    <script>
        // Register service worker for offline capabilities
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?= BASE_URL ?>/service-worker.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    })
                    .catch(function(error) {
                        console.log('ServiceWorker registration failed: ', error);
                    });
            });
        }
        
        // Enhanced PWA install prompt
        let deferredPrompt;
        const installPrompt = document.getElementById('pwaInstallPrompt');
        const installButton = document.getElementById('install-button');
        const dismissPrompt = document.getElementById('dismiss-prompt');
        
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent Chrome 67 and earlier from automatically showing the prompt
            e.preventDefault();
            // Stash the event so it can be triggered later
            deferredPrompt = e;
            // Show the install prompt
            setTimeout(() => {
                installPrompt.classList.add('show');
            }, 2000);
        });

        if (installButton) {
            installButton.addEventListener('click', () => {
                // Hide our UI
                installPrompt.classList.remove('show');
                // Show the install prompt
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    // Wait for the user to respond to the prompt
                    deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('User accepted the install prompt');
                        } else {
                            console.log('User dismissed the install prompt');
                        }
                        deferredPrompt = null;
                    });
                }
            });
        }
        
        if (dismissPrompt) {
            dismissPrompt.addEventListener('click', () => {
                installPrompt.classList.remove('show');
            });
        }
        
        // Handle installed PWA
        window.addEventListener('appinstalled', (evt) => {
            installPrompt.classList.remove('show');
        });
    </script>
    <?php endif; ?>
    
    <style>
        /* Enhanced Footer Styles */
        footer {
            background-color: transparent;
            position: relative;
        }
        
        .footer-content {
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .footer-content:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .social-link {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            border-radius: 50%;
            color: #4a89dc;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }
        
        .social-link:hover {
            background-color: #4a89dc;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(74, 137, 220, 0.3);
        }
        
        .social-link-hover {
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* Loading Indicator */
        .loading-indicator {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: rgba(255, 255, 255, 0.5);
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s;
        }
        
        .loading-indicator.active {
            opacity: 1;
            visibility: visible;
        }
        
        .spinner {
            height: 100%;
            width: 100%;
            background: linear-gradient(to right, #4a89dc, #3a77c5);
            animation: loading 2s ease-in-out infinite;
            transform-origin: 0% 50%;
        }
        
        @keyframes loading {
            0% { transform: scaleX(0); }
            50% { transform: scaleX(1); }
            100% { transform: scaleX(0); transform-origin: 100% 50%; }
        }
        
        /* PWA Install Prompt */
        .pwa-install-prompt {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
            padding: 15px;
            z-index: 9999;
            transform: translateY(100px);
            opacity: 0;
            visibility: hidden;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        .pwa-install-prompt.show {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }
        
        .prompt-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .prompt-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4a89dc, #6ea8fe);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .prompt-text {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }
        
        .prompt-text strong {
            font-size: 1rem;
        }
        
        .prompt-text span {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .btn-close {
            margin-left: 10px;
        }
        
        /* Responsive Styles */
        @media (max-width: 767px) {
            .footer-content {
                text-align: center;
            }
        }
    </style>
</body>
</html>