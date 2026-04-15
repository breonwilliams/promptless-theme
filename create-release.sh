#!/bin/bash

# Promptless Theme Release Script
# Creates a clean ZIP file for WordPress.org submission
# Usage: ./create-release.sh

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Promptless Theme Release Builder${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Get the directory where the script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Extract version from style.css
VERSION=$(grep "^Version:" style.css | head -1 | awk '{print $2}')

if [ -z "$VERSION" ]; then
    echo -e "${RED}Error: Could not extract version from style.css${NC}"
    exit 1
fi

echo -e "${GREEN}Detected version: ${VERSION}${NC}"
echo ""

# Version verification across all files
echo -e "${YELLOW}Verifying version numbers...${NC}"

STYLE_VERSION=$(grep "^Version:" style.css | head -1 | awk '{print $2}')
README_VERSION=$(grep "^Stable tag:" readme.txt | head -1 | awk '{print $3}')
FUNCTIONS_VERSION=$(grep "PROMPTLESS_THEME_VERSION" functions.php | head -1 | grep -oE "'[0-9]+\.[0-9]+\.[0-9]+'" | tr -d "'")

echo "  style.css:     $STYLE_VERSION"
echo "  readme.txt:    $README_VERSION"
echo "  functions.php: $FUNCTIONS_VERSION"

if [ "$STYLE_VERSION" != "$README_VERSION" ] || [ "$STYLE_VERSION" != "$FUNCTIONS_VERSION" ]; then
    echo ""
    echo -e "${RED}Error: Version mismatch detected!${NC}"
    echo -e "${RED}Please ensure all three files have the same version number.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ All version numbers match${NC}"
echo ""

# Check if minified assets exist
echo -e "${YELLOW}Checking for minified assets...${NC}"
MISSING_ASSETS=false

if [ ! -f "assets/css/header.min.css" ]; then
    echo -e "  ${RED}✗ Missing: assets/css/header.min.css${NC}"
    MISSING_ASSETS=true
fi
if [ ! -f "assets/css/footer.min.css" ]; then
    echo -e "  ${RED}✗ Missing: assets/css/footer.min.css${NC}"
    MISSING_ASSETS=true
fi
if [ ! -f "assets/css/archive.min.css" ]; then
    echo -e "  ${RED}✗ Missing: assets/css/archive.min.css${NC}"
    MISSING_ASSETS=true
fi
if [ ! -f "assets/js/navigation.min.js" ]; then
    echo -e "  ${RED}✗ Missing: assets/js/navigation.min.js${NC}"
    MISSING_ASSETS=true
fi

if [ "$MISSING_ASSETS" = true ]; then
    echo ""
    echo -e "${YELLOW}Running npm build to generate minified assets...${NC}"
    if command -v npm &> /dev/null; then
        npm run build
        echo -e "${GREEN}✓ Build completed${NC}"
    else
        echo -e "${RED}Error: npm not found. Please install Node.js and run 'npm run build' first.${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}✓ All minified assets present${NC}"
fi
echo ""

# Create release directory
RELEASE_DIR="release"
ZIP_NAME="promptless-v${VERSION}.zip"
ZIP_PATH="${RELEASE_DIR}/${ZIP_NAME}"

mkdir -p "$RELEASE_DIR"

# Remove old ZIP if exists
if [ -f "$ZIP_PATH" ]; then
    rm "$ZIP_PATH"
    echo -e "${YELLOW}Removed existing: ${ZIP_NAME}${NC}"
fi

# Create ZIP file
echo -e "${YELLOW}Creating release ZIP...${NC}"

# Create a temporary directory for clean copy
TEMP_DIR=$(mktemp -d)
THEME_TEMP="${TEMP_DIR}/promptless"

# Copy theme files (excluding unwanted files/folders)
mkdir -p "$THEME_TEMP"

# Use rsync for clean copy
rsync -a --exclude='node_modules' \
         --exclude='package.json' \
         --exclude='package-lock.json' \
         --exclude='scripts' \
         --exclude='release' \
         --exclude='.git' \
         --exclude='.gitignore' \
         --exclude='.DS_Store' \
         --exclude='*.log' \
         --exclude='create-release.sh' \
         --exclude='THEME_RELEASE_GUIDE.md' \
         . "$THEME_TEMP/"

# Create ZIP
cd "$TEMP_DIR"
zip -r "${SCRIPT_DIR}/${ZIP_PATH}" promptless -x "*.DS_Store"

# Clean up temp directory
rm -rf "$TEMP_DIR"

cd "$SCRIPT_DIR"

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Release ZIP Created Successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "  ${BLUE}File:${NC} ${ZIP_PATH}"
echo -e "  ${BLUE}Size:${NC} $(du -h "$ZIP_PATH" | cut -f1)"
echo ""

# List ZIP contents
echo -e "${YELLOW}ZIP Contents:${NC}"
unzip -l "$ZIP_PATH" | tail -n +4 | head -n -2 | awk '{print "  " $4}'

echo ""
echo -e "${GREEN}Next steps:${NC}"
echo "  1. Test the ZIP on a clean WordPress installation"
echo "  2. Commit changes: git add -A && git commit -m 'Release: Promptless Theme v${VERSION}'"
echo "  3. Tag the release: git tag v${VERSION}-theme"
echo "  4. Push to GitHub: git push && git push --tags"
echo "  5. Create GitHub release with: gh release create v${VERSION}-theme --title 'Promptless Theme v${VERSION}' ${ZIP_PATH}"
echo ""
