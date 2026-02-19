/**
 * Navigation JavaScript
 *
 * Handles mobile menu toggle, keyboard navigation, and mini-cart dropdown.
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

            // Close mini-cart if open
            closeMiniCart();

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
            if (event.key === 'Escape') {
                // Close mobile menu if open
                if (navWrapper.classList.contains('is-open')) {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    menuToggle.setAttribute('aria-label', 'Open menu');
                    navWrapper.classList.remove('is-open');
                    menuToggle.focus();
                }
                // Close mini-cart if open
                closeMiniCart();
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

        // Initialize mini-cart
        initMiniCart();

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

        // Initialize sticky header height management
        initStickyHeightManager();
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

    /**
     * Initialize mini-cart dropdown functionality
     */
    function initMiniCart() {
        const cartToggle = document.querySelector('.promptless-header__cart-toggle');
        const miniCart = document.getElementById('header-mini-cart');

        if (!cartToggle || !miniCart) {
            return;
        }

        // Toggle mini-cart on button click
        cartToggle.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();

            const isExpanded = this.getAttribute('aria-expanded') === 'true';

            if (isExpanded) {
                closeMiniCart();
            } else {
                openMiniCart();
            }
        });

        // Close mini-cart when clicking outside
        document.addEventListener('click', function(event) {
            const cartContainer = document.querySelector('.promptless-header__cart');
            if (cartContainer && !cartContainer.contains(event.target)) {
                closeMiniCart();
            }
        });

        // Prevent clicks inside mini-cart from closing it
        miniCart.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }

    /**
     * Open the mini-cart dropdown
     */
    function openMiniCart() {
        const cartToggle = document.querySelector('.promptless-header__cart-toggle');
        const miniCart = document.getElementById('header-mini-cart');

        if (cartToggle && miniCart) {
            cartToggle.setAttribute('aria-expanded', 'true');
            miniCart.setAttribute('aria-hidden', 'false');
        }
    }

    /**
     * Close the mini-cart dropdown
     */
    function closeMiniCart() {
        const cartToggle = document.querySelector('.promptless-header__cart-toggle');
        const miniCart = document.getElementById('header-mini-cart');

        if (cartToggle && miniCart) {
            cartToggle.setAttribute('aria-expanded', 'false');
            miniCart.setAttribute('aria-hidden', 'true');
        }
    }

    /**
     * Initialize sticky header height management
     *
     * Dynamically measures topbar height and sets CSS variable for
     * gap-free sticky header positioning. Uses ResizeObserver for
     * responsive updates.
     */
    function initStickyHeightManager() {
        const topbar = document.querySelector('.promptless-topbar');
        const header = document.querySelector('.promptless-header--sticky');

        // Exit if no sticky elements exist
        if (!topbar && !header) {
            return;
        }

        /**
         * Measure actual topbar height and update CSS variable
         */
        function updateStickyOffsets() {
            const topbarHeight = topbar ? topbar.offsetHeight : 0;

            // Set CSS variable on document root for sticky positioning
            document.documentElement.style.setProperty(
                '--topbar-height',
                topbarHeight + 'px'
            );
        }

        // Initial measurement on DOM ready
        updateStickyOffsets();

        // Watch for topbar size changes using ResizeObserver
        if (topbar && typeof ResizeObserver !== 'undefined') {
            const resizeObserver = new ResizeObserver(function() {
                updateStickyOffsets();
            });
            resizeObserver.observe(topbar);
        }

        // Update after fonts load (can affect height)
        window.addEventListener('load', updateStickyOffsets);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNavigation);
    } else {
        initNavigation();
    }
})();
