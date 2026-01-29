<?php
/**
 * WooCommerce Template
 *
 * This template is used for WooCommerce pages (cart, checkout, my-account)
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="main-content">
    <?php while (have_posts()) : the_post(); ?>
        <?php the_content(); ?>
    <?php endwhile; ?>
</main>

<?php
get_footer();
