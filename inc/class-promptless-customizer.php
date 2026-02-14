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
                'title'    => __( 'Theme Settings', 'promptless-theme' ),
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
                'label'       => __( 'Header Theme', 'promptless-theme' ),
                'description' => __( 'Choose light or dark styling for the header. Colors are inherited from Promptless WP Global Settings.', 'promptless-theme' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'select',
                'choices'     => array(
                    'light' => __( 'Light', 'promptless-theme' ),
                    'dark'  => __( 'Dark', 'promptless-theme' ),
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
                'label'       => __( 'Footer Theme', 'promptless-theme' ),
                'description' => __( 'Choose light or dark styling for the footer. Colors are inherited from Promptless WP Global Settings.', 'promptless-theme' ),
                'section'     => 'promptless_theme_settings',
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
                'section'     => 'promptless_theme_settings',
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
                'label'       => __( 'Header CTA URL', 'promptless-theme' ),
                'description' => __( 'Link URL for the header call-to-action button.', 'promptless-theme' ),
                'section'     => 'promptless_theme_settings',
                'type'        => 'url',
            )
        );
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
}
