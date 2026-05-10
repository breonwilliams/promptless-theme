<?php
/**
 * Promptless WP — Full Width Template
 *
 * Preserves the theme's header and footer chrome but renders sections
 * in full viewport width inside a single main element. Use this when
 * you want plugin sections paired with the site's normal navigation
 * and footer.
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

get_header();
?>

<main id="main-content" class="site-main promptless-fullwidth">
    <div id="aisb-fullwidth-wrapper" class="aisb-fullwidth-wrapper">
        <?php Promptless_Plugin_Bridge::render_sections( get_the_ID(), __( 'Full Width Mode', 'promptless' ) ); ?>
    </div>
</main>

<?php
get_footer();
