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
define( 'PROMPTLESS_THEME_VERSION', '1.0.0' );

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
require_once PROMPTLESS_THEME_DIR . '/inc/class-promptless-customizer.php';
require_once PROMPTLESS_THEME_DIR . '/inc/template-functions.php';

/**
 * Initialize theme classes
 */
function promptless_theme_init() {
    new Promptless_Setup();
    new Promptless_Assets();
    new Promptless_Integration();
    new Promptless_Customizer();
}
add_action( 'after_setup_theme', 'promptless_theme_init', 5 );

/**
 * Set content width for embedded media
 */
function promptless_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'promptless_content_width', 1280 );
}
add_action( 'after_setup_theme', 'promptless_content_width', 0 );
