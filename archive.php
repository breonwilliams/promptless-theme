<?php
/**
 * The template for displaying archive pages
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

get_header();
?>

<main id="main-content" class="site-main <?php echo esc_attr( promptless_get_content_classes() ); ?>">
    <div class="promptless-container">
        <?php if ( have_posts() ) : ?>
            <header class="promptless-archive__header">
                <h1 class="promptless-archive__title"><?php echo esc_html( promptless_get_archive_title() ); ?></h1>
                <?php
                $description = get_the_archive_description();
                if ( $description ) :
                    ?>
                    <div class="promptless-archive__description">
                        <?php echo wp_kses_post( $description ); ?>
                    </div>
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

            <?php promptless_pagination(); ?>

        <?php else : ?>

            <?php get_template_part( 'template-parts/content/content', 'none' ); ?>

        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
