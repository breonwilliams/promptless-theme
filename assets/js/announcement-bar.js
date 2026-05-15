/**
 * Promptless Theme — Announcement Bar Dismiss (Wave 6)
 *
 * Vanilla JS, no dependencies. Tiny on purpose — listens for the close
 * button click, sets a cookie keyed to the message-content hash (read from
 * the bar's data-cookie-key attribute), then collapses the bar smoothly
 * out of the document flow.
 *
 * The cookie name is computed server-side and passed via data-cookie-key
 * so we never have to re-derive a hash here — defensive against any
 * mismatch between PHP's sha1 and a JS implementation.
 *
 * Cookie expiry: 365 days. The hash invalidation pattern (changing the
 * message produces a new cookie name) does the actual "reset dismissals"
 * work, so we can safely set a long expiry without trapping visitors with
 * stale dismissals after a content update.
 *
 * @package Promptless_Theme
 * @since   1.2.0
 */

( function () {
    'use strict';

    var bar = document.querySelector( '.promptless-announcement-bar[data-cookie-key]' );
    if ( ! bar ) {
        return;
    }

    var dismissBtn = bar.querySelector( '.promptless-announcement-bar__dismiss' );
    if ( ! dismissBtn ) {
        return;
    }

    var cookieKey = bar.getAttribute( 'data-cookie-key' );
    if ( ! cookieKey ) {
        return;
    }

    /**
     * Set a cookie with sane defaults: site-wide path, 365-day expiry,
     * SameSite=Lax (default modern-browser behavior, but explicit for
     * older UAs that read defaults less consistently).
     *
     * @param {string} name
     * @param {string} value
     * @param {number} days
     */
    function setCookie( name, value, days ) {
        var expires = '';
        if ( days ) {
            var date = new Date();
            date.setTime( date.getTime() + ( days * 24 * 60 * 60 * 1000 ) );
            expires = '; expires=' + date.toUTCString();
        }
        // Encode value defensively even though we only ever set "1".
        document.cookie = name + '=' + encodeURIComponent( value ) +
            expires + '; path=/; SameSite=Lax';
    }

    /**
     * Dismiss handler. Sets the cookie first (so a fast page-reload still
     * sees the dismissed state) before triggering the visual collapse.
     */
    function dismissBar() {
        setCookie( cookieKey, '1', 365 );
        bar.classList.add( 'is-dismissing' );

        // Remove the element after the CSS transition completes so it
        // doesn't continue to take up space (even though max-height: 0
        // collapses it visually). 300ms matches the slowest of the
        // CSS transitions on .promptless-announcement-bar.
        var REMOVAL_DELAY_MS = 300;
        window.setTimeout( function () {
            if ( bar && bar.parentNode ) {
                bar.parentNode.removeChild( bar );
            }
        }, REMOVAL_DELAY_MS );
    }

    dismissBtn.addEventListener( 'click', dismissBar );
} )();
