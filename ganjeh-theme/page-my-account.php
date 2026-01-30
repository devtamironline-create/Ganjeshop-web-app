<?php
/**
 * Template Name: My Account Page
 *
 * Custom template for WooCommerce My Account
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

// Check if WooCommerce is active
if (!class_exists('WooCommerce')) {
    get_header();
    echo '<p>WooCommerce is not active.</p>';
    get_footer();
    return;
}

// Check if user is logged in
if (!is_user_logged_in()) {
    get_header();
    wc_get_template('myaccount/form-login.php');
    get_footer();
    return;
}

get_header();

// Get current endpoint
global $wp;
$current_endpoint = '';

// Check which endpoint we're on
$endpoints = [
    'orders',
    'view-order',
    'edit-account',
    'edit-address',
    'lost-password',
    'customer-logout',
];

foreach ($endpoints as $endpoint) {
    if (isset($wp->query_vars[$endpoint])) {
        $current_endpoint = $endpoint;
        break;
    }
}

// Route to appropriate template
switch ($current_endpoint) {
    case 'orders':
        wc_get_template('myaccount/orders.php');
        break;

    case 'view-order':
        $order_id = absint($wp->query_vars['view-order']);
        wc_get_template('myaccount/view-order.php', ['order_id' => $order_id]);
        break;

    case 'edit-account':
        wc_get_template('myaccount/form-edit-account.php');
        break;

    case 'edit-address':
        $load_address = isset($wp->query_vars['edit-address']) ? $wp->query_vars['edit-address'] : '';
        wc_get_template('myaccount/edit-address.php', ['load_address' => $load_address]);
        break;

    case 'customer-logout':
        wp_logout();
        wp_redirect(wc_get_page_permalink('myaccount'));
        exit;

    default:
        // Dashboard - main my account page
        wc_get_template('myaccount/my-account.php');
        break;
}

get_footer();
