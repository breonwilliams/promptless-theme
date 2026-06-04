=== Promptless ===
Contributors: promptlesswp
Tags: one-column, custom-logo, custom-menu, featured-images, full-width-template, block-styles, wide-blocks, blog, portfolio
Requires at least: 6.6
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A minimal, accessibility-focused companion theme for the Promptless WP page builder.

== Description ==

Promptless is a clean, minimal WordPress theme built specifically as a companion to the Promptless WP page builder plugin. It inherits colors, typography, and border styling from the plugin's Global Settings, creating a unified brand experience across your entire website.

**Key Features:**

* **Global Settings Integration** - Header, footer, archive pages, and WooCommerce screens automatically inherit colors, typography, and border radius from the Promptless WP plugin's Global Settings
* **Two Header Layouts** - Choose Default (single row) or Stacked (two rows: branding above, navigation below), each with independent light/dark color modes
* **Utility Top Bar** - Optional bar above the header with separate left and right menu locations, its own light/dark theme, and sticky behavior that coordinates with the main header
* **Smart Mobile Top Bar Behavior** - Choose between "Always Show at Top" (a horizontally-scrollable utility row with scroll-aware edge fades) or "Collapse into Hamburger Menu" (folds top-bar menus into the main mobile drawer)
* **Accessible Hamburger Drawer** - Full keyboard focus trap, distinct keyboard-vs-mouse activation handling, ARIA-expanded state management, and Escape-to-close
* **Sticky Header Coordination** - Sticky header and sticky top bar work together; offsets are calculated dynamically to keep anchor links and skip links accurate
* **Header CTA Button** - Customizer-driven label and URL for a primary call-to-action in the header
* **Dark Mode Toggle** - Built-in sun/moon toggle in the header that respects system preferences and remembers user choice
* **WooCommerce Ready** - Mini-cart dropdown (or link-to-cart-page mode), mobile-optimized cart/checkout/account layouts using grid-based cards instead of overflowing tables, and surface styling that uses your global tokens
* **HTML-Capable Footer Brand Description** - Replace the site tagline with rich text (bold, italic, links) sanitized via wp_kses_post
* **Three Footer Columns + Bottom Bar** - Three independent menu locations with optional headings, plus a bottom footer menu for legal/policy links
* **Accessibility Ready** - Skip-to-content link, visible focus indicators on featured images and archive cards, ARIA labels throughout, and translation-ready strings
* **Performance Conscious** - Conditional CSS loading (archive and WooCommerce styles only enqueue where needed), minified production assets, and bundled build script
* **Gutenberg Ready** - Full support for the block editor with matching styles via theme.json

**Perfect For:**

* Portfolios
* Business websites
* Blogs
* Agency sites
* WooCommerce shops
* Personal websites

== Installation ==

1. In your WordPress dashboard, go to Appearance > Themes
2. Click "Add New"
3. Upload the theme zip file
4. Click "Activate"

For the best experience, install and activate the Promptless WP plugin to take advantage of the Global Settings integration.

== Theme Setup ==

**Adding a Logo:**
1. Go to Appearance > Customize > Site Identity
2. Click "Select Logo" and upload your logo
3. Adjust the logo size if needed
4. Click "Publish"

**Setting Up Menus:**
1. Go to Appearance > Menus
2. Create a new menu or select an existing one
3. Assign the menu to one of the seven available locations:
   - Primary Menu: Main navigation in the header
   - Footer Menu: Links in the footer bottom bar
   - Footer Column 1: First footer navigation column
   - Footer Column 2: Second footer navigation column
   - Footer Column 3: Third footer navigation column
   - Top Bar Left: Utility menu on the left side of the top bar (when Top Bar is enabled)
   - Top Bar Right: Utility menu on the right side of the top bar (when Top Bar is enabled)
4. Click "Save Menu"

**Choosing a Header Layout:**
1. Go to Appearance > Customize > Header > Header Layout
2. Choose one of:
   - Default (Single Row): branding, navigation, and CTA on one horizontal row
   - Stacked (Two Rows): branding on top, navigation on the row below
3. Optionally enable Sticky Header so the header stays fixed when scrolling
4. Click "Publish"

**Header CTA Button:**
1. Go to Appearance > Customize > Header > Header CTA
2. Enter your CTA button text (leave empty to hide the button entirely)
3. Enter the URL for the button
4. Click "Publish"

**Top Bar (Utility Bar):**
1. Assign menus to Top Bar Left and/or Top Bar Right under Appearance > Menus
2. Go to Appearance > Customize > Top Bar > Top Bar Settings
3. Toggle Enable Top Bar
4. Optionally pick the Top Bar Theme (light or dark, independent of the main header)
5. Optionally enable Sticky Top Bar (requires Sticky Header to be enabled as well)
6. Choose Top Bar Mobile Behavior:
   - **Always Show at Top**: keeps the top bar visible above the header on mobile as a horizontally-scrollable utility row with scroll-aware edge fades
   - **Collapse into Hamburger Menu** (default): hides the top bar on mobile and renders its menus inside the hamburger drawer instead
7. Click "Publish"

**Footer Brand Description:**
1. Go to Appearance > Customize > Footer > Footer Appearance
2. Enter rich text in Footer Brand Description (supports basic HTML: bold, italic, and links — sanitized via wp_kses_post on save)
3. Leave empty to fall back to the site tagline
4. Optionally add headings above each footer column under Footer Columns
5. Click "Publish"

== Frequently Asked Questions ==

= Does this theme require the Promptless WP plugin? =

No, the theme works without the plugin. However, for the best experience with automatic styling integration, we recommend using the Promptless WP plugin.

= How do I change colors? =

When using the Promptless WP plugin, colors are managed in the plugin's Global Settings. The theme automatically inherits these colors. Without the plugin, the theme uses sensible defaults.

= How do I enable dark mode? =

Dark mode is enabled by default. Users can click the sun/moon icon in the header to toggle between light and dark mode. The theme also respects system preferences.

= How do I switch between single-row and stacked headers? =

Go to Appearance > Customize > Header > Header Layout and pick either "Default (Single Row)" or "Stacked (Two Rows)". The Stacked layout puts your branding on the top row and your primary navigation on a row below it, which gives wide navigation menus more horizontal room. Both layouts support light and dark color modes and work with the sticky header option.

= How do I make the header stay visible when scrolling? =

Go to Appearance > Customize > Header > Header Appearance and enable Sticky Header. If you also want the top bar to stick alongside the header, enable Sticky Top Bar under Top Bar Settings. The theme calculates the combined offset automatically so anchor links and the skip-to-content link land at the right position.

= How does the top bar behave on mobile? =

The Top Bar Mobile Behavior setting (Appearance > Customize > Top Bar > Top Bar Settings) controls this. You have two options:

* **Always Show at Top** — the top bar remains visible above the header on mobile as a horizontally-scrollable utility row. Scroll-aware edge fades appear automatically when the row overflows the viewport, indicating there's more content in either direction.
* **Collapse into Hamburger Menu** (default) — the top bar is hidden on mobile and its left and right menus are folded into the main hamburger drawer above the primary navigation.

**Upgrading from v1.1.5 or earlier:** Previous versions of the theme included a "Hide on Mobile" option that hid the top bar without rerouting its links. That option has been removed because hiding important utility links by default is poor UX. Sites with "Hide on Mobile" stored will automatically use "Collapse into Hamburger Menu" instead, so the top-bar links remain accessible. Your selection isn't overwritten in the database — you can re-select "Always Show at Top" anytime if you prefer the inline behavior.

= Is the mobile menu accessible? =

Yes. The hamburger drawer implements a complete keyboard focus trap (Tab cycles within the menu, Shift+Tab cycles backward), Escape closes the drawer, and aria-expanded state updates on open and close. The toggle distinguishes between keyboard activation (Enter or Space on the button) and mouse/touch activation — keyboard users have focus moved into the first menu item on open, while mouse users do not, which prevents an unwanted focus jump after a tap.

= Is this theme compatible with page builders? =

This theme is optimized for the Promptless WP page builder. It may work with other page builders, but full compatibility is not guaranteed.

= How do I create a landing page without the header and footer? =

Use the Promptless WP plugin's Canvas display mode, which provides a full-screen layout without the header and footer.

= Does this theme work with WooCommerce? =

Yes. The theme includes WooCommerce-aware styling that inherits colors and typography from the Promptless WP plugin's Global Settings. The cart icon in the header can be configured to either open a mini-cart dropdown or link directly to the cart page (Appearance > Customize > Header > Cart). Mobile cart, checkout, and account pages render as grid-based cards instead of overflowing the viewport with default WooCommerce table styling. WooCommerce assets are only loaded on shop-related pages to keep non-shop pages fast.

== Changelog ==

= 1.2.1 =
* Added: Second Header CTA Button - configure a secondary call-to-action button with its own label, URL, and style variant (solid or outline)
* Added: Mobile CTA Placement option - choose whether secondary CTA appears next to primary in desktop header or only inside the mobile hamburger menu

= 1.2.0 =
* Added: `promptless_archive_card_show_date` and `promptless_archive_card_show_author` filter hooks in `promptless_post_meta_with_categories()` (rendered by the archive card template). Both default to `true` so existing sites behave unchanged. Plugins (notably Post Runtime Engine) can return `false` per-post or per-CPT to suppress the post create-date and/or author byline on archive cards — useful when a CPT exposes its own meaningful date through a custom field (an event's `event_date`, a session's start time) and showing both is duplicative.
* Improved: When all three meta items (date, author, categories) are suppressed by filters, the wrapping `<div class="aisb-postgrid__metadata">` is also skipped so the card markup stays semantically clean.

= 1.1.9 =
* Improved: Footer container padding now aligns with the Promptless WP plugin's section padding tokens (--aisb-section-space-lg on desktop, --aisb-section-space-md on mobile) so footer content lines up flush with sections built by the plugin instead of using a separate spacing scale
* Improved: Header menu alignment across all variants (default single-row, stacked two-row, top-bar utility menus) — menu items now align with the site title's left edge instead of inheriting the unrelated default theme spacing
* Improved: Announcement bar CTA button now inherits the active button cascade from the Promptless WP plugin (color, hover behavior, border radius adapt to global settings) while preserving its compact size — previously the button rendered with hard-coded styling that didn't adapt to brand color changes

= 1.1.8 =
* Improved: Mobile menu breakpoint handling for better responsive behavior

= 1.1.7 =
* Added: Top-level announcement bar. A promotional bar rendered at the very top of every page (above the existing Top Bar + Header) for event-countdown, registration-window, or general announcement use cases. Configured via Appearance → Customize → Announcement Bar — separate light/dark theme variants that inherit the plugin's active palette, optional CTA button (text + URL), optional dismissible toggle, and optional start/end datetime fields for scheduled visibility. Schedule check runs server-side (PHP, evaluated against the WP site timezone) so there is no flash-of-bar-then-disappear and no client-clock-skew bugs. Dismissal uses a sha1 hash of the message HTML embedded in the cookie name — editing the message text auto-invalidates prior dismissals and visitors see the new message automatically. Conditionally enqueues its own CSS + dismiss JS only when the bar actually renders. Compatible with the Promptless WP plugin connector (`wordpress_announcement_bar_get` / `wordpress_announcement_bar_set` tools available when the plugin is active).

= 1.1.6 =
* Added: "Always Show at Top" option for Top Bar Mobile Behavior — keeps the utility bar visible above the header on mobile as a horizontally-scrollable row with scroll-aware edge fades that indicate when more content is scrolled off either side
* Added: Archive cards now respect the Promptless WP plugin's global Card CTA Style setting (Global Settings → Borders → Card CTA Style), switching the "Read more" link between text-plus-arrow and compact button styles consistently with PostGrid sections. Falls back to a plain text link when the plugin is not active.
* Added: Mobile cart, checkout, and account pages render as clean grid-based cards on phone-sized viewports instead of using the default WooCommerce table layout that overflows narrow screens. Desktop behavior is unchanged.
* Added: Documentation comment in customizer-preview.js explaining the intentional .html() usage for the HTML-capable Footer Brand Description (admin-only setting, wp_kses_post sanitize callback, frontend uses the same sanitizer).
* Changed: Top Bar Mobile Behavior default is now "Collapse into Hamburger Menu" (was "Hide on Mobile"). The "Hide on Mobile" option has been removed because hiding important utility links by default is poor UX. Sites with "hide" stored in the database are migrated to "collapse" at runtime so their top-bar links remain accessible via the hamburger drawer. The database value is not overwritten — users can re-select "Always Show at Top" anytime.
* Improved: Mobile menu toggle now distinguishes keyboard activation (Enter/Space) from mouse/touch activation. Keyboard users still get focus moved into the first menu item on open (WCAG 2.4.3); mouse users no longer get an unexpected focus jump after a tap.
* Improved: Programmatic focus on menu open uses { preventScroll: true } so the viewport doesn't shift when focus moves into the drawer.
* Fixed: Text domain consistency — 72 customizer strings that previously used the 'promptless-theme' text domain now use 'promptless' to match the theme's declared text domain in style.css. Translation .po/.mo files will now apply to the customizer UI strings.

= 1.1.5 =
* Added: Plugin bridge class for improved Promptless WP plugin integration
* Improved: WooCommerce body class filter now uses named function for child theme compatibility
* Improved: Header, footer, archive, and WooCommerce CSS refinements
* Improved: Simplified canvas and fullwidth template code

= 1.1.4 =
* Added: Color mode options for stacked header variant
* Fixed: Sticky menu for stacked header

= 1.1.3 =
* Added: New stacked header variant layout option
* Improved: Customizer UI cleanup
* Fixed: Bottom padding in main content wrapper

= 1.1.2 =
* Fixed: 404 page now properly loads archive styles for correct layout and formatting

= 1.1.1 =
* Performance: WooCommerce assets now conditionally loaded only on shop pages
* Performance: Theme WooCommerce CSS only loads when needed (saves ~116KB on non-shop pages)
* Performance: WooCommerce core scripts/styles dequeued on non-shop pages (saves ~150KB+)
* Added: New `promptless_needs_woocommerce_assets()` helper function for smart asset detection

= 1.1.0 =
* Performance: Conditional CSS loading - archive.css only loads on blog/archive pages
* Fixed: Page header spacing now properly applied on all pages (Cart, Checkout, etc.)
* Added: CSS/JS minification build system
* Added: Release automation script and documentation

= 1.0.9 =
* Fixed: Page header padding now displays correctly on WooCommerce pages (Cart, Checkout, My Account)
* Fixed: Archive styles properly load on all WordPress pages

= 1.0.8 =
* Performance optimization improvements
* Fixed button size inconsistency
* Added neo brutalist variant stylization
* Updated WooCommerce single product page table styling
* Fixed page title link styling

= 1.0.7 =
* Fixed: Mobile menu topbar items now receive keyboard focus when tabbing
* Improved: Complete focus trap for mobile menu (Tab from toggle goes to first nav item)

= 1.0.6 =
* Fixed: Featured images now show visible focus indicator during keyboard navigation
* Fixed: Mobile navigation now traps keyboard focus within menu while open
* Fixed: Skip to content link now fully visible and not cut off
* Fixed: Error messages now translation-ready with proper text domain
* Removed: Direct error_log() calls per WordPress theme guidelines

= 1.0.5 =
* Fixed: Comments section now properly contained within page layout width
* Fixed: Comments alignment matches page content on all singular templates

= 1.0.4 =
* Fixed: Comments section now properly aligns with page content width
* Fixed: Post titles in archives are now clickable
* Fixed: Featured images in archives are now clickable and keyboard accessible
* Improved: Better accessibility for archive card links

= 1.0.3 =
* Fixed: Long titles now wrap properly with overflow-wrap and word-break properties
* Fixed: Dynamic CSS now uses wp_add_inline_style() per WordPress.org guidelines
* Fixed: Mobile menu first item showing focus highlight when menu opens
* Fixed: Home menu item incorrectly highlighted on all pages when using custom link
* Changed: Navigation links now use :focus-visible instead of :focus for better UX
* Changed: Updated Tested up to WordPress 6.9
* Removed: Theme URI from style.css header
* Removed: accessibility-ready tag (requires full audit)

= 1.0.2 =
* Synchronized version numbers across all theme files

= 1.0.0 =
* Initial release

== Resources ==

* Theme by Promptless WP - https://promptlesswp.com
* Feather Icons used for dark mode toggle - https://feathericons.com (MIT License)

== Copyright ==

Promptless, Copyright 2025 Promptless WP
Promptless is distributed under the terms of the GNU GPL

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
