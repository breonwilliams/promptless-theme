# AISB Token Contract — Promptless Theme (consumer side)

**Status:** Ratified 2026-08-29 (first reconciliation)
**Consumer:** Promptless Theme
**Producer:** Promptless WP plugin (AI Section Builder Modern)
**Minimum compatible producer version:** Promptless WP `1.3.0+`
(`Promptless_Plugin_Bridge::MIN_PLUGIN_VERSION`)
**Last reconciled against source:** theme v1.3.2 against Promptless WP v1.6.0
**Sister contracts:**
[`post-runtime-engine/docs/AISB_TOKEN_CONTRACT.md`](../../../plugins/post-runtime-engine/docs/AISB_TOKEN_CONTRACT.md) ·
[`form-runtime-engine/docs/AISB_TOKEN_CONTRACT.md`](../../../plugins/form-runtime-engine/docs/AISB_TOKEN_CONTRACT.md)

---

## Purpose

The Promptless Theme **inherits brand styling from Promptless WP when it is
active** and **degrades to sensible defaults when it is not**. It does this by
reading CSS custom properties emitted by Promptless WP's Global Settings.

This document is the **public contract** between theme and plugin. It is the
authoritative list of `--aisb-*` tokens the theme reads. Because the coupling
is expressed only in CSS, renaming or removing any token listed here breaks
theme chrome silently on every site running both — no PHP error, no console
warning, just wrong colours. Maintainers on either side MUST treat this as a
versioned API.

The theme reads **87 distinct tokens across 1,213 read sites** in 9 stylesheets,
`theme.json`, `style.css`, and 2 PHP files.

---

## How this consumer differs from PRE and FRE

Post Runtime Engine and Form Runtime Engine are **pure consumers**. The theme is
not, and three structural differences follow from that. Read this section before
using the tables.

### 1. The theme is also a conditional *producer*

`Promptless_Integration::add_inline_css_variables()` (hooked to
`wp_enqueue_scripts` at priority 20) emits **24 `--aisb-*` tokens itself when
Promptless WP is inactive**, so the theme's chrome has a palette to read even
standalone. PRE and FRE never do this.

The consequence for this contract: for those 24 tokens the inline
`var(--aisb-x, fallback)` fallbacks are **largely unreachable** — Integration has
already defined the property, so the fallback never fires. The fallbacks that
actually carry degradation are the other ~63 tokens (scales, smart-colour, neo).
Both are documented below, but only one of them is load-bearing.

The values Integration emits are listed in
[Tokens the theme emits when the producer is absent](#tokens-the-theme-emits-when-the-producer-is-absent).

### 2. One token flows backwards, theme → plugin

`--aisb-chrome-offset-top` is declared by the theme in `assets/css/header.css`
and **read by Promptless WP**, not the other way round. It reports the height of
the theme's sticky chrome so plugin sections can offset beneath it. It appears in
no table below because the theme never reads it; it is listed here so nobody
"cleans up" a token that looks unused from the theme side.

### 3. Intermediate variables

As in PRE, most `--aisb-*` references flow through local intermediates
(`--section-*`, `--topbar-*`, `--announcement-*`, `--wc-*`) declared per chrome
context. Mode switching re-declares only the intermediate; the rest of the file
is mode-blind.

```css
.promptless-header {
    --section-primary: var(--aisb-smart-light-section-link, var(--aisb-color-primary, #6366f1));
}
.promptless-header.aisb-section--dark {
    --section-primary: var(--aisb-smart-dark-section-link, var(--aisb-color-primary, #6366f1));
}
.promptless-header__nav a { color: var(--section-primary); }
```

This is why a single smart-link token shows several different fallbacks in the
scan — each chrome context routes it through its own intermediate. That is the
pattern working, not drift. See
[Known fallback variance](#known-fallback-variance) for the distinction.

---

## Contract Terms

**Promptless WP (producer) promises:**

1. Every token listed in the Consumed Tokens tables continues to be emitted on
   any page where Promptless WP is active, for as long as this contract version
   is supported.
2. A token's semantic meaning does not change between minor versions. Values may
   be recalculated; meanings are not repurposed.
3. Removal or rename is preceded by at least one minor version of deprecation
   notice, with the old token emitted as an alias for the full window.
4. `--aisb-chrome-offset-top` continues to be *read* from the theme under that
   name (see the reverse-flow note above).

**Promptless Theme (consumer) promises:**

1. Every `--aisb-*` reference in the files listed under
   [Where tokens are read](#where-tokens-are-read) either supplies a fallback
   that produces usable chrome when the token is absent, or reads a token the
   theme itself emits via `Promptless_Integration`.
2. No `--aisb-*` token is read anywhere else in the theme without updating this
   document first.
3. New token consumption requires: (a) adding it to the tables below, (b)
   pinning the minimum producer version, (c) supplying a fallback.
4. **The values `Promptless_Integration` emits mirror Promptless WP's own
   defaults**, so a site running theme-without-plugin renders the same palette
   as theme-with-plugin-at-defaults. Divergence is permitted only with a reason
   recorded in
   [Documented exceptions](#documented-exceptions).
5. The theme renders at minimum-viable quality with all tokens absent.

---

## Consumed Tokens

Grouped by functional area. **Fallback** is the value used when Promptless WP is
inactive and the theme does not itself emit the token; changing a fallback is a
breaking change for standalone chrome. Tokens marked † are also emitted by
`Promptless_Integration` when the plugin is absent, so their fallback is
effectively unreachable.

### Core Colors (`--aisb-color-*`)

| Token | Purpose | Fallback |
|---|---|---|
| `--aisb-color-primary` † | Brand colour; links, accents, focus rings, active states | `#6366f1` |
| `--aisb-color-primary-bg` | Tinted primary background for icon chips in the header | `rgba(99, 102, 241, 0.1)` ¶ |
| `--aisb-color-secondary` † | Secondary brand colour (editor palette surfacing only) | `#8b5cf6` |
| `--aisb-color-text` † | Body text on light chrome | `#1f2937` |
| `--aisb-color-text-muted` † | Secondary text — meta, captions, breadcrumb separators | `#6b7280` |
| `--aisb-color-text-inverse` † | Text on a filled primary surface | `#ffffff` |
| `--aisb-color-background` † | Light chrome background (header, footer, page) | `#ffffff` |
| `--aisb-color-surface` † | Raised surface — cards, dropdowns, mini-cart | `#f9fafb` |
| `--aisb-color-border` † | Borders and rules on light chrome | `#e5e7eb` |
| `--aisb-color-divider` | Low-contrast divider (breadcrumbs) | `rgba(31, 41, 55, 0.08)` |
| `--aisb-color-error` | WooCommerce error notices | `#dc2626` |
| `--aisb-color-success` | WooCommerce success notices | `#10b981` |

### Dark Mode Colors (`--aisb-color-dark-*`)

Used when chrome carries `aisb-section--dark`, or when a header/topbar/footer
theme setting is `dark`.

| Token | Purpose | Fallback |
|---|---|---|
| `--aisb-color-dark-text` † | Body text on dark chrome | `#f9fafb` |
| `--aisb-color-dark-text-muted` † | Secondary text on dark chrome | `#9ca3af` |
| `--aisb-color-dark-background` † | Dark chrome background | `#111827` |
| `--aisb-color-dark-surface` † | Raised surface on dark chrome | `#1f2937` |
| `--aisb-color-dark-border` † | Borders and rules on dark chrome | `#374151` |
| `--aisb-color-dark-divider` | Low-contrast divider on dark chrome | `rgba(249, 250, 251, 0.1)` |

### Buttons and Links (`--aisb-button-*`, `--aisb-*-link-*`)

| Token | Purpose | Fallback |
|---|---|---|
| `--aisb-button-primary-bg` † | Header CTA / Woo button background | `var(--aisb-color-primary)` |
| `--aisb-button-primary-text` † | Header CTA / Woo button label | `#ffffff` |
| `--aisb-button-primary-hover-bg` | Button hover background | `var(--aisb-button-primary-bg, var(--aisb-color-primary))` |
| `--aisb-button-primary-hover-text` | Button hover label | `var(--aisb-button-primary-text, #ffffff)` |
| `--aisb-button-glow-color` | Soft glow halo on Woo button hover | `rgba(99, 102, 241, 0.4)` |
| `--aisb-secondary-glow-color` | Glow halo for secondary Woo buttons | `rgba(129, 140, 248, 0.4)` |
| `--aisb-link-color` † | Breadcrumb link colour on light chrome | `var(--aisb-color-primary, #6366f1)` |
| `--aisb-link-color-dark` | Breadcrumb link colour on dark chrome | `var(--aisb-color-primary, #818cf8)` |

¶ Promptless WP is internally inconsistent on this token: `src/styles/tokens/section-tokens.css:16` declares `0.1` alpha while `includes/Core/Assets.php:1133` emits `0.2` at runtime. The theme pins its fallback to the declared default until the producer reconciles the two.

### Smart-Color Chain (`--aisb-smart-*`)

Promptless WP computes these contrast-corrected against the surface actually
behind the text. Theme chrome uses them so links and icons stay legible against
any brand colour. Section-level (`-section-link`, `-icon`) resolve against the
chrome background; surface-level (`-surface-*`) against a card or dropdown.

| Token | Purpose | Fallback |
|---|---|---|
| `--aisb-smart-light-section-link` | Nav / footer / announcement link on light chrome | `var(--section-primary)` ‡ |
| `--aisb-smart-dark-section-link` | Same on dark chrome | `var(--aisb-color-primary, #6366f1)` ‡ |
| `--aisb-smart-light-icon` | Header icon glyph on light chrome | `var(--aisb-color-primary)` |
| `--aisb-smart-dark-icon` | Header icon glyph on dark chrome | `var(--aisb-color-primary)` |
| `--aisb-smart-light-icon-bg` | Header icon chip background, light | `var(--aisb-color-primary-bg)` |
| `--aisb-smart-dark-icon-bg` | Header icon chip background, dark | `var(--aisb-color-primary-bg)` |
| `--aisb-smart-light-surface-link` | Link on a light card / dropdown | `var(--aisb-color-primary, #6366f1)` |
| `--aisb-smart-dark-surface-link` | Link on a dark card / dropdown | `var(--aisb-color-primary, #6366f1)` |
| `--aisb-smart-light-surface-icon` | Icon on a light card surface | `var(--aisb-color-primary, #6366f1)` |
| `--aisb-smart-dark-surface-icon` | Icon on a dark card surface | `var(--aisb-color-primary, #6366f1)` |
| `--aisb-smart-light-surface-border` | Border on a light card surface | `var(--wc-border, var(--aisb-color-border, #e5e7eb))` |
| `--aisb-smart-dark-surface-border` | Border on a dark card surface | `var(--wc-border, var(--aisb-color-dark-border, #374151))` |
| `--aisb-smart-light-surface-muted` | Muted text on a light card surface | `var(--section-text-muted)` |
| `--aisb-smart-dark-surface-muted` | Muted text on a dark card surface | `var(--section-text-muted)` |

‡ Routed through per-context intermediates — see
[Known fallback variance](#known-fallback-variance).

### Typography (`--aisb-section-font-*`, `--aisb-section-text-*`, `--aisb-section-leading-*`)

| Token | Purpose | Fallback |
|---|---|---|
| `--aisb-section-font-heading` † | Heading font for chrome headings | `inherit` ※ |
| `--aisb-section-font-heading-weight` | Heading weight | `700` |
| `--aisb-section-font-body` † | Body font for chrome text | `inherit` ※ |
| `--aisb-section-font-body-weight` | Body weight | `400` |
| `--aisb-section-font-button` | Button font family | `inherit` ※ |
| `--aisb-section-font-button-weight` | Button weight | `500` |
| `--aisb-section-font-medium` | Medium weight step | `500` |
| `--aisb-section-text-xs` | Type scale — xs | `0.75rem` |
| `--aisb-section-text-sm` | Type scale — sm | `0.875rem` |
| `--aisb-section-text-base` | Type scale — base | `1rem` |
| `--aisb-section-text-lg` | Type scale — lg | `1.125rem` |
| `--aisb-section-text-xl` | Type scale — xl | `1.25rem` |
| `--aisb-section-text-2xl` | Type scale — 2xl | `1.5rem` |
| `--aisb-section-text-3xl` | Type scale — 3xl | `2rem` |
| `--aisb-section-text-4xl` | Type scale — 4xl | `2.5rem` |
| `--aisb-section-leading-tight` | Line height — headings | `1.2` |
| `--aisb-section-leading-normal` | Line height — default | `1.5` |
| `--aisb-section-leading-relaxed` | Line height — prose | `1.6` |

※ Intentional divergence — see [Documented exceptions](#documented-exceptions).

### Layout, Shape and Motion (`--aisb-section-*`)

| Token | Purpose | Fallback |
|---|---|---|
| `--aisb-section-max-width` † | Container max width | `1280px` ※ |
| `--aisb-section-space-xs` | Space scale — xs | `0.5rem` |
| `--aisb-section-space-sm` | Space scale — sm | `1rem` |
| `--aisb-section-space-md` | Space scale — md | `1rem` |
| `--aisb-section-space-lg` | Space scale — lg | `1.5rem` |
| `--aisb-section-space-xl` | Space scale — xl | `2rem` |
| `--aisb-section-space-2xl` | Space scale — 2xl | `3rem` |
| `--aisb-section-radius-sm` | Radius — small chips, badges | `6px` |
| `--aisb-section-radius-base` | Radius — base step | `6px` |
| `--aisb-section-radius-md` | Radius — medium step | `0.25rem` |
| `--aisb-section-radius-lg` | Radius — large step | `12px` |
| `--aisb-section-radius-full` | Radius — pill / circle | `9999px` |
| `--aisb-section-radius-button` † | Button radius | `6px` |
| `--aisb-section-radius-card` † | Card / dropdown radius | `8px` |
| `--aisb-section-radius-image` † | Image radius on archive cards | `8px` |
| `--aisb-section-shadow-sm` | Small elevation | `0 1px 2px 0 rgba(0, 0, 0, 0.05)` |
| `--aisb-section-shadow-md` | Medium elevation | `0 4px 6px -1px rgba(0, 0, 0, 0.1)` |
| `--aisb-image-shadow` | Archive card image shadow | `0 10px 15px -3px rgb(0 0 0 / 0.1)` |
| `--aisb-section-transition-base` | Default transition timing | `200ms ease` |

### Neo-Brutalist Mode (`--aisb-neo-*`)

Emitted by Promptless WP only when Neo-Brutalist mode is on in Global Settings.
Absent otherwise, so every fallback here is load-bearing on normal sites.

| Token | Purpose | Fallback |
|---|---|---|
| `--aisb-neo-border-width` | Card border width | `4px` |
| `--aisb-neo-border-width-button` | Button border width | `3px` |
| `--aisb-neo-shadow-offset` | Card drop-shadow offset | `8px` |
| `--aisb-neo-shadow-offset-button` | Button drop-shadow offset | `4px` |
| `--aisb-neo-shadow-offset-button-hover` | Button hover drop-shadow offset | `3px` |
| `--aisb-neo-brutalist-primary-border` | Heavy border colour, light chrome | `#000` |
| `--aisb-neo-brutalist-primary-border-dark` | Heavy border colour, dark chrome | `#000` |
| `--aisb-neo-brutalist-secondary-border` | Heavy border on secondary Woo buttons | `#000` |

---

## Known fallback variance

The Fallback column above records the **dominant** value per token. 30 tokens
are read with more than one distinct fallback. They fall into three classes;
only the third is drift.

**Class 1 — by-design context routing (not drift).** The smart-link tokens
resolve through whichever intermediate belongs to the chrome context doing the
reading. This is the intermediate-variable pattern described above.

| Token | Fallbacks in use |
|---|---|
| `--aisb-smart-light-section-link` | `var(--section-primary)` ×17 · `var(--aisb-color-primary, #6366f1)` ×16 · `var(--topbar-primary)` ×2 · `var(--wc-link, …)` ×2 · `var(--announcement-primary)` ×1 · `var(--aisb-color-primary, #4f46e5)` ×1 |
| `--aisb-smart-dark-section-link` | `var(--aisb-color-primary, #6366f1)` ×18 · `var(--section-primary)` ×15 · `var(--topbar-primary)` ×2 · `var(--wc-link, …)` ×2 · `var(--announcement-primary)` ×1 |

**Class 2 — cosmetically equivalent.** `--aisb-button-primary-text` is `#ffffff`
×37 and `#fff` ×1. Same colour.

**Class 3 — materially different values for one token.** With Promptless WP
active every site resolves identically; with it inactive they do not. Standalone
chrome is therefore not a uniform degradation of branded chrome.

| Token | Fallbacks in use |
|---|---|
| `--aisb-section-space-md` | `1rem` ×44 · `1.5rem` ×25 · `16px` ×5 |
| `--aisb-section-space-sm` | `1rem` ×27 · `0.75rem` ×13 · `0.5rem` ×11 · `12px` ×3 |
| `--aisb-section-space-lg` | `1.5rem` ×18 · `2rem` ×18 · `24px` ×3 |
| `--aisb-section-space-xs` | `0.5rem` ×21 · `0.25rem` ×3 · `8px` ×1 |
| `--aisb-section-space-xl` | `2rem` ×22 · `3rem` ×2 |
| `--aisb-section-space-2xl` | `3rem` ×10 · `4rem` ×2 |
| `--aisb-section-text-2xl` | `1.5rem` ×4 · `1.75rem` ×4 · `1.875rem` ×2 |
| `--aisb-section-text-3xl` | `2rem` ×1 · `1.875rem` ×1 |
| `--aisb-section-text-base` | `1rem` ×19 · `0.875rem` ×2 |
| `--aisb-section-leading-tight` | `1.2` ×6 · `1.25` ×3 |
| `--aisb-section-leading-relaxed` | `1.6` ×2 · `1.625` ×2 |
| `--aisb-section-radius-button` | `6px` ×9 · `8px` ×6 · `var(--aisb-section-radius-md)` ×1 |
| `--aisb-section-radius-card` | `8px` ×15 · `12px` ×1 · `0.5rem` ×1 |
| `--aisb-section-radius-image` | `8px` ×2 · `12px` ×1 |
| `--aisb-section-radius-sm` | `6px` ×8 · `4px` ×2 |
| `--aisb-section-radius-full` | `9999px` ×1 · `999px` ×1 |
| `--aisb-section-max-width` | `1280px` ×5 · `1200px` ×1 |
| `--aisb-color-primary` | `#6366f1` ×167 · `#4f46e5` ×2 · `#818cf8` ×1 |
| `--aisb-color-surface` | `#f9fafb` ×15 · `#f3f4f6` ×2 · `#f5f5f5` ×1 |
| `--aisb-color-dark-border` | `#374151` ×35 · `#4b5563` ×4 |
| `--aisb-color-dark-surface` | `#1f2937` ×25 · `#2a2a2a` ×1 |
| `--aisb-color-error` | `#dc2626` ×8 · `#ef4444` ×1 |
| `--aisb-section-font-body` | `inherit` ×25 · two different system stacks ×5 |
| `--aisb-section-font-heading` | `inherit` ×24 · system stack ×1 |
| `--aisb-section-font-button` | `inherit` ×6 · system stack ×1 |
| `--aisb-section-font-button-weight` | `500` ×9 · `var(--aisb-section-font-medium)` ×1 |
| `--aisb-section-font-heading-weight` | `700` ×16 · `600` ×2 |

---

## Tokens the theme emits when the producer is absent

`Promptless_Integration::add_inline_css_variables()` emits these 24 on
`wp_enqueue_scripts` priority 20, **only** when
`class_exists('AISB_Plugin') || defined('AISB_MODERN_VERSION')` is false.

Per consumer promise 4, these values mirror Promptless WP's own defaults. The
table records the current emitted value and the producer default it mirrors.

| Token | Theme emits | Producer default | Matches |
|---|---|---|---|
| `--aisb-color-primary` | `#6366f1` | `#6366f1` | ✅ |
| `--aisb-color-secondary` | `#8b5cf6` | `#818cf8` | ❌ |
| `--aisb-color-text` | `#1f2937` | `#1f2937` | ✅ |
| `--aisb-color-text-muted` | `#6b7280` | `#6b7280` | ✅ |
| `--aisb-color-text-inverse` | `#ffffff` | `#ffffff` | ✅ |
| `--aisb-color-background` | `#ffffff` | `#ffffff` | ✅ |
| `--aisb-color-surface` | `#f9fafb` | `#f9fafb` | ✅ |
| `--aisb-color-border` | `#e5e7eb` | `#e5e7eb` | ✅ |
| `--aisb-color-dark-background` | `#111827` | `#1a1a1a` | ❌ |
| `--aisb-color-dark-text` | `#f9fafb` | `#fafafa` | ❌ |
| `--aisb-color-dark-surface` | `#1f2937` | `#2a2a2a` | ❌ |
| `--aisb-color-dark-border` | `#374151` | `#4b5563` | ❌ |
| `--aisb-color-dark-text-muted` | `#9ca3af` | `#9ca3af` | ✅ |
| `--aisb-section-font-heading` | system stack | system stack (different order) | ❌ |
| `--aisb-section-font-body` | system stack | system stack (different order) | ❌ |
| `--aisb-section-radius-button` | `6px` | `8px` (via `--aisb-section-radius-md`) | ❌ |
| `--aisb-section-radius-card` | `8px` | chained to `--aisb-section-radius-lg` | ❌ |
| `--aisb-section-radius-image` | `8px` | chained to `--aisb-section-radius-lg` | ❌ |
| `--aisb-section-max-width` | `1280px` | `1200px` | ※ |
| `--aisb-button-primary-bg` | primary colour | primary colour | ✅ |
| `--aisb-button-primary-text` | `#ffffff` | `#ffffff` | ✅ |
| `--aisb-ghost-button-color` | primary colour | computed smart colour | ❌ |
| `--aisb-link-color` | primary colour | computed smart colour | ❌ |

Rows marked ❌ are open defects against consumer promise 4 and are tracked
outside this document. Rows marked ※ are documented exceptions below.

### Documented exceptions

Two divergences are intentional and are **not** defects.

1. **`--aisb-section-font-body` / `-heading` / `-button` fall back to
   `inherit`, not to a font stack.** A theme must not impose a typeface on a
   site that has chosen its own. `inherit` lets the WordPress-level or
   user-level font win when Promptless WP is not supplying one. FRE's contract
   lists a concrete stack for the same token because a form is an embedded
   widget with no ambient typography to inherit; a theme is the ambient
   typography. The divergence is a consequence of what each consumer is.

2. **`--aisb-section-max-width` is `1280px`, not the producer's `1200px`.** The
   theme's own content width is 1280px
   (`promptless_content_width()`, filterable via `promptless_content_width`).
   Falling back to 1200px would make standalone chrome narrower than the content
   it wraps, misaligning the container against `the_content()` output. The theme
   matches itself when the producer is absent, and defers to the producer when
   present.

---

## Where tokens are read

| File | Role |
|---|---|
| `assets/css/header.css` | Header, nav, CTAs, search + cart triggers, icon chips |
| `assets/css/header-breakpoint.css` | Mobile drawer at the configured breakpoint |
| `assets/css/footer.css` | Footer columns, brand block, footer nav |
| `assets/css/archive.css` | Archive grid and cards |
| `assets/css/woocommerce.css` | Woo templates, mini-cart, notices, buttons |
| `assets/css/breadcrumbs.css` | Breadcrumb trail |
| `assets/css/search.css` | Search overlay and instant results |
| `assets/css/announcement-bar.css` | Announcement bar |
| `assets/css/editor-style.css` | Block-editor content styling |
| `style.css` | Base chrome and shared element styling |
| `theme.json` | Editor colour/typography palette surfacing |
| `inc/class-promptless-integration.php` | **Emits** the 24-token fallback block |
| `inc/class-promptless-assets.php` | Inline sticky-header offset script |
| `inc/class-promptless-plugin-bridge.php` | Admin compatibility notice styling |
| `assets/css/*.min.css` | Build output — never edited directly |
| `assets/js/*`, `inc/template-functions.php`, `inc/class-promptless-customizer.php` | **None.** No JS or template code reads `--aisb-*`. |

---

## Visual quality bar without Promptless WP

The theme must render at minimum-viable quality with the plugin inactive:

- All three header layouts (`default`, `stacked`, `floating`) render, including
  sticky behaviour and the overlay mode
- The mobile drawer opens, traps focus, and closes
- Light and dark chrome are both legible, with text meeting WCAG AA against
  their fallback backgrounds
- Footer columns, breadcrumbs, archive cards and the search overlay lay out
  correctly
- WooCommerce templates and the mini-cart render without layout collapse
- No console errors, no invisible text, no zero-height chrome

The theme does **not** need to look branded without Promptless WP. Fallbacks
exist for graceful degradation, not to replace the design system.

---

## When this contract changes

**Adding a token to consume:**

1. Add it to the appropriate table with purpose + fallback
2. Pin the minimum producer version at the top of this document
3. Add the CSS reference with the fallback, or route it through an existing
   intermediate
4. If `Promptless_Integration` should also emit it standalone, add it there and
   to the emitter table

**Removing a consumed token:**

1. Stop reading it in CSS
2. Remove the row
3. No producer-side action — Promptless WP may keep emitting it for PRE and FRE

**Renaming (producer-side):**

1. Promptless WP emits both names for the deprecation window
2. The theme migrates its reads, and its emitter if the token is in that set
3. After all consumers migrate, Promptless WP drops the old name

**Changing a value the theme emits:** by consumer promise 4 the emitted value
tracks the producer default. Changing one requires either matching the producer
or recording a reason under
[Documented exceptions](#documented-exceptions).

This matches the versioning protocol in PRE's and FRE's contracts.
