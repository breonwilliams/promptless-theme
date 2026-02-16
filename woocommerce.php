<?php
/**
 * WooCommerce Template
 *
 * This template is used for WooCommerce pages (shop, product archives, single products).
 * Cart, Checkout, and My Account pages use page.php (shortcode-based).
 *
 * The container width respects Global Settings (--aisb-section-max-width) via
 * the .promptless-container wrapper added by WooCommerce hooks.
 *
 * @package Promptless_Theme
 * @since 1.0.0
 * @see https://woocommerce.com/document/woocommerce-theme-developer-handbook/
 */

get_header();
?>

<main id="main-content" class="site-main <?php echo esc_attr( promptless_get_content_classes() ); ?>">
    <?php
    /**
     * woocommerce_before_main_content hook
     *
     * Outputs: promptless-container wrapper start
     *
     * @hooked Promptless_Setup::woocommerce_wrapper_start - 10
     */
    do_action( 'woocommerce_before_main_content' );

    // Shop/Archive page header
    if ( is_shop() || is_product_category() || is_product_tag() ) :
        ?>
        <header class="promptless-woocommerce__header">
            <h1 class="promptless-woocommerce__title"><?php woocommerce_page_title(); ?></h1>
            <?php
            // Archive description (category/tag)
            if ( is_product_category() || is_product_tag() ) {
                /**
                 * woocommerce_archive_description hook
                 *
                 * @hooked woocommerce_taxonomy_archive_description - 10
                 * @hooked woocommerce_product_archive_description - 10
                 */
                do_action( 'woocommerce_archive_description' );
            }
            ?>
        </header>
        <?php
    endif;

    // Main WooCommerce content
    woocommerce_content();

    /**
     * woocommerce_after_main_content hook
     *
     * Outputs: promptless-container wrapper end
     *
     * @hooked Promptless_Setup::woocommerce_wrapper_end - 10
     */
    do_action( 'woocommerce_after_main_content' );
    ?>
</main>

<?php
get_footer();
