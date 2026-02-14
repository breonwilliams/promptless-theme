<?php
/**
 * Template part for displaying a message when no posts are found
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

?>

<section class="promptless-no-results">
    <header class="promptless-no-results__header">
        <h1 class="promptless-no-results__title"><?php esc_html_e( 'Nothing Found', 'promptless-theme' ); ?></h1>
    </header>

    <div class="promptless-no-results__content">
        <?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>
            <p>
                <?php
                printf(
                    /* translators: %s: URL to create new post */
                    wp_kses(
                        __( 'Ready to publish your first post? <a href="%s">Get started here</a>.', 'promptless-theme' ),
                        array(
                            'a' => array(
                                'href' => array(),
                            ),
                        )
                    ),
                    esc_url( admin_url( 'post-new.php' ) )
                );
                ?>
            </p>
        <?php elseif ( is_search() ) : ?>
            <p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'promptless-theme' ); ?></p>
            <?php get_search_form(); ?>
        <?php else : ?>
            <p><?php esc_html_e( 'It seems we can\'t find what you\'re looking for. Perhaps searching can help.', 'promptless-theme' ); ?></p>
            <?php get_search_form(); ?>
        <?php endif; ?>
    </div>
</section>
