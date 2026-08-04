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
                'depth'           => 3,
                'fallback_cb'     => false,
                'walker'          => new Promptless_Walker_Mega_Menu(),
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
 * Add aria-labelledby to footer navigation containers
 *
 * Uses accessible ARIA labeling instead of heading elements
 * to avoid heading hierarchy issues.
 *
 * @param string $nav_menu The HTML content for the navigation menu.
 * @param object $args     An object of wp_nav_menu() arguments.
 * @return string Modified navigation HTML.
 */
function promptless_footer_nav_aria_label( $nav_menu, $args ) {
    // Only apply to footer column menus
    $footer_locations = array( 'footer-col-1', 'footer-col-2', 'footer-col-3' );

    if ( ! in_array( $args->theme_location, $footer_locations, true ) ) {
        return $nav_menu;
    }

    // Get heading for this location
    $location_num = str_replace( 'footer-col-', '', $args->theme_location );
    $heading      = get_theme_mod( 'promptless_footer_col_' . $location_num . '_heading', '' );

    // If heading exists, add aria-labelledby to the nav container
    if ( $heading ) {
        $heading_id = 'footer-nav-col-' . $location_num . '-heading';
        $nav_menu   = str_replace(
            'class="promptless-footer__nav"',
            'class="promptless-footer__nav" aria-labelledby="' . esc_attr( $heading_id ) . '"',
            $nav_menu
        );
    }

    return $nav_menu;
}
add_filter( 'wp_nav_menu', 'promptless_footer_nav_aria_label', 10, 2 );

/**
 * Output the copyright text
 */
function promptless_copyright() {
    $year      = date( 'Y' );
    $site_name = get_bloginfo( 'name' );

    printf(
        /* translators: 1: Current year, 2: Site name */
        esc_html__( '&copy; %1$s %2$s. All rights reserved.', 'promptless' ),
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
    $post_id = get_the_ID();

    /**
     * Whether to show the date in the archive card meta strip.
     *
     * Defaults to true (backward compatible). Plugins (notably Post
     * Runtime Engine) can return false per-CPT so that registered CPTs
     * which already have their own date field defined don't display
     * the post's create-date alongside the field.
     *
     * @param bool $show    True to render the date.
     * @param int  $post_id The post being rendered.
     */
    $show_date = apply_filters( 'promptless_archive_card_show_date', true, $post_id );

    /**
     * Whether to show the author in the archive card meta strip.
     *
     * Defaults to true. Same pattern as promptless_archive_card_show_date
     * — return false from a plugin filter to hide author on a per-CPT
     * basis.
     *
     * @param bool $show    True to render the author.
     * @param int  $post_id The post being rendered.
     */
    $show_author = apply_filters( 'promptless_archive_card_show_author', true, $post_id );

    $categories     = get_the_category();
    $has_categories = ! empty( $categories );

    // Render the wrapper only if at least one meta item is going to land in it.
    if ( ! $show_date && ! $show_author && ! $has_categories ) {
        return;
    }
    ?>
    <div class="aisb-postgrid__metadata">
        <?php if ( $show_date ) : ?>
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
        <?php endif; ?>
        <?php if ( $show_author ) : ?>
            <span class="aisb-postgrid__author">
                <svg class="aisb-postgrid__meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <?php echo esc_html( get_the_author() ); ?>
            </span>
        <?php endif; ?>
        <?php if ( $has_categories ) : ?>
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
            esc_html__( 'Previous page', 'promptless' )
        ),
        'next_text' => sprintf(
            '<span class="screen-reader-text">%s</span><span aria-hidden="true">&rarr;</span>',
            esc_html__( 'Next page', 'promptless' )
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
        aria-label="<?php esc_attr_e( 'Open menu', 'promptless' ); ?>"
    >
        <span class="promptless-header__menu-toggle-bar"></span>
        <span class="promptless-header__menu-toggle-bar"></span>
        <span class="promptless-header__menu-toggle-bar"></span>
        <span class="screen-reader-text"><?php esc_html_e( 'Menu', 'promptless' ); ?></span>
    </button>
    <?php
}

/**
 * Map a CTA style key to its Promptless WP button class.
 *
 * Styles inherit their colors from the Promptless WP plugin's Global
 * Settings via the shared button classes. Falls back to a sensible default
 * when the plugin is inactive (the theme ships fallback button styles).
 *
 * @param string $style Style key: 'primary', 'secondary', or 'ghost'.
 * @return string Button class.
 */
function promptless_get_cta_style_class( $style ) {
    $map = array(
        'primary'   => 'aisb-btn-primary',
        'secondary' => 'aisb-btn-secondary',
        'ghost'     => 'aisb-btn-ghost',
    );

    return isset( $map[ $style ] ) ? $map[ $style ] : $map['primary'];
}

/**
 * Get the normalized, ordered list of configured header CTA buttons.
 *
 * A button is included only when BOTH its text and URL are set. Each
 * returned entry carries its resolved style and an *effective* mobile
 * placement.
 *
 * No-clutter invariant: at most ONE button may remain in the header bar on
 * mobile (two buttons beside the logo and hamburger is too crowded). The
 * first button whose placement is 'bar' keeps the bar; any subsequent
 * 'bar' button is downgraded to 'menu'. Desktop always shows every button
 * in the bar regardless of placement — placement only governs mobile.
 *
 * The renderer is data-driven (not hardcoded to two buttons), so adding
 * more buttons in future is a Customizer-only change.
 *
 * @return array<int, array{text:string,url:string,style:string,placement:string}>
 */
function promptless_get_header_ctas() {
    $defs = array(
        array(
            'text'      => get_theme_mod( 'promptless_header_cta_text', '' ),
            'url'       => get_theme_mod( 'promptless_header_cta_url', '' ),
            'style'     => get_theme_mod( 'promptless_header_cta_style', 'primary' ),
            'placement' => get_theme_mod( 'promptless_header_cta_mobile_placement', 'bar' ),
        ),
        array(
            'text'      => get_theme_mod( 'promptless_header_cta_2_text', '' ),
            'url'       => get_theme_mod( 'promptless_header_cta_2_url', '' ),
            'style'     => get_theme_mod( 'promptless_header_cta_2_style', 'secondary' ),
            'placement' => get_theme_mod( 'promptless_header_cta_2_mobile_placement', 'menu' ),
        ),
    );

    $valid_styles     = array( 'primary', 'secondary', 'ghost' );
    $valid_placements = array( 'bar', 'menu' );

    $ctas      = array();
    $bar_taken = false;

    foreach ( $defs as $def ) {
        if ( empty( $def['text'] ) || empty( $def['url'] ) ) {
            continue;
        }

        $style     = in_array( $def['style'], $valid_styles, true ) ? $def['style'] : 'primary';
        $placement = in_array( $def['placement'], $valid_placements, true ) ? $def['placement'] : 'bar';

        // Enforce the no-clutter invariant.
        if ( 'bar' === $placement ) {
            if ( $bar_taken ) {
                $placement = 'menu';
            } else {
                $bar_taken = true;
            }
        }

        $ctas[] = array(
            'text'      => $def['text'],
            'url'       => $def['url'],
            'style'     => $style,
            'placement' => $placement,
        );
    }

    /**
     * Filter the resolved header CTA buttons.
     *
     * @param array $ctas Normalized CTA definitions.
     */
    return apply_filters( 'promptless_header_ctas', $ctas );
}

/**
 * Whether the header has any DESKTOP-visible action.
 *
 * The `.promptless-header__actions` slot in the default (inline) layout always
 * renders in the DOM because it holds the mobile hamburger toggle (which is
 * hidden on desktop). On desktop the slot also shows the cart and EVERY
 * configured CTA: the CTA `placement` value is a *mobile* placement
 * (`promptless_header_cta_mobile_placement`) — it only governs whether a CTA
 * stays in the bar or moves into the hamburger drawer on MOBILE. On desktop a
 * 'menu'-placement CTA still renders in the bar (its `--to-menu` class is only
 * `display:none` below the menu breakpoint). So on desktop the slot is visually
 * empty precisely when there is no cart and no CTA at all — regardless of
 * placement.
 *
 * CSS can't detect this — the slot is never `:empty` (it contains the hidden
 * toggle) — so this helper drives the `promptless-header--no-actions` body
 * class, which lets the stylesheet collapse the empty slot and align a
 * right-aligned nav flush with the container edge (header-breakpoint.css). If a
 * future desktop-visible action is added to the actions slot, extend this check.
 *
 * @return bool True when a cart or any CTA renders in the desktop actions bar.
 */
function promptless_has_desktop_header_actions() {
    if ( promptless_has_header_cart() ) {
        return true;
    }

    // Any configured CTA shows in the bar on desktop (placement only affects
    // mobile), so a non-empty CTA list means the desktop slot is not empty.
    return ! empty( promptless_get_header_ctas() );
}

/**
 * Render a single CTA anchor.
 *
 * @param array  $cta         Normalized CTA entry from promptless_get_header_ctas().
 * @param string $extra_class Optional extra class(es) for the anchor.
 * @return string Anchor HTML.
 */
function promptless_render_cta_button( array $cta, $extra_class = '' ) {
    $classes = array(
        'promptless-header__cta',
        'aisb-btn',
        'aisb-btn--compact',
        promptless_get_cta_style_class( $cta['style'] ),
    );

    if ( '' !== $extra_class ) {
        $classes[] = $extra_class;
    }

    return sprintf(
        '<a href="%s" class="%s">%s</a>',
        esc_url( $cta['url'] ),
        esc_attr( implode( ' ', $classes ) ),
        esc_html( $cta['text'] )
    );
}

/**
 * Output the CTA button(s) in the header actions area.
 *
 * Every configured button renders here for desktop. Each anchor gets a
 * mobile-visibility modifier so the breakpoint CSS can hide the ones that
 * belong in the mobile menu instead:
 *   - placement 'bar'  → promptless-header__cta--keep (visible at all widths)
 *   - placement 'menu' → promptless-header__cta--to-menu (hidden below the
 *                        menu breakpoint, shown again in the mobile menu by
 *                        promptless_header_menu_cta()).
 */
function promptless_header_cta() {
    $ctas = promptless_get_header_ctas();

    if ( empty( $ctas ) ) {
        return;
    }

    foreach ( $ctas as $cta ) {
        $modifier = ( 'menu' === $cta['placement'] )
            ? 'promptless-header__cta--to-menu'
            : 'promptless-header__cta--keep';

        // Anchor HTML is fully escaped inside promptless_render_cta_button().
        echo promptless_render_cta_button( $cta, $modifier ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

/**
 * Whether the collapsed mobile top-bar section will render in the drawer.
 *
 * Mirrors the guard at the top of promptless_mobile_topbar_section() so other
 * code (e.g. the menu-CTA position resolver) can ask "is there a top-bar block
 * to sit between?" without duplicating the conditions. The top-bar section
 * appears only when the top bar is enabled, its mobile behavior is 'collapse',
 * and at least one top-bar menu location has an assigned menu.
 *
 * @since 1.4.0
 * @return bool True when the mobile top-bar section renders.
 */
function promptless_has_mobile_topbar_section() {
    if ( ! promptless_has_topbar() ) {
        return false;
    }

    if ( 'collapse' !== promptless_get_topbar_mobile_behavior() ) {
        return false;
    }

    return has_nav_menu( 'topbar-left' ) || has_nav_menu( 'topbar-right' );
}

/**
 * Resolve where the menu-routed CTA group sits inside the mobile drawer.
 *
 * The raw Customizer value is one of 'top', 'middle', or 'bottom'. 'middle'
 * means "between the top-bar items and the main menu items", which only has a
 * meaning when the collapsed top-bar section is actually present. When it is
 * not, 'middle' is promoted to 'top' so the buttons never silently vanish into
 * a slot that is never rendered.
 *
 * @since 1.4.0
 * @return string One of 'top', 'middle', 'bottom'.
 */
function promptless_get_header_menu_cta_position() {
    $position = get_theme_mod( 'promptless_header_menu_cta_position', 'bottom' );

    if ( ! in_array( $position, array( 'top', 'middle', 'bottom' ), true ) ) {
        $position = 'bottom';
    }

    if ( 'middle' === $position && ! promptless_has_mobile_topbar_section() ) {
        $position = 'top';
    }

    return $position;
}

/**
 * Output the CTA button group inside the mobile menu panel.
 *
 * Renders only the buttons whose effective mobile placement is 'menu', laid out
 * side by side. Hidden on desktop via the breakpoint CSS (where these buttons
 * already appear in the header bar).
 *
 * The drawer calls this once for each of the three possible vertical slots
 * ('top', 'middle', 'bottom'); the call is a no-op unless $slot matches the
 * resolved position from promptless_get_header_menu_cta_position(). Rendering in
 * the real DOM slot — rather than repositioning with CSS order — keeps the
 * visual order, screen-reader order, and keyboard tab order in agreement.
 *
 * Safe to call unconditionally from every header layout: outputs nothing when
 * no button is routed to the menu or when the slot does not match.
 *
 * @since 1.4.0 Added $slot parameter and position gating.
 * @param string $slot Drawer slot this call represents: 'top', 'middle', or
 *                     'bottom'. Defaults to 'bottom' (the historical position).
 */
function promptless_header_menu_cta( $slot = 'bottom' ) {
    if ( ! in_array( $slot, array( 'top', 'middle', 'bottom' ), true ) ) {
        $slot = 'bottom';
    }

    // Only the slot matching the configured position renders.
    if ( $slot !== promptless_get_header_menu_cta_position() ) {
        return;
    }

    $ctas = promptless_get_header_ctas();

    if ( empty( $ctas ) ) {
        return;
    }

    $menu_ctas = array_filter(
        $ctas,
        static function ( $cta ) {
            return 'menu' === $cta['placement'];
        }
    );

    if ( empty( $menu_ctas ) ) {
        return;
    }
    ?>
    <div class="promptless-header__menu-cta promptless-header__menu-cta--<?php echo esc_attr( $slot ); ?>">
        <?php
        foreach ( $menu_ctas as $cta ) {
            // Anchor HTML is fully escaped inside promptless_render_cta_button().
            echo promptless_render_cta_button( $cta ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        ?>
    </div>
    <?php
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
 * Get the navigation bar theme variant setting
 *
 * For stacked layouts only. Falls back to header theme if not set.
 *
 * @return string 'light' or 'dark'
 */
function promptless_get_header_nav_theme() {
    $nav_theme = get_theme_mod( 'promptless_header_nav_theme', '' );

    // If empty (inherit) or not stacked layout, use header theme
    if ( empty( $nav_theme ) || 'stacked' !== promptless_get_header_layout() ) {
        return promptless_get_header_theme();
    }

    return $nav_theme;
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
 * Get the header layout setting
 *
 * 'default'  — single row, full-width bar.
 * 'stacked'  — two rows (logo/actions row + detached nav bar).
 * 'floating' — the single-row DOM restyled as a rounded pill detached
 *              from the viewport edges (CSS-only treatment; shares every
 *              default-layout behavior including the mobile drawer).
 *
 * @return string 'default', 'stacked', or 'floating'
 */
function promptless_get_header_layout() {
    return get_theme_mod( 'promptless_header_layout', 'default' );
}

/**
 * Should the floating header overlay the first section on this request?
 *
 * The overlay mode (Customizer: "Float Over First Section") pulls the
 * page content up underneath the floating pill so it hovers over the
 * first section — the modern island-nav pattern. Because the theme can
 * introspect what a page opens with, eligibility is AUTOMATIC policy
 * rather than a per-page-type settings surface (contrast: Astra ships
 * five disable toggles because it cannot know):
 *
 *   1. Setting on + layout is 'floating'. Overlay is meaningless for
 *      default/stacked bars (they need their own band).
 *   2. Singular content whose sections render FULL-WIDTH: `_aisb_sections`
 *      non-empty and `_aisb_display_mode` fullwidth (empty meta defaults
 *      to fullwidth — mirrors the plugin's TemplateHandler). Boxed
 *      contexts (archives, search, 404, blog, Woo, shortcode-mode pages)
 *      open with a title on the page background where an overlay always
 *      reads broken — they keep today's rendering.
 *   3. Breadcrumbs suppress overlay (founder decision 2026-07-24): the
 *      trail lives in the band between header and content that overlay
 *      removes, and breadcrumb pages are interior pages where the
 *      composition matters least. promptless_show_breadcrumbs() is the
 *      single source of truth, so the two features can never disagree.
 *
 * Canvas mode never reaches this code (it skips get_header()). The
 * chrome offset itself is CSS: the header wrapper pulls following
 * content up by --promptless-header-height (measured by navigation.js,
 * with a static fallback) and the first section's padding compensates —
 * see header.css "overlay mode" and the same-named test-matrix section.
 *
 * Memoized per request: called from header classes, body_class, and the
 * asset layer; the answer cannot change mid-request.
 *
 * @since 1.3.0 (overlay)
 * @return bool
 */
function promptless_is_header_overlay_active() {
    static $active = null;
    if ( null !== $active ) {
        return $active;
    }

    if ( 'floating' !== promptless_get_header_layout() ) {
        return $active = false;
    }
    if ( ! get_theme_mod( 'promptless_header_overlay', false ) ) {
        return $active = false;
    }
    if ( ! is_singular() ) {
        return $active = false;
    }

    $post_id = get_the_ID();
    if ( ! $post_id ) {
        return $active = false;
    }

    // Full-width sections page? (Mirrors the plugin's TemplateHandler
    // exactly: builder enabled + sections present + display mode
    // fullwidth, empty mode meta = fullwidth. The _aisb_enabled check
    // matters: leftover sections meta WITHOUT the enabled flag renders
    // the normal boxed template — title-first, where overlay must not
    // engage.)
    if ( empty( get_post_meta( $post_id, '_aisb_enabled', true ) ) ) {
        return $active = false;
    }
    $sections = get_post_meta( $post_id, '_aisb_sections', true );
    if ( empty( $sections ) ) {
        return $active = false;
    }
    $display_mode = get_post_meta( $post_id, '_aisb_display_mode', true );
    if ( ! empty( $display_mode ) && 'fullwidth' !== $display_mode ) {
        return $active = false;
    }

    // Breadcrumb trail needs the band the overlay removes.
    if ( function_exists( 'promptless_show_breadcrumbs' ) && promptless_show_breadcrumbs() ) {
        return $active = false;
    }

    return $active = true;
}

/**
 * Body class for overlay-active requests. The first-section padding
 * compensation keys off the body (the header class can't reach the
 * section in CSS).
 *
 * @param array $classes Body classes.
 * @return array
 */
function promptless_header_overlay_body_class( $classes ) {
    if ( promptless_is_header_overlay_active() ) {
        $classes[] = 'promptless-has-header-overlay';
    }
    return $classes;
}
add_filter( 'body_class', 'promptless_header_overlay_body_class' );

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
    $layout  = promptless_get_header_layout();

    // Add theme variant class (same pattern as plugin sections)
    $classes[] = 'aisb-section--' . esc_attr( $theme );

    // Add layout class
    $classes[] = 'promptless-header--layout-' . esc_attr( $layout );

    // Navigation position applies to both layouts
    $nav_position = promptless_get_nav_position();
    $classes[]    = 'promptless-header--nav-' . esc_attr( $nav_position );

    // Floating overlay (per-request eligibility — see the function docs).
    if ( promptless_is_header_overlay_active() ) {
        $classes[] = 'promptless-header--overlay';
    }

    // No desktop-visible actions (no cart, no bar CTA) → let CSS collapse the
    // otherwise-empty actions slot so a right-aligned nav can sit flush with
    // the container edge. The slot still holds the desktop-hidden mobile
    // toggle, so it can't be detected as :empty in CSS — hence this class.
    if ( ! promptless_has_desktop_header_actions() ) {
        $classes[] = 'promptless-header--no-actions';
    }

    // Border (no-border class when disabled)
    if ( ! promptless_has_header_border() ) {
        $classes[] = 'promptless-header--no-border';
    }

    // Sticky (sticky class when enabled)
    // For stacked layouts, sticky applies to the separate nav element, not the header
    // But we still add a modifier class so CSS can make header sticky on mobile
    if ( promptless_is_header_sticky() ) {
        if ( 'stacked' !== $layout ) {
            $classes[] = 'promptless-header--sticky';
        } else {
            // Stacked layout: add modifier for mobile sticky (CSS handles breakpoint)
            $classes[] = 'promptless-header--sticky-mobile';
        }
    }

    return implode( ' ', $classes );
}

/**
 * Get header nav CSS classes for stacked layout
 *
 * Used for the separate nav element that appears outside the header
 * when using the stacked layout variant.
 *
 * @return string CSS classes for header nav element
 */
function promptless_get_header_nav_classes() {
    $classes = array();

    // Theme variant (may differ from header in stacked layout)
    $theme     = promptless_get_header_nav_theme();
    $classes[] = 'aisb-section--' . esc_attr( $theme );

    // Nav position
    $nav_position = promptless_get_nav_position();
    $classes[]    = 'promptless-header-nav--' . esc_attr( $nav_position );

    // Sticky (when header sticky is enabled, nav bar becomes sticky)
    if ( promptless_is_header_sticky() ) {
        $classes[] = 'promptless-header-nav--sticky';
    }

    // Border (match header border setting)
    if ( ! promptless_has_header_border() ) {
        $classes[] = 'promptless-header-nav--no-border';
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
 * Get the archive grid CSS classes, including the card image aspect-ratio
 * modifier.
 *
 * The archive card image aspect defaults to 16:9 (the historical hardcoded
 * value). The `promptless_archive_image_aspect` filter lets integrations
 * supply a different ratio per context — Promptless CPT Pages answers it
 * from its per-CPT `archive_image_aspect` setting, the same handshake as
 * the existing `promptless_archive_card_show_date` / `_author` filters.
 *
 * The vocabulary deliberately matches the PostGrid section's
 * `card_image_aspect_ratio` enum so the whole ecosystem speaks one
 * aspect language: '16:9' (wide), '4:3' (standard), '1:1' (square),
 * '4:5' (portrait — headshots/team directories).
 *
 * 16:9 (or any unrecognized value) emits no modifier class, so existing
 * sites render exactly as before.
 *
 * @return string Space-separated classes for the archive grid element.
 * @since 1.3.0
 */
function promptless_get_archive_grid_classes() {
    $classes = array( 'promptless-archive__grid' );

    $queried_post_type = '';
    if ( is_post_type_archive() ) {
        $queried_post_type = (string) get_query_var( 'post_type' );
        if ( is_array( get_query_var( 'post_type' ) ) ) {
            $types             = get_query_var( 'post_type' );
            $queried_post_type = (string) reset( $types );
        }
    } elseif ( is_home() ) {
        $queried_post_type = 'post';
    } elseif ( is_category() || is_tag() || is_tax() ) {
        $taxonomy = get_queried_object();
        if ( $taxonomy instanceof WP_Term ) {
            $tax_object = get_taxonomy( $taxonomy->taxonomy );
            if ( $tax_object && ! empty( $tax_object->object_type ) ) {
                $queried_post_type = (string) reset( $tax_object->object_type );
            }
        }
    }

    /**
     * Filter the archive card image aspect ratio.
     *
     * @since 1.3.0
     *
     * @param string $aspect    One of '16:9', '4:3', '1:1', '4:5'. Default '16:9'.
     * @param string $post_type The archive's post type ('' when indeterminate,
     *                          e.g. search results).
     */
    $aspect = apply_filters( 'promptless_archive_image_aspect', '16:9', $queried_post_type );

    $aspect_class_map = array(
        '4:3' => 'promptless-archive__grid--image-four-three',
        '1:1' => 'promptless-archive__grid--image-square',
        '4:5' => 'promptless-archive__grid--image-four-five',
    );

    if ( isset( $aspect_class_map[ $aspect ] ) ) {
        $classes[] = $aspect_class_map[ $aspect ];
    }

    return implode( ' ', $classes );
}

/**
 * Should the breadcrumb trail render on the current request?
 *
 * Single source of truth for the whole decision — the renderer
 * (Promptless_Breadcrumbs::render) and the asset enqueue gate
 * (Promptless_Assets) both call this, so the CSS only ships on requests
 * where the markup will actually be present (announcement-bar pattern).
 *
 * Decision order:
 *   1. Master toggle (off by default — enabling chrome on live sites is
 *      always an explicit owner action, never a theme-update surprise).
 *   2. Front page: never (universal convention — the trail's root IS home).
 *   3. WooCommerce contexts (shop, product, product taxonomies): defer to
 *      WooCommerce's own breadcrumb, which the theme already renders and
 *      styles (.woocommerce-breadcrumb). Two trails would be worse than
 *      none. Cart/checkout/account are ordinary pages (no WC breadcrumb)
 *      and follow the normal page rules.
 *   4. Per-context toggles (pages, posts, CPT singles, archives, search, 404).
 *   5. Per-post override meta (_promptless_breadcrumbs = 'hide').
 *   6. promptless_show_breadcrumbs filter — final word for child themes
 *      and integrations.
 *
 * @return bool
 * @since 1.3.0
 */
function promptless_show_breadcrumbs() {
    $show = true;

    // 1. Master toggle.
    if ( ! get_theme_mod( 'promptless_breadcrumbs_enabled', false ) ) {
        $show = false;
    }

    // 2. Never on the front page (static or latest-posts mode).
    if ( $show && is_front_page() ) {
        $show = false;
    }

    // 3. WooCommerce owns its own breadcrumb on true Woo contexts.
    if ( $show && function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
        $show = false;
    }

    // 4. Per-context toggles.
    if ( $show ) {
        if ( is_singular( 'post' ) ) {
            $show = (bool) get_theme_mod( 'promptless_breadcrumbs_on_posts', true );
        } elseif ( is_page() ) {
            $show = (bool) get_theme_mod( 'promptless_breadcrumbs_on_pages', true );
        } elseif ( is_singular() ) {
            $show = (bool) get_theme_mod( 'promptless_breadcrumbs_on_cpt_singles', true );
        } elseif ( is_search() ) {
            $show = (bool) get_theme_mod( 'promptless_breadcrumbs_on_search', true );
        } elseif ( is_404() ) {
            $show = (bool) get_theme_mod( 'promptless_breadcrumbs_on_404', true );
        } elseif ( is_archive() || is_home() ) {
            $show = (bool) get_theme_mod( 'promptless_breadcrumbs_on_archives', true );
        } else {
            $show = false;
        }
    }

    // 5. Per-post kill switch (landing pages with edge-to-edge heroes).
    if ( $show && is_singular() ) {
        $post_id = get_queried_object_id();
        if ( $post_id && 'hide' === get_post_meta( $post_id, Promptless_Breadcrumbs::META_KEY, true ) ) {
            $show = false;
        }
    }

    /**
     * Filter the final breadcrumb visibility decision.
     *
     * @since 1.3.0
     *
     * @param bool $show Whether the trail renders on this request.
     */
    return (bool) apply_filters( 'promptless_show_breadcrumbs', $show );
}

/**
 * Get the breadcrumbs theme variant.
 *
 * 'inherit' (the default) tracks the Content Theme setting so the bar
 * matches the page background it sits on; explicit light/dark overrides
 * are available for designs where the bar contrasts with the content.
 *
 * @return string 'light' or 'dark'
 * @since 1.3.0
 */
function promptless_get_breadcrumbs_theme() {
    $theme = get_theme_mod( 'promptless_breadcrumbs_theme', 'inherit' );

    if ( 'inherit' === $theme || ! in_array( $theme, array( 'light', 'dark' ), true ) ) {
        return promptless_get_content_theme();
    }

    return $theme;
}

/**
 * Get breadcrumb wrapper CSS classes including theme variant.
 *
 * Same aisb-section--{variant} recipe as every other chrome element, so
 * dark mode and the WCAG-derived link colors flow through the design
 * system with zero breadcrumb-specific machinery.
 *
 * @return string CSS classes for the breadcrumb nav element
 * @since 1.3.0
 */
function promptless_get_breadcrumbs_classes() {
    $classes   = array( 'promptless-breadcrumbs' );
    $classes[] = 'aisb-section--' . esc_attr( promptless_get_breadcrumbs_theme() );

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
            'promptless'
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
            esc_html( _n( '%d item in cart', '%d items in cart', $count, 'promptless' ) ),
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
	echo '<a href="' . esc_url( wc_get_cart_url() ) . '" class="aisb-btn aisb-btn--compact aisb-btn-ghost">' . esc_html__( 'View cart', 'promptless' ) . '</a>';
}

/**
 * Output mini-cart Checkout button with primary styling
 */
function promptless_mini_cart_checkout_button() {
	echo '<a href="' . esc_url( wc_get_checkout_url() ) . '" class="aisb-btn aisb-btn--compact aisb-btn-primary">' . esc_html__( 'Checkout', 'promptless' ) . '</a>';
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

    // Mobile behavior modifier. When set to 'inline', this class opts the
    // top bar OUT of the default mobile-hide rule so utility links remain
    // visible on small screens (mirroring desktop). When set to 'collapse',
    // the bar is hidden on mobile and rendered inside the hamburger drawer
    // via promptless_mobile_topbar_section() instead. Stays as a BEM
    // modifier on the same element to match the theme's existing
    // --sticky / --layout-stacked conventions.
    if ( 'inline' === promptless_get_topbar_mobile_behavior() ) {
        $classes[] = 'promptless-topbar--mobile-inline';
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

// =============================================================================
// Announcement Bar (Wave 6)
//
// Promotional/marketing bar that renders at the very top of every page, ABOVE
// the existing utility Top Bar and ABOVE the Header. Independent of the topbar
// — both can show together, one, or neither.
//
// Visibility decision flow (server-side):
//   1. Master toggle (promptless_announcement_enabled) must be true
//   2. Schedule window must include "now" (if either start/end date is set)
//   3. Visitor's dismiss cookie must NOT match the current message-content hash
//
// All three are AND-combined: any single false short-circuits to no render.
// The schedule + hash checks happen in PHP so there's no flash-of-bar-then-
// disappear and no client-clock-skew bugs.
// =============================================================================

/**
 * Master visibility check for the announcement bar.
 *
 * Combines the enable toggle, schedule window, message presence, and dismissal
 * cookie state. Returns true only when ALL of these allow the bar to render.
 *
 * @return bool True if the bar should render on this request.
 */
function promptless_has_announcement() {
    // Master toggle.
    if ( ! get_theme_mod( 'promptless_announcement_enabled', false ) ) {
        return false;
    }

    // Empty message → nothing meaningful to show. wp_strip_all_tags catches
    // the case where the user typed only formatting whitespace / empty tags.
    $message = (string) get_theme_mod( 'promptless_announcement_message', '' );
    if ( trim( wp_strip_all_tags( $message ) ) === '' ) {
        return false;
    }

    // Schedule window (server-side, site timezone — no flash-of-bar).
    if ( ! promptless_announcement_in_schedule() ) {
        return false;
    }

    // Visitor dismissed THIS specific message text (not just "any" message).
    if ( promptless_announcement_is_dismissed() ) {
        return false;
    }

    return true;
}

/**
 * Compute the cookie key suffix for the current announcement message.
 *
 * Returns a sha1 hash of the normalized message HTML. The dismiss cookie's
 * name embeds this hash, so when the site owner edits the message text the
 * hash changes and all previously-set dismiss cookies stop matching —
 * visitors see the new announcement automatically (Shopify pattern).
 *
 * Normalization: trim whitespace and collapse runs of internal whitespace.
 * Avoids "different cookie key for the same message just because it has
 * trailing newlines" failure mode.
 *
 * @return string The 40-char sha1 hex digest, or empty string if no message.
 */
function promptless_announcement_get_hash() {
    $message = (string) get_theme_mod( 'promptless_announcement_message', '' );
    $message = trim( $message );
    $message = preg_replace( '/\s+/', ' ', $message );

    if ( $message === '' ) {
        return '';
    }

    return sha1( $message );
}

/**
 * Build the dismissal cookie name for the current message.
 *
 * Centralized so the renderer (which writes the data attribute), the
 * frontend JS (which sets the cookie on click), and the visibility check
 * (which reads the cookie) all agree on the name format.
 *
 * @return string Cookie name. Empty string if no message hash is computable.
 */
function promptless_announcement_cookie_name() {
    $hash = promptless_announcement_get_hash();
    if ( $hash === '' ) {
        return '';
    }
    // Truncate hash to 16 chars — plenty of entropy to avoid collisions
    // across realistic message variants on a single site, and keeps the
    // cookie name short. Full sha1 (40 chars) would inflate cookie headers
    // unnecessarily.
    return 'promptless_announcement_dismissed_' . substr( $hash, 0, 16 );
}

/**
 * Has the current visitor dismissed THIS specific announcement message?
 *
 * "This specific" = matches the current message-content hash. Editing the
 * message text invalidates all prior dismissals automatically; the visitor
 * sees the new announcement without the site owner having to manually
 * "reset dismissals."
 *
 * @return bool True if the dismiss cookie is set for the current message.
 */
function promptless_announcement_is_dismissed() {
    // Non-dismissible bars are never "dismissed" — they always render
    // (subject to enabled + schedule).
    if ( ! get_theme_mod( 'promptless_announcement_dismissible', true ) ) {
        return false;
    }

    $cookie_name = promptless_announcement_cookie_name();
    if ( $cookie_name === '' ) {
        return false;
    }

    return isset( $_COOKIE[ $cookie_name ] ) && $_COOKIE[ $cookie_name ] === '1';
}

/**
 * Is "now" inside the announcement's schedule window?
 *
 * Both start_date and end_date are optional. Semantics:
 *   - both empty → always (no schedule constraint)
 *   - only start → visible from start onward
 *   - only end → visible until end
 *   - both → visible only inside [start, end]
 *
 * Comparisons use the WP site timezone (wp_timezone) so an "ends Friday at
 * midnight" announcement actually ends at midnight LOCAL time, not UTC.
 * Datetime values are stored in HTML5 datetime-local format (no timezone
 * suffix) and are interpreted as wall-clock times in the site's timezone.
 *
 * @return bool True if "now" falls within the configured window.
 */
function promptless_announcement_in_schedule() {
    $start_raw = (string) get_theme_mod( 'promptless_announcement_start_date', '' );
    $end_raw   = (string) get_theme_mod( 'promptless_announcement_end_date', '' );

    // Fast path: no schedule configured at all.
    if ( $start_raw === '' && $end_raw === '' ) {
        return true;
    }

    $tz  = wp_timezone();
    $now = new DateTimeImmutable( 'now', $tz );

    if ( $start_raw !== '' ) {
        $start = promptless_announcement_parse_datetime( $start_raw, $tz );
        if ( $start instanceof DateTimeImmutable && $now < $start ) {
            return false;
        }
    }

    if ( $end_raw !== '' ) {
        $end = promptless_announcement_parse_datetime( $end_raw, $tz );
        if ( $end instanceof DateTimeImmutable && $now > $end ) {
            return false;
        }
    }

    return true;
}

/**
 * Parse an announcement-bar datetime string into a DateTimeImmutable.
 *
 * The Customizer accepts HTML5 `datetime-local` format (YYYY-MM-DDTHH:MM)
 * with optional seconds (YYYY-MM-DDTHH:MM:SS). Browsers vary — Chrome
 * omits :SS by default, Safari includes it, etc. We try the more-specific
 * format first, then fall back to the shorter one.
 *
 * Returns false on unparseable input. The schedule check treats false as
 * "no constraint" so a malformed value (defensive — should already be
 * filtered by the sanitizer) doesn't fatal the page render.
 *
 * @param string       $value Raw datetime string.
 * @param DateTimeZone $tz    Timezone to interpret the value in.
 * @return DateTimeImmutable|false
 */
function promptless_announcement_parse_datetime( $value, DateTimeZone $tz ) {
    $parsed = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i:s', $value, $tz );
    if ( ! $parsed ) {
        $parsed = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i', $value, $tz );
    }
    return $parsed;
}

/**
 * CSS classes for the announcement bar element.
 *
 * Mirrors promptless_get_topbar_classes() conventions exactly so the bar
 * inherits the same theme-variant tokens (`aisb-section--light/dark`) the
 * editor and topbar already produce — visual consistency for free.
 *
 * @return string Space-separated class string.
 */
function promptless_get_announcement_classes() {
    $classes = array( 'promptless-announcement-bar' );

    $theme = get_theme_mod( 'promptless_announcement_theme', 'dark' );
    if ( ! in_array( $theme, array( 'light', 'dark' ), true ) ) {
        $theme = 'dark';
    }
    $classes[] = 'aisb-section--' . esc_attr( $theme );

    if ( get_theme_mod( 'promptless_announcement_dismissible', true ) ) {
        $classes[] = 'promptless-announcement-bar--dismissible';
    }

    return implode( ' ', $classes );
}

/**
 * Render the announcement bar HTML.
 *
 * Called from header.php BEFORE promptless_topbar() so it sits at the very
 * top of the page. Outputs nothing when promptless_has_announcement() is
 * false — meaning the bar is hidden, scheduled out, or dismissed.
 *
 * The container carries `data-cookie-key` so the dismiss JS knows which
 * cookie to set without re-deriving the hash on the client (defensive:
 * avoids any mismatch between PHP's sha1 and JS's hashing).
 */
function promptless_announcement_bar() {
    if ( ! promptless_has_announcement() ) {
        return;
    }

    $classes      = promptless_get_announcement_classes();
    $cookie_name  = promptless_announcement_cookie_name();
    $message_html = (string) get_theme_mod( 'promptless_announcement_message', '' );

    // Resolve [re:KEY] reusable element shortcodes if the Promptless plugin
    // is active and exposes its processor. Falls back to the raw message
    // when the plugin isn't there — the bar still renders, the shortcodes
    // just appear as literal text (acceptable degradation).
    if ( class_exists( '\\AISB\\Modern\\Core\\ReusableElementsProcessor' ) ) {
        try {
            $processor    = new \AISB\Modern\Core\ReusableElementsProcessor();
            $message_html = $processor->process( $message_html );
        } catch ( \Throwable $e ) {
            // Ignore — render the raw message rather than failing the page.
        }
    }

    // wp_kses_post is the same allow-list the Customizer applied at save
    // time. Re-running it here is defense-in-depth — protects against any
    // option-write that bypassed the Customizer (e.g. via wp_options manipulation).
    $message_html = wp_kses_post( $message_html );

    $cta_text = trim( (string) get_theme_mod( 'promptless_announcement_cta_text', '' ) );
    $cta_url  = trim( (string) get_theme_mod( 'promptless_announcement_cta_url', '' ) );
    $has_cta  = $cta_text !== '' && $cta_url !== '';

    // The announcement CTA label is author-set and often generic ("Learn
    // more"), which fails Lighthouse's "descriptive link text" SEO audit (it
    // reads a link's rendered text, not context). The announcement MESSAGE is
    // the natural disambiguator, so append it to the CTA as visually-hidden
    // context — the link's rendered text becomes "{label}: {message}"
    // (descriptive for crawlers, invisible on screen). Capped to a sane length
    // so the accessible name stays reasonable. Hidden via the theme's
    // `.aisb-visually-hidden` utility (style.css), always loaded.
    $cta_context = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $message_html ) ) );
    if ( function_exists( 'mb_strlen' ) && mb_strlen( $cta_context ) > 80 ) {
        $cta_context = rtrim( mb_substr( $cta_context, 0, 80 ) ) . '…';
    }

    $dismissible = (bool) get_theme_mod( 'promptless_announcement_dismissible', true );
    ?>
    <div
        class="<?php echo esc_attr( $classes ); ?>"
        role="region"
        aria-label="<?php esc_attr_e( 'Site announcement', 'promptless' ); ?>"
        <?php if ( $dismissible && $cookie_name !== '' ) : ?>
        data-cookie-key="<?php echo esc_attr( $cookie_name ); ?>"
        <?php endif; ?>
    >
        <div class="promptless-container">
            <div class="promptless-announcement-bar__inner">
                <div class="promptless-announcement-bar__message">
                    <?php echo $message_html; // Already kses-sanitized. ?>
                </div>

                <?php if ( $has_cta ) : ?>
                <?php
                /*
                 * Announcement CTA uses the AISB plugin's button classes —
                 * `aisb-btn aisb-btn--compact aisb-btn-primary` — the same
                 * set the header CTA uses (see promptless_header_cta()).
                 *
                 * Without these classes, the button rendered with just the
                 * theme's own `.promptless-announcement-bar__cta` rule and
                 * missed every global-settings adaptation the plugin's
                 * button system handles: neo-brutalist outline/lifted modes,
                 * button border-radius from global settings, button font
                 * weight / text-transform / letter-spacing tokens, the
                 * smart-color hover state, and the focus ring. Adding the
                 * plugin's classes routes the announcement CTA through
                 * the same cascade that styles every other button on the
                 * site — so toggling a global setting in the Customizer
                 * now updates the announcement button alongside everything
                 * else.
                 *
                 * The theme's `.promptless-announcement-bar__cta` class is
                 * kept on the element for layout-only properties (flex
                 * sizing inside the bar's flex row + nowrap). All visual
                 * styling (color, padding, radius, weight, transitions,
                 * hover) is delegated to the plugin's `.aisb-btn*` rules.
                 */
                ?>
                <a class="promptless-announcement-bar__cta aisb-btn aisb-btn--compact aisb-btn-primary" href="<?php echo esc_url( $cta_url ); ?>"><?php
                    echo esc_html( $cta_text );
                    if ( $cta_context !== '' ) :
                        ?><span class="aisb-visually-hidden"><?php
                        echo esc_html( sprintf(
                            /* translators: %s: announcement message text, appended (visually hidden) after the CTA label so the rendered link text is descriptive. */
                            _x( ': %s', 'hidden announcement CTA context suffix', 'promptless' ),
                            $cta_context
                        ) );
                        ?></span><?php
                    endif;
                ?></a>
                <?php endif; ?>

                <?php if ( $dismissible ) : ?>
                <button
                    type="button"
                    class="promptless-announcement-bar__dismiss"
                    aria-label="<?php esc_attr_e( 'Dismiss announcement', 'promptless' ); ?>"
                >
                    <?php /* Inline SVG instead of &times; — fonts position the × glyph at typographic x-height (not bbox center), which puts it visually high inside the hover pill. SVG gives perfect geometric centering and inherits currentColor through both light and dark theme variants. */ ?>
                    <svg
                        class="promptless-announcement-bar__dismiss-icon"
                        width="14"
                        height="14"
                        viewBox="0 0 14 14"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.75"
                        stroke-linecap="round"
                        aria-hidden="true"
                        focusable="false"
                    >
                        <path d="M3 3 L11 11 M11 3 L3 11" />
                    </svg>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Get top bar mobile behavior setting
 *
 * Possible return values:
 *   - 'inline'   : Top bar stays at the top of the page on mobile, mirroring
 *                  the desktop layout. The mobile-collapsed block is NOT
 *                  rendered into the hamburger drawer.
 *   - 'collapse' : Top bar is hidden on mobile (display:none) and its menus
 *                  are rendered inside the hamburger drawer via
 *                  promptless_mobile_topbar_section().
 *
 * Legacy compatibility (1.1.5 → 1.1.6+):
 *   The pre-1.1.6 setting offered a third value, 'hide', which hid the top
 *   bar on mobile AND skipped rendering it in the hamburger drawer. That
 *   option was removed because hiding important utility links by default
 *   is poor UX. Existing sites with 'hide' stored in wp_options are
 *   migrated at read time to 'collapse' so their utility links remain
 *   accessible (just routed through the hamburger). No database write
 *   happens here — the migration is purely runtime, so users can still
 *   re-select 'inline' in the Customizer without us having clobbered an
 *   intentional preference.
 *
 * @return string 'inline' or 'collapse'
 */
function promptless_get_topbar_mobile_behavior() {
    $value = get_theme_mod( 'promptless_topbar_mobile', 'collapse' );

    // Legacy 'hide' → 'collapse' migration. Anything else outside the
    // canonical set also falls back to 'collapse' (defensive — a future
    // database write from an external tool could plant an unexpected value).
    if ( ! in_array( $value, array( 'inline', 'collapse' ), true ) ) {
        return 'collapse';
    }

    return $value;
}

/**
 * Get footer grid modifier class based on active nav menus and social widget
 *
 * Returns CSS modifier classes that control grid column distribution.
 * The grid adapts dynamically based on which columns are actually present:
 * - Brand column (always present)
 * - 0-3 navigation columns
 * - Optional social widget column
 *
 * @return string CSS class(es) for grid layout modifiers
 */
function promptless_get_footer_grid_class() {
    $nav_count = 0;

    if ( has_nav_menu( 'footer-col-1' ) ) {
        $nav_count++;
    }
    if ( has_nav_menu( 'footer-col-2' ) ) {
        $nav_count++;
    }
    if ( has_nav_menu( 'footer-col-3' ) ) {
        $nav_count++;
    }

    $has_social = is_active_sidebar( 'footer-social' );

    // Build modifier class based on nav count
    $class = 'promptless-footer__main--nav-' . $nav_count;

    // Add social modifier if social widget is active
    if ( $has_social ) {
        $class .= ' promptless-footer__main--has-social';
    }

    return $class;
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
    // Only output when the top bar is enabled, in collapse mode, with at least
    // one assigned top-bar menu. Centralized in the shared predicate so the
    // menu-CTA position resolver stays in lock-step with what actually renders.
    if ( ! promptless_has_mobile_topbar_section() ) {
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

/**
 * Fix incorrect current-menu-item class on home link
 *
 * WordPress incorrectly adds 'current-menu-item' to custom links pointing to
 * the home URL on ALL pages because '/' is a substring of every URL.
 * This filter removes those classes when not actually on the front page.
 *
 * This is the WordPress-standard approach used by major themes (Astra,
 * GeneratePress, OceanWP) to handle this known WordPress core behavior.
 *
 * @since 1.0.3
 * @param array    $classes Current menu item classes.
 * @param WP_Post  $item    Menu item object.
 * @param stdClass $args    Menu arguments.
 * @return array   Modified classes.
 */
function promptless_fix_home_menu_item_classes( $classes, $item, $args ) {
    // Only process custom links (type 'custom')
    if ( 'custom' !== $item->type ) {
        return $classes;
    }

    // Get the home URL for comparison
    $home_url = trailingslashit( home_url() );
    $item_url = trailingslashit( $item->url );

    // Check if this custom link points to the home URL
    $is_home_link = (
        $item_url === $home_url ||
        $item_url === '/' ||
        $item_url === trailingslashit( '/' )
    );

    if ( ! $is_home_link ) {
        return $classes;
    }

    // If we're not on the front page, remove the incorrect current classes
    if ( ! is_front_page() ) {
        $classes = array_diff( $classes, array(
            'current-menu-item',
            'current_page_item',
            'current-menu-ancestor',
            'current-page-ancestor',
        ) );
    }

    return $classes;
}
add_filter( 'nav_menu_css_class', 'promptless_fix_home_menu_item_classes', 10, 3 );

/**
 * Check if current page needs WooCommerce assets
 *
 * Returns true if:
 * - Is a WooCommerce page (shop, product, cart, checkout, account)
 * - Mini-cart is enabled in header
 * - Page has a productgrid section from Promptless WP
 *
 * Per-request memoized: the answer for a given post ID can't change
 * mid-request (WC conditional tags, header-customizer state, and post
 * meta are all stable within one PHP process), and the function is hit
 * multiple times per page load by the asset-enqueue layer. Without
 * caching, each call re-reads `_aisb_sections` post meta and JSON-decodes
 * it just to scan for a `productgrid` section type.
 *
 * Cache key: the global `$post->ID` (or `__no_post__` when absent).
 * Object cache is request-scoped via the function's static array, which
 * is the right scope here — we don't want to persist across requests
 * because the WC conditional tags depend on the current request URL.
 *
 * @since 1.2.0
 * @return bool
 */
function promptless_needs_woocommerce_assets() {
    static $cache = array();

    // Cache key: per-post when available, '__no_post__' otherwise. The
    // global $post is the right scope because the only branch that varies
    // by content is the productgrid scan; the WC/mini-cart branches are
    // request-global and would produce the same answer for any post in
    // the same request anyway.
    global $post;
    $cache_key = ( $post && isset( $post->ID ) ) ? (int) $post->ID : '__no_post__';

    if ( array_key_exists( $cache_key, $cache ) ) {
        return $cache[ $cache_key ];
    }

    // WooCommerce must be active
    if ( ! class_exists( 'WooCommerce' ) ) {
        return $cache[ $cache_key ] = false;
    }

    // Always load on WooCommerce pages
    if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
        return $cache[ $cache_key ] = true;
    }

    // Load if mini-cart is enabled (needs AJAX updates)
    if ( function_exists( 'promptless_has_header_cart' ) && promptless_has_header_cart() ) {
        return $cache[ $cache_key ] = true;
    }

    // Check if page has productgrid section
    if ( $post ) {
        $sections = get_post_meta( $post->ID, '_aisb_sections', true );

        if ( is_string( $sections ) ) {
            $sections = json_decode( $sections, true );
        }

        if ( is_array( $sections ) ) {
            foreach ( $sections as $section ) {
                if ( isset( $section['type'] ) && $section['type'] === 'productgrid' ) {
                    return $cache[ $cache_key ] = true;
                }
            }
        }
    }

    return $cache[ $cache_key ] = false;
}


/**
 * Check if header search should be displayed
 *
 * Mirrors promptless_has_header_cart(): a customizer toggle, default OFF.
 * Search is a header SETTING, not a default - small nav-driven sites never
 * grow an unused magnifying glass (see docs/SEARCH_DESIGN.md section 1).
 *
 * @return bool True if header search is enabled.
 */
function promptless_has_header_search() {
	return (bool) get_theme_mod( 'promptless_header_search_enabled', false );
}

/**
 * Whether the search trigger shows a visible "Search" text label.
 *
 * Icon-only is the default (actions-slot family consistency with the cart
 * icon); the visible label is the NN/g-friendly opt-in for search-critical
 * sites (higher ed, associations).
 *
 * @return bool
 */
function promptless_header_search_show_label() {
	return (bool) get_theme_mod( 'promptless_header_search_show_label', false );
}

/**
 * Output the header search trigger button.
 *
 * Sibling of promptless_header_cart() in the actions slot - same size,
 * hover, focus, and light/dark treatment via the shared toggle styles in
 * header.css. Clicking opens the full-screen search overlay (rendered
 * once in wp_footer by promptless_search_overlay()).
 */
function promptless_header_search() {
	if ( ! promptless_has_header_search() ) {
		return;
	}
	?>
	<button
		type="button"
		class="promptless-header__search-toggle<?php echo promptless_header_search_show_label() ? ' promptless-header__search-toggle--labeled' : ''; ?>"
		aria-label="<?php esc_attr_e( 'Search this site', 'promptless' ); ?>"
		aria-haspopup="dialog"
		aria-expanded="false"
		aria-controls="promptless-search-overlay"
	>
		<?php promptless_search_icon(); ?>
		<?php if ( promptless_header_search_show_label() ) : ?>
			<span class="promptless-header__search-label"><?php esc_html_e( 'Search', 'promptless' ); ?></span>
		<?php endif; ?>
	</button>
	<?php
}

/**
 * Output search (magnifying glass) icon SVG - matches the cart icon's
 * stroke style and 20px sizing.
 */
function promptless_search_icon() {
	?>
	<svg class="promptless-header__search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
		<circle cx="11" cy="11" r="8"></circle>
		<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
	</svg>
	<?php
}

/**
 * Render the full-screen search overlay once, in the footer.
 *
 * Markup contract with assets/js/search-overlay.js and search.css.
 * The no-JS path degrades honestly: the form is a plain GET to the
 * site root, so submitting lands on search.php. ARIA follows the APG
 * combobox pattern; the JS owns aria-expanded / aria-activedescendant.
 * Sizing rule (modal finding #12): the fixed inset container tracks the
 * VISUAL viewport - never size against 100vh.
 */
function promptless_search_overlay() {
	if ( ! promptless_has_header_search() ) {
		return;
	}
	?>
	<div id="promptless-search-overlay" class="promptless-search-overlay" hidden>
		<div class="promptless-search-overlay__backdrop" data-search-close></div>
		<div class="promptless-search-overlay__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Search this site', 'promptless' ); ?>">
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="promptless-search-overlay__form">
				<?php promptless_search_icon(); ?>
				<input
					type="search"
					name="s"
					class="promptless-search-overlay__input"
					placeholder="<?php esc_attr_e( 'Search…', 'promptless' ); ?>"
					autocomplete="off"
					role="combobox"
					aria-expanded="false"
					aria-controls="promptless-search-results"
					aria-autocomplete="list"
				/>
				<button type="submit" class="screen-reader-text"><?php esc_html_e( 'Search', 'promptless' ); ?></button>
				<?php // Close lives IN the flex row: align-items centers it against
				      // the input pixel-perfectly with zero positioning math. ?>
				<button type="button" class="promptless-search-overlay__close" data-search-close aria-label="<?php esc_attr_e( 'Close search', 'promptless' ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
				</button>
			</form>
			<div class="promptless-search-overlay__status screen-reader-text" role="status" aria-live="polite"></div>
			<ul id="promptless-search-results" class="promptless-search-overlay__results" role="listbox" aria-label="<?php esc_attr_e( 'Search results', 'promptless' ); ?>" hidden></ul>
			<div class="promptless-search-overlay__empty" hidden>
				<p><?php esc_html_e( 'No results found. Try a different word, or start from a main page:', 'promptless' ); ?></p>
			</div>
			<div class="promptless-search-overlay__footer" hidden>
				<a class="promptless-search-overlay__view-all" href="#"><?php esc_html_e( 'View all results →', 'promptless' ); ?></a>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'promptless_search_overlay' );
