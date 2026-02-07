<?php
/**
 * Kavenegar SMS Settings
 */

if (!defined('ABSPATH')) exit;

// Add admin menu
add_action('admin_menu', 'ganjeh_sms_settings_menu');
function ganjeh_sms_settings_menu() {
    add_submenu_page(
        'woocommerce',
        __('تنظیمات پیامک', 'ganjeh'),
        __('تنظیمات پیامک', 'ganjeh'),
        'manage_options',
        'ganjeh-sms-settings',
        'ganjeh_sms_settings_page'
    );
}

// Settings page
function ganjeh_sms_settings_page() {
    if (isset($_POST['ganjeh_sms_save']) && check_admin_referer('ganjeh_sms_nonce')) {
        update_option('ganjeh_kavenegar_api_key', sanitize_text_field($_POST['api_key']));
        update_option('ganjeh_kavenegar_template', sanitize_text_field($_POST['template']));
        update_option('ganjeh_kavenegar_sender', sanitize_text_field($_POST['sender']));
        echo '<div class="notice notice-success"><p>' . __('تنظیمات ذخیره شد.', 'ganjeh') . '</p></div>';
    }

    $api_key = get_option('ganjeh_kavenegar_api_key', '');
    $template = get_option('ganjeh_kavenegar_template', '');
    $sender = get_option('ganjeh_kavenegar_sender', '');
    ?>
    <div class="wrap">
        <h1><?php _e('تنظیمات پیامک کاوه نگار', 'ganjeh'); ?></h1>

        <form method="post" action="">
            <?php wp_nonce_field('ganjeh_sms_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="api_key"><?php _e('API Key', 'ganjeh'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="api_key" id="api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" dir="ltr">
                        <p class="description"><?php _e('کلید API کاوه نگار', 'ganjeh'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="template"><?php _e('نام قالب', 'ganjeh'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="template" id="template" value="<?php echo esc_attr($template); ?>" class="regular-text" dir="ltr">
                        <p class="description"><?php _e('نام قالب تعریف شده در کاوه نگار برای ارسال کد تایید', 'ganjeh'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sender"><?php _e('شماره ارسال کننده', 'ganjeh'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="sender" id="sender" value="<?php echo esc_attr($sender); ?>" class="regular-text" dir="ltr" placeholder="10008663">
                        <p class="description"><?php _e('شماره خط ارسال کننده پیامک (اختیاری)', 'ganjeh'); ?></p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="ganjeh_sms_save" class="button button-primary">
                    <?php _e('ذخیره تنظیمات', 'ganjeh'); ?>
                </button>
            </p>
        </form>

        <hr>
        <h2><?php _e('راهنما', 'ganjeh'); ?></h2>
        <p><?php _e('برای استفاده از این سیستم:', 'ganjeh'); ?></p>
        <ol>
            <li><?php _e('وارد پنل کاوه نگار شوید', 'ganjeh'); ?></li>
            <li><?php _e('یک قالب تایید (Verify) با پارامتر token بسازید', 'ganjeh'); ?></li>
            <li><?php _e('API Key و نام قالب را در اینجا وارد کنید', 'ganjeh'); ?></li>
        </ol>
    </div>
    <?php
}

/**
 * Send OTP via Kavenegar
 */
function ganjeh_send_otp($mobile, $code) {
    $api_key = get_option('ganjeh_kavenegar_api_key', '');
    $template = get_option('ganjeh_kavenegar_template', '');

    if (empty($api_key) || empty($template)) {
        return new WP_Error('config_error', __('تنظیمات پیامک انجام نشده است', 'ganjeh'));
    }

    // Normalize mobile number
    $mobile = ganjeh_normalize_mobile($mobile);
    if (!$mobile) {
        return new WP_Error('invalid_mobile', __('شماره موبایل نامعتبر است', 'ganjeh'));
    }

    // Kavenegar Verify Lookup API
    $url = "https://api.kavenegar.com/v1/{$api_key}/verify/lookup.json";

    $response = wp_remote_post($url, [
        'body' => [
            'receptor' => $mobile,
            'token' => $code,
            'template' => $template,
        ],
        'timeout' => 30,
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['return']['status']) && $body['return']['status'] == 200) {
        return true;
    }

    $message = isset($body['return']['message']) ? $body['return']['message'] : __('خطا در ارسال پیامک', 'ganjeh');
    return new WP_Error('sms_error', $message);
}

/**
 * Send simple SMS via Kavenegar (for payment links, notifications, etc.)
 */
function ganjeh_send_sms($mobile, $message) {
    $api_key = get_option('ganjeh_kavenegar_api_key', '');
    $sender = get_option('ganjeh_kavenegar_sender', '');

    if (empty($api_key)) {
        return new WP_Error('config_error', __('API Key کاوه نگار تنظیم نشده است', 'ganjeh'));
    }

    // Normalize mobile number
    $mobile = ganjeh_normalize_mobile($mobile);
    if (!$mobile) {
        return new WP_Error('invalid_mobile', __('شماره موبایل نامعتبر است', 'ganjeh'));
    }

    // Kavenegar Send SMS API
    $url = "https://api.kavenegar.com/v1/{$api_key}/sms/send.json";

    $body = [
        'receptor' => $mobile,
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
        return $response;
    }

    $result = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($result['return']['status']) && $result['return']['status'] == 200) {
        return true;
    }

    $error_message = isset($result['return']['message']) ? $result['return']['message'] : __('خطا در ارسال پیامک', 'ganjeh');
    return new WP_Error('sms_error', $error_message);
}

/**
 * Normalize mobile number to 09XXXXXXXXX format
 */
function ganjeh_normalize_mobile($mobile) {
    // Remove spaces and non-numeric characters
    $mobile = preg_replace('/[^0-9]/', '', $mobile);

    // Handle +98
    if (substr($mobile, 0, 2) === '98' && strlen($mobile) === 12) {
        $mobile = '0' . substr($mobile, 2);
    }

    // Handle 9XXXXXXXXX (without leading 0)
    if (strlen($mobile) === 10 && substr($mobile, 0, 1) === '9') {
        $mobile = '0' . $mobile;
    }

    // Validate format
    if (preg_match('/^09[0-9]{9}$/', $mobile)) {
        return $mobile;
    }

    return false;
}

/**
 * AJAX handler for sending payment link SMS
 */
add_action('wp_ajax_ganjeh_send_payment_sms', 'ganjeh_send_payment_sms_ajax');
function ganjeh_send_payment_sms_ajax() {
    // Security check
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => __('شما دسترسی لازم را ندارید', 'ganjeh')]);
    }

    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ganjeh_payment_sms_nonce')) {
        wp_send_json_error(['message' => __('خطای امنیتی', 'ganjeh')]);
    }

    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';

    if (!$order_id || !$phone) {
        wp_send_json_error(['message' => __('اطلاعات ناقص است', 'ganjeh')]);
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error(['message' => __('سفارش یافت نشد', 'ganjeh')]);
    }

    // Build payment URL
    $payment_url = add_query_arg([
        'direct_pay' => '1',
        'order' => $order->get_id(),
        'key' => $order->get_order_key(),
    ], home_url('/'));

    // Build SMS message
    $order_total = strip_tags(wc_price($order->get_total()));
    $message = sprintf(
        "سفارش شماره %s به مبلغ %s آماده پرداخت است.\nلینک پرداخت:\n%s",
        $order->get_order_number(),
        $order_total,
        $payment_url
    );

    // Send SMS via Kavenegar
    $result = ganjeh_send_sms($phone, $message);

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    }

    wp_send_json_success(['message' => __('پیامک با موفقیت ارسال شد', 'ganjeh')]);
}
