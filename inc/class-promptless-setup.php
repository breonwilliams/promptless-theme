<?php
/**
 * Theme Setup Class
 *
 * Handles theme setup, support declarations, and menu registration.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Promptless_Setup
 */
class Promptless_Setup {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'after_setup_theme', array( $this, 'setup' ) );
        add_action( 'widgets_init', array( $this, 'register_sidebars' ) );

        // WooCommerce wrapper hooks - add our container structure
        add_action( 'woocommerce_before_main_content', array( $this, 'woocommerce_wrapper_start' ), 10 );
        add_action( 'woocommerce_after_main_content', array( $this, 'woocommerce_wrapper_end' ), 10 );

        // Disable WooCommerce's default page title - theme provides its own via woocommerce.php
        add_filter( 'woocommerce_show_page_title', '__return_false' );
    }

    /**
     * Theme setup
     */
    public function setup() {
        // Make theme available for translation
        load_theme_textdomain( 'promptless-theme', PROMPTLESS_THEME_DIR . '/languages' );

        // Add default posts and comments RSS feed links to head
        add_theme_support( 'automatic-feed-links' );

        // Let WordPress manage the document title
        add_theme_support( 'title-tag' );

        // Enable support for Post Thumbnails
        add_theme_support( 'post-thumbnails' );

        // Set default thumbnail size
        set_post_thumbnail_size( 1200, 675, true );

        // Add custom image sizes for archive cards
        add_image_size( 'promptless-card', 600, 400, true );
        add_image_size( 'promptless-card-large', 800, 450, true );

        // Register navigation menus
        register_nav_menus(
            array(
                'primary'      => esc_html__( 'Primary Menu', 'promptless-theme' ),
                'footer'       => esc_html__( 'Footer Menu', 'promptless-theme' ),
                'footer-col-1' => esc_html__( 'Footer Column 1', 'promptless-theme' ),
                'footer-col-2' => esc_html__( 'Footer Column 2', 'promptless-theme' ),
            )
        );

        // Switch default core markup to valid HTML5
        add_theme_support(
            'html5',
            array(
                'search-form',
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
                'style',
                'script',
                'navigation-widgets',
            )
        );

        // Add support for custom logo
        add_theme_support(
            'custom-logo',
            array(
                'height'      => 60,
                'width'       => 200,
                'flex-width'  => true,
                'flex-height' => true,
            )
        );

        // Add support for responsive embeds
        add_theme_support( 'responsive-embeds' );

        // Add support for block styles
        add_theme_support( 'wp-block-styles' );

        // Add support for wide and full alignment
        add_theme_support( 'align-wide' );

        // Add support for editor styles
        add_theme_support( 'editor-styles' );

        // Declare support for Promptless WP plugin native integration
        add_theme_support( 'aisb-native-theme' );

        // WooCommerce Support
        // Following official WooCommerce documentation for theme integration
        // See: https://woocommerce.com/document/woocommerce-theme-developer-handbook/
        if ( class_exists( 'WooCommerce' ) ) {
            add_theme_support( 'woocommerce', array(
                'thumbnail_image_width' => 300,
                'single_image_width'    => 600,
                'product_grid'          => array(
                    'default_rows'    => 3,
                    'min_rows'        => 1,
                    'default_columns' => 3,
                    'min_columns'     => 1,
                    'max_columns'     => 6,
                ),
            ) );

            // Add support for WooCommerce product gallery features
            add_theme_support( 'wc-product-gallery-zoom' );
            add_theme_support( 'wc-product-gallery-lightbox' );
            add_theme_support( 'wc-product-gallery-slider' );

            // Remove default WooCommerce wrappers (we provide our own via hooks)
            remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
            remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
        }
    }

    /**
     * Register widget areas
     */
    public function register_sidebars() {
        // Footer widget area
        register_sidebar(
            array(
                'name'          => esc_html__( 'Footer Widgets', 'promptless-theme' ),
                'id'            => 'footer-widgets',
                'description'   => esc_html__( 'Add widgets here to appear in your footer.', 'promptless-theme' ),
                'before_widget' => '<div id="%1$s" class="promptless-footer__widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h4 class="promptless-footer__widget-title">',
                'after_title'   => '</h4>',
            )
        );

        // WooCommerce sidebar (Shop pages)
        if ( class_exists( 'WooCommerce' ) ) {
            register_sidebar(
                array(
                    'name'          => esc_html__( 'Shop Sidebar', 'promptless-theme' ),
                    'id'            => 'shop-sidebar',
                    'description'   => esc_html__( 'Add widgets here to appear in the shop sidebar.', 'promptless-theme' ),
                    'before_widget' => '<div id="%1$s" class="promptless-widget %2$s">',
                    'after_widget'  => '</div>',
                    'before_title'  => '<h4 class="promptless-widget__title">',
                    'after_title'   => '</h4>',
                )
            );
        }

        // Social links widget area
        register_sidebar(
            array(
                'name'          => esc_html__( 'Footer Social Links', 'promptless-theme' ),
                'id'            => 'footer-social',
                'description'   => esc_html__( 'Add social link widgets here.', 'promptless-theme' ),
                'before_widget' => '<div id="%1$s" class="promptless-footer__social %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<span class="screen-reader-text">',
                'after_title'   => '</span>',
            )
        );
    }

    /**
     * WooCommerce container wrapper start
     *
     * Wraps WooCommerce content in theme's container structure for proper
     * max-width handling via Global Settings (--aisb-section-max-width).
     *
     * @since 1.0.0
     */
    public function woocommerce_wrapper_start() {
        echo '<div class="promptless-container">';
        echo '<div class="promptless-woocommerce">';
    }

    /**
     * WooCommerce container wrapper end
     *
     * @since 1.0.0
     */
    public function woocommerce_wrapper_end() {
        echo '</div><!-- .promptless-woocommerce -->';
        echo '</div><!-- .promptless-container -->';
    }
}
