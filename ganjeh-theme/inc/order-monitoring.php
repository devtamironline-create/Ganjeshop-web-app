<?php
/**
 * Order Monitoring System
 *
 * Monitors orders and sends SMS alerts when no orders received
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Order Monitoring Settings
 */
function ganjeh_order_monitoring_settings_init() {
    register_setting('ganjeh_order_monitoring', 'ganjeh_order_monitoring_options');

    add_settings_section(
        'ganjeh_order_monitoring_section',
        __('تنظیمات مانیتورینگ سفارشات', 'ganjeh'),
        'ganjeh_order_monitoring_section_callback',
        'ganjeh_order_monitoring'
    );

    add_settings_field(
        'enabled',
        __('فعال‌سازی', 'ganjeh'),
        'ganjeh_order_monitoring_enabled_callback',
        'ganjeh_order_monitoring',
        'ganjeh_order_monitoring_section'
    );

    add_settings_field(
        'phone_numbers',
        __('شماره موبایل مدیران', 'ganjeh'),
        'ganjeh_order_monitoring_phones_callback',
        'ganjeh_order_monitoring',
        'ganjeh_order_monitoring_section'
    );

    add_settings_field(
        'hours_threshold',
        __('آستانه زمانی (ساعت)', 'ganjeh'),
        'ganjeh_order_monitoring_hours_callback',
        'ganjeh_order_monitoring',
        'ganjeh_order_monitoring_section'
    );

    add_settings_field(
        'quiet_hours',
        __('ساعات بی‌صدا', 'ganjeh'),
        'ganjeh_order_monitoring_quiet_hours_callback',
        'ganjeh_order_monitoring',
        'ganjeh_order_monitoring_section'
    );
}
add_action('admin_init', 'ganjeh_order_monitoring_settings_init');

function ganjeh_order_monitoring_section_callback() {
    echo '<p>' . __('تنظیمات هشدار پیامکی برای عدم دریافت سفارش', 'ganjeh') . '</p>';
}

function ganjeh_order_monitoring_enabled_callback() {
    $options = get_option('ganjeh_order_monitoring_options', []);
    $enabled = isset($options['enabled']) ? $options['enabled'] : 0;
    ?>
    <label>
        <input type="checkbox" name="ganjeh_order_monitoring_options[enabled]" value="1" <?php checked(1, $enabled); ?>>
        <?php _e('فعال کردن سیستم مانیتورینگ', 'ganjeh'); ?>
    </label>
    <?php
}

function ganjeh_order_monitoring_phones_callback() {
    $options = get_option('ganjeh_order_monitoring_options', []);
    $phones = isset($options['phone_numbers']) ? $options['phone_numbers'] : '';
    ?>
    <textarea name="ganjeh_order_monitoring_options[phone_numbers]" rows="4" cols="50" class="large-text" placeholder="09123456789&#10;09121234567"><?php echo esc_textarea($phones); ?></textarea>
    <p class="description"><?php _e('هر شماره در یک خط جداگانه (بدون خط تیره)', 'ganjeh'); ?></p>
    <?php
}

function ganjeh_order_monitoring_hours_callback() {
    $options = get_option('ganjeh_order_monitoring_options', []);
    $hours = isset($options['hours_threshold']) ? $options['hours_threshold'] : 2;
    ?>
    <input type="number" name="ganjeh_order_monitoring_options[hours_threshold]" value="<?php echo esc_attr($hours); ?>" min="1" max="24" style="width: 80px;">
    <span><?php _e('ساعت', 'ganjeh'); ?></span>
    <p class="description"><?php _e('اگر به این مدت سفارشی ثبت نشود، پیامک ارسال می‌شود', 'ganjeh'); ?></p>
    <?php
}

function ganjeh_order_monitoring_quiet_hours_callback() {
    $options = get_option('ganjeh_order_monitoring_options', []);
    $quiet_start = isset($options['quiet_start']) ? $options['quiet_start'] : '00:00';
    $quiet_end = isset($options['quiet_end']) ? $options['quiet_end'] : '08:00';
    ?>
    <label>
        <?php _e('از ساعت', 'ganjeh'); ?>
        <input type="time" name="ganjeh_order_monitoring_options[quiet_start]" value="<?php echo esc_attr($quiet_start); ?>">
    </label>
    <label>
        <?php _e('تا ساعت', 'ganjeh'); ?>
        <input type="time" name="ganjeh_order_monitoring_options[quiet_end]" value="<?php echo esc_attr($quiet_end); ?>">
    </label>
    <p class="description"><?php _e('در این بازه زمانی پیامک ارسال نمی‌شود', 'ganjeh'); ?></p>
    <?php
}

/**
 * Add admin menu page
 */
function ganjeh_order_monitoring_menu() {
    add_submenu_page(
        'woocommerce',
        __('مانیتورینگ سفارشات', 'ganjeh'),
        __('مانیتورینگ سفارشات', 'ganjeh'),
        'manage_woocommerce',
        'order-monitoring',
        'ganjeh_order_monitoring_page'
    );
}
add_action('admin_menu', 'ganjeh_order_monitoring_menu');

/**
 * Order monitoring admin page
 */
function ganjeh_order_monitoring_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('مانیتورینگ سفارشات', 'ganjeh'); ?></h1>

        <div id="order-monitoring-tabs">
            <h2 class="nav-tab-wrapper">
                <a href="#tab-dashboard" class="nav-tab nav-tab-active"><?php _e('داشبورد', 'ganjeh'); ?></a>
                <a href="#tab-settings" class="nav-tab"><?php _e('تنظیمات', 'ganjeh'); ?></a>
            </h2>

            <!-- Dashboard Tab -->
            <div id="tab-dashboard" class="tab-content" style="display: block;">
                <?php ganjeh_order_monitoring_dashboard(); ?>
            </div>

            <!-- Settings Tab -->
            <div id="tab-settings" class="tab-content" style="display: none;">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('ganjeh_order_monitoring');
                    do_settings_sections('ganjeh_order_monitoring');
                    submit_button(__('ذخیره تنظیمات', 'ganjeh'));
                    ?>
                </form>

                <hr>
                <h3><?php _e('تست ارسال پیامک', 'ganjeh'); ?></h3>
                <button type="button" id="test-sms-btn" class="button button-secondary">
                    <?php _e('ارسال پیامک تست', 'ganjeh'); ?>
                </button>
                <span id="test-sms-result"></span>
            </div>
        </div>

        <style>
            .tab-content { background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-top: none; }
            .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
            .stat-card { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
            .stat-card h3 { margin: 0 0 10px; color: #666; font-size: 14px; }
            .stat-card .number { font-size: 36px; font-weight: bold; color: #2271b1; }
            .stat-card.warning .number { color: #dba617; }
            .stat-card.success .number { color: #00a32a; }
            .stat-card.danger .number { color: #d63638; }
            .hourly-chart { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
            .hourly-bars { display: flex; align-items: flex-end; height: 200px; gap: 4px; padding: 10px 0; border-bottom: 2px solid #ddd; }
            .hour-bar { flex: 1; background: #2271b1; border-radius: 4px 4px 0 0; min-height: 4px; position: relative; transition: background 0.3s; }
            .hour-bar:hover { background: #135e96; }
            .hour-bar .tooltip { position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); background: #333; color: #fff; padding: 5px 10px; border-radius: 4px; font-size: 12px; white-space: nowrap; opacity: 0; transition: opacity 0.3s; pointer-events: none; }
            .hour-bar:hover .tooltip { opacity: 1; }
            .hour-labels { display: flex; gap: 4px; }
            .hour-labels span { flex: 1; text-align: center; font-size: 10px; color: #666; }
            .recent-orders { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; }
            .recent-orders table { width: 100%; border-collapse: collapse; }
            .recent-orders th, .recent-orders td { padding: 10px; text-align: right; border-bottom: 1px solid #eee; }
            .recent-orders th { background: #f9f9f9; }
            .status-badge { padding: 3px 8px; border-radius: 4px; font-size: 12px; }
            .last-order-alert { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
            .last-order-alert.danger { background: #f8d7da; border-color: #dc3545; }
            .last-order-alert.success { background: #d4edda; border-color: #28a745; }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Tabs
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.tab-content').hide();
                $($(this).attr('href')).show();
            });

            // Test SMS
            $('#test-sms-btn').on('click', function() {
                var btn = $(this);
                btn.prop('disabled', true).text('<?php _e('در حال ارسال...', 'ganjeh'); ?>');

                $.post(ajaxurl, {
                    action: 'ganjeh_test_monitoring_sms',
                    nonce: '<?php echo wp_create_nonce('ganjeh_monitoring_nonce'); ?>'
                }, function(response) {
                    btn.prop('disabled', false).text('<?php _e('ارسال پیامک تست', 'ganjeh'); ?>');
                    if (response.success) {
                        $('#test-sms-result').html('<span style="color:green;">✓ ' + response.data.message + '</span>');
                    } else {
                        $('#test-sms-result').html('<span style="color:red;">✗ ' + response.data.message + '</span>');
                    }
                });
            });
        });
        </script>
    </div>
    <?php
}

/**
 * Dashboard content
 */
function ganjeh_order_monitoring_dashboard() {
    // Get statistics
    $today_orders = ganjeh_get_orders_count('today');
    $yesterday_orders = ganjeh_get_orders_count('yesterday');
    $week_orders = ganjeh_get_orders_count('week');
    $month_orders = ganjeh_get_orders_count('month');
    $last_order = ganjeh_get_last_order_time();
    $hourly_stats = ganjeh_get_hourly_orders_stats();
    $recent_orders = ganjeh_get_recent_orders(10);

    // Calculate hours since last order
    $hours_since_last = 0;
    $last_order_class = 'success';
    if ($last_order) {
        $hours_since_last = round((current_time('timestamp') - strtotime($last_order)) / 3600, 1);
        if ($hours_since_last > 2) {
            $last_order_class = 'danger';
        } elseif ($hours_since_last > 1) {
            $last_order_class = 'warning';
        }
    }
    ?>

    <!-- Last Order Alert -->
    <?php if ($last_order): ?>
    <div class="last-order-alert <?php echo $last_order_class; ?>">
        <strong><?php _e('آخرین سفارش:', 'ganjeh'); ?></strong>
        <?php
        echo sprintf(
            __('%s (%s ساعت پیش)', 'ganjeh'),
            wp_date('Y/m/d H:i', strtotime($last_order)),
            $hours_since_last
        );
        ?>
    </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php _e('سفارشات امروز', 'ganjeh'); ?></h3>
            <div class="number"><?php echo $today_orders; ?></div>
        </div>
        <div class="stat-card">
            <h3><?php _e('سفارشات دیروز', 'ganjeh'); ?></h3>
            <div class="number"><?php echo $yesterday_orders; ?></div>
        </div>
        <div class="stat-card">
            <h3><?php _e('سفارشات این هفته', 'ganjeh'); ?></h3>
            <div class="number"><?php echo $week_orders; ?></div>
        </div>
        <div class="stat-card">
            <h3><?php _e('سفارشات این ماه', 'ganjeh'); ?></h3>
            <div class="number"><?php echo $month_orders; ?></div>
        </div>
    </div>

    <!-- Hourly Chart -->
    <div class="hourly-chart">
        <h3><?php _e('تعداد سفارشات بر اساس ساعت (امروز)', 'ganjeh'); ?></h3>
        <?php
        $max_orders = max($hourly_stats) ?: 1;
        ?>
        <div class="hourly-bars">
            <?php for ($h = 0; $h < 24; $h++):
                $count = isset($hourly_stats[$h]) ? $hourly_stats[$h] : 0;
                $height = ($count / $max_orders) * 100;
            ?>
                <div class="hour-bar" style="height: <?php echo max($height, 2); ?>%;">
                    <span class="tooltip"><?php echo sprintf(__('ساعت %d: %d سفارش', 'ganjeh'), $h, $count); ?></span>
                </div>
            <?php endfor; ?>
        </div>
        <div class="hour-labels">
            <?php for ($h = 0; $h < 24; $h++): ?>
                <span><?php echo $h; ?></span>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="recent-orders">
        <h3><?php _e('آخرین سفارشات', 'ganjeh'); ?></h3>
        <table>
            <thead>
                <tr>
                    <th><?php _e('شماره', 'ganjeh'); ?></th>
                    <th><?php _e('مشتری', 'ganjeh'); ?></th>
                    <th><?php _e('مبلغ', 'ganjeh'); ?></th>
                    <th><?php _e('وضعیت', 'ganjeh'); ?></th>
                    <th><?php _e('تاریخ', 'ganjeh'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_orders): foreach ($recent_orders as $order): ?>
                <tr>
                    <td><a href="<?php echo admin_url('post.php?post=' . $order->get_id() . '&action=edit'); ?>">#<?php echo $order->get_order_number(); ?></a></td>
                    <td><?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?></td>
                    <td><?php echo $order->get_formatted_order_total(); ?></td>
                    <td><span class="status-badge" style="background: #<?php echo ganjeh_get_status_color($order->get_status()); ?>;"><?php echo wc_get_order_status_name($order->get_status()); ?></span></td>
                    <td><?php echo wp_date('Y/m/d H:i', $order->get_date_created()->getTimestamp()); ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="5"><?php _e('سفارشی یافت نشد', 'ganjeh'); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Get orders count for a period
 */
function ganjeh_get_orders_count($period = 'today') {
    $args = [
        'status' => ['wc-processing', 'wc-completed', 'wc-on-hold', 'wc-pending'],
        'return' => 'ids',
        'limit' => -1,
    ];

    switch ($period) {
        case 'today':
            $args['date_created'] = '>=' . date('Y-m-d 00:00:00');
            break;
        case 'yesterday':
            $args['date_created'] = date('Y-m-d', strtotime('-1 day')) . '...' . date('Y-m-d', strtotime('-1 day')) . ' 23:59:59';
            break;
        case 'week':
            $args['date_created'] = '>=' . date('Y-m-d', strtotime('-7 days'));
            break;
        case 'month':
            $args['date_created'] = '>=' . date('Y-m-01');
            break;
    }

    $orders = wc_get_orders($args);
    return count($orders);
}

/**
 * Get last order time
 */
function ganjeh_get_last_order_time() {
    $orders = wc_get_orders([
        'limit' => 1,
        'orderby' => 'date',
        'order' => 'DESC',
        'status' => ['wc-processing', 'wc-completed', 'wc-on-hold', 'wc-pending'],
    ]);

    if ($orders) {
        return $orders[0]->get_date_created()->format('Y-m-d H:i:s');
    }
    return null;
}

/**
 * Get hourly orders statistics
 */
function ganjeh_get_hourly_orders_stats() {
    $stats = array_fill(0, 24, 0);

    $orders = wc_get_orders([
        'date_created' => '>=' . date('Y-m-d 00:00:00'),
        'status' => ['wc-processing', 'wc-completed', 'wc-on-hold', 'wc-pending'],
        'limit' => -1,
    ]);

    foreach ($orders as $order) {
        $hour = (int) $order->get_date_created()->format('G');
        $stats[$hour]++;
    }

    return $stats;
}

/**
 * Get recent orders
 */
function ganjeh_get_recent_orders($limit = 10) {
    return wc_get_orders([
        'limit' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
}

/**
 * Get status color
 */
function ganjeh_get_status_color($status) {
    $colors = [
        'pending' => 'f8dda7',
        'processing' => 'c6e1c6',
        'on-hold' => 'f8dda7',
        'completed' => 'c8d7e1',
        'cancelled' => 'eba3a3',
        'refunded' => 'e5e5e5',
        'failed' => 'eba3a3',
    ];
    return isset($colors[$status]) ? $colors[$status] : 'e5e5e5';
}

/**
 * Schedule cron event
 */
function ganjeh_schedule_order_monitoring() {
    if (!wp_next_scheduled('ganjeh_check_orders_cron')) {
        wp_schedule_event(time(), 'hourly', 'ganjeh_check_orders_cron');
    }
}
add_action('wp', 'ganjeh_schedule_order_monitoring');

/**
 * Clear cron on deactivation
 */
function ganjeh_clear_order_monitoring_cron() {
    wp_clear_scheduled_hook('ganjeh_check_orders_cron');
}
add_action('switch_theme', 'ganjeh_clear_order_monitoring_cron');

/**
 * Cron job to check orders
 */
function ganjeh_check_orders_callback() {
    $options = get_option('ganjeh_order_monitoring_options', []);

    // Check if enabled
    if (empty($options['enabled'])) {
        return;
    }

    // Check quiet hours
    $current_hour = (int) current_time('H');
    $current_minute = (int) current_time('i');
    $current_time_minutes = $current_hour * 60 + $current_minute;

    $quiet_start = isset($options['quiet_start']) ? $options['quiet_start'] : '00:00';
    $quiet_end = isset($options['quiet_end']) ? $options['quiet_end'] : '08:00';

    $quiet_start_parts = explode(':', $quiet_start);
    $quiet_end_parts = explode(':', $quiet_end);

    $quiet_start_minutes = (int)$quiet_start_parts[0] * 60 + (int)$quiet_start_parts[1];
    $quiet_end_minutes = (int)$quiet_end_parts[0] * 60 + (int)$quiet_end_parts[1];

    // Check if in quiet hours
    if ($quiet_start_minutes < $quiet_end_minutes) {
        // Normal range (e.g., 00:00 to 08:00)
        if ($current_time_minutes >= $quiet_start_minutes && $current_time_minutes < $quiet_end_minutes) {
            return;
        }
    } else {
        // Overnight range (e.g., 22:00 to 06:00)
        if ($current_time_minutes >= $quiet_start_minutes || $current_time_minutes < $quiet_end_minutes) {
            return;
        }
    }

    // Get threshold
    $hours_threshold = isset($options['hours_threshold']) ? (int)$options['hours_threshold'] : 2;

    // Get last order time
    $last_order_time = ganjeh_get_last_order_time();

    if (!$last_order_time) {
        return;
    }

    $hours_since_last = (current_time('timestamp') - strtotime($last_order_time)) / 3600;

    // Check if we should send alert
    if ($hours_since_last >= $hours_threshold) {
        // Check if we already sent alert recently (prevent duplicate alerts)
        $last_alert = get_option('ganjeh_last_order_alert_time', 0);
        if ((time() - $last_alert) < 3600) {
            return; // Already sent alert in last hour
        }

        // Send SMS alert
        ganjeh_send_order_monitoring_sms($hours_since_last);

        // Update last alert time
        update_option('ganjeh_last_order_alert_time', time());
    }
}
add_action('ganjeh_check_orders_cron', 'ganjeh_check_orders_callback');

/**
 * Send SMS alert
 */
function ganjeh_send_order_monitoring_sms($hours_since_last) {
    $options = get_option('ganjeh_order_monitoring_options', []);

    if (empty($options['phone_numbers'])) {
        return false;
    }

    // Get Kavenegar API key from WooCommerce settings or theme settings
    $api_key = get_option('woocommerce_kavenegar_api_key');
    if (!$api_key) {
        $api_key = get_option('ganjeh_kavenegar_api_key');
    }
    if (!$api_key) {
        // Try to get from SMS settings
        $sms_settings = get_option('ganjeh_sms_settings', []);
        $api_key = isset($sms_settings['api_key']) ? $sms_settings['api_key'] : '';
    }

    if (!$api_key) {
        error_log('Ganjeh Order Monitoring: Kavenegar API key not found');
        return false;
    }

    $phones = array_filter(array_map('trim', explode("\n", $options['phone_numbers'])));

    if (empty($phones)) {
        return false;
    }

    $message = sprintf(
        'هشدار: %s ساعت است که سفارش جدیدی ثبت نشده است.',
        round($hours_since_last, 1)
    );

    $success = true;
    foreach ($phones as $phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) < 10) {
            continue;
        }

        $result = ganjeh_send_kavenegar_sms($api_key, $phone, $message);
        if ($result !== true) {
            $success = false;
        }
    }

    return $success;
}

/**
 * Send SMS via Kavenegar
 * @return true|WP_Error
 */
function ganjeh_send_kavenegar_sms($api_key, $phone, $message) {
    $url = "https://api.kavenegar.com/v1/{$api_key}/sms/send.json";

    // Get sender from settings
    $sender = get_option('ganjeh_kavenegar_sender', '');

    $body = [
        'receptor' => $phone,
        'message' => $message,
    ];

    if (!empty($sender)) {
        $body['sender'] = $sender;
    }

    $response = wp_remote_post($url, [
        'body' => $body,
        'timeout' => 30,
    ]);

    if (is_wp_error($response)) {
        error_log('Kavenegar SMS Error: ' . $response->get_error_message());
        return $response;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['return']['status']) && $body['return']['status'] == 200) {
        return true;
    }

    $error_message = isset($body['return']['message']) ? $body['return']['message'] : __('خطای ناشناخته', 'ganjeh');
    $error_code = isset($body['return']['status']) ? $body['return']['status'] : 'unknown';
    error_log('Kavenegar SMS Error: ' . print_r($body, true));
    return new WP_Error('kavenegar_error', $error_message, ['status' => $error_code]);
}

/**
 * AJAX handler for test SMS
 */
function ganjeh_test_monitoring_sms_ajax() {
    check_ajax_referer('ganjeh_monitoring_nonce', 'nonce');

    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => __('دسترسی غیرمجاز', 'ganjeh')]);
    }

    $options = get_option('ganjeh_order_monitoring_options', []);

    if (empty($options['phone_numbers'])) {
        wp_send_json_error(['message' => __('شماره موبایلی تنظیم نشده است', 'ganjeh')]);
    }

    // Get API key
    $api_key = get_option('woocommerce_kavenegar_api_key');
    if (!$api_key) {
        $api_key = get_option('ganjeh_kavenegar_api_key');
    }
    if (!$api_key) {
        $sms_settings = get_option('ganjeh_sms_settings', []);
        $api_key = isset($sms_settings['api_key']) ? $sms_settings['api_key'] : '';
    }

    if (!$api_key) {
        wp_send_json_error(['message' => __('کلید API کاوه‌نگار یافت نشد', 'ganjeh')]);
    }

    // Check sender
    $sender = get_option('ganjeh_kavenegar_sender', '');
    if (empty($sender)) {
        wp_send_json_error(['message' => __('شماره ارسال کننده در تنظیمات پیامک تنظیم نشده است', 'ganjeh')]);
    }

    $phones = array_filter(array_map('trim', explode("\n", $options['phone_numbers'])));
    $first_phone = preg_replace('/[^0-9]/', '', $phones[0]);

    $result = ganjeh_send_kavenegar_sms($api_key, $first_phone, 'تست سیستم مانیتورینگ سفارشات گنجه');

    if ($result === true) {
        wp_send_json_success(['message' => __('پیامک تست با موفقیت ارسال شد', 'ganjeh')]);
    } else {
        $error_msg = is_wp_error($result) ? $result->get_error_message() : __('خطا در ارسال پیامک', 'ganjeh');
        wp_send_json_error(['message' => $error_msg]);
    }
}
add_action('wp_ajax_ganjeh_test_monitoring_sms', 'ganjeh_test_monitoring_sms_ajax');
