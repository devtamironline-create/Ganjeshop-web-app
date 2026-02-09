<?php
/**
 * Template Name: Checkout Page
 * Template for the checkout page
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

// Make sure WooCommerce is active
if (!class_exists('WooCommerce')) {
    get_header();
    echo '<p>WooCommerce is not active.</p>';
    get_footer();
    return;
}

// Check if this is the order received (thank you) page
if (is_wc_endpoint_url('order-received')) {
    get_header();

    // Get order ID from URL
    global $wp;
    $order_id = absint($wp->query_vars['order-received']);

    // Include the thankyou template
    wc_get_template('checkout/thankyou.php', ['order_id' => $order_id]);

    get_footer();
    return;
}

// Redirect to cart if cart is empty (only for regular checkout, not thankyou page)
if (WC()->cart->is_empty()) {
    wp_redirect(wc_get_cart_url());
    exit;
}

// Set customer shipping country to Iran so shipping zones work correctly
WC()->customer->set_shipping_country('IR');
WC()->customer->set_billing_country('IR');

// If user has a saved address, set the state so correct shipping zone is matched
if (is_user_logged_in()) {
    $user_id = get_current_user_id();
    $saved_addresses = ganjeh_get_user_addresses($user_id);
    if (!empty($saved_addresses)) {
        $first_address = $saved_addresses[0];
        if (!empty($first_address['state'])) {
            WC()->customer->set_shipping_state($first_address['state']);
            WC()->customer->set_billing_state($first_address['state']);
        }
        if (!empty($first_address['city'])) {
            WC()->customer->set_shipping_city($first_address['city']);
            WC()->customer->set_billing_city($first_address['city']);
        }
        if (!empty($first_address['postcode'])) {
            WC()->customer->set_shipping_postcode($first_address['postcode']);
            WC()->customer->set_billing_postcode($first_address['postcode']);
        }
    }
}

get_header();

// Get checkout object
$checkout = WC()->checkout();

// Include the checkout template directly
wc_get_template('checkout/form-checkout.php', ['checkout' => $checkout]);

get_footer();
