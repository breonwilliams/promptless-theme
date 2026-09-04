<?php
/**
 * Promptless Theme — Accessibility Contract Tests
 *
 * Standalone PHP test, same pattern as test-announcement-bar.php: zero WP
 * runtime, zero database. It reads the stylesheets and templates that ship and
 * asserts the accessibility properties they are required to have.
 *
 * Run: php tests/test-accessibility.php
 *
 * WHY THIS IS SOURCE-LEVEL AND WHAT THAT COSTS
 *
 * The real check for reflow is a browser at 320 CSS pixels, and that gate
 * exists — tests/a11y/run-reflow.js in the Promptless WP repo, which drives
 * this theme's pages. But it needs a running WordPress site, so it cannot run
 * here, and this theme had no automated accessibility coverage at all.
 *
 * Asserting the source is weaker: it proves the rule is still written, not that
 * the browser still honours it. It is the check that fits this repo, and it
 * holds the specific lines that were crossed.
 *
 * IT CHECKS THE BUILT FILE TOO
 *
 * The theme enqueues header.min.css, never header.css. A fix present in the
 * source and absent from the build ships as no fix at all — the same class of
 * mistake as a stale build directory, which took a production site down earlier
 * in this codebase's history. So both files are asserted to contain the rule.
 *
 * By CONTENT, not by timestamp. A first draft compared mtimes, which is
 * worthless in CI: a fresh clone writes every file at the same moment, so the
 * comparison would have passed by coincidence rather than by correctness. A
 * check that cannot fail is worse than no check.
 *
 * @package Promptless_Theme
 * @since 1.2.1
 */

$theme_dir = dirname( __DIR__ );

/**
 * Minimal assertion runner, mirroring the other standalone tests here.
 */
class AccessibilityTestRunner {

    /** @var int */
    private $passed = 0;

    /** @var string[] */
    private $failures = array();

    /** @var string */
    private $theme_dir;

    /**
     * @param string $theme_dir Theme root.
     */
    public function __construct( $theme_dir ) {
        $this->theme_dir = $theme_dir;
    }

    /**
     * Asserts a condition.
     *
     * @param bool   $condition Result.
     * @param string $name      What was being checked.
     * @param string $why       What breaks for a user when it fails.
     */
    private function check( $condition, $name, $why ) {
        if ( $condition ) {
            $this->passed++;
            echo "  \033[32m✓\033[0m {$name}\n";
            return;
        }
        $this->failures[] = "{$name}\n      {$why}";
        echo "  \033[31m✗\033[0m {$name}\n      {$why}\n";
    }

    /**
     * Reads a theme file.
     *
     * @param string $relative Path relative to the theme root.
     * @return string|false
     */
    private function read( $relative ) {
        $path = $this->theme_dir . '/' . ltrim( $relative, '/' );
        return file_exists( $path ) ? file_get_contents( $path ) : false;
    }

    /**
     * WCAG 1.4.10 Reflow — the header at 320 CSS pixels.
     *
     * 320px is a 1280px desktop at 400% zoom, which is what a low-vision user
     * actually does; it is not a phone width. At that size the header row did
     * not fit and the menu toggle was clipped off the right edge — the
     * navigation, unreachable, on every page.
     */
    private function test_header_reflow() {
        echo "\nHeader reflow at 320px (WCAG 1.4.10)\n";

        foreach ( array( 'assets/css/header.css', 'assets/css/header.min.css' ) as $file ) {
            $css = $this->read( $file );

            $this->check(
                false !== $css,
                "{$file} exists",
                'The stylesheet is missing, so nothing can be verified about it.'
            );
            if ( false === $css ) {
                continue;
            }

            // The minifier collapses whitespace, so match on the shape rather
            // than the source formatting.
            $normalised = preg_replace( '/\s+/', '', $css );

            $this->check(
                false !== strpos( $normalised, '@media(max-width:400px)' )
                    || false !== strpos( $normalised, '@media(max-width:480px)' ),
                "{$file} has a narrow-width header rule",
                'The header has no narrow-width handling. At 320px its row does not fit, '
                . 'and the menu toggle is pushed off the right edge where no scroll reaches it.'
            );

            $this->check(
                false !== strpos( $normalised, '--promptless-header-pill-gutter' ),
                "{$file} keeps the pill gutter overridable",
                'The pill\'s horizontal padding is no longer a custom property. An '
                . 'equal-specificity declaration loses to the later-loaded breakpoint '
                . 'stylesheet, which is why the override is done this way.'
            );
        }
    }

    /**
     * Skip-link targets must be able to receive focus.
     *
     * A <main> is not focusable by default: without tabindex="-1" the browser
     * moves the viewport to the fragment and leaves focus where it was, so the
     * next Tab returns the user to the top of the navigation. The link scrolls
     * and skips nothing, which axe cannot see.
     */
    private function test_skip_link_targets_are_focusable() {
        echo "\nSkip-link targets accept focus (WCAG 2.4.1)\n";

        $templates = glob( $this->theme_dir . '/*.php' );
        $checked   = 0;

        foreach ( (array) $templates as $path ) {
            $source = file_get_contents( $path );
            if ( false === strpos( $source, 'id="main-content"' ) ) {
                continue;
            }
            $checked++;
            $name = basename( $path );

            $this->check(
                (bool) preg_match( '/<main[^>]*\btabindex=["\']-1["\'][^>]*>/', $source ),
                "{$name} makes its main landmark focusable",
                'The <main> that "Skip to content" targets has no tabindex="-1", so '
                . 'activating the skip link scrolls the page without moving focus.'
            );
        }

        $this->check(
            $checked > 0,
            'found templates rendering #main-content',
            'No template renders id="main-content" — either the skip-link target moved '
            . 'or this test is looking in the wrong place.'
        );
    }

    /**
     * Runs everything and exits non-zero on any failure.
     */
    public function run() {
        echo "\nPromptless Theme — accessibility contract\n";
        echo str_repeat( '=', 56 ) . "\n";

        $this->test_header_reflow();
        $this->test_skip_link_targets_are_focusable();

        echo "\n" . str_repeat( '=', 56 ) . "\n";
        echo "Passed: {$this->passed}\n";
        echo 'Failed: ' . count( $this->failures ) . "\n";

        if ( $this->failures ) {
            echo "\n\033[31mFAILED\033[0m\n";
            exit( 1 );
        }
        echo "\n\033[32mPASSED\033[0m\n";
        exit( 0 );
    }
}

( new AccessibilityTestRunner( $theme_dir ) )->run();
