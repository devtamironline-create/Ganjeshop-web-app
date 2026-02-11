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
 * Add tab icon style only
 */
function ganjeh_bundle_tab_icon() {
    echo '<style>#woocommerce-product-data ul.wc-tabs li.ganjeh_bundle_options a::before{content:"\f481";font-family:dashicons;}</style>';
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
    <div id="ganjeh_bundle_product_data" class="panel woocommerce_options_panel" style="padding:12px;">
        <h4 style="margin:0 0 12px 0;"><?php _e('محصولات داخل بسته', 'ganjeh'); ?></h4>

        <div id="ganjeh-bundle-items-list">
            <?php if (!empty($bundle_items)) : ?>
                <?php foreach ($bundle_items as $index => $item) :
                    $item_product = wc_get_product($item['id']);
                    if (!$item_product) continue;
                    ganjeh_render_bundle_item_admin($item, $item_product, $index);
                endforeach; ?>
            <?php else : ?>
                <div style="color:#999;font-style:italic;padding:20px 0;text-align:center;">
                    <?php _e('هنوز محصولی اضافه نشده است', 'ganjeh'); ?>
                </div>
            <?php endif; ?>
        </div>

        <input type="hidden" id="ganjeh_bundle_data_field" name="_ganjeh_bundle_data" value="<?php echo esc_attr(wp_json_encode($bundle_items)); ?>">

        <div style="margin-top:12px;display:flex;gap:8px;align-items:center;">
            <select id="ganjeh-bundle-search" class="wc-product-search" data-placeholder="<?php esc_attr_e('جستجوی محصول...', 'ganjeh'); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval($product_id); ?>" style="width:300px;"></select>
            <button type="button" class="button" onclick="ganjehAddBundleItem();">
                <?php _e('افزودن محصول', 'ganjeh'); ?>
            </button>
        </div>
    </div>

    <script>
    var ganjehBundleData = <?php echo wp_json_encode($bundle_items); ?> || [];

    function ganjehRenderItemHtml(item, productName) {
        var id = item.id;
        var idx = ganjehBundleData.length - 1;
        var html = '<div class="ganjeh-bi" data-id="' + id + '" data-index="' + idx + '" style="margin:0 0 8px;background:#f9f9f9;border:1px solid #e2e4e7;border-radius:6px;overflow:hidden;">';
        html += '<div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;cursor:pointer;" onclick="ganjehToggleSettings(this)">';
        html += '<div style="display:flex;align-items:center;gap:10px;flex:1;">';
        html += '<span style="background:#ddd;padding:2px 8px;border-radius:4px;font-size:12px;color:#555;font-weight:600;">#' + id + '</span>';
        html += '<span style="font-weight:500;color:#1d2327;">' + jQuery('<div>').text(productName).html() + '</span>';
        html += '</div>';
        html += '<div style="display:flex;align-items:center;gap:12px;">';
        html += '<span style="color:#2271b1;font-size:13px;cursor:pointer;">⚙ <?php echo esc_js(__("تنظیمات", "ganjeh")); ?></span>';
        html += '<span style="color:#b32d2e;font-weight:500;cursor:pointer;" onclick="event.stopPropagation();ganjehRemoveBundleItem(this);return false;"><?php echo esc_js(__("پاک کردن", "ganjeh")); ?></span>';
        html += '</div></div>';

        // Settings panel
        html += '<div class="ganjeh-bi-settings" style="display:none;padding:16px;border-top:1px solid #e2e4e7;background:#fff;">';
        html += '<div style="display:flex;gap:0;border-bottom:1px solid #e2e4e7;margin-bottom:12px;">';
        html += '<div class="ganjeh-stab active" onclick="ganjehSwitchTab(this,\'basic\')" style="padding:8px 16px;cursor:pointer;font-size:13px;font-weight:500;color:#2271b1;border-bottom:2px solid #2271b1;"><?php echo esc_js(__("تنظیمات اولیه", "ganjeh")); ?></div>';
        html += '<div class="ganjeh-stab" onclick="ganjehSwitchTab(this,\'advanced\')" style="padding:8px 16px;cursor:pointer;font-size:13px;font-weight:500;color:#646970;border-bottom:2px solid transparent;"><?php echo esc_js(__("تنظیمات پیشرفته", "ganjeh")); ?></div>';
        html += '</div>';

        html += '<div class="ganjeh-stab-content" data-tab="basic">';
        html += ganjehSettingRow(idx, 'filter_variations', '<?php echo esc_js(__("تغییرات فیلتر", "ganjeh")); ?>', 'checkbox', item.filter_variations);
        html += ganjehSettingRow(idx, 'override_defaults', '<?php echo esc_js(__("لغو انتخاب های پیش فرض", "ganjeh")); ?>', 'checkbox', item.override_defaults);
        html += ganjehSettingRow(idx, 'layout', '<?php echo esc_js(__("نوع نمایش", "ganjeh")); ?>', 'select', item.layout);
        html += ganjehSettingRow(idx, 'min_qty', '<?php echo esc_js(__("حداقل مقدار", "ganjeh")); ?>', 'number', item.min_qty);
        html += ganjehSettingRow(idx, 'max_qty', '<?php echo esc_js(__("حداکثر مقدار", "ganjeh")); ?>', 'number', item.max_qty);
        html += ganjehSettingRow(idx, 'default_qty', '<?php echo esc_js(__("مقدار پیشفرض", "ganjeh")); ?>', 'number', item.default_qty);
        html += ganjehSettingRow(idx, 'optional', '<?php echo esc_js(__("اختیاری", "ganjeh")); ?>', 'checkbox', item.optional);
        html += ganjehSettingRow(idx, 'priced_individually', '<?php echo esc_js(__("قیمت به صورت تکی", "ganjeh")); ?>', 'checkbox', item.priced_individually);
        html += ganjehSettingRow(idx, 'discount', '<?php echo esc_js(__("تخفیف %", "ganjeh")); ?>', 'number', item.discount);
        html += '</div>';

        html += '<div class="ganjeh-stab-content" data-tab="advanced" style="display:none;">';
        html += '<p style="color:#999;font-style:italic;text-align:center;padding:10px 0;"><?php echo esc_js(__("تنظیمات پیشرفته در آینده اضافه خواهد شد", "ganjeh")); ?></p>';
        html += '</div>';

        html += '</div></div>';
        return html;
    }

    function ganjehSettingRow(idx, key, label, type, value) {
        var row = '<div style="display:flex;align-items:center;padding:8px 0;border-bottom:1px solid #f0f0f1;">';
        row += '<span style="flex:1;font-size:13px;color:#1d2327;font-weight:500;text-align:right;padding-left:15px;">' + label + '</span>';
        row += '<span>';
        if (type === 'checkbox') {
            row += '<input type="checkbox"' + (value ? ' checked' : '') + ' onchange="ganjehUpdateItemSetting(' + idx + ',\'' + key + '\',this.checked)">';
        } else if (type === 'number') {
            row += '<input type="number" min="0" step="1" value="' + (value || 0) + '" style="width:70px;text-align:center;" onchange="ganjehUpdateItemSetting(' + idx + ',\'' + key + '\',parseFloat(this.value)||0)">';
        } else if (type === 'select') {
            row += '<select style="width:130px;" onchange="ganjehUpdateItemSetting(' + idx + ',\'' + key + '\',this.value)">';
            row += '<option value="classic"' + (value === 'classic' ? ' selected' : '') + '><?php echo esc_js(__("کلاسیک", "ganjeh")); ?></option>';
            row += '<option value="grid"' + (value === 'grid' ? ' selected' : '') + '><?php echo esc_js(__("شبکه‌ای", "ganjeh")); ?></option>';
            row += '</select>';
        }
        row += '</span></div>';
        return row;
    }

    function ganjehToggleSettings(headerEl) {
        var settings = jQuery(headerEl).siblings('.ganjeh-bi-settings');
        if (settings.is(':visible')) {
            settings.slideUp(200);
        } else {
            settings.slideDown(200);
        }
    }

    function ganjehSwitchTab(tabEl, tabName) {
        var container = jQuery(tabEl).closest('.ganjeh-bi-settings');
        container.find('.ganjeh-stab').css({'color':'#646970','border-bottom-color':'transparent'});
        jQuery(tabEl).css({'color':'#2271b1','border-bottom-color':'#2271b1'});
        container.find('.ganjeh-stab-content').hide();
        container.find('.ganjeh-stab-content[data-tab="' + tabName + '"]').show();
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
        if (!selectedOption.val()) return;

        var productId = parseInt(selectedOption.val());
        var productName = selectedOption.text();

        if (jQuery('.ganjeh-bi[data-id="' + productId + '"]').length) {
            alert('<?php echo esc_js(__("این محصول قبلاً اضافه شده است", "ganjeh")); ?>');
            return;
        }

        jQuery('#ganjeh-bundle-items-list').find('[style*="font-style:italic"]').remove();

        var item = {
            id: productId, filter_variations: false, override_defaults: true,
            layout: 'classic', min_qty: 1, max_qty: 1, default_qty: 1,
            optional: false, priced_individually: true, discount: 0
        };
        ganjehBundleData.push(item);
        jQuery('#ganjeh-bundle-items-list').append(ganjehRenderItemHtml(item, productName));
        ganjehUpdateBundleField();
        select.val(null).trigger('change');
    }

    function ganjehRemoveBundleItem(el) {
        var item = jQuery(el).closest('.ganjeh-bi');
        var id = parseInt(item.data('id'));
        ganjehBundleData = ganjehBundleData.filter(function(i) { return i.id !== id; });
        item.remove();
        ganjehUpdateBundleField();
        jQuery('.ganjeh-bi').each(function(i) { jQuery(this).attr('data-index', i); });
        if (jQuery('.ganjeh-bi').length === 0) {
            jQuery('#ganjeh-bundle-items-list').html('<div style="color:#999;font-style:italic;padding:20px 0;text-align:center;"><?php echo esc_js(__("هنوز محصولی اضافه نشده است", "ganjeh")); ?></div>');
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
 * Render a single bundle item HTML in admin (PHP for initial load)
 */
function ganjeh_render_bundle_item_admin($item, $product, $index) {
    $id = $item['id'];
    $name = esc_html($product->get_name());
    ?>
    <div class="ganjeh-bi" data-id="<?php echo esc_attr($id); ?>" data-index="<?php echo esc_attr($index); ?>" style="margin:0 0 8px;background:#f9f9f9;border:1px solid #e2e4e7;border-radius:6px;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;cursor:pointer;" onclick="ganjehToggleSettings(this)">
            <div style="display:flex;align-items:center;gap:10px;flex:1;">
                <span style="background:#ddd;padding:2px 8px;border-radius:4px;font-size:12px;color:#555;font-weight:600;">#<?php echo $id; ?></span>
                <span style="font-weight:500;color:#1d2327;"><?php echo $name; ?></span>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="color:#2271b1;font-size:13px;cursor:pointer;">⚙ <?php _e('تنظیمات', 'ganjeh'); ?></span>
                <span style="color:#b32d2e;font-weight:500;cursor:pointer;" onclick="event.stopPropagation();ganjehRemoveBundleItem(this);return false;"><?php _e('پاک کردن', 'ganjeh'); ?></span>
            </div>
        </div>
        <div class="ganjeh-bi-settings" style="display:none;padding:16px;border-top:1px solid #e2e4e7;background:#fff;">
            <div style="display:flex;gap:0;border-bottom:1px solid #e2e4e7;margin-bottom:12px;">
                <div class="ganjeh-stab active" onclick="ganjehSwitchTab(this,'basic')" style="padding:8px 16px;cursor:pointer;font-size:13px;font-weight:500;color:#2271b1;border-bottom:2px solid #2271b1;"><?php _e('تنظیمات اولیه', 'ganjeh'); ?></div>
                <div class="ganjeh-stab" onclick="ganjehSwitchTab(this,'advanced')" style="padding:8px 16px;cursor:pointer;font-size:13px;font-weight:500;color:#646970;border-bottom:2px solid transparent;"><?php _e('تنظیمات پیشرفته', 'ganjeh'); ?></div>
            </div>
            <div class="ganjeh-stab-content" data-tab="basic">
                <?php
                ganjeh_setting_row($index, 'filter_variations', __('تغییرات فیلتر', 'ganjeh'), 'checkbox', $item['filter_variations']);
                ganjeh_setting_row($index, 'override_defaults', __('لغو انتخاب های پیش فرض', 'ganjeh'), 'checkbox', $item['override_defaults']);
                ganjeh_setting_row($index, 'layout', __('نوع نمایش', 'ganjeh'), 'select', $item['layout']);
                ganjeh_setting_row($index, 'min_qty', __('حداقل مقدار', 'ganjeh'), 'number', $item['min_qty']);
                ganjeh_setting_row($index, 'max_qty', __('حداکثر مقدار', 'ganjeh'), 'number', $item['max_qty']);
                ganjeh_setting_row($index, 'default_qty', __('مقدار پیشفرض', 'ganjeh'), 'number', $item['default_qty']);
                ganjeh_setting_row($index, 'optional', __('اختیاری', 'ganjeh'), 'checkbox', $item['optional']);
                ganjeh_setting_row($index, 'priced_individually', __('قیمت به صورت تکی', 'ganjeh'), 'checkbox', $item['priced_individually']);
                ganjeh_setting_row($index, 'discount', __('تخفیف %', 'ganjeh'), 'number', $item['discount']);
                ?>
            </div>
            <div class="ganjeh-stab-content" data-tab="advanced" style="display:none;">
                <p style="color:#999;font-style:italic;text-align:center;padding:10px 0;"><?php _e('تنظیمات پیشرفته در آینده اضافه خواهد شد', 'ganjeh'); ?></p>
            </div>
        </div>
    </div>
    <?php
}

function ganjeh_setting_row($index, $key, $label, $type, $value) {
    ?>
    <div style="display:flex;align-items:center;padding:8px 0;border-bottom:1px solid #f0f0f1;">
        <span style="flex:1;font-size:13px;color:#1d2327;font-weight:500;text-align:right;padding-left:15px;"><?php echo esc_html($label); ?></span>
        <span>
            <?php if ($type === 'checkbox') : ?>
                <input type="checkbox" <?php checked($value); ?> onchange="ganjehUpdateItemSetting(<?php echo $index; ?>,'<?php echo $key; ?>',this.checked)">
            <?php elseif ($type === 'number') : ?>
                <input type="number" min="0" step="1" value="<?php echo esc_attr($value); ?>" style="width:70px;text-align:center;" onchange="ganjehUpdateItemSetting(<?php echo $index; ?>,'<?php echo $key; ?>',parseFloat(this.value)||0)">
            <?php elseif ($type === 'select') : ?>
                <select style="width:130px;" onchange="ganjehUpdateItemSetting(<?php echo $index; ?>,'<?php echo $key; ?>',this.value)">
                    <option value="classic" <?php selected($value, 'classic'); ?>><?php _e('کلاسیک', 'ganjeh'); ?></option>
                    <option value="grid" <?php selected($value, 'grid'); ?>><?php _e('شبکه‌ای', 'ganjeh'); ?></option>
                </select>
            <?php endif; ?>
        </span>
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
            $ids = array_map(function($i) { return $i['id']; }, $sanitized);
            update_post_meta($post_id, '_ganjeh_bundle_items', $ids);
        }
    }
}
add_action('woocommerce_process_product_meta', 'ganjeh_save_bundle_items');
