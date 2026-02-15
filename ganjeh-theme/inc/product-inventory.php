<?php
/**
 * Product Inventory Management
 * صفحه مدیریت موجودی محصولات با قابلیت ویرایش تعداد موجودی، وزن و ابعاد
 * پشتیبانی کامل از محصولات پک/باندل
 *
 * @package Ganjeh
 * @version 1.1.0
 */

defined('ABSPATH') || exit;

/**
 * Add admin menu for product inventory
 */
function ganjeh_add_inventory_menu() {
    add_menu_page(
        __('موجودی محصولات', 'ganjeh'),
        __('موجودی محصولات', 'ganjeh'),
        'manage_woocommerce',
        'ganjeh-inventory',
        'ganjeh_render_inventory_page',
        'dashicons-clipboard',
        56
    );
}
add_action('admin_menu', 'ganjeh_add_inventory_menu');

/**
 * Render the inventory page
 */
function ganjeh_render_inventory_page() {
    // Get pagination parameters
    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
    $per_page = 50;

    // Query in-stock products only
    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        'meta_query'     => [
            [
                'key'     => '_stock_status',
                'value'   => 'instock',
                'compare' => '='
            ]
        ],
        'orderby'        => 'ID',
        'order'          => 'DESC',
    ];

    $products_query = new WP_Query($args);
    $total_products = $products_query->found_posts;
    $total_pages = ceil($total_products / $per_page);

    ?>
    <div class="wrap ganjeh-inventory-wrap">
        <h1>
            <span class="dashicons dashicons-clipboard"></span>
            <?php _e('موجودی محصولات', 'ganjeh'); ?>
            <span class="inventory-count"><?php printf(__('%s محصول موجود', 'ganjeh'), number_format_i18n($total_products)); ?></span>
        </h1>

        <div class="inventory-notice" id="save-notice" style="display: none;">
            <span class="dashicons dashicons-yes-alt"></span>
            <span class="notice-text"><?php _e('تغییرات ذخیره شد', 'ganjeh'); ?></span>
        </div>

        <table class="wp-list-table widefat fixed striped ganjeh-inventory-table">
            <thead>
                <tr>
                    <th class="column-id"><?php _e('آیدی', 'ganjeh'); ?></th>
                    <th class="column-name"><?php _e('نام محصول', 'ganjeh'); ?></th>
                    <th class="column-type"><?php _e('نوع', 'ganjeh'); ?></th>
                    <th class="column-stock"><?php _e('تعداد موجودی', 'ganjeh'); ?></th>
                    <th class="column-weight"><?php _e('وزن (کیلوگرم)', 'ganjeh'); ?></th>
                    <th class="column-dimensions"><?php _e('ابعاد (سانتی‌متر)', 'ganjeh'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($products_query->have_posts()) :
                    while ($products_query->have_posts()) :
                        $products_query->the_post();
                        $product_id = get_the_ID();
                        $product = wc_get_product($product_id);

                        if (!$product) continue;

                        // Check if this is a bundle/pack product
                        $bundle_items = [];
                        if (function_exists('ganjeh_get_bundle_items')) {
                            $bundle_items = ganjeh_get_bundle_items($product_id);
                        }
                        $is_bundle = !empty($bundle_items);

                        $weight = get_post_meta($product_id, '_weight', true);
                        $length = get_post_meta($product_id, '_length', true);
                        $width  = get_post_meta($product_id, '_width', true);
                        $height = get_post_meta($product_id, '_height', true);
                        $stock  = get_post_meta($product_id, '_stock', true);
                        $manage_stock = get_post_meta($product_id, '_manage_stock', true);

                        $product_type = $product->get_type();
                        $type_label = $is_bundle ? __('پک', 'ganjeh') : '';
                        if (!$type_label) {
                            switch ($product_type) {
                                case 'simple': $type_label = __('ساده', 'ganjeh'); break;
                                case 'variable': $type_label = __('متغیر', 'ganjeh'); break;
                                case 'grouped': $type_label = __('گروهی', 'ganjeh'); break;
                                default: $type_label = $product_type;
                            }
                        }
                        ?>
                        <tr data-product-id="<?php echo esc_attr($product_id); ?>" class="<?php echo $is_bundle ? 'bundle-row' : ''; ?>">
                            <td class="column-id">
                                <strong><?php echo esc_html($product_id); ?></strong>
                            </td>
                            <td class="column-name">
                                <a href="<?php echo get_edit_post_link($product_id); ?>" target="_blank">
                                    <?php echo esc_html($product->get_name()); ?>
                                </a>
                                <?php if ($is_bundle) : ?>
                                    <span class="bundle-badge"><?php _e('پک', 'ganjeh'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="column-type">
                                <span class="type-badge type-<?php echo esc_attr($is_bundle ? 'bundle' : $product_type); ?>">
                                    <?php echo esc_html($type_label); ?>
                                </span>
                            </td>
                            <td class="column-stock">
                                <?php if ($manage_stock === 'yes') : ?>
                                    <input type="number"
                                           class="inventory-input stock-input"
                                           name="stock_quantity"
                                           value="<?php echo esc_attr($stock); ?>"
                                           step="1"
                                           min="0"
                                           data-product-id="<?php echo esc_attr($product_id); ?>"
                                           data-original="<?php echo esc_attr($stock); ?>"
                                           placeholder="0">
                                <?php else : ?>
                                    <span class="stock-na" title="<?php esc_attr_e('مدیریت موجودی فعال نیست', 'ganjeh'); ?>">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="column-weight">
                                <input type="number"
                                       class="inventory-input weight-input"
                                       name="weight"
                                       value="<?php echo esc_attr($weight); ?>"
                                       step="0.001"
                                       min="0"
                                       data-product-id="<?php echo esc_attr($product_id); ?>"
                                       data-original="<?php echo esc_attr($weight); ?>"
                                       placeholder="0">
                            </td>
                            <td class="column-dimensions">
                                <div class="dimensions-inputs">
                                    <div class="dimension-group">
                                        <label><?php _e('طول', 'ganjeh'); ?></label>
                                        <input type="number"
                                               class="inventory-input dimension-input"
                                               name="length"
                                               value="<?php echo esc_attr($length); ?>"
                                               step="0.01"
                                               min="0"
                                               data-product-id="<?php echo esc_attr($product_id); ?>"
                                               data-original="<?php echo esc_attr($length); ?>"
                                               placeholder="0">
                                    </div>
                                    <span class="dimension-separator">×</span>
                                    <div class="dimension-group">
                                        <label><?php _e('عرض', 'ganjeh'); ?></label>
                                        <input type="number"
                                               class="inventory-input dimension-input"
                                               name="width"
                                               value="<?php echo esc_attr($width); ?>"
                                               step="0.01"
                                               min="0"
                                               data-product-id="<?php echo esc_attr($product_id); ?>"
                                               data-original="<?php echo esc_attr($width); ?>"
                                               placeholder="0">
                                    </div>
                                    <span class="dimension-separator">×</span>
                                    <div class="dimension-group">
                                        <label><?php _e('ارتفاع', 'ganjeh'); ?></label>
                                        <input type="number"
                                               class="inventory-input dimension-input"
                                               name="height"
                                               value="<?php echo esc_attr($height); ?>"
                                               step="0.01"
                                               min="0"
                                               data-product-id="<?php echo esc_attr($product_id); ?>"
                                               data-original="<?php echo esc_attr($height); ?>"
                                               placeholder="0">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php
                        // Show bundle child products
                        if ($is_bundle) :
                            foreach ($bundle_items as $bundle_item) :
                                $child_id = absint($bundle_item['id']);
                                $child_product = wc_get_product($child_id);
                                if (!$child_product) continue;

                                $child_stock = get_post_meta($child_id, '_stock', true);
                                $child_manage = get_post_meta($child_id, '_manage_stock', true);
                                $child_weight = get_post_meta($child_id, '_weight', true);
                                $child_length = get_post_meta($child_id, '_length', true);
                                $child_width  = get_post_meta($child_id, '_width', true);
                                $child_height = get_post_meta($child_id, '_height', true);
                                $child_qty = isset($bundle_item['default_qty']) ? absint($bundle_item['default_qty']) : 1;
                                ?>
                                <tr data-product-id="<?php echo esc_attr($child_id); ?>" class="bundle-child-row">
                                    <td class="column-id">
                                        <span class="child-indent">↳</span>
                                        <?php echo esc_html($child_id); ?>
                                    </td>
                                    <td class="column-name">
                                        <a href="<?php echo get_edit_post_link($child_id); ?>" target="_blank" class="child-name">
                                            <?php echo esc_html($child_product->get_name()); ?>
                                        </a>
                                        <span class="child-qty-badge">×<?php echo $child_qty; ?></span>
                                    </td>
                                    <td class="column-type">
                                        <span class="type-badge type-child"><?php _e('زیرمحصول', 'ganjeh'); ?></span>
                                    </td>
                                    <td class="column-stock">
                                        <?php if ($child_manage === 'yes') : ?>
                                            <input type="number"
                                                   class="inventory-input stock-input"
                                                   name="stock_quantity"
                                                   value="<?php echo esc_attr($child_stock); ?>"
                                                   step="1"
                                                   min="0"
                                                   data-product-id="<?php echo esc_attr($child_id); ?>"
                                                   data-original="<?php echo esc_attr($child_stock); ?>"
                                                   placeholder="0">
                                        <?php else : ?>
                                            <span class="stock-na">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="column-weight">
                                        <input type="number"
                                               class="inventory-input weight-input"
                                               name="weight"
                                               value="<?php echo esc_attr($child_weight); ?>"
                                               step="0.001"
                                               min="0"
                                               data-product-id="<?php echo esc_attr($child_id); ?>"
                                               data-original="<?php echo esc_attr($child_weight); ?>"
                                               placeholder="0">
                                    </td>
                                    <td class="column-dimensions">
                                        <div class="dimensions-inputs">
                                            <div class="dimension-group">
                                                <label><?php _e('طول', 'ganjeh'); ?></label>
                                                <input type="number"
                                                       class="inventory-input dimension-input"
                                                       name="length"
                                                       value="<?php echo esc_attr($child_length); ?>"
                                                       step="0.01"
                                                       min="0"
                                                       data-product-id="<?php echo esc_attr($child_id); ?>"
                                                       data-original="<?php echo esc_attr($child_length); ?>"
                                                       placeholder="0">
                                            </div>
                                            <span class="dimension-separator">×</span>
                                            <div class="dimension-group">
                                                <label><?php _e('عرض', 'ganjeh'); ?></label>
                                                <input type="number"
                                                       class="inventory-input dimension-input"
                                                       name="width"
                                                       value="<?php echo esc_attr($child_width); ?>"
                                                       step="0.01"
                                                       min="0"
                                                       data-product-id="<?php echo esc_attr($child_id); ?>"
                                                       data-original="<?php echo esc_attr($child_width); ?>"
                                                       placeholder="0">
                                            </div>
                                            <span class="dimension-separator">×</span>
                                            <div class="dimension-group">
                                                <label><?php _e('ارتفاع', 'ganjeh'); ?></label>
                                                <input type="number"
                                                       class="inventory-input dimension-input"
                                                       name="height"
                                                       value="<?php echo esc_attr($child_height); ?>"
                                                       step="0.01"
                                                       min="0"
                                                       data-product-id="<?php echo esc_attr($child_id); ?>"
                                                       data-original="<?php echo esc_attr($child_height); ?>"
                                                       placeholder="0">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            endforeach;
                        endif;
                        ?>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <tr>
                        <td colspan="6" class="no-products">
                            <?php _e('هیچ محصول موجودی یافت نشد.', 'ganjeh'); ?>
                        </td>
                    </tr>
                    <?php
                endif;
                ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1) : ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php printf(__('%s محصول', 'ganjeh'), number_format_i18n($total_products)); ?>
                </span>
                <span class="pagination-links">
                    <?php
                    echo paginate_links([
                        'base'      => add_query_arg('paged', '%#%'),
                        'format'    => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total'     => $total_pages,
                        'current'   => $paged,
                    ]);
                    ?>
                </span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <style>
        .ganjeh-inventory-wrap {
            max-width: 1600px;
        }
        .ganjeh-inventory-wrap h1 {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 23px;
            margin-bottom: 20px;
        }
        .ganjeh-inventory-wrap h1 .dashicons {
            font-size: 28px;
            width: 28px;
            height: 28px;
            color: #2271b1;
        }
        .inventory-count {
            font-size: 14px;
            font-weight: normal;
            color: #50575e;
            background: #f0f0f1;
            padding: 4px 12px;
            border-radius: 4px;
            margin-right: auto;
        }
        .inventory-notice {
            position: fixed;
            top: 40px;
            left: 50%;
            transform: translateX(-50%);
            background: #00a32a;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from { top: 20px; opacity: 0; }
            to { top: 40px; opacity: 1; }
        }
        .ganjeh-inventory-table {
            margin-top: 15px;
        }
        .ganjeh-inventory-table th {
            font-weight: 600;
            background: #f6f7f7;
            padding: 12px 15px;
        }
        .ganjeh-inventory-table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        .ganjeh-inventory-table .column-id {
            width: 80px;
        }
        .ganjeh-inventory-table .column-name {
            width: 25%;
        }
        .ganjeh-inventory-table .column-type {
            width: 90px;
        }
        .ganjeh-inventory-table .column-stock {
            width: 120px;
        }
        .ganjeh-inventory-table .column-weight {
            width: 120px;
        }
        .ganjeh-inventory-table .column-dimensions {
            width: auto;
        }
        .ganjeh-inventory-table .column-name a {
            color: #2271b1;
            text-decoration: none;
            font-weight: 500;
        }
        .ganjeh-inventory-table .column-name a:hover {
            color: #135e96;
            text-decoration: underline;
        }
        .inventory-input {
            width: 80px;
            padding: 6px 10px;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
            transition: all 0.2s;
        }
        .inventory-input:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: none;
        }
        .inventory-input.changed {
            border-color: #dba617;
            background: #fcf9e8;
        }
        .inventory-input.saving {
            opacity: 0.6;
            pointer-events: none;
        }
        .inventory-input.saved {
            border-color: #00a32a;
            background: #edfaef;
        }
        .stock-input {
            width: 90px;
            font-weight: 600;
        }
        .stock-na {
            color: #999;
            font-style: italic;
            cursor: help;
        }
        .dimensions-inputs {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .dimension-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .dimension-group label {
            font-size: 11px;
            color: #646970;
            text-align: center;
        }
        .dimension-separator {
            color: #999;
            font-size: 16px;
            margin-top: 18px;
        }
        /* Bundle/Pack styles */
        .bundle-row {
            background: #f0f6fc !important;
        }
        .bundle-badge {
            display: inline-block;
            background: #2271b1;
            color: white;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 3px;
            margin-right: 6px;
            font-weight: 600;
        }
        .bundle-child-row {
            background: #fafafa !important;
        }
        .bundle-child-row td {
            padding-top: 8px !important;
            padding-bottom: 8px !important;
        }
        .child-indent {
            color: #2271b1;
            font-size: 16px;
            margin-left: 4px;
        }
        .child-name {
            color: #646970 !important;
            font-size: 13px;
        }
        .child-qty-badge {
            display: inline-block;
            background: #e2e4e7;
            color: #50575e;
            font-size: 11px;
            padding: 1px 6px;
            border-radius: 3px;
            margin-right: 4px;
            font-weight: 600;
        }
        .type-badge {
            display: inline-block;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: 500;
        }
        .type-bundle {
            background: #e0edff;
            color: #1d4ed8;
        }
        .type-simple {
            background: #ecfdf5;
            color: #065f46;
        }
        .type-variable {
            background: #fef3c7;
            color: #92400e;
        }
        .type-grouped {
            background: #f3e8ff;
            color: #6b21a8;
        }
        .type-child {
            background: #f1f5f9;
            color: #475569;
        }
        .no-products {
            text-align: center;
            color: #646970;
            padding: 40px !important;
        }
        .tablenav-pages {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
        }
        .tablenav-pages .pagination-links a,
        .tablenav-pages .pagination-links span {
            padding: 5px 10px;
            background: #f0f0f1;
            border: 1px solid #dcdcde;
            text-decoration: none;
        }
        .tablenav-pages .pagination-links .current {
            background: #2271b1;
            border-color: #2271b1;
            color: white;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        var saveTimeout = {};

        // Handle input changes
        $('.inventory-input').on('input', function() {
            var $input = $(this);
            var productId = $input.data('product-id');
            var original = $input.data('original');
            var current = $input.val();

            // Mark as changed if different from original
            if (current !== String(original || '')) {
                $input.addClass('changed').removeClass('saved');
            } else {
                $input.removeClass('changed');
            }

            // Clear previous timeout for this product
            var key = productId + '_' + $input.attr('name');
            if (saveTimeout[key]) {
                clearTimeout(saveTimeout[key]);
            }

            // Set new timeout to save after 800ms of no typing
            saveTimeout[key] = setTimeout(function() {
                saveProductData($input);
            }, 800);
        });

        // Handle blur (when leaving the input)
        $('.inventory-input').on('blur', function() {
            var $input = $(this);
            if ($input.hasClass('changed')) {
                saveProductData($input);
            }
        });

        function saveProductData($input) {
            var productId = $input.data('product-id');
            var field = $input.attr('name');
            var value = $input.val();

            // Don't save if not changed
            if (!$input.hasClass('changed')) {
                return;
            }

            $input.addClass('saving');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ganjeh_update_product_inventory',
                    product_id: productId,
                    field: field,
                    value: value,
                    nonce: '<?php echo wp_create_nonce('ganjeh_inventory_nonce'); ?>'
                },
                success: function(response) {
                    $input.removeClass('saving changed');

                    if (response.success) {
                        $input.addClass('saved').data('original', value);
                        showNotice('<?php _e('تغییرات ذخیره شد', 'ganjeh'); ?>');

                        // Remove saved class after 2 seconds
                        setTimeout(function() {
                            $input.removeClass('saved');
                        }, 2000);
                    } else {
                        alert(response.data.message || '<?php _e('خطا در ذخیره', 'ganjeh'); ?>');
                        $input.addClass('changed');
                    }
                },
                error: function() {
                    $input.removeClass('saving');
                    alert('<?php _e('خطا در ارتباط با سرور', 'ganjeh'); ?>');
                    $input.addClass('changed');
                }
            });
        }

        function showNotice(message) {
            var $notice = $('#save-notice');
            $notice.find('.notice-text').text(message);
            $notice.fadeIn(200);

            setTimeout(function() {
                $notice.fadeOut(200);
            }, 2000);
        }
    });
    </script>
    <?php
}

/**
 * AJAX handler for updating product inventory data
 * Uses update_post_meta directly for reliable saving across all product types
 * (including grouped, bundle, and pack products)
 */
function ganjeh_ajax_update_product_inventory() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'ganjeh_inventory_nonce')) {
        wp_send_json_error(['message' => __('خطای امنیتی', 'ganjeh')]);
    }

    // Check capabilities
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => __('دسترسی غیرمجاز', 'ganjeh')]);
    }

    $product_id = absint($_POST['product_id']);
    $field = sanitize_text_field($_POST['field']);
    $value = sanitize_text_field($_POST['value']);

    if (!$product_id) {
        wp_send_json_error(['message' => __('محصول نامعتبر', 'ganjeh')]);
    }

    // Verify product exists
    if (get_post_type($product_id) !== 'product') {
        wp_send_json_error(['message' => __('محصول یافت نشد', 'ganjeh')]);
    }

    // Map field names to meta keys and update directly via post meta
    // This bypasses WC product API issues with grouped/bundle product types
    $meta_map = [
        'weight'         => '_weight',
        'length'         => '_length',
        'width'          => '_width',
        'height'         => '_height',
        'stock_quantity' => '_stock',
    ];

    if (!isset($meta_map[$field])) {
        wp_send_json_error(['message' => __('فیلد نامعتبر', 'ganjeh')]);
    }

    $meta_key = $meta_map[$field];

    // Format value
    if ($field === 'stock_quantity') {
        $clean_value = intval($value);
    } else {
        $clean_value = $value !== '' ? wc_format_decimal($value) : '';
    }

    // Update post meta directly
    update_post_meta($product_id, $meta_key, $clean_value);

    // For stock quantity, also update stock status
    if ($field === 'stock_quantity') {
        $new_status = $clean_value > 0 ? 'instock' : 'outofstock';
        update_post_meta($product_id, '_stock_status', $new_status);
    }

    // Clear WooCommerce product cache so changes are reflected immediately
    if (function_exists('wc_delete_product_transients')) {
        wc_delete_product_transients($product_id);
    }
    clean_post_cache($product_id);

    wp_send_json_success([
        'message' => __('ذخیره شد', 'ganjeh'),
        'product_id' => $product_id,
        'field' => $field,
        'value' => $clean_value
    ]);
}
add_action('wp_ajax_ganjeh_update_product_inventory', 'ganjeh_ajax_update_product_inventory');
