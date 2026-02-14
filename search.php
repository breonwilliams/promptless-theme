<?php
/**
 * The template for displaying search results pages
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

get_header();
?>

<main id="main-content" class="site-main <?php echo esc_attr( promptless_get_content_classes() ); ?>">
    <div class="promptless-container">
        <header class="promptless-archive__header">
            <h1 class="promptless-archive__title">
                <?php
                printf(
                    /* translators: %s: Search query */
                    esc_html__( 'Search results for: %s', 'promptless-theme' ),
                    '<span>' . esc_html( get_search_query() ) . '</span>'
                );
                ?>
            </h1>
        </header>

        <?php if ( have_posts() ) : ?>

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

            <div class="promptless-search__no-results">
                <p><?php esc_html_e( 'Sorry, no results were found for your search. Please try again with different keywords.', 'promptless-theme' ); ?></p>
                <?php get_search_form(); ?>
            </div>

        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
