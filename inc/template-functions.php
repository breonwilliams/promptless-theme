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
 */
function promptless_site_logo() {
    if ( has_custom_logo() ) {
        the_custom_logo();
    } else {
        ?>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="promptless-header__site-title" rel="home">
            <?php bloginfo( 'name' ); ?>
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
 * Get header CSS classes including theme variant
 *
 * @return string CSS classes for header element
 */
function promptless_get_header_classes() {
    $classes = array( 'promptless-header' );
    $theme   = promptless_get_header_theme();

    // Add theme variant class (same pattern as plugin sections)
    $classes[] = 'aisb-section--' . esc_attr( $theme );

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
