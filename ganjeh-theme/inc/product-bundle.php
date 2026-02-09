<?php
/**
 * Product Bundle Functionality
 *
 * Adds ability to assign bundled products to any product type
 * with admin UI and frontend display
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add "محصولات بسته بندی شده" tab to product data tabs
 */
function ganjeh_bundle_product_tab($tabs) {
    $tabs['ganjeh_bundle'] = [
        'label'    => __('محصولات بسته بندی شده', 'ganjeh'),
        'target'   => 'ganjeh_bundle_product_data',
        'class'    => [],
        'priority' => 80,
    ];
    return $tabs;
}
add_filter('woocommerce_product_data_tabs', 'ganjeh_bundle_product_tab');

/**
 * Add tab icon style
 */
function ganjeh_bundle_tab_icon() {
    ?>
    <style>
        #woocommerce-product-data ul.wc-tabs li.ganjeh_bundle_options a::before {
            content: '\f481';
            font-family: dashicons;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            margin: 0 0 6px 0;
            background: #f9f9f9;
            border: 1px solid #e2e4e7;
            border-radius: 6px;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-item:hover {
            background: #f0f0f1;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-item-info {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-item-id {
            background: #ddd;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: #555;
            font-weight: 600;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-item-name {
            font-weight: 500;
            color: #1d2327;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-remove {
            color: #b32d2e;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-remove:hover {
            color: #a00;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-search-wrap {
            margin-top: 12px;
            display: flex;
            gap: 8px;
            align-items: center;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-search-wrap select {
            min-width: 300px;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-empty {
            color: #999;
            font-style: italic;
            padding: 20px 0;
            text-align: center;
        }
    </style>
    <?php
}
add_action('admin_head', 'ganjeh_bundle_tab_icon');

/**
 * Render bundle tab content
 */
function ganjeh_bundle_product_tab_content() {
    global $post;
    $product_id = $post->ID;
    $bundle_items = get_post_meta($product_id, '_ganjeh_bundle_items', true);
    if (!is_array($bundle_items)) {
        $bundle_items = [];
    }
    ?>
    <div id="ganjeh_bundle_product_data" class="panel woocommerce_options_panel">
        <div class="options_group" style="padding: 12px;">
            <h4 style="margin: 0 0 12px 0;"><?php _e('محصولات داخل بسته', 'ganjeh'); ?></h4>

            <div id="ganjeh-bundle-items-list">
                <?php if (!empty($bundle_items)) : ?>
                    <?php foreach ($bundle_items as $item_id) :
                        $item_product = wc_get_product($item_id);
                        if (!$item_product) continue;
                    ?>
                        <div class="ganjeh-bundle-item" data-id="<?php echo esc_attr($item_id); ?>">
                            <div class="ganjeh-bundle-item-info">
                                <span class="ganjeh-bundle-item-id">#<?php echo esc_html($item_id); ?></span>
                                <span class="ganjeh-bundle-item-name"><?php echo esc_html($item_product->get_name()); ?></span>
                            </div>
                            <a href="#" class="ganjeh-bundle-remove" onclick="ganjehRemoveBundleItem(this); return false;">
                                <?php _e('پاک کردن', 'ganjeh'); ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="ganjeh-bundle-empty">
                        <?php _e('هنوز محصولی اضافه نشده است', 'ganjeh'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <input type="hidden" id="ganjeh_bundle_items_field" name="_ganjeh_bundle_items" value="<?php echo esc_attr(implode(',', $bundle_items)); ?>">

            <div class="ganjeh-bundle-search-wrap">
                <select id="ganjeh-bundle-search" class="wc-product-search" data-placeholder="<?php esc_attr_e('جستجوی محصول...', 'ganjeh'); ?>" data-action="woocommerce_json_search_products" data-exclude="<?php echo intval($product_id); ?>" style="width: 300px;"></select>
                <button type="button" class="button" onclick="ganjehAddBundleItem();">
                    <?php _e('افزودن محصول', 'ganjeh'); ?>
                </button>
            </div>
        </div>
    </div>

    <script>
    function ganjehAddBundleItem() {
        var select = jQuery('#ganjeh-bundle-search');
        var selectedOption = select.find('option:selected');

        if (!selectedOption.val()) {
            return;
        }

        var productId = selectedOption.val();
        var productName = selectedOption.text();

        // Check duplicate
        if (jQuery('.ganjeh-bundle-item[data-id="' + productId + '"]').length) {
            alert('<?php echo esc_js(__('این محصول قبلاً اضافه شده است', 'ganjeh')); ?>');
            return;
        }

        // Remove empty message
        jQuery('.ganjeh-bundle-empty').remove();

        // Add item
        var html = '<div class="ganjeh-bundle-item" data-id="' + productId + '">'
            + '<div class="ganjeh-bundle-item-info">'
            + '<span class="ganjeh-bundle-item-id">#' + productId + '</span>'
            + '<span class="ganjeh-bundle-item-name">' + jQuery('<div>').text(productName).html() + '</span>'
            + '</div>'
            + '<a href="#" class="ganjeh-bundle-remove" onclick="ganjehRemoveBundleItem(this); return false;"><?php echo esc_js(__('پاک کردن', 'ganjeh')); ?></a>'
            + '</div>';

        jQuery('#ganjeh-bundle-items-list').append(html);

        // Update hidden field
        ganjehUpdateBundleField();

        // Clear select
        select.val(null).trigger('change');
    }

    function ganjehRemoveBundleItem(el) {
        jQuery(el).closest('.ganjeh-bundle-item').remove();
        ganjehUpdateBundleField();

        // Show empty message if no items
        if (jQuery('.ganjeh-bundle-item').length === 0) {
            jQuery('#ganjeh-bundle-items-list').html(
                '<div class="ganjeh-bundle-empty"><?php echo esc_js(__('هنوز محصولی اضافه نشده است', 'ganjeh')); ?></div>'
            );
        }
    }

    function ganjehUpdateBundleField() {
        var ids = [];
        jQuery('.ganjeh-bundle-item').each(function() {
            ids.push(jQuery(this).data('id'));
        });
        jQuery('#ganjeh_bundle_items_field').val(ids.join(','));
    }
    </script>
    <?php
}
add_action('woocommerce_product_data_panels', 'ganjeh_bundle_product_tab_content');

/**
 * Save bundle items on product save
 */
function ganjeh_save_bundle_items($post_id) {
    if (isset($_POST['_ganjeh_bundle_items'])) {
        $raw = sanitize_text_field($_POST['_ganjeh_bundle_items']);
        if (empty($raw)) {
            delete_post_meta($post_id, '_ganjeh_bundle_items');
        } else {
            $ids = array_filter(array_map('absint', explode(',', $raw)));
            update_post_meta($post_id, '_ganjeh_bundle_items', $ids);
        }
    }
}
add_action('woocommerce_process_product_meta', 'ganjeh_save_bundle_items');
