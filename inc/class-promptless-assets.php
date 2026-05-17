<?php
/**
 * Theme Assets Class
 *
 * Handles enqueueing of stylesheets and scripts.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Promptless_Assets
 */
class Promptless_Assets {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'maybe_dequeue_woocommerce_assets' ), 99 );
        add_action( 'customize_preview_init', array( $this, 'enqueue_customizer_preview_scripts' ) );

        // Inject the user's plugin global-setting colors as CSS variables
        // into the block editor iframe, so editor-style.css's `var()` calls
        // resolve to the same values the front end is using. Without this
        // the editor preview falls back to the hardcoded defaults baked
        // into editor-style.css and stops reflecting Global Settings
        // changes the moment they're saved.
        add_filter( 'block_editor_settings_all', array( $this, 'inject_editor_iframe_tokens' ), 10, 1 );
    }

    /**
     * Enqueue stylesheets
     */
    public function enqueue_styles() {
        // Main theme stylesheet (style.css with theme header and base reset)
        wp_enqueue_style(
            'promptless-theme-style',
            get_stylesheet_uri(),
            array(),
            PROMPTLESS_THEME_VERSION
        );

        // Header styles (minified for PageSpeed optimization)
        wp_enqueue_style(
            'promptless-theme-header',
            PROMPTLESS_THEME_URI . '/assets/css/header.min.css',
            array( 'promptless-theme-style' ),
            PROMPTLESS_THEME_VERSION
        );

        // Breakpoint-dependent header rules (Mobile Menu Breakpoint customizer).
        //
        // The 13 @media (min-width: 768px) / (max-width: 767px) blocks that
        // control where the main nav collapses live in a SEPARATE file —
        // header-breakpoint.css — so there is only ever one source of those
        // rules in the cascade.
        //
        // Branch:
        //   - User on DEFAULT (768) → load the static .min.css file (cached
        //     by the browser, no inline CSS bloat on every page).
        //   - User on NON-DEFAULT (640 / 900 / 1024 / 1200) → DO NOT load
        //     the static file. Promptless_Mobile_Menu_Breakpoint emits an
        //     inline <style> block in <head> with the same rules rewritten
        //     at the chosen breakpoint. Loading both would re-introduce the
        //     cascade flaw the v1 implementation hit (original 768 rule
        //     still firing in the gap range).
        //
        // The class_exists guard keeps the theme functional if someone
        // strips that class out — without the inline override the static
        // file is still the safest default.
        if (
            ! class_exists( 'Promptless_Mobile_Menu_Breakpoint' )
            || Promptless_Mobile_Menu_Breakpoint::is_default()
        ) {
            wp_enqueue_style(
                'promptless-theme-header-breakpoint',
                PROMPTLESS_THEME_URI . '/assets/css/header-breakpoint.min.css',
                array( 'promptless-theme-header' ),
                PROMPTLESS_THEME_VERSION
            );
        }

        // Footer styles (minified for PageSpeed optimization)
        wp_enqueue_style(
            'promptless-theme-footer',
            PROMPTLESS_THEME_URI . '/assets/css/footer.min.css',
            array( 'promptless-theme-style' ),
            PROMPTLESS_THEME_VERSION
        );

        // Archive and content styles - only load on blog/archive pages
        // EXCLUDED is_page(): Pages using the plugin don't need archive.css (saves ~14KB)
        // PageSpeed optimization: Reduces unused CSS on landing pages
        if ( is_archive() || is_search() || is_singular( 'post' ) || is_home() || is_404() ) {
            wp_enqueue_style(
                'promptless-theme-archive',
                PROMPTLESS_THEME_URI . '/assets/css/archive.min.css',
                array( 'promptless-theme-style' ),
                PROMPTLESS_THEME_VERSION
            );
        }

        // WooCommerce styles - only load when page actually needs WooCommerce
        // PageSpeed optimization: Saves ~116KB on non-shop pages
        if ( function_exists( 'promptless_needs_woocommerce_assets' ) && promptless_needs_woocommerce_assets() ) {
            wp_enqueue_style(
                'promptless-theme-woocommerce',
                PROMPTLESS_THEME_URI . '/assets/css/woocommerce.min.css',
                array( 'promptless-theme-style', 'woocommerce-general' ),
                PROMPTLESS_THEME_VERSION
            );
        }

        // Announcement bar styles — only enqueue when the bar will actually
        // render (master toggle on, message non-empty, in schedule, not
        // dismissed by this visitor). Same gatekeeper the renderer uses, so
        // the CSS only ships on requests where the markup will be present.
        if ( function_exists( 'promptless_has_announcement' ) && promptless_has_announcement() ) {
            wp_enqueue_style(
                'promptless-theme-announcement-bar',
                PROMPTLESS_THEME_URI . '/assets/css/announcement-bar.css',
                array( 'promptless-theme-style' ),
                PROMPTLESS_THEME_VERSION
            );
        }
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
        // Navigation script (mobile menu toggle, mini-cart) - minified for PageSpeed
        wp_enqueue_script(
            'promptless-theme-navigation',
            PROMPTLESS_THEME_URI . '/assets/js/navigation.min.js',
            array(),
            PROMPTLESS_THEME_VERSION,
            true
        );

        // Inject --aisb-page-header-height on document.documentElement so
        // the plugin's section-container.css can size full-height sections
        // (.aisb-section--height-full) correctly — calc(100dvh - header -
        // 2*gutter). The value comes from main.offsetTop, which captures
        // the cumulative height of everything above the content area:
        // optional topbar + header + (stacked layout's separate nav element
        // when present). One measurement, one source of truth.
        //
        // We attach this to the navigation script (rather than emitting a
        // standalone inline tag) so we get the standard WP dependency +
        // footer-placement guarantees: navigation is enqueued in_footer,
        // so this script runs after the DOM has been parsed.
        //
        // Three measurement triggers cover the realistic edge cases:
        //   1. Immediate run (DOM ready, JS in footer) — primary path
        //   2. window 'load' — handles cases where fonts/images push the
        //      header taller after initial render
        //   3. window 'resize' — handles breakpoint transitions that
        //      change header layout (mobile menu vs desktop nav)
        //
        // The CSS-side fallback (var(--aisb-page-header-height, 80px))
        // covers the brief moment before this script runs and any non-
        // Promptless theme that doesn't provide this integration.
        $header_height_script = <<<JS
(function () {
    function setPromptlessHeaderHeight() {
        var main = document.querySelector('main');
        if (!main) { return; }
        document.documentElement.style.setProperty(
            '--aisb-page-header-height',
            main.offsetTop + 'px'
        );
    }
    setPromptlessHeaderHeight();
    window.addEventListener('load', setPromptlessHeaderHeight);
    window.addEventListener('resize', setPromptlessHeaderHeight);
})();
JS;
        wp_add_inline_script(
            'promptless-theme-navigation',
            $header_height_script,
            'after'
        );

        // Announcement bar dismiss script — enqueue only when the bar will
        // render AND it's actually dismissible. Saves an HTTP request on
        // pages where the bar is hidden, scheduled out, or non-dismissible.
        if (
            function_exists( 'promptless_has_announcement' ) &&
            promptless_has_announcement() &&
            (bool) get_theme_mod( 'promptless_announcement_dismissible', true )
        ) {
            wp_enqueue_script(
                'promptless-theme-announcement-bar',
                PROMPTLESS_THEME_URI . '/assets/js/announcement-bar.js',
                array(),
                PROMPTLESS_THEME_VERSION,
                true
            );
        }

        // WooCommerce cart fragments for AJAX updates
        if ( function_exists( 'promptless_has_header_cart' ) && promptless_has_header_cart() ) {
            wp_enqueue_script( 'wc-cart-fragments' );
        }

        // Comment reply script
        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
            wp_enqueue_script( 'comment-reply' );
        }
    }

    /**
     * Enqueue customizer preview scripts
     *
     * Handles real-time preview updates for logo/title toggle and site name changes.
     *
     * @since 1.0.0
     */
    public function enqueue_customizer_preview_scripts() {
        wp_enqueue_script(
            'promptless-theme-customizer-preview',
            PROMPTLESS_THEME_URI . '/assets/js/customizer-preview.js',
            array( 'customize-preview', 'jquery' ),
            PROMPTLESS_THEME_VERSION,
            true
        );
    }

    /**
     * Conditionally dequeue WooCommerce core assets
     *
     * WooCommerce loads its assets on every page by default.
     * This dequeues them when not needed for performance.
     *
     * PageSpeed optimization: Saves ~266KB (CSS + JS) on non-shop pages.
     *
     * @since 1.2.0
     */
    public function maybe_dequeue_woocommerce_assets() {
        // Only run if WooCommerce is active
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }

        // Keep assets if page needs them
        if ( function_exists( 'promptless_needs_woocommerce_assets' ) && promptless_needs_woocommerce_assets() ) {
            return;
        }

        // Dequeue WooCommerce classic styles
        wp_dequeue_style( 'woocommerce-general' );
        wp_dequeue_style( 'woocommerce-layout' );
        wp_dequeue_style( 'woocommerce-smallscreen' );

        // Note: wc-blocks-style is intentionally NOT dequeued per WooCommerce recommendation.
        // Dequeuing it can break block rendering. See:
        // https://developer.woocommerce.com/2023/07/19/woocommerce-blocks-10-7-update/

        // Dequeue WooCommerce scripts
        wp_dequeue_script( 'woocommerce' );
        wp_dequeue_script( 'wc-add-to-cart' );
        wp_dequeue_script( 'wc-cart-fragments' );
        wp_dequeue_script( 'js-cookie' );
        wp_dequeue_script( 'jquery-blockui' );
        wp_dequeue_script( 'wc-order-attribution' );
        wp_dequeue_script( 'sourcebuster-js' );
    }

    /**
     * Inject the user's plugin global-setting colors as CSS variables
     * into the block editor iframe.
     *
     * The block editor renders blocks inside an iframe. CSS variables
     * declared at `:root` in the parent document don't cascade through
     * iframe boundaries, so the editor preview otherwise can't see the
     * user's custom palette — `editor-style.css` would always fall back
     * to its hardcoded defaults.
     *
     * The `block_editor_settings_all` filter is the WordPress 6.0+
     * supported way to push CSS into the editor iframe. Each entry in
     * `$settings['styles']` becomes a `<style>` tag inside the iframe.
     *
     * Reads global settings via `Promptless_Integration::get_global_settings()`,
     * which already handles the plugin-not-active case (returns the
     * theme's local defaults). So this works whether the plugin is
     * active or not — the editor preview is consistent with whatever
     * the front-end would render.
     *
     * Scoped to `.editor-styles-wrapper` (the iframe's body class) rather
     * than `:root` so the variables apply specifically to the editor
     * content area without leaking to the editor chrome (toolbars,
     * sidebars, etc.) which uses its own --aisb-editor-* token system.
     *
     * @param array $settings Block editor settings array.
     * @return array Modified settings with our CSS injected.
     * @since 1.1.5
     */
    public function inject_editor_iframe_tokens( $settings ) {
        // Defensive: integration class may not be loaded in edge cases
        // (e.g. very early hook fires). Bail silently — editor will use
        // editor-style.css fallbacks.
        if ( ! class_exists( 'Promptless_Integration' ) ) {
            return $settings;
        }

        $integration = new Promptless_Integration();
        $settings_data = $integration->get_global_settings();

        // The 8 tokens editor-style.css consumes via var(). Keep this
        // list narrow — every key here is a user-visible editor preview
        // surface, and every key NOT here just uses the hardcoded
        // fallback in editor-style.css. Adding a new key requires
        // updating both this list AND the corresponding var() call site.
        $css_var_map = array(
            '--aisb-color-primary'           => 'primary_color',
            '--aisb-color-text'              => 'text_color',
            '--aisb-color-text-muted'        => 'muted_text_color',
            '--aisb-color-background'        => 'background_color',
            '--aisb-color-surface'           => 'surface_color',
            '--aisb-color-border'            => 'border_color',
            '--aisb-section-font-heading'    => 'heading_font',
            '--aisb-section-font-body'       => 'body_font',
        );

        $declarations = array();
        foreach ( $css_var_map as $css_var => $settings_key ) {
            if ( isset( $settings_data[ $settings_key ] ) && '' !== $settings_data[ $settings_key ] ) {
                // Hex color values come from a controlled list (plugin or
                // theme defaults) but are still user-influenceable, so
                // sanitize per type. Font family values may include
                // commas and quotes legitimately.
                $value = $settings_data[ $settings_key ];
                if ( false !== strpos( $css_var, 'color' ) ) {
                    $value = sanitize_hex_color( $value );
                    if ( ! $value ) {
                        continue; // skip invalid hex
                    }
                }
                $declarations[] = sprintf(
                    '    %s: %s;',
                    esc_attr( $css_var ),
                    // Font values: escape any closing-brace / angle-bracket characters
                    // that could break out of the <style> tag. Hex colors above are
                    // already validated by sanitize_hex_color so this is belt-and-suspenders.
                    esc_attr( $value )
                );
            }
        }

        // Bail if we have nothing to inject (e.g. plugin inactive AND
        // theme integration class returned nothing valid). The editor
        // will use editor-style.css fallbacks.
        if ( empty( $declarations ) ) {
            return $settings;
        }

        $css = ".editor-styles-wrapper {\n" . implode( "\n", $declarations ) . "\n}\n";

        // The `styles` array is the documented place for editor-iframe
        // CSS injection in WP 6.0+. Each entry creates a <style> inside
        // the iframe — not a separate stylesheet request, so no extra
        // HTTP roundtrip and no caching considerations.
        if ( ! isset( $settings['styles'] ) || ! is_array( $settings['styles'] ) ) {
            $settings['styles'] = array();
        }
        $settings['styles'][] = array( 'css' => $css );

        return $settings;
    }
}
