<?php
/**
 * Theme Setup Functions
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add custom body classes
 */
function ganjeh_body_classes($classes) {
    $classes[] = 'ganjeh-theme';

    if (is_rtl()) {
        $classes[] = 'rtl';
    }

    if (is_front_page()) {
        $classes[] = 'front-page';
    }

    if (is_shop() || is_product_category() || is_product_tag()) {
        $classes[] = 'shop-page';
    }

    if (is_product()) {
        $classes[] = 'single-product-page';
    }

    return $classes;
}
add_filter('body_class', 'ganjeh_body_classes');

/**
 * Add defer/async to scripts
 */
function ganjeh_script_loader_tag($tag, $handle, $src) {
    $defer_scripts = ['alpine', 'ganjeh-main'];
    $async_scripts = ['swiper'];

    if (in_array($handle, $defer_scripts)) {
        return str_replace(' src', ' defer src', $tag);
    }

    if (in_array($handle, $async_scripts)) {
        return str_replace(' src', ' async src', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 'ganjeh_script_loader_tag', 10, 3);

/**
 * Disable WordPress scaling
 */
add_filter('big_image_size_threshold', '__return_false');

/**
 * Remove WordPress version from URLs
 */
function ganjeh_remove_version_scripts_styles($src) {
    if (strpos($src, 'ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}
add_filter('style_loader_src', 'ganjeh_remove_version_scripts_styles', 9999);
add_filter('script_loader_src', 'ganjeh_remove_version_scripts_styles', 9999);

/**
 * Limit post revisions
 */
if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 3);
}

/**
 * Custom excerpt length
 */
function ganjeh_excerpt_length($length) {
    return 15;
}
add_filter('excerpt_length', 'ganjeh_excerpt_length');

/**
 * Custom excerpt more
 */
function ganjeh_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'ganjeh_excerpt_more');

/**
 * Allow SVG uploads
 */
function ganjeh_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['webp'] = 'image/webp';
    return $mimes;
}
add_filter('upload_mimes', 'ganjeh_mime_types');

/**
 * Add WebP support
 */
function ganjeh_webp_upload_mimes($existing_mimes) {
    $existing_mimes['webp'] = 'image/webp';
    return $existing_mimes;
}
add_filter('mime_types', 'ganjeh_webp_upload_mimes');

/**
 * Disable XML-RPC for security
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Remove REST API links from header
 */
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('template_redirect', 'rest_output_link_header', 11);

/**
 * Clean up WordPress head
 */
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_shortlink_wp_head');

/**
 * Register category badge meta
 */
function ganjeh_register_term_meta() {
    register_term_meta('product_cat', 'ganjeh_badge', [
        'type'         => 'string',
        'single'       => true,
        'show_in_rest' => true,
    ]);
}
add_action('init', 'ganjeh_register_term_meta');

/**
 * Add badge field to category edit form
 */
function ganjeh_product_cat_add_badge_field($term) {
    $badge = get_term_meta($term->term_id, 'ganjeh_badge', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label for="ganjeh_badge"><?php _e('برچسب نمایشی', 'ganjeh'); ?></label></th>
        <td>
            <input type="text" name="ganjeh_badge" id="ganjeh_badge" value="<?php echo esc_attr($badge); ?>">
            <p class="description"><?php _e('مثال: جدید، تخفیف، پرفروش', 'ganjeh'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('product_cat_edit_form_fields', 'ganjeh_product_cat_add_badge_field');

/**
 * Save badge field
 */
function ganjeh_save_product_cat_badge($term_id) {
    if (isset($_POST['ganjeh_badge'])) {
        update_term_meta($term_id, 'ganjeh_badge', sanitize_text_field($_POST['ganjeh_badge']));
    }
}
add_action('edited_product_cat', 'ganjeh_save_product_cat_badge');
add_action('created_product_cat', 'ganjeh_save_product_cat_badge');
