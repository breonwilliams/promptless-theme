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
     * WCAG 1.3.1 — the archive heading ladder.
     *
     * Every archive rendered its results as h3 directly beneath the page's
     * single h1, with nothing emitting an h2 in between. Someone navigating by
     * heading reads that gap as a missing section: the structure claims a level
     * that does not exist. Fixed by making the card title an h2, which is the
     * correct level anyway — on an archive each result IS a top-level item
     * under the page title.
     *
     * WHY IT NEEDS A GUARD OF ITS OWN. axe does not fail this at AA;
     * `heading-order` is a best-practice rule, so the browser gates stay green
     * while the ladder is broken. The only thing that caught it was the heading
     * audit in Promptless WP's live runner, and that needs a running site. The
     * tag is one character away from regressing and nothing here would notice.
     *
     * It asserts BOTH ENDS of the ladder, not just the card. Guarding the h2
     * alone would let someone demote the archive h1 to an h2 and leave the same
     * gap pointing the other way — a check that only holds one end of a
     * relationship does not hold the relationship.
     */
    private function test_archive_heading_ladder() {
        echo "\nArchive heading ladder (WCAG 1.3.1)\n";

        $card = $this->read( 'template-parts/archive/card.php' );

        $this->check(
            false !== $card,
            'template-parts/archive/card.php exists',
            'The archive card template is missing, so its heading level cannot be verified.'
        );

        if ( false !== $card ) {
            // The heading that carries the permalink is the card TITLE. Matched
            // by that pairing rather than by "the first heading in the file",
            // so adding an unrelated heading above it does not silently move
            // what this test is looking at.
            $matched = preg_match(
                '/<h([1-6])[^>]*>\s*(?:<\?php.*?\?>\s*)*<a\s+href="<\?php\s+the_permalink/s',
                $card,
                $m
            );

            $this->check(
                (bool) $matched,
                'card.php has a linked title heading',
                'No heading wrapping a the_permalink() link was found. Either the card '
                . 'title stopped being a heading — which removes every archive result '
                . 'from heading navigation — or this test is matching the wrong shape.'
            );

            if ( $matched ) {
                $this->check(
                    '2' === $m[1],
                    'card.php renders the result title as h2 (found h' . $m[1] . ')',
                    'Archive results sit directly beneath the archive h1 and nothing emits '
                    . 'an h2 between them, so any level below 2 skips one. A screen-reader '
                    . 'user navigating by heading reads the gap as a missing section.'
                );
            }
        }

        // The other end: the archive templates must still emit the h1 that the
        // card's h2 is a level below.
        foreach ( array( 'archive.php', 'index.php' ) as $file ) {
            $template = $this->read( $file );

            $this->check(
                false !== $template,
                "{$file} exists",
                'The archive template is missing, so the top of the ladder cannot be verified.'
            );
            if ( false === $template ) {
                continue;
            }

            $h1_count = preg_match_all( '/<h1[\s>]/', $template );

            $this->check(
                $h1_count > 0,
                "{$file} emits an h1",
                'The archive page has no h1, so it states no subject and the card h2 '
                . 'headings hang below nothing.'
            );

            // index.php legitimately carries two h1 branches — a blog-page title
            // and a "Latest Posts" fallback — in an if/else, so only ONE ever
            // renders. Counting source occurrences cannot tell branches apart,
            // which is why this asserts a small ceiling rather than exactly one.
            $this->check(
                $h1_count <= 2,
                "{$file} does not stack h1 elements",
                'More than two h1 elements appear in the source. Two pages worth of '
                . 'subject on one page is two subjects, and a reader cannot tell which '
                . 'is the page.'
            );
        }
    }

    /**
     * Runs everything and exits non-zero on any failure.
     */
    public function run() {
        echo "\nPromptless Theme — accessibility contract\n";
        echo str_repeat( '=', 56 ) . "\n";

        $this->test_header_reflow();
        $this->test_skip_link_targets_are_focusable();
        $this->test_archive_heading_ladder();

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
