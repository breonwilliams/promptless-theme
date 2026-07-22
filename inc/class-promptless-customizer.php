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
                'title'    => __( 'Header', 'promptless' ),
                'priority' => 30,
            )
        );

        // Top Bar Panel
        $wp_customize->add_panel(
            'promptless_topbar_panel',
            array(
                'title'    => __( 'Top Bar', 'promptless' ),
                'priority' => 31,
            )
        );

        // Announcement Bar Panel (Wave 6)
        //
        // The announcement bar is a marketing/promotional bar that renders at
        // the very top of every page, ABOVE the existing utility Top Bar and
        // ABOVE the Header. The two are independent: a site can run both
        // (announcement on top, utility nav below it), one, or neither.
        //
        // Schedule visibility is evaluated server-side against the site's
        // timezone so there's no flash-of-announcement-then-disappear.
        // Dismissal uses a content-hashed cookie key (sha1 of the message
        // HTML), so changing the message text invalidates all previously-set
        // dismiss cookies and visitors see the new announcement automatically.
        $wp_customize->add_panel(
            'promptless_announcement_panel',
            array(
                'title'    => __( 'Announcement Bar', 'promptless' ),
                'priority' => 32,
            )
        );

        // Footer Panel
        $wp_customize->add_panel(
            'promptless_footer_panel',
            array(
                'title'    => __( 'Footer', 'promptless' ),
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
                'title'    => __( 'Header Layout', 'promptless' ),
                'panel'    => 'promptless_header_panel',
                'priority' => 5,
            )
        );

        // Header Appearance Section
        $wp_customize->add_section(
            'promptless_header_appearance',
            array(
                'title'    => __( 'Header Appearance', 'promptless' ),
                'panel'    => 'promptless_header_panel',
                'priority' => 10,
            )
        );

        // Navigation Section
        $wp_customize->add_section(
            'promptless_header_nav',
            array(
                'title'    => __( 'Navigation', 'promptless' ),
                'panel'    => 'promptless_header_panel',
                'priority' => 20,
            )
        );

        // Header CTA Section
        $wp_customize->add_section(
            'promptless_header_cta',
            array(
                'title'    => __( 'Header CTA', 'promptless' ),
                'panel'    => 'promptless_header_panel',
                'priority' => 30,
            )
        );

        // Cart Section (WooCommerce only - added conditionally below)

        // Top Bar Settings Section
        $wp_customize->add_section(
            'promptless_topbar_settings',
            array(
                'title'    => __( 'Top Bar Settings', 'promptless' ),
                'panel'    => 'promptless_topbar_panel',
                'priority' => 10,
            )
        );

        // Announcement Bar Settings Section (Wave 6)
        $wp_customize->add_section(
            'promptless_announcement_settings',
            array(
                'title'    => __( 'Announcement Bar Settings', 'promptless' ),
                'panel'    => 'promptless_announcement_panel',
                'priority' => 10,
            )
        );

        // Content Section (standalone, no panel)
        $wp_customize->add_section(
            'promptless_content_section',
            array(
                'title'    => __( 'Content', 'promptless' ),
                'priority' => 32,
            )
        );

        // Breadcrumbs Section (standalone, no panel)
        $wp_customize->add_section(
            'promptless_breadcrumbs_section',
            array(
                'title'       => __( 'Breadcrumbs', 'promptless' ),
                'description' => __( 'A hierarchy trail rendered between the header and page content. WooCommerce shop and product pages keep WooCommerce\'s own breadcrumb.', 'promptless' ),
                'priority'    => 33,
            )
        );

        // Footer Appearance Section
        $wp_customize->add_section(
            'promptless_footer_appearance',
            array(
                'title'    => __( 'Footer Appearance', 'promptless' ),
                'panel'    => 'promptless_footer_panel',
                'priority' => 10,
            )
        );

        // Footer Columns Section
        $wp_customize->add_section(
            'promptless_footer_columns',
            array(
                'title'    => __( 'Footer Columns', 'promptless' ),
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
                'label'       => __( 'Header Layout', 'promptless' ),
                'description' => __( 'Choose the header layout style. Floating renders the single-row header as a rounded pill that detaches from the viewport edges.', 'promptless' ),
                'section'     => 'promptless_header_layout_section',
                'type'        => 'select',
                'choices'     => array(
                    'default'  => __( 'Default (Single Row)', 'promptless' ),
                    'stacked'  => __( 'Stacked (Two Rows)', 'promptless' ),
                    'floating' => __( 'Floating (Rounded Pill)', 'promptless' ),
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
                'label'       => __( 'Header Theme', 'promptless' ),
                'description' => __( 'Choose light or dark styling for the header. Colors are inherited from Promptless WP Global Settings.', 'promptless' ),
                'section'     => 'promptless_header_appearance',
                'type'        => 'select',
                'choices'     => array(
                    'light' => __( 'Light', 'promptless' ),
                    'dark'  => __( 'Dark', 'promptless' ),
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
                'label'           => __( 'Navigation Bar Theme', 'promptless' ),
                'description'     => __( 'Choose light or dark styling for the navigation bar. Only applies to stacked layout.', 'promptless' ),
                'section'         => 'promptless_header_appearance',
                'type'            => 'select',
                'choices'         => array(
                    ''      => __( 'Same as Header', 'promptless' ),
                    'light' => __( 'Light', 'promptless' ),
                    'dark'  => __( 'Dark', 'promptless' ),
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
                'label'       => __( 'Footer Theme', 'promptless' ),
                'description' => __( 'Choose light or dark styling for the footer. Colors are inherited from Promptless WP Global Settings.', 'promptless' ),
                'section'     => 'promptless_footer_appearance',
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
                'section'     => 'promptless_content_section',
                'type'        => 'select',
                'choices'     => array(
                    'light' => __( 'Light', 'promptless' ),
                    'dark'  => __( 'Dark', 'promptless' ),
                ),
            )
        );

        // =============================================
        // Breadcrumbs Settings
        //
        // Design contract: docs/BREADCRUMBS_DESIGN_EXPLORATION.md.
        // Master toggle defaults OFF — new chrome appearing after a theme
        // update on a live site would violate the "never surprise live
        // sites" principle. Everything else defaults to the most useful
        // state so enabling is a one-click decision.
        // =============================================
        $wp_customize->add_setting(
            'promptless_breadcrumbs_enabled',
            array(
                'default'           => false,
                'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_breadcrumbs_enabled',
            array(
                'label'       => __( 'Enable Breadcrumbs', 'promptless' ),
                'description' => __( 'Show a breadcrumb trail after the site header. Never shown on the front page.', 'promptless' ),
                'section'     => 'promptless_breadcrumbs_section',
                'type'        => 'checkbox',
            )
        );

        $wp_customize->add_setting(
            'promptless_breadcrumbs_theme',
            array(
                'default'           => 'inherit',
                'sanitize_callback' => array( $this, 'sanitize_breadcrumbs_theme' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_breadcrumbs_theme',
            array(
                'label'           => __( 'Breadcrumbs Theme', 'promptless' ),
                'description'     => __( 'Inherit follows the Content Theme setting. Colors come from Promptless WP Global Settings.', 'promptless' ),
                'section'         => 'promptless_breadcrumbs_section',
                'type'            => 'select',
                'choices'         => array(
                    'inherit' => __( 'Inherit (Content Theme)', 'promptless' ),
                    'light'   => __( 'Light', 'promptless' ),
                    'dark'    => __( 'Dark', 'promptless' ),
                ),
                'active_callback' => array( $this, 'is_breadcrumbs_enabled' ),
            )
        );

        $wp_customize->add_setting(
            'promptless_breadcrumbs_home_label',
            array(
                'default'           => __( 'Home', 'promptless' ),
                'sanitize_callback' => 'sanitize_text_field',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_breadcrumbs_home_label',
            array(
                'label'           => __( 'Home Label', 'promptless' ),
                'section'         => 'promptless_breadcrumbs_section',
                'type'            => 'text',
                'active_callback' => array( $this, 'is_breadcrumbs_enabled' ),
            )
        );

        // Per-context toggles. One theme_mod per context so the connector
        // surface (a future wordpress_breadcrumbs_get/set) can read/write
        // them individually with no packing format.
        $breadcrumb_contexts = array(
            'promptless_breadcrumbs_on_pages'       => __( 'Show on pages', 'promptless' ),
            'promptless_breadcrumbs_on_posts'       => __( 'Show on blog posts', 'promptless' ),
            'promptless_breadcrumbs_on_cpt_singles' => __( 'Show on custom post type singles', 'promptless' ),
            'promptless_breadcrumbs_on_archives'    => __( 'Show on archives (blog, categories, custom post types)', 'promptless' ),
            'promptless_breadcrumbs_on_search'      => __( 'Show on search results', 'promptless' ),
            'promptless_breadcrumbs_on_404'         => __( 'Show on the 404 page', 'promptless' ),
        );

        foreach ( $breadcrumb_contexts as $setting_id => $label ) {
            $wp_customize->add_setting(
                $setting_id,
                array(
                    'default'           => true,
                    'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                    'transport'         => 'refresh',
                )
            );

            $wp_customize->add_control(
                $setting_id,
                array(
                    'label'           => $label,
                    'section'         => 'promptless_breadcrumbs_section',
                    'type'            => 'checkbox',
                    'active_callback' => array( $this, 'is_breadcrumbs_enabled' ),
                )
            );
        }

        $wp_customize->add_setting(
            'promptless_breadcrumbs_show_category',
            array(
                'default'           => true,
                'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_breadcrumbs_show_category',
            array(
                'label'           => __( 'Include category in blog post trails', 'promptless' ),
                'description'     => __( 'Home > Blog > Category > Post. The default "Uncategorized" bucket is always skipped.', 'promptless' ),
                'section'         => 'promptless_breadcrumbs_section',
                'type'            => 'checkbox',
                'active_callback' => array( $this, 'is_breadcrumbs_enabled' ),
            )
        );

        $wp_customize->add_setting(
            'promptless_breadcrumbs_schema',
            array(
                'default'           => true,
                'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_breadcrumbs_schema',
            array(
                'label'           => __( 'Output BreadcrumbList structured data', 'promptless' ),
                'description'     => __( 'Helps search engines understand your site hierarchy. Automatically disabled when an SEO plugin (Yoast, Rank Math, etc.) is active, since those output their own.', 'promptless' ),
                'section'         => 'promptless_breadcrumbs_section',
                'type'            => 'checkbox',
                'active_callback' => array( $this, 'is_breadcrumbs_enabled' ),
            )
        );

        // =============================================
        // Header CTA Settings (up to two buttons)
        //
        // Buttons are independent: a button renders only when BOTH its text
        // and URL are filled. Each button picks a style (primary / secondary /
        // ghost) that maps to the Promptless WP button classes, so colors are
        // inherited from the plugin's Global Settings. Each button also picks
        // a mobile placement (header bar vs. mobile menu). Defaults preserve
        // the prior single-primary-button behavior for existing sites.
        // =============================================
        $cta_style_choices     = array(
            'primary'   => __( 'Primary', 'promptless' ),
            'secondary' => __( 'Secondary', 'promptless' ),
            'ghost'     => __( 'Ghost / Outline', 'promptless' ),
        );
        $cta_placement_choices = array(
            'bar'  => __( 'Keep in header bar', 'promptless' ),
            'menu' => __( 'Move into mobile menu', 'promptless' ),
        );

        // ---- Button 1 (existing keys preserved for backward compatibility) ----
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
                'label'       => __( 'Button 1 Text', 'promptless' ),
                'description' => __( 'Button text for the primary header call-to-action. Leave empty to hide.', 'promptless' ),
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
                'label'       => __( 'Button 1 URL', 'promptless' ),
                'description' => __( 'Link URL for button 1.', 'promptless' ),
                'section'     => 'promptless_header_cta',
                'type'        => 'url',
            )
        );

        $wp_customize->add_setting(
            'promptless_header_cta_style',
            array(
                'default'           => 'primary',
                'sanitize_callback' => array( $this, 'sanitize_cta_style' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_header_cta_style',
            array(
                'label'       => __( 'Button 1 Style', 'promptless' ),
                'description' => __( 'Colors are inherited from Promptless WP Global Settings.', 'promptless' ),
                'section'     => 'promptless_header_cta',
                'type'        => 'select',
                'choices'     => $cta_style_choices,
            )
        );

        $wp_customize->add_setting(
            'promptless_header_cta_mobile_placement',
            array(
                'default'           => 'bar',
                'sanitize_callback' => array( $this, 'sanitize_cta_mobile_placement' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_header_cta_mobile_placement',
            array(
                'label'       => __( 'Button 1 on Mobile', 'promptless' ),
                'description' => __( 'On phones, keep this button next to the menu icon or move it into the mobile menu. To avoid crowding, only one button can stay in the header bar on mobile; if both are set to stay, button 2 moves into the menu.', 'promptless' ),
                'section'     => 'promptless_header_cta',
                'type'        => 'select',
                'choices'     => $cta_placement_choices,
            )
        );

        // ---- Button 2 (optional second button) ----
        $wp_customize->add_setting(
            'promptless_header_cta_2_text',
            array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_header_cta_2_text',
            array(
                'label'       => __( 'Button 2 Text', 'promptless' ),
                'description' => __( 'Optional second button. Leave empty to use a single button.', 'promptless' ),
                'section'     => 'promptless_header_cta',
                'type'        => 'text',
            )
        );

        $wp_customize->add_setting(
            'promptless_header_cta_2_url',
            array(
                'default'           => '',
                'sanitize_callback' => 'esc_url_raw',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_header_cta_2_url',
            array(
                'label'       => __( 'Button 2 URL', 'promptless' ),
                'description' => __( 'Link URL for button 2.', 'promptless' ),
                'section'     => 'promptless_header_cta',
                'type'        => 'url',
            )
        );

        $wp_customize->add_setting(
            'promptless_header_cta_2_style',
            array(
                'default'           => 'secondary',
                'sanitize_callback' => array( $this, 'sanitize_cta_style' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_header_cta_2_style',
            array(
                'label'       => __( 'Button 2 Style', 'promptless' ),
                'description' => __( 'Colors are inherited from Promptless WP Global Settings.', 'promptless' ),
                'section'     => 'promptless_header_cta',
                'type'        => 'select',
                'choices'     => $cta_style_choices,
            )
        );

        $wp_customize->add_setting(
            'promptless_header_cta_2_mobile_placement',
            array(
                'default'           => 'menu',
                'sanitize_callback' => array( $this, 'sanitize_cta_mobile_placement' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_header_cta_2_mobile_placement',
            array(
                'label'       => __( 'Button 2 on Mobile', 'promptless' ),
                'description' => __( 'On phones, keep this button next to the menu icon or move it into the mobile menu.', 'promptless' ),
                'section'     => 'promptless_header_cta',
                'type'        => 'select',
                'choices'     => $cta_placement_choices,
            )
        );

        // ---- Position of buttons moved into the mobile menu ----
        $wp_customize->add_setting(
            'promptless_header_menu_cta_position',
            array(
                'default'           => 'bottom',
                'sanitize_callback' => array( $this, 'sanitize_cta_menu_position' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_header_menu_cta_position',
            array(
                'label'       => __( 'Mobile Menu Button Position', 'promptless' ),
                'description' => __( 'Where buttons moved into the mobile menu appear in the drawer. "Between top bar and menu" falls back to the top when there is no collapsed top bar. Only affects buttons set to "Move into mobile menu" above.', 'promptless' ),
                'section'     => 'promptless_header_cta',
                'type'        => 'select',
                'choices'     => array(
                    'top'    => __( 'Top of menu', 'promptless' ),
                    'middle' => __( 'Between top bar and menu', 'promptless' ),
                    'bottom' => __( 'Bottom of menu (default)', 'promptless' ),
                ),
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
                'section'     => 'promptless_header_nav',
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
                'label'       => __( 'Sticky Header', 'promptless' ),
                'description' => __( 'Keep the header fixed at the top when scrolling.', 'promptless' ),
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
                'label'       => __( 'Enable Top Bar', 'promptless' ),
                'description' => __( 'Display a utility bar above the header with left/right menus.', 'promptless' ),
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
                'label'       => __( 'Top Bar Theme', 'promptless' ),
                'description' => __( 'Choose light or dark styling for the top bar.', 'promptless' ),
                'section'     => 'promptless_topbar_settings',
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
                'section'     => 'promptless_topbar_settings',
                'type'        => 'checkbox',
            )
        );

        $wp_customize->add_setting(
            'promptless_topbar_mobile',
            array(
                // Default routes utility links into the hamburger drawer so
                // they stay reachable on small screens without competing for
                // header real estate. See promptless_get_topbar_mobile_behavior()
                // for the legacy 'hide' value migration.
                'default'           => 'collapse',
                'sanitize_callback' => array( $this, 'sanitize_topbar_mobile' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_topbar_mobile',
            array(
                'label'       => __( 'Top Bar Mobile Behavior', 'promptless' ),
                'description' => __( 'Choose how the top bar behaves on mobile devices.', 'promptless' ),
                'section'     => 'promptless_topbar_settings',
                'type'        => 'select',
                'choices'     => array(
                    // 'inline' keeps the bar at the top of the page on mobile,
                    // mirroring desktop. 'collapse' moves the utility links
                    // into the hamburger drawer. The legacy 'hide' option was
                    // removed because hiding important utility links by default
                    // is poor UX — users with 'hide' stored are migrated to
                    // 'collapse' at read time (see template-functions.php).
                    'inline'   => __( 'Always Show at Top', 'promptless' ),
                    'collapse' => __( 'Collapse into Hamburger Menu', 'promptless' ),
                ),
            )
        );

        // =============================================
        // Announcement Bar Settings (Wave 6)
        // =============================================
        //
        // Master enable toggle. Default false so a freshly-installed theme
        // doesn't surprise users with a placeholder banner — they have to
        // opt in by composing a real message and ticking the box.
        $wp_customize->add_setting(
            'promptless_announcement_enabled',
            array(
                'default'           => false,
                'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_announcement_enabled',
            array(
                'label'       => __( 'Enable Announcement Bar', 'promptless' ),
                'description' => __( 'Display a promotional bar above the Top Bar and Header. Use sparingly for sales, launches, holidays, or critical notices.', 'promptless' ),
                'section'     => 'promptless_announcement_settings',
                'type'        => 'checkbox',
            )
        );

        // Message HTML. wp_kses_post limits to safe inline tags; supports
        // [re:KEY] reusable element shortcodes resolved at render time
        // (same processor that handles section content).
        $wp_customize->add_setting(
            'promptless_announcement_message',
            array(
                'default'           => '',
                'sanitize_callback' => 'wp_kses_post',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_announcement_message',
            array(
                'label'       => __( 'Message', 'promptless' ),
                'description' => __( 'The announcement text. Basic HTML allowed (bold, italic, links). Supports [re:KEY] reusable element shortcodes — e.g. [re:phone_number].', 'promptless' ),
                'section'     => 'promptless_announcement_settings',
                'type'        => 'textarea',
            )
        );

        // Optional CTA button. Both fields are independent — empty CTA text
        // hides the button entirely; empty URL renders as plain bold text
        // for cases where the announcement is purely informational.
        $wp_customize->add_setting(
            'promptless_announcement_cta_text',
            array(
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_announcement_cta_text',
            array(
                'label'       => __( 'CTA Button Text', 'promptless' ),
                'description' => __( 'Optional. The button label. Leave empty to omit the button.', 'promptless' ),
                'section'     => 'promptless_announcement_settings',
                'type'        => 'text',
            )
        );

        $wp_customize->add_setting(
            'promptless_announcement_cta_url',
            array(
                'default'           => '',
                'sanitize_callback' => 'esc_url_raw',
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_announcement_cta_url',
            array(
                'label'       => __( 'CTA Button URL', 'promptless' ),
                'description' => __( 'Optional. Where the button links. Site-relative (/sale) or full URL (https://...).', 'promptless' ),
                'section'     => 'promptless_announcement_settings',
                'type'        => 'url',
            )
        );

        // Theme variant. Reuses the existing `aisb-section--light/dark`
        // modifier classes so the bar inherits the same color tokens the
        // editor and topbar already produce. Default 'dark' for visual
        // emphasis (announcements typically want to stand out).
        $wp_customize->add_setting(
            'promptless_announcement_theme',
            array(
                'default'           => 'dark',
                'sanitize_callback' => array( $this, 'sanitize_theme_variant' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_announcement_theme',
            array(
                'label'       => __( 'Theme', 'promptless' ),
                'description' => __( 'Light uses the site\'s background color; dark uses the dark-background token. Inherits all colors from the active palette.', 'promptless' ),
                'section'     => 'promptless_announcement_settings',
                'type'        => 'select',
                'choices'     => array(
                    'light' => __( 'Light', 'promptless' ),
                    'dark'  => __( 'Dark', 'promptless' ),
                ),
            )
        );

        // Dismissible toggle. Default true — most modern sites let visitors
        // close announcements after reading. Sites running critical notices
        // (e.g. service outage) can turn it off so the bar persists.
        $wp_customize->add_setting(
            'promptless_announcement_dismissible',
            array(
                'default'           => true,
                'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_announcement_dismissible',
            array(
                'label'       => __( 'Allow Visitors to Dismiss', 'promptless' ),
                'description' => __( 'Show a close button. Visitors who dismiss the bar won\'t see it again until the message text changes.', 'promptless' ),
                'section'     => 'promptless_announcement_settings',
                'type'        => 'checkbox',
            )
        );

        // Schedule fields. Both optional. Format: YYYY-MM-DDTHH:MM (HTML5
        // datetime-local). Evaluated server-side against the WP site
        // timezone so there's no flash-of-announcement-then-disappear and
        // no client-clock-skew bugs.
        //
        // - Both empty = always show (subject to enabled + dismissed).
        // - Only start_date = visible from that moment forward.
        // - Only end_date = visible until that moment.
        // - Both = visible only inside the window (start <= now <= end).
        $wp_customize->add_setting(
            'promptless_announcement_start_date',
            array(
                'default'           => '',
                'sanitize_callback' => array( $this, 'sanitize_announcement_datetime' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_announcement_start_date',
            array(
                'label'       => __( 'Start Date / Time', 'promptless' ),
                'description' => __( 'Optional. Bar starts showing at this moment (site timezone). Leave empty to start immediately.', 'promptless' ),
                'section'     => 'promptless_announcement_settings',
                'type'        => 'datetime-local',
            )
        );

        $wp_customize->add_setting(
            'promptless_announcement_end_date',
            array(
                'default'           => '',
                'sanitize_callback' => array( $this, 'sanitize_announcement_datetime' ),
                'transport'         => 'refresh',
            )
        );

        $wp_customize->add_control(
            'promptless_announcement_end_date',
            array(
                'label'       => __( 'End Date / Time', 'promptless' ),
                'description' => __( 'Optional. Bar stops showing at this moment (site timezone). Leave empty for no end date.', 'promptless' ),
                'section'     => 'promptless_announcement_settings',
                'type'        => 'datetime-local',
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
                'label'       => __( 'Footer Column 1 Heading', 'promptless' ),
                'description' => __( 'Optional heading above Footer Column 1 menu. Leave empty to hide.', 'promptless' ),
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
                'label'       => __( 'Footer Column 2 Heading', 'promptless' ),
                'description' => __( 'Optional heading above Footer Column 2 menu. Leave empty to hide.', 'promptless' ),
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
                'label'       => __( 'Footer Column 3 Heading', 'promptless' ),
                'description' => __( 'Optional heading above Footer Column 3 menu. Leave empty to hide.', 'promptless' ),
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
                    'title'    => __( 'Cart', 'promptless' ),
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
                    'label'       => __( 'Show Cart Icon', 'promptless' ),
                    'description' => __( 'Display a shopping cart icon in the header.', 'promptless' ),
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
                    'label'       => __( 'Cart Icon Behavior', 'promptless' ),
                    'description' => __( 'Choose whether the cart icon opens a mini-cart dropdown or links directly to the cart page.', 'promptless' ),
                    'section'     => 'promptless_header_cart',
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
     * Sanitize a header CTA button style.
     *
     * Maps to the Promptless WP button classes (aisb-btn-primary /
     * aisb-btn-secondary / aisb-btn-ghost).
     *
     * @param string $value Setting value.
     * @return string Sanitized value.
     */
    public function sanitize_cta_style( $value ) {
        $valid = array( 'primary', 'secondary', 'ghost' );

        if ( in_array( $value, $valid, true ) ) {
            return $value;
        }

        return 'primary';
    }

    /**
     * Sanitize a header CTA button mobile placement.
     *
     * @param string $value Setting value.
     * @return string Sanitized value ('bar' or 'menu').
     */
    public function sanitize_cta_mobile_placement( $value ) {
        $valid = array( 'bar', 'menu' );

        if ( in_array( $value, $valid, true ) ) {
            return $value;
        }

        return 'bar';
    }

    /**
     * Sanitize the mobile-menu CTA position setting.
     *
     * @param string $value Setting value.
     * @return string Sanitized value ('top', 'middle', or 'bottom').
     */
    public function sanitize_cta_menu_position( $value ) {
        $valid = array( 'top', 'middle', 'bottom' );

        if ( in_array( $value, $valid, true ) ) {
            return $value;
        }

        return 'bottom';
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
     * Sanitize the breadcrumbs theme variant setting.
     *
     * Unlike the shared theme-variant sanitizer, breadcrumbs support an
     * 'inherit' value that tracks the Content Theme setting.
     *
     * @param string $value Setting value.
     * @return string Sanitized value ('inherit', 'light', or 'dark').
     * @since 1.3.0
     */
    public function sanitize_breadcrumbs_theme( $value ) {
        $valid = array( 'inherit', 'light', 'dark' );

        if ( in_array( $value, $valid, true ) ) {
            return $value;
        }

        return 'inherit';
    }

    /**
     * Active callback: are breadcrumbs enabled?
     *
     * Hides the detail controls until the master toggle is on, keeping the
     * section scannable.
     *
     * @return bool
     * @since 1.3.0
     */
    public function is_breadcrumbs_enabled() {
        return (bool) get_theme_mod( 'promptless_breadcrumbs_enabled', false );
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
     * 'hide' was removed in 1.1.6 — sites with that value stored continue
     * to round-trip 'hide' through the option getter but are migrated to
     * 'collapse' at the call site (see promptless_get_topbar_mobile_behavior()).
     * The whitelist here is the new canonical set; anything outside it
     * (including the legacy 'hide') falls back to 'collapse'.
     *
     * @param string $value Setting value.
     * @return string Sanitized value.
     */
    public function sanitize_topbar_mobile( $value ) {
        $valid = array( 'inline', 'collapse' );

        if ( in_array( $value, $valid, true ) ) {
            return $value;
        }

        return 'collapse';
    }

    /**
     * Sanitize header layout setting
     *
     * @param string $value Setting value.
     * @return string Sanitized value.
     */
    public function sanitize_header_layout( $value ) {
        $valid = array( 'default', 'stacked', 'floating' );

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

    /**
     * Sanitize an announcement-bar datetime field.
     *
     * Accepts the HTML5 `datetime-local` format `YYYY-MM-DDTHH:MM` (with
     * optional `:SS` seconds). Anything else — including stray garbage,
     * non-numeric input, or impossible dates — is normalized to an empty
     * string, which the renderer treats as "no schedule set on this side".
     *
     * Stored as a literal string. The renderer parses it against the WP
     * site timezone at render time via promptless_announcement_in_schedule()
     * — see template-functions.php for the timezone-correct comparison.
     *
     * @param string $value Setting value (HTML5 datetime-local format).
     * @return string Sanitized value or empty string if invalid.
     */
    public function sanitize_announcement_datetime( $value ) {
        if ( ! is_string( $value ) || $value === '' ) {
            return '';
        }

        // Match YYYY-MM-DDTHH:MM with optional :SS. The trailing seconds are
        // accepted but not required because some browsers omit them in the
        // datetime-local input.
        if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(?::(\d{2}))?$/', $value, $matches ) ) {
            return '';
        }

        // Validate that the date/time components are real (e.g. not Feb 30).
        // checkdate() catches impossible calendar dates; the hour/minute/sec
        // bounds catch typos in the time portion.
        $year   = (int) $matches[1];
        $month  = (int) $matches[2];
        $day    = (int) $matches[3];
        $hour   = (int) $matches[4];
        $minute = (int) $matches[5];
        $second = isset( $matches[6] ) ? (int) $matches[6] : 0;

        if ( ! checkdate( $month, $day, $year ) ) {
            return '';
        }
        if ( $hour < 0 || $hour > 23 || $minute < 0 || $minute > 59 || $second < 0 || $second > 59 ) {
            return '';
        }

        return $value;
    }
}
