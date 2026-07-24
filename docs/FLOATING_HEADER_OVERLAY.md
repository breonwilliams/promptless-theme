# Floating Header — Overlay Mode ("Float Over First Section")

Design + implementation record for the floating (pill) header's overlay
mode, shipped after v1.2.9. Exploration that produced these decisions:
2026-07-24 session (Astra/Kadence/island-nav pattern research).

## What it is

Opt-in Customizer toggle (Header Layout section → "Float Over First
Section", default OFF). When active, the floating pill overlays the top
of the page's first section instead of sitting in its own band above it —
the modern island-nav composition. Existing sites see zero change unless
the toggle is flipped.

## Why the pill needs no contrast machinery

Classic transparent headers need per-state logos, color forks, and scrims
because nav text sits directly on unknown content. The pill never does —
its surface, border, and shadow are self-contained, so it reads correctly
over dark and light first sections alike. Founder decisions (2026-07-24):
Header Theme stays authoritative (no auto-match to the first section's
theme_variant), and breadcrumbs SUPPRESS overlay rather than coexisting.

## Mechanics (header.css "FLOATING OVERLAY MODE")

- Negative bottom margin on the wrapper equal to
  `--promptless-header-height` pulls following content up underneath.
  The wrapper keeps its flow position, so sticky re-pinning, admin-bar
  offsets, topbar stacking, and drawer mechanics survive untouched.
- `--promptless-header-height` is measured by navigation.js
  (ResizeObserver; static 5.5rem fallback no-JS). Compensation consumes
  the SAME variable, so header and section can never disagree.
- First-section compensation is DELEGATED TO THE PLUGIN via the chrome
  offset contract: the theme publishes `--aisb-chrome-offset-top`
  (= measured header height) on `body.promptless-has-header-overlay`;
  AISB's `components/chrome-offset.css` absorbs it per section family —
  `max(normal padding, offset + space-lg)`, with zero-band variants
  (split-screen hero) absorbing it in their content column instead of a
  band. Evolution: v1 stacked band + header (read as a void); v2 used a
  theme-side `max()` (double-padded split-screen — the theme can't know
  which variants zero their band, and CSS can't sit between the base
  band rule and a variant's zero rule at equal specificity). REQUIRES
  Promptless WP ≥ 1.5.2. Older plugin: overlay renders but banded first
  sections get no extra cushion (~8px visual gap at default tokens on
  desktop; tighter on mobile) — acceptable degraded mode, upgrade path
  is the fix. Plugin absent entirely: overlay is moot (no sections).
- `scroll-padding-top` gets the same variable (anchor links never land
  under the pill).
- Non-sticky overlay: `:not(--sticky)` rule adds position:relative +
  z-index 100 without ever overriding sticky's position.

## Eligibility (promptless_is_header_overlay_active())

Automatic server-side policy — no per-page-type settings surface
(contrast: Astra ships five disable toggles because it cannot introspect):

1. Layout is `floating` + toggle on.
2. Singular content rendering full-width sections: `_aisb_sections`
   non-empty, `_aisb_display_mode` fullwidth (empty = fullwidth, mirrors
   the plugin's TemplateHandler). Archives, search, 404, blog, Woo, and
   shortcode-mode pages open with a title on the page background — they
   keep the standard band automatically.
3. `promptless_show_breadcrumbs()` true → suppressed. The trail lives in
   the band overlay removes; single source of truth shared with the
   breadcrumb renderer, so the two features can never disagree.

Canvas mode never reaches the header. PRE singles are NOT eligible in v1
(their hero layouts vary between full-bleed and boxed; support is a
follow-up once per-hero-layout eligibility is defined).

## Verified (2026-07-24, local)

Overlay active on a full-width page: classes emitted, measured var 96px,
wrapper margin -96px, first-section padding = band + 96px, scroll-padding
112px, sticky pinning with content passing beneath, non-sticky class
combination correct. Suppression verified on: breadcrumbs-enabled page,
search results, front page without sections. Customizer round-trip
(enable/disable) clean. Test matrix scenarios 25–27.
