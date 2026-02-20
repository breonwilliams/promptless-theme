<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

get_header();
?>

<main id="main-content" class="site-main <?php echo esc_attr( promptless_get_content_classes() ); ?>">
    <div class="promptless-container">
        <div class="promptless-404">
            <header class="promptless-404__header">
                <h1 class="promptless-404__title"><?php esc_html_e( '404', 'promptless' ); ?></h1>
                <h2 class="promptless-404__subtitle"><?php esc_html_e( 'Page not found', 'promptless' ); ?></h2>
            </header>

            <div class="promptless-404__content">
                <p><?php esc_html_e( 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'promptless' ); ?></p>
            </div>

            <div class="promptless-404__actions">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="aisb-btn aisb-btn-primary">
                    <?php esc_html_e( 'Go to Homepage', 'promptless' ); ?>
                </a>
            </div>

            <div class="promptless-404__search">
                <p><?php esc_html_e( 'Or try searching:', 'promptless' ); ?></p>
                <?php get_search_form(); ?>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
