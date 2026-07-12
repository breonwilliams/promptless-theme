<?php
/**
 * Breadcrumbs
 *
 * Location-based (hierarchy) breadcrumb trail rendered on the
 * `promptless_after_header` action, following the WAI-ARIA APG breadcrumb
 * pattern, with optional BreadcrumbList JSON-LD.
 *
 * Design contract: docs/BREADCRUMBS_DESIGN_EXPLORATION.md
 *
 * Key rules implemented here:
 * - Trails reflect the page's canonical position in the site hierarchy,
 *   never the visitor's click path (NN/g location-based model).
 * - Front page never shows a trail. WooCommerce contexts defer to
 *   WooCommerce's own breadcrumb (the theme already styles it).
 * - Exactly one BreadcrumbList per page: our JSON-LD is suppressed when a
 *   dedicated SEO plugin is active (they emit their own).
 * - Visible trail may collapse middle crumbs on deep page ancestry; the
 *   JSON-LD always carries the full trail.
 * - Per-post kill switch via `_promptless_breadcrumbs` post meta ('hide'),
 *   for landing pages that want seamless edge-to-edge heroes.
 *
 * @package Promptless_Theme
 * @since 1.3.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Promptless_Breadcrumbs
 */
class Promptless_Breadcrumbs {

    /**
     * Per-post override meta key. Values: '' (default) | 'hide'.
     *
     * @var string
     */
    const META_KEY = '_promptless_breadcrumbs';

    /**
     * Visible trails longer than this collapse their middle crumbs into an
     * ellipsis (Home > … > Parent > Current). Schema always stays full.
     *
     * @var int
     */
    const COLLAPSE_THRESHOLD = 5;

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'promptless_after_header', array( $this, 'render' ) );
        add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_meta_box' ) );
    }

    /**
     * Render the breadcrumb trail (and JSON-LD) when the current context
     * qualifies. Hooked on promptless_after_header, so it fires once per
     * page on every template that calls get_header().
     */
    public function render() {
        if ( ! promptless_show_breadcrumbs() ) {
            return;
        }

        $items = $this->build_items();

        // A trail needs at least Home + one more crumb to orient anyone.
        if ( count( $items ) < 2 ) {
            return;
        }

        $visible = $this->collapse_items( $items );

        echo '<nav class="' . esc_attr( promptless_get_breadcrumbs_classes() ) . '" aria-label="' . esc_attr__( 'Breadcrumb', 'promptless' ) . '">';
        echo '<div class="promptless-container">';
        echo '<ol class="promptless-breadcrumbs__list">';

        $last_index = count( $visible ) - 1;
        foreach ( $visible as $index => $item ) {
            echo '<li class="promptless-breadcrumbs__item">';
            if ( $index === $last_index ) {
                // Leaf: current page, non-link text per NN/g + APG (when the
                // current item is not a link, aria-current is optional but
                // still communicates "you are here" to assistive tech).
                echo '<span class="promptless-breadcrumbs__current" aria-current="page">' . esc_html( $item['label'] ) . '</span>';
            } elseif ( ! empty( $item['url'] ) ) {
                echo '<a class="promptless-breadcrumbs__link" href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';
            } else {
                // Non-link middle crumb (collapsed ellipsis, or an archive
                // label whose CPT registered has_archive: false).
                echo '<span class="promptless-breadcrumbs__text">' . esc_html( $item['label'] ) . '</span>';
            }
            echo '</li>';
        }

        echo '</ol>';
        echo '</div>';
        echo '</nav>';

        $this->render_schema( $items );
    }

    /**
     * Build the trail for the current query context.
     *
     * Returns a flat list of ['label' => string, 'url' => string|null].
     * The last item is always the current page (leaf); its url is ignored
     * by the renderer and omitted from schema per Google's guidance.
     *
     * @return array[]
     */
    public function build_items() {
        $items   = array();
        $items[] = array(
            'label' => get_theme_mod( 'promptless_breadcrumbs_home_label', __( 'Home', 'promptless' ) ),
            'url'   => home_url( '/' ),
        );

        if ( is_home() ) {
            // Blog index (not the front page — that's suppressed upstream).
            $items[] = array(
                'label' => $this->get_blog_label(),
                'url'   => null,
            );
        } elseif ( is_singular() ) {
            $items = array_merge( $items, $this->build_singular_items() );
        } elseif ( is_post_type_archive() ) {
            $items[] = array(
                'label' => post_type_archive_title( '', false ),
                'url'   => null,
            );
        } elseif ( is_category() || is_tag() || is_tax() ) {
            $items = array_merge( $items, $this->build_term_items() );
        } elseif ( is_search() ) {
            $items[] = array(
                /* translators: %s: the visitor's search query */
                'label' => sprintf( __( 'Search results for “%s”', 'promptless' ), get_search_query() ),
                'url'   => null,
            );
        } elseif ( is_404() ) {
            $items[] = array(
                'label' => __( 'Page not found', 'promptless' ),
                'url'   => null,
            );
        } elseif ( is_author() ) {
            $author  = get_queried_object();
            $items[] = array(
                'label' => ( $author instanceof WP_User ) ? $author->display_name : __( 'Author', 'promptless' ),
                'url'   => null,
            );
        } elseif ( is_date() ) {
            $items[] = array(
                'label' => wp_strip_all_tags( get_the_archive_title() ),
                'url'   => null,
            );
        } elseif ( is_archive() ) {
            // Any archive flavor not matched above.
            $items[] = array(
                'label' => wp_strip_all_tags( get_the_archive_title() ),
                'url'   => null,
            );
        }

        /**
         * Filter the breadcrumb trail items before rendering.
         *
         * This is the extensibility contract: SEO-plugin trail swaps, client
         * customizations, and future context enrichment all land here rather
         * than forking the builder.
         *
         * @since 1.3.0
         *
         * @param array[] $items Flat list of ['label' => string, 'url' => string|null].
         *                       The last item is the current page (leaf).
         */
        return apply_filters( 'promptless_breadcrumbs_items', $items );
    }

    /**
     * Trail segments for singular views (post, page, CPT single),
     * excluding the leading Home crumb.
     *
     * @return array[]
     */
    private function build_singular_items() {
        $items = array();
        $post  = get_queried_object();

        if ( ! $post instanceof WP_Post ) {
            return $items;
        }

        if ( 'post' === $post->post_type ) {
            // Home > [Blog] > [Category chain] > Post
            $blog_crumb = $this->get_blog_crumb();
            if ( $blog_crumb ) {
                $items[] = $blog_crumb;
            }
            if ( get_theme_mod( 'promptless_breadcrumbs_show_category', true ) ) {
                $items = array_merge( $items, $this->get_post_category_items( $post ) );
            }
        } elseif ( 'page' === $post->post_type ) {
            // Home > Ancestor(s) > Page
            $items = array_merge( $items, $this->get_ancestor_items( $post ) );
        } else {
            // CPT single: Home > {Archive} > [Ancestor(s)] > Post.
            // Covers Promptless CPT Pages singles — the archive crumb comes
            // from the registered post type object (plural label +
            // get_post_type_archive_link()), so PRE needs no coupling.
            $type_object = get_post_type_object( $post->post_type );
            if ( $type_object ) {
                $archive_url = $type_object->has_archive ? get_post_type_archive_link( $post->post_type ) : false;
                $items[]     = array(
                    'label' => $type_object->labels->name,
                    // Only link the archive crumb when the CPT actually has
                    // an archive; otherwise the label renders as plain text.
                    'url'   => $archive_url ? $archive_url : null,
                );
            }
            if ( is_post_type_hierarchical( $post->post_type ) ) {
                $items = array_merge( $items, $this->get_ancestor_items( $post ) );
            }
        }

        // Leaf: current post title.
        $items[] = array(
            'label' => get_the_title( $post ),
            'url'   => null,
        );

        return $items;
    }

    /**
     * Trail segments for taxonomy term archives (category, tag, custom tax),
     * excluding the leading Home crumb.
     *
     * @return array[]
     */
    private function build_term_items() {
        $items = array();
        $term  = get_queried_object();

        if ( ! $term instanceof WP_Term ) {
            return $items;
        }

        if ( in_array( $term->taxonomy, array( 'category', 'post_tag' ), true ) ) {
            // Post taxonomies: Home > [Blog] > [Parent terms] > Term
            $blog_crumb = $this->get_blog_crumb();
            if ( $blog_crumb ) {
                $items[] = $blog_crumb;
            }
        } else {
            // Custom taxonomy: if it belongs to a (non-post) CPT with an
            // archive, orient the trail under that CPT: Home > {CPT} > Term.
            $taxonomy = get_taxonomy( $term->taxonomy );
            if ( $taxonomy && ! empty( $taxonomy->object_type ) ) {
                $post_type = reset( $taxonomy->object_type );
                if ( 'post' !== $post_type ) {
                    $type_object = get_post_type_object( $post_type );
                    if ( $type_object && $type_object->has_archive ) {
                        $archive_url = get_post_type_archive_link( $post_type );
                        if ( $archive_url ) {
                            $items[] = array(
                                'label' => $type_object->labels->name,
                                'url'   => $archive_url,
                            );
                        }
                    }
                }
            }
        }

        // Hierarchical term ancestors, top-down.
        if ( is_taxonomy_hierarchical( $term->taxonomy ) ) {
            $ancestor_ids = array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) );
            foreach ( $ancestor_ids as $ancestor_id ) {
                $ancestor = get_term( $ancestor_id, $term->taxonomy );
                if ( $ancestor instanceof WP_Term ) {
                    $link    = get_term_link( $ancestor );
                    $items[] = array(
                        'label' => $ancestor->name,
                        'url'   => is_wp_error( $link ) ? null : $link,
                    );
                }
            }
        }

        // Leaf: the term itself.
        $items[] = array(
            'label' => $term->name,
            'url'   => null,
        );

        return $items;
    }

    /**
     * Ancestor chain for hierarchical post types, top-down.
     *
     * @param WP_Post $post Current post.
     * @return array[]
     */
    private function get_ancestor_items( $post ) {
        $items        = array();
        $ancestor_ids = array_reverse( get_post_ancestors( $post ) );

        foreach ( $ancestor_ids as $ancestor_id ) {
            $items[] = array(
                'label' => get_the_title( $ancestor_id ),
                'url'   => get_permalink( $ancestor_id ),
            );
        }

        return $items;
    }

    /**
     * The category chain for a blog post: the post's canonical category
     * plus its hierarchical ancestors, top-down.
     *
     * Canonical category resolution (NN/g: polyhierarchical pages should
     * pick ONE canonical parent): Yoast's primary category when set, then
     * Rank Math's, then the first assigned category. The default
     * "Uncategorized" bucket is never shown — it orients nobody.
     *
     * @param WP_Post $post Current post.
     * @return array[]
     */
    private function get_post_category_items( $post ) {
        $items      = array();
        $categories = get_the_category( $post->ID );

        if ( empty( $categories ) ) {
            return $items;
        }

        $category = $categories[0];

        // Prefer the SEO plugin's "primary category" designation when set.
        $primary_id = 0;
        $yoast_meta = get_post_meta( $post->ID, '_yoast_wpseo_primary_category', true );
        if ( $yoast_meta ) {
            $primary_id = (int) $yoast_meta;
        } else {
            $rank_math_meta = get_post_meta( $post->ID, 'rank_math_primary_category', true );
            if ( $rank_math_meta ) {
                $primary_id = (int) $rank_math_meta;
            }
        }
        if ( $primary_id > 0 ) {
            foreach ( $categories as $candidate ) {
                if ( (int) $candidate->term_id === $primary_id ) {
                    $category = $candidate;
                    break;
                }
            }
        }

        // Skip the default bucket entirely.
        if ( (int) get_option( 'default_category' ) === (int) $category->term_id ) {
            return $items;
        }

        $chain_ids   = array_reverse( get_ancestors( $category->term_id, 'category', 'taxonomy' ) );
        $chain_ids[] = $category->term_id;

        foreach ( $chain_ids as $term_id ) {
            $term = get_term( $term_id, 'category' );
            if ( $term instanceof WP_Term ) {
                $link    = get_term_link( $term );
                $items[] = array(
                    'label' => $term->name,
                    'url'   => is_wp_error( $link ) ? null : $link,
                );
            }
        }

        return $items;
    }

    /**
     * The "Blog" crumb — only meaningful when a static posts page is set
     * (show_on_front = page). With posts on the front page there is no
     * intermediate blog location to link, so no phantom crumb is added.
     *
     * @return array|null ['label','url'] or null.
     */
    private function get_blog_crumb() {
        $posts_page_id = (int) get_option( 'page_for_posts' );

        if ( 'page' !== get_option( 'show_on_front' ) || $posts_page_id <= 0 ) {
            return null;
        }

        return array(
            'label' => get_the_title( $posts_page_id ),
            'url'   => get_permalink( $posts_page_id ),
        );
    }

    /**
     * Label for the blog index leaf.
     *
     * @return string
     */
    private function get_blog_label() {
        $posts_page_id = (int) get_option( 'page_for_posts' );
        if ( $posts_page_id > 0 ) {
            return get_the_title( $posts_page_id );
        }
        return __( 'Blog', 'promptless' );
    }

    /**
     * Collapse middle crumbs on deep trails for the VISIBLE list only
     * (Home > … > Parent > Current). Schema always uses the full trail —
     * Google's guidance is to carry the complete hierarchy even when the
     * visible presentation truncates.
     *
     * @param array[] $items Full trail.
     * @return array[] Possibly-collapsed trail.
     */
    private function collapse_items( $items ) {
        if ( count( $items ) <= self::COLLAPSE_THRESHOLD ) {
            return $items;
        }

        return array_merge(
            array( $items[0] ),
            array(
                array(
                    'label' => '…',
                    'url'   => null,
                ),
            ),
            array_slice( $items, -3 )
        );
    }

    /**
     * Emit BreadcrumbList JSON-LD for the (full) trail.
     *
     * Suppressed when a dedicated SEO plugin is active — Yoast, Rank Math,
     * AIOSEO, SEOPress, The SEO Framework, and Slim SEO all emit their own
     * BreadcrumbList, and duplicate breadcrumb markup is the classic
     * implementation bug. Detection mirrors Promptless CPT Pages'
     * PCPTPages_Meta_Tags::is_seo_plugin_active() so the whole ecosystem
     * agrees on who owns the document head.
     *
     * @param array[] $items Full trail.
     */
    private function render_schema( $items ) {
        if ( ! get_theme_mod( 'promptless_breadcrumbs_schema', true ) ) {
            return;
        }

        /**
         * Filter whether the theme emits BreadcrumbList JSON-LD.
         *
         * Defaults to false when a dedicated SEO plugin is active (those
         * plugins emit their own BreadcrumbList when configured). Return
         * true to force emission, false to always suppress.
         *
         * @since 1.3.0
         *
         * @param bool $enabled Whether to emit the JSON-LD block.
         */
        if ( ! apply_filters( 'promptless_breadcrumbs_schema_enabled', ! self::is_seo_plugin_active() ) ) {
            return;
        }

        $list_items = array();
        $position   = 1;
        $last_index = count( $items ) - 1;

        foreach ( $items as $index => $item ) {
            $list_item = array(
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => wp_strip_all_tags( $item['label'] ),
            );
            // Google: the item URL may be omitted for the final (current
            // page) crumb; omit for any non-linked crumb as well.
            if ( $index !== $last_index && ! empty( $item['url'] ) ) {
                $list_item['item'] = esc_url_raw( $item['url'] );
            }
            $list_items[] = $list_item;
            $position++;
        }

        $schema = array(
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $list_items,
        );

        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
    }

    /**
     * Is a dedicated SEO plugin active?
     *
     * Same constant list as PCPTPages_Meta_Tags::is_seo_plugin_active().
     *
     * @return bool
     */
    public static function is_seo_plugin_active() {
        return defined( 'WPSEO_VERSION' )             // Yoast SEO.
            || defined( 'RANK_MATH_VERSION' )         // Rank Math.
            || defined( 'AIOSEO_VERSION' )            // All in One SEO.
            || defined( 'SEOPRESS_VERSION' )          // SEOPress.
            || defined( 'THE_SEO_FRAMEWORK_VERSION' ) // The SEO Framework.
            || defined( 'SLIM_SEO_VER' );             // Slim SEO.
    }

    // =========================================================
    // Per-post override (landing-page kill switch)
    // =========================================================

    /**
     * Register the per-post override meta box on all public post types.
     * Only when breadcrumbs are enabled site-wide — no dead UI otherwise.
     */
    public function register_meta_box() {
        if ( ! get_theme_mod( 'promptless_breadcrumbs_enabled', false ) ) {
            return;
        }

        $post_types = get_post_types( array( 'public' => true ) );
        unset( $post_types['attachment'] );

        add_meta_box(
            'promptless-breadcrumbs',
            __( 'Breadcrumbs', 'promptless' ),
            array( $this, 'render_meta_box' ),
            array_values( $post_types ),
            'side',
            'default'
        );
    }

    /**
     * Render the override meta box.
     *
     * @param WP_Post $post Current post.
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( 'promptless_breadcrumbs_meta', 'promptless_breadcrumbs_nonce' );
        $value = get_post_meta( $post->ID, self::META_KEY, true );
        ?>
        <label class="screen-reader-text" for="promptless-breadcrumbs-visibility"><?php esc_html_e( 'Breadcrumb visibility', 'promptless' ); ?></label>
        <select name="promptless_breadcrumbs_visibility" id="promptless-breadcrumbs-visibility" style="width:100%">
            <option value="" <?php selected( $value, '' ); ?>><?php esc_html_e( 'Default (follow theme settings)', 'promptless' ); ?></option>
            <option value="hide" <?php selected( $value, 'hide' ); ?>><?php esc_html_e( 'Hide on this page', 'promptless' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'Hide the breadcrumb trail on this page — useful for landing pages with edge-to-edge hero sections.', 'promptless' ); ?></p>
        <?php
    }

    /**
     * Persist the override meta box value.
     *
     * @param int $post_id Post being saved.
     */
    public function save_meta_box( $post_id ) {
        if (
            ! isset( $_POST['promptless_breadcrumbs_nonce'] ) ||
            ! wp_verify_nonce( sanitize_key( $_POST['promptless_breadcrumbs_nonce'] ), 'promptless_breadcrumbs_meta' )
        ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        if ( ! isset( $_POST['promptless_breadcrumbs_visibility'] ) ) {
            return;
        }

        $value = sanitize_key( wp_unslash( $_POST['promptless_breadcrumbs_visibility'] ) );

        if ( 'hide' === $value ) {
            update_post_meta( $post_id, self::META_KEY, 'hide' );
        } else {
            delete_post_meta( $post_id, self::META_KEY );
        }
    }
}
