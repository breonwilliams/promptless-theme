<?php
/**
 * The footer template
 *
 * Contains the closing of the main content and all content after.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

?>

<footer class="<?php echo esc_attr( promptless_get_footer_classes() ); ?>" role="contentinfo">
    <div class="promptless-container">
        <!-- Footer Main Content -->
        <div class="promptless-footer__main">
            <!-- Footer Brand Column -->
            <div class="promptless-footer__brand">
                <?php promptless_site_logo(); ?>
                <?php
                $description = get_bloginfo( 'description', 'display' );
                if ( $description ) :
                    ?>
                    <p class="promptless-footer__tagline"><?php echo esc_html( $description ); ?></p>
                <?php endif; ?>
            </div>

            <!-- Footer Navigation Columns -->
            <div class="promptless-footer__nav-columns">
                <?php if ( has_nav_menu( 'footer-col-1' ) ) : ?>
                    <div class="promptless-footer__nav-column">
                        <?php promptless_footer_nav( 'footer-col-1' ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( has_nav_menu( 'footer-col-2' ) ) : ?>
                    <div class="promptless-footer__nav-column">
                        <?php promptless_footer_nav( 'footer-col-2' ); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer Widgets / Social -->
            <?php if ( is_active_sidebar( 'footer-social' ) ) : ?>
                <div class="promptless-footer__social-wrapper">
                    <?php dynamic_sidebar( 'footer-social' ); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer Bottom Bar -->
        <div class="promptless-footer__bottom">
            <div class="promptless-footer__copyright">
                <?php promptless_copyright(); ?>
            </div>

            <?php if ( has_nav_menu( 'footer' ) ) : ?>
                <div class="promptless-footer__bottom-nav">
                    <?php promptless_footer_nav( 'footer' ); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
