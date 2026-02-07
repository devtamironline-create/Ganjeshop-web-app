<?php
/**
 * AJAX Live Search
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX Search Handler
 */
function ganjeh_ajax_search() {
    $query = sanitize_text_field($_POST['query'] ?? '');

    if (strlen($query) < 2) {
        wp_send_json_success(['products' => [], 'categories' => []]);
    }

    $results = [
        'products' => [],
        'categories' => []
    ];

    // Search Products
    $products = wc_get_products([
        'limit' => 5,
        'status' => 'publish',
        's' => $query,
        'orderby' => 'relevance',
    ]);

    foreach ($products as $product) {
        $image = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
        $results['products'][] = [
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'price' => $product->get_price_html(),
            'url' => $product->get_permalink(),
            'image' => $image ?: '',
        ];
    }

    // Search Categories
    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'name__like' => $query,
        'hide_empty' => false,
        'number' => 4,
    ]);

    if (!is_wp_error($categories)) {
        foreach ($categories as $cat) {
            $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
            $image = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '';
            $results['categories'][] = [
                'id' => $cat->term_id,
                'name' => $cat->name,
                'count' => $cat->count,
                'url' => get_term_link($cat),
                'image' => $image,
            ];
        }
    }

    wp_send_json_success($results);
}
add_action('wp_ajax_ganjeh_search', 'ganjeh_ajax_search');
add_action('wp_ajax_nopriv_ganjeh_search', 'ganjeh_ajax_search');
