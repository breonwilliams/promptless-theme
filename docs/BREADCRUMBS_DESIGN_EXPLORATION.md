# Breadcrumbs — Design Exploration (Pre-Implementation)

**Status:** Exploration / decision document — no code yet
**Date:** 2026-07-12
**Owner surface:** promptless-theme (with token-level ties to Promptless WP global settings)
**Related contracts:** `AISB_TOKEN_CONTRACT.md` (FRE/PRE copies), PRE `docs/HERO_CONTRAST_DESIGN.md`

This document grounds a future breadcrumbs feature in published user research and current (2026) SEO/accessibility standards, then maps it onto the actual architecture of the Promptless ecosystem. It ends with a recommended build shape and the open decisions that need a human call before implementation.

---

## 1. What the research says (and what we adopt from it)

### 1.1 Users benefit at near-zero cost — when the site has real hierarchy

Nielsen Norman Group has recommended breadcrumbs continuously since 1995: they aid orientation, cut clicks back up the tree, and impose almost no UI cost because they're a compact, single line that users recognize instantly. Their two strongest guidelines for us:

- **Location-based, never history-based.** The trail must show the page's canonical position in the site's information architecture, not the visitor's click path. A visitor landing from Google on a listing page should see `Home > Listings > 123 Main St`, regardless of how they arrived.
- **Secondary, not competitive.** Breadcrumbs sit visually below the global navigation and above the page title, smaller than both, and never compete with the main menu. This maps exactly to the slot between `</header>` and the page content in our templates.

Baymard Institute's e-commerce benchmarking found 68% of major sites have sub-par breadcrumb implementations, and notes a poorly implemented trail is often *worse* than none. Their nuance — that product pages ideally offer both hierarchy and history trails — matters for WooCommerce shops but is out of scope for a v1: we adopt the pure hierarchy model NN/g recommends, which Baymard agrees is the correct single choice when only one is offered.

**Adopted:** hierarchy-only trails; placement immediately after the site header, above the page title/hero; visually quiet (muted text, body font, small size).

### 1.2 SEO in 2026: the visible trail shrank in SERPs, the schema got more important

In January 2025 Google **removed breadcrumb trails from mobile search snippets** (showing domain-only), while keeping them on desktop. The industry read, consistent across Sitebulb / Search Engine Land / others: this changed the *display*, not the *value*. With no visible trail on mobile SERPs, Google leans harder on `BreadcrumbList` structured data and internal linking to understand site hierarchy and topical association. Google's own documentation still lists BreadcrumbList as a core supported type: an `itemListElement` array of `ListItem` objects with `position`, `name`, and `item` (URL — omitted for the current-page leaf), minimum two items.

**Adopted:**
- Emit `BreadcrumbList` JSON-LD whenever the visible trail renders — full trail, even if the visible mobile trail truncates.
- Exactly **one** BreadcrumbList per page, ever. Yoast, Rank Math, AIOSEO, SEOPress, etc. all emit their own — duplicate markup is the classic breadcrumb bug flagged in every 2026 implementation guide. See §5.

### 1.3 Accessibility: the WAI-ARIA APG pattern is settled and cheap

The W3C APG breadcrumb pattern is unambiguous:

```html
<nav class="promptless-breadcrumbs ..." aria-label="Breadcrumb">
  <ol>
    <li><a href="/">Home</a></li>
    <li><a href="/listings/">Listings</a></li>
    <li><a href="/listings/123-main-st/" aria-current="page">123 Main St</a></li>
  </ol>
</nav>
```

- `<nav aria-label="Breadcrumb">` creates a named landmark screen-reader users can jump to.
- Ordered list `<ol>` conveys the sequence semantically.
- `aria-current="page"` marks the leaf (the leaf may be plain text instead of a link; if so `aria-current` is optional — we'll render it as non-link text, which NN/g also prefers).
- **Separators are CSS-only** (`::before`/`::after` content on `li`), never markup characters, so screen readers don't announce "greater-than" between every crumb. Use `>` or `/` visually; research calls `>` the most universally understood.

**Adopted wholesale.** This also aligns with the button-state and contrast discipline we finished last cycle: crumb links get the same `:hover` / `:focus-visible` ring treatment as other theme links.

### 1.4 Mobile: truncate, don't remove

IxDF and the 2026 pattern guides converge on: long trails break narrow layouts, and the wrong fix is hiding breadcrumbs on mobile (orientation matters *more* there). Preferred solutions: collapse middle crumbs to an ellipsis (`Home > … > Parent > Current`), or allow horizontal scroll within the trail container. For our typical depth (2–4 levels — see trail rules below), a simple CSS approach covers it: allow wrapping up to two lines, truncate the leaf with `text-overflow: ellipsis` past a max width, and collapse middles only if we ever exceed 4 levels (page ancestor chains are the only source of deep trails in this ecosystem).

---

## 2. Where breadcrumbs live in THIS architecture

### 2.1 Ownership: the theme, following the announcement-bar precedent

Breadcrumbs are site chrome, exactly like the announcement bar, topbar, and footer — all theme-owned, all configured via Customizer `promptless_*` theme_mods, all styled exclusively through `--aisb-*` tokens with documented fallbacks. Neither plugin should own this:

- **Promptless WP** builds page *content* (sections); chrome around content is theme territory. Its SEO subsystem is gated on `_aisb_sections`, so it never fires on PRE singles or plain templates — a plugin-owned breadcrumb would have coverage holes.
- **PRE** deliberately has no PHP dependency from the theme and renders inside the theme's chrome (`single-base.php` calls `get_header()`), so a theme-owned breadcrumb automatically wraps CPT singles with zero PRE changes and zero new coupling.

### 2.2 The injection point: one new theme hook (the scalability decision)

Recon confirmed there is **no `do_action` between `</header>` and `<main>`** anywhere in the theme today. That gap is the real architectural finding of this exploration, and closing it is worth more than breadcrumbs alone.

**Recommendation:** add a theme action at the very end of `header.php`:

```php
/**
 * Fires after the site header (and stacked nav, when present),
 * before the template's <main> element.
 *
 * @since 1.3.0
 */
do_action( 'promptless_after_header' );
```

The breadcrumb renderer hooks this action. Why this shape wins:

- **Universal coverage for free.** Every surface that calls `get_header()` gets breadcrumbs: `single.php`, `page.php`, `archive.php`, `search.php`, `404.php`, `index.php`, `woocommerce.php`, **`aisb-fullwidth.php`**, and **PRE's `templates/single-base.php`**. The one template that shouldn't have chrome — `aisb-canvas.php` — skips `get_header()` and is excluded automatically.
- **PRE render-cache safe.** PRE caches the output of `PCPTPages_Renderer::render()` in a transient. Anything rendered *inside* that call gets frozen into the cache (the exact class of bug behind the v0.6.5 style-replay fix). The theme hook fires *outside* the cached region, so the trail always reflects current settings. No PRE code change, no cache invalidation concern.
- **Future chrome slots in cleanly.** Sub-headers, page-level notices, reading-progress bars, contextual CTAs — all future "between header and content" features get a sanctioned insertion point instead of template surgery. This is the "scales for future enhancements" ask made concrete.
- **Child-theme and integration friendly.** Third parties (or FlowMint-era client work) can add or remove at this hook without template overrides.

A companion `promptless_before_footer` is cheap to add in the same release for symmetry, but is optional.

### 2.3 Design-system integration: same recipe as every other chrome element

The breadcrumb bar follows the established chrome pattern exactly (matching `promptless_announcement_bar()` / `promptless_topbar()`):

```
<nav class="promptless-breadcrumbs aisb-section--{light|dark}" aria-label="Breadcrumb">
  <div class="promptless-container">
    <ol class="promptless-breadcrumbs__list"> … </ol>
  </div>
</nav>
```

Token consumption (every reference `var(--aisb-x, fallback)` per the token contract — new tokens require updating the contract first, and none are needed here):

| Role | Token (light) | Token (dark context) | Fallback |
|---|---|---|---|
| Non-link crumbs, separators, current page | `--aisb-color-text-muted` | `--aisb-color-dark-text-muted` | `#6b7280` / `#9ca3af` |
| Crumb links | `--aisb-link-color` (falls back through `--aisb-color-primary`) | `--aisb-link-color-dark` | `#6366f1` |
| Background | transparent by default; optional `--aisb-color-surface` variant | `--aisb-color-dark-surface` | — |
| Optional bottom hairline | `--aisb-color-divider` | dark equivalent | — |
| Typography | `--aisb-section-font-body`, ~0.8125rem | same | system stack |
| Spacing | `--aisb-section-space-xs/sm` vertical padding; container handles horizontal | same | 0.5/1rem |

Theme variant comes from a theme_mod (`inherit content theme` default — reuse `promptless_get_content_theme()` so it tracks the existing Content setting; explicit light/dark override available). Because the variant class is `aisb-section--{light|dark}`, dark mode, WCAG-derived link colors, and any future neo-brutalist treatment flow through the same machinery as everything else — nothing breadcrumb-specific to maintain.

**Build wiring (known-sharp edges from this cycle):** new `assets/css/breadcrumbs.css` must be added to the hardcoded `cssFiles[]` array in `scripts/build-css.js` (it hard-fails otherwise — good), and enqueued in `Promptless_Assets::enqueue_styles()` gated on "breadcrumbs enabled AND current context shows them" so disabled sites ship zero extra bytes. Version-stamp via `PROMPTLESS_THEME_VERSION` (consider the mtime-suffix trick we added to FRE if iteration churn is expected).

### 2.4 The full-bleed hero question (the one honest visual tension)

Two surfaces start with a full-bleed visual flush under the header: AISB fullwidth pages that open with a hero section, and PRE singles with `hero_width: full` (or `hero_layout: overlay`). A contained breadcrumb bar above them re-introduces a strip of page background between header and hero — changing the current seamless look.

The research answer is clear: orientation beats seamlessness on *interior* pages, and the pages where heroes are most cinematic (front page, landing pages) are precisely where breadcrumbs are conventionally suppressed anyway. Options, in recommended order:

1. **Default: render the bar; accept the strip.** It matches the trail's "quiet chrome" role and is what Astra/GeneratePress-class themes do. Transparent background + hairline keeps it minimal.
2. **Per-page kill switch.** Landing pages that want edge-to-edge drama disable breadcrumbs for that page (see §4, per-page override).
3. **Overlay mode (deferred).** Absolutely positioning the trail over the hero (as some real-estate/travel sites do) creates the same contrast-guarantee problem we just spent a design contract solving for PRE heroes (`HERO_CONTRAST_DESIGN.md`). Not v1; if demand appears, it becomes a PRE-side rendering concern with scrim rules, not a theme hack.

---

## 3. Trail construction rules (per context)

`Home` is always the first crumb (links to `home_url('/')`, label filterable/translatable). Leaf is always non-link text with the current title. Every trail is location-based per §1.1.

| Context | Trail | Notes / data source |
|---|---|---|
| Front page | **suppressed** | Universal convention (NN/g: never breadcrumb the homepage) |
| Static page | `Home > Ancestor(s) > Page` | `get_post_ancestors()` walk — the only deep-trail source; middle-collapse beyond 4 levels |
| Blog post | `Home > [Blog] > [Category] > Post` | `[Blog]` = `page_for_posts` title when set. Category = primary/first category, optional via setting (polyhierarchy: NN/g says pick ONE canonical parent — use Yoast/RM "primary category" when those plugins provide it, else first by term order) |
| Blog index (`home`) | `Home > Blog` | |
| **PRE CPT single** | `Home > {CPT plural label} > Post` | Archive crumb from `get_post_type_archive_link()` + registry `label_plural`; **only link the archive crumb when `has_archive` is truthy** — otherwise render label as text or omit. Hierarchical CPTs (registry supports it) walk ancestors like pages |
| CPT archive | `Home > {CPT plural label}` | |
| Category/tag/tax archive | `Home > [Blog] > Term` (post taxes) / `Home > {CPT} > Term` (CPT taxes via registry `taxonomies`) | Hierarchical terms walk `get_ancestors()` |
| Search results | `Home > Search: “{query}”` | Leaf only, no link |
| 404 | `Home > Page not found` | Orientation is the whole point of a 404 |
| Date/author archives | `Home > {label}` | Low value; include for completeness |
| Paginated (`/page/2/`) | Same trail as page 1; leaf unchanged | Never crumb pagination |
| **WooCommerce** | **Defer to WooCommerce's native breadcrumb** | Theme already styles `.woocommerce-breadcrumb` (light/dark). Suppress the theme trail on `is_woocommerce()` contexts to avoid doubles; revisit replacing WC's trail for visual unification as a follow-up |
| AISB canvas template | never renders (no `get_header()`) | Automatic |
| Privacy policy / other special pages | Standard page rules | |

Everything above must flow through one filter — `promptless_breadcrumbs_items( array $items, $context )` — where `$items` is a flat array of `['label' => ..., 'url' => ...|null]`. That single filter is the extensibility contract: SEO-plugin swaps, client customizations, and future PRE-driven enrichment (e.g., a taxonomy crumb between archive and listing) all land there instead of forking the builder.

---

## 4. Enablement UX (Customizer)

New Customizer section **"Breadcrumbs"** (its own section under the existing panels area, or inside `promptless_content_section` — own section recommended since it has ~5 controls). Theme_mods follow the house convention:

| Setting | Default | Control |
|---|---|---|
| `promptless_breadcrumbs_enabled` | **false** (opt-in — a new chrome element appearing after a theme update would violate the "never surprise live sites" principle) | checkbox, `sanitize_checkbox` |
| `promptless_breadcrumbs_contexts` | pages ✓, posts ✓, CPT singles ✓, archives ✓, search ✓, 404 ✓ | multi-checkbox group (one mod per context, e.g. `promptless_breadcrumbs_on_pages`) |
| `promptless_breadcrumbs_theme` | `inherit` (content theme) | select inherit/light/dark, `sanitize_theme_variant`-style |
| `promptless_breadcrumbs_home_label` | `Home` | text, `sanitize_text_field` |
| `promptless_breadcrumbs_show_category` | true | checkbox (posts only) |
| `promptless_breadcrumbs_schema` | true | checkbox "Output BreadcrumbList structured data (auto-disabled when an SEO plugin provides it)" |

Plus a **per-page override**: post meta `_promptless_breadcrumbs` (`default | hide`) via a small meta-box/sidebar control — this is the landing-page kill switch from §2.4. Getter helper `promptless_show_breadcrumbs()` in `template-functions.php` centralizes the whole decision (enabled → context toggle → front-page suppression → Woo deference → per-page override → `promptless_show_breadcrumbs` filter), mirroring `promptless_has_announcement()`.

**Connector parity (queue for later, not v1):** header/footer/announcement chrome is already writable via the AISB connector (`wordpress_footer_set` etc. from this cycle). A `wordpress_breadcrumbs_get/set` pair over the same theme_mods is the natural follow-up so demo/client sessions can enable breadcrumbs without the Customizer — same read-merge-write pattern as `FooterService`. Design the theme_mod names now (above) so the connector surface needs no renames.

---

## 5. Structured data strategy (the collision map)

Current JSON-LD emitters, confirmed by recon — **none emit BreadcrumbList**, so the lane is open, but the gating must be deliberate:

| Emitter | Hook | Gate |
|---|---|---|
| AISB `SchemaGraph` (`@graph`: WebPage, Organization, …) | `wp_head` prio 5 | `_aisb_sections` present; Yoast-merge / supplement / full modes |
| PRE `PCPTPages_Event_Schema` (Event) | `wp_head` prio 20 | registered CPT single + `event_start` role |
| PRE `PCPTPages_Meta_Tags` (meta/OG only) | `wp_head` prio 1 | registered CPT single + **no SEO plugin active** |
| SEO plugins (Yoast/RM/AIOSEO/SEOPress/TSF/Slim) | various | emit their own BreadcrumbList when *their* breadcrumbs are configured |

**Rules for the theme's emitter:**

1. Emit one standalone `<script type="application/ld+json">` BreadcrumbList from the breadcrumb renderer (same data as the visible trail — single source of truth, trivially consistent), on `wp_head` or inline with the nav.
2. **Suppress when an SEO plugin is active** — port PRE's `is_seo_plugin_active()` detection list into a theme helper with its own filter (`promptless_breadcrumbs_schema_enabled`). This is conservative (an SEO plugin present but with breadcrumbs unconfigured means no schema from anyone), which every 2026 guide prefers over risking duplicates. The filter lets a site force it back on.
3. Never merge into AISB's `@graph` — cross-plugin PHP coupling is prohibited by the theme's own architecture rules (CSS-token coupling only). Google handles multiple JSON-LD blocks on a page fine; duplication of the *same type* is the only real hazard, handled by rule 2.
4. Visible trail and schema toggle independently (per §4) but schema never renders where the visible trail doesn't — Google's guidance ties BreadcrumbList to an actual breadcrumb UI.

One deliberate **non-goal**: rendering `yoast_breadcrumb()` / `rank_math_the_breadcrumbs()` output inside our wrapper. It buys config-sync with the SEO plugin at the cost of markup we don't control (styling fragility, no token discipline, APG-pattern uncertainty). The theme trail is self-sufficient; SEO plugins keep schema duty when present. Revisit only if a client needs Yoast's custom trail structures.

---

## 6. Edge-case checklist (implementation acceptance list)

Carrying forward the "find them all in one session" discipline — each of these becomes a smoke check:

- [ ] Front page (static and latest-posts modes): no trail, no schema, no enqueued CSS
- [ ] Page with 5+ ancestors: middle-collapse, no layout break at 390px
- [ ] Post with no category / uncategorized: category crumb skipped cleanly
- [ ] Post when `page_for_posts` unset: `Home > Post` (no phantom Blog crumb)
- [ ] PRE CPT with `has_archive: false`: archive crumb not a link (or omitted)
- [ ] PRE single with `hero_width: full` and `hero_layout: overlay`: bar renders above, hero contrast contract untouched
- [ ] PRE render cache warm before enabling breadcrumbs: trail appears without cache purge (hook is outside `render()` — verify)
- [ ] `_aisb_enabled` page via aisb-fullwidth with hero first section: strip acceptable / per-page hide works
- [ ] aisb-canvas: nothing renders
- [ ] WooCommerce shop/product/cart/checkout: exactly one breadcrumb (WC's), zero theme trail, zero duplicate schema
- [ ] Yoast active with breadcrumbs on: theme trail visible, theme schema suppressed, exactly one BreadcrumbList in source (Rich Results Test clean)
- [ ] No SEO plugin: theme BreadcrumbList validates in Rich Results Test
- [ ] Search with special chars in query: escaped leaf
- [ ] 404: trail renders
- [ ] Dark content theme + light header: bar follows its own theme setting, WCAG AA on links and muted text (both variants)
- [ ] Keyboard: nav landmark reachable, focus-visible rings match theme link treatment, separators silent in VoiceOver
- [ ] RTL: separators flip (use logical properties / `dir`-aware CSS content)
- [ ] Multisite subdirectory install (flowmintdemos pattern): `home_url()` correct per site
- [ ] Trail labels with long titles: leaf ellipsis at max-width
- [ ] `promptless_after_header` fires exactly once per page on every template incl. stacked header layout (after the detached `<nav>`)

---

## 7. Recommended build shape (when we implement)

Phase 1 — theme core (one minor release, ~1.3.0 given the new public hook):
1. `promptless_after_header` action in `header.php` (documented `@since`, one-line doc in THEME docs)
2. `inc/class-promptless-breadcrumbs.php` — trail builder (context rules §3), renderer (APG markup §1.3), schema emitter (§5), all filtered
3. Customizer section + theme_mods + `promptless_show_breadcrumbs()` helper (§4)
4. `assets/css/breadcrumbs.css` + build-script registration + gated enqueue (§2.3)
5. Per-page `_promptless_breadcrumbs` override meta
6. Smoke the §6 checklist on the local lab + Harbor & Oak demo before release

Phase 2 (demand-driven): connector `wordpress_breadcrumbs_get/set`; WC trail unification; hero-overlay placement (with PRE contrast contract); Yoast primary-category awareness.

**Effort estimate:** Phase 1 is roughly announcement-bar-sized — the trail builder is the only genuinely new logic; everything else reuses established patterns.

---

## 8. Open decisions for Breon

1. **Default state** — recommendation is opt-in (off) to honor "never surprise live sites"; confirm.
2. **Separator glyph** — `>` (research favorite) vs `/` (denser, modern). Cosmetic, CSS-only, changeable later.
3. **Category crumb for posts** — on by default, or keep post trails minimal (`Home > Blog > Post`)?
4. **WooCommerce** — accept WC's native trail in v1 (recommended) or unify immediately?
5. **Version target** — fold into next theme minor (1.3.0) or ship alone?

---

## Sources

- [NN/g — Breadcrumbs: 11 Design Guidelines for Desktop and Mobile](https://www.nngroup.com/articles/breadcrumbs/)
- [NN/g — Breadcrumb Navigation Increasingly Useful](https://www.nngroup.com/articles/breadcrumb-navigation-useful/)
- [Baymard — E-Commerce Sites Need 2 Types of Breadcrumbs (68% Get it Wrong)](https://baymard.com/blog/ecommerce-breadcrumbs)
- [Google Search Central — Breadcrumb (BreadcrumbList) structured data](https://developers.google.com/search/docs/appearance/structured-data/breadcrumb)
- [Sitebulb — Breadcrumbs in SEO: What Google's Mobile Change Actually Means](https://sitebulb.com/resources/guides/breadcrumbs-in-seo-what-googles-mobile-change-actually-means/)
- [Search Engine Land — SEO breadcrumbs: Structure, benefits & best practices](https://searchengineland.com/guide/seo-breadcrumbs)
- [W3C WAI-ARIA APG — Breadcrumb Pattern](https://www.w3.org/WAI/ARIA/apg/patterns/breadcrumb/)
- [W3C WAI-ARIA APG — Breadcrumb Example](https://www.w3.org/WAI/ARIA/apg/patterns/breadcrumb/examples/breadcrumb/)
- [IxDF — Mobile Breadcrumbs: 8 Best Practices](https://ixdf.org/literature/article/mobile-breadcrumbs)
- [Rank Math — Making a theme Rank Math compatible](https://rankmath.com/kb/make-theme-rank-math-compatible/)
- [Eleken — UX Breadcrumbs in 2026: Patterns & Best Practices](https://www.eleken.co/blog-posts/breadcrumbs-ux)
