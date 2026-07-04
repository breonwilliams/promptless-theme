# Promptless Theme Release Guide

A comprehensive guide for releasing new versions of the Promptless WordPress theme.

> **⚠️ NEVER hand-assemble a release zip.** `./create-release.sh` is the only sanctioned packaging path. The v1.2.5 incident: a manually packaged zip (built with `cp -r dir/*`-style commands) flattened the directory structure, shipped without `inc/`, and fataled every site that updated. The script verifies critical files in both the staging folder and the final zip; ad-hoc `cp`/`zip` commands bypass every guard.

---

## Quick Release Steps (TL;DR)

```bash
# 1. Update version in all 3 files (see checklist below)
# 2. Update changelog in readme.txt
# 3. Build and create release
npm run build
./create-release.sh

# 4. Test the ZIP on a clean WordPress install
# 5. Commit, tag, and release
git add style.css readme.txt functions.php inc/ assets/
git commit -m "Release: Promptless Theme v1.X.X"
git tag v1.X.X-theme
git push && git push --tags

# 6. Create GitHub release
gh release create v1.X.X-theme \
  --title "Promptless Theme v1.X.X" \
  --notes "Release notes here" \
  release/promptless-v1.X.X.zip
```

---

## Version Update Checklist

**CRITICAL**: Version numbers must match in ALL 3 locations:

| File | Line | Pattern |
|------|------|---------|
| `style.css` | Line 6 | `Version: X.X.X` |
| `readme.txt` | Line 7 | `Stable tag: X.X.X` |
| `functions.php` | Line 17 | `define( 'PROMPTLESS_THEME_VERSION', 'X.X.X' );` |

The release script will verify these match before creating the ZIP.

---

## Build Process

### Prerequisites

1. Node.js installed (v16+)
2. Dependencies installed: `npm install`

### Build Commands

```bash
# Generate minified CSS and JS
npm run build

# This creates:
# - assets/css/header.min.css
# - assets/css/footer.min.css
# - assets/css/archive.min.css
# - assets/css/woocommerce.min.css
# - assets/js/navigation.min.js
# - assets/js/customizer-preview.min.js
```

---

## Release Script Usage

The `create-release.sh` script automates release creation:

```bash
./create-release.sh
```

**What it does:**
1. Extracts version from style.css
2. Verifies version numbers match in all 3 files
3. Checks for minified assets (runs build if missing)
4. Creates clean ZIP excluding:
   - `node_modules/`
   - `package.json` / `package-lock.json`
   - `scripts/`
   - `release/`
   - `.git/` / `.gitignore`
   - `create-release.sh`
   - `THEME_RELEASE_GUIDE.md`
   - `.DS_Store`
5. Outputs ZIP to `release/promptless-vX.X.X.zip`
6. Displays contents and next steps

---

## WordPress.org Compliance Checklist

### Required Files
- [x] `style.css` - Theme stylesheet with proper header
- [x] `index.php` - Main template
- [x] `screenshot.png` - Theme screenshot (1200x900 recommended)
- [x] `readme.txt` - Theme documentation

### Required Headers (style.css)
- [x] Theme Name
- [x] Author
- [x] Description
- [x] Version
- [x] Requires at least
- [x] Tested up to
- [x] Requires PHP
- [x] License
- [x] License URI
- [x] Text Domain
- [x] Tags

### Code Requirements
- [x] GPL-compatible license
- [x] Proper escaping functions (`esc_html()`, `esc_attr()`, `esc_url()`, etc.)
- [x] No plugin territory violations
- [x] Text domain matches theme slug ("promptless")
- [x] Translation-ready strings
- [x] Accessibility features (skip links, focus states)

### What NOT to Include in ZIP
- ❌ `node_modules/`
- ❌ `package.json` / `package-lock.json`
- ❌ Development scripts
- ❌ `.git/` directory
- ❌ Build tools
- ❌ Test files

---

## Testing Checklist

### Before Release
- [ ] Theme activates without errors
- [ ] No PHP errors with `WP_DEBUG=true`
- [ ] No JavaScript console errors
- [ ] Responsive design works on mobile/tablet
- [ ] Skip link is visible on focus
- [ ] Keyboard navigation works
- [ ] Dark mode toggle functions
- [ ] Header CTA button works
- [ ] Footer displays correctly
- [ ] Archive pages list posts correctly
- [ ] Single post/page displays correctly
- [ ] Comments section works (if enabled)

### WooCommerce Integration (if applicable)
- [ ] Shop page displays products
- [ ] Single product page works
- [ ] Cart page has proper styling
- [ ] Checkout page has proper styling
- [ ] My Account pages work

### Plugin Integration
- [ ] Works with Promptless WP plugin disabled
- [ ] Inherits styles when plugin is enabled
- [ ] Global Settings colors apply correctly
- [ ] Typography settings apply correctly

---

## Git Workflow

### Branching
- `main` - Stable releases only
- Feature branches for development

### Commit Message Format
```
Release: Promptless Theme vX.X.X
```

### Tags
Use `-theme` suffix to distinguish from plugin releases:
```bash
git tag v1.1.0-theme
```

---

## GitHub Release

### Create Release with CLI

```bash
gh release create v1.1.0-theme \
  --title "Promptless Theme v1.1.0" \
  --notes "$(cat <<'EOF'
## What's New

### Features
- Feature description

### Bug Fixes
- Fix description

### Performance
- Performance improvement
EOF
)" \
  release/promptless-v1.1.0.zip
```

### Release Notes Template

```markdown
## What's New in vX.X.X

### Features
- New feature description

### Bug Fixes
- Fixed: Description of bug fix

### Performance
- Performance improvement description

### Developer Notes
- Technical change description
```

---

## WordPress.org Submission

1. Go to https://wordpress.org/themes/upload/
2. Upload the ZIP file
3. Wait for review (can take several weeks for initial review)
4. Address any reviewer feedback
5. Theme goes live after approval

### For Theme Updates
After initial approval, updates are automatic via SVN or the upload form.

---

## Troubleshooting

### Version Mismatch Error
The release script will fail if versions don't match. Update all 3 files:
- `style.css` line 6
- `readme.txt` line 7
- `functions.php` line 17

### Missing Minified Assets
Run `npm run build` before creating release.

### ZIP Too Large
Ensure `node_modules/` is excluded. The release script handles this automatically.

### Theme Review Rejection
Common issues:
- Escaping functions missing
- Plugin territory violations
- Non-GPL resources
- Missing text domain

---

## File Structure Reference

```
promptless/
├── assets/
│   ├── css/
│   │   ├── archive.css          # Source
│   │   ├── archive.min.css      # Built
│   │   ├── footer.css           # Source
│   │   ├── footer.min.css       # Built
│   │   ├── header.css           # Source
│   │   ├── header.min.css       # Built
│   │   ├── woocommerce.css      # Source
│   │   └── woocommerce.min.css  # Built
│   └── js/
│       ├── navigation.js        # Source
│       ├── navigation.min.js    # Built
│       ├── customizer-preview.js # Source
│       └── customizer-preview.min.js # Built
├── inc/
│   ├── class-promptless-assets.php
│   ├── class-promptless-customizer.php
│   ├── class-promptless-integration.php
│   ├── class-promptless-setup.php
│   └── template-functions.php
├── template-parts/
│   ├── content/
│   ├── footer/
│   └── header/
├── functions.php
├── index.php
├── style.css
├── readme.txt
├── screenshot.png
└── [other template files]
```

---

## Version History

| Version | Date | Notes |
|---------|------|-------|
| 1.1.0 | 2025-XX-XX | Conditional CSS, PageSpeed optimization |
| 1.0.9 | 2025-XX-XX | Page header spacing fix |
| 1.0.8 | 2025-XX-XX | Performance optimization |
| ... | ... | ... |
