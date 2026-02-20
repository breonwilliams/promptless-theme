<?php
/**
 * Theme Customizer Class
 *
 * Handles Customizer settings for header and footer theme variants.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Promptless_Customizer
 */
class Promptless_Customizer {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'customize_register', array( $this, 'register_customizer_settings' ) );
    }

    /**
     * Register Customizer settings and controls
     *
     * @param WP_Customize_Manager $wp_customize Theme Customizer object.
     */
    public function register_customizer_settings( $wp_customize ) {

        // =============================================
        // Theme Settings Section
        // =============================================
        $wp_customize->add_section(
            'promptless_theme_settings',
            array(
                'title'    => __( 'Theme Settings', 'promptless' ),
                'priority' => 30,
            )
        );

        // =============================================
        // Header Theme Variant
        // =============================================
        $wp_customize->add_setting(
            'promptless_header_theme',
            array(
                'default'           => 'light',
                'sanitize_callback' => array( $this, 'sanitize_theme_variant' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_header_theme',
            array(
                'label'       => __( 'Header Theme', 'promptless' ),
                'description' => __( 'Choose light or dark styling for the header. Colors are inherited from Promptless WP Global Settings.', 'promptless' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'select',
                'choices'     => array(
                    'light' => __( 'Light', 'promptless' ),
                    'dark'  => __( 'Dark', 'promptless' ),
                ),
            )
        );

        // =============================================
        // Footer Theme Variant
        // =============================================
        $wp_customize->add_setting(
            'promptless_footer_theme',
            array(
                'default'           => 'light',
                'sanitize_callback' => array( $this, 'sanitize_theme_variant' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_footer_theme',
            array(
                'label'       => __( 'Footer Theme', 'promptless' ),
                'description' => __( 'Choose light or dark styling for the footer. Colors are inherited from Promptless WP Global Settings.', 'promptless' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'select',
                'choices'     => array(
                    'light' => __( 'Light', 'promptless' ),
                    'dark'  => __( 'Dark', 'promptless' ),
                ),
            )
        );

        // =============================================
        // Content Theme Variant
        // =============================================
        $wp_customize->add_setting(
            'promptless_content_theme',
            array(
                'default'           => 'light',
                'sanitize_callback' => array( $this, 'sanitize_theme_variant' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_content_theme',
            array(
                'label'       => __( 'Content Theme', 'promptless' ),
                'description' => __( 'Choose light or dark styling for page content areas.', 'promptless' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'select',
                'choices'     => array(
                    'light' => __( 'Light', 'promptless' ),
                    'dark'  => __( 'Dark', 'promptless' ),
                ),
            )
        );

        // =============================================
        // Header CTA Settings
        // =============================================
        $wp_customize->add_setting(
            'promptless_header_cta_text',
            array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_header_cta_text',
            array(
                'label'       => __( 'Header CTA Text', 'promptless' ),
                'description' => __( 'Button text for the header call-to-action. Leave empty to hide.', 'promptless' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'text',
            )
        );

        $wp_customize->add_setting(
            'promptless_header_cta_url',
            array(
                'default'           => '',
                'sanitize_callback' => 'esc_url_raw',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_header_cta_url',
            array(
                'label'       => __( 'Header CTA URL', 'promptless' ),
                'description' => __( 'Link URL for the header call-to-action button.', 'promptless' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'url',
            )
        );

        // =============================================
        // Navigation Position
        // =============================================
        $wp_customize->add_setting(
            'promptless_nav_position',
            array(
                'default'           => 'center',
                'sanitize_callback' => array( $this, 'sanitize_nav_position' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_nav_position',
            array(
                'label'       => __( 'Navigation Position', 'promptless' ),
                'description' => __( 'Align the primary navigation menu within the header.', 'promptless' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'select',
                'choices'     => array(
                    'left'   => __( 'Left', 'promptless' ),
                    'center' => __( 'Center', 'promptless' ),
                    'right'  => __( 'Right', 'promptless' ),
                ),
            )
        );

        // =============================================
        // Header Border
        // =============================================
        $wp_customize->add_setting(
            'promptless_header_border',
            array(
                'default'           => true,
                'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_header_border',
            array(
                'label'       => __( 'Show Header Border', 'promptless' ),
                'description' => __( 'Display a bottom border on the header.', 'promptless' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'checkbox',
            )
        );

        // =============================================
        // Sticky Header
        // =============================================
        $wp_customize->add_setting(
            'promptless_header_sticky',
            array(
                'default'           => true,
                'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_header_sticky',
            array(
                'label'       => __( 'Sticky Header', 'promptless' ),
                'description' => __( 'Keep the header fixed at the top when scrolling.', 'promptless' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'checkbox',
            )
        );

        // =============================================
        // Top Bar Settings
        // =============================================
        $wp_customize->add_setting(
            'promptless_topbar_enabled',
            array(
                'default'           => false,
                'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_topbar_enabled',
            array(
                'label'       => __( 'Enable Top Bar', 'promptless' ),
                'description' => __( 'Display a utility bar above the header with left/right menus.', 'promptless' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'checkbox',
            )
        );

        $wp_customize->add_setting(
            'promptless_topbar_theme',
            array(
                'default'           => 'dark',
                'sanitize_callback' => array( $this, 'sanitize_theme_variant' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_topbar_theme',
            array(
                'label'       => __( 'Top Bar Theme', 'promptless' ),
                'description' => __( 'Choose light or dark styling for the top bar.', 'promptless' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'select',
                'choices'     => array(
                    'light' => __( 'Light', 'promptless' ),
                    'dark'  => __( 'Dark', 'promptless' ),
                ),
            )
        );

        $wp_customize->add_setting(
            'promptless_topbar_sticky',
            array(
                'default'           => false,
                'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_topbar_sticky',
            array(
                'label'       => __( 'Sticky Top Bar', 'promptless' ),
                'description' => __( 'Keep the top bar fixed when scrolling. Only works when header is also sticky.', 'promptless' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'checkbox',
            )
        );

        $wp_customize->add_setting(
            'promptless_topbar_mobile',
            array(
                'default'           => 'hide',
                'sanitize_callback' => array( $this, 'sanitize_topbar_mobile' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_topbar_mobile',
            array(
                'label'       => __( 'Top Bar Mobile Behavior', 'promptless' ),
                'description' => __( 'Choose how the top bar behaves on mobile devices.', 'promptless' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'select',
                'choices'     => array(
                    'hide'     => __( 'Hide on Mobile', 'promptless' ),
                    'collapse' => __( 'Collapse into Hamburger Menu', 'promptless' ),
                ),
            )
        );

        // =============================================
        // Footer Brand Text (Rich Text Area)
        // =============================================
        $wp_customize->add_setting(
            'promptless_footer_brand_text',
            array(
                'default'           => '',
                'sanitize_callback' => 'wp_kses_post',
                'transport'         => 'postMessage',
            )
        );

        $wp_customize->add_control(
            'promptless_footer_brand_text',
            array(
                'label'       => __( 'Footer Brand Description', 'promptless' ),
                'description' => __( 'Add text, contact info, or links below the logo. Supports basic HTML formatting (bold, italic, links). Leave empty to show site tagline.', 'promptless' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'textarea',
            )
        );

        // =============================================
        // WooCommerce Header Cart Settings
        // Only show if WooCommerce is active
        // =============================================
        if ( class_exists( 'WooCommerce' ) ) {
            // Enable cart icon toggle
            $wp_customize->add_setting(
                'promptless_header_cart_enabled',
                array(
                    'default'           => false,
                    'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                    'transport'         => 'refresh',
                )
            );

            $wp_customize->add_control(
                'promptless_header_cart_enabled',
                array(
                    'label'       => __( 'Show Cart Icon', 'promptless' ),
                    'description' => __( 'Display a shopping cart icon in the header.', 'promptless' ),
                    'section'     => 'promptless_theme_settings',
                    'type'        => 'checkbox',
                )
            );

            // Cart behavior: link or dropdown
            $wp_customize->add_setting(
                'promptless_header_cart_style',
                array(
                    'default'           => 'dropdown',
                    'sanitize_callback' => array( $this, 'sanitize_cart_style' ),
                    'transport'         => 'refresh',
                )
            );

            $wp_customize->add_control(
                'promptless_header_cart_style',
                array(
                    'label'       => __( 'Cart Icon Behavior', 'promptless' ),
                    'description' => __( 'Choose whether the cart icon opens a mini-cart dropdown or links directly to the cart page.', 'promptless' ),
                    'section'     => 'promptless_theme_settings',
                    'type'        => 'select',
                    'choices'     => array(
                        'dropdown' => __( 'Mini-Cart Dropdown', 'promptless' ),
                        'link'     => __( 'Link to Cart Page', 'promptless' ),
                    ),
                )
            );
        }
    }

    /**
     * Sanitize theme variant setting
     *
     * @param string $value Setting value.
     * @return string Sanitized value.
     */
    public function sanitize_theme_variant( $value ) {
        $valid = array( 'light', 'dark' );

        if ( in_array( $value, $valid, true ) ) {
            return $value;
        }

        return 'light';
    }

    /**
     * Sanitize navigation position setting
     *
     * @param string $value Setting value.
     * @return string Sanitized value.
     */
    public function sanitize_nav_position( $value ) {
        $valid = array( 'left', 'center', 'right' );

        if ( in_array( $value, $valid, true ) ) {
            return $value;
        }

        return 'center';
    }

    /**
     * Sanitize checkbox setting
     *
     * @param mixed $value Setting value.
     * @return bool Sanitized value.
     */
    public function sanitize_checkbox( $value ) {
        return (bool) $value;
    }

    /**
     * Sanitize cart style setting
     *
     * @param string $value Setting value.
     * @return string Sanitized value.
     */
    public function sanitize_cart_style( $value ) {
        $valid = array( 'link', 'dropdown' );

        if ( in_array( $value, $valid, true ) ) {
            return $value;
        }

        return 'dropdown';
    }

    /**
     * Sanitize top bar mobile behavior setting
     *
     * @param string $value Setting value.
     * @return string Sanitized value.
     */
    public function sanitize_topbar_mobile( $value ) {
        $valid = array( 'hide', 'collapse' );

        if ( in_array( $value, $valid, true ) ) {
            return $value;
        }

        return 'hide';
    }
}
