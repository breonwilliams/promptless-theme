<?php
/**
 * Promptless Theme — WooCommerce asset gate recognises Promptless WP product grids.
 *
 * promptless_needs_woocommerce_assets() keeps WooCommerce's stylesheets and
 * add-to-cart script on a page that has a product grid. It compared the
 * section type against `productgrid`, Promptless WP's name before 1.3.2; the
 * type is `product_grid`, so on a site without the header cart every product
 * grid lost its AJAX add-to-cart and its WooCommerce styling (found
 * 2026-09-19). Both names must match.
 *
 * Run: php tests/test-woocommerce-assets.php
 *
 * @package Promptless_Theme
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


$run = 0; $failed = 0;
function check( $expected, $actual, $label ) {
    global $run, $failed;
    $run++;
    if ( $expected === $actual ) { echo "  ✓ {$label}\n"; return; }
    $failed++;
    echo "  ✗ {$label} — expected " . var_export( $expected, true ) . ', got ' . var_export( $actual, true ) . "\n";
}

echo "\nProduct grid detection\n";
check( true,  promptless_sections_include_product_grid( array( array( 'type' => 'hero' ), array( 'type' => 'product_grid' ) ) ), 'the current type, product_grid, is recognised' );
check( true,  promptless_sections_include_product_grid( array( array( 'type' => 'productgrid' ) ) ), 'the pre-1.3.2 name still matches' );
check( true,  promptless_sections_include_product_grid( json_encode( array( array( 'type' => 'product_grid' ) ) ) ), 'JSON-encoded meta is decoded' );
check( false, promptless_sections_include_product_grid( array( array( 'type' => 'postgrid' ), array( 'type' => 'features' ) ) ), 'a page without a product grid does not need WooCommerce assets' );
check( false, promptless_sections_include_product_grid( '' ), 'no sections' );
check( false, promptless_sections_include_product_grid( array( 'not-a-section', array() ) ), 'malformed entries are skipped' );

// ---------------------------------------------------------------------------
// Asset scope: full / grid / none (2026-09-19)
// ---------------------------------------------------------------------------
// A Product Grid page loaded ~242 KB of WooCommerce CSS and used ~2 KB;
// promptless_woocommerce_asset_scope() gives it 'grid' — the add-to-cart
// script plus a generated subset stylesheet — instead of everything.

if ( ! class_exists( 'WooCommerce' ) ) {
    class WooCommerce {}
}
$GLOBALS['__wc_page']     = false;
$GLOBALS['__sections']    = array();
function is_woocommerce() { return $GLOBALS['__wc_page']; }
function is_cart() { return false; }
function is_checkout() { return false; }
function is_account_page() { return false; }
if ( ! function_exists( 'get_post_meta' ) ) {
    function get_post_meta( $id, $key, $single = false ) { return $GLOBALS['__sections']; }
}

function scope_for( $id, $wc_page, $header_cart, $sections ) {
    global $post;
    $post                     = (object) array( 'ID' => $id );
    $GLOBALS['__wc_page']     = $wc_page;
    $GLOBALS['__test_theme_mods']['promptless_header_cart_enabled'] = $header_cart;
    $GLOBALS['__sections']    = $sections;
    return promptless_woocommerce_asset_scope();
}

echo "\nAsset scope\n";
check( 'full', scope_for( 101, true, false, array() ), 'a WooCommerce page gets everything' );
check( 'full', scope_for( 102, false, true, array( array( 'type' => 'product_grid' ) ) ), 'the header cart needs everything, grid or not' );
check( 'grid', scope_for( 103, false, false, array( array( 'type' => 'product_grid' ) ) ), 'a Product Grid page gets the grid scope' );
check( 'none', scope_for( 104, false, false, array( array( 'type' => 'features' ) ) ), 'any other page gets nothing' );
scope_for( 105, false, false, array( array( 'type' => 'product_grid' ) ) );
check( true, promptless_needs_woocommerce_assets(), 'needs_woocommerce_assets() still says yes for a grid page' );
scope_for( 106, false, false, array( array( 'type' => 'hero' ) ) );
check( false, promptless_needs_woocommerce_assets(), 'and no for a page without one' );

echo "\nGrid subset stylesheet\n";
$grid_css = @file_get_contents( dirname( __FILE__ ) . '/../assets/css/woocommerce-grid.min.css' );
check( true, is_string( $grid_css ) && '' !== $grid_css, 'woocommerce-grid.min.css is built (npm run build:css)' );
check( true, is_string( $grid_css ) && false !== strpos( $grid_css, 'a.added_to_cart' ), 'it styles the View cart link' );
check( false, is_string( $grid_css ) && false !== strpos( $grid_css, 'ul.products' ), 'it carries no shop-list rules' );
check( true, is_string( $grid_css ) && strlen( $grid_css ) < 10240, 'it stays small (under 10 KB)' );
check( true, file_exists( dirname( __FILE__ ) . '/../assets/css/woocommerce-grid.min-rtl.css' ), 'its right-to-left sibling is built' );

$assets_src = file_get_contents( dirname( __FILE__ ) . '/../inc/class-promptless-assets.php' );
check( true, false !== strpos( $assets_src, "'promptless-theme-woocommerce-grid'" ), 'the grid scope enqueues the subset' );
check( true, (bool) preg_match( "/wp_dequeue_style\\( 'woocommerce-smallscreen' \\);\\s*\\n.*?if \\( 'grid' === \\\$woo_scope \\) \\{\\s*return;/s", $assets_src ), 'a grid page drops WooCommerce styles but keeps its scripts' );

echo "\n" . ( $run - $failed ) . "/{$run} passed\n";
exit( $failed ? 1 : 0 );
