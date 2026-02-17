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

    $filename = 'gozaresh-foroush-' . ganjeh_jalali_date('Y-m-j') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fwrite($output, $bom);

    // Header row
    $headers = array(
        'آیدی محصول',
        'نام محصول',
        'SKU',
        'نوع',
    );

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
        $row = array(
            $product['id'],
            $product['name'],
            $product['sku'],
            $product['type'],
        );

        foreach ($months as $month) {
            $csv_qty = 0;
            $csv_total = 0;
            foreach ($month['g_keys'] as $gk) {
                if (isset($product['months'][$gk])) {
                    $csv_qty += $product['months'][$gk]['qty'];
                    $csv_total += $product['months'][$gk]['total'];
                }
            }
            $row[] = $csv_qty;
            $row[] = $csv_total;
        }

        $row[] = $product['total_qty'];
        $row[] = $product['total_revenue'];

        fputcsv($output, $row);
    }

    // Summary row
    $summary_row = array('', 'جمع کل', '', '');
    foreach ($months as $month) {
        $s_qty = 0;
        $s_total = 0;
        foreach ($month['g_keys'] as $gk) {
            if (isset($data['month_totals'][$gk])) {
                $s_qty += $data['month_totals'][$gk]['qty'];
                $s_total += $data['month_totals'][$gk]['total'];
            }
        }
        $summary_row[] = $s_qty;
        $summary_row[] = $s_total;
    }
    $summary_row[] = $data['grand_total_qty'];
    $summary_row[] = $data['grand_total_revenue'];
    fputcsv($output, $summary_row);

    fclose($output);
    exit;
}
add_action('admin_init', 'ganjeh_handle_sales_export');

/**
 * Convert Gregorian date to Jalali (Shamsi)
 * Built-in converter - no external plugin needed
 *
 * @param int $gy Gregorian year
 * @param int $gm Gregorian month
 * @param int $gd Gregorian day
 * @return array [year, month, day]
 */
function ganjeh_gregorian_to_jalali($gy, $gm, $gd) {
    $g_d_m = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = 355666 + (365 * $gy) + intval(($gy2 + 3) / 4) - intval(($gy2 + 99) / 100)
            + intval(($gy2 + 399) / 400) + $gd + $g_d_m[$gm - 1];
    $jy = -1595 + (33 * intval($days / 12053));
    $days = $days % 12053;
    $jy += 4 * intval($days / 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += intval(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    if ($days < 186) {
        $jm = 1 + intval($days / 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + intval(($days - 186) / 30);
        $jd = 1 + (($days - 186) % 30);
    }
    return array($jy, $jm, $jd);
}

/**
 * Format a Gregorian date as Jalali string
 *
 * @param string $format 'j F Y' or 'Y-m' etc.
 * @param int|null $timestamp Unix timestamp (null for current time)
 * @return string
 */
function ganjeh_jalali_date($format, $timestamp = null) {
    if ($timestamp === null) {
        $timestamp = time();
    }

    $gy = intval(date('Y', $timestamp));
    $gm = intval(date('m', $timestamp));
    $gd = intval(date('d', $timestamp));

    list($jy, $jm, $jd) = ganjeh_gregorian_to_jalali($gy, $gm, $gd);

    $persian_month_names = array(
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
        4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
        7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
        10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
    );

    $result = $format;
    $result = str_replace('j', $jd, $result);
    $result = str_replace('F', $persian_month_names[$jm], $result);
    $result = str_replace('Y', $jy, $result);
    $result = str_replace('m', str_pad($jm, 2, '0', STR_PAD_LEFT), $result);

    return $result;
}

/**
 * Get Persian (Shamsi) months for the past year
 */
function ganjeh_get_persian_months_range() {
    $months = array();
    $persian_month_names = array(
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
        4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
        7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
        10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
    );

    // Build list of unique Shamsi months for the past 12 Gregorian months
    $seen = array();

    for ($i = 11; $i >= 0; $i--) {
        $date = strtotime("-{$i} months");
        $gy = intval(date('Y', $date));
        $gm = intval(date('m', $date));

        // Gregorian key for data lookup
        $g_key = date('Y-m', $date);

        // Convert to Jalali
        list($jy, $jm, $jd) = ganjeh_gregorian_to_jalali($gy, $gm, 15);

        $label = $persian_month_names[$jm] . ' ' . $jy;

        // Avoid duplicate Shamsi months
        $shamsi_key = $jy . '-' . str_pad($jm, 2, '0', STR_PAD_LEFT);
        if (isset($seen[$shamsi_key])) {
            // Map this Gregorian month key to the same Shamsi month
            $seen[$shamsi_key]['g_keys'][] = $g_key;
            continue;
        }

        $seen[$shamsi_key] = array(
            'key' => $g_key,
            'g_keys' => array($g_key),
            'label' => $label,
            'year' => $gy,
            'month' => str_pad($gm, 2, '0', STR_PAD_LEFT),
        );
    }

    // Convert to indexed array
    foreach ($seen as $shamsi_key => $info) {
        $months[] = $info;
    }

    return $months;
}

/**
 * Get sales data per product for the past year
 * Uses direct SQL on WooCommerce lookup table for performance
 */
function ganjeh_get_sales_data() {
    $empty_result = array(
        'products' => array(),
        'month_totals' => array(),
        'grand_total_qty' => 0,
        'grand_total_revenue' => 0,
        'order_count' => 0,
    );

    global $wpdb;

    $date_from = date('Y-m-d 00:00:00', strtotime('-12 months'));
    $date_to = date('Y-m-d 23:59:59');

    // Check if wc_order_product_lookup table exists (WooCommerce Analytics)
    $lookup_table = $wpdb->prefix . 'wc_order_product_lookup';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$lookup_table}'");

    if (!$table_exists) {
        return $empty_result;
    }

    // Single efficient query: aggregate sales per product per month
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT
            product_id,
            variation_id,
            DATE_FORMAT(date_created, '%%Y-%%m') as month_key,
            SUM(product_qty) as total_qty,
            SUM(product_net_revenue) as total_revenue
         FROM {$lookup_table}
         WHERE date_created >= %s
         AND date_created <= %s
         GROUP BY product_id, variation_id, month_key
         ORDER BY total_revenue DESC",
        $date_from,
        $date_to
    ));

    if (!$results || !is_array($results)) {
        return $empty_result;
    }

    // Count distinct orders
    $order_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT order_id) FROM {$lookup_table}
         WHERE date_created >= %s AND date_created <= %s",
        $date_from,
        $date_to
    ));

    $products = array();
    $month_totals = array();
    $grand_total_qty = 0;
    $grand_total_revenue = 0;

    foreach ($results as $row) {
        $product_id = intval($row->product_id);
        $variation_id = intval($row->variation_id);
        $month_key = $row->month_key;
        $qty = intval($row->total_qty);
        $revenue = floatval($row->total_revenue);

        $item_key = $variation_id ? $product_id . '_' . $variation_id : strval($product_id);

        if (!isset($products[$item_key])) {
            // Get product name and SKU
            $product_obj = wc_get_product($variation_id ? $variation_id : $product_id);
            $product_name = $product_obj ? $product_obj->get_name() : get_the_title($product_id);
            $sku = ($product_obj && is_callable(array($product_obj, 'get_sku'))) ? $product_obj->get_sku() : '';
            $type = $variation_id ? 'متغیر' : 'ساده';

            $products[$item_key] = array(
                'id' => $product_id,
                'name' => $product_name,
                'sku' => $sku,
                'type' => $type,
                'months' => array(),
                'total_qty' => 0,
                'total_revenue' => 0,
            );
        }

        // Store month data
        $products[$item_key]['months'][$month_key] = array('qty' => $qty, 'total' => $revenue);
        $products[$item_key]['total_qty'] += $qty;
        $products[$item_key]['total_revenue'] += $revenue;

        // Month totals
        if (!isset($month_totals[$month_key])) {
            $month_totals[$month_key] = array('qty' => 0, 'total' => 0);
        }
        $month_totals[$month_key]['qty'] += $qty;
        $month_totals[$month_key]['total'] += $revenue;

        $grand_total_qty += $qty;
        $grand_total_revenue += $revenue;
    }

    // Sort by total revenue descending
    uasort($products, function($a, $b) {
        if ($b['total_revenue'] == $a['total_revenue']) return 0;
        return ($b['total_revenue'] > $a['total_revenue']) ? 1 : -1;
    });

    return array(
        'products' => $products,
        'month_totals' => $month_totals,
        'grand_total_qty' => $grand_total_qty,
        'grand_total_revenue' => $grand_total_revenue,
        'order_count' => intval($order_count),
    );
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
                        '<strong>' . ganjeh_jalali_date('j F Y', strtotime('-12 months')) . '</strong>',
                        '<strong>' . ganjeh_jalali_date('j F Y') . '</strong>',
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
                            <td colspan="<?php echo 6 + count($months); ?>" class="no-data">
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
                                <span class="product-type-badge type-<?php echo ($product['type'] === 'متغیر') ? 'variable' : 'simple'; ?>">
                                    <?php echo esc_html($product['type']); ?>
                                </span>
                            </td>
                            <td class="col-sku"><?php echo esc_html($product['sku']); ?></td>
                            <?php foreach ($months as $month) :
                                $m_qty = 0;
                                $m_total = 0;
                                $m_has_data = false;
                                foreach ($month['g_keys'] as $gk) {
                                    if (isset($product['months'][$gk])) {
                                        $m_qty += $product['months'][$gk]['qty'];
                                        $m_total += $product['months'][$gk]['total'];
                                        $m_has_data = true;
                                    }
                                }
                            ?>
                                <td class="col-month <?php echo $m_has_data ? '' : 'empty-cell'; ?>">
                                    <?php if ($m_has_data) : ?>
                                        <span class="month-qty"><?php echo number_format_i18n($m_qty); ?></span>
                                        <span class="month-total"><?php echo number_format_i18n($m_total); ?></span>
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
                            $mt_qty = 0;
                            $mt_total = 0;
                            $mt_has_data = false;
                            foreach ($month['g_keys'] as $gk) {
                                if (isset($data['month_totals'][$gk])) {
                                    $mt_qty += $data['month_totals'][$gk]['qty'];
                                    $mt_total += $data['month_totals'][$gk]['total'];
                                    $mt_has_data = true;
                                }
                            }
                        ?>
                            <td class="col-month">
                                <?php if ($mt_has_data) : ?>
                                    <span class="month-qty"><strong><?php echo number_format_i18n($mt_qty); ?></strong></span>
                                    <span class="month-total"><strong><?php echo number_format_i18n($mt_total); ?></strong></span>
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
        .col-month { min-width: 90px; }
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
