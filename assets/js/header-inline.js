/**
 * Header measurements that must land before first paint.
 *
 * Printed INLINE right after </header> by promptless_header_inline_script().
 * A parser-inserted script waits for the stylesheets, so it measures the real
 * layout, and it runs before the content below the header is laid out and
 * painted. navigation.js (footer) keeps both measurements current afterwards:
 * on resize, once web fonts load, and through a ResizeObserver.
 *
 * Both used to run only from navigation.js, after the page had painted, and
 * the content below the header moved when they did (Sparrowmere dry run,
 * 2026-10, measured at 390px on a throttled mobile load, floating overlay
 * header): the button swap shortened the header and the page jumped 26px
 * (CLS 0.09-0.11); the height sync replaced the 5.5rem fallback and the page
 * jumped 10px even with the stock header (CLS 0.010).
 *
 * @since 1.4.3
 */
(function() {
    'use strict';

    var header = document.querySelector('.promptless-header');
    if (!header) {
        return;
    }

    /*
     * 1. Header button overflow (phones).
     *
     * header.css lets the site title and a bar-placed header button wrap
     * between words, never inside one, so neither is clipped. When even that
     * cannot fit the row (a long title beside a long label at 320-430px), the
     * button moves into the phone menu: the drawer already holds a hidden copy
     * of it (promptless_header_menu_cta()), and the class added here swaps the
     * two. "Ask a question" beside "Sparrowmere Gardens" at 390px was once
     * squeezed to a 4px content box and clipped.
     *
     * The check runs with the class removed, so it always measures the bar as
     * it would render, and only while the menu toggle is shown (the drawer
     * breakpoint); every rule it enables is inside that breakpoint too.
     * Without JavaScript the button stays in the bar, wrapped. Runs first:
     * it changes the header's height.
     */
    var actions = header.querySelector('.promptless-header__actions');
    var toggle = header.querySelector('.promptless-header__menu-toggle');

    if (header.querySelector('.promptless-header__cta--bar-overflow') && actions && toggle && actions.parentElement) {
        var row = actions.parentElement;
        var OVERFLOW = 'promptless-header--cta-overflow';

        window.promptlessHeaderCtaOverflow = function() {
            header.classList.remove(OVERFLOW);

            if (window.getComputedStyle(toggle).display === 'none') {
                return;
            }

            var style = window.getComputedStyle(row);
            var box = row.getBoundingClientRect();
            var edge = actions.getBoundingClientRect();
            var overflows = style.direction === 'rtl'
                ? edge.left < box.left + parseFloat(style.paddingLeft) - 0.5
                : edge.right > box.right - parseFloat(style.paddingRight) + 0.5;

            if (overflows) {
                header.classList.add(OVERFLOW);
            }
        };

        window.promptlessHeaderCtaOverflow();
    }

    /*
     * 2. Header height, for the floating overlay and sticky headers (see
     * navigation.js "Header height sync" for what consumes it).
     */
    if (header.matches('.promptless-header--overlay, .promptless-header--sticky')) {
        document.documentElement.style.setProperty('--promptless-header-height', header.offsetHeight + 'px');
    }
})();
