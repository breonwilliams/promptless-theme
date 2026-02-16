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

        // Header styles
        wp_enqueue_style(
            'promptless-theme-header',
            PROMPTLESS_THEME_URI . '/assets/css/header.css',
            array( 'promptless-theme-style' ),
            PROMPTLESS_THEME_VERSION
        );

        // Footer styles
        wp_enqueue_style(
            'promptless-theme-footer',
            PROMPTLESS_THEME_URI . '/assets/css/footer.css',
            array( 'promptless-theme-style' ),
            PROMPTLESS_THEME_VERSION
        );

        // Archive and content styles (for blog, category, tag, search, single, pages)
        wp_enqueue_style(
            'promptless-theme-archive',
            PROMPTLESS_THEME_URI . '/assets/css/archive.css',
            array( 'promptless-theme-style' ),
            PROMPTLESS_THEME_VERSION
        );

        // WooCommerce styles - only load when WooCommerce is active
        // Follows official WooCommerce theme integration pattern
        if ( class_exists( 'WooCommerce' ) ) {
            wp_enqueue_style(
                'promptless-theme-woocommerce',
                PROMPTLESS_THEME_URI . '/assets/css/woocommerce.css',
                array( 'promptless-theme-style', 'woocommerce-general' ),
                PROMPTLESS_THEME_VERSION
            );
        }
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
        // Navigation script (mobile menu toggle)
        wp_enqueue_script(
            'promptless-theme-navigation',
            PROMPTLESS_THEME_URI . '/assets/js/navigation.js',
            array(),
            PROMPTLESS_THEME_VERSION,
            true
        );

        // Comment reply script
        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
            wp_enqueue_script( 'comment-reply' );
        }
    }
}
