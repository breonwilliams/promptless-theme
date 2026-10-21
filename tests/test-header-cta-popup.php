<?php
/**
 * Promptless Theme — header buttons that open a popup (1.4.3).
 *
 * A header button can follow a link or open a Promptless WP popup. This pins
 * what renders for every combination, including the degraded ones, and what
 * the theme tells Promptless WP so the popup loads wherever the header shows:
 *
 *   1. a header saved before 1.4.3 (no action) renders the same <a>;
 *   2. action 'modal' with a popup Promptless WP lists renders a real
 *      <button type="button" aria-haspopup="dialog" data-aisb-modal-open>;
 *   3. a missing / deleted popup falls back to the URL, or drops the button
 *      when there is none — never a button that opens nothing;
 *   4. with Promptless WP inactive (no aisb_modal_choices answer) the same;
 *   5. aisb_modal_referenced_ids gets exactly the popups rendered buttons
 *      open, so the popup's markup and assets load on that page;
 *   6. mobile placement is unchanged for popup buttons.
 *
 * Standalone (no WordPress): loads inc/template-functions.php with a minimal
 * filter registry. Run: php tests/test-header-cta-popup.php
 *
 * @package Promptless_Theme
 * @since 1.4.3
 */

define( 'ABSPATH', '/tmp/wordpress/' );

$GLOBALS['__mods']    = array();
$GLOBALS['__filters'] = array();

function add_filter( $tag, $cb, $priority = 10, $args = 1 ) {
	$GLOBALS['__filters'][ $tag ][] = $cb;
	return true;
}
function add_action( $tag, $cb, $priority = 10, $args = 1 ) {
	return true;
}
function remove_all_filters( $tag ) {
	unset( $GLOBALS['__filters'][ $tag ] );
}
function apply_filters( $tag, $value, ...$args ) {
	foreach ( $GLOBALS['__filters'][ $tag ] ?? array() as $cb ) {
		$value = $cb( $value, ...$args );
	}
	return $value;
}
function get_theme_mod( $k, $d = false ) {
	return array_key_exists( $k, $GLOBALS['__mods'] ) ? $GLOBALS['__mods'][ $k ] : $d;
}
function sanitize_key( $k ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $k ) );
}
function esc_url( $u ) {
	return htmlspecialchars( (string) $u, ENT_QUOTES );
}
function esc_attr( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES );
}
function esc_html( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES );
}

require dirname( __DIR__ ) . '/inc/template-functions.php';

$pass = 0;
$fail = 0;
function check( $cond, $label, $detail = '' ) {
	global $pass, $fail;
	if ( $cond ) {
		$pass++;
		echo "  ✓ {$label}\n";
	} else {
		$fail++;
		echo "  ✗ {$label}" . ( '' !== $detail ? "\n      {$detail}" : '' ) . "\n";
	}
}
/** Promptless WP answering aisb_modal_choices, as it does when active. */
function plugin_active( array $popups = array( 'quick_question' => 'Quick question' ) ) {
	remove_all_filters( 'aisb_modal_choices' );
	add_filter(
		'aisb_modal_choices',
		function ( $c ) use ( $popups ) {
			return $c + $popups;
		}
	);
}
function header_html() {
	ob_start();
	promptless_header_cta();
	return ob_get_clean();
}
function mods( array $m ) {
	$GLOBALS['__mods'] = $m;
}

echo "\n1. A header saved before 1.4.3\n";
plugin_active();
mods( array( 'promptless_header_cta_text' => 'Book', 'promptless_header_cta_url' => '/book/' ) );
$html = header_html();
check( false !== strpos( $html, '<a href="/book/" class="promptless-header__cta' ), 'renders the same link', $html );
check( false === strpos( $html, 'data-aisb-modal-open' ), 'no popup trigger' );

echo "\n2. A popup button\n";
mods( array( 'promptless_header_cta_text' => 'Ask a question', 'promptless_header_cta_action' => 'modal', 'promptless_header_cta_modal' => 'quick_question' ) );
$html = header_html();
check( 1 === preg_match( '/<button type="button" class="promptless-header__cta [^"]*" data-aisb-modal-open="quick_question" aria-haspopup="dialog">Ask a question<\/button>/', $html ), 'real <button> announced as opening a dialog', $html );
check( false === strpos( $html, 'href=' ), 'no href' );

echo "\n3. A popup that no longer exists\n";
plugin_active( array() );
mods( array( 'promptless_header_cta_text' => 'Ask', 'promptless_header_cta_action' => 'modal', 'promptless_header_cta_modal' => 'quick_question', 'promptless_header_cta_url' => '/contact/' ) );
$html = header_html();
check( false !== strpos( $html, '<a href="/contact/"' ), 'falls back to its URL', $html );
mods( array( 'promptless_header_cta_text' => 'Ask', 'promptless_header_cta_action' => 'modal', 'promptless_header_cta_modal' => 'quick_question' ) );
check( '' === trim( header_html() ), 'no URL either: the button is not shown' );
check( array() === promptless_get_header_ctas(), 'and it is not counted as a header action' );

echo "\n4. Promptless WP inactive\n";
remove_all_filters( 'aisb_modal_choices' );
mods( array( 'promptless_header_cta_text' => 'Ask', 'promptless_header_cta_action' => 'modal', 'promptless_header_cta_modal' => 'quick_question', 'promptless_header_cta_url' => '/contact/' ) );
check( false !== strpos( header_html(), '<a href="/contact/"' ), 'falls back to the URL' );

echo "\n5. The popup loads where the header shows it\n";
plugin_active( array( 'quick_question' => 'Q', 'tour' => 'T' ) );
mods( array(
	'promptless_header_cta_text'     => 'Ask',
	'promptless_header_cta_action'   => 'modal',
	'promptless_header_cta_modal'    => 'Quick-Question',
	'promptless_header_cta_2_text'   => 'Visit',
	'promptless_header_cta_2_url'    => '/visit/',
	'promptless_header_cta_2_modal'  => 'tour',
) );
$ids = apply_filters( 'aisb_modal_referenced_ids', array( 'from_a_section' ) );
check( array( 'from_a_section', 'quick_question' ) === $ids, 'adds the header popup (normalised id) and nothing else', json_encode( $ids ) );
mods( array( 'promptless_header_cta_text' => 'Ask', 'promptless_header_cta_action' => 'modal', 'promptless_header_cta_modal' => 'gone' ) );
check( array() === apply_filters( 'aisb_modal_referenced_ids', array() ), 'a popup no rendered button opens is not added' );

echo "\n6. Mobile placement is unchanged\n";
plugin_active();
mods( array(
	'promptless_header_cta_text'               => 'Ask',
	'promptless_header_cta_action'             => 'modal',
	'promptless_header_cta_modal'              => 'quick_question',
	'promptless_header_cta_mobile_placement'   => 'menu',
) );
$ctas = promptless_get_header_ctas();
check( 'menu' === $ctas[0]['placement'] && 'modal' === $ctas[0]['type'], 'a popup button can move into the mobile menu' );
check( false !== strpos( header_html(), 'promptless-header__cta--to-menu' ), 'bar copy carries the to-menu modifier' );
check( false !== strpos( promptless_render_cta_button( $ctas[0] ), 'data-aisb-modal-open="quick_question"' ), 'the menu copy is the same popup trigger' );

echo "\n7. Unknown action values\n";
mods( array( 'promptless_header_cta_text' => 'Call', 'promptless_header_cta_action' => 'phone', 'promptless_header_cta_url' => '/call/' ) );
check( false !== strpos( header_html(), '<a href="/call/"' ), 'treated as a link' );
check( array( 'url', 'modal' ) === promptless_get_cta_actions(), 'actions are url and modal' );

echo "\n8. A bar button the bar cannot fit (item 8 of the video dry run)\n";
function menu_html() {
	ob_start();
	promptless_header_menu_cta( 'bottom' );
	return ob_get_clean();
}
plugin_active();
mods( array( 'promptless_header_cta_text' => 'Ask a question', 'promptless_header_cta_action' => 'modal', 'promptless_header_cta_modal' => 'quick_question' ) );
$menu = menu_html();
check( false !== strpos( header_html(), 'promptless-header__cta--keep' ), 'the bar button is unchanged' );
check( false !== strpos( $menu, 'promptless-header__menu-cta--overflow-only' ), 'the drawer group exists but is marked overflow-only', $menu );
check( 1 === preg_match( '/<button type="button" class="[^"]*promptless-header__cta--bar-overflow" data-aisb-modal-open="quick_question" aria-haspopup="dialog">Ask a question<\/button>/', $menu ), 'with an overflow copy that opens the same popup', $menu );
mods( array(
	'promptless_header_cta_text'             => 'Ask',
	'promptless_header_cta_url'              => '/ask/',
	'promptless_header_cta_2_text'           => 'Tour',
	'promptless_header_cta_2_url'            => '/tour/',
	'promptless_header_cta_2_mobile_placement' => 'menu',
) );
$menu = menu_html();
check( false === strpos( $menu, '--overflow-only' ), 'a group that holds a menu button is always shown', $menu );
check( strpos( $menu, '/ask/' ) < strpos( $menu, '/tour/' ), 'buttons keep their configured order' );
check( 1 === substr_count( $menu, '--bar-overflow' ), 'only the bar button gets an overflow copy' );
mods( array() );
check( '' === trim( menu_html() ), 'no buttons: nothing in the drawer' );
$css = file_get_contents( dirname( __DIR__ ) . '/assets/css/header-breakpoint.css' );
check( 1 === preg_match( '/@media \(max-width: 767px\) \{\s*\.promptless-header__menu-cta \.promptless-header__cta--bar-overflow,\s*\.promptless-header__menu-cta--overflow-only,\s*\.promptless-header--cta-overflow \.promptless-header__actions \.promptless-header__cta--keep \{\s*display: none;/', $css ), 'copies are hidden, and the swap is scoped to the drawer breakpoint' );
check( false !== strpos( file_get_contents( dirname( __DIR__ ) . '/assets/js/navigation.min.js' ), 'promptless-header--cta-overflow' ), 'the built navigation script sets the overflow class' );

echo "\n{$pass} passed, {$fail} failed\n";
exit( $fail ? 1 : 0 );
