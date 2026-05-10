# Wave 2 Plan: Kill the `[href][href][href]…` CSS Specificity Hack

This is the design doc for finding C1 from `THEME_AUDIT.md`. Wave 2 was deferred during the original audit-execution session because investigation revealed:

1. The hack count is **228 occurrences across four CSS files** (not the ~18 the audit estimated).
2. The root cause is a **symmetric hack on the plugin side** — `plugins/ai-section-builder-modern/src/styles/core-components.css:67` declares `.aisb-section--light a:not(.aisb-btn):not(.button):not(.remove):not(.add_to_cart_button):not(.added_to_cart):not(.checkout-button):not(.wc-forward):not(.product_type_grouped):not(.product_type_external):not(.page-numbers)` for specificity **0,11,1**. The theme's `[href][href][href]…` chains are the counter-hack to outweigh that.
3. Visual verification is mandatory and can't be done from a sandbox session — touching 228 CSS rules without a live browser produces silent regressions.

The goal of this doc: enter the Wave 2 execution session with the architectural choice already settled and the migration order already mapped, so the session is pure execution + browser verification.

---

## What's actually broken

`.aisb-section--light` and `.aisb-section--dark` are the plugin's "theme variant" classes. The Promptless theme uses them on its own footer (`.promptless-footer.aisb-section--light`) and other chrome elements **on purpose** — to inherit the plugin's color tokens (`--aisb-color-text`, `--aisb-color-primary`, etc.) so that when a user changes their global palette, the chrome follows.

The plugin's `core-components.css:67` selector says: "Any `<a>` inside `.aisb-section--light` that isn't one of these 10 button-ish classes gets the brand link color." The intent: unstyled links inside section bodies should look like brand links. Reasonable intent.

The collision: theme chrome links (footer brand, footer nav, header nav, archive card title) ALSO match this selector — they're inside `.aisb-section--light` and don't have any of the 10 excluded classes. So the plugin paints them brand-color when the theme wants them to follow chrome rules (e.g. footer brand stays text-color, doesn't pulse blue on hover).

The current theme "fix" is `[href][href][href][href][href][href][href][href]` (8–9× repeated) which raises specificity to 0,12,0 and barely beats the plugin's 0,11,1. It works. It's also undocumented except for one comment, fragile to any change in the plugin's selector chain, and the same anti-pattern the plugin's own CLAUDE.md bans (`!important` is forbidden; this is just `!important` cosplaying as specificity).

---

## Three viable paths

### Path A — Plugin-side fix (the root cause)

Refactor `core-components.css:67` so it doesn't fight theme chrome. Two sub-options:

**A1. Wrap the rule in `@layer plugin-defaults`.** Theme rules outside any layer automatically win over rules inside any named layer. One plugin commit, zero theme changes, zero specificity arithmetic. The plugin keeps its current 10-`:not()` chain (it still wants high specificity *within* its own scope, e.g. to beat unrelated CSS) but the layer demotes it relative to anything theme-side that wants to override.

**A2. Scope the selector tighter.** Change `.aisb-section--light a:not(.aisb-btn)…` to something like `.aisb-section--light .aisb-section__body a:not(.aisb-btn)` — apply only to descendants of plugin-rendered content, not to descendants of `.aisb-section--light` in general. Theme chrome that opts into `aisb-section--light` for color-token inheritance no longer triggers the link-paint rule. This requires auditing every section type's HTML to confirm the new descendant class is always present where the brand-link paint is wanted; medium effort plugin-side, but the cleanest semantic.

**Trade-offs of A:**
- ✅ Removes the root cause. All 228 theme-side hacks become deletable.
- ✅ Future theme components (or third-party themes building on the plugin) get the same benefit for free.
- ❌ Changes the plugin, not the theme. Out of scope for "theme audit" boundary the user originally set.
- ❌ Plugin tests would need to be re-snapshotted (the snapshot harness emits CSS that may shift order or specificity).
- ⚠ A2 requires verifying every section's HTML. A1 is mechanically simpler but `@layer` is a 2022 CSS feature — browser support is now ~96% (Baseline Widely Available since 2023) so this is a non-issue for modern sites.

### Path B — Theme-side `@layer` wrap

Wrap all theme chrome CSS in `@layer promptless-chrome`. Layered rules lose to unlayered rules; unlayered theme rules then beat the plugin's 0,11,1 selector regardless of specificity. The theme's `[href]` chains can be stripped entirely.

**Implementation sketch:**
```css
/* assets/css/footer.css */
.promptless-footer__brand a { color: var(--section-text); text-decoration: none; }
/* No more [href][href][href]... — normal-specificity selector wins because
   the plugin's rule lives inside `@layer` (after Path A) OR because we
   re-emit theme rules outside any layer (current state). */
```

If we go with Path B alone (no plugin change), we need to either:
- **B1.** Wrap PLUGIN CSS in `@layer plugin` from the theme side (impossible — we don't control the plugin's stylesheet).
- **B2.** Use the `@layer` declaration order trick: declare an empty layer `@layer plugin, theme;` first in the theme's earliest-loaded CSS, then put theme rules inside `@layer theme`. This gives `theme` higher precedence than `plugin` regardless of source order. Plugin rules need to be MOVED into the `plugin` layer for this to work — which means the plugin still needs cooperation. So B2 alone is no different from path A1.
- **B3.** Wrap ONLY theme chrome in `@layer`-elevated rules using inverse logic: put fallback theme rules in a low-priority layer, then declare overrides in `@layer override` with explicit precedence. Convoluted; not recommended.

**Net:** Pure theme-side @layer doesn't work without plugin coordination. B reduces to A in practice.

### Path C — Per-component CSS variables on theme chrome (no `@layer`)

Define theme-component-scoped variables on the chrome elements:

```css
/* footer.css */
.promptless-footer {
  /* These tokens are what the chrome's link rules read. They cascade
     normally — not subject to specificity in the property-resolution
     sense. */
  --promptless-footer-link: var(--section-text);
  --promptless-footer-link-hover: var(--section-primary);
}
.promptless-footer__brand a {
  color: var(--promptless-footer-link);
  text-decoration: none;
}
```

**Doesn't work alone.** The plugin's 0,11,1 selector still wins in the property cascade — `color: var(--aisb-smart-light-section-link, ...)` from the plugin overrides the theme's `color: var(--promptless-footer-link)` because the plugin's selector has higher specificity. CSS variables don't bypass specificity for property resolution; they're just values.

**Could work if combined with Path A** (plugin scopes its selector down). Then the theme's chrome rules can be normal-specificity AND read from component-scoped variables for clarity.

### Recommended path

**A1 + C combined.**

- **A1** (plugin: wrap `core-components.css:67` in `@layer plugin-defaults`) is a one-line plugin change with zero risk to existing site rendering. It demotes the plugin's catch-all link-paint rule below any theme rule that doesn't explicitly opt into a layer.
- **C** (theme: introduce per-component CSS variables for chrome links/text, replace the 228 `[href]` hacks with normal-specificity rules reading those variables) is the architectural improvement that makes the theme's link-color decisions explicit and overridable.

Together: plugin stops fighting theme chrome, theme stops needing to fight back, the variable cascade becomes the documented contract between them.

---

## Why not the alternatives

- **Path A alone** (just plugin change, theme keeps the `[href]` hacks): works mechanically but leaves the technical debt in the theme. No reason to keep the hacks once the root cause is gone.
- **Path A2 alone** (plugin tighter selector): cleaner semantically but requires plugin-side audit of every section's HTML markup. A1 is faster and accomplishes the same outcome.
- **Path C alone**: doesn't actually win the cascade; needs A.
- **Pure `!important` everywhere on theme**: forbidden by CLAUDE.md and would still be the same arms race, just louder.

---

## Migration order (when Wave 2 executes)

Execute in this order so each step is testable in isolation:

### Step 1 — Plugin: wrap `core-components.css:67` in `@layer`

```css
/* plugins/ai-section-builder-modern/src/styles/core-components.css */
@layer aisb-defaults {
  .aisb-section--light a:not(.aisb-btn):not(.button):not(.remove):not(.add_to_cart_button):not(.added_to_cart):not(.checkout-button):not(.wc-forward):not(.product_type_grouped):not(.product_type_external):not(.page-numbers) {
    color: var(--aisb-smart-light-section-link, var(--aisb-color-primary));
  }
  /* ... :hover and dark-variant rules likewise ... */
}
```

Wrap the surrounding rules consistently (likely lines 67–88 based on the audit's grep). Do NOT wrap `section-tokens.css` link rules — those use the `[class*="__body"]` etc. scoping which is already correctly scoped to plugin internals.

**Verification before continuing:** load the plugin's snapshot suite (`php tests/test-section-renderer.php all`). Confirm 12/12 still pass. The snapshots compare emitted HTML byte-for-byte; CSS changes don't affect them, but the test harness exercises the full render path and would catch any CSS-loading regression. Then visually load a section page in the browser — link colors inside section content should still paint correctly (they read from `[class*="__body"]` selectors which are unaffected).

### Step 2 — Theme: introduce per-component link tokens

Add to a new `assets/css/_chrome-tokens.css` (or inline at the top of each chrome stylesheet — designer call):

```css
.promptless-footer {
  --promptless-footer-link-color: var(--section-text);
  --promptless-footer-link-hover-color: var(--section-primary);
  --promptless-footer-brand-color: var(--section-text);
  /* No hover state for brand — it's identity, not a CTA. */
}
.promptless-header {
  --promptless-header-nav-link-color: var(--section-text);
  --promptless-header-nav-link-hover-color: var(--section-primary);
  --promptless-header-brand-color: var(--section-text);
}
.promptless-archive {
  /* Card titles, meta, "read more" — same pattern. Define in archive.css. */
}
```

The variables should default to plugin tokens where it makes sense (so user palette changes still flow through chrome) and to hardcoded fallbacks when the plugin is inactive. The variable name encodes the scope (`--promptless-footer-…`) so a future maintainer can grep one component's color decisions and find them all in one place.

**Verification:** no visual change yet (no rules read the new tokens — they're declared but unused). Load the site to confirm no regression, then continue.

### Step 3 — Theme: rewrite chrome link rules to read the new tokens, strip `[href]` chains

Per file:

- `assets/css/footer.css` — ~80 instances. Brand link, brand-text link, nav links (light + dark variants).
- `assets/css/header.css` — ~40 instances.
- `assets/css/archive.css` — ~60 instances. Card titles, meta, pagination.
- `assets/css/woocommerce.css` — ~50 instances. Cart/account links.

For each rule, replace:

```css
/* BEFORE */
.promptless-footer.aisb-section--light .promptless-footer__brand-text a[href][href][href][href][href][href][href][href] {
  color: var(--section-text);
  text-decoration: underline;
}

/* AFTER */
.promptless-footer__brand-text a {
  color: var(--promptless-footer-link-color);
  text-decoration: underline;
}
```

The `.aisb-section--light` qualifier is still useful where light/dark theming differs — but that's now handled by the variable's value (set differently per `aisb-section--light`/`aisb-section--dark` scope at the token-declaration site), not by selector specificity gymnastics.

**Verification per file:** after each file's rewrite, browser-test the corresponding chrome region. Footer first (smallest, easiest to eyeball). Header second. Archive third. WooCommerce fourth.

### Step 4 — Cleanup

- Remove the comment `/* high specificity to override plugin link rules */` and similar — they no longer describe reality.
- Update `THEME_AUDIT.md` C1 to "completed" with a link to the commit.
- Run `npm run build` to regenerate `.min.css` files.
- Snapshot the visual diff with the audit script (`tests/scripts/audit-theme-tokens.js`) to confirm WCAG contrast didn't regress.

---

## Risk register

| Risk | Mitigation |
|---|---|
| Browser support: `@layer` is from 2022, sites on ancient browsers may render chrome with plugin colors | Browser support is ~96% Baseline Widely Available; if a target audience needs older support, fall back to Path A2 (selector scoping) |
| Plugin's snapshot tests fail after layer wrap (CSS source order changes) | Re-snapshot, verify the only diff is the `@layer` wrapper, accept the new baseline |
| Some chrome link is missed in Step 3 grep, keeps reading old plugin color | Use the `[href][href]` count as a watermark — `grep -rn "href\].*href\].*href\]" assets/css | wc -l` should drop to 0 when complete |
| Editor preview (block editor iframe) doesn't get the new tokens | Editor styles are a separate Wave 4 item (I3); not blocked by this work, but defer the editor token wire-up to its own session |

---

## Out of scope for Wave 2

- Editor-style.css token migration (Wave 4 / I3)
- Removing `aisb-section--light`/`aisb-section--dark` from chrome elements entirely (would lose plugin color-token inheritance — different design choice, not on the table)
- Migrating the theme to a block theme / FSE (Wave 4 / I6, separate decision)
- Updating the plugin's `:not(...)` chain itself (not needed once Step 1 wraps it in `@layer`)

---

## Estimated effort

- Step 1 (plugin): 30 min including snapshot verification
- Step 2 (token declaration): 1 hour
- Step 3 (file-by-file rewrite + browser test): 4–6 hours across all four CSS files
- Step 4 (cleanup): 30 min

**Total: ~1 working day with a live browser open.**

Recommended: schedule a half-day block for Steps 1–2, browser-test, commit. Then a half-day block for Steps 3–4, with commits per file rewritten so any visual regression has a small rollback target.

---

## Pre-flight checklist for the next session

Before opening this work, confirm:

- [ ] You can render the local Flywheel site in a browser to verify changes
- [ ] Plugin snapshot tests are green (`php tests/test-section-renderer.php all` from the plugin dir)
- [ ] Latest plugin and theme are committed (clean git status before starting)
- [ ] You've decided whether the plugin-side `@layer` change (Step 1) is acceptable scope. If yes, this plan is execution-ready. If no, the only viable path is Path A2 (plugin selector scoping) which needs a separate design session before we can act.
