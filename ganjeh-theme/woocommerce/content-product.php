<?php
/**
 * Product Loop Content Template
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

global $product;

if (!$product || !is_a($product, 'WC_Product')) {
    return;
}

// Use our custom product card component
get_template_part('template-parts/components/product-card');
