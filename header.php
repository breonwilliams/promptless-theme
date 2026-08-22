<?php
/**
 * The header template
 *
 * Displays all of the <head> section and the site header.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main-content">
    <?php esc_html_e( 'Skip to content', 'promptless' ); ?>
</a>

<?php promptless_announcement_bar(); ?>

<?php promptless_topbar(); ?>

<?php $header_layout = promptless_get_header_layout(); ?>

<header class="<?php echo esc_attr( promptless_get_header_classes() ); ?>" role="banner">
    <div class="promptless-container">
        <?php if ( 'stacked' === $header_layout ) : ?>
            <!-- Stacked: Row 1 only (nav is separate element below) -->
            <div class="promptless-header__row promptless-header__row--primary">
                <div class="promptless-header__brand" data-home-url="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php promptless_site_logo( 'header' ); ?>
                </div>
                <?php // ACTIONS SLOT CONTRACT: this container is HIDDEN on
                      // desktop unless promptless_has_desktop_header_actions()
                      // returns true (template-functions.php). Adding a new
                      // element here without adding its predicate there makes
                      // it desktop-invisible on sites with no cart and no CTA
                      // -- exactly how the search trigger shipped broken
                      // (2026-08-07). Update BOTH places together, and test
                      // with WooCommerce deactivated and no CTA configured. ?>
                <div class="promptless-header__actions">
                    <?php promptless_header_search(); ?>
                    <?php promptless_header_cart(); ?>
                    <?php promptless_header_cta(); ?>
                    <?php promptless_mobile_menu_toggle(); ?>
                </div>
            </div>
            <!-- Mobile-only nav wrapper (hidden on desktop, used for hamburger menu) -->
            <div class="promptless-header__nav-wrapper promptless-header__nav-wrapper--mobile-only">
                <?php promptless_header_menu_cta( 'top' ); ?>
                <?php promptless_mobile_topbar_section(); ?>
                <?php promptless_header_menu_cta( 'middle' ); ?>
                <?php promptless_primary_nav(); ?>
                <?php promptless_header_menu_cta( 'bottom' ); ?>
            </div>
        <?php else : ?>
            <!-- Default + Floating: Single-row layout. The floating variant
                 intentionally shares this DOM — it is a CSS-only pill
                 treatment of the single row (see header.css, LAYOUT
                 VARIANT: Floating), so nav position, CTAs, cart, and the
                 mobile drawer all behave identically to default. -->
            <div class="promptless-header__inner">
                <!-- Logo / Site Title -->
                <div class="promptless-header__brand" data-home-url="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php promptless_site_logo( 'header' ); ?>
                </div>

                <!-- Primary Navigation -->
                <div class="promptless-header__nav-wrapper">
                    <?php promptless_header_menu_cta( 'top' ); ?>
                    <?php promptless_mobile_topbar_section(); ?>
                    <?php promptless_header_menu_cta( 'middle' ); ?>
                    <?php promptless_primary_nav(); ?>
                    <?php promptless_header_menu_cta( 'bottom' ); ?>
                </div>

                <!-- Header Actions (Cart, CTA, Mobile Menu) -->
                <?php // ACTIONS SLOT CONTRACT: this container is HIDDEN on
                      // desktop unless promptless_has_desktop_header_actions()
                      // returns true (template-functions.php). Adding a new
                      // element here without adding its predicate there makes
                      // it desktop-invisible on sites with no cart and no CTA
                      // -- exactly how the search trigger shipped broken
                      // (2026-08-07). Update BOTH places together, and test
                      // with WooCommerce deactivated and no CTA configured. ?>
                <div class="promptless-header__actions">
                    <?php promptless_header_search(); ?>
                    <?php promptless_header_cart(); ?>
                    <?php promptless_header_cta(); ?>
                    <?php promptless_mobile_menu_toggle(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>

<?php if ( 'stacked' === $header_layout ) : ?>
<!-- Stacked: Nav bar always outside header for proper sticky positioning -->
<nav class="promptless-header-nav <?php echo esc_attr( promptless_get_header_nav_classes() ); ?>" aria-label="<?php esc_attr_e( 'Primary', 'promptless' ); ?>">
    <div class="promptless-container">
        <div class="promptless-header__nav-wrapper">
            <?php promptless_mobile_topbar_section(); ?>
            <?php promptless_primary_nav(); ?>
        </div>
    </div>
</nav>
<?php endif; ?>

<?php
/**
 * Fires after the site header (and the stacked layout's detached nav bar,
 * when present), before each template's <main> element opens.
 *
 * This is the sanctioned insertion point for chrome that sits between the
 * header and the page content — breadcrumbs, sub-headers, contextual
 * notices. Every template that calls get_header() fires it exactly once,
 * including plugin-provided templates (e.g. Promptless CPT Pages singles)
 * and the theme's aisb-fullwidth template. The chrome-less aisb-canvas
 * template skips get_header() and is therefore excluded automatically.
 *
 * @since 1.3.0
 */
do_action( 'promptless_after_header' );

