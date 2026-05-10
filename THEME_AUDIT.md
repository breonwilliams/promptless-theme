# Promptless Theme — Architectural Audit

Prepared 2026-05. Synthesizes three parallel audit passes (plugin integration + assets, templates + accessibility + theme.json, WooCommerce + perf + code patterns) with my own verification of the load-bearing claims.

The plugin refactor close-out gave us a working, tested baseline. This doc is the equivalent prioritized punch list for the theme. Findings are graded **Critical** (architectural debt that will compound), **Important** (real improvements but not blockers), **Nice-to-have** (polish), and **Non-issues** (claims I checked and downgraded).

---

## Critical

### C1. CSS specificity arms race (`[href]` repeated 8–9 times) — ✅ RESOLVED

**Status:** Fixed in Wave 2 execution session. See `THEME_AUDIT_WAVE2_PLAN.md` for the design and the actual implementation differs slightly (simpler than the plan — no per-component variables needed thanks to the theme's existing `--section-*` token abstraction).

**What shipped:**
1. **Plugin-side `@layer` wrap** — `plugins/ai-section-builder-modern/src/styles/core-components.css` now wraps the catch-all link-paint rule (the 10× `:not()` chain at specificity 0,11,1) inside `@layer aisb-defaults`. CSS Cascade Layers demote everything in the layer below any unlayered rule regardless of specificity, so theme chrome rules now win without specificity gymnastics.
2. **Theme-side `[href]` chain collapse** — All 224 instances of `[href][href][href]…` (8× and 9× repetitions) across `footer.css`, `header.css`, `archive.css`, `woocommerce.css` collapsed to a single `[href]` (the legitimate "anchor with href attribute" filter). Identical match set, sane specificity (~0,4,1 instead of 0,12,0).
3. **Stale comment cleanup** — All `HIGH-SPECIFICITY ... LINK OVERRIDES` block headers and inline `Uses 8x [href]…` annotations rewritten to describe the new `@layer`-based architecture.

**Verification:**
- Hack watermark across all four CSS files: **0 remaining** (was 224)
- Source CSS sizes dropped ~6 KB combined (footer 16.3→15.3, archive 55.2→54.3, woocommerce 157.9→153.6)
- Plugin Jest suite: 546/546 passing post-`@layer` wrap (no regressions)
- Theme `npm run build` clean

**Visual verification still owed (you):** reload your local Flywheel site and confirm header/footer/archive/woocommerce link colors render identically to before. If anything regressed, the most likely cause is browser support — `@layer` is Baseline Widely Available since 2023 (~96% support); ancient browsers without it would see the catch-all link-paint reapply.

### C2. `theme.json` $schema pinned to `/trunk/` instead of a released WP version

**Where:** `theme.json:2`
```json
"$schema": "https://schemas.wp.org/trunk/theme.json"
```

The trunk schema can change between WordPress releases without notice. Theme.json validation (in editors and CI) effectively chases a moving target. Production themes should pin.

**Recommended fix:** Pin to your minimum-supported WP version, e.g. `https://schemas.wp.org/wp/6.5/theme.json`. One-line change.

### C3. Direct `SectionRenderer` instantiation in templates without API contract guard

**Where:** `aisb-canvas.php:71`, `aisb-fullwidth.php:60`
```php
$renderer = new \AISB\Modern\Core\SectionRenderer();
```

The theme reaches into a fully-qualified plugin class name without:
1. Checking the plugin is active (it does check `class_exists` elsewhere but not at the point of instantiation in some branches)
2. Asserting a minimum plugin version (the plugin could rename `SectionRenderer` or change `render_section()`'s signature in a future release)
3. Surfacing a meaningful error to admins on mismatch

**Recommended fix:** Wrap the instantiation in a small adapter (e.g. `Promptless_Plugin_Bridge::render_section($section, $index, $post_id)`) that does the version + class existence check up-front and emits a graceful admin-visible fallback if the contract isn't met. The bridge is the single chokepoint to maintain when the plugin's API evolves.

---

## Important

### I1. Theme rendering would benefit from the same per-section-palette propagation fix we just shipped on the plugin side

**Context:** The plugin fix we just shipped emits per-section `--aisb-smart-*` overrides on the section element when a snapshot is present. The theme's header/footer chrome reads global smart-color variables from `:root`, NOT from a section. So any per-section palette work doesn't extend into the chrome — that's correct (chrome lives outside the section), but the chrome's link colors *do* read through some of the same smart-color tokens. The C1 fix above is the more general remedy; this finding is the integration-level note.

**Action:** Verify (after C1 is done) that toggling per-section palettes in the editor still produces correct chrome behavior with no surprise color shifts.

### I2. Build scripts silently skip missing source files

**Where:** `scripts/build-css.js:67-74`, `scripts/build-js.js:64-71`

Both build scripts log `⚠ Skipping...` and continue when a listed source file doesn't exist. Typos in the file list become silent build success with stale shipped assets.

**Recommended fix:** Treat a missing source file as a build failure. Two-line change in each script (`process.exit(1)` instead of `return`).

### I3. `editor-style.css` uses hardcoded brand colors instead of plugin tokens — ✅ RESOLVED

**What shipped:**
1. `assets/css/editor-style.css` refactored to read from `--aisb-*` tokens with hardcoded fallbacks (so the editor still looks sane if the plugin is inactive or the dynamic injection fails).
2. `inc/class-promptless-assets.php::inject_editor_iframe_tokens()` hooked to `block_editor_settings_all` — the WordPress 6.0+ supported way to push CSS into the block editor iframe (CSS variables don't cascade through iframe boundaries, so a wp_enqueue_style at `:root` won't work).
3. The injected CSS is scoped to `.editor-styles-wrapper` (the iframe's body wrapper) so it doesn't leak to editor chrome (toolbars/sidebars use their own `--aisb-editor-*` token system).
4. Reads global settings via `Promptless_Integration::get_global_settings()` which already handles the plugin-not-active case gracefully.
5. Hex color values are validated via `sanitize_hex_color()` (defense-in-depth, even though the upstream values are already trusted).

**Verified (you):** open the block editor, change Primary Color in Global Settings → Save → reload editor. The link color in the editor preview should now reflect the new color instead of staying on `#6366f1`.

### I4. `promptless_needs_woocommerce_assets()` runs uncached on every page load

**Where:** `inc/template-functions.php` (function `promptless_needs_woocommerce_assets`, around line 1074)

The function reads `_aisb_sections` post meta and JSON-decodes it on every page load to decide whether to enqueue WooCommerce styles. On high-traffic sites that's an extra DB hit + JSON decode per request. Object cache may absorb the meta read but the JSON decode still costs.

**Recommended fix:** Cache the decoded result on the post object via static memoization keyed by post ID. ~5 lines.

### I5. Anonymous closure in `functions.php` body_class filter can't be removed

**Where:** `functions.php:54-61`

```php
add_filter( 'body_class', function() { ... } );
```

Site owners or child themes can't `remove_filter()` this because the closure has no recoverable handle. Convention is named functions for any filter you might want to override.

**Recommended fix:** Extract to a named function `promptless_woocommerce_body_class()`. 4-line change.

### I6. Customizer-only — no Site Editor support yet

**Where:** `inc/class-promptless-customizer.php` (entire file, by design)

The theme is classic, not block. Header/footer/nav/sticky options live in the legacy Customizer. This is a valid stance for a classic theme. But if the roadmap includes "modernize to a block theme" or "support Site Editor for chrome customization," it'll be a substantial rewrite — worth flagging now so it doesn't surprise you later.

**Action:** Decide whether classic Customizer is a permanent stance or a pre-FSE choice. Mark the answer in `THEME_RELEASE_GUIDE.md` so future contributors don't burn a week on speculative FSE migration.

### I7. No version compatibility check between theme and plugin

**Where:** `inc/class-promptless-integration.php` and `inc/class-promptless-assets.php` both reference `PROMPTLESS_THEME_VERSION` but never compare against `AISB_MODERN_VERSION`.

If the theme depends on a plugin feature added in plugin v1.4 but is paired with plugin v1.2, no admin warning fires.

**Recommended fix:** Add a one-time `admin_notices` hook that compares `defined('AISB_MODERN_VERSION') ? AISB_MODERN_VERSION : null` against a constant in the theme like `PROMPTLESS_THEME_REQUIRES_PLUGIN = '1.4.0'`. ~20 lines.

---

## Nice-to-have

- **N1.** `tests/scripts/audit-theme-tokens.js` and `tests/header-test-matrix.php` are advisory-only — no CI integration. Wiring `audit-theme-tokens.js` into a Playwright test that fails the build on WCAG contrast drops would close a real regression risk, but it's discretionary.
- **N2.** Footer nav `aria-labelledby` logic in `template-functions.php` uses string replacement. A custom nav walker would be cleaner but the current code works.
- **N3.** WooCommerce CSS has theme-variant variable duplication (~50 lines of light/dark scope rules). Modern CSS nesting could compress this; older browser support keeps the current verbose form valid.
- **N4.** `aisb-canvas.php:101` and `aisb-fullwidth.php:90` use inline `<style>` for admin-only error banners. Inline styles for admin warnings are within accepted WP convention; not a real issue.
- **N5.** Mini-cart `<a>` line `assets/css/woocommerce/...mini-cart.php:57` uses unescaped `sprintf('%s &times; %s', ...)` for quantity × price. WC quantity is type-coerced, so it's safe in practice — but defense-in-depth `esc_html()` on the quantity wouldn't hurt.

---

## Non-issues (audit-flagged but verified as fine)

- **NI1.** Mini-cart image link with `tabindex="-1"` + `aria-hidden="true"` (`woocommerce/cart/mini-cart.php:43`). The audit flagged this as a WCAG 2.4.3 violation but I checked — there's a sibling `<a class="promptless-cart-item__name">` to the same product permalink at line 52 that IS keyboard-focusable. Hiding the duplicate image link from the focus order is the *correct* pattern (avoid duplicate focus targets to the same destination). No fix needed.
- **NI2.** Customizer-preview JS uses jQuery `.html()` to inject footer brand text. The audit raised XSS concerns. Customizer preview only runs in admin, sanitization happens server-side via `wp_kses_post` callbacks (`class-promptless-customizer.php:395`). No real attack surface.

---

## Recommended sequencing

If you want to tackle these in waves:

1. **Wave 1 — quick wins (under 1 hour total):** C2 (theme.json schema pin), I2 (build scripts fail on missing files), I5 (named body_class filter). Low risk, mechanical.
2. **Wave 2 — architectural cleanup (1–2 days):** C1 (CSS specificity hack → CSS variable cascade). This is the headline structural improvement. Touch most CSS files but each diff is small.
3. **Wave 3 — integration robustness (half day):** C3 (plugin bridge adapter), I7 (version compatibility check). Both are about graceful degradation when theme + plugin drift.
4. **Wave 4 — polish (discretionary):** I3 (editor-style tokens), I4 (cache woo asset check), I6 (FSE decision), N1 (CI integration of audit scripts).

**My recommendation:** Wave 1 today (quick mechanical), then Wave 2 as the next focused sprint — it's the equivalent of the plugin's editorStore.js split. Cleans up the same structural debt and prevents the next round of "why does my section color do X in the chrome but Y in the body" bug reports.
