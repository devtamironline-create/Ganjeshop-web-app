<?php
/**
 * Payment Reminder SMS System
 *
 * Automatically sends SMS payment link to customers
 * who have pending orders after 20 minutes.
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register custom cron schedule (every 5 minutes)
 */
add_filter('cron_schedules', 'ganjeh_payment_reminder_cron_schedule');
function ganjeh_payment_reminder_cron_schedule($schedules) {
    $schedules['every_five_minutes'] = [
        'interval' => 300,
        'display'  => __('هر ۵ دقیقه', 'ganjeh'),
    ];
    return $schedules;
}

/**
 * Schedule the payment reminder cron event
 * Use 'init' instead of 'wp' so it fires on admin/AJAX requests too
 */
add_action('init', 'ganjeh_schedule_payment_reminder');
function ganjeh_schedule_payment_reminder() {
    if (!wp_next_scheduled('ganjeh_payment_reminder_cron')) {
        wp_schedule_event(time(), 'every_five_minutes', 'ganjeh_payment_reminder_cron');
    }
}

/**
 * Clear cron on theme switch
 */
add_action('switch_theme', 'ganjeh_clear_payment_reminder_cron');
function ganjeh_clear_payment_reminder_cron() {
    wp_clear_scheduled_hook('ganjeh_payment_reminder_cron');
}

/**
 * Cron callback: check pending orders and send reminder SMS
 */
add_action('ganjeh_payment_reminder_cron', 'ganjeh_process_payment_reminders');
function ganjeh_process_payment_reminders() {
    error_log('Ganjeh Payment Reminder: Cron started at ' . current_time('mysql'));

    // Use WordPress local time for consistency with WooCommerce date storage
    $twenty_minutes_ago = gmdate('Y-m-d H:i:s', time() - (20 * 60));

    // Don't use meta_query - it's unreliable with WooCommerce HPOS
    // Instead, fetch recent pending orders and filter in PHP
    $orders = wc_get_orders([
        'status'       => 'pending',
        'date_created' => '<' . $twenty_minutes_ago,
        'limit'        => 50,
        'orderby'      => 'date',
        'order'        => 'ASC',
    ]);

    if (empty($orders)) {
        error_log('Ganjeh Payment Reminder: No pending orders older than 20 minutes found.');
        return;
    }

    error_log('Ganjeh Payment Reminder: Found ' . count($orders) . ' pending orders to check.');

    $sent_count = 0;
    $skipped_count = 0;

    foreach ($orders as $order) {
        $order_id = $order->get_id();

        // Skip if reminder already sent
        if ($order->get_meta('_ganjeh_payment_reminder_sent')) {
            $skipped_count++;
            continue;
        }

        // Skip orders with zero total (free orders)
        if (floatval($order->get_total()) == 0) {
            error_log("Ganjeh Payment Reminder: Skipping order #{$order_id} - free order.");
            continue;
        }

        // Get customer phone number
        $phone = $order->get_billing_phone();
        if (empty($phone)) {
            error_log("Ganjeh Payment Reminder: Skipping order #{$order_id} - no phone number.");
            continue;
        }

        // Skip very old orders (older than 24 hours) to avoid spamming
        $order_date = $order->get_date_created();
        if ($order_date) {
            $order_timestamp = $order_date->getTimestamp();
            $hours_old = (time() - $order_timestamp) / 3600;
            if ($hours_old > 24) {
                // Mark as sent so we don't keep checking it
                $order->update_meta_data('_ganjeh_payment_reminder_sent', 'skipped_old');
                $order->save();
                $skipped_count++;
                continue;
            }
        }

        // Build direct payment URL
        $payment_url = add_query_arg([
            'direct_pay' => '1',
            'order'      => $order_id,
            'key'        => $order->get_order_key(),
        ], home_url('/'));

        // Build SMS message
        $order_total = strip_tags(wc_price($order->get_total()));
        $message = "سفارش شما به شماره {$order->get_order_number()} به مبلغ {$order_total} در انتظار پرداخت است.\nبرای پرداخت روی لینک زیر کلیک کنید:\n{$payment_url}";

        error_log("Ganjeh Payment Reminder: Sending SMS for order #{$order_id} to {$phone}");

        // Send SMS
        $result = ganjeh_send_sms($phone, $message);

        if ($result === true) {
            // Mark order so we don't send again
            $order->update_meta_data('_ganjeh_payment_reminder_sent', current_time('mysql'));
            $order->save();

            // Add order note
            $order->add_order_note(
                __('پیامک یادآوری پرداخت به مشتری ارسال شد.', 'ganjeh')
            );

            $sent_count++;
            error_log("Ganjeh Payment Reminder: SMS sent successfully for order #{$order_id}");
        } else {
            $error_msg = is_wp_error($result) ? $result->get_error_message() : 'Unknown error';
            error_log("Ganjeh Payment Reminder: Failed to send SMS for order #{$order_id} - {$error_msg}");
        }
    }

    error_log("Ganjeh Payment Reminder: Done. Sent: {$sent_count}, Skipped: {$skipped_count}");
}

/**
 * Admin menu for payment reminder status
 */
add_action('admin_menu', 'ganjeh_payment_reminder_menu');
function ganjeh_payment_reminder_menu() {
    add_submenu_page(
        'woocommerce',
        __('یادآوری پرداخت', 'ganjeh'),
        __('یادآوری پرداخت', 'ganjeh'),
        'manage_options',
        'ganjeh-payment-reminder',
        'ganjeh_payment_reminder_page'
    );
}

/**
 * Admin page: show status and allow manual trigger
 */
function ganjeh_payment_reminder_page() {
    // Handle manual trigger
    if (isset($_POST['ganjeh_trigger_reminder']) && check_admin_referer('ganjeh_reminder_nonce')) {
        ganjeh_process_payment_reminders();
        echo '<div class="notice notice-success"><p>بررسی سفارش‌ها انجام شد. لاگ‌ها رو چک کنید.</p></div>';
    }

    // Handle manual test SMS
    if (isset($_POST['ganjeh_test_reminder_sms']) && check_admin_referer('ganjeh_reminder_nonce')) {
        $test_phone = sanitize_text_field($_POST['test_phone']);
        if (!empty($test_phone)) {
            $result = ganjeh_send_sms($test_phone, 'تست سیستم یادآوری پرداخت گنجه - این پیام تستی است.');
            if ($result === true) {
                echo '<div class="notice notice-success"><p>پیامک تست با موفقیت ارسال شد.</p></div>';
            } else {
                $err = is_wp_error($result) ? $result->get_error_message() : 'خطای نامشخص';
                echo '<div class="notice notice-error"><p>خطا در ارسال: ' . esc_html($err) . '</p></div>';
            }
        }
    }

    // Get cron status
    $next_run = wp_next_scheduled('ganjeh_payment_reminder_cron');
    $api_key = get_option('ganjeh_kavenegar_api_key', '');

    // Get pending orders count (for info)
    $twenty_minutes_ago = gmdate('Y-m-d H:i:s', time() - (20 * 60));
    $pending_orders = wc_get_orders([
        'status'       => 'pending',
        'date_created' => '<' . $twenty_minutes_ago,
        'limit'        => -1,
        'return'       => 'ids',
    ]);

    // Filter: how many haven't received reminder yet
    $unsent_count = 0;
    foreach ($pending_orders as $oid) {
        $order = wc_get_order($oid);
        if ($order && !$order->get_meta('_ganjeh_payment_reminder_sent')) {
            $unsent_count++;
        }
    }

    ?>
    <div class="wrap">
        <h1>یادآوری پرداخت (پیامک خودکار)</h1>

        <div class="card" style="max-width:700px;padding:20px;margin-bottom:20px;">
            <h2 style="margin-top:0;">وضعیت سیستم</h2>
            <table class="form-table">
                <tr>
                    <th>API Key کاوه‌نگار</th>
                    <td>
                        <?php if (!empty($api_key)): ?>
                            <span style="color:green;">&#10003; تنظیم شده</span>
                            <code dir="ltr"><?php echo esc_html(substr($api_key, 0, 8) . '...'); ?></code>
                        <?php else: ?>
                            <span style="color:red;">&#10007; تنظیم نشده!</span>
                            <a href="<?php echo admin_url('admin.php?page=ganjeh-sms-settings'); ?>">تنظیمات پیامک</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>کرون بعدی</th>
                    <td>
                        <?php if ($next_run): ?>
                            <span style="color:green;">&#10003; فعال</span>
                            — اجرای بعدی: <code dir="ltr"><?php echo date_i18n('Y-m-d H:i:s', $next_run); ?></code>
                            (<?php
                                $diff = $next_run - time();
                                if ($diff > 0) {
                                    echo intval($diff / 60) . ' دقیقه و ' . ($diff % 60) . ' ثانیه دیگه';
                                } else {
                                    echo 'در انتظار اجرا (صفحه‌ای باز بشه اجرا میشه)';
                                }
                            ?>)
                        <?php else: ?>
                            <span style="color:red;">&#10007; کرون ثبت نشده!</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>سفارش‌های در انتظار</th>
                    <td>
                        <strong><?php echo count($pending_orders); ?></strong> سفارش pending بیشتر از ۲۰ دقیقه
                        — <strong><?php echo $unsent_count; ?></strong> تاش هنوز پیامک نخورده
                    </td>
                </tr>
            </table>
        </div>

        <div class="card" style="max-width:700px;padding:20px;margin-bottom:20px;">
            <h2 style="margin-top:0;">اجرای دستی</h2>
            <p>با این دکمه سیستم یادآوری رو الان اجرا کنید (بدون نیاز به کرون):</p>
            <form method="post">
                <?php wp_nonce_field('ganjeh_reminder_nonce'); ?>
                <button type="submit" name="ganjeh_trigger_reminder" class="button button-primary">
                    اجرای الان
                </button>
            </form>
        </div>

        <div class="card" style="max-width:700px;padding:20px;">
            <h2 style="margin-top:0;">تست پیامک</h2>
            <p>یه پیامک تستی بفرستید تا مطمئن بشید سیستم کار می‌کنه:</p>
            <form method="post">
                <?php wp_nonce_field('ganjeh_reminder_nonce'); ?>
                <input type="text" name="test_phone" placeholder="09123456789" dir="ltr"
                       style="width:200px;padding:5px;" required>
                <button type="submit" name="ganjeh_test_reminder_sms" class="button">
                    ارسال تست
                </button>
            </form>
        </div>
    </div>
    <?php
}
