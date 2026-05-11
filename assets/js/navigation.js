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
        menuToggle.addEventListener('click', function(event) {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';

            this.setAttribute('aria-expanded', !isExpanded);
            this.setAttribute('aria-label', isExpanded ?
                'Open menu' :
                'Close menu'
            );

            navWrapper.classList.toggle('is-open');

            // Close mini-cart if open
            closeMiniCart();

            // Focus first menu item when opening — KEYBOARD ACTIVATION ONLY.
            //
            // WCAG SC 2.4.3 (Focus Order) is well-served by moving focus
            // into the menu when a keyboard user opens it: their next Tab
            // should land inside the menu, not jump past it. But for
            // MOUSE / TOUCH activations, programmatic focus is unwanted:
            //
            //   • Modern :focus-visible suppresses the visible focus
            //     ring on mouse-initiated programmatic focus, so it
            //     usually isn't visible — but browsers don't always
            //     guess right, especially after a setTimeout breaks the
            //     direct click → focus chain.
            //   • Screen readers announce the focus shift unexpectedly.
            //   • Voice control and "next Tab" mental models break: the
            //     user's tap was on the toggle, but focus quietly moved
            //     to a link they didn't ask for.
            //
            // event.detail is the standard way to distinguish activation
            // source on a click event:
            //   • 0  → keyboard (Enter / Space on the <button>)
            //   • ≥1 → mouse / touch (count of consecutive clicks)
            //
            // Use { preventScroll: true } so the focus shift never
            // scrolls the page — the menu is already in view; we don't
            // want to bump the viewport.
            const wasKeyboardActivation = event.detail === 0;
            if (!isExpanded && navWrapper && wasKeyboardActivation) {
                const firstLink = navWrapper.querySelector('a');
                if (firstLink) {
                    setTimeout(function() {
                        firstLink.focus({ preventScroll: true });
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

            // Focus trap for mobile menu
            if (event.key === 'Tab' && navWrapper.classList.contains('is-open')) {
                trapFocusInMobileMenu(event, navWrapper, menuToggle);
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

        // Initialize scroll-aware gradient for the mobile inline top bar
        initTopbarScrollGradient();
    }

    /**
     * Initialize submenu accessibility with split button pattern for mobile
     *
     * On mobile: Injects a separate toggle button so parent links remain accessible
     * On desktop: Toggle button hidden via CSS, hover behavior works as expected
     *
     * Supports nested submenus up to 3 levels
     */
    function initSubmenuAccessibility() {
        // Select ALL items with children, not just top-level
        const menuItemsWithChildren = document.querySelectorAll('.promptless-header__nav-list .menu-item-has-children');

        menuItemsWithChildren.forEach(function(item) {
            const link = item.querySelector(':scope > a');  // Direct child link only
            const submenu = item.querySelector(':scope > .sub-menu');  // Direct child submenu only

            if (!submenu || !link) {
                return;
            }

            // Add aria-haspopup to link for screen readers
            link.setAttribute('aria-haspopup', 'true');

            // Create toggle button for mobile (injected after the link)
            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'submenu-toggle';
            toggleBtn.setAttribute('type', 'button');
            toggleBtn.setAttribute('aria-expanded', 'false');
            toggleBtn.setAttribute('aria-label', 'Toggle ' + link.textContent.trim() + ' submenu');
            toggleBtn.innerHTML = '<span class="submenu-toggle__icon" aria-hidden="true"></span>';

            // Insert button after link, before submenu
            link.parentNode.insertBefore(toggleBtn, submenu);

            // Toggle button click handler (works on mobile, hidden on desktop via CSS)
            toggleBtn.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();

                const isExpanded = this.getAttribute('aria-expanded') === 'true';

                // Close sibling submenus at same level
                const siblings = item.parentElement.querySelectorAll(':scope > .menu-item-has-children');
                siblings.forEach(function(sibling) {
                    if (sibling !== item) {
                        const siblingToggle = sibling.querySelector(':scope > .submenu-toggle');
                        const siblingSubmenu = sibling.querySelector(':scope > .sub-menu');
                        if (siblingToggle && siblingSubmenu) {
                            siblingToggle.setAttribute('aria-expanded', 'false');
                            siblingSubmenu.style.display = '';
                            sibling.classList.remove('is-expanded');
                        }
                    }
                });

                // Toggle current submenu
                this.setAttribute('aria-expanded', !isExpanded);
                submenu.style.display = isExpanded ? '' : 'block';
                item.classList.toggle('is-expanded', !isExpanded);
            });

            // Handle keyboard navigation on toggle button
            toggleBtn.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', !isExpanded);
                    item.classList.toggle('is-expanded', !isExpanded);
                    submenu.style.display = isExpanded ? '' : 'block';

                    if (!isExpanded) {
                        const firstSubmenuLink = submenu.querySelector('a');
                        if (firstSubmenuLink) {
                            firstSubmenuLink.focus();
                        }
                    }
                }
            });

            // Desktop: Update aria-expanded on hover (toggle button hidden via CSS)
            item.addEventListener('mouseenter', function() {
                if (window.innerWidth >= 768) {
                    toggleBtn.setAttribute('aria-expanded', 'true');
                }
            });

            item.addEventListener('mouseleave', function() {
                if (window.innerWidth >= 768) {
                    toggleBtn.setAttribute('aria-expanded', 'false');
                }
            });
        });

        // Initialize nested keyboard navigation
        initNestedKeyboardNav();
    }

    /**
     * Handle keyboard navigation for nested submenus
     */
    function initNestedKeyboardNav() {
        const navList = document.querySelector('.promptless-header__nav-list');
        if (!navList) return;

        navList.addEventListener('keydown', function(event) {
            const target = event.target;
            const parentItem = target.closest('.menu-item-has-children');

            if (!parentItem) return;

            const submenu = parentItem.querySelector(':scope > .sub-menu');
            const link = parentItem.querySelector(':scope > a');

            // ArrowRight: Open submenu and focus first item (desktop only)
            if (event.key === 'ArrowRight' && submenu && window.innerWidth >= 768) {
                event.preventDefault();
                link.setAttribute('aria-expanded', 'true');
                const firstSubLink = submenu.querySelector('a');
                if (firstSubLink) {
                    firstSubLink.focus();
                }
            }

            // ArrowLeft: Close submenu and focus parent (desktop only)
            if (event.key === 'ArrowLeft' && window.innerWidth >= 768) {
                const parentSubmenu = target.closest('.sub-menu');
                if (parentSubmenu) {
                    event.preventDefault();
                    const parentMenuItem = parentSubmenu.closest('.menu-item-has-children');
                    if (parentMenuItem) {
                        const parentLink = parentMenuItem.querySelector(':scope > a');
                        if (parentLink) {
                            parentLink.setAttribute('aria-expanded', 'false');
                            parentLink.focus();
                        }
                    }
                }
            }

            // Escape: Close current submenu level
            if (event.key === 'Escape') {
                const currentSubmenu = target.closest('.sub-menu');
                if (currentSubmenu) {
                    const parentMenuItem = currentSubmenu.closest('.menu-item-has-children');
                    if (parentMenuItem) {
                        const parentLink = parentMenuItem.querySelector(':scope > a');
                        if (parentLink) {
                            parentLink.setAttribute('aria-expanded', 'false');
                            parentMenuItem.classList.remove('is-expanded');
                            parentLink.focus();
                        }
                    }
                }
            }
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

    /**
     * Initialize scroll-aware gradient for the mobile inline top bar
     *
     * When the user picks "Always Show at Top" in the Customizer, the
     * top bar renders as a single horizontal row on mobile (see
     * .promptless-topbar--mobile-inline rules in assets/css/header.css).
     * If utility items overflow the viewport width, the row becomes
     * horizontally scrollable.
     *
     * The CSS provides a mask-image fade at each edge of the row, but
     * the fade widths are driven by CSS custom properties that default
     * to 0px (no fade). This function toggles two state classes based
     * on actual scroll position so the fade only appears for edges
     * with scrollable content beyond them:
     *
     *   - .is-scrolled-from-start  → scrollLeft > 0  (left fade kicks in)
     *   - .has-overflow-right      → more content scrolled-off-right
     *                                (right fade kicks in)
     *
     * Without this, the always-on mask would fade the very first item
     * even at scrollLeft=0, making it look cut off on initial load.
     *
     * The function exits cleanly when:
     *   - The Customizer setting is 'collapse' (no .--mobile-inline class)
     *   - The user is on desktop (the CSS mask is inside @media <=767px,
     *     so even if we toggle classes here, no fade appears on desktop)
     *   - The scroll container has no overflow (both checks fail, no
     *     classes get added, no fade appears)
     *
     * Pattern mirrors the plugin's FAQ tabs scroll-gradient handler
     * (src/frontend.js) so the two implementations stay conceptually
     * aligned even though the plugin and theme can't share JS directly.
     */
    function initTopbarScrollGradient() {
        const scrollContainer = document.querySelector(
            '.promptless-topbar--mobile-inline .promptless-topbar__inner'
        );

        if (!scrollContainer) {
            return;
        }

        // Subpixel scroll values can sit at e.g. 0.4 even when "at start"
        // on some platforms. A 2px threshold avoids flickering between
        // states near the boundaries.
        const threshold = 2;
        let ticking = false;

        function updateScrollState() {
            const scrollLeft = scrollContainer.scrollLeft;
            const scrollWidth = scrollContainer.scrollWidth;
            const clientWidth = scrollContainer.clientWidth;

            scrollContainer.classList.toggle(
                'is-scrolled-from-start',
                scrollLeft > threshold
            );

            scrollContainer.classList.toggle(
                'has-overflow-right',
                scrollWidth - clientWidth - scrollLeft > threshold
            );

            ticking = false;
        }

        function onScroll() {
            if (!ticking) {
                requestAnimationFrame(updateScrollState);
                ticking = true;
            }
        }

        // Initial state — sets has-overflow-right on load if items
        // overflow the viewport. Left fade stays off (scrollLeft is 0).
        updateScrollState();

        // Listen for horizontal scroll. Passive listener is correct
        // here — we never preventDefault on scroll.
        scrollContainer.addEventListener('scroll', onScroll, { passive: true });

        // Layout changes (orientation change, font load, item add/remove)
        // can change whether there's overflow. ResizeObserver handles
        // the container itself; the window resize handles viewport
        // changes that affect the parent's computed width.
        if (typeof ResizeObserver !== 'undefined') {
            const resizeObserver = new ResizeObserver(onScroll);
            resizeObserver.observe(scrollContainer);
        }
        window.addEventListener('resize', onScroll, { passive: true });

        // Late re-measurement after fonts load — font swaps can change
        // item widths and therefore overflow state.
        window.addEventListener('load', updateScrollState);
    }

    /**
     * Get all focusable elements within a container
     *
     * @param {Element} container - The container element to search within
     * @return {NodeList} List of focusable elements
     */
    function getFocusableElements(container) {
        return container.querySelectorAll(
            'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
    }

    /**
     * Trap focus within mobile menu when open
     *
     * WCAG 2.1 SC 2.1.2 (No Keyboard Trap): Focus cycles within the modal
     * WAI-ARIA Authoring Practices: Modal dialog focus management
     *
     * Focus order: menuToggle → nav items → back to menuToggle
     * This keeps focus trapped within the mobile menu modal.
     *
     * @param {KeyboardEvent} event - The keydown event
     * @param {Element} navWrapper - The navigation wrapper element
     * @param {Element} menuToggle - The menu toggle button
     */
    function trapFocusInMobileMenu(event, navWrapper, menuToggle) {
        var navFocusable = getFocusableElements(navWrapper);

        // Build complete focus cycle: menuToggle + all nav items
        // menuToggle is part of the modal UI even though it's outside navWrapper
        var allFocusable = [menuToggle].concat(Array.prototype.slice.call(navFocusable));

        if (allFocusable.length <= 1) {
            // Only menuToggle exists, prevent tabbing away
            event.preventDefault();
            return;
        }

        var firstFocusable = allFocusable[0]; // menuToggle
        var lastFocusable = allFocusable[allFocusable.length - 1];

        // Check if current focus is within the menu area
        var isInMenu = navWrapper.contains(document.activeElement) ||
                       menuToggle === document.activeElement;

        // If focus escaped the menu somehow, bring it back
        if (!isInMenu) {
            event.preventDefault();
            menuToggle.focus();
            return;
        }

        // Second focusable is the first nav item (after menuToggle)
        var secondFocusable = allFocusable.length > 1 ? allFocusable[1] : null;

        if (event.shiftKey) {
            // Shift + Tab: going backward
            if (document.activeElement === firstFocusable) {
                // From menuToggle, wrap to last nav item
                event.preventDefault();
                lastFocusable.focus();
            } else if (secondFocusable && document.activeElement === secondFocusable) {
                // From first nav item, go to menuToggle
                event.preventDefault();
                menuToggle.focus();
            }
        } else {
            // Tab: going forward
            if (document.activeElement === lastFocusable) {
                // From last nav item, wrap to menuToggle
                event.preventDefault();
                firstFocusable.focus();
            } else if (secondFocusable && document.activeElement === firstFocusable) {
                // From menuToggle, go to first nav item (topbar or primary nav)
                event.preventDefault();
                secondFocusable.focus();
            }
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNavigation);
    } else {
        initNavigation();
    }
})();
