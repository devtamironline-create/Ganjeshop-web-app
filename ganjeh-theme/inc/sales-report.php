<?php
/**
 * Sales Report - Per Product (Single Item) with Excel Export
 * گزارش فروش تک قلم محصولات به تفکیک ماه با خروجی اکسل
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

/**
 * Add admin menu for sales report
 */
function ganjeh_add_sales_report_menu() {
    add_menu_page(
        __('گزارش فروش', 'ganjeh'),
        __('گزارش فروش', 'ganjeh'),
        'manage_woocommerce',
        'ganjeh-sales-report',
        'ganjeh_render_sales_report_page',
        'dashicons-chart-bar',
        57
    );
}
add_action('admin_menu', 'ganjeh_add_sales_report_menu');

/**
 * Handle Excel export before any output
 */
function ganjeh_handle_sales_export() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'ganjeh-sales-report') {
        return;
    }
    if (!isset($_GET['export']) || $_GET['export'] !== 'excel') {
        return;
    }
    if (!current_user_can('manage_woocommerce')) {
        return;
    }

    $data = ganjeh_get_sales_data();

    // BOM for UTF-8 Excel compatibility
    $bom = "\xEF\xBB\xBF";

    $filename = 'sales-report-' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fwrite($output, $bom);

    // Header row
    $headers = [
        'آیدی محصول',
        'نام محصول',
        'SKU',
        'نوع',
    ];

    // Add month headers
    $months = ganjeh_get_persian_months_range();
    foreach ($months as $month) {
        $headers[] = $month['label'] . ' (تعداد)';
        $headers[] = $month['label'] . ' (مبلغ)';
    }

    $headers[] = 'مجموع تعداد';
    $headers[] = 'مجموع فروش (تومان)';

    fputcsv($output, $headers);

    // Data rows
    foreach ($data['products'] as $product) {
        $row = [
            $product['id'],
            $product['name'],
            $product['sku'],
            $product['type'],
        ];

        foreach ($months as $month) {
            $key = $month['key'];
            $row[] = $product['months'][$key]['qty'] ?? 0;
            $row[] = $product['months'][$key]['total'] ?? 0;
        }

        $row[] = $product['total_qty'];
        $row[] = $product['total_revenue'];

        fputcsv($output, $row);
    }

    // Summary row
    $summary_row = ['', 'جمع کل', '', ''];
    foreach ($months as $month) {
        $key = $month['key'];
        $summary_row[] = $data['month_totals'][$key]['qty'] ?? 0;
        $summary_row[] = $data['month_totals'][$key]['total'] ?? 0;
    }
    $summary_row[] = $data['grand_total_qty'];
    $summary_row[] = $data['grand_total_revenue'];
    fputcsv($output, $summary_row);

    fclose($output);
    exit;
}
add_action('admin_init', 'ganjeh_handle_sales_export');

/**
 * Get Persian months for the past year
 */
function ganjeh_get_persian_months_range() {
    $months = [];
    $persian_month_names = [
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
        4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
        7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
        10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
    ];

    // Generate last 12 Gregorian months as keys
    for ($i = 11; $i >= 0; $i--) {
        $date = strtotime("-{$i} months");
        $year = date('Y', $date);
        $month = date('m', $date);
        $key = $year . '-' . $month;

        // Try to convert to Jalali if possible, otherwise use Gregorian month names
        $gregorian_names = [
            '01' => 'ژانویه', '02' => 'فوریه', '03' => 'مارس',
            '04' => 'آوریل', '05' => 'مه', '06' => 'ژوئن',
            '07' => 'ژوئیه', '08' => 'اوت', '09' => 'سپتامبر',
            '10' => 'اکتبر', '11' => 'نوامبر', '12' => 'دسامبر',
        ];

        $label = $gregorian_names[$month] . ' ' . $year;

        // If jdate function exists (Jalali calendar plugin), use Persian month names
        if (function_exists('jdate')) {
            $jdate_parts = explode('-', jdate('Y-m', $date));
            $j_year = $jdate_parts[0];
            $j_month = intval($jdate_parts[1]);
            $label = $persian_month_names[$j_month] . ' ' . $j_year;
        }

        $months[] = [
            'key' => $key,
            'label' => $label,
            'year' => $year,
            'month' => $month,
        ];
    }

    return $months;
}

/**
 * Get sales data per product for the past year
 */
function ganjeh_get_sales_data() {
    $date_from = date('Y-m-d', strtotime('-12 months'));
    $date_to = date('Y-m-d');

    // Get all completed/processing orders in the past year using WooCommerce API
    $order_ids = wc_get_orders([
        'status' => ['completed', 'processing'],
        'date_created' => $date_from . '...' . $date_to,
        'limit' => -1,
        'return' => 'ids',
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

    $products = [];
    $month_totals = [];
    $grand_total_qty = 0;
    $grand_total_revenue = 0;

    foreach ($order_ids as $order_id) {
        $order = wc_get_order($order_id);
        if (!$order) continue;

        $order_date = $order->get_date_created();
        if (!$order_date) continue;

        $month_key = $order_date->date('Y-m');

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $variation_id = $item->get_variation_id();
            $qty = $item->get_quantity();
            $line_total = (float) $item->get_total();

            // Use variation ID as key if it's a variation
            $item_key = $variation_id ? $product_id . '_' . $variation_id : (string) $product_id;

            if (!isset($products[$item_key])) {
                $product = $item->get_product();
                $product_name = $item->get_name();
                $sku = $product ? $product->get_sku() : '';
                $type = '';

                if ($variation_id) {
                    $type = 'متغیر';
                    // Append variation attributes to name
                    $variation_attrs = $item->get_meta_data();
                    $attr_parts = [];
                    foreach ($variation_attrs as $meta) {
                        $meta_data = $meta->get_data();
                        $key = $meta_data['key'];
                        // Only include attribute meta (starts with pa_ or is a known attribute)
                        if (strpos($key, 'pa_') === 0 || strpos($key, 'attribute_') === 0) {
                            $attr_parts[] = $meta_data['value'];
                        }
                    }
                    if (!empty($attr_parts)) {
                        $product_name .= ' (' . implode(', ', $attr_parts) . ')';
                    }
                } else {
                    $type = 'ساده';
                }

                $products[$item_key] = [
                    'id' => $product_id,
                    'name' => $product_name,
                    'sku' => $sku,
                    'type' => $type,
                    'months' => [],
                    'total_qty' => 0,
                    'total_revenue' => 0,
                ];
            }

            // Aggregate per month
            if (!isset($products[$item_key]['months'][$month_key])) {
                $products[$item_key]['months'][$month_key] = ['qty' => 0, 'total' => 0];
            }
            $products[$item_key]['months'][$month_key]['qty'] += $qty;
            $products[$item_key]['months'][$month_key]['total'] += $line_total;

            $products[$item_key]['total_qty'] += $qty;
            $products[$item_key]['total_revenue'] += $line_total;

            // Month totals
            if (!isset($month_totals[$month_key])) {
                $month_totals[$month_key] = ['qty' => 0, 'total' => 0];
            }
            $month_totals[$month_key]['qty'] += $qty;
            $month_totals[$month_key]['total'] += $line_total;

            $grand_total_qty += $qty;
            $grand_total_revenue += $line_total;
        }
    }

    // Sort by total revenue descending
    uasort($products, function($a, $b) {
        return $b['total_revenue'] <=> $a['total_revenue'];
    });

    return [
        'products' => $products,
        'month_totals' => $month_totals,
        'grand_total_qty' => $grand_total_qty,
        'grand_total_revenue' => $grand_total_revenue,
        'order_count' => count($order_ids),
    ];
}

/**
 * Render the sales report page
 */
function ganjeh_render_sales_report_page() {
    $data = ganjeh_get_sales_data();
    $months = ganjeh_get_persian_months_range();
    $export_url = admin_url('admin.php?page=ganjeh-sales-report&export=excel');
    ?>
    <div class="wrap ganjeh-sales-report-wrap">
        <div class="report-header">
            <div class="report-title-section">
                <h1>
                    <span class="dashicons dashicons-chart-bar"></span>
                    <?php _e('گزارش فروش تک قلم محصولات', 'ganjeh'); ?>
                </h1>
                <p class="report-subtitle">
                    <?php printf(
                        __('از %s تا %s | %s سفارش | %s محصول', 'ganjeh'),
                        '<strong>' . date_i18n('j F Y', strtotime('-12 months')) . '</strong>',
                        '<strong>' . date_i18n('j F Y') . '</strong>',
                        '<strong>' . number_format_i18n($data['order_count']) . '</strong>',
                        '<strong>' . number_format_i18n(count($data['products'])) . '</strong>'
                    ); ?>
                </p>
            </div>
            <a href="<?php echo esc_url($export_url); ?>" class="button button-primary button-hero export-btn">
                <span class="dashicons dashicons-download"></span>
                <?php _e('دانلود اکسل (CSV)', 'ganjeh'); ?>
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="card-icon" style="background: #dbeafe; color: #2563eb;">
                    <span class="dashicons dashicons-cart"></span>
                </div>
                <div class="card-info">
                    <span class="card-value"><?php echo number_format_i18n($data['order_count']); ?></span>
                    <span class="card-label"><?php _e('تعداد سفارشات', 'ganjeh'); ?></span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background: #f0fdf4; color: #16a34a;">
                    <span class="dashicons dashicons-products"></span>
                </div>
                <div class="card-info">
                    <span class="card-value"><?php echo number_format_i18n($data['grand_total_qty']); ?></span>
                    <span class="card-label"><?php _e('تعداد کل فروش', 'ganjeh'); ?></span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background: #fef3c7; color: #d97706;">
                    <span class="dashicons dashicons-money-alt"></span>
                </div>
                <div class="card-info">
                    <span class="card-value"><?php echo number_format_i18n($data['grand_total_revenue']); ?></span>
                    <span class="card-label"><?php _e('مجموع فروش (تومان)', 'ganjeh'); ?></span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background: #fce7f3; color: #db2777;">
                    <span class="dashicons dashicons-tag"></span>
                </div>
                <div class="card-info">
                    <span class="card-value"><?php echo number_format_i18n(count($data['products'])); ?></span>
                    <span class="card-label"><?php _e('تعداد محصولات فروخته شده', 'ganjeh'); ?></span>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="table-container">
            <table class="wp-list-table widefat fixed striped ganjeh-sales-table">
                <thead>
                    <tr>
                        <th class="col-rank">#</th>
                        <th class="col-id"><?php _e('آیدی', 'ganjeh'); ?></th>
                        <th class="col-name"><?php _e('نام محصول', 'ganjeh'); ?></th>
                        <th class="col-sku"><?php _e('SKU', 'ganjeh'); ?></th>
                        <?php foreach ($months as $month) : ?>
                            <th class="col-month"><?php echo esc_html($month['label']); ?></th>
                        <?php endforeach; ?>
                        <th class="col-total-qty"><?php _e('مجموع تعداد', 'ganjeh'); ?></th>
                        <th class="col-total-revenue"><?php _e('مجموع فروش', 'ganjeh'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['products'])) : ?>
                        <tr>
                            <td colspan="<?php echo 5 + count($months); ?>" class="no-data">
                                <?php _e('هیچ فروشی در این بازه یافت نشد.', 'ganjeh'); ?>
                            </td>
                        </tr>
                    <?php else :
                        $rank = 0;
                        foreach ($data['products'] as $product) :
                            $rank++;
                    ?>
                        <tr>
                            <td class="col-rank"><?php echo $rank; ?></td>
                            <td class="col-id"><?php echo esc_html($product['id']); ?></td>
                            <td class="col-name">
                                <a href="<?php echo get_edit_post_link($product['id']); ?>" target="_blank">
                                    <?php echo esc_html($product['name']); ?>
                                </a>
                                <span class="product-type-badge type-<?php echo $product['type'] === 'متغیر' ? 'variable' : 'simple'; ?>">
                                    <?php echo esc_html($product['type']); ?>
                                </span>
                            </td>
                            <td class="col-sku"><?php echo esc_html($product['sku']); ?></td>
                            <?php foreach ($months as $month) :
                                $m = $product['months'][$month['key']] ?? null;
                            ?>
                                <td class="col-month <?php echo $m ? '' : 'empty-cell'; ?>">
                                    <?php if ($m) : ?>
                                        <span class="month-qty"><?php echo number_format_i18n($m['qty']); ?></span>
                                        <span class="month-total"><?php echo number_format_i18n($m['total']); ?></span>
                                    <?php else : ?>
                                        <span class="no-sale">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="col-total-qty"><strong><?php echo number_format_i18n($product['total_qty']); ?></strong></td>
                            <td class="col-total-revenue"><strong><?php echo number_format_i18n($product['total_revenue']); ?></strong></td>
                        </tr>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </tbody>
                <?php if (!empty($data['products'])) : ?>
                <tfoot>
                    <tr class="totals-row">
                        <td colspan="4"><strong><?php _e('جمع کل', 'ganjeh'); ?></strong></td>
                        <?php foreach ($months as $month) :
                            $mt = $data['month_totals'][$month['key']] ?? null;
                        ?>
                            <td class="col-month">
                                <?php if ($mt) : ?>
                                    <span class="month-qty"><strong><?php echo number_format_i18n($mt['qty']); ?></strong></span>
                                    <span class="month-total"><strong><?php echo number_format_i18n($mt['total']); ?></strong></span>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <td><strong><?php echo number_format_i18n($data['grand_total_qty']); ?></strong></td>
                        <td><strong><?php echo number_format_i18n($data['grand_total_revenue']); ?></strong></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <style>
        .ganjeh-sales-report-wrap {
            max-width: 100%;
            margin: 20px 20px 20px 0;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .report-title-section h1 {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 23px;
            margin: 0 0 8px;
        }
        .report-title-section h1 .dashicons {
            font-size: 28px;
            width: 28px;
            height: 28px;
            color: #2271b1;
        }
        .report-subtitle {
            color: #50575e;
            font-size: 14px;
            margin: 0;
        }
        .export-btn {
            display: flex !important;
            align-items: center;
            gap: 8px;
            font-size: 14px !important;
            padding: 10px 24px !important;
            height: auto !important;
        }
        .export-btn .dashicons {
            font-size: 20px;
            width: 20px;
            height: 20px;
        }

        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 25px;
        }
        .summary-card {
            background: white;
            border: 1px solid #dcdcde;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .card-icon .dashicons {
            font-size: 24px;
            width: 24px;
            height: 24px;
        }
        .card-info {
            display: flex;
            flex-direction: column;
        }
        .card-value {
            font-size: 22px;
            font-weight: 700;
            color: #1d2327;
        }
        .card-label {
            font-size: 13px;
            color: #646970;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
            background: white;
            border: 1px solid #dcdcde;
            border-radius: 8px;
        }
        .ganjeh-sales-table {
            border: none;
            margin: 0;
            border-radius: 8px;
        }
        .ganjeh-sales-table thead th {
            background: #f6f7f7;
            padding: 12px 10px;
            font-weight: 600;
            font-size: 12px;
            white-space: nowrap;
            text-align: center;
            border-bottom: 2px solid #dcdcde;
        }
        .ganjeh-sales-table td {
            padding: 10px;
            vertical-align: middle;
            font-size: 13px;
            text-align: center;
        }
        .col-rank { width: 40px; }
        .col-id { width: 70px; }
        .col-name {
            text-align: right !important;
            min-width: 200px;
        }
        .col-name a {
            color: #2271b1;
            text-decoration: none;
            font-weight: 500;
        }
        .col-name a:hover {
            color: #135e96;
            text-decoration: underline;
        }
        .col-sku { width: 80px; font-size: 12px; color: #646970; }
        .col-month {
            min-width: 90px;
        }
        .col-total-qty, .col-total-revenue {
            background: #f9fafb !important;
            font-weight: 600;
        }
        .month-qty {
            display: block;
            font-weight: 600;
            color: #1d2327;
        }
        .month-total {
            display: block;
            font-size: 11px;
            color: #646970;
        }
        .empty-cell { color: #ccc; }
        .no-sale { color: #ccc; }
        .no-data {
            text-align: center !important;
            padding: 40px !important;
            color: #646970;
        }

        .product-type-badge {
            display: inline-block;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            margin-right: 6px;
        }
        .type-simple { background: #dbeafe; color: #1e40af; }
        .type-variable { background: #fef3c7; color: #92400e; }

        .totals-row {
            background: #f0f6fc !important;
        }
        .totals-row td {
            border-top: 2px solid #2271b1;
            font-weight: 600;
        }
    </style>
    <?php
}
