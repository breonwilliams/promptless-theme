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
        if ( is_archive() || is_search() || is_singular( 'post' ) || is_home() ) {
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
}
