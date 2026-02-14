<?php
/**
 * WooCommerce Functions
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if WooCommerce is active
 */
if (!class_exists('WooCommerce')) {
    return;
}

/**
 * Disable default WooCommerce styles
 */
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

/**
 * Remove default WooCommerce wrappers
 */
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

/**
 * Add custom wrappers
 */
function ganjeh_woocommerce_wrapper_before() {
    if (is_product()) {
        echo '<main id="main-content" class="pb-20">';
    }
}
add_action('woocommerce_before_main_content', 'ganjeh_woocommerce_wrapper_before');

function ganjeh_woocommerce_wrapper_after() {
    if (is_product()) {
        echo '</main>';
    }
}
add_action('woocommerce_after_main_content', 'ganjeh_woocommerce_wrapper_after');

/**
 * Remove sidebar from shop pages
 */
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

/**
 * Customize products per page
 */
function ganjeh_products_per_page() {
    return 12;
}
add_filter('loop_shop_per_page', 'ganjeh_products_per_page');

/**
 * Customize product columns
 */
function ganjeh_loop_columns() {
    return 2;
}
add_filter('loop_shop_columns', 'ganjeh_loop_columns');

/**
 * Remove default product title
 */
remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);

/**
 * Remove default product thumbnail
 */
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);

/**
 * Remove default product price
 */
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);

/**
 * Remove default add to cart button
 */
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);

/**
 * Remove default product link wrappers
 */
remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);

/**
 * Remove sale flash from loop
 */
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);

/**
 * Custom product loop
 */
function ganjeh_custom_product_loop() {
    get_template_part('template-parts/components/product-card');
}
add_action('woocommerce_shop_loop_item_title', 'ganjeh_custom_product_loop', 10);

/**
 * Remove breadcrumbs (we'll add custom ones)
 */
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

/**
 * Remove result count and ordering
 */
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

/**
 * Custom archive header
 */
function ganjeh_shop_header() {
    if (is_shop() || is_product_category() || is_product_tag()) {
        ?>
        <div class="shop-header px-4 py-4 bg-white sticky top-0 z-30 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <!-- Back Button & Title -->
                <div class="flex items-center gap-3">
                    <a href="javascript:history.back()" class="p-2 -mr-2 text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <h1 class="text-lg font-bold text-secondary">
                        <?php
                        if (is_shop()) {
                            echo get_the_title(wc_get_page_id('shop'));
                        } elseif (is_product_category()) {
                            single_term_title();
                        } elseif (is_product_tag()) {
                            single_term_title();
                        }
                        ?>
                    </h1>
                </div>

                <!-- Filter Button -->
                <button
                    type="button"
                    class="p-2 text-gray-600"
                    @click="$dispatch('open-filters')"
                    aria-label="<?php _e('فیلترها', 'ganjeh'); ?>"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </button>
            </div>

            <!-- Category Pills (for shop page) -->
            <?php if (is_shop()) : ?>
                <?php
                $categories = get_terms([
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => true,
                    'parent'     => 0,
                    'number'     => 10,
                ]);

                if ($categories && !is_wp_error($categories)) :
                ?>
                    <div class="mt-3 -mx-4 px-4 overflow-x-auto scrollbar-hide">
                        <div class="flex gap-2" style="width: max-content;">
                            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="px-4 py-2 bg-primary text-white text-sm rounded-full whitespace-nowrap">
                                <?php _e('همه', 'ganjeh'); ?>
                            </a>
                            <?php foreach ($categories as $category) : ?>
                                <a href="<?php echo get_term_link($category); ?>" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-full whitespace-nowrap hover:bg-gray-200 transition-colors">
                                    <?php echo esc_html($category->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
add_action('woocommerce_before_shop_loop', 'ganjeh_shop_header', 5);

/**
 * Customize single product page
 */
// Remove tabs
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);

// Remove related products
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);

// Remove upsells
remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);

/**
 * Change number of related products
 */
function ganjeh_related_products_args($args) {
    $args['posts_per_page'] = 4;
    $args['columns'] = 2;
    return $args;
}
add_filter('woocommerce_output_related_products_args', 'ganjeh_related_products_args');

/**
 * Empty cart message
 */
function ganjeh_empty_cart_message() {
    ?>
    <div class="text-center py-12">
        <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <h2 class="text-lg font-bold text-gray-700 mb-2"><?php _e('سبد خرید شما خالی است', 'ganjeh'); ?></h2>
        <p class="text-gray-500 text-sm mb-6"><?php _e('محصولات مورد نظر خود را به سبد اضافه کنید', 'ganjeh'); ?></p>
        <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="inline-flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-xl font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <?php _e('مشاهده فروشگاه', 'ganjeh'); ?>
        </a>
    </div>
    <?php
}
remove_action('woocommerce_cart_is_empty', 'wc_empty_cart_message', 10);
add_action('woocommerce_cart_is_empty', 'ganjeh_empty_cart_message', 10);

/**
 * Format price in Persian
 */
function ganjeh_format_price($formatted_price, $price, $args) {
    // Convert numbers to Persian
    $persian_numbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english_numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $formatted_price = str_replace($english_numbers, $persian_numbers, $formatted_price);

    return $formatted_price;
}
add_filter('woocommerce_price_format', function() {
    return '%2$s %1$s';
});

/**
 * Change currency symbol to تومان
 */
function ganjeh_currency_symbol($symbol, $currency) {
    if ($currency === 'IRR' || $currency === 'IRT') {
        return 'تومان';
    }
    return $symbol;
}
add_filter('woocommerce_currency_symbol', 'ganjeh_currency_symbol', 10, 2);

/**
 * Add IRT currency
 */
function ganjeh_add_irt_currency($currencies) {
    $currencies['IRT'] = __('تومان ایران', 'ganjeh');
    return $currencies;
}
add_filter('woocommerce_currencies', 'ganjeh_add_irt_currency');

function ganjeh_add_irt_currency_symbol($symbols) {
    $symbols['IRT'] = 'تومان';
    return $symbols;
}
add_filter('woocommerce_currency_symbols', 'ganjeh_add_irt_currency_symbol');

/**
 * Filter products by stock status tab (instock / outofstock)
 * Only on shop/category archive pages
 */
function ganjeh_filter_by_stock_tab($query) {
    if (!is_shop() && !is_product_category()) {
        return;
    }
    if (is_search()) {
        return;
    }
    // Skip stock filter when searching in shop
    if (!empty($_GET['product_search'])) {
        return;
    }
    $stock = isset($_GET['stock_filter']) ? sanitize_text_field($_GET['stock_filter']) : 'instock';
    $query->set('meta_query', [[
        'key'   => '_stock_status',
        'value' => $stock === 'outofstock' ? 'outofstock' : 'instock',
    ]]);
    $query->set('posts_per_page', -1);
}
add_action('woocommerce_product_query', 'ganjeh_filter_by_stock_tab');

/**
 * Filter products by category and attribute filters (brand, scent, etc.)
 */
function ganjeh_filter_by_attributes($query) {
    if (!is_shop() && !is_product_category()) {
        return;
    }
    if (is_search() || !empty($_GET['product_search'])) {
        return;
    }

    $tax_query = $query->get('tax_query');
    if (!is_array($tax_query)) {
        $tax_query = [];
    }

    // Category filter
    if (!empty($_GET['filter_cat'])) {
        $cats = array_map('sanitize_text_field', explode(',', $_GET['filter_cat']));
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $cats,
            'operator' => 'IN',
        ];
    }

    // WooCommerce Attribute filters (brand, scent, etc.)
    if (function_exists('wc_get_attribute_taxonomies')) {
        $wc_attributes = wc_get_attribute_taxonomies();
        if ($wc_attributes) {
            foreach ($wc_attributes as $attribute) {
                $param = 'filter_' . $attribute->attribute_name;
                if (!empty($_GET[$param])) {
                    $terms = array_map('sanitize_text_field', explode(',', $_GET[$param]));
                    $tax_query[] = [
                        'taxonomy' => 'pa_' . $attribute->attribute_name,
                        'field'    => 'slug',
                        'terms'    => $terms,
                        'operator' => 'IN',
                    ];
                }
            }
        }
    }

    if (count($tax_query) > 0) {
        $tax_query['relation'] = 'AND';
        $query->set('tax_query', $tax_query);
    }
}
add_action('woocommerce_product_query', 'ganjeh_filter_by_attributes', 15);

/**
 * Filter products by search term on shop page (?product_search=...)
 * Only searches in product title (not content/excerpt/meta)
 * Orders in-stock products first
 */
function ganjeh_shop_product_search($query) {
    if (!is_shop() || empty($_GET['product_search'])) {
        return;
    }
    $search_term = sanitize_text_field($_GET['product_search']);
    // Use custom title search instead of WP's default 's' (which searches content too)
    $query->set('_ganjeh_title_search', $search_term);
    $query->set('posts_per_page', 40);
    // Join stock status meta for ordering
    $query->set('meta_key', '_stock_status');
    $query->set('orderby', 'meta_value');
    $query->set('order', 'ASC'); // 'instock' comes before 'outofstock' alphabetically
}
add_action('woocommerce_product_query', 'ganjeh_shop_product_search');

/**
 * Custom WHERE clause for title-only product search
 */
function ganjeh_product_title_search_where($where, $query) {
    if ($term = $query->get('_ganjeh_title_search')) {
        global $wpdb;
        $like = '%' . $wpdb->esc_like($term) . '%';
        $where .= $wpdb->prepare(" AND {$wpdb->posts}.post_title LIKE %s", $like);
    }
    return $where;
}
add_filter('posts_where', 'ganjeh_product_title_search_where', 10, 2);

/**
 * Redirect native WP product search to shop page with product_search param
 */
function ganjeh_redirect_product_search() {
    if (is_search() && isset($_GET['post_type']) && $_GET['post_type'] === 'product') {
        $search_term = get_search_query();
        if ($search_term) {
            $shop_url = add_query_arg('product_search', urlencode($search_term), wc_get_page_permalink('shop'));
            wp_redirect($shop_url, 301);
            exit;
        }
    }
}
add_action('template_redirect', 'ganjeh_redirect_product_search');

/**
 * Empty cart after successful order placement (thank you page)
 */
function ganjeh_empty_cart_on_thankyou($order_id) {
    if ($order_id && WC()->cart && !WC()->cart->is_empty()) {
        WC()->cart->empty_cart();
    }
}
add_action('woocommerce_thankyou', 'ganjeh_empty_cart_on_thankyou', 1);

/**
 * Set WooCommerce session expiration to 24 hours
 * Cart will be automatically cleared after 24 hours of inactivity
 */
add_filter('wc_session_expiring', function() {
    return 23 * HOUR_IN_SECONDS; // 23 hours warning
});
add_filter('wc_session_expiration', function() {
    return 24 * HOUR_IN_SECONDS; // 24 hours expiry
});
