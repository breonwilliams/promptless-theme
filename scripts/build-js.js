/**
 * JavaScript Minification Build Script
 *
 * Uses Terser to minify JavaScript files.
 * Creates .min.js versions in the same directory as source files.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

const fs = require('fs');
const path = require('path');
const { minify } = require('terser');

// JavaScript files to minify (relative to theme root)
const jsFiles = [
    'assets/js/navigation.js',
    'assets/js/customizer-preview.js',
    'assets/js/search-overlay.js'
];

const themeRoot = path.resolve(__dirname, '..');

async function minifyJS(inputPath) {
    const fullInputPath = path.join(themeRoot, inputPath);
    const outputPath = fullInputPath.replace('.js', '.min.js');

    try {
        const code = fs.readFileSync(fullInputPath, 'utf8');

        const result = await minify(code, {
            compress: {
                drop_console: false, // Keep console.log for debugging if needed
                drop_debugger: true,
                passes: 2
            },
            mangle: true,
            output: {
                comments: false
            }
        });

        if (result.error) {
            throw result.error;
        }

        fs.writeFileSync(outputPath, result.code);

        const originalSize = Buffer.byteLength(code, 'utf8');
        const minifiedSize = Buffer.byteLength(result.code, 'utf8');
        const savings = ((originalSize - minifiedSize) / originalSize * 100).toFixed(1);

        console.log(`✓ ${path.basename(inputPath)} → ${path.basename(outputPath)}`);
        console.log(`  ${(originalSize / 1024).toFixed(1)}KB → ${(minifiedSize / 1024).toFixed(1)}KB (${savings}% savings)`);

    } catch (error) {
        console.error(`✗ Error minifying ${inputPath}:`, error.message);
        process.exit(1);
    }
}

async function build() {
    console.log('Building minified JavaScript files...\n');

    // Pass 1: assert every listed source file exists. Previously we logged
    // "⚠ Skipping…" and continued, which silently masked typos in jsFiles
    // (or accidentally-deleted source files) and shipped stale .min.js.
    // Failing here is a CI-friendly hard stop: the build is wrong, fix the
    // file list or restore the missing source.
    const missing = jsFiles.filter(
        (file) => !fs.existsSync(path.join(themeRoot, file))
    );
    if (missing.length > 0) {
        console.error('✗ JavaScript build aborted — listed source files do not exist:');
        for (const file of missing) {
            console.error(`    ${file}`);
        }
        console.error('\nFix scripts/build-js.js jsFiles[] or restore the missing source.');
        process.exit(1);
    }

    // Pass 2: minify every file. Each file's own try/catch in minifyJS
    // already exits with code 1 on a per-file error.
    for (const file of jsFiles) {
        await minifyJS(file);
    }

    console.log('\n✓ JavaScript build complete!');
}

build();
