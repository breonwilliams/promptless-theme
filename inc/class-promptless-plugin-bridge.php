<?php
/**
 * Plugin Bridge
 *
 * Single chokepoint between the Promptless theme's templates and the
 * Promptless WP plugin's section-rendering API. Both `aisb-canvas.php` and
 * `aisb-fullwidth.php` previously instantiated `\AISB\Modern\Core\SectionRenderer`
 * directly with ~80 lines of identical surrounding glue (sections fetch,
 * JSON decode, render loop, CSS collection, error handling). When the
 * plugin's API evolves, every place doing this needed to be updated in
 * lockstep — easy to miss, easy to break.
 *
 * This class consolidates that glue so:
 *   1. Templates become tiny: a single `Promptless_Plugin_Bridge::render_sections($post_id)` call.
 *   2. Plugin compatibility checks (class_exists + minimum version) happen
 *      at one well-defined boundary instead of scattered across templates.
 *   3. When the plugin's render contract changes, ONE method needs updating.
 *   4. Admins get a clear, themed warning when the plugin is missing or
 *      out of date — instead of fatal `Class not found` errors.
 *
 * Architectural note: this is intentionally a static utility (no instance
 * state, called from template scope where instance injection would be
 * awkward). The class is namespaced via the `Promptless_` prefix to match
 * the rest of the theme's class set; if the theme migrates to PSR-4
 * autoloading later, this is one of the first classes worth namespacing
 * properly.
 *
 * @package Promptless_Theme
 * @since 1.1.5
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Promptless_Plugin_Bridge
 */
class Promptless_Plugin_Bridge {

    /**
     * Minimum plugin version this theme is known to be compatible with.
     *
     * Bump this whenever the theme starts depending on a plugin feature
     * added in a newer version. The compatibility check below will warn
     * admins if their installed plugin is older. Theme will still attempt
     * to render — the version gate is a soft signal, not a hard block,
     * because production sites should never see a blank page from a
     * theme/plugin version mismatch.
     *
     * Current value reflects the plugin version the per-section palette
     * smart-color fix shipped in (1.3.2). Any theme code that depends on
     * the v3+ smart-color emission contract should keep this current.
     *
     * @var string
     */
    const MIN_PLUGIN_VERSION = '1.3.0';

    /**
     * Wire up admin-side hooks. Called once from `Promptless_Setup` (or
     * directly from `functions.php` if Setup isn't responsible for it).
     *
     * @return void
     */
    public static function init() {
        add_action( 'admin_notices', array( __CLASS__, 'render_admin_compat_notice' ) );
    }

    /**
     * Read sections from a post's `_aisb_sections` post-meta.
     *
     * Handles the three legitimate storage shapes (already-decoded array,
     * JSON string, empty/missing) with defensive fallbacks. Applies the
     * `aisb_get_sections` filter so the plugin's preview mode can override
     * what's returned without the theme caring.
     *
     * @param int $post_id The post ID to read sections from.
     * @return array Array of section dicts, or empty array on any error.
     */
    public static function get_sections( $post_id ) {
        $sections_raw = get_post_meta( $post_id, '_aisb_sections', true );

        if ( empty( $sections_raw ) ) {
            $sections = array();
        } elseif ( is_array( $sections_raw ) ) {
            // Already an array (from filter or direct storage).
            $sections = $sections_raw;
        } elseif ( is_string( $sections_raw ) ) {
            $sections = json_decode( $sections_raw, true );

            // Treat any JSON parse failure as "no sections" — never bubble
            // a half-decoded structure into the renderer.
            if ( JSON_ERROR_NONE !== json_last_error() ) {
                $sections = array();
            }
        } else {
            // Unknown storage shape — defensive empty.
            $sections = array();
        }

        if ( ! is_array( $sections ) ) {
            $sections = array();
        }

        // Apply plugin's filter so preview mode can substitute its in-progress draft.
        return apply_filters( 'aisb_get_sections', $sections, $post_id );
    }

    /**
     * Is the plugin both LOADED and at a compatible version?
     *
     * Returns true only if the SectionRenderer class is loaded AND the
     * plugin reports a version >= MIN_PLUGIN_VERSION. If the plugin
     * doesn't define AISB_MODERN_VERSION (very old install), we still
     * return true if the class exists — better to attempt render than
     * to blank out. The render path's try/catch will catch any actual
     * incompatibility at runtime.
     *
     * @return bool
     */
    public static function is_plugin_compatible() {
        if ( ! class_exists( '\\AISB\\Modern\\Core\\SectionRenderer' ) ) {
            return false;
        }
        if ( ! defined( 'AISB_MODERN_VERSION' ) ) {
            // Plugin is loaded (class exists) but doesn't declare a
            // version. Trust the class and proceed.
            return true;
        }
        return version_compare( AISB_MODERN_VERSION, self::MIN_PLUGIN_VERSION, '>=' );
    }

    /**
     * Render all sections for a post.
     *
     * Consolidates the render loop, CSS collection, and admin error
     * surfacing that previously lived in every template. Returns void
     * (echoes directly) because the templates were already echo-driven.
     *
     * Behavior matrix:
     *
     *   - Plugin not installed/active → admin gets a placeholder, public sees nothing
     *   - Plugin too old → admin gets compatibility warning + placeholder, public sees nothing
     *   - Plugin OK, no sections → admin gets a placeholder, public sees empty wrapper
     *   - Plugin OK, sections render fine → output sections + collected inline CSS
     *   - Plugin OK, render throws → admin gets error notice, public sees nothing
     *
     * @param int    $post_id        The post ID to render.
     * @param string $template_label Human-readable template name for admin
     *                               placeholders/errors (e.g. "Canvas Mode").
     * @param array|null $wrapper    Optional layout wrapper this method owns:
     *                               [ 'id' => ..., 'class' => ... ]. When set,
     *                               the collected <style> is emitted OUTSIDE
     *                               this wrapper so the first section stays its
     *                               :first-child (the chrome-offset contract).
     *                               When null, the caller supplies its own
     *                               wrapper and behaviour is unchanged.
     * @return void
     */
    public static function render_sections( $post_id, $template_label = '', $wrapper = null ) {
        // The collected <style> must NOT be a child of the layout wrapper:
        // the plugin's chrome-offset overlay-clearance rules target
        // `.aisb-fullwidth-wrapper > .aisb-section:first-child`, and a leading
        // <style> child silently defeats them (founder-reported floating-header
        // overlap on pages with no breadcrumbs, 2026-08). So buffer the inner
        // content here, then emit <style> BEFORE the optional wrapper below.
        $collected_css = '';

        ob_start();

        if ( ! self::is_plugin_compatible() ) {
            self::render_admin_only_placeholder(
                $template_label,
                self::compat_message()
            );
        } else {
            $sections = self::get_sections( $post_id );

            if ( empty( $sections ) ) {
                self::render_admin_only_placeholder(
                    $template_label,
                    __( 'No sections added yet. Use the editor to add sections to this page.', 'promptless' )
                );
            } else {
                try {
                    // Type-asserted because is_plugin_compatible() guarantees the class.
                    $renderer = new \AISB\Modern\Core\SectionRenderer();

                    $sections_html = '';
                    foreach ( $sections as $section_index => $section ) {
                        if ( ! is_array( $section ) ) {
                            continue;
                        }
                        $sections_html .= $renderer->render_section( $section, $section_index, $post_id );
                    }

                    $collected_css = $renderer->get_collected_css();

                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- internally generated HTML by trusted renderer
                    echo $sections_html;

                    $renderer->clear_collected_css();
                } catch ( \Throwable $e ) {
                    // \Throwable catches both Exception and Error — covers plugin
                    // contract changes that produce class-not-found errors at
                    // runtime as well as ordinary exceptions from the renderer.
                    self::render_admin_only_error( $e );
                }
            }
        }

        $inner = ob_get_clean();

        // Collected CSS FIRST — ahead of the sections it styles (so there is no
        // first-paint colour flash) yet OUTSIDE the wrapper (see note above).
        // CSS is generated by the plugin's SectionRenderer and already
        // sanitized internally; the id attribute IS escaped.
        if ( ! empty( $collected_css ) ) {
            echo '<style id="aisb-custom-css-' . esc_attr( (string) $post_id ) . '">';
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- internally generated CSS
            echo $collected_css;
            echo '</style>';
        }

        if ( is_array( $wrapper ) ) {
            $id_attr    = ! empty( $wrapper['id'] ) ? ' id="' . esc_attr( $wrapper['id'] ) . '"' : '';
            $class_attr = ! empty( $wrapper['class'] ) ? esc_attr( $wrapper['class'] ) : '';
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attrs escaped above; $inner is trusted rendered HTML
            echo '<div' . $id_attr . ' class="' . $class_attr . '">' . $inner . '</div>';
        } else {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted rendered HTML
            echo $inner;
        }
    }

    /**
     * Show admin notice when the plugin is missing or older than required.
     *
     * Hook target for `admin_notices`. Only fires when the gap is real.
     * Stays silent on the public site — the render path already shows
     * a separate placeholder there.
     *
     * @return void
     */
    public static function render_admin_compat_notice() {
        if ( self::is_plugin_compatible() ) {
            return;
        }
        ?>
        <div class="notice notice-warning">
            <p>
                <strong>Promptless Theme:</strong>
                <?php echo esc_html( self::compat_message() ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Build the human-readable compatibility status message.
     *
     * Surfaced in two places: the admin notice (always visible to admins)
     * and the in-page admin-only placeholder on AISB-template pages
     * (so editors see what's wrong on the page they were trying to edit).
     *
     * @return string Translated, plain-text message safe for esc_html.
     */
    private static function compat_message() {
        if ( ! class_exists( '\\AISB\\Modern\\Core\\SectionRenderer' ) ) {
            return __(
                'The Promptless WP plugin is not active. Sections will not render until you activate it.',
                'promptless'
            );
        }
        if ( defined( 'AISB_MODERN_VERSION' ) ) {
            return sprintf(
                /* translators: 1: installed plugin version, 2: minimum required version */
                __(
                    'The Promptless WP plugin (version %1$s) is older than this theme requires (%2$s). Please update the plugin.',
                    'promptless'
                ),
                AISB_MODERN_VERSION,
                self::MIN_PLUGIN_VERSION
            );
        }
        return __(
            'The Promptless WP plugin is loaded but does not report a version. Please update to a current release.',
            'promptless'
        );
    }

    /**
     * Output a centered admin-only placeholder ("you, the editor, are
     * seeing this because there's nothing to render").
     *
     * Inline styles intentionally minimal (Wave 2 will move these into a
     * proper CSS class). The placeholder is admin-only because public
     * visitors should see an empty page if no sections exist — they
     * shouldn't see the editor's status messages.
     *
     * @param string $template_label e.g. "Canvas Mode" or "Full Width Mode".
     * @param string $message        Translated message body.
     * @return void
     */
    private static function render_admin_only_placeholder( $template_label, $message ) {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return;
        }
        $heading = $template_label
            ? sprintf( /* translators: %s: template name */ __( 'Promptless WP — %s', 'promptless' ), $template_label )
            : __( 'Promptless WP', 'promptless' );
        ?>
        <div class="aisb-template-placeholder" style="padding: 60px 20px; text-align: center; background: var(--aisb-color-surface, #f5f5f5);">
            <h2><?php echo esc_html( $heading ); ?></h2>
            <p><?php echo esc_html( $message ); ?></p>
        </div>
        <?php
    }

    /**
     * Output an admin-only error notice when the renderer threw.
     *
     * Logs the exception details to error_log (for the developer/host)
     * but only surfaces a generic message to the admin (so the page UI
     * doesn't expose internals). Public visitors see nothing — the
     * page's other content (header/footer in fullwidth template) still
     * renders, just without the sections.
     *
     * @param \Throwable $e
     * @return void
     */
    private static function render_admin_only_error( $e ) {
        // Always log for the developer, regardless of who's viewing.
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- gated by WP_DEBUG
            error_log( 'Promptless theme: SectionRenderer threw — ' . $e->getMessage() );
        }

        if ( ! current_user_can( 'edit_posts' ) ) {
            return;
        }
        ?>
        <div class="aisb-template-error notice notice-warning" style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; margin: 20px; border-radius: 4px;">
            <strong><?php esc_html_e( 'Promptless WP Error:', 'promptless' ); ?></strong>
            <?php esc_html_e( 'Unable to render sections. Please check the error logs.', 'promptless' ); ?>
        </div>
        <?php
    }
}

// Wire the admin compatibility notice. Fires once at file load — class
// methods are idempotent so duplicate hook registration is harmless.
Promptless_Plugin_Bridge::init();
