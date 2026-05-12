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
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail( 'promptless-card', array( 'class' => 'aisb-features__item-image' ) ); ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="aisb-features__item-content">
        <?php promptless_post_meta_with_categories(); ?>

        <h3 class="aisb-features__item-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <div class="aisb-features__item-description">
            <p><?php echo esc_html( promptless_get_excerpt( 20 ) ); ?></p>
        </div>

        <?php
        /*
         * Read more CTA — respects the plugin's global Card CTA Style setting
         * (Global Settings → Borders → Card CTA Style) so the archive cards
         * stay visually consistent with PostGrid sections.
         *
         * `aisb_render_card_cta()` is the plugin's public API (loaded from
         * `includes/public-api.php` in the plugin main file). When the plugin
         * is deactivated the function won't exist, so we fall back to the
         * historical plain text link.
         */
        if ( function_exists( 'aisb_render_card_cta' ) ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- aisb_render_card_cta() handles all escaping internally
            echo aisb_render_card_cta(
                get_permalink(),
                __( 'Read more', 'promptless' ),
                'aisb-features__item-link'
            );
        } else {
            ?>
            <a href="<?php the_permalink(); ?>" class="aisb-features__item-link">
                <?php esc_html_e( 'Read more', 'promptless' ); ?>
            </a>
            <?php
        }
        ?>
    </div>
</article>
