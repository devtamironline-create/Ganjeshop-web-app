<?php
/**
 * Template Name: Cart Page
 * Template for the cart page
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

get_header();

// Include the cart template directly
wc_get_template('cart/cart.php');

get_footer();
