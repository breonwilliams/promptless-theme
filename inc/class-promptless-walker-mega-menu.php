<?php
/**
 * Mega Menu Walker (Phase 1)
 *
 * Renders the primary navigation, upgrading any top-level item flagged
 * "Display as mega menu" (see Promptless_Mega_Menu) into a multi-column
 * panel of its direct children — each child shown as an icon + title +
 * supporting-text card wrapped in a single link.
 *
 * Everything that is NOT a mega item is delegated to the parent
 * Walker_Nav_Menu unchanged, so regular links and standard dropdowns render
 * exactly as before. This keeps the mega menu strictly additive.
 *
 * Scope (Phase 1):
 *   - Only DIRECT children of a mega parent populate the panel. Grandchildren
 *     (depth >= 2 inside a mega subtree) are intentionally not rendered.
 *   - Markup + token-driven styling only. The disclosure/keyboard a11y layer
 *     (aria-expanded, Esc, arrow keys) is added in Phase 2; Phase 1 reveals
 *     the panel via the same CSS hover/focus-within pattern the existing
 *     dropdowns use.
 *
 * @package Promptless_Theme
 * @since 1.3.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Promptless_Walker_Mega_Menu
 */
class Promptless_Walker_Mega_Menu extends Walker_Nav_Menu {

	/**
	 * Whether the walker is currently inside a mega parent's subtree.
	 *
	 * Set on the depth-0 mega parent in start_el(), consumed by start_lvl /
	 * end_lvl / start_el / end_el for its descendants, and reset when the
	 * parent closes in end_el(). The base walker processes one branch fully
	 * before the next, so a single boolean is sufficient — top-level items
	 * never interleave.
	 *
	 * @var bool
	 */
	protected $mega_active = false;

	/**
	 * Start a sub-level (the <ul> under an item).
	 *
	 * For a mega parent's direct children, emit the panel + grid wrapper
	 * instead of a standard `.sub-menu`. Deeper levels inside a mega subtree
	 * are suppressed (Phase 1 renders direct children only).
	 *
	 * @param string   $output Passed by reference. Menu HTML.
	 * @param int      $depth  Depth of the parent item (0 = top level).
	 * @param stdClass $args   Menu arguments.
	 * @return void
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( $this->mega_active ) {
			if ( 0 === (int) $depth ) {
				$output .= '<div class="promptless-header__mega"><ul class="promptless-header__mega-grid">' . "\n";
			}
			// depth >= 1 inside a mega subtree: render nothing.
			return;
		}

		parent::start_lvl( $output, $depth, $args );
	}

	/**
	 * End a sub-level.
	 *
	 * @param string   $output Passed by reference. Menu HTML.
	 * @param int      $depth  Depth of the parent item.
	 * @param stdClass $args   Menu arguments.
	 * @return void
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( $this->mega_active ) {
			if ( 0 === (int) $depth ) {
				$output .= "</ul></div>\n";
			}
			return;
		}

		parent::end_lvl( $output, $depth, $args );
	}

	/**
	 * Start an element (a menu item).
	 *
	 * @param string   $output Passed by reference. Menu HTML.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth of the item.
	 * @param stdClass $args   Menu arguments.
	 * @param int      $id     Current item ID.
	 * @return void
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		// Top level: decide whether this item is a mega parent. A mega parent
		// must be flagged AND actually have children to display.
		if ( 0 === (int) $depth ) {
			$config            = Promptless_Mega_Menu::get_item_config( $item->ID, $item );
			$this->mega_active = ( $config['is_mega'] && $this->has_children );

			// The trigger row itself is the standard top-level <li><a>. The
			// `.promptless-header__mega-parent` class is added via the
			// nav_menu_css_class filter so existing item classes/states are
			// preserved.
			parent::start_el( $output, $item, $depth, $args, $id );
			return;
		}

		// Direct children of a mega parent become grid cards.
		if ( $this->mega_active && 1 === (int) $depth ) {
			$output .= $this->render_mega_card( $item );
			return;
		}

		// Grandchildren inside a mega subtree are out of scope for Phase 1.
		if ( $this->mega_active && (int) $depth >= 2 ) {
			return;
		}

		// Everything else (regular dropdown items) renders normally.
		parent::start_el( $output, $item, $depth, $args, $id );
	}

	/**
	 * End an element.
	 *
	 * @param string   $output Passed by reference. Menu HTML.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth of the item.
	 * @param stdClass $args   Menu arguments.
	 * @return void
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( $this->mega_active && 1 === (int) $depth ) {
			$output .= "</li>\n";
			return;
		}

		if ( $this->mega_active && (int) $depth >= 2 ) {
			return;
		}

		parent::end_el( $output, $item, $depth, $args );

		// The mega parent has now fully closed (end_lvl already ran). Reset
		// so the next top-level branch starts clean.
		if ( 0 === (int) $depth ) {
			$this->mega_active = false;
		}
	}

	/**
	 * Build the markup for a single mega-panel card.
	 *
	 * The whole card is one link (icon + title + supporting text) for a large,
	 * touch-friendly target. The icon is decorative — the title is the label —
	 * so it is rendered aria-hidden with explicit dimensions to avoid layout
	 * shift while the Iconify runtime upgrades. The closing </li> is emitted by
	 * end_el().
	 *
	 * @param WP_Post $item Menu item.
	 * @return string Card HTML (opening <li> ... </a>, no closing </li>).
	 */
	protected function render_mega_card( $item ) {
		$config = Promptless_Mega_Menu::get_item_config( $item->ID, $item );

		$url   = ! empty( $item->url ) ? $item->url : '';
		$title = apply_filters( 'the_title', $item->title, $item->ID );

		// Always render the icon gutter so titles align whether or not a card
		// has an icon (an empty span still reserves its fixed width via CSS).
		$icon_inner = '';
		if ( '' !== $config['icon'] ) {
			$icon_inner = sprintf(
				'<iconify-icon icon="%s" width="24" height="24"></iconify-icon>',
				esc_attr( $config['icon'] )
			);
		}
		$icon_html = '<span class="promptless-header__mega-icon" aria-hidden="true">' . $icon_inner . '</span>';

		$desc_html = '';
		if ( '' !== $config['description'] ) {
			$desc_html = sprintf(
				'<span class="promptless-header__mega-desc">%s</span>',
				esc_html( $config['description'] )
			);
		}

		return sprintf(
			'<li class="promptless-header__mega-item"><a class="promptless-header__mega-link" href="%1$s">%2$s<span class="promptless-header__mega-text"><span class="promptless-header__mega-title">%3$s</span>%4$s</span></a>',
			esc_url( $url ),
			$icon_html, // Pre-escaped above.
			esc_html( $title ),
			$desc_html  // Pre-escaped above.
		);
	}
}
