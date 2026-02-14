<?php
/**
 * The template for displaying all single posts
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
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'promptless-single' ); ?>>
            <header class="promptless-single__header">
                <div class="promptless-container">
                    <div class="promptless-single__header-grid">
                        <!-- Content Column (Left) -->
                        <div class="promptless-single__header-content">
                            <h1 class="promptless-single__title"><?php the_title(); ?></h1>
                            <div class="promptless-single__meta">
                                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" class="promptless-single__date">
                                    <?php echo esc_html( get_the_date() ); ?>
                                </time>
                                <span class="promptless-single__author">
                                    <?php
                                    printf(
                                        /* translators: %s: Author name */
                                        esc_html__( 'by %s', 'promptless-theme' ),
                                        '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a>'
                                    );
                                    ?>
                                </span>
                                <?php
                                $reading_time = ceil( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 );
                                ?>
                                <span class="promptless-single__reading-time">
                                    <?php
                                    printf(
                                        /* translators: %d: Number of minutes */
                                        esc_html( _n( '%d min read', '%d min read', $reading_time, 'promptless-theme' ) ),
                                        esc_html( $reading_time )
                                    );
                                    ?>
                                </span>
                            </div>
                        </div>

                        <!-- Media Column (Right) -->
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="promptless-single__header-media">
                                <?php the_post_thumbnail( 'large', array( 'class' => 'promptless-single__image' ) ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <div class="promptless-single__content entry-content">
                <div class="promptless-container">
                    <?php the_content(); ?>
                </div>
            </div>

            <?php
            wp_link_pages(
                array(
                    'before' => '<div class="page-links promptless-container">' . esc_html__( 'Pages:', 'promptless-theme' ),
                    'after'  => '</div>',
                )
            );
            ?>

            <footer class="promptless-single__footer">
                <div class="promptless-container">
                    <?php
                    $tags = get_the_tags();
                    if ( $tags ) :
                        ?>
                        <div class="promptless-single__tags">
                            <span class="promptless-single__tags-label"><?php esc_html_e( 'Tags:', 'promptless-theme' ); ?></span>
                            <?php
                            foreach ( $tags as $tag ) :
                                printf(
                                    '<a href="%s" class="promptless-single__tag">%s</a>',
                                    esc_url( get_tag_link( $tag->term_id ) ),
                                    esc_html( $tag->name )
                                );
                            endforeach;
                            ?>
                        </div>
                    <?php endif; ?>

                    <!-- Post Navigation -->
                    <nav class="promptless-single__nav" aria-label="<?php esc_attr_e( 'Post navigation', 'promptless-theme' ); ?>">
                        <?php
                        $prev_post = get_previous_post();
                        $next_post = get_next_post();

                        if ( $prev_post ) :
                            ?>
                            <a href="<?php echo esc_url( get_permalink( $prev_post ) ); ?>" class="promptless-single__nav-link promptless-single__nav-link--prev">
                                <span class="promptless-single__nav-label"><?php esc_html_e( 'Previous', 'promptless-theme' ); ?></span>
                                <span class="promptless-single__nav-title"><?php echo esc_html( get_the_title( $prev_post ) ); ?></span>
                            </a>
                        <?php endif; ?>

                        <?php if ( $next_post ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $next_post ) ); ?>" class="promptless-single__nav-link promptless-single__nav-link--next">
                                <span class="promptless-single__nav-label"><?php esc_html_e( 'Next', 'promptless-theme' ); ?></span>
                                <span class="promptless-single__nav-title"><?php echo esc_html( get_the_title( $next_post ) ); ?></span>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </footer>
        </article>
        <?php

        // If comments are open or we have at least one comment, load up the comment template.
        if ( comments_open() || get_comments_number() ) :
            ?>
            <section class="promptless-single__comments">
                <div class="promptless-container">
                    <?php comments_template(); ?>
                </div>
            </section>
            <?php
        endif;

    endwhile;
    ?>
</main>

<?php
get_footer();
