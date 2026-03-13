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

    // Search Products by title only, word by word
    global $wpdb;

    // Split query into individual words
    $words = array_filter(preg_split('/\s+/', trim($query)));

    if (!empty($words)) {
        // Build WHERE clause: each word must match the title (AND logic)
        $where_clauses = [];
        $prepare_args = [];
        foreach ($words as $word) {
            $where_clauses[] = "p.post_title LIKE %s";
            $prepare_args[] = '%' . $wpdb->esc_like($word) . '%';
        }

        $where_sql = implode(' AND ', $where_clauses);

        // Get product IDs matching title search, ordered by total_sales
        $sql = "SELECT p.ID FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'total_sales'
                WHERE p.post_type = 'product'
                AND p.post_status = 'publish'
                AND ({$where_sql})
                ORDER BY CAST(pm.meta_value AS UNSIGNED) DESC
                LIMIT 20";

        $product_ids = $wpdb->get_col($wpdb->prepare($sql, ...$prepare_args));
    } else {
        $product_ids = [];
    }

    $count = 0;
    $total_found = 0;
    $added_ids = [];
    $added_names = [];
    $added_permalinks = [];

    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);
        if (!$product) continue;

        // Skip variations
        if ($product->is_type('variation') || $product->get_parent_id() > 0) {
            continue;
        }

        $product_name = $product->get_name();
        $permalink = $product->get_permalink();

        // Skip duplicates
        if (in_array($product_id, $added_ids) ||
            in_array($product_name, $added_names) ||
            in_array($permalink, $added_permalinks)) {
            continue;
        }

        $total_found++;

        if ($count >= 5) {
            continue;
        }

        // Check stock status
        $is_out_of_stock = false;
        if ($product->is_type('simple')) {
            $is_out_of_stock = ($product->get_stock_status() === 'outofstock' || !$product->is_in_stock());
        }

        $image = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
        $results['products'][] = [
            'id' => $product_id,
            'name' => $product_name,
            'price' => $is_out_of_stock ? '<span class="out-of-stock-badge">' . __('ناموجود', 'ganjeh') . '</span>' : $product->get_price_html(),
            'url' => $permalink,
            'image' => $image ?: '',
            'in_stock' => !$is_out_of_stock,
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

    // Sort products: in-stock first, then out-of-stock
    usort($results['products'], function($a, $b) {
        return $b['in_stock'] - $a['in_stock'];
    });

    $results['has_more'] = $total_found > 5;
    $results['search_url'] = add_query_arg('product_search', urlencode($query), wc_get_page_permalink('shop'));

    wp_send_json_success($results);
}
add_action('wp_ajax_ganjeh_search', 'ganjeh_ajax_search');
add_action('wp_ajax_nopriv_ganjeh_search', 'ganjeh_ajax_search');
