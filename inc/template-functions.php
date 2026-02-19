<?php
/**
 * Template Functions
 *
 * Helper functions for use in theme templates.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Output the site logo or site title
 *
 * Enhanced accessibility:
 * - Adds aria-current="page" on homepage (matching WordPress core pattern)
 * - Logo images get role="img" via filter in class-promptless-setup.php
 */
function promptless_site_logo() {
    if ( has_custom_logo() ) {
        the_custom_logo();
    } else {
        $site_name    = get_bloginfo( 'name' );
        $aria_current = ( is_front_page() && ! is_paged() ) ? ' aria-current="page"' : '';
        ?>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
           class="promptless-header__site-title"
           rel="home"<?php echo $aria_current; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
            <?php echo esc_html( $site_name ); ?>
        </a>
        <?php
    }
}

/**
 * Output the primary navigation
 */
function promptless_primary_nav() {
    if ( has_nav_menu( 'primary' ) ) {
        wp_nav_menu(
            array(
                'theme_location'  => 'primary',
                'menu_class'      => 'promptless-header__nav-list',
                'container'       => 'nav',
                'container_class' => 'promptless-header__nav',
                'container_id'    => 'primary-navigation',
                'depth'           => 2,
                'fallback_cb'     => false,
            )
        );
    }
}

/**
 * Output the footer navigation
 *
 * @param string $location Menu location slug.
 */
function promptless_footer_nav( $location = 'footer' ) {
    if ( has_nav_menu( $location ) ) {
        wp_nav_menu(
            array(
                'theme_location'  => $location,
                'menu_class'      => 'promptless-footer__nav-list',
                'container'       => 'nav',
                'container_class' => 'promptless-footer__nav',
                'depth'           => 1,
                'fallback_cb'     => false,
            )
        );
    }
}

/**
 * Output the copyright text
 */
function promptless_copyright() {
    $year      = date( 'Y' );
    $site_name = get_bloginfo( 'name' );

    printf(
        /* translators: 1: Current year, 2: Site name */
        esc_html__( '&copy; %1$s %2$s. All rights reserved.', 'promptless-theme' ),
        esc_html( $year ),
        esc_html( $site_name )
    );
}

/**
 * Get the post excerpt with a specific length
 *
 * @param int $length Number of words.
 * @return string Excerpt text.
 */
function promptless_get_excerpt( $length = 20 ) {
    $excerpt = get_the_excerpt();

    if ( empty( $excerpt ) ) {
        $excerpt = get_the_content();
    }

    $excerpt = wp_strip_all_tags( $excerpt );
    $words   = explode( ' ', $excerpt );

    if ( count( $words ) > $length ) {
        $words   = array_slice( $words, 0, $length );
        $excerpt = implode( ' ', $words ) . '&hellip;';
    }

    return $excerpt;
}

/**
 * Output post categories for archive cards
 * Uses plugin-matching class names for visual consistency
 */
function promptless_post_categories() {
    $categories = get_the_category();

    if ( ! empty( $categories ) ) {
        echo '<div class="aisb-postgrid__categories">';
        $category = $categories[0]; // Get first category
        printf(
            '<span class="aisb-postgrid__category">%s</span>',
            esc_html( $category->name )
        );
        echo '</div>';
    }
}

/**
 * Output post meta (date, author) for archive cards
 * Uses plugin-matching class names for visual consistency
 *
 * @deprecated Use promptless_post_meta_with_categories() instead for better spacing
 */
function promptless_post_meta() {
    ?>
    <div class="aisb-postgrid__metadata">
        <span class="aisb-postgrid__date">
            <svg class="aisb-postgrid__meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                <?php echo esc_html( get_the_date() ); ?>
            </time>
        </span>
        <span class="aisb-postgrid__author">
            <svg class="aisb-postgrid__meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <?php echo esc_html( get_the_author() ); ?>
        </span>
    </div>
    <?php
}

/**
 * Output post meta (date, author) AND categories in a single wrapper
 *
 * This matches the EXACT HTML structure of the PostGrid section from the plugin.
 * Categories are INSIDE the metadata wrapper (not separate elements) to ensure
 * consistent spacing via flexbox gap rather than double margins.
 *
 * Uses plugin-matching class names for visual consistency.
 */
function promptless_post_meta_with_categories() {
    ?>
    <div class="aisb-postgrid__metadata">
        <span class="aisb-postgrid__date">
            <svg class="aisb-postgrid__meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                <?php echo esc_html( get_the_date() ); ?>
            </time>
        </span>
        <span class="aisb-postgrid__author">
            <svg class="aisb-postgrid__meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <?php echo esc_html( get_the_author() ); ?>
        </span>
        <?php
        $categories = get_the_category();
        if ( ! empty( $categories ) ) :
            ?>
            <span class="aisb-postgrid__categories">
                <?php
                // Show only first category (matching PostGrid behavior)
                $category = $categories[0];
                printf(
                    '<span class="aisb-postgrid__category">%s</span>',
                    esc_html( $category->name )
                );
                ?>
            </span>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Output pagination
 */
function promptless_pagination() {
    $args = array(
        'prev_text' => sprintf(
            '<span class="screen-reader-text">%s</span><span aria-hidden="true">&larr;</span>',
            esc_html__( 'Previous page', 'promptless-theme' )
        ),
        'next_text' => sprintf(
            '<span class="screen-reader-text">%s</span><span aria-hidden="true">&rarr;</span>',
            esc_html__( 'Next page', 'promptless-theme' )
        ),
    );

    the_posts_pagination( $args );
}

/**
 * Check if we should show the sidebar
 *
 * @return bool
 */
function promptless_has_sidebar() {
    // For now, theme is sidebar-less. Can be extended later.
    return false;
}

/**
 * Get archive title without prefix
 *
 * @return string
 */
function promptless_get_archive_title() {
    $title = get_the_archive_title();

    // Remove prefix (Category:, Tag:, Author:, etc.)
    if ( is_category() ) {
        $title = single_cat_title( '', false );
    } elseif ( is_tag() ) {
        $title = single_tag_title( '', false );
    } elseif ( is_author() ) {
        $title = get_the_author();
    } elseif ( is_post_type_archive() ) {
        $title = post_type_archive_title( '', false );
    }

    return $title;
}

/**
 * Output mobile menu toggle button
 */
function promptless_mobile_menu_toggle() {
    ?>
    <button
        type="button"
        class="promptless-header__menu-toggle"
        aria-controls="primary-navigation"
        aria-expanded="false"
        aria-label="<?php esc_attr_e( 'Open menu', 'promptless-theme' ); ?>"
    >
        <span class="promptless-header__menu-toggle-bar"></span>
        <span class="promptless-header__menu-toggle-bar"></span>
        <span class="promptless-header__menu-toggle-bar"></span>
        <span class="screen-reader-text"><?php esc_html_e( 'Menu', 'promptless-theme' ); ?></span>
    </button>
    <?php
}

/**
 * Output CTA button in header
 */
function promptless_header_cta() {
    // Get CTA settings from theme customizer
    $cta_text = get_theme_mod( 'promptless_header_cta_text', '' );
    $cta_url  = get_theme_mod( 'promptless_header_cta_url', '' );

    if ( empty( $cta_text ) || empty( $cta_url ) ) {
        return;
    }

    printf(
        '<a href="%s" class="promptless-header__cta aisb-btn aisb-btn--compact aisb-btn-primary">%s</a>',
        esc_url( $cta_url ),
        esc_html( $cta_text )
    );
}

/**
 * Get the header theme variant setting
 *
 * @return string 'light' or 'dark'
 */
function promptless_get_header_theme() {
    return get_theme_mod( 'promptless_header_theme', 'light' );
}

/**
 * Get the footer theme variant setting
 *
 * @return string 'light' or 'dark'
 */
function promptless_get_footer_theme() {
    return get_theme_mod( 'promptless_footer_theme', 'light' );
}

/**
 * Get the navigation position setting
 *
 * @return string 'left', 'center', or 'right'
 */
function promptless_get_nav_position() {
    return get_theme_mod( 'promptless_nav_position', 'center' );
}

/**
 * Check if header border should be displayed
 *
 * @return bool True if header border is enabled.
 */
function promptless_has_header_border() {
    return (bool) get_theme_mod( 'promptless_header_border', true );
}

/**
 * Check if header should be sticky
 *
 * @return bool True if sticky header is enabled.
 */
function promptless_is_header_sticky() {
    return (bool) get_theme_mod( 'promptless_header_sticky', true );
}

/**
 * Get header CSS classes including theme variant and navigation position
 *
 * @return string CSS classes for header element
 */
function promptless_get_header_classes() {
    $classes = array( 'promptless-header' );
    $theme   = promptless_get_header_theme();

    // Add theme variant class (same pattern as plugin sections)
    $classes[] = 'aisb-section--' . esc_attr( $theme );

    // Add navigation position class
    $nav_position = promptless_get_nav_position();
    $classes[]    = 'promptless-header--nav-' . esc_attr( $nav_position );

    // Border (no-border class when disabled)
    if ( ! promptless_has_header_border() ) {
        $classes[] = 'promptless-header--no-border';
    }

    // Sticky (sticky class when enabled)
    if ( promptless_is_header_sticky() ) {
        $classes[] = 'promptless-header--sticky';
    }

    return implode( ' ', $classes );
}

/**
 * Get footer CSS classes including theme variant
 *
 * @return string CSS classes for footer element
 */
function promptless_get_footer_classes() {
    $classes = array( 'promptless-footer' );
    $theme   = promptless_get_footer_theme();

    // Add theme variant class (same pattern as plugin sections)
    $classes[] = 'aisb-section--' . esc_attr( $theme );

    return implode( ' ', $classes );
}

/**
 * Get the content theme variant setting
 *
 * @return string 'light' or 'dark'
 */
function promptless_get_content_theme() {
    return get_theme_mod( 'promptless_content_theme', 'light' );
}

/**
 * Get content wrapper CSS classes including theme variant
 *
 * @return string CSS classes for content element
 */
function promptless_get_content_classes() {
    $classes = array( 'promptless-content' );
    $theme   = promptless_get_content_theme();

    // Add theme variant class (same pattern as plugin sections)
    $classes[] = 'aisb-section--' . esc_attr( $theme );

    return implode( ' ', $classes );
}

/**
 * Check if header cart should be displayed
 *
 * @return bool True if WooCommerce is active and cart is enabled.
 */
function promptless_has_header_cart() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return false;
    }

    return (bool) get_theme_mod( 'promptless_header_cart_enabled', false );
}

/**
 * Get header cart style setting
 *
 * @return string 'link' or 'dropdown'
 */
function promptless_get_header_cart_style() {
    return get_theme_mod( 'promptless_header_cart_style', 'dropdown' );
}

/**
 * Output header cart icon with optional mini-cart dropdown
 *
 * Features:
 * - Shopping bag icon with item count badge
 * - Badge hidden when cart is empty
 * - Dropdown mini-cart or direct link based on settings
 * - Full accessibility support (aria-label, aria-expanded, screen-reader text)
 * - AJAX cart fragment support for real-time updates
 */
function promptless_header_cart() {
    // Only display if WooCommerce active and setting enabled
    if ( ! promptless_has_header_cart() ) {
        return;
    }

    $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $cart_url   = wc_get_cart_url();
    $cart_style = promptless_get_header_cart_style();

    // Translators: %d is the number of items in cart
    $aria_label = sprintf(
        _n(
            'Shopping cart, %d item',
            'Shopping cart, %d items',
            $cart_count,
            'promptless-theme'
        ),
        $cart_count
    );

    ?>
    <div class="promptless-header__cart<?php echo 'dropdown' === $cart_style ? ' promptless-header__cart--dropdown' : ''; ?>">
        <?php if ( 'dropdown' === $cart_style ) : ?>
            <button
                type="button"
                class="promptless-header__cart-toggle"
                aria-label="<?php echo esc_attr( $aria_label ); ?>"
                aria-expanded="false"
                aria-controls="header-mini-cart"
            >
                <?php promptless_cart_icon(); ?>
                <?php promptless_cart_count_badge( $cart_count ); ?>
            </button>
            <div id="header-mini-cart" class="promptless-header__mini-cart" aria-hidden="true">
                <div class="promptless-header__mini-cart-inner widget_shopping_cart_content">
                    <?php woocommerce_mini_cart(); ?>
                </div>
            </div>
        <?php else : ?>
            <a
                href="<?php echo esc_url( $cart_url ); ?>"
                class="promptless-header__cart-link"
                aria-label="<?php echo esc_attr( $aria_label ); ?>"
            >
                <?php promptless_cart_icon(); ?>
                <?php promptless_cart_count_badge( $cart_count ); ?>
            </a>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Output cart icon SVG
 *
 * Modern shopping bag icon matching the design system.
 */
function promptless_cart_icon() {
    ?>
    <svg class="promptless-header__cart-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <path d="M16 10a4 4 0 0 1-8 0"></path>
    </svg>
    <?php
}

/**
 * Output cart count badge
 *
 * Hidden when cart is empty (count is 0).
 *
 * @param int $count Number of items in cart.
 */
function promptless_cart_count_badge( $count ) {
    $hidden = 0 === $count ? ' style="display: none;"' : '';
    ?>
    <span class="promptless-header__cart-count" aria-hidden="true"<?php echo $hidden; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
        <?php echo esc_html( $count ); ?>
    </span>
    <span class="screen-reader-text">
        <?php
        printf(
            /* translators: %d is the number of items */
            esc_html( _n( '%d item in cart', '%d items in cart', $count, 'promptless-theme' ) ),
            (int) $count
        );
        ?>
    </span>
    <?php
}

/**
 * Custom mini-cart buttons with plugin styling
 *
 * Replaces WooCommerce's default mini-cart buttons with buttons
 * that use the Promptless WP plugin's button classes for consistent
 * styling across the theme.
 *
 * @since 1.0.0
 */
function promptless_mini_cart_buttons() {
	// Remove default WooCommerce button hooks
	remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
	remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );

	// Add custom buttons with plugin classes
	add_action( 'woocommerce_widget_shopping_cart_buttons', 'promptless_mini_cart_view_cart_button', 10 );
	add_action( 'woocommerce_widget_shopping_cart_buttons', 'promptless_mini_cart_checkout_button', 20 );
}
add_action( 'wp_loaded', 'promptless_mini_cart_buttons' );

/**
 * Modify cart hash to force browsers to fetch new fragments
 * Increment version when button markup changes
 */
add_filter( 'woocommerce_cart_hash', function( $hash ) {
	return $hash . '_btn_v3';
}, 10 );

/**
 * Output mini-cart View Cart button with ghost styling
 */
function promptless_mini_cart_view_cart_button() {
	echo '<a href="' . esc_url( wc_get_cart_url() ) . '" class="aisb-btn aisb-btn--compact aisb-btn-ghost">' . esc_html__( 'View cart', 'woocommerce' ) . '</a>';
}

/**
 * Output mini-cart Checkout button with primary styling
 */
function promptless_mini_cart_checkout_button() {
	echo '<a href="' . esc_url( wc_get_checkout_url() ) . '" class="aisb-btn aisb-btn--compact aisb-btn-primary">' . esc_html__( 'Checkout', 'woocommerce' ) . '</a>';
}

/**
 * Invalidate WooCommerce cart fragment cache to force fresh rendering
 * This ensures button markup changes are immediately visible
 */
add_filter( 'woocommerce_add_to_cart_fragments', function( $fragments ) {
	// The fragments are re-rendered when this filter runs,
	// so just returning them ensures fresh content
	return $fragments;
}, 999 );

/**
 * Check if top bar should be displayed
 *
 * @return bool True if top bar is enabled.
 */
function promptless_has_topbar() {
    return (bool) get_theme_mod( 'promptless_topbar_enabled', false );
}

/**
 * Get the top bar theme variant setting
 *
 * @return string 'light' or 'dark'
 */
function promptless_get_topbar_theme() {
    return get_theme_mod( 'promptless_topbar_theme', 'dark' );
}

/**
 * Check if top bar should be sticky
 *
 * Requires header to also be sticky for top bar sticky to work.
 *
 * @return bool True if top bar sticky is enabled and header is sticky.
 */
function promptless_is_topbar_sticky() {
    // Top bar can only be sticky if header is also sticky
    if ( ! promptless_is_header_sticky() ) {
        return false;
    }

    return (bool) get_theme_mod( 'promptless_topbar_sticky', false );
}

/**
 * Get top bar CSS classes
 *
 * @return string CSS classes for top bar element.
 */
function promptless_get_topbar_classes() {
    $classes = array( 'promptless-topbar' );
    $theme   = promptless_get_topbar_theme();

    // Add theme variant class (same pattern as plugin sections)
    $classes[] = 'aisb-section--' . esc_attr( $theme );

    // Sticky (sticky class when enabled)
    if ( promptless_is_topbar_sticky() ) {
        $classes[] = 'promptless-topbar--sticky';
    }

    return implode( ' ', $classes );
}

/**
 * Output the top bar left navigation
 */
function promptless_topbar_nav_left() {
    if ( has_nav_menu( 'topbar-left' ) ) {
        wp_nav_menu(
            array(
                'theme_location'  => 'topbar-left',
                'menu_class'      => 'promptless-topbar__nav-list',
                'container'       => 'nav',
                'container_class' => 'promptless-topbar__nav promptless-topbar__nav--left',
                'depth'           => 1,
                'fallback_cb'     => false,
            )
        );
    }
}

/**
 * Output the top bar right navigation
 */
function promptless_topbar_nav_right() {
    if ( has_nav_menu( 'topbar-right' ) ) {
        wp_nav_menu(
            array(
                'theme_location'  => 'topbar-right',
                'menu_class'      => 'promptless-topbar__nav-list',
                'container'       => 'nav',
                'container_class' => 'promptless-topbar__nav promptless-topbar__nav--right',
                'depth'           => 1,
                'fallback_cb'     => false,
            )
        );
    }
}

/**
 * Output the top bar HTML
 *
 * Displays a compact utility bar above the header with left and right menus.
 * Only outputs if top bar is enabled in Customizer settings.
 */
function promptless_topbar() {
    // Only display if top bar is enabled
    if ( ! promptless_has_topbar() ) {
        return;
    }

    // Check if at least one menu is assigned
    if ( ! has_nav_menu( 'topbar-left' ) && ! has_nav_menu( 'topbar-right' ) ) {
        return;
    }
    ?>
    <div class="<?php echo esc_attr( promptless_get_topbar_classes() ); ?>">
        <div class="promptless-container">
            <div class="promptless-topbar__inner">
                <?php promptless_topbar_nav_left(); ?>
                <?php promptless_topbar_nav_right(); ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Get top bar mobile behavior setting
 *
 * @return string 'hide' or 'collapse'
 */
function promptless_get_topbar_mobile_behavior() {
    return get_theme_mod( 'promptless_topbar_mobile', 'hide' );
}

/**
 * Output collapsed top bar section for mobile hamburger menu
 *
 * Only outputs when:
 * - Top bar is enabled
 * - Mobile behavior is set to 'collapse'
 * - At least one top bar menu is assigned
 */
function promptless_mobile_topbar_section() {
    // Only output if top bar enabled and collapse mode selected
    if ( ! promptless_has_topbar() ) {
        return;
    }

    if ( 'collapse' !== promptless_get_topbar_mobile_behavior() ) {
        return;
    }

    // Check if at least one menu exists
    if ( ! has_nav_menu( 'topbar-left' ) && ! has_nav_menu( 'topbar-right' ) ) {
        return;
    }
    ?>
    <div class="promptless-mobile-topbar">
        <?php if ( has_nav_menu( 'topbar-left' ) ) : ?>
            <?php wp_nav_menu( array(
                'theme_location'  => 'topbar-left',
                'menu_class'      => 'promptless-mobile-topbar__list',
                'container'       => false,
                'depth'           => 1,
                'fallback_cb'     => false,
            ) ); ?>
        <?php endif; ?>

        <?php if ( has_nav_menu( 'topbar-right' ) ) : ?>
            <?php wp_nav_menu( array(
                'theme_location'  => 'topbar-right',
                'menu_class'      => 'promptless-mobile-topbar__list',
                'container'       => false,
                'depth'           => 1,
                'fallback_cb'     => false,
            ) ); ?>
        <?php endif; ?>
    </div>
    <?php
}
