/**
 * Navigation JavaScript
 *
 * Handles mobile menu toggle and keyboard navigation.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

(function() {
    'use strict';

    /**
     * Initialize navigation functionality
     */
    function initNavigation() {
        const menuToggle = document.querySelector('.promptless-header__menu-toggle');
        const navWrapper = document.querySelector('.promptless-header__nav-wrapper');
        const primaryNav = document.getElementById('primary-navigation');

        if (!menuToggle || !navWrapper) {
            return;
        }

        // Mobile menu toggle
        menuToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';

            this.setAttribute('aria-expanded', !isExpanded);
            this.setAttribute('aria-label', isExpanded ?
                'Open menu' :
                'Close menu'
            );

            navWrapper.classList.toggle('is-open');

            // Focus first menu item when opening
            if (!isExpanded && primaryNav) {
                const firstLink = primaryNav.querySelector('a');
                if (firstLink) {
                    setTimeout(function() {
                        firstLink.focus();
                    }, 100);
                }
            }
        });

        // Close menu on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && navWrapper.classList.contains('is-open')) {
                menuToggle.setAttribute('aria-expanded', 'false');
                menuToggle.setAttribute('aria-label', 'Open menu');
                navWrapper.classList.remove('is-open');
                menuToggle.focus();
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (navWrapper.classList.contains('is-open')) {
                const isClickInside = navWrapper.contains(event.target) ||
                                     menuToggle.contains(event.target);

                if (!isClickInside) {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    menuToggle.setAttribute('aria-label', 'Open menu');
                    navWrapper.classList.remove('is-open');
                }
            }
        });

        // Handle submenu accessibility
        initSubmenuAccessibility();

        // Close mobile menu on resize to desktop
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth >= 768 && navWrapper.classList.contains('is-open')) {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    menuToggle.setAttribute('aria-label', 'Open menu');
                    navWrapper.classList.remove('is-open');
                }
            }, 100);
        });
    }

    /**
     * Initialize submenu accessibility
     */
    function initSubmenuAccessibility() {
        const menuItems = document.querySelectorAll('.promptless-header__nav-list > li');

        menuItems.forEach(function(item) {
            const link = item.querySelector('a');
            const submenu = item.querySelector('.sub-menu');

            if (!submenu) {
                return;
            }

            // Add aria attributes
            link.setAttribute('aria-haspopup', 'true');
            link.setAttribute('aria-expanded', 'false');

            // Toggle submenu on click (for touch devices)
            link.addEventListener('click', function(event) {
                // Only prevent default and toggle on mobile
                if (window.innerWidth < 768) {
                    event.preventDefault();
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', !isExpanded);
                    submenu.style.display = isExpanded ? 'none' : 'block';
                }
            });

            // Handle keyboard navigation
            link.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', !isExpanded);

                    if (!isExpanded) {
                        const firstSubmenuLink = submenu.querySelector('a');
                        if (firstSubmenuLink) {
                            firstSubmenuLink.focus();
                        }
                    }
                }
            });

            // Update aria-expanded on hover (desktop)
            item.addEventListener('mouseenter', function() {
                if (window.innerWidth >= 768) {
                    link.setAttribute('aria-expanded', 'true');
                }
            });

            item.addEventListener('mouseleave', function() {
                if (window.innerWidth >= 768) {
                    link.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNavigation);
    } else {
        initNavigation();
    }
})();
