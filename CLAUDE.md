# Promptless Theme — AI Reference

The companion WordPress theme for the Promptless WP page builder. Classic
(non-block) theme; every piece of site chrome — header, top bar,
announcement bar, footer, breadcrumbs, archives, search, WooCommerce — is
skinned by the `--aisb-*` design tokens Promptless WP emits, with literal
fallbacks so the theme stands alone.

**Status (as of 2026-08-29, version 1.3.2):** Shipping and stable.
`Requires at least: 6.6`, `Tested up to: 6.9`, `Requires PHP: 7.4`.
Recent work: header search (trigger + full-screen overlay + instant
results, v1.3.0), the floating rounded-pill header layout and its
Float-Over-First-Section overlay mode (v1.2.9–1.3.0), archive card meta
controls, and routing all search forms through `searchform.php` (v1.3.1).
Trust `readme.txt`'s changelog for exact per-version detail.

> **💼 Licensing model:** This theme is **FREE**. No Freemius, no premium
> tier, no license gates. Only Promptless WP is sold; the theme, PRE, FRE
> and FlowMint are all free and exist to add value to the Promptless
> ecosystem. Do NOT add license checks when extending it.

> **⚠️ The theme has NO REST surface and NO MCP relay of its own.** It is
> the one package in the stack that AI reaches *indirectly*: Promptless
> WP's `HeaderService` / `FooterService` / `AnnouncementBarService` write
> the very same `theme_mods` this theme's Customizer owns. That makes the
> **Customizer sanitizers the vocabulary owner** for theme chrome — see
> "The sanitizer is the enum owner" below. It is the single most
> load-bearing fact about this codebase.

## What this theme IS

- A companion theme that renders site chrome consistent with Promptless WP
  sections — so header/footer/archives match hand-built pages
- A **consumer** of `--aisb-*` design tokens (92 distinct tokens across
  `assets/css/`), each with a literal fallback
- The **owner** of the theme-chrome vocabulary: 49 `promptless_*`
  theme_mods with sanitize callbacks that define every closed enum
- A thin, deliberately shallow render layer — 78 `promptless_*` template
  functions in `inc/template-functions.php`, no data model of its own
- WooCommerce-aware (declared support, mini-cart dropdown, dedicated
  stylesheet), accessibility-focused (focus-trapped drawer, WAI-ARIA)

## What this theme IS NOT

- A page builder (Promptless WP does that; the theme renders *around* it)
- A block theme — it is classic, `theme.json` exists only for editor
  colour/typography surfacing, not for templates
- A CPT renderer (PRE does that) or a form renderer (FRE does that)
- A hard dependent of Promptless WP. Every chrome feature works with the
  plugin inactive; `Promptless_Integration` emits fallback CSS variables
  in that case. The ONE PHP touchpoint is the plugin bridge (below), and
  it is soft-gated.
- Connector-addressable in its own right — it has no `promptless/v1`-style
  namespace. AISB's connector writes its theme_mods on its behalf.

## System requirements

| Requirement | Version | Notes |
|---|---|---|
| WordPress | 6.6+ | `Requires at least` in `style.css` |
| PHP | 7.4+ | Type hints, arrow functions |
| Promptless WP | 1.3.0+ (recommended, not required) | `Promptless_Plugin_Bridge::MIN_PLUGIN_VERSION`. Soft gate — admin notice only, never a fatal or a blank page |
| WooCommerce | any (optional) | All Woo code is behind `class_exists('WooCommerce')` |
| Node | for builds only | `cssnano` / `postcss` / `terser` via `npm run build` |

## Documentation map

| Topic | File |
|---|---|
| **Release procedure (canonical)** | `THEME_RELEASE_GUIDE.md` |
| Floating header overlay contract | `docs/FLOATING_HEADER_OVERLAY.md` |
| Header search design | `docs/SEARCH_DESIGN.md` |
| Logo variants (light/dark) design | `docs/LOGO_VARIANTS_DESIGN.md` |
| Breadcrumbs design exploration | `docs/BREADCRUMBS_DESIGN_EXPLORATION.md` |
| Audit history / remediation plan | `THEME_AUDIT.md`, `THEME_AUDIT_WAVE2_PLAN.md` |
| Header combination harness | `tests/header-test-matrix.php` (**gitignored** — see gotchas) |

There is **no `AISB_TOKEN_CONTRACT.md` for the theme.** PRE and FRE each
maintain one and require updating it before consuming a new token; the
theme consumes ~92 tokens with no equivalent contract. Treat that as a
known gap, not as permission to consume tokens carelessly.

## Quick architectural summary

```
┌────────────────────────────────────────────────────────────────────┐
│                Promptless WP (separate plugin, sold)                │
│  Global Settings ──emits──▶ --aisb-* CSS custom properties          │
│  Connector services ──write──▶ theme_mods (header/footer/announce)  │
└───────────────┬──────────────────────────────┬─────────────────────┘
                │ CSS token contract           │ theme_mod writes
                ▼                              ▼
┌────────────────────────────────────────────────────────────────────┐
│                    Promptless Theme (this repo)                     │
│                                                                     │
│  Promptless_Customizer ── 49 promptless_* theme_mods                │
│      └─ sanitize_*() callbacks = THE closed enums (vocabulary owner) │
│                                                                     │
│  Promptless_Setup        theme supports, 7 nav locations, image sizes│
│  Promptless_Assets       conditional .min enqueue, mtime versioning  │
│  Promptless_Integration  fallback CSS vars when plugin inactive      │
│  Promptless_Plugin_Bridge  ONE chokepoint into AISB SectionRenderer  │
│  Promptless_Mobile_Menu_Breakpoint  regenerates breakpoint override  │
│  Promptless_Mega_Menu (+ Walker)    menu-item icons/descriptions     │
│  Promptless_Breadcrumbs             trail + Schema.org JSON-LD       │
│  inc/template-functions.php         78 promptless_* render helpers   │
│                                                                     │
│  Templates: header.php / footer.php / archive.php / single.php /    │
│             page.php / search.php / 404.php / searchform.php        │
│  Page templates: aisb-canvas.php (no chrome), aisb-fullwidth.php    │
└────────────────────────────────────────────────────────────────────┘
```

## The sanitizer is the enum owner

Every closed vocabulary for theme chrome lives in a
`Promptless_Customizer::sanitize_*()` method. These are the authoritative
lists — AISB's connector service allowlists, its relay tool schemas, its
preflight critical rules and its knowledge map all MIRROR them
downstream:

| Sanitizer | Allowed values |
|---|---|
| `sanitize_header_layout` | `default`, `stacked`, `floating` |
| `sanitize_theme_variant` | `light`, `dark` |
| `sanitize_nav_theme_variant` | `''` (inherit), `light`, `dark` |
| `sanitize_nav_position` | `left`, `center`, `right` |
| `sanitize_cta_style` | `primary`, `secondary`, `ghost` |
| `sanitize_cta_mobile_placement` | `bar`, `menu` |
| `sanitize_cta_menu_position` | `top`, `middle`, `bottom` |
| `sanitize_topbar_mobile` | `inline`, `collapse` |
| `sanitize_breadcrumbs_theme` | `inherit`, `light`, `dark` |
| `sanitize_cart_style` | `link`, `dropdown` |
| `Promptless_Mobile_Menu_Breakpoint::get_allowed_breakpoints()` | `640`, `768` (default), `900`, `1024`, `1200` |

**Growing one of these is a cross-codebase change set, not a theme
commit.** Adding a value here without extending AISB's HeaderService
allowlist, its REST args, the bundled relay's tool schema *and* body-field
list, the preflight rules and the knowledge map produces the stack's
signature silent failure: the relay drops the unknown field and reports
success, so it reads as "saved fine, then reverted to default."

Customizer panels: `promptless_header_panel`, `promptless_topbar_panel`,
`promptless_announcement_panel`, `promptless_footer_panel`.

## Naming conventions

- **Class prefix:** `Promptless_*` (files `inc/class-promptless-*.php`).
  No namespaces, no PSR-4 — `functions.php` `require_once`s each file and
  instantiates on `after_setup_theme` priority 5.
- **Text domain / theme slug:** `promptless`
- **Function prefix:** `promptless_*`
- **Constants:** `PROMPTLESS_THEME_VERSION`, `PROMPTLESS_THEME_DIR`,
  `PROMPTLESS_THEME_URI`
- **theme_mod prefix:** `promptless_*` (all 49)
- **Post meta:** `_promptless_*` (`_promptless_mega_enabled`,
  `_promptless_menu_icon`, `_promptless_menu_description`,
  `_promptless_breadcrumbs`)
- **CSS classes:** `promptless-*`, plus the **shared** `aisb-section--light`
  / `aisb-section--dark` scope classes — deliberately the same classes AISB
  sections use, so chrome re-skins with the palette automatically
- **Nav menu locations (7):** `primary`, `footer`, `footer-col-1`,
  `footer-col-2`, `footer-col-3`, `topbar-left`, `topbar-right`
- **Image sizes:** `promptless-card` (600×400), `promptless-card-large`
  (800×450)

## The integration seams

Three, and only three:

1. **CSS tokens (one-way, primary).** The theme reads `--aisb-*` with
   literal fallbacks. `Promptless_Integration` declares
   `add_theme_support('aisb-native-theme')`, which tells the plugin to emit
   variables on *every* page rather than only section pages; when the
   plugin is inactive the same class emits a fallback variable block.
2. **`Promptless_Plugin_Bridge` (the one PHP touchpoint).** A static
   chokepoint that `aisb-canvas.php` and `aisb-fullwidth.php` call instead
   of instantiating `\AISB\Modern\Core\SectionRenderer` themselves. It
   version-checks against `MIN_PLUGIN_VERSION` (1.3.0) and shows an admin
   notice on mismatch — **soft gate by design**, because a production site
   must never get a blank page from a version skew. It applies the
   `aisb_get_sections` filter so plugin preview mode can override.
   *Unlike PRE and FRE, the theme is not PHP-decoupled from AISB.* Keep
   this the only place that knows the plugin's class names.
3. **Hooks the theme publishes for others.** `promptless_after_header`,
   `promptless_archive_card_section` (fired at 5 slots — `image_overlay`,
   `headline`, `subtitle`, `meta_strip`, `footer_meta` — which is exactly
   what PRE's `PCPTPages_Card_Filter_Hooks` listens to), plus
   `promptless_header_ctas`, `promptless_breadcrumbs_items`,
   `promptless_show_breadcrumbs`, `promptless_archive_image_aspect`,
   `promptless_mobile_menu_breakpoint_choices`,
   `promptless_mega_menu_item_config`, `promptless_content_width`.
   The mega-menu class also *listens* to AISB's
   `aisb_connector_menu_item_created` / `aisb_connector_menu_item_export`
   so connector-built menus carry icons and descriptions.

## Build pipeline

```bash
npm run build          # both
npm run build:css      # scripts/build-css.js  → *.min.css via postcss+cssnano
npm run build:js       # scripts/build-js.js   → *.min.js  via terser
```

The theme **always enqueues the `.min` files**, never the sources — an
un-built change is invisible on the frontend. Assets are versioned by file
**mtime** (`Promptless_Assets::asset_version()`), so a rebuilt file busts
cache automatically. Enqueues are conditional throughout (archive CSS only
on archives, WooCommerce CSS only when Woo is active, search assets only
when header search is enabled) — "never ship assets a page can't use" is
the standing pattern; match it for anything new.

Two assets are exceptions with **no `.min` build at all** —
`assets/css/announcement-bar.css` and `assets/js/announcement-bar.js` are
enqueued as sources. They are also absent from `create-release.sh`'s
minified-asset check loop, so nothing would catch it if that were
accidental. Leave as-is unless deliberately changing it.

## Guardrails for AI sessions on this theme

- **NEVER run `scripts/split-header-breakpoint.js`.** It was a one-off
  generator that **overwrites** `assets/css/header-breakpoint.css`,
  destroying every hand-authored rule added since (there are in-file
  comments marking them). Breakpoint-tracking rules go *directly into*
  `header-breakpoint.css`; fixed-width tiers that must NOT follow the
  user's breakpoint setting go in `header.css` instead.
- **Respect the cross-file cascade.** `header.min.css` loads *before*
  `header-breakpoint.min.css` (or its Customizer-generated override), so
  an equal-specificity override authored in `header.css` **loses**. Tune
  values through custom properties instead — the breakpoint rule consumes
  `var(--x, default)` and the fixed-tier rule sets `--x`; custom-property
  overrides win regardless of load order.
- **The breakpoint override is generated and cached.**
  `Promptless_Mobile_Menu_Breakpoint` extracts and rewrites rules from
  `header-breakpoint.css`, keyed on (breakpoint, file mtime), cached in a
  day-long transient. Hand edits to that file are picked up automatically
  — which is *why* rules must live there. On the default 768 the static
  `.min.css` is served instead.
- **No CSS hacks.** No `!important` overrides, no forcing layouts, no
  hiding plugin or WooCommerce elements to get a result. Same rule as the
  plugin side.
- **The theme must work with Promptless WP inactive.** Every `--aisb-*`
  reference needs a literal fallback; anything reaching plugin PHP goes
  through `Promptless_Plugin_Bridge` and stays soft-gated.
- **Chrome vocabulary changes are cross-codebase** — see "The sanitizer is
  the enum owner". Do the AISB connector side in the same change set.
- **Container padding is a deliberate mirror.** `.promptless-container`
  padding intentionally matches AISB section padding (2-tier: `space-lg`
  above 640px, `space-md` at/below) so chrome and section content align
  pixel-perfect. Don't "tidy" one side of that.
- **Accessibility is contract-level, not polish.** Focus-trapped mobile
  drawer, WAI-ARIA patterns, reduced-motion, real keyboard paths. Verify
  them, don't assume them.
- **Media-query testing:** `resize_window` does not change
  `window.innerWidth` fullscreen. Use an injected fixed-width iframe or the
  Customizer's device-preview buttons. `wp.customize('<setting>').set(v)`
  plus preview-iframe inspection verifies without publishing; clear
  `window.onbeforeunload` before navigating away.

## Releasing new versions

**Canonical procedure: [`THEME_RELEASE_GUIDE.md`](THEME_RELEASE_GUIDE.md).**

```bash
npm run build
./create-release.sh
```

`create-release.sh` gates on two things and aborts on either:
1. **Version consistency** across `style.css` (`Version:`), `readme.txt`
   (`Stable tag:`), and `functions.php` (`PROMPTLESS_THEME_VERSION`).
2. **All `.min` assets present** for `header`, `header-breakpoint`,
   `footer`, `archive`, `woocommerce`, `breadcrumbs` CSS and `navigation`,
   `customizer-preview` JS.

Output: `release/promptless-v<version>.zip`.

**This repo has no publish script.** AISB ships
`scripts/publish-github-release.sh` and PRE ships
`bin/publish-github-release.sh`; the theme has neither —
`create-release.sh` builds the ZIP and stops. Publishing is the manual
step documented in `THEME_RELEASE_GUIDE.md`:

```bash
git tag v<version>-theme
git push && git push --tags
gh release create v<version>-theme --title "Promptless Theme v<version>" --notes "..." release/promptless-v<version>.zip
```

Two details that are easy to get wrong:

- **The tag carries a `-theme` suffix** (`v1.3.2-theme`), unlike the
  plugins' plain `vX.Y.Z`. Every tag in this repo follows it.
- **The built ZIP must be passed to `gh release create` as a positional
  argument.** A release published without it has no installable asset, and
  GitHub's auto-generated "Source code (zip)" is NOT a substitute — it
  extracts to a differently-named folder, so WordPress installs it as a
  duplicate theme instead of replacing the existing one.

**Gotchas the script does NOT catch:**
- **`package.json` `"version"` is not checked and is currently drifted**
  (`1.3.0` while the theme is `1.3.2`). Bump it by hand.
- **The script packages the WORKING TREE, not git HEAD** — uncommitted work
  ships. Useful for pressure-test uploads; surprising if you expected a
  clean-release guarantee.
- **`tests/` is gitignored**, so `tests/header-test-matrix.php` — the harness
  that renders every header combination inline — exists only on this
  machine. Don't assume a fresh clone has it.
- Cowork/device-bridge sessions can leave a stale `.git/index.lock`; clear
  it in a real terminal before pushing.

---

**Theme status:** v1.3.2 shipping. Header (3 layouts incl. floating +
overlay mode), top bar, announcement bar, footer, breadcrumbs with
Schema.org, archives with card meta controls, header search with instant
results, mega menu with Iconify icons, WooCommerce mini-cart, dark mode.
Known gaps: no theme-side `AISB_TOKEN_CONTRACT.md` despite ~92 tokens
consumed; `announcement-bar` assets unminified and unguarded by the release
script; `package.json` version drift.
