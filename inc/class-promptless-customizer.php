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
        // Panels
        // =============================================

        // Header Panel
        $wp_customize->add_panel(
            'promptless_header_panel',
            array(
                'title'    => __( 'Header', 'promptless-theme' ),
                'priority' => 30,
            )
        );

        // Top Bar Panel
        $wp_customize->add_panel(
            'promptless_topbar_panel',
            array(
                'title'    => __( 'Top Bar', 'promptless-theme' ),
                'priority' => 31,
            )
        );

        // Footer Panel
        $wp_customize->add_panel(
            'promptless_footer_panel',
            array(
                'title'    => __( 'Footer', 'promptless-theme' ),
                'priority' => 33,
            )
        );

        // =============================================
        // Sections
        // =============================================

        // Header Layout Section (first in Header panel)
        $wp_customize->add_section(
            'promptless_header_layout_section',
            array(
                'title'    => __( 'Header Layout', 'promptless-theme' ),
                'panel'    => 'promptless_header_panel',
                'priority' => 5,
            )
        );

        // Header Appearance Section
        $wp_customize->add_section(
            'promptless_header_appearance',
            array(
                'title'    => __( 'Header Appearance', 'promptless-theme' ),
                'panel'    => 'promptless_header_panel',
                'priority' => 10,
            )
        );

        // Navigation Section
        $wp_customize->add_section(
            'promptless_header_nav',
            array(
                'title'    => __( 'Navigation', 'promptless-theme' ),
                'panel'    => 'promptless_header_panel',
                'priority' => 20,
            )
        );

        // Header CTA Section
        $wp_customize->add_section(
            'promptless_header_cta',
            array(
                'title'    => __( 'Header CTA', 'promptless-theme' ),
                'panel'    => 'promptless_header_panel',
                'priority' => 30,
            )
        );

        // Cart Section (WooCommerce only - added conditionally below)

        // Top Bar Settings Section
        $wp_customize->add_section(
            'promptless_topbar_settings',
            array(
                'title'    => __( 'Top Bar Settings', 'promptless-theme' ),
                'panel'    => 'promptless_topbar_panel',
                'priority' => 10,
            )
        );

        // Content Section (standalone, no panel)
        $wp_customize->add_section(
            'promptless_content_section',
            array(
                'title'    => __( 'Content', 'promptless-theme' ),
                'priority' => 32,
            )
        );

        // Footer Appearance Section
        $wp_customize->add_section(
            'promptless_footer_appearance',
            array(
                'title'    => __( 'Footer Appearance', 'promptless-theme' ),
                'panel'    => 'promptless_footer_panel',
                'priority' => 10,
            )
        );

        // Footer Columns Section
        $wp_customize->add_section(
            'promptless_footer_columns',
            array(
                'title'    => __( 'Footer Columns', 'promptless-theme' ),
                'panel'    => 'promptless_footer_panel',
                'priority' => 20,
            )
        );

        // =============================================
        // Header Layout Setting
        // =============================================
        $wp_customize->add_setting(
            'promptless_header_layout',
            array(
                'default'           => 'default',
                'sanitize_callback' => array( $this, 'sanitize_header_layout' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_header_layout',
            array(
                'label'       => __( 'Header Layout', 'promptless-theme' ),
                'description' => __( 'Choose the header layout style.', 'promptless-theme' ),
                'section'     => 'promptless_header_layout_section',
                'type'        => 'select',
                'choices'     => array(
                    'default' => __( 'Default (Single Row)', 'promptless-theme' ),
                    'stacked' => __( 'Stacked (Two Rows)', 'promptless-theme' ),
                ),
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
                'label'       => __( 'Header Theme', 'promptless-theme' ),
                'description' => __( 'Choose light or dark styling for the header. Colors are inherited from Promptless WP Global Settings.', 'promptless-theme' ),
                'section'     => 'promptless_header_appearance',
                'type'        => 'select',
                'choices'     => array(
                    'light' => __( 'Light', 'promptless-theme' ),
                    'dark'  => __( 'Dark', 'promptless-theme' ),
                ),
            )
        );

        // =============================================
        // Navigation Bar Theme (Stacked Layout Only)
        // =============================================
        $wp_customize->add_setting(
            'promptless_header_nav_theme',
            array(
                'default'           => '',
                'sanitize_callback' => array( $this, 'sanitize_nav_theme_variant' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_header_nav_theme',
            array(
                'label'           => __( 'Navigation Bar Theme', 'promptless-theme' ),
                'description'     => __( 'Choose light or dark styling for the navigation bar. Only applies to stacked layout.', 'promptless-theme' ),
                'section'         => 'promptless_header_appearance',
                'type'            => 'select',
                'choices'         => array(
                    ''      => __( 'Same as Header', 'promptless-theme' ),
                    'light' => __( 'Light', 'promptless-theme' ),
                    'dark'  => __( 'Dark', 'promptless-theme' ),
                ),
                'active_callback' => array( $this, 'is_stacked_layout' ),
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
                'label'       => __( 'Footer Theme', 'promptless-theme' ),
                'description' => __( 'Choose light or dark styling for the footer. Colors are inherited from Promptless WP Global Settings.', 'promptless-theme' ),
                'section'     => 'promptless_footer_appearance',
                'type'        => 'select',
                'choices'     => array(
                    'light' => __( 'Light', 'promptless-theme' ),
                    'dark'  => __( 'Dark', 'promptless-theme' ),
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
                'label'       => __( 'Content Theme', 'promptless-theme' ),
                'description' => __( 'Choose light or dark styling for page content areas.', 'promptless-theme' ),
                'section'     => 'promptless_content_section',
                'type'        => 'select',
                'choices'     => array(
                    'light' => __( 'Light', 'promptless-theme' ),
                    'dark'  => __( 'Dark', 'promptless-theme' ),
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
                'label'       => __( 'Header CTA Text', 'promptless-theme' ),
                'description' => __( 'Button text for the header call-to-action. Leave empty to hide.', 'promptless-theme' ),
                'section'     => 'promptless_header_cta',
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
                'label'       => __( 'Header CTA URL', 'promptless-theme' ),
                'description' => __( 'Link URL for the header call-to-action button.', 'promptless-theme' ),
                'section'     => 'promptless_header_cta',
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
                'label'       => __( 'Navigation Position', 'promptless-theme' ),
                'description' => __( 'Align the primary navigation menu within the header.', 'promptless-theme' ),
                'section'     => 'promptless_header_nav',
                'type'        => 'select',
                'choices'     => array(
                    'left'   => __( 'Left', 'promptless-theme' ),
                    'center' => __( 'Center', 'promptless-theme' ),
                    'right'  => __( 'Right', 'promptless-theme' ),
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
                'label'       => __( 'Show Header Border', 'promptless-theme' ),
                'description' => __( 'Display a bottom border on the header.', 'promptless-theme' ),
                'section'     => 'promptless_header_appearance',
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
                'label'       => __( 'Sticky Header', 'promptless-theme' ),
                'description' => __( 'Keep the header fixed at the top when scrolling.', 'promptless-theme' ),
                'section'     => 'promptless_header_appearance',
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
                'label'       => __( 'Enable Top Bar', 'promptless-theme' ),
                'description' => __( 'Display a utility bar above the header with left/right menus.', 'promptless-theme' ),
                'section'     => 'promptless_topbar_settings',
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
                'label'       => __( 'Top Bar Theme', 'promptless-theme' ),
                'description' => __( 'Choose light or dark styling for the top bar.', 'promptless-theme' ),
                'section'     => 'promptless_topbar_settings',
                'type'        => 'select',
                'choices'     => array(
                    'light' => __( 'Light', 'promptless-theme' ),
                    'dark'  => __( 'Dark', 'promptless-theme' ),
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
                'label'       => __( 'Sticky Top Bar', 'promptless-theme' ),
                'description' => __( 'Keep the top bar fixed when scrolling. Only works when header is also sticky.', 'promptless-theme' ),
                'section'     => 'promptless_topbar_settings',
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
                'label'       => __( 'Top Bar Mobile Behavior', 'promptless-theme' ),
                'description' => __( 'Choose how the top bar behaves on mobile devices.', 'promptless-theme' ),
                'section'     => 'promptless_topbar_settings',
                'type'        => 'select',
                'choices'     => array(
                    'hide'     => __( 'Hide on Mobile', 'promptless-theme' ),
                    'collapse' => __( 'Collapse into Hamburger Menu', 'promptless-theme' ),
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
                'label'       => __( 'Footer Brand Description', 'promptless-theme' ),
                'description' => __( 'Add text, contact info, or links below the logo. Supports basic HTML formatting (bold, italic, links). Leave empty to show site tagline.', 'promptless-theme' ),
                'section'     => 'promptless_footer_appearance',
                'type'        => 'textarea',
            )
        );

        // =============================================
        // Footer Column Headings
        // =============================================
        $wp_customize->add_setting(
            'promptless_footer_col_1_heading',
            array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_footer_col_1_heading',
            array(
                'label'       => __( 'Footer Column 1 Heading', 'promptless-theme' ),
                'description' => __( 'Optional heading above Footer Column 1 menu. Leave empty to hide.', 'promptless-theme' ),
                'section'     => 'promptless_footer_columns',
                'type'        => 'text',
            )
        );

        $wp_customize->add_setting(
            'promptless_footer_col_2_heading',
            array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_footer_col_2_heading',
            array(
                'label'       => __( 'Footer Column 2 Heading', 'promptless-theme' ),
                'description' => __( 'Optional heading above Footer Column 2 menu. Leave empty to hide.', 'promptless-theme' ),
                'section'     => 'promptless_footer_columns',
                'type'        => 'text',
            )
        );

        $wp_customize->add_setting(
            'promptless_footer_col_3_heading',
            array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_footer_col_3_heading',
            array(
                'label'       => __( 'Footer Column 3 Heading', 'promptless-theme' ),
                'description' => __( 'Optional heading above Footer Column 3 menu. Leave empty to hide.', 'promptless-theme' ),
                'section'     => 'promptless_footer_columns',
                'type'        => 'text',
            )
        );

        // =============================================
        // WooCommerce Header Cart Settings
        // Only show if WooCommerce is active
        // =============================================
        if ( class_exists( 'WooCommerce' ) ) {
            // Cart Section (WooCommerce only)
            $wp_customize->add_section(
                'promptless_header_cart',
                array(
                    'title'    => __( 'Cart', 'promptless-theme' ),
                    'panel'    => 'promptless_header_panel',
                    'priority' => 40,
                )
            );

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
                    'label'       => __( 'Show Cart Icon', 'promptless-theme' ),
                    'description' => __( 'Display a shopping cart icon in the header.', 'promptless-theme' ),
                    'section'     => 'promptless_header_cart',
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
                    'label'       => __( 'Cart Icon Behavior', 'promptless-theme' ),
                    'description' => __( 'Choose whether the cart icon opens a mini-cart dropdown or links directly to the cart page.', 'promptless-theme' ),
                    'section'     => 'promptless_header_cart',
                    'type'        => 'select',
                    'choices'     => array(
                        'dropdown' => __( 'Mini-Cart Dropdown', 'promptless-theme' ),
                        'link'     => __( 'Link to Cart Page', 'promptless-theme' ),
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

    /**
     * Sanitize header layout setting
     *
     * @param string $value Setting value.
     * @return string Sanitized value.
     */
    public function sanitize_header_layout( $value ) {
        $valid = array( 'default', 'stacked' );

        if ( in_array( $value, $valid, true ) ) {
            return $value;
        }

        return 'default';
    }

    /**
     * Check if stacked header layout is selected
     *
     * @param WP_Customize_Control $control Current control.
     * @return bool True if stacked layout is selected.
     */
    public function is_stacked_layout( $control ) {
        return 'stacked' === $control->manager->get_setting( 'promptless_header_layout' )->value();
    }

    /**
     * Sanitize navigation theme variant setting
     *
     * @param string $value Setting value.
     * @return string Sanitized value.
     */
    public function sanitize_nav_theme_variant( $value ) {
        $valid = array( '', 'light', 'dark' );

        if ( in_array( $value, $valid, true ) ) {
            return $value;
        }

        return '';
    }
}
