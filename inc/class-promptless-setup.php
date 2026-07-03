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

        // Enhance logo accessibility with role="img" for SVG edge cases
        add_filter( 'get_custom_logo_image_attributes', array( $this, 'enhance_logo_accessibility' ), 10, 3 );

        // WooCommerce wrapper hooks - add our container structure
        add_action( 'woocommerce_before_main_content', array( $this, 'woocommerce_wrapper_start' ), 10 );
        add_action( 'woocommerce_after_main_content', array( $this, 'woocommerce_wrapper_end' ), 10 );

        // Disable WooCommerce's default page title - theme provides its own via woocommerce.php
        add_filter( 'woocommerce_show_page_title', '__return_false' );

        // Change sale badge text from "Sale!" to "Sale" to match Product Grid section
        add_filter( 'woocommerce_sale_flash', array( $this, 'custom_sale_flash' ), 10, 3 );
    }

    /**
     * Theme setup
     */
    public function setup() {
        // Make theme available for translation
        load_theme_textdomain( 'promptless', PROMPTLESS_THEME_DIR . '/languages' );

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
                'primary'      => esc_html__( 'Primary Menu', 'promptless' ),
                'footer'       => esc_html__( 'Footer Menu', 'promptless' ),
                'footer-col-1' => esc_html__( 'Footer Column 1', 'promptless' ),
                'footer-col-2' => esc_html__( 'Footer Column 2', 'promptless' ),
                'footer-col-3' => esc_html__( 'Footer Column 3', 'promptless' ),
                'topbar-left'  => esc_html__( 'Top Bar Left', 'promptless' ),
                'topbar-right' => esc_html__( 'Top Bar Right', 'promptless' ),
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
        add_editor_style( 'assets/css/editor-style.css' );

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
                'name'          => esc_html__( 'Footer Widgets', 'promptless' ),
                'id'            => 'footer-widgets',
                'description'   => esc_html__( 'Add widgets here to appear in your footer.', 'promptless' ),
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
                    'name'          => esc_html__( 'Shop Sidebar', 'promptless' ),
                    'id'            => 'shop-sidebar',
                    'description'   => esc_html__( 'Add widgets here to appear in the shop sidebar.', 'promptless' ),
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
                'name'          => esc_html__( 'Footer Social Links', 'promptless' ),
                'id'            => 'footer-social',
                'description'   => esc_html__( 'Add social link widgets here.', 'promptless' ),
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

    /**
     * Customize sale flash badge text
     *
     * Changes "Sale!" to "Sale" to match Product Grid section styling.
     *
     * @since 1.0.0
     * @param string $html    Sale flash HTML.
     * @param object $post    Post object.
     * @param object $product Product object.
     * @return string Modified sale flash HTML.
     */
    public function custom_sale_flash( $html, $post, $product ) {
        return '<span class="onsale">' . esc_html__( 'Sale', 'promptless' ) . '</span>';
    }

    /**
     * Filter the custom logo image attributes: accessibility + right-sizing.
     *
     * Two jobs:
     *  1. Accessibility — adds role="img" for better SVG logo support.
     *  2. Performance — corrects the `sizes` attribute. WordPress derives the
     *     logo's `sizes` from the SOURCE image width (e.g. a 1100px logo →
     *     `(max-width: 1100px) 100vw, 1100px`), which tells the browser the
     *     logo is full-viewport-width. The header logo actually renders tiny
     *     (capped at the registered custom-logo height, ~40–60px tall), so the
     *     browser was downloading the largest srcset candidate — e.g. a 1100w
     *     source (~540 KiB) for a 40px logo, which PageSpeed flags under
     *     "Improve image delivery". We replace `sizes` with the logo's real
     *     rendered slot width, computed from the registered logo height and the
     *     attachment's aspect ratio so it's correct for square AND wide
     *     wordmark logos (no upscaling/blur). The browser then picks a small
     *     srcset candidate instead of the full-resolution file.
     *
     * @since 1.0.0
     * @param array $custom_logo_attr Custom logo image attributes.
     * @param int   $custom_logo_id   Custom logo attachment ID.
     * @param int   $blog_id          ID of the blog to get the custom logo for.
     * @return array Modified attributes.
     */
    public function enhance_logo_accessibility( $custom_logo_attr, $custom_logo_id, $blog_id ) {
        // Add role="img" for SVG logos and general accessibility.
        $custom_logo_attr['role'] = 'img';

        // Never touch SVG logos. They're vector — infinitely crisp at any size,
        // with no raster srcset — so there is nothing to "right-size" and no
        // quality to lose. Leave their markup exactly as WordPress emitted it.
        if ( 'image/svg+xml' === get_post_mime_type( $custom_logo_id ) ) {
            return $custom_logo_attr;
        }

        // Right-size the `sizes` attribute so the browser fetches a logo-sized
        // srcset candidate rather than the full-resolution source. This changes
        // only WHICH candidate is chosen — never the pixels — and errs toward a
        // slightly larger slot so the logo never upscales or blurs (the browser
        // still fetches a 2× candidate on retina displays). Skipped when there
        // is no pixel metadata (defensive; SVGs already returned above).
        $meta = wp_get_attachment_metadata( $custom_logo_id );
        if ( is_array( $meta ) && ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
            $logo_support = get_theme_support( 'custom-logo' );
            $max_height   = ( is_array( $logo_support ) && ! empty( $logo_support[0]['height'] ) )
                ? (int) $logo_support[0]['height']
                : 60;

            // Rendered width = capped height × aspect ratio; clamp to sane
            // bounds so an unusual logo can't produce a degenerate value.
            $slot = (int) ceil( $max_height * ( (int) $meta['width'] / (int) $meta['height'] ) );
            $slot = max( 32, min( $slot, 400 ) );

            $custom_logo_attr['sizes'] = $slot . 'px';
        }

        return $custom_logo_attr;
    }
}
