<?php
/**
 * Promptless WP — Canvas Template
 *
 * Provides a completely blank canvas for plugin sections. No theme header
 * or footer — sections render in a viewport-filling main element. Ideal
 * for landing pages, coming-soon pages, and anywhere chrome would
 * compete with the section design.
 *
 * Note: when this theme template exists, the plugin uses it instead of
 * its own template (via `locate_template()`).
 *
 * The PLUGIN INTEGRATION boundary (sections fetch + SectionRenderer
 * lifecycle + admin error handling + version compat checking) lives in
 * `Promptless_Plugin_Bridge`. This template just wires page chrome around
 * one bridge call so future plugin API changes touch one file, not every
 * template that renders sections.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'aisb-canvas aisb-template-canvas promptless-canvas' ); ?>>
    <?php wp_body_open(); ?>

    <a class="skip-link" href="#main-content">
        <?php esc_html_e( 'Skip to content', 'promptless' ); ?>
    </a>

    <main id="main-content" tabindex="-1" class="site-main promptless-canvas__content">
        <div id="aisb-canvas-wrapper" class="aisb-canvas-wrapper">
            <?php Promptless_Plugin_Bridge::render_sections( get_the_ID(), __( 'Canvas Mode', 'promptless' ) ); ?>
        </div>
    </main>

    <?php wp_footer(); ?>
</body>
</html>
