<?php
/**
 * Mega Menu — admin foundation (Phase 0)
 *
 * Extends the native WordPress menu system (Appearance → Menus) with the
 * per-item data the mega menu walker consumes in later phases:
 *   - a "Display as mega menu" toggle (top-level items only)
 *   - an Iconify icon name (e.g. "lucide:book-open")
 *   - supporting text shown beneath the link title
 *
 * Storage is native nav_menu_item post meta — no custom tables. The meta
 * keys below form a documented, filterable contract (see get_item_config())
 * so the Promptless connector / MCP layer can populate mega menus
 * programmatically, the same way it builds menus today.
 *
 * Phase 0 adds ONLY the admin fields + persistence. There is NO frontend
 * rendering change until the walker lands in Phase 1 — menus render exactly
 * as before.
 *
 * @package Promptless_Theme
 * @since 1.3.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Promptless_Mega_Menu
 */
class Promptless_Mega_Menu {

	/**
	 * Meta key: "Display as mega menu" toggle ('1' or absent). Top-level only.
	 *
	 * @var string
	 */
	const META_ENABLED = '_promptless_mega_enabled';

	/**
	 * Meta key: Iconify icon identifier, e.g. "lucide:book-open".
	 *
	 * @var string
	 */
	const META_ICON = '_promptless_menu_icon';

	/**
	 * Meta key: supporting text shown beneath the item title.
	 *
	 * @var string
	 */
	const META_DESCRIPTION = '_promptless_menu_description';

	/**
	 * Version of the vendored iconify-icon web component (assets/js/).
	 * Used for cache-busting; bump when the vendored file is updated.
	 *
	 * @var string
	 */
	const ICONIFY_VERSION = '3.0.1';

	/**
	 * Constructor — wire admin hooks.
	 */
	public function __construct() {
		// Render the custom fields inside each item row in Appearance → Menus.
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'render_fields' ), 10, 5 );

		// Persist them when the menu is saved.
		add_action( 'wp_update_nav_menu_item', array( $this, 'save_fields' ), 10, 2 );

		// Tag mega parents on the primary menu so the walker + CSS can target
		// them, while reusing core's class pipeline (preserving item states).
		add_filter( 'nav_menu_css_class', array( $this, 'add_mega_parent_class' ), 10, 4 );

		// Ensure the Iconify runtime is present wherever the header renders
		// mega icons. Priority 20 so it runs after the Promptless plugin's own
		// asset enqueue, letting us dedupe against its handle.
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_iconify' ), 20 );
	}

	/**
	 * Load the Iconify web-component runtime when the primary menu uses icons.
	 *
	 * The header (and therefore the mega menu) renders on every page, but the
	 * Promptless plugin only registers the `<iconify-icon>` element on pages
	 * that contain its sections — it imports the component inside its frontend
	 * entry (`aisb-frontend`). On section-less pages (blog, archive,
	 * WooCommerce, …) the element would never upgrade. The theme therefore
	 * ships its own copy of the self-registering `iconify-icon` web component
	 * and loads it on demand, keeping the header self-sufficient.
	 *
	 * When the plugin's frontend entry is already loading on this page it
	 * registers the element itself, so we skip our copy to avoid a redundant
	 * download. The component self-guards double-definition, so this is purely
	 * an optimization, not a correctness requirement.
	 *
	 * @return void
	 */
	public function maybe_enqueue_iconify() {
		if ( ! $this->primary_menu_has_icons() ) {
			return;
		}

		// The plugin's frontend entry registers <iconify-icon> on section pages.
		if ( wp_script_is( 'aisb-frontend', 'enqueued' ) ) {
			return;
		}

		$relative = 'assets/js/iconify-icon.min.js';
		$path     = get_theme_file_path( $relative );

		/**
		 * Filter the Iconify web-component runtime URL the theme enqueues.
		 *
		 * Return '' to opt out and keep the mega menu text-only.
		 *
		 * @param string $url Script URL.
		 */
		$src = apply_filters( 'promptless_iconify_runtime_url', get_theme_file_uri( $relative ) );

		if ( '' === $src || ! file_exists( $path ) ) {
			return; // No runtime available → icons gracefully omitted.
		}

		wp_enqueue_script(
			'promptless-iconify-icon',
			$src,
			array(),
			self::ICONIFY_VERSION,
			true
		);
	}

	/**
	 * Whether the menu assigned to the `primary` location uses any mega icon.
	 *
	 * Runs once per request (on wp_enqueue_scripts). Menus are small and
	 * WordPress caches the item query + meta, so the scan is cheap.
	 *
	 * @return bool
	 */
	private function primary_menu_has_icons() {
		$locations = get_nav_menu_locations();
		if ( empty( $locations['primary'] ) ) {
			return false;
		}

		$items = wp_get_nav_menu_items( (int) $locations['primary'] );
		if ( ! $items ) {
			return false;
		}

		foreach ( $items as $item ) {
			if ( '' !== (string) get_post_meta( $item->ID, self::META_ICON, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Append the mega-parent class to qualifying top-level items.
	 *
	 * Scoped to the `primary` menu location (the only place the mega walker
	 * runs) so other menus skip the extra meta lookups entirely. A mega parent
	 * must be flagged AND have children.
	 *
	 * @param string[] $classes Item CSS classes.
	 * @param WP_Post  $item    Menu item.
	 * @param stdClass $args    wp_nav_menu args.
	 * @param int      $depth   Item depth.
	 * @return string[] Filtered classes.
	 */
	public function add_mega_parent_class( $classes, $item, $args, $depth = 0 ) {
		if ( 0 !== (int) $depth ) {
			return $classes;
		}

		if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
			return $classes;
		}

		// Only items that actually have children can be a mega panel.
		if ( ! in_array( 'menu-item-has-children', $classes, true ) ) {
			return $classes;
		}

		$config = self::get_item_config( $item->ID, $item );
		if ( $config['is_mega'] ) {
			$classes[] = 'promptless-header__mega-parent';
		}

		return $classes;
	}

	/**
	 * Get the resolved mega-menu configuration for a menu item.
	 *
	 * This is the public, filterable contract consumed by the walker (Phase 1)
	 * and available to the connector / other integrations. Supporting text
	 * falls back to the native menu-item Description when our dedicated field
	 * is empty, so either field works.
	 *
	 * @param int          $item_id Menu item (post) ID.
	 * @param WP_Post|null $item    Optional menu item object (for the native
	 *                              description fallback without an extra query).
	 * @return array{is_mega:bool,icon:string,description:string}
	 */
	public static function get_item_config( $item_id, $item = null ) {
		$description = (string) get_post_meta( $item_id, self::META_DESCRIPTION, true );

		if ( '' === $description && $item instanceof WP_Post && ! empty( $item->description ) ) {
			$description = (string) $item->description;
		}

		$config = array(
			'is_mega'     => '1' === get_post_meta( $item_id, self::META_ENABLED, true ),
			'icon'        => (string) get_post_meta( $item_id, self::META_ICON, true ),
			'description' => $description,
		);

		/**
		 * Filter the resolved mega-menu config for a menu item.
		 *
		 * Lets the connector / integrations override per-item mega settings
		 * without touching post meta directly.
		 *
		 * @param array $config  { is_mega, icon, description }.
		 * @param int   $item_id Menu item ID.
		 */
		return apply_filters( 'promptless_mega_menu_item_config', $config, $item_id );
	}

	/**
	 * Render the custom fields for a single menu item.
	 *
	 * Hooked to `wp_nav_menu_item_custom_fields` (WordPress 5.4+). The toggle
	 * is rendered only on top-level items; the icon + supporting-text fields
	 * are available at every depth (children populate the panel grid).
	 *
	 * @param int      $item_id Menu item (post) ID.
	 * @param WP_Post  $item    Menu item object.
	 * @param int      $depth   Item depth (0 = top level).
	 * @param stdClass $args    Menu arguments (unused).
	 * @param int      $id      Nav menu term ID (unused).
	 * @return void
	 */
	public function render_fields( $item_id, $item, $depth, $args, $id = 0 ) {
		$enabled     = get_post_meta( $item_id, self::META_ENABLED, true );
		$icon        = get_post_meta( $item_id, self::META_ICON, true );
		$description = get_post_meta( $item_id, self::META_DESCRIPTION, true );
		?>
		<div class="promptless-mega-menu-fields" style="clear:both;">

			<?php if ( 0 === (int) $depth ) : ?>
				<p class="field-promptless-mega-enabled description description-wide">
					<label for="promptless-mega-enabled-<?php echo esc_attr( $item_id ); ?>">
						<input type="checkbox"
							id="promptless-mega-enabled-<?php echo esc_attr( $item_id ); ?>"
							name="promptless-mega-enabled[<?php echo esc_attr( $item_id ); ?>]"
							value="1" <?php checked( $enabled, '1' ); ?> />
						<?php esc_html_e( 'Display as mega menu', 'promptless' ); ?>
					</label>
					<span class="description"><?php esc_html_e( "Show this item's sub-items as a multi-column panel.", 'promptless' ); ?></span>
				</p>
			<?php endif; ?>

			<p class="field-promptless-menu-icon description description-wide">
				<label for="promptless-menu-icon-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'Icon (Iconify name)', 'promptless' ); ?><br />
					<input type="text"
						id="promptless-menu-icon-<?php echo esc_attr( $item_id ); ?>"
						class="widefat code"
						name="promptless-menu-icon[<?php echo esc_attr( $item_id ); ?>]"
						value="<?php echo esc_attr( $icon ); ?>"
						placeholder="lucide:book-open" />
				</label>
				<span class="description"><?php esc_html_e( 'Paste an Iconify name, e.g. lucide:book-open. Browse at icon-sets.iconify.design.', 'promptless' ); ?></span>
			</p>

			<p class="field-promptless-menu-description description description-wide">
				<label for="promptless-menu-description-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'Supporting text', 'promptless' ); ?><br />
					<textarea
						id="promptless-menu-description-<?php echo esc_attr( $item_id ); ?>"
						class="widefat"
						rows="2"
						name="promptless-menu-description[<?php echo esc_attr( $item_id ); ?>]"><?php echo esc_textarea( $description ); ?></textarea>
				</label>
				<span class="description"><?php esc_html_e( 'Short description shown beneath the title in the mega menu.', 'promptless' ); ?></span>
			</p>

		</div>
		<?php
	}

	/**
	 * Persist the custom fields when a menu is saved.
	 *
	 * Fires inside core's nav-menu save, which has already verified the
	 * `update-nav_menu` nonce and the `edit_theme_options` capability before
	 * this hook runs. We re-check the capability defensively, and use the
	 * always-rendered icon field as a sentinel that this item's row was part
	 * of the submitted form (the hook also fires during the ajax add-item
	 * flow, where our fields are absent and must be left untouched).
	 *
	 * @param int $menu_id         Nav menu term ID (unused).
	 * @param int $menu_item_db_id Menu item (post) ID.
	 * @return void
	 */
	public function save_fields( $menu_id, $menu_item_db_id ) {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		// Sentinel: the icon field is rendered for every item, so its presence
		// means this item's row was submitted. Without it, do nothing.
		// Nonce + capability are enforced by core before this hook fires.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['promptless-menu-icon'][ $menu_item_db_id ] ) ) {
			return;
		}

		// Toggle: checkbox present = enabled. Only top-level items render it;
		// for children the box is never present, so the meta is cleared.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST['promptless-mega-enabled'][ $menu_item_db_id ] ) ) {
			update_post_meta( $menu_item_db_id, self::META_ENABLED, '1' );
		} else {
			delete_post_meta( $menu_item_db_id, self::META_ENABLED );
		}

		// Icon — validated to an Iconify "set:name" shape.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$icon = $this->sanitize_icon( wp_unslash( $_POST['promptless-menu-icon'][ $menu_item_db_id ] ) );
		if ( '' !== $icon ) {
			update_post_meta( $menu_item_db_id, self::META_ICON, $icon );
		} else {
			delete_post_meta( $menu_item_db_id, self::META_ICON );
		}

		// Supporting text — plain text.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$raw_desc = isset( $_POST['promptless-menu-description'][ $menu_item_db_id ] )
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			? wp_unslash( $_POST['promptless-menu-description'][ $menu_item_db_id ] )
			: '';
		$desc = sanitize_text_field( $raw_desc );
		if ( '' !== $desc ) {
			update_post_meta( $menu_item_db_id, self::META_DESCRIPTION, $desc );
		} else {
			delete_post_meta( $menu_item_db_id, self::META_DESCRIPTION );
		}
	}

	/**
	 * Sanitize an Iconify icon identifier.
	 *
	 * Accepts only the canonical "prefix:name" shape (lowercase alphanumeric
	 * segments separated by single hyphens, one colon). Anything else returns
	 * an empty string, so a malformed value can never reach an HTML attribute.
	 *
	 * @param string $value Raw input.
	 * @return string Validated identifier, or '' if invalid.
	 */
	private function sanitize_icon( $value ) {
		$value = strtolower( trim( sanitize_text_field( $value ) ) );

		if ( '' === $value ) {
			return '';
		}

		if ( preg_match( '/^[a-z0-9]+(-[a-z0-9]+)*:[a-z0-9]+(-[a-z0-9]+)*$/', $value ) ) {
			return $value;
		}

		return '';
	}
}
