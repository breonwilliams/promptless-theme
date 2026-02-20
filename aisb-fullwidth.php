<?php
/**
 * Promptless WP - Full Width Template
 *
 * This template preserves the theme's header and footer
 * but renders sections in full viewport width.
 *
 * Note: When this theme template exists, the plugin will use it
 * instead of its own template (via locate_template()).
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get theme header
get_header();

// Get sections data
$post_id      = get_the_ID();
$sections_raw = get_post_meta( $post_id, '_aisb_sections', true );

// Validate and decode sections with error handling
if ( empty( $sections_raw ) ) {
    $sections = array();
} elseif ( is_array( $sections_raw ) ) {
    // Already an array (from filter or direct storage)
    $sections = $sections_raw;
} elseif ( is_string( $sections_raw ) ) {
    // Decode JSON string with error handling
    $sections = json_decode( $sections_raw, true );

    // Check for JSON decode errors
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        error_log( 'AISB: JSON decode error for post ' . $post_id . ': ' . json_last_error_msg() );
        $sections = array();
    }
} else {
    // Unknown format - log and use empty array
    error_log( 'AISB: Unexpected sections data type for post ' . $post_id . ': ' . gettype( $sections_raw ) );
    $sections = array();
}

// Ensure sections is an array after all processing
if ( ! is_array( $sections ) ) {
    $sections = array();
}

// Apply filter to allow preview mode to override sections
$sections = apply_filters( 'aisb_get_sections', $sections, $post_id );
?>

<main id="main-content" class="site-main promptless-fullwidth">
    <div id="aisb-fullwidth-wrapper" class="aisb-fullwidth-wrapper">
        <?php
        if ( ! empty( $sections ) && is_array( $sections ) ) {
            try {
                $renderer = new \AISB\Modern\Core\SectionRenderer();

                // Render all sections (CSS will be collected internally)
                $sections_html = '';
                foreach ( $sections as $section_index => $section ) {
                    // Skip invalid section entries
                    if ( ! is_array( $section ) ) {
                        error_log( 'AISB: Invalid section at index ' . $section_index . ' for post ' . $post_id );
                        continue;
                    }
                    $sections_html .= $renderer->render_section( $section, $section_index, $post_id );
                }

                // Get collected CSS from all sections
                $collected_css = $renderer->get_collected_css();

                // Output consolidated CSS if any was collected
                if ( ! empty( $collected_css ) ) {
                    echo '<style id="aisb-custom-css-' . esc_attr( $post_id ) . '">';
                    echo $collected_css;
                    echo '</style>';
                }

                // Output the rendered sections
                echo $sections_html;

                // Clear collected CSS for next render
                $renderer->clear_collected_css();
            } catch ( Exception $e ) {
                // Log the error but don't break the page
                error_log( 'AISB: Section rendering error for post ' . $post_id . ': ' . $e->getMessage() );

                // Show error message only to admins
                if ( current_user_can( 'edit_posts' ) ) {
                    echo '<div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; margin: 20px; border-radius: 4px;">';
                    echo '<strong>Promptless WP Error:</strong> Unable to render sections. Please check the error logs.';
                    echo '</div>';
                }
            }
        } else {
            // Show placeholder for admins
            if ( current_user_can( 'edit_posts' ) ) {
                ?>
                <div style="padding: 60px 20px; text-align: center; background: var(--aisb-color-surface, #f5f5f5);">
                    <h2><?php esc_html_e( 'Promptless WP - Full Width Mode', 'promptless' ); ?></h2>
                    <p><?php esc_html_e( 'No sections added yet. Use the editor to add sections to this page.', 'promptless' ); ?></p>
                </div>
                <?php
            }
        }
        ?>
    </div>
</main>

<?php
// Get theme footer
get_footer();
