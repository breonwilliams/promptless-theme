# Changelog

All notable changes to the Promptless theme are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Entries up to and including 1.3.2 were migrated from `readme.txt`, which was the theme's only changelog until 1.3.3. Their wording is preserved as written; dates come from the release tags. `readme.txt` now carries a rolling window of the six most recent releases and points here for the rest.

## [Unreleased]

### Fixed

- **Archive card titles skipped a heading level.** Each result rendered as `h3`
  directly beneath the archive's `h1`, with no `h2` between, so a screen-reader
  user navigating by heading perceived a missing section. On an archive each
  result is a top-level item under the page title, so the card title is now
  `h2`. The size comes from `.aisb-features__item-title`, so this is a semantic
  change only — the card looks identical.

### Fixed

- **The skip link scrolled but did not move focus.** Activating "Skip to
  content" jumped the page to `<main id="main-content">`, but focus stayed on
  the document body, so the next Tab took a keyboard user straight back into
  the header — the link skipped nothing for the people who need it. `<main>`
  now carries `tabindex="-1"` in all nine templates that render it, including
  `aisb-fullwidth.php`, which is the one that renders every Promptless page.

- **The announcement bar's dismiss button had no visible focus indicator.**
  `outline: none` was grouped onto a shared `:hover, :focus-visible` rule, so a
  keyboard user got the hover background tint and nothing else. A background
  shift alone is not a focus indicator (WCAG 2.4.7, Level AA). Focus now has
  its own ring in `currentColor` so it stays visible on both the light and
  dark bar.

## [1.3.4] - 2026-09-06

### Fixed

- **The header ran off the screen at 320px** — a 1280px desktop at 400% zoom,
  which is what a low-vision user actually does, not a phone width. The row did
  not fit and the menu toggle was pushed past the right edge where no scroll
  reached it, leaving the navigation unreachable on every page (WCAG 1.4.10).
  The pill's horizontal padding is now a custom property so the breakpoint
  stylesheet can override it at equal specificity.

- **"Skip to content" scrolled without moving focus.** A `<main>` is not
  focusable by default, so the browser moved the viewport and left focus where
  it was — the next Tab returned the user to the top of the navigation. The
  link skipped nothing, which axe cannot see. `tabindex="-1"` on every
  skip-link target, verified by ACTIVATING the link rather than by its presence.

- **Archive card titles were h3 directly beneath the page h1**, with nothing
  emitting an h2 between them, so every archive skipped a level. On an archive
  each result IS a top-level item under the page title, so h2 is the correct
  level rather than a workaround. Purely semantic — the size comes from
  `.aisb-features__item-title` and the card renders identically.

- **The primary navigation had no accessible name.** One unnamed `<nav>` is
  unambiguous; on any page that also renders a post grid's pager the landmark
  list read "navigation, navigation" with no way to tell the site menu from the
  pager. Uses `wp_nav_menu`'s `container_aria_label`.

- **The announcement bar's dismiss button lost its keyboard focus ring.**
  `outline: none` had been grouped onto the hover rule, so a keyboard user got
  the hover tint and nothing else — a background shift alone is not a focus
  indicator (WCAG 2.4.7).

### Added

- **`tests/test-accessibility.php`** — a standalone accessibility contract test,
  25 assertions, wired into CI. It checks the BUILT `header.min.css` as well as
  the source, because a fix present in the source and absent from the build
  ships as no fix at all. It also guards the archive heading ladder at both
  ends: the card's h2 and the templates' h1, since guarding one end does not
  hold the relationship.

## [1.3.3] — 2026-09-02

- Fixed: a faint hairline appeared under the announcement bar on screens narrower than 640px.
- Improved: the theme now supplies its own fallback values for nine Promptless WP design tokens, so it renders correctly when the plugin is inactive or an older version is installed.

## [1.3.2] — 2026-08-22

- New: Light/dark logo variants. Add an optional dark-background version of your logo (Appearance > Customize > Site Identity), and the header and footer each automatically use the version matching their own light or dark theme — so a dark header and a light footer can both show a correctly-contrasting logo. Optional footer-only logo overrides cover brands whose footer uses a different lockup. Fully optional; sites that don't set the new logos are unchanged.
- Fixed: on pages using the floating "pill" header in Float Over First Section (overlay) mode without breadcrumbs, the first section could render partly beneath the floating header when that section used a custom colour palette — the header's clearance was lost. The first section now reserves space for the floating header again on both mobile and desktop, across all section layouts (standard, boxed, and split-screen).

## [1.3.1] — 2026-08-12

- Improved: all search forms now render through the theme's searchform.php template via get_search_form(), for consistent search markup and easier customization.

## [1.3.0] — 2026-08-07

- New: Header Search — magnifying-glass trigger in the header actions (enable in Customizer or via the Connector), full-screen overlay with instant results as you type, full keyboard support ("/" or Cmd/Ctrl+K), and an accessible combobox for screen readers. Mobile gets a full-screen search sheet that stays above the keyboard
- New: Search works through any serving origin (local live links, staging proxies) and shows an honest "instant results unavailable" state with a working fallback when the results API is blocked
- New: Floating header overlay mode — the pill floats over the first section on eligible pages
- New: Archive card date/author display toggles in the Customizer (pages never show meta)
- Improved: Scrollable WooCommerce product tabs on mobile; all assets now cache-bust by file modification time
- Fixed: header search icon now shows on desktop when search is the only header action (no cart, no CTA)

## [1.2.8] — 2026-07-16

- New: Breadcrumbs. An opt-in, hierarchy-based breadcrumb trail rendered between the site header and page content, following the WAI-ARIA APG breadcrumb pattern. Enable under Customize > Breadcrumbs; per-context toggles (pages, posts, custom post type singles, archives, search, 404), light/dark/inherit theme variant driven by Promptless WP design tokens, an editable Home label, and a per-page "Hide on this page" override for landing pages. Never shown on the front page; WooCommerce shop and product pages keep WooCommerce's own breadcrumb.
- New: BreadcrumbList structured data (JSON-LD) emitted with the visible trail, automatically suppressed when a dedicated SEO plugin (Yoast, Rank Math, AIOSEO, SEOPress, The SEO Framework, Slim SEO) is active so exactly one BreadcrumbList ever ships per page.
- New: promptless_after_header action - fires after the site header on every template that calls get_header(), giving child themes and integrations a sanctioned insertion point for chrome between the header and content. Filters: promptless_breadcrumbs_items, promptless_show_breadcrumbs, promptless_breadcrumbs_schema_enabled.
- New: Archive card image aspect ratio. The archive grid's featured-image crop (previously hardcoded 16:9) is now resolved through the promptless_archive_image_aspect filter - 16:9 (default), 4:3, 1:1, or 4:5 - matching the PostGrid section's aspect vocabulary. Promptless CPT Pages answers the filter per custom post type, so e.g. an agents directory renders square headshots while listings stay wide. Default behavior is unchanged.

## [1.2.7] — 2026-07-11

- Improved: Mobile menu drawer spacing is now pixel-balanced across every layout the drawer supports (CTA button in the top, middle, or bottom slot; top-bar menus present or absent) - uniform item rhythm, all rows at a 44px tap-target height, and the top-bar text, menu text, and CTA button aligned to the same left inset.
- Fixed: The mobile menu button no longer has doubled spacing above the top-bar items when positioned at the top of the drawer.
- Fixed: Archive card images and header mini-cart thumbnails no longer collapse when an image optimizer (e.g. EWWW WebP delivery) wraps images in <picture> tags.
- Fixed: Native browser control chrome (number spinner arrows in the mini-cart quantity, dropdown panels, checkboxes, scrollbars) now renders in dark colors inside dark header/footer components instead of the light page default.

## [1.2.6] — 2026-07-04

- Fixed: Critical — the v1.2.5 distribution package was built with a flattened directory structure (files from `inc/`, `assets/`, `template-parts/`, and `woocommerce/` were placed at the theme root), causing a fatal error on activation/update: `require_once(): Failed opening required '.../inc/class-promptless-setup.php'`. The theme code itself was unaffected; this release repackages the identical 1.2.5 source with the correct directory structure. If your site is running the broken 1.2.5 package, update to 1.2.6 immediately (or reinstall the theme).

## [1.2.5] — 2026-07-03

- Performance: The site custom logo no longer downloads its full-resolution source. WordPress derives the logo's `sizes` attribute from the source image width (e.g. a 1100px logo → `(max-width: 1100px) 100vw, 1100px`), which made the browser fetch the largest srcset candidate — a ~540 KiB, 1100×1100 file for a logo that renders at ~40px (PageSpeed "Improve image delivery"). `enhance_logo_accessibility()` now also rewrites `sizes` to the logo's real rendered slot width, computed from the registered custom-logo height × the attachment's aspect ratio (so it's correct for square and wide/wordmark logos with no blur). The browser now picks a small candidate (≈150–300w) instead of the full source. SVG logos (no pixel metadata) are left untouched. (`inc/class-promptless-setup.php`.)
- Accessibility: Archive card featured-image links now have an accessible name. The image link wrapped `the_post_thumbnail()`, whose alt comes from the media library and is frequently empty, leaving the `<a>` with no discernible name (Lighthouse Accessibility "Links do not have a discernible name"). The link now carries `aria-label="{post title}"`, matching the plugin's PostGrid card image link. (`template-parts/archive/card.php`.)
- SEO: The announcement-bar CTA now passes the "descriptive link text" audit regardless of its (author-set) button label. A generic label like "Learn more" was flagged because the audit reads a link's rendered text; the CTA now appends the announcement message as visually-hidden context (`.aisb-visually-hidden`), so its rendered text reads "{label}: {message}" — descriptive for crawlers, invisible on screen, capped to a sane length. (`inc/template-functions.php`.)
- SEO: Archive "Read more" card links now also pass Lighthouse's "Links do not have descriptive text" audit. The links already had a disambiguated `aria-label` for screen readers, but that SEO audit reads a link's rendered text, not its `aria-label`, so a visible "Read more" was still flagged. When Promptless WP is active the plugin's `aisb_render_card_cta()` now appends the post title in a visually-hidden span (making the rendered text descriptive); the theme's plugin-inactive fallback link does the same directly in `template-parts/archive/card.php`. The span is hidden via a new `.aisb-visually-hidden` utility class in `style.css` (mirrors the plugin's identical class), so archive cards hide the context correctly even when the plugin's frontend CSS isn't enqueued on the page.

## [1.2.4] — 2026-06-29

- Fixed: Header (default/inline layout) — a right-aligned navigation now sits flush with the container edge when there are no header actions (no cart, no CTA), instead of being inset by the empty actions slot. Center/left alignment and headers with a cart/CTA are unchanged.
- Fixed: Header dropdowns — a top-level menu item near the right edge no longer lets its submenu overflow the viewport and cause horizontal scrolling; the dropdown now flips to open leftward (matching the existing mega-menu behavior). Desktop only; the mobile drawer is unaffected.
- Accessibility: Archive "Read more" card links now carry a disambiguated accessible name — "Read more: {Post title}" — so a screen-reader user navigating by the links list can tell the cards apart (WCAG 2.4.4 Link Purpose; the visible "Read more" stays the prefix to satisfy 2.5.3 Label in Name). When Promptless WP is active this is supplied via its `aisb_render_card_cta()` public API's new `aria_context` option; the plugin-inactive fallback link applies the same label directly.

## [1.2.3] — 2026-06-18

- Fixed: Footer fine print (copyright, tagline, brand text, and bottom navigation links) now meets WCAG 2.2 AA contrast on tinted footer surfaces. These elements now use the smart "surface muted" color token (with a graceful fallback), so muted text on the footer surface is always dark enough to stay accessible. Requires Promptless WP 1.4.2+ for the token; older plugin versions fall back to the previous color.

## [1.2.2] — 2026-06-15

- Added: Mega Menu support - configure mega menu layouts directly in the WordPress menu editor with column settings and featured content areas
- Added: "No Border" global stylization option - choose between bordered and borderless design styles that integrate with the Promptless WP plugin's Global Settings
- Improved: Border global stylization now works seamlessly across all theme components
- Improved: Mega menu styling polish for consistent visual appearance
- Fixed: Mini-cart dropdown spacing rhythm now properly balanced in header

## [1.2.1] — 2026-06-04

- Added: Second Header CTA Button - configure a secondary call-to-action button with its own label, URL, and style variant (solid or outline)
- Added: Mobile CTA Placement option - choose whether secondary CTA appears next to primary in desktop header or only inside the mobile hamburger menu

## [1.2.0] — 2026-05-22

- Added: `promptless_archive_card_show_date` and `promptless_archive_card_show_author` filter hooks in `promptless_post_meta_with_categories()` (rendered by the archive card template). Both default to `true` so existing sites behave unchanged. Plugins (notably Post Runtime Engine) can return `false` per-post or per-CPT to suppress the post create-date and/or author byline on archive cards — useful when a CPT exposes its own meaningful date through a custom field (an event's `event_date`, a session's start time) and showing both is duplicative.
- Improved: When all three meta items (date, author, categories) are suppressed by filters, the wrapping `<div class="aisb-postgrid__metadata">` is also skipped so the card markup stays semantically clean.

## [1.1.9] — 2026-05-20

- Improved: Footer container padding now aligns with the Promptless WP plugin's section padding tokens (--aisb-section-space-lg on desktop, --aisb-section-space-md on mobile) so footer content lines up flush with sections built by the plugin instead of using a separate spacing scale
- Improved: Header menu alignment across all variants (default single-row, stacked two-row, top-bar utility menus) — menu items now align with the site title's left edge instead of inheriting the unrelated default theme spacing
- Improved: Announcement bar CTA button now inherits the active button cascade from the Promptless WP plugin (color, hover behavior, border radius adapt to global settings) while preserving its compact size — previously the button rendered with hard-coded styling that didn't adapt to brand color changes

## [1.1.8] — 2026-05-17

- Improved: Mobile menu breakpoint handling for better responsive behavior

## [1.1.7] — 2026-05-15

- Added: Top-level announcement bar. A promotional bar rendered at the very top of every page (above the existing Top Bar + Header) for event-countdown, registration-window, or general announcement use cases. Configured via Appearance → Customize → Announcement Bar — separate light/dark theme variants that inherit the plugin's active palette, optional CTA button (text + URL), optional dismissible toggle, and optional start/end datetime fields for scheduled visibility. Schedule check runs server-side (PHP, evaluated against the WP site timezone) so there is no flash-of-bar-then-disappear and no client-clock-skew bugs. Dismissal uses a sha1 hash of the message HTML embedded in the cookie name — editing the message text auto-invalidates prior dismissals and visitors see the new message automatically. Conditionally enqueues its own CSS + dismiss JS only when the bar actually renders. Compatible with the Promptless WP plugin connector (`wordpress_announcement_bar_get` / `wordpress_announcement_bar_set` tools available when the plugin is active).

## [1.1.6] — 2026-05-12

- Added: "Always Show at Top" option for Top Bar Mobile Behavior — keeps the utility bar visible above the header on mobile as a horizontally-scrollable row with scroll-aware edge fades that indicate when more content is scrolled off either side
- Added: Archive cards now respect the Promptless WP plugin's global Card CTA Style setting (Global Settings → Borders → Card CTA Style), switching the "Read more" link between text-plus-arrow and compact button styles consistently with PostGrid sections. Falls back to a plain text link when the plugin is not active.
- Added: Mobile cart, checkout, and account pages render as clean grid-based cards on phone-sized viewports instead of using the default WooCommerce table layout that overflows narrow screens. Desktop behavior is unchanged.
- Added: Documentation comment in customizer-preview.js explaining the intentional .html() usage for the HTML-capable Footer Brand Description (admin-only setting, wp_kses_post sanitize callback, frontend uses the same sanitizer).
- Changed: Top Bar Mobile Behavior default is now "Collapse into Hamburger Menu" (was "Hide on Mobile"). The "Hide on Mobile" option has been removed because hiding important utility links by default is poor UX. Sites with "hide" stored in the database are migrated to "collapse" at runtime so their top-bar links remain accessible via the hamburger drawer. The database value is not overwritten — users can re-select "Always Show at Top" anytime.
- Improved: Mobile menu toggle now distinguishes keyboard activation (Enter/Space) from mouse/touch activation. Keyboard users still get focus moved into the first menu item on open (WCAG 2.4.3); mouse users no longer get an unexpected focus jump after a tap.
- Improved: Programmatic focus on menu open uses { preventScroll: true } so the viewport doesn't shift when focus moves into the drawer.
- Fixed: Text domain consistency — 72 customizer strings that previously used the 'promptless-theme' text domain now use 'promptless' to match the theme's declared text domain in style.css. Translation .po/.mo files will now apply to the customizer UI strings.

## [1.1.5] — 2026-05-11

- Added: Plugin bridge class for improved Promptless WP plugin integration
- Improved: WooCommerce body class filter now uses named function for child theme compatibility
- Improved: Header, footer, archive, and WooCommerce CSS refinements
- Improved: Simplified canvas and fullwidth template code

## [1.1.4] — 2026-05-04

- Added: Color mode options for stacked header variant
- Fixed: Sticky menu for stacked header

## [1.1.3] — 2026-04-26

- Added: New stacked header variant layout option
- Improved: Customizer UI cleanup
- Fixed: Bottom padding in main content wrapper

## [1.1.2] — 2026-04-21

- Fixed: 404 page now properly loads archive styles for correct layout and formatting

## [1.1.1] — 2026-04-17

- Performance: WooCommerce assets now conditionally loaded only on shop pages
- Performance: Theme WooCommerce CSS only loads when needed (saves ~116KB on non-shop pages)
- Performance: WooCommerce core scripts/styles dequeued on non-shop pages (saves ~150KB+)
- Added: New `promptless_needs_woocommerce_assets()` helper function for smart asset detection

## [1.1.0] — 2026-04-15

- Performance: Conditional CSS loading - archive.css only loads on blog/archive pages
- Fixed: Page header spacing now properly applied on all pages (Cart, Checkout, etc.)
- Added: CSS/JS minification build system
- Added: Release automation script and documentation

## [1.0.9]

- Fixed: Page header padding now displays correctly on WooCommerce pages (Cart, Checkout, My Account)
- Fixed: Archive styles properly load on all WordPress pages

## [1.0.8]

- Performance optimization improvements
- Fixed button size inconsistency
- Added neo brutalist variant stylization
- Updated WooCommerce single product page table styling
- Fixed page title link styling

## [1.0.7]

- Fixed: Mobile menu topbar items now receive keyboard focus when tabbing
- Improved: Complete focus trap for mobile menu (Tab from toggle goes to first nav item)

## [1.0.6]

- Fixed: Featured images now show visible focus indicator during keyboard navigation
- Fixed: Mobile navigation now traps keyboard focus within menu while open
- Fixed: Skip to content link now fully visible and not cut off
- Fixed: Error messages now translation-ready with proper text domain
- Removed: Direct error_log() calls per WordPress theme guidelines

## [1.0.5]

- Fixed: Comments section now properly contained within page layout width
- Fixed: Comments alignment matches page content on all singular templates

## [1.0.4]

- Fixed: Comments section now properly aligns with page content width
- Fixed: Post titles in archives are now clickable
- Fixed: Featured images in archives are now clickable and keyboard accessible
- Improved: Better accessibility for archive card links

## [1.0.3]

- Fixed: Long titles now wrap properly with overflow-wrap and word-break properties
- Fixed: Dynamic CSS now uses wp_add_inline_style() per WordPress.org guidelines
- Fixed: Mobile menu first item showing focus highlight when menu opens
- Fixed: Home menu item incorrectly highlighted on all pages when using custom link
- Changed: Navigation links now use :focus-visible instead of :focus for better UX
- Changed: Updated Tested up to WordPress 6.9
- Removed: Theme URI from style.css header
- Removed: accessibility-ready tag (requires full audit)

## [1.0.2]

- Synchronized version numbers across all theme files

## [1.0.0]

- Initial release
