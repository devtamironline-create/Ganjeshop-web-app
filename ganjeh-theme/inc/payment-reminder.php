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
 */
add_action('wp', 'ganjeh_schedule_payment_reminder');
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
    // Get pending orders older than 20 minutes
    $twenty_minutes_ago = date('Y-m-d H:i:s', strtotime('-20 minutes'));

    $orders = wc_get_orders([
        'status'       => 'pending',
        'date_created' => '<' . $twenty_minutes_ago,
        'limit'        => 20,
        'orderby'      => 'date',
        'order'        => 'ASC',
        'meta_query'   => [
            [
                'key'     => '_ganjeh_payment_reminder_sent',
                'compare' => 'NOT EXISTS',
            ],
        ],
    ]);

    if (empty($orders)) {
        return;
    }

    foreach ($orders as $order) {
        // Double-check: skip if reminder already sent
        if ($order->get_meta('_ganjeh_payment_reminder_sent')) {
            continue;
        }

        // Skip orders with zero total (free orders)
        if ($order->get_total() == 0) {
            continue;
        }

        // Get customer phone number
        $phone = $order->get_billing_phone();
        if (empty($phone)) {
            continue;
        }

        // Build direct payment URL
        $payment_url = add_query_arg([
            'direct_pay' => '1',
            'order'      => $order->get_id(),
            'key'        => $order->get_order_key(),
        ], home_url('/'));

        // Build SMS message
        $message = "شما یک سفارش باز دارید اگه موفق به پرداخت نشدید میتونید از لینک زیر پرداخت خود رو انجام بدید\n{$payment_url}";

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
        } else {
            // Log error but don't mark as sent so it retries next cycle
            $error_msg = is_wp_error($result) ? $result->get_error_message() : 'Unknown error';
            error_log("Ganjeh Payment Reminder: Failed to send SMS for order #{$order->get_id()} - {$error_msg}");
        }
    }
}
