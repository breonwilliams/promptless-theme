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
    'assets/js/customizer-preview.js'
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

    for (const file of jsFiles) {
        const fullPath = path.join(themeRoot, file);
        if (fs.existsSync(fullPath)) {
            await minifyJS(file);
        } else {
            console.log(`⚠ Skipping ${file} (not found)`);
        }
    }

    console.log('\n✓ JavaScript build complete!');
}

build();
