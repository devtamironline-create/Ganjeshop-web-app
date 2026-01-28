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
        'primary'    => __('منوی اصلی', 'ganjeh'),
        'categories' => __('دسته‌بندی‌ها', 'ganjeh'),
        'footer'     => __('منوی فوتر', 'ganjeh'),
    ]);

    // Image Sizes
    add_image_size('ganjeh-product-thumb', 300, 300, true);
    add_image_size('ganjeh-product-large', 600, 600, true);
    add_image_size('ganjeh-category-icon', 100, 100, true);
    add_image_size('ganjeh-slider', 800, 400, true);
}
add_action('after_setup_theme', 'ganjeh_setup');

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

    // Alpine.js - Defer for performance
    wp_enqueue_script(
        'alpine',
        GANJEH_URI . '/assets/js/alpine.min.js',
        [],
        '3.14.3',
        ['strategy' => 'defer']
    );

    // Swiper JS
    wp_enqueue_script(
        'swiper',
        GANJEH_URI . '/assets/js/swiper.min.js',
        [],
        '11.0.5',
        true
    );

    // Main JS
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

    // Remove jQuery migrate
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', '', [], '', true);
    }
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
    $quantity = absint($_POST['quantity'] ?? 1);

    if (!$product_id) {
        wp_send_json_error(['message' => __('محصول نامعتبر', 'ganjeh')]);
    }

    $added = WC()->cart->add_to_cart($product_id, $quantity);

    if ($added) {
        wp_send_json_success([
            'message'    => __('به سبد اضافه شد', 'ganjeh'),
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_total' => WC()->cart->get_cart_total(),
        ]);
    } else {
        wp_send_json_error(['message' => __('خطا در افزودن به سبد', 'ganjeh')]);
    }
}
add_action('wp_ajax_ganjeh_add_to_cart', 'ganjeh_ajax_add_to_cart');
add_action('wp_ajax_nopriv_ganjeh_add_to_cart', 'ganjeh_ajax_add_to_cart');

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
