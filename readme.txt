=== Promptless ===
Contributors: promptlesswp
Tags: one-column, custom-logo, custom-menu, featured-images, full-width-template, block-styles, wide-blocks, blog, portfolio
Requires at least: 6.6
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.3.4
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

= 1.3.4 =
* Fixed: the header no longer runs off the screen at 320px, which is what a 1280px desktop looks like at 400% zoom (WCAG 1.4.10).
* Fixed: "Skip to content" now moves keyboard focus instead of only scrolling, so the next Tab continues from the content.
* Fixed: archive card titles are now h2 rather than h3, so archives no longer skip a heading level.
* Fixed: the primary navigation now has an accessible name, so it can be told apart from a post grid's pagination in the landmark list.
* Fixed: the announcement bar's dismiss button has a visible keyboard focus ring again.

= 1.3.3 =
* Fixed: a faint hairline appeared under the announcement bar on screens narrower than 640px.
* Improved: the theme now supplies its own fallback values for nine Promptless WP design tokens, so it renders correctly when the plugin is inactive or an older version is installed.

= 1.3.2 =
* New: Light/dark logo variants. Add an optional dark-background version of your logo (Appearance > Customize > Site Identity), and the header and footer each automatically use the version matching their own light or dark theme — so a dark header and a light footer can both show a correctly-contrasting logo. Optional footer-only logo overrides cover brands whose footer uses a different lockup. Fully optional; sites that don't set the new logos are unchanged.
* Fixed: on pages using the floating "pill" header in Float Over First Section (overlay) mode without breadcrumbs, the first section could render partly beneath the floating header when that section used a custom colour palette — the header's clearance was lost. The first section now reserves space for the floating header again on both mobile and desktop, across all section layouts (standard, boxed, and split-screen).

= 1.3.1 =
* Improved: all search forms now render through the theme's searchform.php template via get_search_form(), for consistent search markup and easier customization.

= 1.3.0 =
* New: Header Search — magnifying-glass trigger in the header actions (enable in Customizer or via the Connector), full-screen overlay with instant results as you type, full keyboard support ("/" or Cmd/Ctrl+K), and an accessible combobox for screen readers. Mobile gets a full-screen search sheet that stays above the keyboard
* New: Search works through any serving origin (local live links, staging proxies) and shows an honest "instant results unavailable" state with a working fallback when the results API is blocked
* New: Floating header overlay mode — the pill floats over the first section on eligible pages
* New: Archive card date/author display toggles in the Customizer (pages never show meta)
* Improved: Scrollable WooCommerce product tabs on mobile; all assets now cache-bust by file modification time
* Fixed: header search icon now shows on desktop when search is the only header action (no cart, no CTA)

= 1.2.8 =
* New: Breadcrumbs. An opt-in, hierarchy-based breadcrumb trail rendered between the site header and page content, following the WAI-ARIA APG breadcrumb pattern. Enable under Customize > Breadcrumbs; per-context toggles (pages, posts, custom post type singles, archives, search, 404), light/dark/inherit theme variant driven by Promptless WP design tokens, an editable Home label, and a per-page "Hide on this page" override for landing pages. Never shown on the front page; WooCommerce shop and product pages keep WooCommerce's own breadcrumb.
* New: BreadcrumbList structured data (JSON-LD) emitted with the visible trail, automatically suppressed when a dedicated SEO plugin (Yoast, Rank Math, AIOSEO, SEOPress, The SEO Framework, Slim SEO) is active so exactly one BreadcrumbList ever ships per page.
* New: promptless_after_header action - fires after the site header on every template that calls get_header(), giving child themes and integrations a sanctioned insertion point for chrome between the header and content. Filters: promptless_breadcrumbs_items, promptless_show_breadcrumbs, promptless_breadcrumbs_schema_enabled.
* New: Archive card image aspect ratio. The archive grid's featured-image crop (previously hardcoded 16:9) is now resolved through the promptless_archive_image_aspect filter - 16:9 (default), 4:3, 1:1, or 4:5 - matching the PostGrid section's aspect vocabulary. Promptless CPT Pages answers the filter per custom post type, so e.g. an agents directory renders square headshots while listings stay wide. Default behavior is unchanged.

Only the most recent releases are listed here; readme.txt truncates this section
at 5000 characters. The complete history lives in CHANGELOG.md in the theme
folder, and on the GitHub releases page.

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
