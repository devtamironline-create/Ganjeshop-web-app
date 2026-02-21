<?php
/**
 * Product-level Shipping Methods
 *
 * Adds per-product shipping method checkboxes in the product edit page.
 * At checkout, only methods common to ALL cart items are shown.
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * All available shipping methods with their labels.
 */
function ganjeh_get_all_shipping_methods() {
    return [
        'post'       => __('ارسال پستی', 'ganjeh'),
        'express'    => __('پیک فوری در تهران', 'ganjeh'),
        'collection' => __('ارسال عادی', 'ganjeh'),
        'pickup'     => __('تحویل حضوری', 'ganjeh'),
    ];
}

/**
 * Add shipping methods checkboxes to the Shipping tab of product edit page.
 */
function ganjeh_product_shipping_methods_fields() {
    global $post;

    $saved = get_post_meta($post->ID, '_ganjeh_shipping_methods', true);
    $all_methods = ganjeh_get_all_shipping_methods();

    // Default: all methods enabled
    if (!is_array($saved) || empty($saved)) {
        $saved = array_keys($all_methods);
    }

    echo '<div class="options_group">';
    echo '<p class="form-field"><label>' . __('روش‌های ارسال مجاز', 'ganjeh') . '</label></p>';

    foreach ($all_methods as $key => $label) {
        $checked = in_array($key, $saved) ? 'checked="checked"' : '';
        echo '<p class="form-field _ganjeh_shipping_method_' . esc_attr($key) . '_field">';
        echo '<label for="_ganjeh_sm_' . esc_attr($key) . '">';
        echo '<input type="checkbox" id="_ganjeh_sm_' . esc_attr($key) . '" name="_ganjeh_shipping_methods[]" value="' . esc_attr($key) . '" ' . $checked . ' style="margin-left:6px;" />';
        echo esc_html($label);
        echo '</label>';
        echo '</p>';
    }

    echo '<p class="description" style="padding-right:24px;color:#666;font-size:12px;">'
        . __('در صورتی که هیچ روشی انتخاب نشود، همه روش‌ها فعال خواهند بود. در چک‌اوت فقط روش‌های مشترک بین تمام محصولات سبد نمایش داده می‌شود.', 'ganjeh')
        . '</p>';
    echo '</div>';
}
add_action('woocommerce_product_options_shipping', 'ganjeh_product_shipping_methods_fields');

/**
 * Save shipping methods meta when product is saved.
 */
function ganjeh_save_product_shipping_methods($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    $methods = isset($_POST['_ganjeh_shipping_methods']) ? array_map('sanitize_text_field', $_POST['_ganjeh_shipping_methods']) : [];

    // Validate against known methods
    $valid = array_keys(ganjeh_get_all_shipping_methods());
    $methods = array_intersect($methods, $valid);

    // If empty, save all (default behaviour)
    if (empty($methods)) {
        $methods = $valid;
    }

    update_post_meta($post_id, '_ganjeh_shipping_methods', $methods);
}
add_action('woocommerce_process_product_meta', 'ganjeh_save_product_shipping_methods');

/**
 * Get the shipping methods allowed by ALL products currently in the cart.
 *
 * Returns an array of method keys (e.g. ['post', 'collection']).
 * If the cart is empty, returns all methods.
 */
function ganjeh_get_cart_allowed_shipping_methods() {
    if (!function_exists('WC') || !WC()->cart) {
        return array_keys(ganjeh_get_all_shipping_methods());
    }

    $all_methods = array_keys(ganjeh_get_all_shipping_methods());
    $allowed = null;

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = $cart_item['product_id']; // always use parent product
        $product_methods = get_post_meta($product_id, '_ganjeh_shipping_methods', true);

        // If not set or empty, this product allows all methods
        if (!is_array($product_methods) || empty($product_methods)) {
            $product_methods = $all_methods;
        }

        if ($allowed === null) {
            $allowed = $product_methods;
        } else {
            $allowed = array_intersect($allowed, $product_methods);
        }
    }

    return $allowed !== null ? array_values($allowed) : $all_methods;
}
