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
        'limit' => 10, // Get more to filter
        'status' => 'publish',
        's' => $query,
        'orderby' => 'relevance',
    ]);

    $count = 0;
    foreach ($products as $product) {
        if ($count >= 5) break;

        // Skip out of stock products
        if ($product->get_stock_status() === 'outofstock' || !$product->is_in_stock()) {
            continue;
        }

        $image = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
        $results['products'][] = [
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'price' => $product->get_price_html(),
            'url' => $product->get_permalink(),
            'image' => $image ?: '',
        ];
        $count++;
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
