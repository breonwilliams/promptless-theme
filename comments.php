<?php
/**
 * The template for displaying comments
 *
 * This template provides proper wrapper structure for comments styling.
 * It creates a .comments-area wrapper and uses .comment-list class
 * which matches the CSS selectors in archive.css.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password,
 * return early without loading the comments.
 */
if ( post_password_required() ) {
    return;
}
?>

<div id="comments" class="comments-area">
    <?php if ( have_comments() ) : ?>
        <h2 class="comments-title">
            <?php
            $promptless_comment_count = get_comments_number();
            printf(
                /* translators: 1: Number of comments, 2: Post title */
                esc_html( _n( '%1$s response to &ldquo;%2$s&rdquo;', '%1$s responses to &ldquo;%2$s&rdquo;', $promptless_comment_count, 'promptless-theme' ) ),
                number_format_i18n( $promptless_comment_count ),
                get_the_title()
            );
            ?>
        </h2>

        <?php the_comments_navigation(); ?>

        <ol class="comment-list">
            <?php
            wp_list_comments(
                array(
                    'style'       => 'ol',
                    'short_ping'  => true,
                    'avatar_size' => 48,
                )
            );
            ?>
        </ol>

        <?php
        the_comments_navigation();

        // If comments are closed and there are comments, let's leave a little note.
        if ( ! comments_open() ) :
            ?>
            <p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'promptless-theme' ); ?></p>
            <?php
        endif;

    endif; // Check for have_comments().

    comment_form();
    ?>
</div><!-- #comments -->
