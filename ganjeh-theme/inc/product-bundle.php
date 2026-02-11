<?php
/**
 * Product Bundle Functionality
 *
 * Adds ability to assign bundled products to any product type
 * with admin UI, per-item settings, and frontend display
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
 * Add tab icon and styles
 */
function ganjeh_bundle_tab_icon() {
    ?>
    <style>
        #woocommerce-product-data ul.wc-tabs li.ganjeh_bundle_options a::before {
            content: '\f481';
            font-family: dashicons;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-item {
            margin: 0 0 8px 0;
            background: #f9f9f9;
            border: 1px solid #e2e4e7;
            border-radius: 6px;
            overflow: hidden;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-item:hover {
            background: #f0f0f1;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            cursor: pointer;
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
        #ganjeh_bundle_product_data .ganjeh-bundle-item-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-toggle {
            color: #2271b1;
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
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
        #ganjeh_bundle_product_data .ganjeh-bundle-item-settings {
            display: none;
            padding: 12px 16px;
            border-top: 1px solid #e2e4e7;
            background: #fff;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-item-settings.open {
            display: block;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-settings-tabs {
            display: flex;
            gap: 0;
            border-bottom: 1px solid #e2e4e7;
            margin-bottom: 12px;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-settings-tab {
            padding: 8px 16px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: #646970;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-settings-tab.active {
            color: #2271b1;
            border-bottom-color: #2271b1;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-tab-content {
            display: none;
        }
        #ganjeh_bundle_product_data .ganjeh-bundle-tab-content.active {
            display: block;
        }
        #ganjeh_bundle_product_data .ganjeh-setting-row {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 6px 0;
        }
        #ganjeh_bundle_product_data .ganjeh-setting-row label {
            font-size: 13px;
            color: #1d2327;
            font-weight: 500;
        }
        #ganjeh_bundle_product_data .ganjeh-setting-row input[type="number"] {
            width: 70px;
            text-align: center;
        }
        #ganjeh_bundle_product_data .ganjeh-setting-row select {
            min-width: 120px;
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
 * Get bundle items with settings (backward compatible)
 */
function ganjeh_get_bundle_items($product_id) {
    $bundle_data = get_post_meta($product_id, '_ganjeh_bundle_data', true);
    if (!empty($bundle_data) && is_array($bundle_data)) {
        return $bundle_data;
    }

    // Backward compatibility: convert old format (array of IDs) to new format
    $old_items = get_post_meta($product_id, '_ganjeh_bundle_items', true);
    if (!empty($old_items) && is_array($old_items)) {
        $items = [];
        foreach ($old_items as $item_id) {
            $items[] = ganjeh_bundle_item_defaults($item_id);
        }
        return $items;
    }

    return [];
}

/**
 * Default settings for a bundle item
 */
function ganjeh_bundle_item_defaults($product_id) {
    return [
        'id'                  => absint($product_id),
        'filter_variations'   => false,
        'override_defaults'   => true,
        'layout'              => 'classic',
        'min_qty'             => 1,
        'max_qty'             => 1,
        'default_qty'         => 1,
        'optional'            => false,
        'priced_individually' => true,
        'discount'            => 0,
    ];
}

/**
 * Render bundle tab content
 */
function ganjeh_bundle_product_tab_content() {
    global $post;
    $product_id = $post->ID;
    $bundle_items = ganjeh_get_bundle_items($product_id);
    ?>
    <div id="ganjeh_bundle_product_data" class="panel woocommerce_options_panel">
        <div class="options_group" style="padding: 12px;">
            <h4 style="margin: 0 0 12px 0;"><?php _e('محصولات داخل بسته', 'ganjeh'); ?></h4>

            <div id="ganjeh-bundle-items-list">
                <?php if (!empty($bundle_items)) : ?>
                    <?php foreach ($bundle_items as $index => $item) :
                        $item_product = wc_get_product($item['id']);
                        if (!$item_product) continue;
                        ganjeh_render_bundle_item_html($item, $item_product, $index);
                    endforeach; ?>
                <?php else : ?>
                    <div class="ganjeh-bundle-empty">
                        <?php _e('هنوز محصولی اضافه نشده است', 'ganjeh'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <input type="hidden" id="ganjeh_bundle_data_field" name="_ganjeh_bundle_data" value="<?php echo esc_attr(wp_json_encode($bundle_items)); ?>">

            <div class="ganjeh-bundle-search-wrap">
                <select id="ganjeh-bundle-search" class="wc-product-search" data-placeholder="<?php esc_attr_e('جستجوی محصول...', 'ganjeh'); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval($product_id); ?>" style="width: 300px;"></select>
                <button type="button" class="button" onclick="ganjehAddBundleItem();">
                    <?php _e('افزودن محصول', 'ganjeh'); ?>
                </button>
            </div>
        </div>
    </div>

    <script>
    var ganjehBundleData = <?php echo wp_json_encode($bundle_items); ?> || [];

    function ganjehRenderItemHtml(item, productName) {
        var id = item.id;
        var idx = ganjehBundleData.length - 1;
        return '<div class="ganjeh-bundle-item" data-id="' + id + '" data-index="' + idx + '">'
            + '<div class="ganjeh-bundle-item-header" onclick="ganjehToggleSettings(this)">'
            + '<div class="ganjeh-bundle-item-info">'
            + '<span class="ganjeh-bundle-item-id">#' + id + '</span>'
            + '<span class="ganjeh-bundle-item-name">' + jQuery('<div>').text(productName).html() + '</span>'
            + '</div>'
            + '<div class="ganjeh-bundle-item-actions">'
            + '<a href="#" class="ganjeh-bundle-toggle">⚙ <?php echo esc_js(__('تنظیمات', 'ganjeh')); ?></a>'
            + '<a href="#" class="ganjeh-bundle-remove" onclick="event.stopPropagation(); ganjehRemoveBundleItem(this); return false;"><?php echo esc_js(__('پاک کردن', 'ganjeh')); ?></a>'
            + '</div>'
            + '</div>'
            + '<div class="ganjeh-bundle-item-settings">'
            + '<div class="ganjeh-bundle-settings-tabs">'
            + '<div class="ganjeh-bundle-settings-tab active" onclick="ganjehSwitchTab(this, \'basic\')"><?php echo esc_js(__('تنظیمات اولیه', 'ganjeh')); ?></div>'
            + '<div class="ganjeh-bundle-settings-tab" onclick="ganjehSwitchTab(this, \'advanced\')"><?php echo esc_js(__('تنظیمات پیشرفته', 'ganjeh')); ?></div>'
            + '</div>'
            + '<div class="ganjeh-bundle-tab-content active" data-tab="basic">'
            + ganjehSettingCheckbox(idx, 'filter_variations', '<?php echo esc_js(__('تغییرات فیلتر', 'ganjeh')); ?>', item.filter_variations)
            + ganjehSettingCheckbox(idx, 'override_defaults', '<?php echo esc_js(__('لغو انتخاب های پیش فرض', 'ganjeh')); ?>', item.override_defaults)
            + ganjehSettingSelect(idx, 'layout', '<?php echo esc_js(__('نوع نمایش', 'ganjeh')); ?>', item.layout)
            + ganjehSettingNumber(idx, 'min_qty', '<?php echo esc_js(__('حداقل مقدار', 'ganjeh')); ?>', item.min_qty)
            + ganjehSettingNumber(idx, 'max_qty', '<?php echo esc_js(__('حداکثر مقدار', 'ganjeh')); ?>', item.max_qty)
            + ganjehSettingNumber(idx, 'default_qty', '<?php echo esc_js(__('مقدار پیشفرض', 'ganjeh')); ?>', item.default_qty)
            + ganjehSettingCheckbox(idx, 'optional', '<?php echo esc_js(__('اختیاری', 'ganjeh')); ?>', item.optional)
            + ganjehSettingCheckbox(idx, 'priced_individually', '<?php echo esc_js(__('قیمت به صورت تکی', 'ganjeh')); ?>', item.priced_individually)
            + ganjehSettingNumber(idx, 'discount', '<?php echo esc_js(__('تخفیف %', 'ganjeh')); ?>', item.discount)
            + '</div>'
            + '<div class="ganjeh-bundle-tab-content" data-tab="advanced">'
            + '<p style="color:#999; font-style:italic; text-align:center; padding: 10px 0;"><?php echo esc_js(__('تنظیمات پیشرفته در آینده اضافه خواهد شد', 'ganjeh')); ?></p>'
            + '</div>'
            + '</div>'
            + '</div>';
    }

    function ganjehSettingCheckbox(idx, key, label, value) {
        var checked = value ? ' checked' : '';
        return '<div class="ganjeh-setting-row">'
            + '<input type="checkbox" id="bundle_' + idx + '_' + key + '"' + checked + ' onchange="ganjehUpdateItemSetting(' + idx + ', \'' + key + '\', this.checked)">'
            + '<label for="bundle_' + idx + '_' + key + '">' + label + '</label>'
            + '</div>';
    }

    function ganjehSettingNumber(idx, key, label, value) {
        return '<div class="ganjeh-setting-row">'
            + '<input type="number" min="0" step="1" value="' + (value || 0) + '" onchange="ganjehUpdateItemSetting(' + idx + ', \'' + key + '\', parseFloat(this.value) || 0)">'
            + '<label>' + label + '</label>'
            + '</div>';
    }

    function ganjehSettingSelect(idx, key, label, value) {
        var opts = [
            {val: 'classic', text: '<?php echo esc_js(__('کلاسیک', 'ganjeh')); ?>'},
            {val: 'grid', text: '<?php echo esc_js(__('شبکه‌ای', 'ganjeh')); ?>'}
        ];
        var html = '<div class="ganjeh-setting-row">'
            + '<select onchange="ganjehUpdateItemSetting(' + idx + ', \'' + key + '\', this.value)">';
        opts.forEach(function(o) {
            html += '<option value="' + o.val + '"' + (value === o.val ? ' selected' : '') + '>' + o.text + '</option>';
        });
        html += '</select><label>' + label + '</label></div>';
        return html;
    }

    function ganjehToggleSettings(headerEl) {
        var settings = jQuery(headerEl).siblings('.ganjeh-bundle-item-settings');
        settings.toggleClass('open');
    }

    function ganjehSwitchTab(tabEl, tabName) {
        var container = jQuery(tabEl).closest('.ganjeh-bundle-item-settings');
        container.find('.ganjeh-bundle-settings-tab').removeClass('active');
        jQuery(tabEl).addClass('active');
        container.find('.ganjeh-bundle-tab-content').removeClass('active');
        container.find('.ganjeh-bundle-tab-content[data-tab="' + tabName + '"]').addClass('active');
    }

    function ganjehUpdateItemSetting(index, key, value) {
        if (ganjehBundleData[index]) {
            ganjehBundleData[index][key] = value;
            ganjehUpdateBundleField();
        }
    }

    function ganjehAddBundleItem() {
        var select = jQuery('#ganjeh-bundle-search');
        var selectedOption = select.find('option:selected');

        if (!selectedOption.val()) {
            return;
        }

        var productId = parseInt(selectedOption.val());
        var productName = selectedOption.text();

        // Check duplicate
        if (jQuery('.ganjeh-bundle-item[data-id="' + productId + '"]').length) {
            alert('<?php echo esc_js(__('این محصول قبلاً اضافه شده است', 'ganjeh')); ?>');
            return;
        }

        // Remove empty message
        jQuery('.ganjeh-bundle-empty').remove();

        // Create item with defaults
        var item = {
            id: productId,
            filter_variations: false,
            override_defaults: true,
            layout: 'classic',
            min_qty: 1,
            max_qty: 1,
            default_qty: 1,
            optional: false,
            priced_individually: true,
            discount: 0
        };

        ganjehBundleData.push(item);

        var html = ganjehRenderItemHtml(item, productName);
        jQuery('#ganjeh-bundle-items-list').append(html);

        ganjehUpdateBundleField();

        // Clear select
        select.val(null).trigger('change');
    }

    function ganjehRemoveBundleItem(el) {
        var item = jQuery(el).closest('.ganjeh-bundle-item');
        var id = parseInt(item.data('id'));

        // Remove from data array
        ganjehBundleData = ganjehBundleData.filter(function(i) { return i.id !== id; });

        item.remove();
        ganjehUpdateBundleField();

        // Reindex data-index attributes
        jQuery('.ganjeh-bundle-item').each(function(i) {
            jQuery(this).attr('data-index', i);
        });

        // Show empty message if no items
        if (jQuery('.ganjeh-bundle-item').length === 0) {
            jQuery('#ganjeh-bundle-items-list').html(
                '<div class="ganjeh-bundle-empty"><?php echo esc_js(__('هنوز محصولی اضافه نشده است', 'ganjeh')); ?></div>'
            );
        }
    }

    function ganjehUpdateBundleField() {
        jQuery('#ganjeh_bundle_data_field').val(JSON.stringify(ganjehBundleData));
    }
    </script>
    <?php
}
add_action('woocommerce_product_data_panels', 'ganjeh_bundle_product_tab_content');

/**
 * Render a single bundle item HTML in admin
 */
function ganjeh_render_bundle_item_html($item, $product, $index) {
    $id = $item['id'];
    $name = $product->get_name();
    ?>
    <div class="ganjeh-bundle-item" data-id="<?php echo esc_attr($id); ?>" data-index="<?php echo esc_attr($index); ?>">
        <div class="ganjeh-bundle-item-header" onclick="ganjehToggleSettings(this)">
            <div class="ganjeh-bundle-item-info">
                <span class="ganjeh-bundle-item-id">#<?php echo esc_html($id); ?></span>
                <span class="ganjeh-bundle-item-name"><?php echo esc_html($name); ?></span>
            </div>
            <div class="ganjeh-bundle-item-actions">
                <a href="#" class="ganjeh-bundle-toggle">⚙ <?php _e('تنظیمات', 'ganjeh'); ?></a>
                <a href="#" class="ganjeh-bundle-remove" onclick="event.stopPropagation(); ganjehRemoveBundleItem(this); return false;">
                    <?php _e('پاک کردن', 'ganjeh'); ?>
                </a>
            </div>
        </div>
        <div class="ganjeh-bundle-item-settings">
            <div class="ganjeh-bundle-settings-tabs">
                <div class="ganjeh-bundle-settings-tab active" onclick="ganjehSwitchTab(this, 'basic')"><?php _e('تنظیمات اولیه', 'ganjeh'); ?></div>
                <div class="ganjeh-bundle-settings-tab" onclick="ganjehSwitchTab(this, 'advanced')"><?php _e('تنظیمات پیشرفته', 'ganjeh'); ?></div>
            </div>
            <div class="ganjeh-bundle-tab-content active" data-tab="basic">
                <?php ganjeh_render_setting_checkbox($index, 'filter_variations', __('تغییرات فیلتر', 'ganjeh'), $item['filter_variations']); ?>
                <?php ganjeh_render_setting_checkbox($index, 'override_defaults', __('لغو انتخاب های پیش فرض', 'ganjeh'), $item['override_defaults']); ?>
                <?php ganjeh_render_setting_select($index, 'layout', __('نوع نمایش', 'ganjeh'), $item['layout']); ?>
                <?php ganjeh_render_setting_number($index, 'min_qty', __('حداقل مقدار', 'ganjeh'), $item['min_qty']); ?>
                <?php ganjeh_render_setting_number($index, 'max_qty', __('حداکثر مقدار', 'ganjeh'), $item['max_qty']); ?>
                <?php ganjeh_render_setting_number($index, 'default_qty', __('مقدار پیشفرض', 'ganjeh'), $item['default_qty']); ?>
                <?php ganjeh_render_setting_checkbox($index, 'optional', __('اختیاری', 'ganjeh'), $item['optional']); ?>
                <?php ganjeh_render_setting_checkbox($index, 'priced_individually', __('قیمت به صورت تکی', 'ganjeh'), $item['priced_individually']); ?>
                <?php ganjeh_render_setting_number($index, 'discount', __('تخفیف %', 'ganjeh'), $item['discount']); ?>
            </div>
            <div class="ganjeh-bundle-tab-content" data-tab="advanced">
                <p style="color:#999; font-style:italic; text-align:center; padding: 10px 0;"><?php _e('تنظیمات پیشرفته در آینده اضافه خواهد شد', 'ganjeh'); ?></p>
            </div>
        </div>
    </div>
    <?php
}

function ganjeh_render_setting_checkbox($index, $key, $label, $value) {
    $checked = $value ? 'checked' : '';
    ?>
    <div class="ganjeh-setting-row">
        <input type="checkbox" id="bundle_<?php echo $index; ?>_<?php echo $key; ?>" <?php echo $checked; ?> onchange="ganjehUpdateItemSetting(<?php echo $index; ?>, '<?php echo $key; ?>', this.checked)">
        <label for="bundle_<?php echo $index; ?>_<?php echo $key; ?>"><?php echo esc_html($label); ?></label>
    </div>
    <?php
}

function ganjeh_render_setting_number($index, $key, $label, $value) {
    ?>
    <div class="ganjeh-setting-row">
        <input type="number" min="0" step="1" value="<?php echo esc_attr($value); ?>" onchange="ganjehUpdateItemSetting(<?php echo $index; ?>, '<?php echo $key; ?>', parseFloat(this.value) || 0)">
        <label><?php echo esc_html($label); ?></label>
    </div>
    <?php
}

function ganjeh_render_setting_select($index, $key, $label, $value) {
    $options = [
        'classic' => __('کلاسیک', 'ganjeh'),
        'grid'    => __('شبکه‌ای', 'ganjeh'),
    ];
    ?>
    <div class="ganjeh-setting-row">
        <select onchange="ganjehUpdateItemSetting(<?php echo $index; ?>, '<?php echo $key; ?>', this.value)">
            <?php foreach ($options as $val => $text) : ?>
                <option value="<?php echo esc_attr($val); ?>" <?php selected($value, $val); ?>><?php echo esc_html($text); ?></option>
            <?php endforeach; ?>
        </select>
        <label><?php echo esc_html($label); ?></label>
    </div>
    <?php
}

/**
 * Save bundle data on product save
 */
function ganjeh_save_bundle_items($post_id) {
    if (isset($_POST['_ganjeh_bundle_data'])) {
        $raw = wp_unslash($_POST['_ganjeh_bundle_data']);
        $data = json_decode($raw, true);

        if (empty($data) || !is_array($data)) {
            delete_post_meta($post_id, '_ganjeh_bundle_data');
            delete_post_meta($post_id, '_ganjeh_bundle_items');
        } else {
            // Sanitize each item
            $sanitized = [];
            foreach ($data as $item) {
                $sanitized[] = [
                    'id'                  => absint($item['id']),
                    'filter_variations'   => !empty($item['filter_variations']),
                    'override_defaults'   => !empty($item['override_defaults']),
                    'layout'              => sanitize_text_field($item['layout'] ?? 'classic'),
                    'min_qty'             => absint($item['min_qty'] ?? 1),
                    'max_qty'             => absint($item['max_qty'] ?? 1),
                    'default_qty'         => absint($item['default_qty'] ?? 1),
                    'optional'            => !empty($item['optional']),
                    'priced_individually' => !empty($item['priced_individually']),
                    'discount'            => floatval($item['discount'] ?? 0),
                ];
            }
            update_post_meta($post_id, '_ganjeh_bundle_data', $sanitized);

            // Also keep old format for backward compatibility in frontend
            $ids = array_map(function($i) { return $i['id']; }, $sanitized);
            update_post_meta($post_id, '_ganjeh_bundle_items', $ids);
        }
    }
}
add_action('woocommerce_process_product_meta', 'ganjeh_save_bundle_items');
