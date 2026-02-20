<?php
/**
 * Plugin Integration Class
 *
 * Handles coordination with the Promptless WP plugin.
 *
 * When the plugin is active, it handles ALL CSS variable output site-wide
 * (via `enqueue_global_css_variables()` for native themes).
 *
 * This class only outputs CSS variables as a fallback when the plugin is NOT active,
 * ensuring the theme degrades gracefully.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Promptless_Integration
 */
class Promptless_Integration {

    /**
     * Constructor
     */
    public function __construct() {
        // Register theme support for native theme integration
        // This tells the plugin to output CSS variables on ALL pages (not just section pages)
        add_action( 'after_setup_theme', array( $this, 'register_theme_support' ) );

        // Output CSS variables only as fallback when plugin is NOT active
        add_action( 'wp_head', array( $this, 'output_css_variables' ), 5 );

        add_filter( 'body_class', array( $this, 'add_body_classes' ) );
    }

    /**
     * Register theme support for native plugin integration
     *
     * This signals to the Promptless WP plugin that this theme needs
     * CSS variables output on ALL pages, not just pages with sections.
     */
    public function register_theme_support() {
        add_theme_support( 'aisb-native-theme' );
    }

    /**
     * Check if the Promptless WP plugin is active
     *
     * @return bool
     */
    public function is_plugin_active() {
        return class_exists( 'AISB_Plugin' ) || defined( 'AISB_MODERN_VERSION' );
    }

    /**
     * Get global settings from the plugin
     *
     * @return array
     */
    public function get_global_settings() {
        if ( ! $this->is_plugin_active() ) {
            return $this->get_default_settings();
        }

        $settings = get_option( 'aisb_global_settings', array() );

        return wp_parse_args( $settings, $this->get_default_settings() );
    }

    /**
     * Get default settings (fallback when plugin is not active)
     *
     * These are minimal defaults to ensure the theme works when the plugin is deactivated.
     * The plugin provides 50+ CSS variables with WCAG-compliant calculations;
     * this fallback provides basic styling only.
     *
     * @return array
     */
    private function get_default_settings() {
        return array(
            // Colors
            'primary_color'       => '#6366f1',
            'secondary_color'     => '#8b5cf6',
            'text_color'          => '#1f2937',
            'background_color'    => '#ffffff',
            'surface_color'       => '#f9fafb',
            'border_color'        => '#e5e7eb',
            'muted_text_color'    => '#6b7280',

            // Dark mode colors
            'dark_background'     => '#111827',
            'dark_text'           => '#f9fafb',
            'dark_surface'        => '#1f2937',
            'dark_border'         => '#374151',
            'dark_muted_text'     => '#9ca3af',

            // Typography
            'heading_font'        => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif',
            'body_font'           => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif',

            // Border radius
            'button_radius'       => '6px',
            'card_radius'         => '8px',
            'image_radius'        => '8px',

            // Container
            'max_width'           => '1280px',
        );
    }

    /**
     * Output CSS variables for theme elements
     *
     * This is a FALLBACK ONLY - it runs when the plugin is NOT active.
     *
     * When the plugin IS active, it handles ALL CSS variable output via
     * `enqueue_global_css_variables()` which provides:
     * - 50+ CSS variables (vs ~15 here)
     * - Font weight variables
     * - Button/eyebrow/highlight fonts
     * - WCAG-compliant calculated colors (ghost buttons, links, icons)
     * - Google Font loading
     *
     * This fallback ensures graceful degradation when plugin is deactivated.
     */
    public function output_css_variables() {
        // If plugin is active, it handles ALL CSS variables site-wide for native themes
        // The plugin's enqueue_global_css_variables() runs on priority 5
        if ( $this->is_plugin_active() ) {
            return;
        }

        // Plugin is NOT active - output minimal fallback CSS variables
        $settings = $this->get_default_settings();

        ?>
        <style id="promptless-theme-css-variables-fallback">
            :root {
                /* Colors - Basic fallback when plugin is deactivated */
                --aisb-color-primary: <?php echo esc_attr( $settings['primary_color'] ); ?>;
                --aisb-color-secondary: <?php echo esc_attr( $settings['secondary_color'] ); ?>;
                --aisb-color-text: <?php echo esc_attr( $settings['text_color'] ); ?>;
                --aisb-color-background: <?php echo esc_attr( $settings['background_color'] ); ?>;
                --aisb-color-surface: <?php echo esc_attr( $settings['surface_color'] ); ?>;
                --aisb-color-border: <?php echo esc_attr( $settings['border_color'] ); ?>;
                --aisb-color-text-muted: <?php echo esc_attr( $settings['muted_text_color'] ); ?>;

                /* Dark mode colors */
                --aisb-color-dark-background: <?php echo esc_attr( $settings['dark_background'] ); ?>;
                --aisb-color-dark-text: <?php echo esc_attr( $settings['dark_text'] ); ?>;
                --aisb-color-dark-surface: <?php echo esc_attr( $settings['dark_surface'] ); ?>;
                --aisb-color-dark-border: <?php echo esc_attr( $settings['dark_border'] ); ?>;
                --aisb-color-dark-text-muted: <?php echo esc_attr( $settings['dark_muted_text'] ); ?>;

                /* Typography - System fonts as fallback */
                --aisb-section-font-heading: <?php echo $settings['heading_font']; ?>;
                --aisb-section-font-body: <?php echo $settings['body_font']; ?>;

                /* Border radius */
                --aisb-section-radius-button: <?php echo esc_attr( $settings['button_radius'] ); ?>;
                --aisb-section-radius-card: <?php echo esc_attr( $settings['card_radius'] ); ?>;
                --aisb-section-radius-image: <?php echo esc_attr( $settings['image_radius'] ); ?>;

                /* Container */
                --aisb-section-max-width: <?php echo esc_attr( $settings['max_width'] ); ?>;

                /* Basic fallbacks for calculated colors (plugin calculates WCAG-compliant versions) */
                --aisb-button-primary-text: #ffffff;
                --aisb-button-primary-bg: <?php echo esc_attr( $settings['primary_color'] ); ?>;
                --aisb-ghost-button-color: <?php echo esc_attr( $settings['primary_color'] ); ?>;
                --aisb-link-color: <?php echo esc_attr( $settings['primary_color'] ); ?>;
                --aisb-color-text-inverse: #ffffff;
            }
        </style>
        <?php
    }

    /**
     * Add body classes for theme features
     *
     * @param array $classes Existing body classes.
     * @return array Modified body classes.
     */
    public function add_body_classes( $classes ) {
        // Add class when plugin is active
        if ( $this->is_plugin_active() ) {
            $classes[] = 'promptless-plugin-active';
        }

        // Add class for native theme
        $classes[] = 'promptless-theme';

        return $classes;
    }
}
