<?php
/**
 * Payment Reminder SMS System
 *
 * Sends SMS payment link to customers who have pending orders after 20 minutes.
 *
 * Uses WooCommerce Action Scheduler (reliable, not dependent on site traffic)
 * + a fallback wp-cron sweep for any missed orders.
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

// ─── 1. Per-Order Scheduling via Action Scheduler ────────────────────────────

/**
 * When a new order is created with pending status, schedule a reminder for 20 min later.
 */
add_action('woocommerce_new_order', 'ganjeh_schedule_reminder_for_order', 10, 2);
function ganjeh_schedule_reminder_for_order($order_id, $order = null) {
    if (!$order) {
        $order = wc_get_order($order_id);
    }
    if (!$order) {
        return;
    }

    // Only schedule for pending payment orders
    if ($order->get_status() !== 'pending') {
        return;
    }

    // Skip free orders
    if (floatval($order->get_total()) == 0) {
        return;
    }

    // Schedule a single action 20 minutes from now using Action Scheduler
    if (function_exists('as_schedule_single_action')) {
        // Unschedule any existing reminder for this order first
        as_unschedule_all_actions('ganjeh_send_payment_reminder_sms', ['order_id' => $order_id]);

        as_schedule_single_action(
            time() + (20 * 60), // 20 minutes from now
            'ganjeh_send_payment_reminder_sms',
            ['order_id' => $order_id],
            'ganjeh-payment-reminder'
        );

        error_log("Ganjeh Payment Reminder: Scheduled reminder for order #{$order_id} in 20 minutes.");
    } else {
        // Fallback: use wp_schedule_single_event
        wp_schedule_single_event(
            time() + (20 * 60),
            'ganjeh_send_payment_reminder_sms_wp',
            [$order_id]
        );
    }
}

/**
 * Also schedule when order status changes TO pending (e.g. failed -> pending)
 */
add_action('woocommerce_order_status_pending', 'ganjeh_schedule_reminder_on_pending', 10, 1);
function ganjeh_schedule_reminder_on_pending($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    // Don't reschedule if reminder was already sent
    if ($order->get_meta('_ganjeh_payment_reminder_sent')) {
        return;
    }

    ganjeh_schedule_reminder_for_order($order_id, $order);
}

/**
 * Cancel scheduled reminder when order is paid or cancelled
 */
add_action('woocommerce_order_status_changed', 'ganjeh_cancel_reminder_on_status_change', 10, 3);
function ganjeh_cancel_reminder_on_status_change($order_id, $old_status, $new_status) {
    // If order moves away from pending, cancel the reminder
    if ($old_status === 'pending' && $new_status !== 'pending') {
        if (function_exists('as_unschedule_all_actions')) {
            as_unschedule_all_actions('ganjeh_send_payment_reminder_sms', ['order_id' => $order_id]);
        }
        wp_clear_scheduled_hook('ganjeh_send_payment_reminder_sms_wp', [$order_id]);

        error_log("Ganjeh Payment Reminder: Cancelled reminder for order #{$order_id} (status: {$new_status})");
    }
}

// ─── 2. The actual SMS sending action ────────────────────────────────────────

/**
 * Action Scheduler callback: send SMS for a specific order
 */
add_action('ganjeh_send_payment_reminder_sms', 'ganjeh_send_reminder_for_order');
add_action('ganjeh_send_payment_reminder_sms_wp', 'ganjeh_send_reminder_for_order');
function ganjeh_send_reminder_for_order($order_id) {
    // Support both array arg (Action Scheduler) and direct arg (wp-cron)
    if (is_array($order_id) && isset($order_id['order_id'])) {
        $order_id = $order_id['order_id'];
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        error_log("Ganjeh Payment Reminder: Order #{$order_id} not found.");
        return;
    }

    // Check if order is still pending
    if ($order->get_status() !== 'pending') {
        error_log("Ganjeh Payment Reminder: Order #{$order_id} is no longer pending (status: {$order->get_status()}). Skipping.");
        return;
    }

    // Check if reminder was already sent
    if ($order->get_meta('_ganjeh_payment_reminder_sent')) {
        error_log("Ganjeh Payment Reminder: Order #{$order_id} already received a reminder. Skipping.");
        return;
    }

    // Skip free orders
    if (floatval($order->get_total()) == 0) {
        return;
    }

    // Get phone number
    $phone = $order->get_billing_phone();
    if (empty($phone)) {
        error_log("Ganjeh Payment Reminder: Order #{$order_id} has no phone number. Skipping.");
        return;
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
        $order->update_meta_data('_ganjeh_payment_reminder_sent', current_time('mysql'));
        $order->save();

        $order->add_order_note(
            __('پیامک یادآوری پرداخت به مشتری ارسال شد.', 'ganjeh')
        );

        error_log("Ganjeh Payment Reminder: SMS sent successfully for order #{$order_id}");
    } else {
        $error_msg = is_wp_error($result) ? $result->get_error_message() : 'Unknown error';
        error_log("Ganjeh Payment Reminder: Failed to send SMS for order #{$order_id} - {$error_msg}");
    }
}

// ─── 3. Fallback: wp-cron sweep for missed orders ───────────────────────────

add_filter('cron_schedules', 'ganjeh_payment_reminder_cron_schedule');
function ganjeh_payment_reminder_cron_schedule($schedules) {
    $schedules['every_ten_minutes'] = [
        'interval' => 600,
        'display'  => __('هر ۱۰ دقیقه', 'ganjeh'),
    ];
    return $schedules;
}

add_action('init', 'ganjeh_schedule_payment_reminder');
function ganjeh_schedule_payment_reminder() {
    // Clear old 5-minute cron if exists
    if (wp_next_scheduled('ganjeh_payment_reminder_cron')) {
        wp_clear_scheduled_hook('ganjeh_payment_reminder_cron');
    }

    if (!wp_next_scheduled('ganjeh_payment_reminder_sweep')) {
        wp_schedule_event(time(), 'every_ten_minutes', 'ganjeh_payment_reminder_sweep');
    }
}

add_action('switch_theme', 'ganjeh_clear_payment_reminder_cron');
function ganjeh_clear_payment_reminder_cron() {
    wp_clear_scheduled_hook('ganjeh_payment_reminder_sweep');
    wp_clear_scheduled_hook('ganjeh_payment_reminder_cron');
}

/**
 * Sweep: catch any pending orders that were missed by per-order scheduling
 */
add_action('ganjeh_payment_reminder_sweep', 'ganjeh_process_payment_reminders');
function ganjeh_process_payment_reminders() {
    error_log('Ganjeh Payment Reminder Sweep: Started at ' . current_time('mysql'));

    $twenty_minutes_ago = gmdate('Y-m-d H:i:s', time() - (20 * 60));
    $twenty_four_hours_ago = gmdate('Y-m-d H:i:s', time() - (24 * 3600));

    $orders = wc_get_orders([
        'status'       => 'pending',
        'date_created' => $twenty_four_hours_ago . '...' . $twenty_minutes_ago,
        'limit'        => 50,
        'orderby'      => 'date',
        'order'        => 'ASC',
    ]);

    if (empty($orders)) {
        error_log('Ganjeh Payment Reminder Sweep: No pending orders found.');
        return;
    }

    $sent_count = 0;
    foreach ($orders as $order) {
        if ($order->get_meta('_ganjeh_payment_reminder_sent')) {
            continue;
        }

        // This order was missed - send now
        ganjeh_send_reminder_for_order($order->get_id());
        $sent_count++;
    }

    error_log("Ganjeh Payment Reminder Sweep: Processed {$sent_count} missed orders.");
}

// ─── 4. Admin page ──────────────────────────────────────────────────────────

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

function ganjeh_payment_reminder_page() {
    // Handle manual trigger
    if (isset($_POST['ganjeh_trigger_reminder']) && check_admin_referer('ganjeh_reminder_nonce')) {
        ganjeh_process_payment_reminders();
        echo '<div class="notice notice-success"><p>بررسی سفارش‌ها انجام شد. لاگ‌ها رو چک کنید.</p></div>';
    }

    // Handle test SMS
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

    // Handle test for specific order
    if (isset($_POST['ganjeh_test_order_reminder']) && check_admin_referer('ganjeh_reminder_nonce')) {
        $test_order_id = absint($_POST['test_order_id']);
        if ($test_order_id) {
            $order = wc_get_order($test_order_id);
            if ($order) {
                // Temporarily remove the sent flag for testing
                $was_sent = $order->get_meta('_ganjeh_payment_reminder_sent');
                if ($was_sent) {
                    $order->delete_meta_data('_ganjeh_payment_reminder_sent');
                    $order->save();
                }
                ganjeh_send_reminder_for_order($test_order_id);
                echo '<div class="notice notice-success"><p>پیامک برای سفارش #' . $test_order_id . ' ارسال شد. لاگ‌ها رو چک کنید.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>سفارش پیدا نشد.</p></div>';
            }
        }
    }

    $api_key = get_option('ganjeh_kavenegar_api_key', '');
    $has_action_scheduler = function_exists('as_schedule_single_action');
    $next_sweep = wp_next_scheduled('ganjeh_payment_reminder_sweep');

    // Count pending orders
    $twenty_minutes_ago = gmdate('Y-m-d H:i:s', time() - (20 * 60));
    $pending_orders = wc_get_orders([
        'status'       => 'pending',
        'date_created' => '<' . $twenty_minutes_ago,
        'limit'        => -1,
        'return'       => 'ids',
    ]);

    $unsent_count = 0;
    foreach ($pending_orders as $oid) {
        $order = wc_get_order($oid);
        if ($order && !$order->get_meta('_ganjeh_payment_reminder_sent')) {
            $unsent_count++;
        }
    }

    // Check scheduled actions
    $scheduled_reminders = 0;
    if ($has_action_scheduler && function_exists('as_get_scheduled_actions')) {
        $actions = as_get_scheduled_actions([
            'hook'   => 'ganjeh_send_payment_reminder_sms',
            'status' => \ActionScheduler_Store::STATUS_PENDING,
        ]);
        $scheduled_reminders = count($actions);
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
                    <th>Action Scheduler</th>
                    <td>
                        <?php if ($has_action_scheduler): ?>
                            <span style="color:green;">&#10003; فعال</span>
                            — <strong><?php echo $scheduled_reminders; ?></strong> یادآوری در صف
                        <?php else: ?>
                            <span style="color:orange;">&#9888; ندارد - از wp-cron استفاده میشه</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Sweep بعدی (فالبک)</th>
                    <td>
                        <?php if ($next_sweep): ?>
                            <span style="color:green;">&#10003; فعال</span>
                            — <code dir="ltr"><?php echo date_i18n('Y-m-d H:i:s', $next_sweep); ?></code>
                        <?php else: ?>
                            <span style="color:red;">&#10007; ثبت نشده</span>
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
            <h2 style="margin-top:0;">نحوه کار</h2>
            <ol style="line-height:2;">
                <li>مشتری سفارش ثبت می‌کنه ← یه تایمر ۲۰ دقیقه‌ای تنظیم میشه</li>
                <li>بعد ۲۰ دقیقه Action Scheduler چک می‌کنه: اگه سفارش هنوز pending باشه ← پیامک ارسال میشه</li>
                <li>اگه مشتری قبل از ۲۰ دقیقه پرداخت کنه ← تایمر کنسل میشه</li>
                <li>یه sweep هر ۱۰ دقیقه هم هست که سفارش‌های از قلم افتاده رو پیدا کنه</li>
            </ol>
        </div>

        <div class="card" style="max-width:700px;padding:20px;margin-bottom:20px;">
            <h2 style="margin-top:0;">اجرای دستی (sweep)</h2>
            <p>همه سفارش‌های pending بالای ۲۰ دقیقه رو الان چک کن:</p>
            <form method="post">
                <?php wp_nonce_field('ganjeh_reminder_nonce'); ?>
                <button type="submit" name="ganjeh_trigger_reminder" class="button button-primary">
                    اجرای الان
                </button>
            </form>
        </div>

        <div class="card" style="max-width:700px;padding:20px;margin-bottom:20px;">
            <h2 style="margin-top:0;">تست پیامک</h2>
            <form method="post" style="margin-bottom:15px;">
                <?php wp_nonce_field('ganjeh_reminder_nonce'); ?>
                <label>شماره تست:</label>
                <input type="text" name="test_phone" placeholder="09123456789" dir="ltr"
                       style="width:180px;padding:5px;" required>
                <button type="submit" name="ganjeh_test_reminder_sms" class="button">
                    ارسال پیامک تست
                </button>
            </form>
            <form method="post">
                <?php wp_nonce_field('ganjeh_reminder_nonce'); ?>
                <label>تست برای سفارش خاص:</label>
                <input type="number" name="test_order_id" placeholder="شماره سفارش" dir="ltr"
                       style="width:140px;padding:5px;" required>
                <button type="submit" name="ganjeh_test_order_reminder" class="button">
                    ارسال یادآوری
                </button>
            </form>
        </div>
    </div>
    <?php
}
