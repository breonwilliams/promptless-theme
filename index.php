<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

get_header();
?>

<main id="main-content" class="site-main <?php echo esc_attr( promptless_get_content_classes() ); ?>">
    <div class="promptless-container">
        <?php
        if ( have_posts() ) :
            ?>
            <header class="promptless-archive__header">
                <?php if ( is_home() && ! is_front_page() ) : ?>
                    <h1 class="promptless-archive__title"><?php single_post_title(); ?></h1>
                <?php else : ?>
                    <h1 class="promptless-archive__title"><?php esc_html_e( 'Latest Posts', 'promptless' ); ?></h1>
                <?php endif; ?>
            </header>
            <div class="promptless-archive__grid">
                <?php
                while ( have_posts() ) :
                    the_post();
                    get_template_part( 'template-parts/archive/card' );
                endwhile;
                ?>
            </div>
            <?php

            promptless_pagination();

        else :

            get_template_part( 'template-parts/content/content', 'none' );

        endif;
        ?>
    </div>
</main>

<?php
get_footer();
