<?php
/**
 * The template for displaying all pages
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

get_header();
?>

<main id="main-content" class="site-main <?php echo esc_attr( promptless_get_content_classes() ); ?>">
    <?php
    while ( have_posts() ) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'promptless-page' ); ?>>
            <?php if ( ! is_front_page() ) : ?>
                <header class="promptless-page__header">
                    <div class="promptless-container">
                        <div class="promptless-page__header-grid">
                            <!-- Content Column (Left) -->
                            <div class="promptless-page__header-content">
                                <h1 class="promptless-page__title"><?php the_title(); ?></h1>
                            </div>

                            <!-- Media Column (Right) - Only if featured image exists -->
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="promptless-page__header-media">
                                    <?php the_post_thumbnail( 'large', array( 'class' => 'promptless-page__image' ) ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </header>
            <?php endif; ?>

            <div class="promptless-page__content entry-content">
                <div class="promptless-container">
                    <?php the_content(); ?>
                </div>
            </div>

            <?php
            wp_link_pages(
                array(
                    'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'promptless-theme' ),
                    'after'  => '</div>',
                )
            );
            ?>
        </article>
        <?php

        // If comments are open or we have at least one comment, load up the comment template.
        if ( comments_open() || get_comments_number() ) :
            comments_template();
        endif;

    endwhile;
    ?>
</main>

<?php
get_footer();
