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
    'assets/css/header-breakpoint.css',
    'assets/css/footer.css',
    'assets/css/archive.css',
    'assets/css/woocommerce.css',
    'assets/css/breadcrumbs.css'
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

    // Pass 1: assert every listed source file exists. Previously we logged
    // "⚠ Skipping…" and continued, which silently masked typos in cssFiles
    // (or accidentally-deleted source files) and shipped stale .min.css.
    // Failing here is a CI-friendly hard stop: the build is wrong, fix the
    // file list or restore the missing source.
    const missing = cssFiles.filter(
        (file) => !fs.existsSync(path.join(themeRoot, file))
    );
    if (missing.length > 0) {
        console.error('✗ CSS build aborted — listed source files do not exist:');
        for (const file of missing) {
            console.error(`    ${file}`);
        }
        console.error('\nFix scripts/build-css.js cssFiles[] or restore the missing source.');
        process.exit(1);
    }

    // Pass 2: minify every file. Each file's own try/catch in minifyCSS
    // already exits with code 1 on a per-file error.
    for (const file of cssFiles) {
        await minifyCSS(file);
    }

    console.log('\n✓ CSS build complete!');
}

build();
