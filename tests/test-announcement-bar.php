<?php
/**
 * Promptless Theme — Announcement Bar Unit Tests (Wave 6)
 *
 * Standalone PHP test for the announcement-bar helper functions
 * (schedule evaluation, content-hash computation, cookie-name shape,
 * visibility decision flow). Mirrors the Promptless plugin's
 * test-design-optimizer.php pattern: zero WP runtime, zero database,
 * just inline mocks for the WP functions the helpers call.
 *
 * Run: php tests/test-announcement-bar.php
 *
 * @package Promptless_Theme
 * @since 1.2.0
 */

// ---------------------------------------------------------------------------
// Minimal WP runtime stubs (only what the helpers under test actually call)
// ---------------------------------------------------------------------------

define( 'ABSPATH', '/tmp/wordpress/' );

// In-memory theme_mod store. Tests reset this between runs via reset_test_state().
$GLOBALS['__test_theme_mods'] = [];

if ( ! function_exists( 'get_theme_mod' ) ) {
    function get_theme_mod( $name, $default = false ) {
        return $GLOBALS['__test_theme_mods'][ $name ] ?? $default;
    }
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
    function wp_strip_all_tags( $string ) {
        return preg_replace( '/<[^>]*>/', '', (string) $string );
    }
}

if ( ! function_exists( 'wp_kses_post' ) ) {
    function wp_kses_post( $string ) {
        // Test stub — real wp_kses_post does HTML allow-list filtering.
        // For the unit tests below we only care about pass-through behavior.
        return (string) $string;
    }
}

if ( ! function_exists( 'wp_timezone' ) ) {
    function wp_timezone() {
        // Use UTC for deterministic test results regardless of where the
        // test machine actually lives. Real installs use the WP-configured
        // timezone, which is the same DateTimeZone-shaped object.
        return new DateTimeZone( 'UTC' );
    }
}

if ( ! function_exists( 'esc_attr_e' ) ) {
    function esc_attr_e( $text, $domain = '' ) { /* no-op in unit tests */ }
}

if ( ! function_exists( 'esc_attr__' ) ) {
    function esc_attr__( $text, $domain = '' ) { return (string) $text; }
}

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
}

if ( ! function_exists( 'esc_url' ) ) {
    function esc_url( $url ) { return (string) $url; }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
}

// Hook stubs — the functions we're testing don't use hooks directly, but
// template-functions.php has top-level add_action / add_filter calls that
// would fatal without stubs.
if ( ! function_exists( 'add_action' ) ) {
    function add_action( ...$args ) { /* no-op */ }
}
if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( ...$args ) { /* no-op */ }
}
if ( ! function_exists( 'apply_filters' ) ) {
    function apply_filters( $tag, $value, ...$args ) { return $value; }
}
if ( ! function_exists( 'do_action' ) ) {
    function do_action( ...$args ) { /* no-op */ }
}

// Other WP functions the helpers might transitively reference. Keep these
// minimal — only what's needed for the announcement-bar functions to load.
if ( ! function_exists( 'is_user_logged_in' ) ) {
    function is_user_logged_in() { return false; }
}
if ( ! function_exists( 'has_nav_menu' ) ) {
    function has_nav_menu( $location ) { return false; }
}
if ( ! function_exists( 'wp_nav_menu' ) ) {
    function wp_nav_menu( $args = [] ) { /* no-op */ }
}
if ( ! function_exists( '_e' ) ) {
    function _e( $text, $domain = '' ) { /* no-op */ }
}
if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = '' ) { return (string) $text; }
}
if ( ! function_exists( 'sprintf' ) ) {
    // sprintf is a built-in; stub guard not needed — but kept for symmetry
}
if ( ! function_exists( 'wp_get_environment_type' ) ) {
    function wp_get_environment_type() { return 'production'; }
}
if ( ! function_exists( 'is_admin' ) ) {
    function is_admin() { return false; }
}
if ( ! function_exists( 'wp_is_mobile' ) ) {
    function wp_is_mobile() { return false; }
}
if ( ! function_exists( 'is_singular' ) ) {
    function is_singular() { return false; }
}
if ( ! function_exists( 'comments_open' ) ) {
    function comments_open() { return false; }
}

// ---------------------------------------------------------------------------
// Load the helpers under test
// ---------------------------------------------------------------------------

// template-functions.php has a lot of unrelated code; loading the whole
// file is the simplest way to keep the test in sync with the source.
$source_file = dirname( __FILE__ ) . '/../inc/template-functions.php';
if ( ! file_exists( $source_file ) ) {
    fwrite( STDERR, "ERROR: Source file not found: $source_file\n" );
    exit( 1 );
}
require_once $source_file;

// ---------------------------------------------------------------------------
// Test infrastructure (inline mini-runner — same pattern as the optimizer)
// ---------------------------------------------------------------------------

class AnnouncementBarUnitTestRunner {

    private $tests_run    = 0;
    private $tests_passed = 0;
    private $tests_failed = 0;
    private $failures     = [];

    public function run() {
        echo "\n";
        echo "╔══════════════════════════════════════════════════╗\n";
        echo "║   Promptless Theme Announcement Bar Unit Tests   ║\n";
        echo "╚══════════════════════════════════════════════════╝\n\n";

        // Hash + cookie name
        $this->test_hash_consistency_across_calls();
        $this->test_hash_normalizes_whitespace();
        $this->test_hash_changes_with_message();
        $this->test_hash_returns_empty_for_blank_message();
        $this->test_cookie_name_includes_hash_prefix();
        $this->test_cookie_name_empty_when_no_message();

        // Schedule
        $this->test_schedule_no_window_returns_true();
        $this->test_schedule_only_start_in_past_visible();
        $this->test_schedule_only_start_in_future_hidden();
        $this->test_schedule_only_end_in_future_visible();
        $this->test_schedule_only_end_in_past_hidden();
        $this->test_schedule_window_active_visible();
        $this->test_schedule_window_before_start_hidden();
        $this->test_schedule_window_after_end_hidden();
        $this->test_schedule_seconds_optional();
        $this->test_schedule_invalid_string_treated_as_unset();

        // Visibility integration
        $this->test_has_announcement_disabled_returns_false();
        $this->test_has_announcement_empty_message_returns_false();
        $this->test_has_announcement_outside_schedule_returns_false();
        $this->test_has_announcement_dismissed_returns_false();
        $this->test_has_announcement_all_clear_returns_true();
        $this->test_dismissible_false_skips_cookie_check();

        // Reusable elements in the message
        $this->test_message_unchanged_without_the_plugin();
        $this->test_message_resolves_reusable_elements_with_the_plugin();

        $this->print_results();
    }

    // -----------------------------------------------------------------------
    // Hash + cookie name
    // -----------------------------------------------------------------------

    private function test_hash_consistency_across_calls() {
        echo "  Hash › same message produces same hash across calls\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_message', 'Spring sale — 20% off!' );
        $h1 = promptless_announcement_get_hash();
        $h2 = promptless_announcement_get_hash();
        $this->assert( $h1 === $h2, 'Hash is deterministic' );
        $this->assert( strlen( $h1 ) === 40, 'Hash is sha1-shaped (40 hex chars, got ' . strlen( $h1 ) . ')' );
    }

    private function test_hash_normalizes_whitespace() {
        echo "  Hash › whitespace differences normalize to same hash\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_message', 'Hello world' );
        $h1 = promptless_announcement_get_hash();
        set_theme_mod_test( 'promptless_announcement_message', "  Hello   world  \n" );
        $h2 = promptless_announcement_get_hash();
        $this->assert( $h1 === $h2, 'Whitespace normalization makes "Hello world" and "  Hello   world  \\n" hash identically' );
    }

    private function test_hash_changes_with_message() {
        echo "  Hash › different messages produce different hashes\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_message', 'Black Friday' );
        $h1 = promptless_announcement_get_hash();
        set_theme_mod_test( 'promptless_announcement_message', 'Cyber Monday' );
        $h2 = promptless_announcement_get_hash();
        $this->assert( $h1 !== $h2, 'Different messages → different hashes (dismissals will reset)' );
    }

    private function test_hash_returns_empty_for_blank_message() {
        echo "  Hash › empty message → empty hash\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_message', '' );
        $this->assert( promptless_announcement_get_hash() === '', 'Empty message returns empty string (no cookie name to compute)' );
    }

    private function test_cookie_name_includes_hash_prefix() {
        echo "  Cookie name › includes deterministic prefix + hash\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_message', 'Foo' );
        $name = promptless_announcement_cookie_name();
        $this->assert( strpos( $name, 'promptless_announcement_dismissed_' ) === 0, 'Cookie name starts with the documented prefix' );
        $this->assert( strlen( $name ) === strlen( 'promptless_announcement_dismissed_' ) + 16, 'Cookie name suffix is 16 hex chars (hash truncation)' );
    }

    private function test_cookie_name_empty_when_no_message() {
        echo "  Cookie name › empty when no message hash computable\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_message', '' );
        $this->assert( promptless_announcement_cookie_name() === '', 'No message → no cookie name' );
    }

    // -----------------------------------------------------------------------
    // Schedule
    // -----------------------------------------------------------------------

    private function test_schedule_no_window_returns_true() {
        echo "  Schedule › neither start nor end set → always visible\n";
        reset_test_state();
        $this->assert( promptless_announcement_in_schedule() === true, 'Empty schedule = always show' );
    }

    private function test_schedule_only_start_in_past_visible() {
        echo "  Schedule › only start_date, in past → visible\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_start_date', $this->offset_now( '-7 days' ) );
        $this->assert( promptless_announcement_in_schedule() === true, 'Past start, no end = visible from then on' );
    }

    private function test_schedule_only_start_in_future_hidden() {
        echo "  Schedule › only start_date, in future → hidden\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_start_date', $this->offset_now( '+7 days' ) );
        $this->assert( promptless_announcement_in_schedule() === false, 'Future start = hidden until then' );
    }

    private function test_schedule_only_end_in_future_visible() {
        echo "  Schedule › only end_date, in future → visible\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_end_date', $this->offset_now( '+7 days' ) );
        $this->assert( promptless_announcement_in_schedule() === true, 'Future end, no start = visible until then' );
    }

    private function test_schedule_only_end_in_past_hidden() {
        echo "  Schedule › only end_date, in past → hidden\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_end_date', $this->offset_now( '-7 days' ) );
        $this->assert( promptless_announcement_in_schedule() === false, 'Past end = hidden after then' );
    }

    private function test_schedule_window_active_visible() {
        echo "  Schedule › now inside [start, end] window → visible\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_start_date', $this->offset_now( '-1 day' ) );
        set_theme_mod_test( 'promptless_announcement_end_date',   $this->offset_now( '+1 day' ) );
        $this->assert( promptless_announcement_in_schedule() === true, 'Inside window = visible' );
    }

    private function test_schedule_window_before_start_hidden() {
        echo "  Schedule › now before start_date → hidden\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_start_date', $this->offset_now( '+1 day' ) );
        set_theme_mod_test( 'promptless_announcement_end_date',   $this->offset_now( '+7 days' ) );
        $this->assert( promptless_announcement_in_schedule() === false, 'Before window = hidden' );
    }

    private function test_schedule_window_after_end_hidden() {
        echo "  Schedule › now after end_date → hidden\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_start_date', $this->offset_now( '-7 days' ) );
        set_theme_mod_test( 'promptless_announcement_end_date',   $this->offset_now( '-1 day' ) );
        $this->assert( promptless_announcement_in_schedule() === false, 'After window = hidden' );
    }

    private function test_schedule_seconds_optional() {
        echo "  Schedule › datetime values without :SS still parse correctly\n";
        reset_test_state();
        // Use the H:i format (no seconds) — what most browsers' datetime-local inputs emit by default.
        $past_no_seconds = ( new DateTimeImmutable( '-1 day', new DateTimeZone( 'UTC' ) ) )->format( 'Y-m-d\TH:i' );
        set_theme_mod_test( 'promptless_announcement_start_date', $past_no_seconds );
        $this->assert( promptless_announcement_in_schedule() === true, 'H:i format (no seconds) parses correctly' );
    }

    private function test_schedule_invalid_string_treated_as_unset() {
        echo "  Schedule › unparseable date string defensively skipped\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_start_date', 'not a date' );
        // Unparseable values should be treated as no-constraint — the
        // sanitize callback should have rejected them at write time, but
        // the renderer must not fatal if the option somehow contains junk.
        $this->assert( promptless_announcement_in_schedule() === true, 'Junk value = no constraint = visible' );
    }

    // -----------------------------------------------------------------------
    // Visibility integration
    // -----------------------------------------------------------------------

    private function test_has_announcement_disabled_returns_false() {
        echo "  Visibility › disabled → not visible\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_enabled', false );
        set_theme_mod_test( 'promptless_announcement_message', 'Real message' );
        $this->assert( promptless_has_announcement() === false, 'Disabled overrides everything' );
    }

    private function test_has_announcement_empty_message_returns_false() {
        echo "  Visibility › enabled but empty message → not visible\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_enabled', true );
        set_theme_mod_test( 'promptless_announcement_message', '   ' );
        $this->assert( promptless_has_announcement() === false, 'Whitespace-only message = nothing to show' );
    }

    private function test_has_announcement_outside_schedule_returns_false() {
        echo "  Visibility › enabled + message but outside schedule → not visible\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_enabled', true );
        set_theme_mod_test( 'promptless_announcement_message', 'Black Friday' );
        set_theme_mod_test( 'promptless_announcement_start_date', $this->offset_now( '+7 days' ) );
        $this->assert( promptless_has_announcement() === false, 'Schedule wins over enable+message' );
    }

    private function test_has_announcement_dismissed_returns_false() {
        echo "  Visibility › dismiss cookie matches → not visible\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_enabled', true );
        set_theme_mod_test( 'promptless_announcement_message', 'Dismiss me' );
        $cookie_name = promptless_announcement_cookie_name();
        $_COOKIE[ $cookie_name ] = '1';
        $this->assert( promptless_has_announcement() === false, 'Dismiss cookie hides bar' );
        unset( $_COOKIE[ $cookie_name ] );
    }

    private function test_has_announcement_all_clear_returns_true() {
        echo "  Visibility › all gates pass → visible\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_enabled', true );
        set_theme_mod_test( 'promptless_announcement_message', 'Visible' );
        $this->assert( promptless_has_announcement() === true, 'Enabled + message + no schedule + no dismiss = visible' );
    }

    private function test_dismissible_false_skips_cookie_check() {
        echo "  Visibility › non-dismissible bar shows even with stale cookie\n";
        reset_test_state();
        set_theme_mod_test( 'promptless_announcement_enabled', true );
        set_theme_mod_test( 'promptless_announcement_message', 'Critical notice' );
        set_theme_mod_test( 'promptless_announcement_dismissible', false );
        // Plant a stale dismiss cookie matching this message hash.
        $cookie_name = 'promptless_announcement_dismissed_' . substr( promptless_announcement_get_hash(), 0, 16 );
        $_COOKIE[ $cookie_name ] = '1';
        $this->assert( promptless_announcement_is_dismissed() === false, 'Non-dismissible bars are never "dismissed"' );
        $this->assert( promptless_has_announcement() === true, 'Critical notices persist past stale cookies' );
        unset( $_COOKIE[ $cookie_name ] );
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Build a datetime-local string offset from now.
     */
    private function offset_now( string $modifier ): string {
        $when = new DateTimeImmutable( $modifier, new DateTimeZone( 'UTC' ) );
        return $when->format( 'Y-m-d\TH:i:s' );
    }

    // -----------------------------------------------------------------------
    // [re:KEY] reusable elements (fixed 2026-09-18: the theme called a
    // process() method the plugin does not have, and the exception was
    // swallowed, so shortcodes reached visitors unresolved)
    // -----------------------------------------------------------------------

    private function test_message_unchanged_without_the_plugin() {
        echo "  Reusable elements › plugin absent → message unchanged\n";
        $this->assert(
            ! class_exists( '\\AISB\\Modern\\Core\\ReusableElementsProcessor' ),
            'precondition: no processor class loaded yet'
        );
        $this->assert(
            promptless_resolve_announcement_message( 'Call [re:phone] today' ) === 'Call [re:phone] today',
            'the raw message is returned'
        );
    }

    private function test_message_resolves_reusable_elements_with_the_plugin() {
        echo "  Reusable elements › plugin active → [re:KEY] resolved through process_shortcodes()\n";
        // Stand-in with the plugin's real public method name.
        eval( 'namespace AISB\\Modern\\Core; class ReusableElementsProcessor { public function process_shortcodes( $c ) { return str_replace( "[re:phone]", "555-0100", $c ); } }' );
        $this->assert(
            promptless_resolve_announcement_message( 'Call [re:phone] today' ) === 'Call 555-0100 today',
            'the shortcode is resolved'
        );
        $this->assert(
            promptless_resolve_announcement_message( '' ) === '',
            'an empty message stays empty'
        );
    }

    private function assert( bool $condition, string $message ): void {
        $this->tests_run++;
        if ( $condition ) {
            $this->tests_passed++;
            echo "    ✓ {$message}\n";
        } else {
            $this->tests_failed++;
            $this->failures[] = $message;
            echo "    ✗ FAIL: {$message}\n";
        }
    }

    private function print_results(): void {
        echo "\n";
        echo "══════════════════════════════════════════════════\n";
        echo "  Results: {$this->tests_passed}/{$this->tests_run} passed";
        if ( $this->tests_failed > 0 ) {
            echo " ({$this->tests_failed} failed)";
        }
        echo "\n";
        echo "══════════════════════════════════════════════════\n";

        if ( ! empty( $this->failures ) ) {
            echo "\n  Failures:\n";
            foreach ( $this->failures as $failure ) {
                echo "    - {$failure}\n";
            }
        }
        echo "\n";
        exit( $this->tests_failed > 0 ? 1 : 0 );
    }
}

/** Reset the in-memory theme_mod store + $_COOKIE between tests. */
function reset_test_state() {
    $GLOBALS['__test_theme_mods'] = [
        'promptless_announcement_enabled'      => false,
        'promptless_announcement_message'      => '',
        'promptless_announcement_cta_text'     => '',
        'promptless_announcement_cta_url'      => '',
        'promptless_announcement_theme'        => 'dark',
        'promptless_announcement_dismissible'  => true,
        'promptless_announcement_start_date'   => '',
        'promptless_announcement_end_date'     => '',
    ];
    $_COOKIE = [];
}

/** Test-only setter — writes through to the in-memory store. */
function set_theme_mod_test( string $key, $value ) {
    $GLOBALS['__test_theme_mods'][ $key ] = $value;
}

// Run.
( new AnnouncementBarUnitTestRunner() )->run();
