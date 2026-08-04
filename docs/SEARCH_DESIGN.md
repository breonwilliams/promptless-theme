# Promptless Search — Design Exploration & Phased Plan

**Status:** Research + design draft for founder review. No code written.
**Home:** promptless-theme (the experience lives in the theme; PRE contributes searchable data in Phase 2).
**Convention:** follows `BREADCRUMBS_DESIGN_EXPLORATION.md` / `FLOATING_HEADER_OVERLAY.md` — explore, decide, phase.
**Research date:** 2026-08-04.

---

## 1. Who needs this, and how badly (industry read)

Search dependence varies sharply across the verticals this stack serves. That ranking, not a generic "sites need search," drives every scoping decision below:

| Tier | Verticals | Search reality |
|---|---|---|
| **Search-critical** | Higher ed, associations/nonprofits (CAEP-shaped), government-adjacent | Large, deep, multi-audience sites. Users arrive with a WORD, not a path — "transcript," "CEU," "bursar." Site search is a primary navigation mode; every major .edu ships a header search. |
| **Filter-driven** | Real estate (Harbor & Oak-shaped), directories, job boards | Users browse structured inventory. The schema-driven PostGrid filters ALREADY serve this correctly — a keyword box is secondary, filters are primary. Do not confuse the two jobs. |
| **Nav-driven** | Law, dental, HVAC, restaurants, small SaaS marketing | 5–30 pages; the menu IS the finding mechanism. Search adds chrome without value and NN/g's guidance is blunt: don't ship search on sites where browsing is faster. |

**Decision:** Search is a **header setting, default OFF**. One connector/customizer toggle turns it on. This is the constraint philosophy applied: small sites never grow an unused magnifying glass; content-heavy sites enable it in one line. The demo site enables it (16+ pages, listings, products — legitimately search-worthy, and it becomes the showcase).

## 2. Architecture — three layers, matching the stack

**Layer 1 — Theme (the experience, Phase 1).** Header trigger + full-screen search overlay + the results page. All UI, all tokens, all a11y.

**Layer 2 — Engine (deliberately boring).** WordPress native search, unmodified, as the baseline. We do NOT build a search engine (bucket-3 discipline — that's a decision we eliminate, not a feature we lack). Two honest facts drive this: (a) native search is weak at relevance (LIKE queries, no weighting), and (b) the fix is a solved problem — Relevanssi/SearchWP filter the standard query, and because our results page uses the standard loop and our live results use core REST, **both plugins upgrade Promptless Search automatically with zero integration work**. We document that pairing instead of rebuilding it. What we DO own in Phase 2: making PRE's structured fields findable at all (native search never looks at postmeta — a listing's "Shorecrest" neighborhood field is invisible to it today).

**Layer 3 — Instant results (Phase 1).** Debounced (250ms) calls to core's `/wp/v2/search` REST endpoint, grouped by type, top ~6 with a "View all results →" handoff to the results page. Core REST already respects post-type visibility (`show_in_rest`), draft/private exclusion, and includes PRE CPTs and Woo products for free. REST also bypasses page caching naturally.

## 3. UX decisions (each grounded, not vibed)

- **Trigger: magnifying-glass icon button in the header actions slot, built as a SIBLING of the cart icon (founder-confirmed, 2026-08-04).** `promptless_header_cart()` is the implementation template: same slot, same customizer-toggle-default-off philosophy, and the new `.promptless-header__search-toggle` inherits the `.promptless-header__cart-toggle` treatment (size, hover, focus ring, light/dark) so the actions slot reads as one family. The intentional divergence: cart click opens a small anchored dropdown (its content is a few line items); search click opens the full-screen overlay (its content is an input plus live results) - same icon-then-surface grammar, scaled to the surface's content. Optional text label via setting. NN/g's research: the magnifying glass is one of the few universally recognized icons, users expect search in the top-right, and icon-only triggers measurably reduce search usage versus visible fields — but a persistent open field doesn't fit any of our three header layouts (the floating pill has no room; stacked/default have CTA priority). The overlay pattern (below) resolves the tension the same way virtually every current .edu site does: recognizable icon, then a LARGE input on demand. The optional label ("Search") is the NN/g-friendly setting for search-critical sites.
- **Interaction: full-screen overlay, not a dropdown or inline expansion.** This is the dominant higher-ed and 2026-general pattern for a reason: it works identically across all three header layouts (nothing to squeeze into the pill), it's mobile-identical (no separate mobile pattern to design or break), it gives the input NN/g's recommended generous width, and it gives instant results honest space. It also reuses everything the modal work taught us: focus trap, Esc to close, focus return to trigger, visual-viewport sizing with safe-area padding (finding #12's lesson — never 100vh), scroll lock.
- **Keyboard: `/` and Cmd/Ctrl+K open, Esc closes.** The 2026 expectation set by GitHub/Docs-style command palettes; costs nothing, delights the audience that notices. Never steals keys while focus is in an input.
- **A11y: the ARIA combobox pattern (APG), done fully.** `role="combobox"` input + `role="listbox"` results, arrow-key traversal, `aria-activedescendant`, and an `aria-live=polite` region announcing "N results for X" (the FRE lightbox/live-region muscle). This is the difference between "has search" and "has search a screen-reader user can operate" — and it's a marketable line for higher-ed buyers with accessibility mandates.
- **Results page stays, and matters.** The overlay is convenience; `?s=` URLs are SEO-visible, shareable, cacheable, and the no-JS fallback (the trigger degrades to a plain GET form). `search.php` already renders archive cards + pagination; Phase 1 polishes its states, Phase 2 adds type tabs (All / Pages / Listings / Products…) reusing the PostGrid filter visual language.
- **States that make or break trust:** minimum 2 characters before querying; loading skeleton (no spinner-flash); no-results state that offers the top menu destinations instead of a dead end; long-query truncation; in-flight request aborted on each keystroke.
- **Design tokens throughout, finding-#15 discipline:** overlay backdrop and surfaces follow theme light/dark, every text/surface pairing uses the surface-corrected variables, and the rendered-contrast sweep covers the overlay before ship.

## 4. Where the code lives

- **Theme:** trigger partial in `promptless-header__actions` (all three layouts), overlay template part + `search-overlay.js` (vanilla, mirrors lightbox patterns) + `search.css` (mtime-versioned via `asset_version()`), customizer section (enable / label on-off / scope), states on `search.php`.
- **Connector:** `wordpress_header_get/set` gain `search: { enabled, show_label, include_post_types }` — Claude can turn search on when a brief says "make it searchable."
- **PRE (Phase 2):** per-field `searchable` flag; on save, flagged values copy into a `_pre_search_blob` meta; one `posts_search` filter ORs a LIKE against it. Small, honest, and it makes structured fields findable without an indexing engine. Registered via the same filterable pattern as the validate_site checks.
- **Explicitly not ours:** external engines (Algolia/Elastic), AI/semantic search (watch item — a connector-era differentiator someday, not a v1 feature), federated multi-site search, and search analytics dashboards (v1 logs nothing; revisit with demand).

## 5. Edge cases (accounted for up front)

Empty/1-char queries (no request); special characters and encoding (REST handles, results page escapes — existing pattern); Woo active or not (`include_post_types` respects what exists); PRE CPTs opt out via `exclude_from_search` as today; password-protected posts (core excludes content, verify excerpt behavior in QA); mega-menu open when search opens (close it); sticky/floating header z-index vs overlay (overlay owns the top layer); iOS keyboard resizing (visual-viewport sizing, not 100vh); reduced-motion (fade only, no zoom); RTL (logical properties); page cache (REST + `?s=` both cache-safe); translations (all strings through the theme text domain).

## 6. Phases

| Phase | Scope | Size |
|---|---|---|
| **1 — Theme MVP** | Toggle + trigger (3 layouts), overlay with instant REST results, full combobox a11y, keyboard shortcuts, results-page states, customizer + connector settings, demo-site enablement | M |
| **2 — Structured data** | PRE searchable-field flag + search blob, results-page type tabs, grouped-by-type overlay sections | S–M |
| **3 — Watch items** | Documented Relevanssi/SearchWP pairing page (help site), AI-assisted search exploration, search analytics | — |

## 7. Success criteria

1. Keyboard-only and VoiceOver passes on the overlay (open, query, traverse, select, close, focus return).
2. Works on all three header layouts, light and dark, mobile and desktop, with zero layout-specific bugs.
3. Rendered-contrast sweep clean over the overlay in both themes.
4. A PRE listing is findable by a Phase-2 searchable field value ("Shorecrest" finds the listing).
5. Demo site ships with search enabled as the living showcase; Lighthouse a11y stays clean.
6. With Relevanssi active, both overlay and results page reflect its ranking with no Promptless changes — proving the boring-engine bet.

## 8. Open questions for review

1. **Default label:** icon-only or icon+"Search" text as the shipped default?
   > **Answered 2026-08-04 (founder): icon-only, matching the cart icon precedent** - actions-slot consistency wins; the label stays one toggle away for search-critical sites.
2. **Scope default:** pages + posts + PRE CPTs + products all-in by default, or pages/posts only with CPTs opt-in?
3. Does the demo's how-it-was-built page get a line when search ships ("enabled by one setting"), as part of the showcase story?

---

**END**
