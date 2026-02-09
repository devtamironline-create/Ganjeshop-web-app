<?php
/**
 * Duplicate Content Functionality
 *
 * Adds duplicate functionality for posts, products, and categories
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add duplicate link to post/product row actions
 */
function ganjeh_duplicate_post_link($actions, $post) {
    if (!current_user_can('edit_posts')) {
        return $actions;
    }

    $post_type_object = get_post_type_object($post->post_type);
    if (!$post_type_object) {
        return $actions;
    }

    $url = wp_nonce_url(
        admin_url('admin.php?action=ganjeh_duplicate_post&post=' . $post->ID),
        'ganjeh_duplicate_post_' . $post->ID
    );

    $actions['duplicate'] = sprintf(
        '<a href="%s" title="%s" rel="permalink">%s</a>',
        $url,
        __('کپی از این مورد', 'ganjeh'),
        __('کپی', 'ganjeh')
    );

    return $actions;
}
add_filter('post_row_actions', 'ganjeh_duplicate_post_link', 10, 2);
add_filter('page_row_actions', 'ganjeh_duplicate_post_link', 10, 2);

/**
 * Add duplicate link to WooCommerce product row actions
 */
function ganjeh_duplicate_product_link($actions, $post) {
    if ($post->post_type !== 'product') {
        return $actions;
    }

    if (!current_user_can('edit_products')) {
        return $actions;
    }

    $url = wp_nonce_url(
        admin_url('admin.php?action=ganjeh_duplicate_post&post=' . $post->ID),
        'ganjeh_duplicate_post_' . $post->ID
    );

    $actions['ganjeh_duplicate'] = sprintf(
        '<a href="%s" title="%s" rel="permalink">%s</a>',
        $url,
        __('کپی کامل با محتویات', 'ganjeh'),
        __('کپی کامل', 'ganjeh')
    );

    return $actions;
}
add_filter('post_row_actions', 'ganjeh_duplicate_product_link', 10, 2);

/**
 * Handle post/product duplication
 */
function ganjeh_duplicate_post_action() {
    if (!isset($_GET['post']) || !isset($_GET['_wpnonce'])) {
        wp_die(__('درخواست نامعتبر', 'ganjeh'));
    }

    $post_id = absint($_GET['post']);

    if (!wp_verify_nonce($_GET['_wpnonce'], 'ganjeh_duplicate_post_' . $post_id)) {
        wp_die(__('خطای امنیتی', 'ganjeh'));
    }

    $post = get_post($post_id);

    if (!$post) {
        wp_die(__('پست یافت نشد', 'ganjeh'));
    }

    if (!current_user_can('edit_post', $post_id)) {
        wp_die(__('شما اجازه کپی این مورد را ندارید', 'ganjeh'));
    }

    $new_post_id = ganjeh_duplicate_post($post);

    if (is_wp_error($new_post_id)) {
        wp_die($new_post_id->get_error_message());
    }

    // Redirect to edit the new post
    $redirect_url = admin_url('post.php?action=edit&post=' . $new_post_id);

    // For products, redirect to product edit page
    if ($post->post_type === 'product') {
        $redirect_url = admin_url('post.php?action=edit&post=' . $new_post_id);
    }

    wp_redirect($redirect_url);
    exit;
}
add_action('admin_action_ganjeh_duplicate_post', 'ganjeh_duplicate_post_action');

/**
 * Duplicate a post with all its meta and taxonomies
 */
function ganjeh_duplicate_post($post) {
    $new_post_args = [
        'post_title'     => $post->post_title . ' (کپی)',
        'post_content'   => $post->post_content,
        'post_excerpt'   => $post->post_excerpt,
        'post_status'    => 'draft',
        'post_type'      => $post->post_type,
        'post_author'    => get_current_user_id(),
        'post_parent'    => $post->post_parent,
        'menu_order'     => $post->menu_order,
        'comment_status' => $post->comment_status,
        'ping_status'    => $post->ping_status,
        'post_password'  => $post->post_password,
    ];

    $new_post_id = wp_insert_post($new_post_args);

    if (is_wp_error($new_post_id)) {
        return $new_post_id;
    }

    // Copy all post meta
    $post_meta = get_post_meta($post->ID);

    foreach ($post_meta as $meta_key => $meta_values) {
        // Skip internal WordPress meta
        if (in_array($meta_key, ['_edit_lock', '_edit_last'])) {
            continue;
        }

        foreach ($meta_values as $meta_value) {
            $meta_value = maybe_unserialize($meta_value);
            add_post_meta($new_post_id, $meta_key, $meta_value);
        }
    }

    // Copy all taxonomies (categories, tags, product_cat, etc.)
    $taxonomies = get_object_taxonomies($post->post_type);

    foreach ($taxonomies as $taxonomy) {
        $terms = wp_get_object_terms($post->ID, $taxonomy, ['fields' => 'ids']);
        if (!is_wp_error($terms) && !empty($terms)) {
            wp_set_object_terms($new_post_id, $terms, $taxonomy);
        }
    }

    // For WooCommerce products, handle special cases
    if ($post->post_type === 'product') {
        ganjeh_duplicate_product_extras($post->ID, $new_post_id);
    }

    return $new_post_id;
}

/**
 * Handle WooCommerce product specific duplication
 */
function ganjeh_duplicate_product_extras($original_id, $new_id) {
    $original_product = wc_get_product($original_id);

    if (!$original_product) {
        return;
    }

    // Copy product attributes
    $attributes = $original_product->get_attributes();
    if (!empty($attributes)) {
        update_post_meta($new_id, '_product_attributes', $attributes);
    }

    // For grouped products, copy children
    if ($original_product->is_type('grouped')) {
        $children = $original_product->get_children();
        if (!empty($children)) {
            update_post_meta($new_id, '_children', $children);
        }
    }

    // For variable products, duplicate variations
    if ($original_product->is_type('variable')) {
        ganjeh_duplicate_product_variations($original_id, $new_id);
    }

    // Copy product gallery
    $gallery_ids = $original_product->get_gallery_image_ids();
    if (!empty($gallery_ids)) {
        update_post_meta($new_id, '_product_image_gallery', implode(',', $gallery_ids));
    }

    // Copy upsells and cross-sells
    $upsell_ids = get_post_meta($original_id, '_upsell_ids', true);
    if (!empty($upsell_ids)) {
        update_post_meta($new_id, '_upsell_ids', $upsell_ids);
    }

    $crosssell_ids = get_post_meta($original_id, '_crosssell_ids', true);
    if (!empty($crosssell_ids)) {
        update_post_meta($new_id, '_crosssell_ids', $crosssell_ids);
    }

    // Clear transients
    wc_delete_product_transients($new_id);
}

/**
 * Duplicate product variations
 */
function ganjeh_duplicate_product_variations($original_id, $new_id) {
    $variations = get_posts([
        'post_type'   => 'product_variation',
        'post_parent' => $original_id,
        'numberposts' => -1,
        'post_status' => 'any',
    ]);

    foreach ($variations as $variation) {
        $variation_args = [
            'post_title'   => $variation->post_title,
            'post_content' => $variation->post_content,
            'post_status'  => $variation->post_status,
            'post_type'    => 'product_variation',
            'post_parent'  => $new_id,
            'menu_order'   => $variation->menu_order,
        ];

        $new_variation_id = wp_insert_post($variation_args);

        if (!is_wp_error($new_variation_id)) {
            // Copy variation meta
            $variation_meta = get_post_meta($variation->ID);

            foreach ($variation_meta as $meta_key => $meta_values) {
                if (in_array($meta_key, ['_edit_lock', '_edit_last'])) {
                    continue;
                }

                foreach ($meta_values as $meta_value) {
                    $meta_value = maybe_unserialize($meta_value);
                    add_post_meta($new_variation_id, $meta_key, $meta_value);
                }
            }
        }
    }
}

/**
 * Add duplicate link to category/term row actions
 */
function ganjeh_duplicate_term_link($actions, $term) {
    if (!current_user_can('manage_categories')) {
        return $actions;
    }

    $url = wp_nonce_url(
        admin_url('admin.php?action=ganjeh_duplicate_term&term_id=' . $term->term_id . '&taxonomy=' . $term->taxonomy),
        'ganjeh_duplicate_term_' . $term->term_id
    );

    $actions['duplicate'] = sprintf(
        '<a href="%s" title="%s">%s</a>',
        $url,
        __('کپی از این دسته‌بندی', 'ganjeh'),
        __('کپی', 'ganjeh')
    );

    return $actions;
}

// Add to all taxonomy row actions
add_action('admin_init', function() {
    $taxonomies = get_taxonomies(['public' => true], 'names');

    foreach ($taxonomies as $taxonomy) {
        add_filter($taxonomy . '_row_actions', 'ganjeh_duplicate_term_link', 10, 2);
    }
});

/**
 * Handle term/category duplication
 */
function ganjeh_duplicate_term_action() {
    if (!isset($_GET['term_id']) || !isset($_GET['taxonomy']) || !isset($_GET['_wpnonce'])) {
        wp_die(__('درخواست نامعتبر', 'ganjeh'));
    }

    $term_id = absint($_GET['term_id']);
    $taxonomy = sanitize_text_field($_GET['taxonomy']);

    if (!wp_verify_nonce($_GET['_wpnonce'], 'ganjeh_duplicate_term_' . $term_id)) {
        wp_die(__('خطای امنیتی', 'ganjeh'));
    }

    if (!current_user_can('manage_categories')) {
        wp_die(__('شما اجازه کپی این مورد را ندارید', 'ganjeh'));
    }

    $term = get_term($term_id, $taxonomy);

    if (!$term || is_wp_error($term)) {
        wp_die(__('دسته‌بندی یافت نشد', 'ganjeh'));
    }

    $new_term_id = ganjeh_duplicate_term($term);

    if (is_wp_error($new_term_id)) {
        wp_die($new_term_id->get_error_message());
    }

    // Redirect to edit the new term
    $redirect_url = admin_url('term.php?taxonomy=' . $taxonomy . '&tag_ID=' . $new_term_id);

    wp_redirect($redirect_url);
    exit;
}
add_action('admin_action_ganjeh_duplicate_term', 'ganjeh_duplicate_term_action');

/**
 * Duplicate a term with all its meta
 */
function ganjeh_duplicate_term($term) {
    $new_term = wp_insert_term(
        $term->name . ' (کپی)',
        $term->taxonomy,
        [
            'description' => $term->description,
            'parent'      => $term->parent,
            'slug'        => $term->slug . '-copy-' . time(),
        ]
    );

    if (is_wp_error($new_term)) {
        return $new_term;
    }

    $new_term_id = $new_term['term_id'];

    // Copy all term meta
    $term_meta = get_term_meta($term->term_id);

    foreach ($term_meta as $meta_key => $meta_values) {
        foreach ($meta_values as $meta_value) {
            $meta_value = maybe_unserialize($meta_value);
            add_term_meta($new_term_id, $meta_key, $meta_value);
        }
    }

    return $new_term_id;
}

/**
 * Add admin styles for duplicate button
 */
function ganjeh_duplicate_admin_styles() {
    ?>
    <style>
        .row-actions .duplicate a,
        .row-actions .ganjeh_duplicate a {
            color: #2271b1;
        }
        .row-actions .duplicate a:hover,
        .row-actions .ganjeh_duplicate a:hover {
            color: #135e96;
        }
    </style>
    <?php
}
add_action('admin_head', 'ganjeh_duplicate_admin_styles');
