<?php
/**
 * Product Weight & Dimensions Management
 * صفحه مدیریت وزن و ابعاد محصولات
 *
 * @package Ganjeh
 * @version 1.1.0
 */

defined('ABSPATH') || exit;

/**
 * Add admin menu for product weight and dimensions
 */
function ganjeh_add_weight_menu() {
    add_menu_page(
        __('وزن و ابعاد', 'ganjeh'),
        __('وزن و ابعاد', 'ganjeh'),
        'manage_woocommerce',
        'ganjeh-weight',
        'ganjeh_render_weight_page',
        'dashicons-scale',
        57
    );
}
add_action('admin_menu', 'ganjeh_add_weight_menu');

/**
 * Render the weight management page
 */
function ganjeh_render_weight_page() {
    // Get pagination parameters
    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
    $per_page = 50;

    // Get search parameter
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

    // Get filter parameter
    $filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : 'all';

    // Query products
    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ];

    // Add search
    if (!empty($search)) {
        $args['s'] = $search;
    }

    // Filter by weight/dimensions status
    if ($filter === 'no_weight') {
        $args['meta_query'] = [
            'relation' => 'OR',
            [
                'key'     => '_weight',
                'compare' => 'NOT EXISTS'
            ],
            [
                'key'     => '_weight',
                'value'   => '',
                'compare' => '='
            ],
            [
                'key'     => '_weight',
                'value'   => '0',
                'compare' => '='
            ]
        ];
    } elseif ($filter === 'no_dimensions') {
        $args['meta_query'] = [
            'relation' => 'OR',
            [
                'key'     => '_length',
                'compare' => 'NOT EXISTS'
            ],
            [
                'key'     => '_length',
                'value'   => '',
                'compare' => '='
            ],
            [
                'key'     => '_width',
                'compare' => 'NOT EXISTS'
            ],
            [
                'key'     => '_width',
                'value'   => '',
                'compare' => '='
            ],
            [
                'key'     => '_height',
                'compare' => 'NOT EXISTS'
            ],
            [
                'key'     => '_height',
                'value'   => '',
                'compare' => '='
            ]
        ];
    } elseif ($filter === 'has_weight') {
        $args['meta_query'] = [
            [
                'key'     => '_weight',
                'value'   => ['', '0'],
                'compare' => 'NOT IN'
            ]
        ];
    } elseif ($filter === 'instock') {
        $args['meta_query'] = [
            [
                'key'     => '_stock_status',
                'value'   => 'instock',
                'compare' => '='
            ]
        ];
    }

    $products_query = new WP_Query($args);
    $total_products = $products_query->found_posts;
    $total_pages = ceil($total_products / $per_page);

    // Count products without weight
    $no_weight_args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => [
            'relation' => 'OR',
            [
                'key'     => '_weight',
                'compare' => 'NOT EXISTS'
            ],
            [
                'key'     => '_weight',
                'value'   => '',
                'compare' => '='
            ],
            [
                'key'     => '_weight',
                'value'   => '0',
                'compare' => '='
            ]
        ]
    ];
    $no_weight_count = count(get_posts($no_weight_args));

    // Count products without dimensions
    $no_dimensions_args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => [
            'relation' => 'OR',
            [
                'key'     => '_length',
                'compare' => 'NOT EXISTS'
            ],
            [
                'key'     => '_length',
                'value'   => '',
                'compare' => '='
            ],
            [
                'key'     => '_width',
                'compare' => 'NOT EXISTS'
            ],
            [
                'key'     => '_width',
                'value'   => '',
                'compare' => '='
            ],
            [
                'key'     => '_height',
                'compare' => 'NOT EXISTS'
            ],
            [
                'key'     => '_height',
                'value'   => '',
                'compare' => '='
            ]
        ]
    ];
    $no_dimensions_count = count(get_posts($no_dimensions_args));

    ?>
    <div class="wrap ganjeh-weight-wrap">
        <h1>
            <span class="dashicons dashicons-scale"></span>
            <?php _e('مدیریت وزن و ابعاد محصولات', 'ganjeh'); ?>
        </h1>

        <div class="weight-stats">
            <div class="stat-box">
                <span class="stat-number"><?php echo number_format_i18n($total_products); ?></span>
                <span class="stat-label"><?php _e('کل محصولات', 'ganjeh'); ?></span>
            </div>
            <div class="stat-box warning">
                <span class="stat-number"><?php echo number_format_i18n($no_weight_count); ?></span>
                <span class="stat-label"><?php _e('بدون وزن', 'ganjeh'); ?></span>
            </div>
            <div class="stat-box warning">
                <span class="stat-number"><?php echo number_format_i18n($no_dimensions_count); ?></span>
                <span class="stat-label"><?php _e('بدون ابعاد', 'ganjeh'); ?></span>
            </div>
        </div>

        <div class="weight-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="ganjeh-weight">

                <div class="filter-row">
                    <div class="search-box">
                        <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('جستجوی محصول...', 'ganjeh'); ?>">
                        <button type="submit" class="button"><?php _e('جستجو', 'ganjeh'); ?></button>
                    </div>

                    <div class="filter-buttons">
                        <a href="?page=ganjeh-weight" class="button <?php echo $filter === 'all' ? 'button-primary' : ''; ?>">
                            <?php _e('همه', 'ganjeh'); ?>
                        </a>
                        <a href="?page=ganjeh-weight&filter=no_weight" class="button <?php echo $filter === 'no_weight' ? 'button-primary' : ''; ?>">
                            <?php _e('بدون وزن', 'ganjeh'); ?>
                        </a>
                        <a href="?page=ganjeh-weight&filter=no_dimensions" class="button <?php echo $filter === 'no_dimensions' ? 'button-primary' : ''; ?>">
                            <?php _e('بدون ابعاد', 'ganjeh'); ?>
                        </a>
                        <a href="?page=ganjeh-weight&filter=has_weight" class="button <?php echo $filter === 'has_weight' ? 'button-primary' : ''; ?>">
                            <?php _e('دارای وزن', 'ganjeh'); ?>
                        </a>
                        <a href="?page=ganjeh-weight&filter=instock" class="button <?php echo $filter === 'instock' ? 'button-primary' : ''; ?>">
                            <?php _e('موجود', 'ganjeh'); ?>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="inventory-notice" id="save-notice" style="display: none;">
            <span class="dashicons dashicons-yes-alt"></span>
            <span class="notice-text"><?php _e('ذخیره شد', 'ganjeh'); ?></span>
        </div>

        <table class="wp-list-table widefat fixed striped ganjeh-weight-table">
            <thead>
                <tr>
                    <th class="column-id"><?php _e('شناسه', 'ganjeh'); ?></th>
                    <th class="column-image"><?php _e('تصویر', 'ganjeh'); ?></th>
                    <th class="column-name"><?php _e('نام محصول', 'ganjeh'); ?></th>
                    <th class="column-weight"><?php _e('وزن (گرم)', 'ganjeh'); ?></th>
                    <th class="column-dimension"><?php _e('طول (cm)', 'ganjeh'); ?></th>
                    <th class="column-dimension"><?php _e('عرض (cm)', 'ganjeh'); ?></th>
                    <th class="column-dimension"><?php _e('ارتفاع (cm)', 'ganjeh'); ?></th>
                    <th class="column-status"><?php _e('وضعیت', 'ganjeh'); ?></th>
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

                        $weight = $product->get_weight();
                        $length = $product->get_length();
                        $width = $product->get_width();
                        $height = $product->get_height();
                        $image = $product->get_image([50, 50]);
                        $has_weight = !empty($weight) && floatval($weight) > 0;
                        $has_dimensions = !empty($length) && !empty($width) && !empty($height);
                        $is_complete = $has_weight && $has_dimensions;
                        ?>
                        <tr data-product-id="<?php echo esc_attr($product_id); ?>" class="<?php echo !$is_complete ? 'no-weight-row' : ''; ?>">
                            <td class="column-id">
                                <strong>#<?php echo esc_html($product_id); ?></strong>
                            </td>
                            <td class="column-image">
                                <?php echo $image; ?>
                            </td>
                            <td class="column-name">
                                <a href="<?php echo get_edit_post_link($product_id); ?>" target="_blank">
                                    <?php echo esc_html($product->get_name()); ?>
                                </a>
                            </td>
                            <td class="column-weight">
                                <input type="number"
                                       class="dimension-input weight-input"
                                       name="weight"
                                       value="<?php echo esc_attr($weight); ?>"
                                       step="1"
                                       min="0"
                                       data-product-id="<?php echo esc_attr($product_id); ?>"
                                       data-field="weight"
                                       data-original="<?php echo esc_attr($weight); ?>"
                                       placeholder="0">
                            </td>
                            <td class="column-dimension">
                                <input type="number"
                                       class="dimension-input"
                                       name="length"
                                       value="<?php echo esc_attr($length); ?>"
                                       step="0.1"
                                       min="0"
                                       data-product-id="<?php echo esc_attr($product_id); ?>"
                                       data-field="length"
                                       data-original="<?php echo esc_attr($length); ?>"
                                       placeholder="0">
                            </td>
                            <td class="column-dimension">
                                <input type="number"
                                       class="dimension-input"
                                       name="width"
                                       value="<?php echo esc_attr($width); ?>"
                                       step="0.1"
                                       min="0"
                                       data-product-id="<?php echo esc_attr($product_id); ?>"
                                       data-field="width"
                                       data-original="<?php echo esc_attr($width); ?>"
                                       placeholder="0">
                            </td>
                            <td class="column-dimension">
                                <input type="number"
                                       class="dimension-input"
                                       name="height"
                                       value="<?php echo esc_attr($height); ?>"
                                       step="0.1"
                                       min="0"
                                       data-product-id="<?php echo esc_attr($product_id); ?>"
                                       data-field="height"
                                       data-original="<?php echo esc_attr($height); ?>"
                                       placeholder="0">
                            </td>
                            <td class="column-status">
                                <?php if ($is_complete) : ?>
                                    <span class="status-badge status-ok">
                                        <span class="dashicons dashicons-yes"></span>
                                        <?php _e('تکمیل', 'ganjeh'); ?>
                                    </span>
                                <?php elseif (!$has_weight && !$has_dimensions) : ?>
                                    <span class="status-badge status-missing">
                                        <span class="dashicons dashicons-warning"></span>
                                        <?php _e('ناقص', 'ganjeh'); ?>
                                    </span>
                                <?php elseif (!$has_weight) : ?>
                                    <span class="status-badge status-partial">
                                        <span class="dashicons dashicons-minus"></span>
                                        <?php _e('بدون وزن', 'ganjeh'); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="status-badge status-partial">
                                        <span class="dashicons dashicons-minus"></span>
                                        <?php _e('بدون ابعاد', 'ganjeh'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <tr>
                        <td colspan="8" class="no-products">
                            <?php _e('هیچ محصولی یافت نشد.', 'ganjeh'); ?>
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
                    $pagination_args = [
                        'base'      => add_query_arg('paged', '%#%'),
                        'format'    => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total'     => $total_pages,
                        'current'   => $paged,
                    ];
                    echo paginate_links($pagination_args);
                    ?>
                </span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <style>
        .ganjeh-weight-wrap {
            max-width: 1400px;
        }
        .ganjeh-weight-wrap h1 {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 23px;
            margin-bottom: 20px;
        }
        .ganjeh-weight-wrap h1 .dashicons {
            font-size: 28px;
            width: 28px;
            height: 28px;
            color: #2271b1;
        }

        /* Stats */
        .weight-stats {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: white;
            border: 1px solid #dcdcde;
            border-radius: 8px;
            padding: 15px 25px;
            text-align: center;
        }
        .stat-box.warning {
            border-color: #dba617;
            background: #fcf9e8;
        }
        .stat-number {
            display: block;
            font-size: 28px;
            font-weight: 600;
            color: #1d2327;
        }
        .stat-box.warning .stat-number {
            color: #996800;
        }
        .stat-label {
            font-size: 13px;
            color: #646970;
        }

        /* Filters */
        .weight-filters {
            background: white;
            border: 1px solid #dcdcde;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .filter-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .search-box {
            display: flex;
            gap: 8px;
        }
        .search-box input[type="search"] {
            width: 250px;
            padding: 6px 12px;
        }
        .filter-buttons {
            display: flex;
            gap: 8px;
        }

        /* Notice */
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

        /* Table */
        .ganjeh-weight-table {
            background: white;
        }
        .ganjeh-weight-table th {
            font-weight: 600;
            background: #f6f7f7;
            padding: 12px 15px;
        }
        .ganjeh-weight-table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        .ganjeh-weight-table .column-id {
            width: 80px;
        }
        .ganjeh-weight-table .column-image {
            width: 60px;
        }
        .ganjeh-weight-table .column-image img {
            border-radius: 4px;
            width: 50px;
            height: 50px;
            object-fit: cover;
        }
        .ganjeh-weight-table .column-name {
            width: 25%;
        }
        .ganjeh-weight-table .column-name a {
            color: #2271b1;
            text-decoration: none;
            font-weight: 500;
        }
        .ganjeh-weight-table .column-name a:hover {
            color: #135e96;
            text-decoration: underline;
        }
        .ganjeh-weight-table .column-weight {
            width: 100px;
        }
        .ganjeh-weight-table .column-dimension {
            width: 90px;
        }
        .ganjeh-weight-table .column-status {
            width: 110px;
        }

        .no-weight-row {
            background: #fff8e5 !important;
        }
        .no-sku {
            color: #ccc;
        }

        /* Dimension Input */
        .dimension-input {
            width: 70px;
            padding: 6px 8px;
            border: 2px solid #dcdcde;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            transition: all 0.2s;
        }
        .dimension-input:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: none;
        }
        .dimension-input.changed {
            border-color: #dba617;
            background: #fcf9e8;
        }
        .dimension-input.saving {
            opacity: 0.6;
            pointer-events: none;
        }
        .dimension-input.saved {
            border-color: #00a32a;
            background: #edfaef;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-badge .dashicons {
            font-size: 14px;
            width: 14px;
            height: 14px;
        }
        .status-ok {
            background: #edfaef;
            color: #00a32a;
        }
        .status-partial {
            background: #fcf9e8;
            color: #996800;
        }
        .status-missing {
            background: #fcf0f1;
            color: #d63638;
        }

        .no-products {
            text-align: center;
            color: #646970;
            padding: 40px !important;
        }

        /* Pagination */
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
        $('.dimension-input').on('input', function() {
            var $input = $(this);
            var productId = $input.data('product-id');
            var field = $input.data('field');
            var original = $input.data('original');
            var current = $input.val();
            var timeoutKey = productId + '_' + field;

            // Mark as changed if different from original
            if (current !== String(original)) {
                $input.addClass('changed').removeClass('saved');
            } else {
                $input.removeClass('changed');
            }

            // Clear previous timeout for this field
            if (saveTimeout[timeoutKey]) {
                clearTimeout(saveTimeout[timeoutKey]);
            }

            // Set new timeout to save after 800ms of no typing
            saveTimeout[timeoutKey] = setTimeout(function() {
                saveField($input);
            }, 800);
        });

        // Handle blur (when leaving the input)
        $('.dimension-input').on('blur', function() {
            var $input = $(this);
            if ($input.hasClass('changed')) {
                saveField($input);
            }
        });

        function saveField($input) {
            var productId = $input.data('product-id');
            var field = $input.data('field');
            var value = $input.val();
            var $row = $input.closest('tr');

            // Don't save if not changed
            if (!$input.hasClass('changed')) {
                return;
            }

            $input.addClass('saving');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ganjeh_update_product_dimensions',
                    product_id: productId,
                    field: field,
                    value: value,
                    nonce: '<?php echo wp_create_nonce('ganjeh_dimensions_nonce'); ?>'
                },
                success: function(response) {
                    $input.removeClass('saving changed');

                    if (response.success) {
                        $input.addClass('saved').data('original', value);
                        showNotice('<?php _e('ذخیره شد', 'ganjeh'); ?>');

                        // Update status badge based on all fields
                        updateRowStatus($row);

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

        function updateRowStatus($row) {
            var weight = $row.find('[data-field="weight"]').val();
            var length = $row.find('[data-field="length"]').val();
            var width = $row.find('[data-field="width"]').val();
            var height = $row.find('[data-field="height"]').val();
            var $statusCell = $row.find('.column-status');

            var hasWeight = weight && parseFloat(weight) > 0;
            var hasDimensions = length && width && height && parseFloat(length) > 0 && parseFloat(width) > 0 && parseFloat(height) > 0;

            if (hasWeight && hasDimensions) {
                $statusCell.html('<span class="status-badge status-ok"><span class="dashicons dashicons-yes"></span><?php _e('تکمیل', 'ganjeh'); ?></span>');
                $row.removeClass('no-weight-row');
            } else if (!hasWeight && !hasDimensions) {
                $statusCell.html('<span class="status-badge status-missing"><span class="dashicons dashicons-warning"></span><?php _e('ناقص', 'ganjeh'); ?></span>');
                $row.addClass('no-weight-row');
            } else if (!hasWeight) {
                $statusCell.html('<span class="status-badge status-partial"><span class="dashicons dashicons-minus"></span><?php _e('بدون وزن', 'ganjeh'); ?></span>');
                $row.addClass('no-weight-row');
            } else {
                $statusCell.html('<span class="status-badge status-partial"><span class="dashicons dashicons-minus"></span><?php _e('بدون ابعاد', 'ganjeh'); ?></span>');
                $row.addClass('no-weight-row');
            }
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
 * AJAX handler for updating product weight and dimensions
 */
function ganjeh_ajax_update_product_dimensions() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'ganjeh_dimensions_nonce')) {
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

    $allowed_fields = ['weight', 'length', 'width', 'height'];
    if (!in_array($field, $allowed_fields)) {
        wp_send_json_error(['message' => __('فیلد نامعتبر', 'ganjeh')]);
    }

    $product = wc_get_product($product_id);

    if (!$product) {
        wp_send_json_error(['message' => __('محصول یافت نشد', 'ganjeh')]);
    }

    // Update the field - values are stored in WooCommerce's configured unit (grams/cm)
    switch ($field) {
        case 'weight':
            $product->set_weight($value);
            break;
        case 'length':
            $product->set_length($value);
            break;
        case 'width':
            $product->set_width($value);
            break;
        case 'height':
            $product->set_height($value);
            break;
    }

    $product->save();

    wp_send_json_success([
        'message' => __('ذخیره شد', 'ganjeh'),
        'product_id' => $product_id,
        'field' => $field,
        'value' => $value
    ]);
}
add_action('wp_ajax_ganjeh_update_product_dimensions', 'ganjeh_ajax_update_product_dimensions');
