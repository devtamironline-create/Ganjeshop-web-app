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
        'limit' => 20, // Get more to filter
        'status' => 'publish',
        's' => $query,
        'orderby' => 'relevance',
    ]);

    $count = 0;
    $added_ids = []; // Track added product IDs to avoid duplicates
    $added_names = []; // Track added product names to avoid duplicates
    $added_permalinks = []; // Track added permalinks to avoid duplicates

    foreach ($products as $product) {
        if ($count >= 5) break;

        $product_id = $product->get_id();
        $product_name = $product->get_name();
        $permalink = $product->get_permalink();

        // Skip duplicates by ID, name, or permalink
        if (in_array($product_id, $added_ids) ||
            in_array($product_name, $added_names) ||
            in_array($permalink, $added_permalinks)) {
            continue;
        }

        // Skip out of stock products (only for simple products)
        // Grouped, bundle, and variable products have different stock logic
        if ($product->is_type('simple')) {
            if ($product->get_stock_status() === 'outofstock' || !$product->is_in_stock()) {
                continue;
            }
        }

        $image = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
        $results['products'][] = [
            'id' => $product_id,
            'name' => $product_name,
            'price' => $product->get_price_html(),
            'url' => $permalink,
            'image' => $image ?: '',
        ];
        $added_ids[] = $product_id;
        $added_names[] = $product_name;
        $added_permalinks[] = $permalink;
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
