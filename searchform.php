<?php
/**
 * Custom search form template.
 *
 * Rendered by get_search_form(), which makes every search form in the
 * theme filterable (Theme Review: never hardcode search forms; this file
 * is the sanctioned override point).
 *
 * Two contexts share this template:
 *
 * - Default: the standard .search-form used by search.php, 404.php and
 *   content-none.php. Markup mirrors WordPress core's HTML5 fallback so
 *   the existing archive.css rules (.search-form / .search-field /
 *   .search-submit) apply unchanged.
 *
 * - Overlay: promptless_search_overlay() calls
 *   get_search_form( array( 'promptless_context' => 'overlay' ) ).
 *   MARKUP CONTRACT with assets/js/search-overlay.js and search.css —
 *   do not rename classes, data attributes, or ARIA attributes here
 *   without updating both.
 *
 * @package Promptless
 */

if ( isset( $args['promptless_context'] ) && 'overlay' === $args['promptless_context'] ) : ?>
	<form role="search" method="get" action="<?php echo esc_url( wp_make_link_relative( home_url( '/' ) ) ); ?>" class="promptless-search-overlay__form">
		<?php promptless_search_icon(); ?>
		<input
			type="search"
			name="s"
			class="promptless-search-overlay__input"
			placeholder="<?php esc_attr_e( 'Search…', 'promptless' ); ?>"
			autocomplete="off"
			role="combobox"
			aria-expanded="false"
			aria-controls="promptless-search-results"
			aria-autocomplete="list"
		/>
		<button type="submit" class="screen-reader-text"><?php esc_html_e( 'Search', 'promptless' ); ?></button>
		<?php // Close lives IN the flex row: align-items centers it against
		      // the input pixel-perfectly with zero positioning math. ?>
		<button type="button" class="promptless-search-overlay__close" data-search-close aria-label="<?php esc_attr_e( 'Close search', 'promptless' ); ?>">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
		</button>
	</form>
<?php else : ?>
	<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label>
			<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'promptless' ); ?></span>
			<input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search…', 'promptless' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
		</label>
		<input type="submit" class="search-submit" value="<?php esc_attr_e( 'Search', 'promptless' ); ?>" />
	</form>
<?php endif; ?>
