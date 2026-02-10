<?php
/**
 * AJAX Handlers
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Update cart item quantity via AJAX
 */
function ganjeh_update_cart_item() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    // Accept both cart_item_key and cart_key for compatibility
    $cart_item_key = sanitize_text_field($_POST['cart_item_key'] ?? $_POST['cart_key'] ?? '');
    $quantity = absint($_POST['quantity']);

    if (!$cart_item_key) {
        wp_send_json_error(['message' => __('آیتم نامعتبر', 'ganjeh')]);
    }

    if ($quantity === 0) {
        WC()->cart->remove_cart_item($cart_item_key);
    } else {
        WC()->cart->set_quantity($cart_item_key, $quantity);
    }

    WC()->cart->calculate_totals();

    wp_send_json_success([
        'cart_count' => WC()->cart->get_cart_contents_count(),
        'cart_total' => WC()->cart->get_cart_total(),
        'subtotal'   => WC()->cart->get_cart_subtotal(),
    ]);
}
add_action('wp_ajax_ganjeh_update_cart_item', 'ganjeh_update_cart_item');
add_action('wp_ajax_nopriv_ganjeh_update_cart_item', 'ganjeh_update_cart_item');

/**
 * Remove cart item via AJAX
 */
function ganjeh_remove_cart_item() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    // Accept both cart_item_key and cart_key for compatibility
    $cart_item_key = sanitize_text_field($_POST['cart_item_key'] ?? $_POST['cart_key'] ?? '');

    if (!$cart_item_key) {
        wp_send_json_error(['message' => __('آیتم نامعتبر', 'ganjeh')]);
    }

    $removed = WC()->cart->remove_cart_item($cart_item_key);

    if ($removed) {
        wp_send_json_success([
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_total' => WC()->cart->get_cart_total(),
            'is_empty'   => WC()->cart->is_empty(),
        ]);
    } else {
        wp_send_json_error(['message' => __('خطا در حذف آیتم', 'ganjeh')]);
    }
}
add_action('wp_ajax_ganjeh_remove_cart_item', 'ganjeh_remove_cart_item');
add_action('wp_ajax_nopriv_ganjeh_remove_cart_item', 'ganjeh_remove_cart_item');

/**
 * Update shipping totals via AJAX (uses WooCommerce native shipping)
 */
function ganjeh_update_shipping_totals() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    // Set chosen shipping method in WC session
    if (isset($_POST['shipping_method']) && is_array($_POST['shipping_method'])) {
        $chosen = array_map('sanitize_text_field', $_POST['shipping_method']);
        WC()->session->set('chosen_shipping_methods', $chosen);
    }

    // Recalculate totals
    WC()->cart->calculate_shipping();
    WC()->cart->calculate_totals();

    $shipping_total = WC()->cart->get_shipping_total();

    wp_send_json_success([
        'shipping_cost' => $shipping_total > 0 ? wc_price($shipping_total) : __('رایگان', 'ganjeh'),
        'total' => WC()->cart->get_total(),
    ]);
}
add_action('wp_ajax_ganjeh_update_shipping_totals', 'ganjeh_update_shipping_totals');
add_action('wp_ajax_nopriv_ganjeh_update_shipping_totals', 'ganjeh_update_shipping_totals');

/**
 * Set custom shipping method and cost via AJAX
 */
function ganjeh_set_shipping_method() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    $method = sanitize_text_field($_POST['method'] ?? 'post');

    // Validate method
    $valid_methods = ['post', 'express', 'collection', 'pickup'];
    if (!in_array($method, $valid_methods)) {
        wp_send_json_error(['message' => 'روش ارسال نامعتبر']);
    }

    // Calculate cost server-side based on method and cart total
    $cart_subtotal = WC()->cart->get_subtotal();
    $free_threshold = 5000000;
    $is_free_eligible = ($cart_subtotal >= $free_threshold);

    $costs = [
        'post'       => $is_free_eligible ? 0 : 90000,
        'express'    => 200000, // always paid
        'collection' => $is_free_eligible ? 0 : 90000,
        'pickup'     => 0,     // always free
    ];

    $cost = $costs[$method];

    // Save to WC session
    WC()->session->set('ganjeh_shipping_method', $method);
    WC()->session->set('ganjeh_shipping_cost', $cost);

    // Recalculate cart totals (this triggers woocommerce_cart_calculate_fees)
    WC()->cart->calculate_totals();

    $cart_total = WC()->cart->get_total('edit');

    wp_send_json_success([
        'shipping_cost' => $cost > 0 ? wc_price($cost) : __('رایگان', 'ganjeh'),
        'total' => wc_price($cart_total),
    ]);
}
add_action('wp_ajax_ganjeh_set_shipping_method', 'ganjeh_set_shipping_method');
add_action('wp_ajax_nopriv_ganjeh_set_shipping_method', 'ganjeh_set_shipping_method');

/**
 * Get cart contents via AJAX (for refreshing cart)
 */
function ganjeh_get_cart() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    $cart_items = [];

    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];

        $cart_items[] = [
            'key'       => $cart_item_key,
            'id'        => $product->get_id(),
            'name'      => $product->get_name(),
            'quantity'  => $cart_item['quantity'],
            'price'     => $product->get_price(),
            'subtotal'  => WC()->cart->get_product_subtotal($product, $cart_item['quantity']),
            'image'     => wp_get_attachment_image_url($product->get_image_id(), 'ganjeh-product-thumb'),
            'permalink' => $product->get_permalink(),
        ];
    }

    wp_send_json_success([
        'items'      => $cart_items,
        'cart_count' => WC()->cart->get_cart_contents_count(),
        'cart_total' => WC()->cart->get_cart_total(),
        'subtotal'   => WC()->cart->get_cart_subtotal(),
    ]);
}
add_action('wp_ajax_ganjeh_get_cart', 'ganjeh_get_cart');
add_action('wp_ajax_nopriv_ganjeh_get_cart', 'ganjeh_get_cart');

/**
 * Product search via AJAX
 */
function ganjeh_product_search() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    $search_term = sanitize_text_field($_POST['s']);

    if (strlen($search_term) < 2) {
        wp_send_json_success(['products' => []]);
    }

    // Get all matching products first (exclude variations)
    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 30, // Get more to filter
        's'              => $search_term,
        'post_parent'    => 0, // Only get parent products, not variations
    ];

    $query = new WP_Query($args);
    $results = [];
    $count = 0;
    $added_ids = []; // Track added product IDs to avoid duplicates
    $added_post_ids = []; // Track added post IDs to avoid duplicates
    $added_names = []; // Track added product names to avoid duplicates
    $added_permalinks = []; // Track added permalinks to avoid duplicates

    if ($query->have_posts()) {
        while ($query->have_posts() && $count < 10) {
            $query->the_post();
            $post_id = get_the_ID();
            $product = wc_get_product($post_id);

            if (!$product) {
                continue;
            }

            // Skip variations (they should show as part of parent variable product)
            if ($product->is_type('variation') || $product->get_parent_id() > 0) {
                continue;
            }

            $product_id = $product->get_id();
            $product_name = $product->get_name();
            $permalink = $product->get_permalink();

            // Skip duplicates by post ID, product ID, name, or permalink
            if (in_array($post_id, $added_post_ids) ||
                in_array($product_id, $added_ids) ||
                in_array($product_name, $added_names) ||
                in_array($permalink, $added_permalinks)) {
                continue;
            }

            // Skip out of stock products (only for simple products)
            if ($product->is_type('simple')) {
                $stock_status = $product->get_stock_status();
                if ($stock_status === 'outofstock' || !$product->is_in_stock()) {
                    continue;
                }
            }

            $results[] = [
                'id'        => $product_id,
                'name'      => $product_name,
                'price'     => $product->get_price_html(),
                'image'     => wp_get_attachment_image_url($product->get_image_id(), 'ganjeh-product-thumb'),
                'permalink' => $permalink,
            ];
            $added_ids[] = $product_id;
            $added_post_ids[] = $post_id;
            $added_names[] = $product_name;
            $added_permalinks[] = $permalink;
            $count++;
        }
        wp_reset_postdata();
    }

    wp_send_json_success(['products' => $results]);
}
add_action('wp_ajax_ganjeh_product_search', 'ganjeh_product_search');
add_action('wp_ajax_nopriv_ganjeh_product_search', 'ganjeh_product_search');

/**
 * Apply coupon via AJAX
 */
function ganjeh_apply_coupon() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    $coupon_code = sanitize_text_field($_POST['coupon_code']);

    if (empty($coupon_code)) {
        wp_send_json_error(['message' => __('کد تخفیف را وارد کنید', 'ganjeh')]);
    }

    $applied = WC()->cart->apply_coupon($coupon_code);

    if ($applied) {
        wp_send_json_success([
            'message'    => __('کد تخفیف اعمال شد', 'ganjeh'),
            'cart_total' => WC()->cart->get_cart_total(),
            'discount'   => WC()->cart->get_discount_total(),
        ]);
    } else {
        $error = wc_get_notices('error');
        wc_clear_notices();

        wp_send_json_error([
            'message' => !empty($error) ? strip_tags($error[0]['notice']) : __('کد تخفیف نامعتبر است', 'ganjeh'),
        ]);
    }
}
add_action('wp_ajax_ganjeh_apply_coupon', 'ganjeh_apply_coupon');
add_action('wp_ajax_nopriv_ganjeh_apply_coupon', 'ganjeh_apply_coupon');

/**
 * Get cross-sell products for checkout popup
 */
function ganjeh_get_crosssell_products() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    $cross_sell_ids = [];
    $cart_product_ids = [];

    // Get all product IDs from cart
    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = $cart_item['product_id'];
        $cart_product_ids[] = $product_id;

        // Get cross-sells for this product
        $product = wc_get_product($product_id);
        if ($product) {
            $product_cross_sells = $product->get_cross_sell_ids();
            $cross_sell_ids = array_merge($cross_sell_ids, $product_cross_sells);
        }
    }

    // Remove duplicates and products already in cart
    $cross_sell_ids = array_unique($cross_sell_ids);
    $cross_sell_ids = array_diff($cross_sell_ids, $cart_product_ids);

    // If no cross-sells defined, get related products based on cart categories
    if (empty($cross_sell_ids)) {
        $category_ids = [];

        foreach ($cart_product_ids as $product_id) {
            $terms = get_the_terms($product_id, 'product_cat');
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $category_ids[] = $term->term_id;
                }
            }
        }

        $category_ids = array_unique($category_ids);

        if (!empty($category_ids)) {
            $related_products = wc_get_products([
                'limit'    => 6,
                'status'   => 'publish',
                'category' => $category_ids,
                'exclude'  => $cart_product_ids,
                'orderby'  => 'rand',
            ]);

            foreach ($related_products as $product) {
                $cross_sell_ids[] = $product->get_id();
            }
        }
    }

    // If still no products, get best-selling products
    if (empty($cross_sell_ids)) {
        $best_selling = wc_get_products([
            'limit'    => 6,
            'status'   => 'publish',
            'orderby'  => 'meta_value_num',
            'meta_key' => 'total_sales',
            'order'    => 'DESC',
            'exclude'  => $cart_product_ids,
        ]);

        foreach ($best_selling as $product) {
            $cross_sell_ids[] = $product->get_id();
        }
    }

    // Limit to 6 products
    $cross_sell_ids = array_slice($cross_sell_ids, 0, 6);

    // Build product data
    $products = [];

    foreach ($cross_sell_ids as $product_id) {
        $product = wc_get_product($product_id);

        // Skip variable products - only show simple products
        if (!$product || !$product->is_purchasable() || !$product->is_in_stock() || $product->is_type('variable')) {
            continue;
        }

        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();
        $discount = 0;

        if ($regular_price && $sale_price && $sale_price < $regular_price) {
            $discount = round((($regular_price - $sale_price) / $regular_price) * 100);
        }

        $products[] = [
            'id'            => $product->get_id(),
            'name'          => $product->get_name(),
            'price'         => $product->get_price_html(),
            'regular_price' => $regular_price ? wc_price($regular_price) : '',
            'image'         => wp_get_attachment_image_url($product->get_image_id(), 'ganjeh-product-thumb') ?: wc_placeholder_img_src('ganjeh-product-thumb'),
            'discount'      => $discount,
        ];
    }

    wp_send_json_success(['products' => $products]);
}
add_action('wp_ajax_ganjeh_get_crosssell_products', 'ganjeh_get_crosssell_products');
add_action('wp_ajax_nopriv_ganjeh_get_crosssell_products', 'ganjeh_get_crosssell_products');

/**
 * Add cross-sell product to cart
 */
function ganjeh_add_crosssell_to_cart() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    $product_id = absint($_POST['product_id']);

    if (!$product_id) {
        wp_send_json_error(['message' => __('محصول نامعتبر', 'ganjeh')]);
    }

    $product = wc_get_product($product_id);

    if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
        wp_send_json_error(['message' => __('این محصول قابل خرید نیست', 'ganjeh')]);
    }

    // Add to cart
    $added = WC()->cart->add_to_cart($product_id, 1);

    if ($added) {
        WC()->cart->calculate_totals();

        // Get shipping cost from session
        $shipping_cost = WC()->session->get('ganjeh_shipping_cost', 90000);
        $cart_total = WC()->cart->get_total('edit') + $shipping_cost;

        wp_send_json_success([
            'message'    => __('محصول به سبد اضافه شد', 'ganjeh'),
            'cart_total' => wc_price($cart_total),
            'cart_count' => WC()->cart->get_cart_contents_count(),
        ]);
    } else {
        wp_send_json_error(['message' => __('خطا در افزودن محصول', 'ganjeh')]);
    }
}
add_action('wp_ajax_ganjeh_add_crosssell_to_cart', 'ganjeh_add_crosssell_to_cart');
add_action('wp_ajax_nopriv_ganjeh_add_crosssell_to_cart', 'ganjeh_add_crosssell_to_cart');
