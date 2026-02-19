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
     */
    wp.customize( 'promptless_footer_brand_text', function( value ) {
        value.bind( function( newval ) {
            var $brandText = $( '.promptless-footer__brand-text' );
            var $tagline = $( '.promptless-footer__tagline' );

            if ( newval ) {
                // Hide tagline, show/create brand text
                $tagline.hide();
                if ( $brandText.length ) {
                    $brandText.html( newval ).show();
                } else {
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
