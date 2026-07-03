<?php
/**
 * Template part for displaying post cards in archive pages
 *
 * Uses EXACT same class names and structure as Post Grid section from
 * the Promptless WP plugin for perfect visual consistency.
 *
 * Fires the `promptless_archive_card_section` action at 5 semantically-
 * meaningful positions inside each card so companion plugins (notably
 * Post Runtime Engine) can inject per-position content without replacing
 * the card body. Each fire passes the position name as the first arg
 * so a single listener can branch on it. Positions, in render order:
 * image_overlay (inside image wrapper), headline (above meta), subtitle
 * (under title), meta_strip (after excerpt, before CTA), footer_meta
 * (last). Companion-plugin listeners must echo (not return) — output
 * is emitted at the point the action fires. Listeners should silently
 * no-op when the post type isn't one they manage.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

$promptless_post_id = get_the_ID();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'aisb-features__item aisb-postgrid__item' ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <div class="aisb-features__item-image-wrapper">
            <?php
            /*
             * The image link needs its own accessible name: the featured
             * image's alt comes from the media library and is frequently empty,
             * which would leave this <a> with no discernible name (Lighthouse
             * "Links do not have a discernible name"). Label it with the post
             * title — mirroring the plugin's PostGrid card image link
             * (PostGridRenderer sets aria-label + alt to the title) so the
             * theme archive card and the plugin section behave identically.
             */
            ?>
            <a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
                <?php the_post_thumbnail( 'promptless-card', array( 'class' => 'aisb-features__item-image' ) ); ?>
            </a>
            <?php do_action( 'promptless_archive_card_section', 'image_overlay', $promptless_post_id ); ?>
        </div>
    <?php endif; ?>

    <div class="aisb-features__item-content">
        <?php do_action( 'promptless_archive_card_section', 'headline', $promptless_post_id ); ?>

        <?php promptless_post_meta_with_categories(); ?>

        <h3 class="aisb-features__item-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <?php do_action( 'promptless_archive_card_section', 'subtitle', $promptless_post_id ); ?>

        <div class="aisb-features__item-description">
            <p><?php echo esc_html( promptless_get_excerpt( 20 ) ); ?></p>
        </div>

        <?php do_action( 'promptless_archive_card_section', 'meta_strip', $promptless_post_id ); ?>

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
                'aisb-features__item-link',
                // Pass the post title so the repeated "Read more" links get a
                // disambiguated accessible name — "Read more: {Post title}"
                // (WCAG 2.4.4 / 2.5.3). See includes/public-api.php.
                array( 'aria_context' => get_the_title() )
            );
        } else {
            // Plugin inactive: mirror the same disambiguation on the fallback
            // link. The aria-label pairs the visible label with the post title
            // for screen readers; the visually-hidden <span> makes the link's
            // RENDERED text descriptive too, because search crawlers (and
            // Lighthouse's "descriptive link text" SEO audit) read the text,
            // not the aria-label. Hidden via the theme's `.aisb-visually-hidden`
            // utility (style.css), which is always loaded.
            ?>
            <a href="<?php the_permalink(); ?>" class="aisb-features__item-link"
               aria-label="<?php echo esc_attr( sprintf(
                   /* translators: 1: visible link label. 2: post title. */
                   _x( '%1$s: %2$s', 'accessible card link label', 'promptless' ),
                   __( 'Read more', 'promptless' ),
                   get_the_title()
               ) ); ?>">
                <?php esc_html_e( 'Read more', 'promptless' ); ?><span class="aisb-visually-hidden"><?php echo esc_html( sprintf(
                    /* translators: %s: post title, appended (visually hidden) after the link label so the rendered link text is descriptive. */
                    _x( ': %s', 'hidden card link context suffix', 'promptless' ),
                    get_the_title()
                ) ); ?></span>
            </a>
            <?php
        }
        ?>

        <?php do_action( 'promptless_archive_card_section', 'footer_meta', $promptless_post_id ); ?>
    </div>
</article>
