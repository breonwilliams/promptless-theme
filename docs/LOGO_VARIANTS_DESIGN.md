# Logo Variants (Light/Dark + Optional Footer Override) — Design Contract

**Status:** Design contract — approved direction, pre-implementation
**Date:** 2026-08-22
**Owner surface:** promptless-theme (Customizer + header/footer templates); token ties to Promptless WP Global Settings
**Related:** `docs/FLOATING_HEADER_OVERLAY.md` (founder overlay decision), `inc/template-functions.php` (variant resolvers + `promptless_site_logo()`), `inc/class-promptless-setup.php` (logo a11y + right-sizing), AISB connector theme-chrome tools

---

## 1. Problem

The theme renders ONE site logo (WordPress core custom logo) across three chrome
contexts — floating header, stacked header, footer — through a single function,
`promptless_site_logo()`. Each of those contexts carries an INDEPENDENT light/dark
theme variant (`promptless_header_theme`, `promptless_footer_theme`, both default
`light`), skinned by the shared `aisb-section--{light|dark}` tokens.

A single logo cannot satisfy opposing variants. When the header is dark and the
footer is light (a common pairing), the brand needs a light-on-dark mark up top
and a dark-on-light mark below. One asset is only ever correct in one of them.

### 1.1 The reframe: contrast, not placement
The visible symptom is "different logo in header vs footer," but the real driver
is CONTRAST against the container surface — and that surface is light or dark per
the context's theme variant. Modeling the feature on the light/dark axis (not the
header/footer axis) is correct by construction and survives a later variant flip;
a placement-keyed "header logo / footer logo" silently clashes the day someone
changes a context's variant.

### 1.2 The secondary, real-but-rarer need
Some brands ship a genuinely DIFFERENT footer lockup (a full stacked wordmark with
legal marks in the footer vs a compact horizontal mark in the header). That is a
PLACEMENT concern, orthogonal to contrast, industry-dependent, and is handled here
as an OPTIONAL layer — never the base model.

---

## 2. What exists today
- Logo: WP core custom logo (`the_custom_logo()` / `has_custom_logo()`), theme_mod
  `custom_logo` (attachment ID). Rendered by `promptless_site_logo()`
  (`inc/template-functions.php`), called at `header.php:40` (floating),
  `header.php:74` (stacked), `footer.php:19`. Falls back to a linked site-title.
- A11y + performance already applied to the core logo via the
  `get_custom_logo_image_attributes` filter (`class-promptless-setup.php`):
  `role="img"`, and a right-sized `sizes` attribute computed from the registered
  logo height (PageSpeed "improve image delivery"); SVG logos are skipped.
- Variant resolvers already exist and return `light|dark` — the SAME resolvers
  that pick each context's `aisb-section--{variant}` chrome class:
  - `promptless_get_header_theme()`  (default light)
  - `promptless_get_footer_theme()`  (default light)
  - `promptless_get_header_nav_theme()` (stacked NAV ROW only; falls back to header
    theme) — governs the nav bar, NOT the logo.

---

## 3. Relationship to the floating-overlay founder decision (orthogonal)
`docs/FLOATING_HEADER_OVERLAY.md` records a 2026-07-24 decision: the overlay does
NOT get a transparent-state, section-matching, scroll-aware logo swap, because the
pill carries its own opaque surface — "Header Theme stays authoritative; no
auto-match."

That is a DIFFERENT feature from this one:
- Declined: a DYNAMIC logo that matches the underlying first section and swaps on
  scroll (the transparent-header marketing pattern).
- This contract: a STATIC pair of brand logos (light-bg + dark-bg); each context
  renders the one matching ITS OWN resolved variant.

The overlay pill resolves to Header Theme like every other header layout, so it
needs NO special-casing here: it renders the header's variant logo. This contract
is fully consistent with the founder decision and does not reopen it.

---

## 4. Model — variant-keyed base + optional footer override

Two orthogonal axes:
- Variant (light/dark background): ALWAYS in play.
- Placement (header vs footer lockup): OPTIONAL, footer-only override.

### 4.1 Assets
- Base default logo = existing WP core `custom_logo`. Treated as the LIGHT-background
  logo (matches the header/footer `light` defaults; zero migration).
- Base dark logo (new, optional): `promptless_logo_dark` — dark-background version
  of the same mark.
- Footer override (new, optional): `promptless_footer_logo_light` and
  `promptless_footer_logo_dark` — used ONLY by the footer, only when set.

### 4.2 Resolution chain (per context, resolved variant V in {light, dark})

Header (and any non-footer context):
  1. V == dark AND `promptless_logo_dark` set  → dark base logo
  2. else                                       → core `custom_logo` (base default)
  3. else (no core logo)                        → linked site-title text

Footer:
  1. `promptless_footer_logo_{V}` set           → footer override for V
  2. else V == dark AND `promptless_logo_dark`  → dark base logo
  3. else                                       → core `custom_logo`
  4. else                                       → linked site-title text

Every layer is a light/dark-aware choice, so contrast is always correct regardless
of a later variant flip. COMMON case: set 0–1 extra assets (a dark base logo) and
never touch footer controls. EDGE case: distinct footer mark → set the footer pair.

---

## 5. Settings & storage
- New theme_mods (attachment IDs, default 0):
  `promptless_logo_dark`, `promptless_footer_logo_light`, `promptless_footer_logo_dark`.
- Controls: `WP_Customize_Media_Control` (`mime_type => 'image'`), same picker core
  uses for the logo.
- Placement:
  - `promptless_logo_dark` → Site Identity, beside the core Logo control (the two
    base logos read as a pair).
  - Footer override pair → the existing `promptless_footer_appearance` section,
    beneath Footer Theme, introduced with a description that marks them OPTIONAL
    (progressive disclosure — out of the common path).
- Labels (contrast-explicit; avoid "dark mode", which is ambiguous about WHICH is dark):
  - `promptless_logo_dark`: **"Logo — dark backgrounds"**, help: "Shown wherever the
    header or footer uses the Dark theme. Falls back to your main logo when empty."
  - Footer pair: **"Footer logo — light backgrounds"** / **"Footer logo — dark
    backgrounds"**, help: "Optional. Overrides the site logo in the footer only —
    for brands whose footer uses a different lockup. Falls back to the site logo."
- Transport: `refresh` (matches the existing variant settings and core logo).

---

## 6. Rendering
- Refactor `promptless_site_logo()` → `promptless_site_logo( $context = 'header' )`,
  $context in {header, footer}. It resolves the variant
  (`promptless_get_header_theme()` / `promptless_get_footer_theme()`), runs the §4.2
  chain, and:
  - Base default (core logo) → keep calling `the_custom_logo()` — preserves core
    srcset/markup byte-for-byte (no regression on the overwhelmingly common path).
  - Any chosen NON-core attachment (dark base, footer overrides) → render via
    `wp_get_attachment_image()` inside the same linked-home anchor + logo classes,
    applying the SAME `role="img"` and right-sized `sizes` the core-logo filter
    applies. REFACTOR the sizing/role logic out of the
    `get_custom_logo_image_attributes` filter into a shared helper both paths call,
    so core and alternate never drift.
  - No logo at all → existing site-title fallback.
- alt text: site name (parity with core).
- Call sites pass context: `header.php` → `'header'` (BOTH floating and stacked —
  the logo follows Header Theme, not nav theme); `footer.php` → `'footer'`.
- Preserve the wrapping `.custom-logo-link` / `.promptless-header__*` hooks so
  existing CSS (logo sizing, floating pill logo box) applies unchanged to alternates.

---

## 7. Edge cases (all must hold)
1. Existing site, nothing new set → byte-identical output everywhere (pure additive).
2. Header dark + only base logos → header uses dark base; footer (light) uses core
   logo. Two different images may load, one per context — expected.
3. Footer override set for only ONE variant → the other variant falls back per chain.
4. Stacked nav theme = dark while Header Theme = light → LOGO stays light (Header
   Theme); only the nav bar row is dark. Logo lives in the header/top row.
5. Floating overlay → pill = Header Theme; logo picks header variant. No special case.
6. SVG logo → skip right-sizing (as the core filter does) for alternates too.
7. Retina → `wp_get_attachment_image()` emits srcset for alternates.
8. A theme_mod pointing at a deleted attachment → `wp_get_attachment_image()` returns
   '' → treat as unset and fall through the chain.
9. Live preview → `refresh` transport re-renders on any of these changes.

---

## 8. Owner-synced vocabulary (do NOT skip)
Per the stack's owner-synced rule, the three new theme_mods must be reflected wherever
theme chrome is read/written:
- AISB connector theme-chrome service (the header/footer `theme_mods` read + write
  paths): add the new logo fields to the read payload AND the write allowlist — a
  field missing from the allowlist is SILENTLY DROPPED and reads as "saved with
  defaults".
- This doc + the theme readme changelog; relay restart + connector reconnect after
  the change (standard).

---

## 9. Backward compatibility
Fully additive and opt-in. No migration. Defaults reproduce current rendering exactly.
Existing `custom_logo` semantics unchanged.

---

## 10. Rejected alternatives
- CSS `filter: invert()` / `hue-rotate` auto-inversion of one logo: mangles colored /
  multi-color brand marks (a colored icon inverts to the wrong colors). Unacceptable.
- Full MANDATORY 4-slot matrix (header L/D × footer L/D) as the primary UI:
  over-engineered; forces four uploads to answer a two-asset question. Base + optional
  footer override reaches the same ceiling with a trivial common path.
- Transparent-state / scroll-aware auto-matching logo (the overlay pattern): declined
  by the founder (§3); not revisited.
- Symmetric HEADER override: DEFERRED — no demonstrated need (header is the primary
  placement using the base). The §4.2 chain is written so a header override is a
  trivial symmetric addition if it ever arises.

---

## 11. Accessibility
- Every rendered logo keeps `role="img"` and a linked home anchor with the site name
  as accessible name (parity with core).
- No reliance on color alone: contrast is AUTHORED INTO each asset for its surface,
  not computed. Only one logo per context renders (no decorative duplication).

---

## 12. Open decisions (need a human call)
1. **Release vehicle.** Recommend SEPARATE: keep the pending **1.3.2** as the focused,
   already-tested floating-overlay clearance fix (deployable now); ship this logo
   feature as **1.3.3** (a "new feature" minor). Keeps the changelog honest and lets
   the fix go out immediately rather than waiting on the feature.
2. **Customizer placement of the dark base logo.** Recommend Site Identity, beside the
   core Logo control (vs a new "Logos" section).
3. **Footer-override labeling.** Recommend the explicit "Footer logo — light/dark
   backgrounds" labels (vs "Alternate footer logo").
