<?php
/**
 * Template part for displaying post cards in archive pages
 *
 * Uses EXACT same class names and structure as Post Grid section from
 * the Promptless WP plugin for perfect visual consistency.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'aisb-features__item aisb-postgrid__item' ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <div class="aisb-features__item-image-wrapper">
            <a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                <?php the_post_thumbnail( 'promptless-card', array( 'class' => 'aisb-features__item-image' ) ); ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="aisb-features__item-content">
        <?php promptless_post_meta_with_categories(); ?>

        <h3 class="aisb-features__item-title">
            <?php the_title(); ?>
        </h3>

        <div class="aisb-features__item-description">
            <p><?php echo esc_html( promptless_get_excerpt( 20 ) ); ?></p>
        </div>

        <a href="<?php the_permalink(); ?>" class="aisb-features__item-link">
            <?php esc_html_e( 'Read more', 'promptless' ); ?>
        </a>
    </div>
</article>
