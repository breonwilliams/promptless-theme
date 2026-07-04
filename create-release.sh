#!/bin/bash

# Promptless Theme Release Script
# Creates a clean distribution package for WordPress.org submission

set -e

THEME_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$THEME_DIR"

# Extract version from style.css
VERSION=$(grep -m1 "^Version:" style.css | sed 's/Version: *//' | tr -d ' \r')

if [ -z "$VERSION" ]; then
    echo "❌ ERROR: Could not extract version from style.css"
    exit 1
fi

echo "🚀 Creating Promptless Theme v${VERSION} release package..."

# ============================================
# VERSION CONSISTENCY CHECK
# ============================================
echo ""
echo "🔢 Verifying version consistency across all files..."

# Extract version from style.css header
STYLE_VERSION="$VERSION"

# Extract Stable tag from readme.txt
README_VERSION=$(grep "^Stable tag:" readme.txt | sed 's/Stable tag: *//' | tr -d ' \r')

# Extract version from functions.php constant
FUNCTIONS_VERSION=$(grep "PROMPTLESS_THEME_VERSION" functions.php | sed "s/.*'\([0-9.]*\)'.*/\1/")

VERSION_MISMATCH=0

echo "  style.css Version:           $STYLE_VERSION"
echo "  readme.txt Stable tag:       $README_VERSION"
echo "  functions.php VERSION:       $FUNCTIONS_VERSION"

if [ "$STYLE_VERSION" != "$README_VERSION" ]; then
    echo "  ❌ MISMATCH: style.css ($STYLE_VERSION) != readme.txt ($README_VERSION)"
    VERSION_MISMATCH=1
fi

if [ "$STYLE_VERSION" != "$FUNCTIONS_VERSION" ]; then
    echo "  ❌ MISMATCH: style.css ($STYLE_VERSION) != functions.php ($FUNCTIONS_VERSION)"
    VERSION_MISMATCH=1
fi

if [ $VERSION_MISMATCH -eq 1 ]; then
    echo ""
    echo "❌ ERROR: Version mismatch detected!"
    echo ""
    echo "All versions must match. Update the following files:"
    echo "  1. style.css      → Version: x.x.x"
    echo "  2. readme.txt     → Stable tag: x.x.x"
    echo "  3. functions.php  → define( 'PROMPTLESS_THEME_VERSION', 'x.x.x' )"
    echo ""
    echo "Package creation ABORTED."
    exit 1
fi

echo "  ✅ All versions match: $VERSION"

# ============================================
# CHECK MINIFIED ASSETS
# ============================================
echo ""
echo "🔍 Checking for minified assets..."

MISSING_MIN=0

# Check CSS files
for css in header footer archive woocommerce; do
    if [ -f "assets/css/${css}.css" ]; then
        if [ ! -f "assets/css/${css}.min.css" ]; then
            echo "  ❌ Missing: assets/css/${css}.min.css"
            MISSING_MIN=1
        else
            echo "  ✓ assets/css/${css}.min.css"
        fi
    fi
done

# Check JS files
for js in navigation customizer-preview; do
    if [ -f "assets/js/${js}.js" ]; then
        if [ ! -f "assets/js/${js}.min.js" ]; then
            echo "  ❌ Missing: assets/js/${js}.min.js"
            MISSING_MIN=1
        else
            echo "  ✓ assets/js/${js}.min.js"
        fi
    fi
done

if [ $MISSING_MIN -eq 1 ]; then
    echo ""
    echo "❌ ERROR: Minified assets are missing!"
    echo ""
    echo "Run 'npm run build' first to generate minified files."
    echo "Package creation ABORTED."
    exit 1
fi

echo "  ✅ All minified assets present"

# ============================================
# CREATE RELEASE PACKAGE
# ============================================
echo ""
echo "📦 Creating release package..."

# Clean up any existing release directory
rm -rf release/
mkdir -p release/promptless

# Copy theme files (excluding dev files)
echo "📁 Copying theme files..."

# Copy PHP files
cp *.php release/promptless/

# Copy style.css
cp style.css release/promptless/

# Copy other required files
cp readme.txt release/promptless/
cp screenshot.png release/promptless/
cp theme.json release/promptless/

# Copy directories
cp -r assets release/promptless/
cp -r inc release/promptless/
cp -r template-parts release/promptless/
cp -r woocommerce release/promptless/
cp -r languages release/promptless/ 2>/dev/null || mkdir release/promptless/languages

# Remove development/test files
find release/promptless -name ".DS_Store" -delete
find release/promptless -name "Thumbs.db" -delete
find release/promptless -name "*.map" -delete

echo "  ✓ Theme files copied"

# ============================================
# VERIFY CRITICAL FILES
# ============================================
echo ""
echo "🔍 Verifying critical files..."

CRITICAL_FILES=(
    "release/promptless/style.css"
    "release/promptless/functions.php"
    "release/promptless/index.php"
    "release/promptless/header.php"
    "release/promptless/footer.php"
    "release/promptless/screenshot.png"
    "release/promptless/readme.txt"
    "release/promptless/inc/class-promptless-setup.php"
    "release/promptless/assets/css/header.min.css"
    "release/promptless/assets/js/navigation.min.js"
)

MISSING_FILES=0
for file in "${CRITICAL_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        echo "  ❌ MISSING: ${file#release/promptless/}"
        MISSING_FILES=$((MISSING_FILES + 1))
    else
        echo "  ✓ ${file#release/promptless/}"
    fi
done

if [ $MISSING_FILES -gt 0 ]; then
    echo ""
    echo "❌ ERROR: $MISSING_FILES critical files are missing!"
    echo "Package creation ABORTED."
    exit 1
fi

echo "  ✅ All critical files present"

# ============================================
# CREATE ZIP
# ============================================
echo ""
echo "📦 Creating ZIP archive..."

cd release/
zip -r "promptless-v${VERSION}.zip" promptless -q

# ============================================
# VERIFY ZIP INTERNAL STRUCTURE
# ============================================
# Guards against a flattened/hand-assembled archive (the v1.2.5 incident:
# a manually built zip lost the inc/ directory level and fataled on every
# site at functions.php's require_once). This checks the SHIPPED ARTIFACT
# itself, not the staging folder.
echo ""
echo "🔍 Verifying ZIP internal structure..."

ZIP_MANIFEST=$(unzip -l "promptless-v${VERSION}.zip")

REQUIRED_ZIP_PATHS=(
    "promptless/functions.php"
    "promptless/inc/class-promptless-setup.php"
    "promptless/inc/template-functions.php"
    "promptless/assets/css/header.min.css"
    "promptless/assets/js/navigation.min.js"
    "promptless/template-parts/archive/card.php"
    "promptless/woocommerce/cart/mini-cart.php"
)

ZIP_STRUCTURE_OK=1
for path in "${REQUIRED_ZIP_PATHS[@]}"; do
    if echo "$ZIP_MANIFEST" | grep -q " ${path}$"; then
        echo "  ✓ $path"
    else
        echo "  ❌ MISSING FROM ZIP: $path"
        ZIP_STRUCTURE_OK=0
    fi
done

# A flattened build puts inc/ files at the theme root — detect that too.
if echo "$ZIP_MANIFEST" | grep -q " promptless/class-promptless-setup.php$"; then
    echo "  ❌ FLATTENED STRUCTURE DETECTED: class files found at theme root"
    ZIP_STRUCTURE_OK=0
fi

if [ $ZIP_STRUCTURE_OK -eq 0 ]; then
    rm -f "promptless-v${VERSION}.zip"
    echo ""
    echo "❌ ERROR: ZIP structure verification FAILED — archive deleted."
    echo "Do NOT hand-assemble release zips; this script is the only sanctioned packaging path."
    exit 1
fi

echo "  ✅ ZIP structure verified"

echo ""
echo "✅ Release package created successfully!"
echo ""
echo "📊 Package Summary:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📍 Location: release/promptless-v${VERSION}.zip"

# Get file size and counts
if [ -f "promptless-v${VERSION}.zip" ]; then
    SIZE=$(ls -lh "promptless-v${VERSION}.zip" | awk '{print $5}')
    TOTAL_FILES=$(unzip -l "promptless-v${VERSION}.zip" | tail -1 | awk '{print $2}')
    echo "📦 Package size: $SIZE"
    echo "📁 Total files: $TOTAL_FILES"
fi

echo "🔖 Version: $VERSION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🎉 Promptless Theme v${VERSION} is ready for distribution!"
echo ""
echo "📋 Next steps:"
echo "   1. Test by uploading to a clean WordPress site"
echo "   2. Create GitHub release: gh release create v${VERSION}-theme ..."
echo "   3. Submit to WordPress.org if applicable"
