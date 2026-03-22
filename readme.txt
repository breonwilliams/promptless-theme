=== Promptless ===
Contributors: promptlesswp
Tags: one-column, custom-logo, custom-menu, featured-images, full-width-template, block-styles, wide-blocks, blog, portfolio
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.7
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A minimal WordPress theme designed to work seamlessly with the Promptless WP plugin.

== Description ==

Promptless is a clean, minimal WordPress theme built specifically as a companion to the Promptless WP page builder plugin. It inherits all styling—colors, typography, and border radius—from the plugin's Global Settings, creating a unified brand experience across your entire website.

**Key Features:**

* **Global Settings Integration** - Header, footer, and archive pages automatically inherit colors and typography from the Promptless WP plugin
* **Dark Mode Support** - Built-in dark mode toggle that respects system preferences and user choice
* **Clean, Minimal Design** - Distraction-free layouts that let your content shine
* **Accessibility Ready** - Skip links, proper focus states, ARIA labels, and keyboard navigation
* **Mobile Responsive** - Looks great on all devices with a clean mobile navigation
* **Gutenberg Ready** - Full support for the block editor with matching styles
* **Fast & Lightweight** - No bloat, no unnecessary features, just what you need

**Perfect For:**

* Portfolios
* Business websites
* Blogs
* Agency sites
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
3. Assign the menu to a location:
   - Primary Menu: Main navigation in the header
   - Footer Menu: Links in the footer bottom bar
   - Footer Column 1: First navigation column
   - Footer Column 2: Second navigation column
4. Click "Save Menu"

**Header CTA Button:**
1. Go to Appearance > Customize > Promptless Theme > Header
2. Enter your CTA button text
3. Enter the URL for the button
4. Click "Publish"

== Frequently Asked Questions ==

= Does this theme require the Promptless WP plugin? =

No, the theme works without the plugin. However, for the best experience with automatic styling integration, we recommend using the Promptless WP plugin.

= How do I change colors? =

When using the Promptless WP plugin, colors are managed in the plugin's Global Settings. The theme automatically inherits these colors. Without the plugin, the theme uses sensible defaults.

= How do I enable dark mode? =

Dark mode is enabled by default. Users can click the sun/moon icon in the header to toggle between light and dark mode. The theme also respects system preferences.

= Is this theme compatible with page builders? =

This theme is optimized for the Promptless WP page builder. It may work with other page builders, but full compatibility is not guaranteed.

= How do I create a landing page without the header and footer? =

Use the Promptless WP plugin's Canvas display mode, which provides a full-screen layout without the header and footer.

== Changelog ==

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
