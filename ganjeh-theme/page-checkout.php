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

// Redirect to cart if cart is empty
if (WC()->cart->is_empty()) {
    wp_redirect(wc_get_cart_url());
    exit;
}

get_header();

// Get checkout object
$checkout = WC()->checkout();

// Include the checkout template directly
wc_get_template('checkout/form-checkout.php', ['checkout' => $checkout]);

get_footer();
