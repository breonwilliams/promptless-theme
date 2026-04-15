/**
 * CSS Minification Build Script
 *
 * Uses PostCSS with cssnano to minify CSS files.
 * Creates .min.css versions in the same directory as source files.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

const fs = require('fs');
const path = require('path');
const postcss = require('postcss');
const cssnano = require('cssnano');

// CSS files to minify (relative to theme root)
const cssFiles = [
    'assets/css/header.css',
    'assets/css/footer.css',
    'assets/css/archive.css',
    'assets/css/woocommerce.css'
];

const themeRoot = path.resolve(__dirname, '..');

async function minifyCSS(inputPath) {
    const fullInputPath = path.join(themeRoot, inputPath);
    const outputPath = fullInputPath.replace('.css', '.min.css');

    try {
        const css = fs.readFileSync(fullInputPath, 'utf8');

        const result = await postcss([
            cssnano({
                preset: ['default', {
                    discardComments: {
                        removeAll: true
                    },
                    normalizeWhitespace: true,
                    minifySelectors: true,
                    minifyParams: true
                }]
            })
        ]).process(css, {
            from: fullInputPath,
            to: outputPath
        });

        fs.writeFileSync(outputPath, result.css);

        const originalSize = Buffer.byteLength(css, 'utf8');
        const minifiedSize = Buffer.byteLength(result.css, 'utf8');
        const savings = ((originalSize - minifiedSize) / originalSize * 100).toFixed(1);

        console.log(`✓ ${path.basename(inputPath)} → ${path.basename(outputPath)}`);
        console.log(`  ${(originalSize / 1024).toFixed(1)}KB → ${(minifiedSize / 1024).toFixed(1)}KB (${savings}% savings)`);

    } catch (error) {
        console.error(`✗ Error minifying ${inputPath}:`, error.message);
        process.exit(1);
    }
}

async function build() {
    console.log('Building minified CSS files...\n');

    for (const file of cssFiles) {
        const fullPath = path.join(themeRoot, file);
        if (fs.existsSync(fullPath)) {
            await minifyCSS(file);
        } else {
            console.log(`⚠ Skipping ${file} (not found)`);
        }
    }

    console.log('\n✓ CSS build complete!');
}

build();
