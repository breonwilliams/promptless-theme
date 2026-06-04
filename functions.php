<?php
/**
 * Promptless Theme functions and definitions
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Theme version constant
 */
define( 'PROMPTLESS_THEME_VERSION', '1.2.1' );

/**
 * Theme directory path
 */
define( 'PROMPTLESS_THEME_DIR', get_template_directory() );

/**
 * Theme directory URI
 */
define( 'PROMPTLESS_THEME_URI', get_template_directory_uri() );

/**
 * Load theme class files
 */
require_once PROMPTLESS_THEME_DIR . '/inc/class-promptless-setup.php';
require_once PROMPTLESS_THEME_DIR . '/inc/class-promptless-assets.php';
require_once PROMPTLESS_THEME_DIR . '/inc/class-promptless-integration.php';
require_once PROMPTLESS_THEME_DIR . '/inc/class-promptless-plugin-bridge.php';
require_once PROMPTLESS_THEME_DIR . '/inc/class-promptless-customizer.php';
require_once PROMPTLESS_THEME_DIR . '/inc/class-promptless-mobile-menu-breakpoint.php';
require_once PROMPTLESS_THEME_DIR . '/inc/template-functions.php';

/**
 * Initialize theme classes
 */
function promptless_theme_init() {
    new Promptless_Setup();
    new Promptless_Assets();
    new Promptless_Integration();
    new Promptless_Customizer();
    new Promptless_Mobile_Menu_Breakpoint();
}
add_action( 'after_setup_theme', 'promptless_theme_init', 5 );

/**
 * Set content width for embedded media
 */
function promptless_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'promptless_content_width', 1280 );
}
add_action( 'after_setup_theme', 'promptless_content_width', 0 );

/**
 * Add body class to disable WooCommerce default button styles.
 *
 * Tells WooCommerce that the theme handles its own button styling, which
 * disables WooCommerce's `:where()` selector default button CSS. Without
 * this, WooCommerce applies gray text color to buttons.
 *
 * Why this is a NAMED function instead of an anonymous closure: child
 * themes and site-owner code need a recoverable handle to call
 * `remove_filter('body_class', 'promptless_woocommerce_body_class')` if
 * they want to opt out (e.g. when their own integration manages the
 * WooCommerce button-styles signal). Closures give callers no such
 * handle — once registered they can only be removed via reflection on
 * the WP filter registry, which is not a contract we should require.
 *
 * @param array $classes Existing body classes from WordPress core.
 * @return array Possibly-augmented body classes.
 * @since 1.1.5
 */
function promptless_woocommerce_body_class( $classes ) {
    if ( class_exists( 'WooCommerce' ) ) {
        $classes[] = 'woocommerce-block-theme-has-button-styles';
    }
    return $classes;
}
add_filter( 'body_class', 'promptless_woocommerce_body_class' );
