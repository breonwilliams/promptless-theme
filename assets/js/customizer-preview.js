/**
 * Customizer Preview Script
 *
 * Handles real-time preview updates in the WordPress customizer.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

( function( $ ) {
    'use strict';

    /**
     * Custom Logo - Toggle between logo and site title
     *
     * When user removes logo, show site title.
     * When user adds logo, hide site title.
     */
    wp.customize( 'custom_logo', function( value ) {
        value.bind( function( newval ) {
            var $brand = $( '.promptless-header__brand' );
            var $logo = $brand.find( '.custom-logo-link' );
            var $siteTitle = $brand.find( '.promptless-header__site-title' );

            if ( newval ) {
                // Logo is set - show logo, hide title
                $logo.show();
                $siteTitle.hide();
            } else {
                // Logo removed - hide logo, show title
                $logo.hide();

                // If site title doesn't exist, create it
                if ( ! $siteTitle.length ) {
                    var siteName = wp.customize( 'blogname' ).get();
                    var homeUrl = $brand.data( 'home-url' ) || '/';
                    var $newTitle = $( '<a>', {
                        'class': 'promptless-header__site-title',
                        'href': homeUrl,
                        'rel': 'home',
                        'text': siteName
                    });
                    $brand.append( $newTitle );
                } else {
                    $siteTitle.show();
                }
            }
        });
    });

    /**
     * Blog Name - Update site title text in real-time
     */
    wp.customize( 'blogname', function( value ) {
        value.bind( function( newval ) {
            $( '.promptless-header__site-title' ).text( newval );
        });
    });

    /**
     * Footer Brand Text - Update in real-time
     *
     * SECURITY NOTE — INTENTIONAL .html() USAGE
     * ------------------------------------------
     * This setting is HTML-capable BY DESIGN. The Customizer control
     * description (class-promptless-customizer.php line 508) explicitly
     * states: "Supports basic HTML formatting (bold, italic, links)."
     *
     * The full security boundary works as follows:
     *
     *   1. Access control: only users with `edit_theme_options` capability
     *      (administrators by default) can write to this setting via the
     *      WordPress Customizer. There is no public form, no REST endpoint,
     *      and no untrusted-input path that flows into `newval`.
     *
     *   2. Save-side sanitization: when the admin clicks "Save",
     *      `wp_kses_post` runs as the registered `sanitize_callback`
     *      (class-promptless-customizer.php line 499). This strips
     *      `<script>`, `<iframe>`, `on*` event attributes, and other
     *      dangerous tags BEFORE the value reaches the database.
     *
     *   3. Frontend rendering: footer.php line 26 renders the saved value
     *      with `<?php echo wp_kses_post( $brand_text ); ?>` — same
     *      sanitizer, same allowlist. The customizer preview MUST match
     *      this frontend behavior, otherwise the preview lies to the
     *      admin about how their changes will actually render.
     *
     * DO NOT change `.html(newval)` to `.text(newval)` for "defense in
     * depth". Doing so breaks the documented feature: the preview would
     * show literal HTML markup (e.g. "<strong>FlowSpace</strong>") instead
     * of the rendered output (**FlowSpace**), making the live preview
     * inconsistent with the live site. The theoretical "self-XSS" pattern
     * a static analyzer flags here is not a real privilege-escalation
     * vector — an admin already has full site control and gains nothing
     * by injecting JS into their own preview iframe (which gets stripped
     * by wp_kses_post on save anyway).
     *
     * This pattern matches WordPress core's own Customize_Preview scripts
     * for HTML-capable settings, and is the standard approach for themes
     * with rich-content customizer fields.
     */
    wp.customize( 'promptless_footer_brand_text', function( value ) {
        value.bind( function( newval ) {
            var $brandText = $( '.promptless-footer__brand-text' );
            var $tagline = $( '.promptless-footer__tagline' );

            if ( newval ) {
                // Hide tagline, show/create brand text
                $tagline.hide();
                if ( $brandText.length ) {
                    // .html() is intentional — see SECURITY NOTE above.
                    $brandText.html( newval ).show();
                } else {
                    // String concatenation with `newval` is intentional —
                    // see SECURITY NOTE above. Preview must render HTML
                    // (bold/italic/links) to match the frontend.
                    $( '.promptless-footer__brand' ).append(
                        '<div class="promptless-footer__brand-text">' + newval + '</div>'
                    );
                }
            } else {
                // Hide brand text, show tagline
                $brandText.hide();
                $tagline.show();
            }
        });
    });

} )( jQuery );
