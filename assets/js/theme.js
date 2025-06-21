/**
 * Kiwi Theme JavaScript
 * Progressive enhancement for theme functionality
 * 
 * @package Kiwi_Theme
 * @since 1.0.0
 */

(function() {
    'use strict';
    
    /**
     * Color scheme preference detection
     */
    function initColorScheme() {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
        
        function handleSchemeChange(e) {
            document.documentElement.style.colorScheme = e.matches ? 'dark' : 'light';
        }
        
        // Set initial color scheme
        handleSchemeChange(prefersDark);
        
        // Listen for changes
        prefersDark.addEventListener('change', handleSchemeChange);
    }
    
    /**
     * Smooth scrolling for anchor links
     */
    function initSmoothScrolling() {
        document.addEventListener('click', function(e) {
            const target = e.target.closest('a[href^="#"]');
            if (!target) return;
            
            const href = target.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const id = href.slice(1);
            const element = document.getElementById(id);
            
            if (element) {
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Update focus for accessibility
                element.focus();
                
                // Update URL without triggering scroll
                if (history.pushState) {
                    history.pushState(null, null, href);
                }
            }
        });
    }
    
    /**
     * Enhanced form accessibility
     */
    function initFormAccessibility() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                // Add ARIA labels if missing
                if (!input.getAttribute('aria-label') && !input.getAttribute('aria-labelledby')) {
                    const label = form.querySelector(`label[for="${input.id}"]`);
                    if (label) {
                        input.setAttribute('aria-labelledby', label.id || `label-${input.id}`);
                        if (!label.id) {
                            label.id = `label-${input.id}`;
                        }
                    }
                }
                
                // Enhanced validation feedback
                input.addEventListener('invalid', function(e) {
                    const errorId = `${input.id}-error`;
                    let errorElement = document.getElementById(errorId);
                    
                    if (!errorElement) {
                        errorElement = document.createElement('div');
                        errorElement.id = errorId;
                        errorElement.className = 'validation-error';
                        errorElement.setAttribute('aria-live', 'polite');
                        input.parentNode.insertBefore(errorElement, input.nextSibling);
                    }
                    
                    errorElement.textContent = input.validationMessage;
                    input.setAttribute('aria-describedby', errorId);
                });
                
                input.addEventListener('input', function() {
                    const errorElement = document.getElementById(`${input.id}-error`);
                    if (errorElement && input.validity.valid) {
                        errorElement.textContent = '';
                        input.removeAttribute('aria-describedby');
                    }
                });
            });
        });
    }
    
    /**
     * Lazy loading enhancement for older browsers
     */
    function initLazyLoadingFallback() {
        // Only run if browser doesn't support native lazy loading
        if ('loading' in HTMLImageElement.prototype) {
            return;
        }
        
        const images = document.querySelectorAll('img[loading="lazy"]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                        }
                        img.classList.remove('lazy-loading');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            images.forEach(img => {
                img.classList.add('lazy-loading');
                imageObserver.observe(img);
            });
        } else {
            // Fallback for very old browsers
            images.forEach(img => {
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                }
                img.classList.remove('lazy-loading');
            });
        }
    }
    
    /**
     * Enhanced keyboard navigation
     */
    function initKeyboardNavigation() {
        // Global keyboard event handling
        document.addEventListener('keydown', function(e) {
            // Escape key handling for modals/dropdowns
            if (e.key === 'Escape') {
                const activeElement = document.activeElement;
                
                // Close any open dropdowns or modals
                const openDropdowns = document.querySelectorAll('.dropdown.open, .modal.open');
                openDropdowns.forEach(dropdown => {
                    dropdown.classList.remove('open');
                });
                
                // Return focus to trigger element if available
                if (activeElement && activeElement.hasAttribute('data-return-focus')) {
                    const returnElement = document.getElementById(activeElement.getAttribute('data-return-focus'));
                    if (returnElement) returnElement.focus();
                }
            }
            
            // Tab navigation enhancements
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });
        
        // Remove keyboard navigation class on mouse use
        document.addEventListener('mousedown', function() {
            document.body.classList.remove('keyboard-navigation');
        });
    }
    
    /**
     * Initialize theme enhancements
     */
    function init() {
        initColorScheme();
        initSmoothScrolling();
        initFormAccessibility();
        initLazyLoadingFallback();
        initKeyboardNavigation();
        
        // Add loaded class to body for CSS enhancements
        document.body.classList.add('js-loaded');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();