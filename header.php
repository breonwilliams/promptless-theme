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
                    <?php promptless_site_logo(); ?>
                </div>
                <div class="promptless-header__actions">
                    <?php promptless_header_cart(); ?>
                    <?php promptless_header_cta(); ?>
                    <?php promptless_mobile_menu_toggle(); ?>
                </div>
            </div>
            <!-- Mobile-only nav wrapper (hidden on desktop, used for hamburger menu) -->
            <div class="promptless-header__nav-wrapper promptless-header__nav-wrapper--mobile-only">
                <?php promptless_mobile_topbar_section(); ?>
                <?php promptless_primary_nav(); ?>
            </div>
        <?php else : ?>
            <!-- Default: Single-row layout -->
            <div class="promptless-header__inner">
                <!-- Logo / Site Title -->
                <div class="promptless-header__brand" data-home-url="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php promptless_site_logo(); ?>
                </div>

                <!-- Primary Navigation -->
                <div class="promptless-header__nav-wrapper">
                    <?php promptless_mobile_topbar_section(); ?>
                    <?php promptless_primary_nav(); ?>
                </div>

                <!-- Header Actions (Cart, CTA, Mobile Menu) -->
                <div class="promptless-header__actions">
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
