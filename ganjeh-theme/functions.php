<?php
/**
 * Ganjeh Market Theme Functions
 *
 * @package Ganjeh
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Theme Constants
define('GANJEH_VERSION', '1.0.0');
define('GANJEH_DIR', get_template_directory());
define('GANJEH_URI', get_template_directory_uri());

/**
 * Theme Setup
 */
function ganjeh_setup() {
    // RTL Support
    load_theme_textdomain('ganjeh', GANJEH_DIR . '/languages');

    // Theme Support
    add_theme_support('automatic-feed-links');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    // WooCommerce Support
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    // Custom Logo
    add_theme_support('custom-logo', [
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    // Navigation Menus
    register_nav_menus([
        'primary'       => __('منوی اصلی', 'ganjeh'),
        'categories'    => __('دسته‌بندی‌ها', 'ganjeh'),
        'footer-menu-1' => __('فوتر - ستون اول', 'ganjeh'),
        'footer-menu-2' => __('فوتر - ستون دوم', 'ganjeh'),
    ]);

    // Image Sizes
    add_image_size('ganjeh-product-thumb', 300, 300, true);
    add_image_size('ganjeh-product-large', 600, 600, true);
    add_image_size('ganjeh-category-icon', 100, 100, true);
    add_image_size('ganjeh-slider', 800, 400, true);
}
add_action('after_setup_theme', 'ganjeh_setup');

/**
 * Custom WooCommerce Cart/Checkout Templates
 */
function ganjeh_woocommerce_template_redirect($template) {
    // Skip for single products
    if (is_singular('product')) {
        return $template;
    }

    // Cart page
    if (function_exists('is_cart') && is_cart()) {
        $custom_template = locate_template('page-cart.php');
        if ($custom_template) {
            return $custom_template;
        }
    }

    // Checkout page (not order received)
    if (function_exists('is_checkout') && is_checkout() && !is_order_received_page()) {
        $custom_template = locate_template('page-checkout.php');
        if ($custom_template) {
            return $custom_template;
        }
    }

    // My Account page
    if (function_exists('is_account_page') && is_account_page()) {
        $custom_template = locate_template('page-my-account.php');
        if ($custom_template) {
            return $custom_template;
        }
    }

    return $template;
}
add_filter('template_include', 'ganjeh_woocommerce_template_redirect', 99);

/**
 * Enqueue Scripts and Styles
 */
function ganjeh_scripts() {
    // Dequeue default WooCommerce styles for custom styling
    wp_dequeue_style('woocommerce-general');
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');

    // Main Stylesheet (Tailwind compiled)
    wp_enqueue_style(
        'ganjeh-style',
        GANJEH_URI . '/assets/css/style.min.css',
        [],
        GANJEH_VERSION
    );

    // Vazirmatn Font - Load from local
    wp_enqueue_style(
        'ganjeh-font',
        GANJEH_URI . '/assets/fonts/vazirmatn/font.css',
        [],
        GANJEH_VERSION
    );

    // Swiper CSS (minimal)
    wp_enqueue_style(
        'swiper',
        GANJEH_URI . '/assets/css/swiper.min.css',
        [],
        '11.0.5'
    );

    // Swiper JS - Load first in footer
    wp_enqueue_script(
        'swiper',
        GANJEH_URI . '/assets/js/swiper.min.js',
        [],
        '11.0.5',
        true
    );

    // Alpine.js Collapse Plugin (must load before Alpine)
    wp_enqueue_script(
        'alpine-collapse',
        GANJEH_URI . '/assets/js/alpine-collapse.min.js',
        [],
        '3.14.3',
        true
    );

    // Alpine.js - Depends on Collapse plugin
    wp_enqueue_script(
        'alpine',
        GANJEH_URI . '/assets/js/alpine.min.js',
        ['alpine-collapse'],
        '3.14.3',
        true
    );

    // Main JS - Depends on Alpine and Swiper
    wp_enqueue_script(
        'ganjeh-main',
        GANJEH_URI . '/assets/js/main.js',
        ['alpine', 'swiper'],
        GANJEH_VERSION,
        true
    );

    // Localize script for AJAX
    wp_localize_script('ganjeh-main', 'ganjeh', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('ganjeh_nonce'),
        'cart_url' => wc_get_cart_url(),
        'i18n'     => [
            'added_to_cart' => __('به سبد اضافه شد', 'ganjeh'),
            'view_cart'     => __('مشاهده سبد', 'ganjeh'),
            'error'         => __('خطایی رخ داد', 'ganjeh'),
        ],
    ]);
}
add_action('wp_enqueue_scripts', 'ganjeh_scripts', 20);

/**
 * Remove unnecessary scripts for speed
 */
function ganjeh_remove_scripts() {
    // Remove WordPress emoji
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');

    // Remove Gutenberg block styles if not using blocks
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wc-blocks-style');

    // Keep jQuery for WooCommerce compatibility
    // jQuery migrate can be removed if not needed
}
add_action('wp_enqueue_scripts', 'ganjeh_remove_scripts', 100);

/**
 * Add preload for critical resources
 */
function ganjeh_preload_resources() {
    ?>
    <link rel="preload" href="<?php echo GANJEH_URI; ?>/assets/fonts/vazirmatn/Vazirmatn-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preconnect" href="<?php echo GANJEH_URI; ?>" crossorigin>
    <?php
}
add_action('wp_head', 'ganjeh_preload_resources', 1);

/**
 * Include theme files
 */
require_once GANJEH_DIR . '/inc/theme-setup.php';
require_once GANJEH_DIR . '/inc/woocommerce.php';
require_once GANJEH_DIR . '/inc/customizer.php';
require_once GANJEH_DIR . '/inc/ajax-handlers.php';
require_once GANJEH_DIR . '/inc/module-loader.php';
require_once GANJEH_DIR . '/inc/banner-settings.php';
require_once GANJEH_DIR . '/inc/category-settings.php';
require_once GANJEH_DIR . '/inc/product-sections-settings.php';
require_once GANJEH_DIR . '/inc/promo-banners-settings.php';
require_once GANJEH_DIR . '/inc/ajax-search.php';
require_once GANJEH_DIR . '/inc/category-slider.php';
require_once GANJEH_DIR . '/inc/sms-settings.php';
require_once GANJEH_DIR . '/inc/auth-handlers.php';

/**
 * Register Widget Areas
 */
function ganjeh_widgets_init() {
    register_sidebar([
        'name'          => __('سایدبار فروشگاه', 'ganjeh'),
        'id'            => 'shop-sidebar',
        'description'   => __('ویجت‌های فروشگاه', 'ganjeh'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
}
add_action('widgets_init', 'ganjeh_widgets_init');

/**
 * AJAX Add to Cart
 */
function ganjeh_ajax_add_to_cart() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    $product_id = absint($_POST['product_id']);
    $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
    $quantity = absint($_POST['quantity'] ?? 1);

    if (!$product_id) {
        wp_send_json_error(['message' => __('محصول نامعتبر', 'ganjeh')]);
    }

    // For variable products
    if ($variation_id) {
        $variation = wc_get_product($variation_id);
        if (!$variation) {
            wp_send_json_error(['message' => __('تنوع محصول نامعتبر', 'ganjeh')]);
        }

        // Get variation attributes in correct format for add_to_cart
        $variation_attributes = $variation->get_variation_attributes();
        $variation_data = [];
        foreach ($variation_attributes as $key => $value) {
            // Convert 'attribute_pa_xxx' format or keep as is
            if (strpos($key, 'attribute_') === 0) {
                $variation_data[$key] = $value;
            } else {
                $variation_data['attribute_' . $key] = $value;
            }
        }

        $added = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_data);
    } else {
        $added = WC()->cart->add_to_cart($product_id, $quantity);
    }

    if ($added) {
        wp_send_json_success([
            'message'    => __('به سبد خرید اضافه شد', 'ganjeh'),
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_total' => WC()->cart->get_cart_total(),
            'cart_url'   => wc_get_cart_url(),
        ]);
    } else {
        // Get WooCommerce error message if available
        $error = wc_get_notices('error');
        $error_msg = !empty($error) ? strip_tags($error[0]['notice']) : __('خطا در افزودن به سبد', 'ganjeh');
        wc_clear_notices();
        wp_send_json_error(['message' => $error_msg]);
    }
}
add_action('wp_ajax_ganjeh_add_to_cart', 'ganjeh_ajax_add_to_cart');
add_action('wp_ajax_nopriv_ganjeh_add_to_cart', 'ganjeh_ajax_add_to_cart');

/**
 * AJAX Update Cart Quantity
 */
function ganjeh_ajax_update_cart() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    $cart_key = sanitize_text_field($_POST['cart_key']);
    $quantity = absint($_POST['quantity']);

    if (!$cart_key) {
        wp_send_json_error(['message' => __('خطا در بروزرسانی', 'ganjeh')]);
    }

    WC()->cart->set_quantity($cart_key, $quantity);

    wp_send_json_success([
        'message'    => __('سبد بروزرسانی شد', 'ganjeh'),
        'cart_count' => WC()->cart->get_cart_contents_count(),
    ]);
}
add_action('wp_ajax_ganjeh_update_cart', 'ganjeh_ajax_update_cart');
add_action('wp_ajax_nopriv_ganjeh_update_cart', 'ganjeh_ajax_update_cart');

/**
 * AJAX Remove Cart Item
 */
function ganjeh_ajax_remove_cart_item() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    $cart_key = sanitize_text_field($_POST['cart_key']);

    if (!$cart_key) {
        wp_send_json_error(['message' => __('خطا در حذف', 'ganjeh')]);
    }

    WC()->cart->remove_cart_item($cart_key);

    wp_send_json_success([
        'message'    => __('محصول حذف شد', 'ganjeh'),
        'cart_count' => WC()->cart->get_cart_contents_count(),
    ]);
}
add_action('wp_ajax_ganjeh_remove_cart_item', 'ganjeh_ajax_remove_cart_item');
add_action('wp_ajax_nopriv_ganjeh_remove_cart_item', 'ganjeh_ajax_remove_cart_item');

/**
 * AJAX Apply Coupon
 */
function ganjeh_ajax_apply_coupon() {
    $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';

    if (empty($coupon_code)) {
        wp_send_json_error(['message' => __('لطفاً کد تخفیف را وارد کنید', 'ganjeh')]);
    }

    // Check if coupon is already applied
    if (WC()->cart->has_discount($coupon_code)) {
        wp_send_json_error(['message' => __('این کد تخفیف قبلاً اعمال شده است', 'ganjeh')]);
    }

    // Try to apply the coupon
    $result = WC()->cart->apply_coupon($coupon_code);

    if ($result) {
        wp_send_json_success([
            'message'    => __('کد تخفیف با موفقیت اعمال شد', 'ganjeh'),
            'discount'   => WC()->cart->get_discount_total(),
            'cart_total' => WC()->cart->get_total(),
        ]);
    } else {
        // Get WooCommerce error messages
        $error_message = __('کد تخفیف نامعتبر است', 'ganjeh');
        wp_send_json_error(['message' => $error_message]);
    }
}
add_action('wp_ajax_ganjeh_apply_coupon', 'ganjeh_ajax_apply_coupon');
add_action('wp_ajax_nopriv_ganjeh_apply_coupon', 'ganjeh_ajax_apply_coupon');

/**
 * AJAX Remove Coupon
 */
function ganjeh_ajax_remove_coupon() {
    $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';

    if (empty($coupon_code)) {
        wp_send_json_error(['message' => __('کد تخفیف نامعتبر', 'ganjeh')]);
    }

    WC()->cart->remove_coupon($coupon_code);

    wp_send_json_success([
        'message'    => __('کد تخفیف حذف شد', 'ganjeh'),
        'cart_total' => WC()->cart->get_total(),
    ]);
}
add_action('wp_ajax_ganjeh_remove_coupon', 'ganjeh_ajax_remove_coupon');
add_action('wp_ajax_nopriv_ganjeh_remove_coupon', 'ganjeh_ajax_remove_coupon');

/**
 * AJAX handler for submitting product reviews
 */
function ganjeh_ajax_submit_review() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'ganjeh_nonce')) {
        wp_send_json_error(['message' => __('خطای امنیتی', 'ganjeh')]);
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('برای ثبت نظر ابتدا وارد شوید', 'ganjeh')]);
    }

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $rating = isset($_POST['rating']) ? absint($_POST['rating']) : 5;
    $content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : '';

    if (!$product_id || !$content) {
        wp_send_json_error(['message' => __('لطفاً متن نظر را وارد کنید', 'ganjeh')]);
    }

    // Ensure rating is between 1 and 5
    $rating = max(1, min(5, $rating));

    $user = wp_get_current_user();
    global $wpdb;

    // Direct database insert
    $result = $wpdb->insert(
        $wpdb->comments,
        [
            'comment_post_ID'      => $product_id,
            'comment_author'       => $user->display_name ?: $user->user_login,
            'comment_author_email' => $user->user_email,
            'comment_author_url'   => '',
            'comment_author_IP'    => $_SERVER['REMOTE_ADDR'] ?? '',
            'comment_date'         => current_time('mysql'),
            'comment_date_gmt'     => current_time('mysql', 1),
            'comment_content'      => $content,
            'comment_karma'        => 0,
            'comment_approved'     => '1',
            'comment_agent'        => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 254),
            'comment_type'         => 'review',
            'comment_parent'       => 0,
            'user_id'              => $user->ID,
        ],
        ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%d']
    );

    if ($result) {
        $comment_id = $wpdb->insert_id;

        // Add rating meta
        add_comment_meta($comment_id, 'rating', $rating);

        // Clear comment cache
        clean_comment_cache($comment_id);

        // Update post comment count
        wp_update_comment_count($product_id);

        wp_send_json_success([
            'message' => __('نظر شما با موفقیت ثبت شد', 'ganjeh'),
            'comment_id' => $comment_id,
        ]);
    } else {
        wp_send_json_error([
            'message' => __('خطا در ثبت نظر', 'ganjeh'),
            'error' => $wpdb->last_error
        ]);
    }
}
add_action('wp_ajax_ganjeh_submit_review', 'ganjeh_ajax_submit_review');

/**
 * Get cart count fragment for AJAX update
 */
function ganjeh_cart_count_fragment($fragments) {
    $fragments['.ganjeh-cart-count'] = '<span class="ganjeh-cart-count">' . WC()->cart->get_cart_contents_count() . '</span>';
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'ganjeh_cart_count_fragment');

/**
 * Add delivery type field to product edit page
 */
function ganjeh_add_delivery_type_field() {
    woocommerce_wp_select([
        'id' => '_ganjeh_delivery_type',
        'label' => __('نوع تحویل', 'ganjeh'),
        'description' => __('نوع تحویل محصول را انتخاب کنید', 'ganjeh'),
        'desc_tip' => true,
        'options' => [
            '' => __('بدون نمایش', 'ganjeh'),
            'in_person' => __('تحویل حضوری', 'ganjeh'),
            'courier' => __('ارسال با پیک', 'ganjeh'),
            'post' => __('ارسال پستی', 'ganjeh'),
            'express' => __('ارسال فوری', 'ganjeh'),
        ],
    ]);
}
add_action('woocommerce_product_options_general_product_data', 'ganjeh_add_delivery_type_field');

/**
 * Save delivery type field
 */
function ganjeh_save_delivery_type_field($post_id) {
    $delivery_type = isset($_POST['_ganjeh_delivery_type']) ? sanitize_text_field($_POST['_ganjeh_delivery_type']) : '';
    update_post_meta($post_id, '_ganjeh_delivery_type', $delivery_type);
}
add_action('woocommerce_process_product_meta', 'ganjeh_save_delivery_type_field');

/**
 * Get user saved addresses
 */
function ganjeh_get_user_addresses($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    if (!$user_id) {
        return [];
    }
    $addresses = get_user_meta($user_id, 'ganjeh_saved_addresses', true);
    return is_array($addresses) ? $addresses : [];
}

/**
 * AJAX Save Address
 */
function ganjeh_ajax_save_address() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('لطفاً وارد شوید', 'ganjeh')]);
    }

    $user_id = get_current_user_id();
    $addresses = ganjeh_get_user_addresses($user_id);

    // Get address data
    $new_address = [
        'id'       => uniqid(),
        'title'    => sanitize_text_field($_POST['title'] ?? __('آدرس جدید', 'ganjeh')),
        'state'    => sanitize_text_field($_POST['state'] ?? ''),
        'city'     => sanitize_text_field($_POST['city'] ?? ''),
        'address'  => sanitize_textarea_field($_POST['address'] ?? ''),
        'postcode' => sanitize_text_field($_POST['postcode'] ?? ''),
    ];

    // Validate required fields
    if (empty($new_address['state']) || empty($new_address['city']) || empty($new_address['address']) || empty($new_address['postcode'])) {
        wp_send_json_error(['message' => __('لطفاً همه فیلدها را پر کنید', 'ganjeh')]);
    }

    // Add new address
    $addresses[] = $new_address;

    // Save to user meta
    update_user_meta($user_id, 'ganjeh_saved_addresses', $addresses);

    wp_send_json_success([
        'message'   => __('آدرس ذخیره شد', 'ganjeh'),
        'address'   => $new_address,
        'addresses' => $addresses,
    ]);
}
add_action('wp_ajax_ganjeh_save_address', 'ganjeh_ajax_save_address');

/**
 * AJAX Delete Address
 */
function ganjeh_ajax_delete_address() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('لطفاً وارد شوید', 'ganjeh')]);
    }

    $user_id = get_current_user_id();
    $address_id = sanitize_text_field($_POST['address_id'] ?? '');

    if (empty($address_id)) {
        wp_send_json_error(['message' => __('آدرس نامعتبر', 'ganjeh')]);
    }

    $addresses = ganjeh_get_user_addresses($user_id);

    // Remove address by ID
    $addresses = array_filter($addresses, function($addr) use ($address_id) {
        return $addr['id'] !== $address_id;
    });
    $addresses = array_values($addresses); // Re-index array

    // Save to user meta
    update_user_meta($user_id, 'ganjeh_saved_addresses', $addresses);

    wp_send_json_success([
        'message'   => __('آدرس حذف شد', 'ganjeh'),
        'addresses' => $addresses,
    ]);
}
add_action('wp_ajax_ganjeh_delete_address', 'ganjeh_ajax_delete_address');

/**
 * AJAX Get Addresses
 */
function ganjeh_ajax_get_addresses() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('لطفاً وارد شوید', 'ganjeh')]);
    }

    $addresses = ganjeh_get_user_addresses();

    wp_send_json_success([
        'addresses' => $addresses,
    ]);
}
add_action('wp_ajax_ganjeh_get_addresses', 'ganjeh_ajax_get_addresses');

/**
 * Handle zero-total orders - auto complete them
 */
function ganjeh_auto_complete_free_orders($order_id) {
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    if (!$order) return;

    // If order total is 0, auto-complete the order
    if ($order->get_total() == 0 && $order->get_status() !== 'completed') {
        $order->update_status('completed', __('سفارش رایگان - تکمیل خودکار', 'ganjeh'));
    }
}
add_action('woocommerce_thankyou', 'ganjeh_auto_complete_free_orders', 10, 1);

/**
 * Allow checkout without payment gateway for free orders
 */
function ganjeh_allow_free_checkout($available_gateways) {
    if (is_admin()) return $available_gateways;

    if (WC()->cart && WC()->cart->get_total('edit') == 0) {
        // If cart is free, we don't need any payment gateway
        return $available_gateways;
    }

    return $available_gateways;
}
add_filter('woocommerce_available_payment_gateways', 'ganjeh_allow_free_checkout');

/**
 * Set default order status for free orders to "processing"
 */
function ganjeh_free_order_status($status, $order_id) {
    $order = wc_get_order($order_id);
    if ($order && $order->get_total() == 0) {
        return 'completed';
    }
    return $status;
}
add_filter('woocommerce_payment_complete_order_status', 'ganjeh_free_order_status', 10, 2);

/**
 * AJAX Handler - Update User Account
 */
function ganjeh_ajax_update_account() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('لطفا وارد حساب کاربری شوید', 'ganjeh')]);
    }

    $user_id = get_current_user_id();
    $user = get_user_by('id', $user_id);

    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name = sanitize_text_field($_POST['last_name'] ?? '');
    $display_name = sanitize_text_field($_POST['display_name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $password_current = $_POST['password_current'] ?? '';
    $password_1 = $_POST['password_1'] ?? '';
    $password_2 = $_POST['password_2'] ?? '';

    // Validate email
    if (!is_email($email)) {
        wp_send_json_error(['message' => __('ایمیل وارد شده معتبر نیست', 'ganjeh')]);
    }

    // Check if email already exists for another user
    $existing_user = get_user_by('email', $email);
    if ($existing_user && $existing_user->ID !== $user_id) {
        wp_send_json_error(['message' => __('این ایمیل قبلا ثبت شده است', 'ganjeh')]);
    }

    // If changing password
    if (!empty($password_1)) {
        // Verify current password
        if (!wp_check_password($password_current, $user->user_pass, $user_id)) {
            wp_send_json_error(['message' => __('رمز عبور فعلی اشتباه است', 'ganjeh')]);
        }

        // Check password match
        if ($password_1 !== $password_2) {
            wp_send_json_error(['message' => __('رمز عبور جدید و تکرار آن یکسان نیستند', 'ganjeh')]);
        }

        // Update password
        wp_set_password($password_1, $user_id);
    }

    // Update user data
    $user_data = [
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'display_name' => $display_name ?: $first_name . ' ' . $last_name,
        'user_email' => $email,
    ];

    $result = wp_update_user($user_data);

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    }

    // Update billing info too
    update_user_meta($user_id, 'billing_first_name', $first_name);
    update_user_meta($user_id, 'billing_last_name', $last_name);
    update_user_meta($user_id, 'billing_email', $email);

    wp_send_json_success(['message' => __('اطلاعات با موفقیت ذخیره شد', 'ganjeh')]);
}
add_action('wp_ajax_ganjeh_update_account', 'ganjeh_ajax_update_account');

/**
 * Calculate reading time for blog posts
 */
function ganjeh_reading_time($content) {
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // Average 200 words per minute

    if ($reading_time < 1) {
        $reading_time = 1;
    }

    return sprintf(__('%d دقیقه', 'ganjeh'), $reading_time);
}

/**
 * Add Payment Link Meta Box to Order Edit Page
 */
function ganjeh_add_payment_link_meta_box() {
    // Determine screen for HPOS compatibility
    $screen = 'shop_order';

    if (function_exists('wc_get_container')) {
        try {
            $controller = wc_get_container()->get(\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class);
            if ($controller && method_exists($controller, 'custom_orders_table_usage_is_enabled') && $controller->custom_orders_table_usage_is_enabled()) {
                $screen = wc_get_page_screen_id('shop-order');
            }
        } catch (Exception $e) {
            // Fallback to legacy
        }
    }

    add_meta_box(
        'ganjeh_payment_link',
        __('لینک پرداخت', 'ganjeh'),
        'ganjeh_payment_link_meta_box_content',
        $screen,
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'ganjeh_add_payment_link_meta_box');

/**
 * Payment Link Meta Box Content
 */
function ganjeh_payment_link_meta_box_content($post_or_order) {
    // Support both HPOS and legacy
    if ($post_or_order instanceof WC_Order) {
        $order = $post_or_order;
    } else {
        $order = wc_get_order($post_or_order->ID);
    }

    if (!$order) {
        echo '<p>' . __('سفارش یافت نشد', 'ganjeh') . '</p>';
        return;
    }

    $order_status = $order->get_status();

    // Direct payment URL - goes straight to payment gateway
    $payment_url = add_query_arg([
        'direct_pay' => '1',
        'order' => $order->get_id(),
        'key' => $order->get_order_key(),
    ], home_url('/'));

    // Get customer phone for WhatsApp
    $customer_phone = $order->get_billing_phone();
    $customer_phone_formatted = preg_replace('/^0/', '98', preg_replace('/[^0-9]/', '', $customer_phone));

    // Only show for unpaid orders
    $unpaid_statuses = ['pending', 'failed', 'on-hold'];

    if (in_array($order_status, $unpaid_statuses)) {
        $order_total = strip_tags(wc_price($order->get_total()));
        $sms_text = "سفارش شماره {$order->get_order_number()} به مبلغ {$order_total} آماده پرداخت است.\nلینک پرداخت:\n{$payment_url}";
        $nonce = wp_create_nonce('ganjeh_payment_sms_nonce');
        ?>
        <div class="ganjeh-payment-link-box">
            <p style="margin-bottom: 10px;">
                <strong><?php _e('وضعیت:', 'ganjeh'); ?></strong>
                <span style="color: #d63638;"><?php _e('پرداخت نشده', 'ganjeh'); ?></span>
            </p>

            <div style="background: #f0f0f1; padding: 10px; border-radius: 4px;">
                <input type="text"
                       id="ganjeh-payment-url"
                       value="<?php echo esc_url($payment_url); ?>"
                       readonly
                       style="width: 100%; direction: ltr; font-size: 11px; margin-bottom: 8px;"
                       onclick="this.select();">

                <button type="button"
                        class="button button-primary"
                        onclick="ganjehCopyPaymentLink()"
                        style="width: 100%; margin-bottom: 10px;">
                    <?php _e('کپی لینک پرداخت', 'ganjeh'); ?>
                </button>

                <div style="border-top: 1px solid #ddd; padding-top: 10px;">
                    <label style="font-size: 11px; font-weight: bold; display: block; margin-bottom: 5px;">
                        <?php _e('ارسال پیامک با کاوه‌نگار:', 'ganjeh'); ?>
                    </label>
                    <input type="text"
                           id="ganjeh-sms-phone"
                           value="<?php echo esc_attr($customer_phone); ?>"
                           placeholder="09123456789"
                           dir="ltr"
                           style="width: 100%; margin-bottom: 8px;">

                    <button type="button"
                            id="ganjeh-send-sms-btn"
                            class="button"
                            onclick="ganjehSendSMS()"
                            style="width: 100%; background: #0073aa; color: white; border-color: #0073aa;">
                        <?php _e('ارسال پیامک', 'ganjeh'); ?>
                    </button>
                    <div id="ganjeh-sms-status" style="margin-top: 8px; font-size: 12px;"></div>
                </div>

                <div style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px;">
                    <button type="button"
                            class="button"
                            onclick="ganjehCopyMessage()"
                            style="width: 100%;">
                        <?php _e('کپی متن پیام', 'ganjeh'); ?>
                    </button>
                </div>
            </div>
        </div>

        <script>
        function ganjehCopyPaymentLink() {
            var copyText = document.getElementById("ganjeh-payment-url");
            copyText.select();
            navigator.clipboard.writeText(copyText.value).then(function() {
                alert('<?php _e('لینک کپی شد!', 'ganjeh'); ?>');
            });
        }

        function ganjehCopyMessage() {
            var message = <?php echo json_encode($sms_text); ?>;
            navigator.clipboard.writeText(message).then(function() {
                alert('<?php _e('متن پیام کپی شد!', 'ganjeh'); ?>');
            });
        }

        function ganjehSendSMS() {
            var phone = document.getElementById('ganjeh-sms-phone').value;
            var btn = document.getElementById('ganjeh-send-sms-btn');
            var statusDiv = document.getElementById('ganjeh-sms-status');

            if (!phone || phone.length < 10) {
                statusDiv.innerHTML = '<span style="color: #d63638;"><?php _e('لطفاً شماره موبایل را وارد کنید', 'ganjeh'); ?></span>';
                return;
            }

            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '<?php _e('در حال ارسال...', 'ganjeh'); ?>';
            statusDiv.innerHTML = '';

            // Send AJAX request
            var formData = new FormData();
            formData.append('action', 'ganjeh_send_payment_sms');
            formData.append('nonce', '<?php echo $nonce; ?>');
            formData.append('order_id', '<?php echo $order->get_id(); ?>');
            formData.append('phone', phone);

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<?php _e('ارسال پیامک', 'ganjeh'); ?>';

                if (data.success) {
                    statusDiv.innerHTML = '<span style="color: #00a32a;">✓ ' + data.data.message + '</span>';
                } else {
                    statusDiv.innerHTML = '<span style="color: #d63638;">✗ ' + data.data.message + '</span>';
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = '<?php _e('ارسال پیامک', 'ganjeh'); ?>';
                statusDiv.innerHTML = '<span style="color: #d63638;"><?php _e('خطا در ارتباط با سرور', 'ganjeh'); ?></span>';
            });
        }
        </script>
        <?php
    } else {
        ?>
        <p style="color: #00a32a;">
            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
            <?php _e('این سفارش پرداخت شده است.', 'ganjeh'); ?>
        </p>
        <?php
    }
}

/**
 * Direct Payment Handler - Redirects directly to payment gateway
 */
function ganjeh_handle_direct_pay() {
    // Check for direct pay parameters
    if (!isset($_GET['direct_pay']) || $_GET['direct_pay'] !== '1') {
        return;
    }

    if (!isset($_GET['order']) || !isset($_GET['key'])) {
        return;
    }

    // Make sure WooCommerce is loaded
    if (!function_exists('wc_get_order') || !function_exists('WC')) {
        return;
    }

    $order_id = absint($_GET['order']);
    $order_key = sanitize_text_field($_GET['key']);

    if (!$order_id || !$order_key) {
        wp_die(__('پارامترهای نامعتبر', 'ganjeh'), __('خطا', 'ganjeh'), ['response' => 400]);
    }

    $order = wc_get_order($order_id);

    if (!$order || $order->get_order_key() !== $order_key) {
        wp_die(__('سفارش نامعتبر است', 'ganjeh'), __('خطا', 'ganjeh'), ['response' => 403]);
    }

    // Check if order needs payment
    if (!$order->needs_payment()) {
        wp_redirect($order->get_checkout_order_received_url());
        exit;
    }

    // Get available payment gateways
    if (!WC()->payment_gateways) {
        wp_die(__('درگاه پرداخت در دسترس نیست', 'ganjeh'), __('خطا', 'ganjeh'));
    }

    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

    if (empty($available_gateways)) {
        wp_die(__('هیچ درگاه پرداختی فعال نیست', 'ganjeh'), __('خطا', 'ganjeh'));
    }

    // Get the first available gateway (or the one set in order)
    $payment_method = $order->get_payment_method();
    if (!$payment_method || !isset($available_gateways[$payment_method])) {
        $payment_method = key($available_gateways);
    }

    // Set the payment method on order
    $order->set_payment_method($available_gateways[$payment_method]);
    $order->save();

    // Process the payment
    $gateway = $available_gateways[$payment_method];
    $result = $gateway->process_payment($order_id);

    if (isset($result['result']) && $result['result'] === 'success') {
        // Redirect to payment gateway
        wp_redirect($result['redirect']);
        exit;
    } else {
        // Show error
        $error_message = isset($result['messages']) ? $result['messages'] : __('خطا در اتصال به درگاه پرداخت', 'ganjeh');
        wp_die($error_message, __('خطا در پرداخت', 'ganjeh'));
    }
}
add_action('template_redirect', 'ganjeh_handle_direct_pay', 1);

/**
 * Enable Cash on Delivery (COD) payment gateway
 */
function ganjeh_enable_cod_gateway($gateways) {
    // Make sure COD is available
    if (!isset($gateways['cod'])) {
        $gateways['cod'] = 'WC_Gateway_COD';
    }
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'ganjeh_enable_cod_gateway');

/**
 * Customize COD gateway settings
 */
function ganjeh_customize_cod_gateway($settings) {
    // Enable COD by default
    update_option('woocommerce_cod_settings', [
        'enabled' => 'yes',
        'title' => 'پرداخت حضوری',
        'description' => 'پرداخت در هنگام تحویل سفارش',
        'instructions' => 'مبلغ سفارش را در هنگام تحویل به پیک پرداخت کنید.',
        'enable_for_methods' => [],
        'enable_for_virtual' => 'no',
    ]);
}
add_action('after_switch_theme', 'ganjeh_customize_cod_gateway');

/**
 * Ensure COD is enabled on init if not set
 */
function ganjeh_ensure_cod_enabled() {
    $cod_settings = get_option('woocommerce_cod_settings', []);
    if (empty($cod_settings) || !isset($cod_settings['enabled']) || $cod_settings['enabled'] !== 'yes') {
        update_option('woocommerce_cod_settings', [
            'enabled' => 'yes',
            'title' => 'پرداخت حضوری',
            'description' => 'پرداخت در هنگام تحویل سفارش',
            'instructions' => 'مبلغ سفارش را در هنگام تحویل به پیک پرداخت کنید.',
            'enable_for_methods' => [],
            'enable_for_virtual' => 'no',
        ]);
    }
}
add_action('init', 'ganjeh_ensure_cod_enabled');

/**
 * Add Payment Method column to WooCommerce Orders list in admin
 */
function ganjeh_add_payment_method_column($columns) {
    $new_columns = [];
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        // Add payment method column after order_status
        if ($key === 'order_status') {
            $new_columns['payment_method'] = __('روش پرداخت', 'ganjeh');
        }
    }
    return $new_columns;
}
add_filter('manage_edit-shop_order_columns', 'ganjeh_add_payment_method_column', 20);
add_filter('manage_woocommerce_page_wc-orders_columns', 'ganjeh_add_payment_method_column', 20);

/**
 * Display Payment Method in Orders list column
 */
function ganjeh_display_payment_method_column($column, $post_id_or_order) {
    if ($column === 'payment_method') {
        // Handle both HPOS and legacy order storage
        if (is_object($post_id_or_order)) {
            $order = $post_id_or_order;
        } else {
            $order = wc_get_order($post_id_or_order);
        }

        if ($order) {
            $payment_method = $order->get_payment_method();
            $payment_title = $order->get_payment_method_title();

            if ($payment_method === 'cod') {
                echo '<mark class="order-status status-on-hold tips" style="background: #f8dda7; color: #94660c;"><span>' . esc_html($payment_title) . '</span></mark>';
            } elseif ($payment_title) {
                echo '<mark class="order-status status-processing tips" style="background: #c6e1c6; color: #5b841b;"><span>' . esc_html($payment_title) . '</span></mark>';
            } else {
                echo '<span class="na">–</span>';
            }
        }
    }
}
add_action('manage_shop_order_posts_custom_column', 'ganjeh_display_payment_method_column', 10, 2);
add_action('manage_woocommerce_page_wc-orders_custom_column', 'ganjeh_display_payment_method_column', 10, 2);

/**
 * Make Payment Method column sortable
 */
function ganjeh_payment_method_column_sortable($columns) {
    $columns['payment_method'] = 'payment_method';
    return $columns;
}
add_filter('manage_edit-shop_order_sortable_columns', 'ganjeh_payment_method_column_sortable');

/**
 * Add custom CSS for payment method column in admin
 */
function ganjeh_admin_order_styles() {
    $screen = get_current_screen();
    if ($screen && (strpos($screen->id, 'shop_order') !== false || strpos($screen->id, 'wc-orders') !== false)) {
        ?>
        <style>
            .column-payment_method { width: 120px; }
            .column-payment_method mark { display: inline-block; padding: 0 8px; border-radius: 3px; font-weight: 600; font-size: 12px; line-height: 24px; }
        </style>
        <?php
    }
}
add_action('admin_head', 'ganjeh_admin_order_styles');
