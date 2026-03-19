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
        update_option('ganjeh_sms_proxy_enabled', isset($_POST['sms_proxy_enabled']) ? '1' : '0');
        update_option('ganjeh_sms_proxy_url', esc_url_raw($_POST['sms_proxy_url']));
        update_option('ganjeh_sms_proxy_secret', sanitize_text_field($_POST['sms_proxy_secret']));
        update_option('ganjeh_bale_enabled', isset($_POST['bale_enabled']) ? '1' : '0');
        update_option('ganjeh_bale_api_key', sanitize_text_field($_POST['bale_api_key']));
        update_option('ganjeh_bale_bot_id', sanitize_text_field($_POST['bale_bot_id']));
        echo '<div class="notice notice-success"><p>' . __('تنظیمات ذخیره شد.', 'ganjeh') . '</p></div>';
    }

    $api_key = get_option('ganjeh_kavenegar_api_key', '');
    $template = get_option('ganjeh_kavenegar_template', '');
    $sender = get_option('ganjeh_kavenegar_sender', '');
    $proxy_enabled = get_option('ganjeh_sms_proxy_enabled', '0');
    $proxy_url = get_option('ganjeh_sms_proxy_url', 'https://api.ganjemarket.com');
    $proxy_secret = get_option('ganjeh_sms_proxy_secret', '');
    $bale_enabled = get_option('ganjeh_bale_enabled', '0');
    $bale_api_key = get_option('ganjeh_bale_api_key', '');
    $bale_bot_id = get_option('ganjeh_bale_bot_id', '');
    ?>
    <div class="wrap">
        <h1><?php _e('تنظیمات پیامک و پیام‌رسان', 'ganjeh'); ?></h1>

        <form method="post" action="">
            <?php wp_nonce_field('ganjeh_sms_nonce'); ?>

            <h2><?php _e('کاوه نگار (پیامک)', 'ganjeh'); ?></h2>
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

            <hr>
            <h2><?php _e('پروکسی ارسال پیامک', 'ganjeh'); ?></h2>
            <p class="description"><?php _e('اگر IP سرور شما توسط کاوه نگار بلاک شده، می‌توانید درخواست‌ها را از طریق یک سرور واسط ارسال کنید.', 'ganjeh'); ?></p>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="sms_proxy_enabled"><?php _e('روش ارسال', 'ganjeh'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="sms_proxy_enabled" id="sms_proxy_enabled" value="1" <?php checked($proxy_enabled, '1'); ?>>
                            <?php _e('ارسال از طریق پروکسی (سرور واسط)', 'ganjeh'); ?>
                        </label>
                        <p class="description"><?php _e('اگر غیرفعال باشد، مستقیم به کاوه نگار درخواست داده می‌شود.', 'ganjeh'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sms_proxy_url"><?php _e('آدرس پروکسی', 'ganjeh'); ?></label>
                    </th>
                    <td>
                        <input type="url" name="sms_proxy_url" id="sms_proxy_url" value="<?php echo esc_attr($proxy_url); ?>" class="regular-text" dir="ltr" placeholder="https://api.ganjemarket.com">
                        <p class="description"><?php _e('آدرس سرور واسط (بدون / انتهایی)', 'ganjeh'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sms_proxy_secret"><?php _e('کلید امنیتی پروکسی', 'ganjeh'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="sms_proxy_secret" id="sms_proxy_secret" value="<?php echo esc_attr($proxy_secret); ?>" class="regular-text" dir="ltr">
                        <p class="description"><?php _e('یک کلید مشترک بین سایت و سرور پروکسی برای احراز هویت درخواست‌ها', 'ganjeh'); ?></p>
                    </td>
                </tr>
            </table>

            <hr>
            <h2><?php _e('پیام‌رسان بله (OTP)', 'ganjeh'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="bale_enabled"><?php _e('فعال‌سازی بله', 'ganjeh'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="bale_enabled" id="bale_enabled" value="1" <?php checked($bale_enabled, '1'); ?>>
                            <?php _e('ارسال همزمان کد تایید از طریق بله', 'ganjeh'); ?>
                        </label>
                        <p class="description"><?php _e('در صورت فعال بودن، کد تایید هم از طریق پیامک و هم از طریق بله ارسال می‌شود.', 'ganjeh'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="bale_api_key"><?php _e('کلید API (api-access-key)', 'ganjeh'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="bale_api_key" id="bale_api_key" value="<?php echo esc_attr($bale_api_key); ?>" class="regular-text" dir="ltr">
                        <p class="description"><?php _e('کلید دسترسی API دریافت شده از درگاه بله', 'ganjeh'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="bale_bot_id"><?php _e('شناسه بازو (Bot ID)', 'ganjeh'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="bale_bot_id" id="bale_bot_id" value="<?php echo esc_attr($bale_bot_id); ?>" class="regular-text" dir="ltr">
                        <p class="description"><?php _e('شناسه بازویی که نام آن در پیام OTP نمایش داده می‌شود', 'ganjeh'); ?></p>
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
        <h3><?php _e('کاوه نگار', 'ganjeh'); ?></h3>
        <ol>
            <li><?php _e('وارد پنل کاوه نگار شوید', 'ganjeh'); ?></li>
            <li><?php _e('یک قالب تایید (Verify) با پارامتر token بسازید', 'ganjeh'); ?></li>
            <li><?php _e('API Key و نام قالب را در اینجا وارد کنید', 'ganjeh'); ?></li>
        </ol>
        <h3><?php _e('پیام‌رسان بله', 'ganjeh'); ?></h3>
        <ol>
            <li><?php _e('وارد سامانه درگاه بله (safir.bale.ai) شوید', 'ganjeh'); ?></li>
            <li><?php _e('کلید API (api-access-key) و شناسه بازو (Bot ID) را دریافت کنید', 'ganjeh'); ?></li>
            <li><?php _e('اطلاعات را در بخش بالا وارد و تیک فعال‌سازی را بزنید', 'ganjeh'); ?></li>
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

    $proxy_enabled = get_option('ganjeh_sms_proxy_enabled', '0');

    if ($proxy_enabled === '1') {
        // Send via proxy
        return ganjeh_send_via_proxy('verify', [
            'api_key'  => $api_key,
            'receptor' => $mobile,
            'token'    => $code,
            'template' => $template,
        ]);
    }

    // Direct Kavenegar
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

    $proxy_enabled = get_option('ganjeh_sms_proxy_enabled', '0');

    if ($proxy_enabled === '1') {
        // Send via proxy
        $params = [
            'api_key'  => $api_key,
            'receptor' => $mobile,
            'message'  => $message,
        ];
        if (!empty($sender)) {
            $params['sender'] = $sender;
        }
        return ganjeh_send_via_proxy('send', $params);
    }

    // Direct Kavenegar
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
 * Send SMS request via proxy server
 */
function ganjeh_send_via_proxy($action, $params) {
    $proxy_url = rtrim(get_option('ganjeh_sms_proxy_url', 'https://api.ganjemarket.com'), '/');
    $proxy_secret = get_option('ganjeh_sms_proxy_secret', '');

    $response = wp_remote_post($proxy_url . '/sms-proxy.php', [
        'headers' => [
            'Content-Type'    => 'application/json',
            'X-Proxy-Secret'  => $proxy_secret,
        ],
        'body'    => wp_json_encode([
            'action' => $action,
            'params' => $params,
        ]),
        'timeout' => 30,
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['success']) && $body['success'] === true) {
        return true;
    }

    $message = isset($body['message']) ? $body['message'] : __('خطا در ارسال از طریق پروکسی', 'ganjeh');
    return new WP_Error('proxy_error', $message);
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
 * Send OTP via Bale messenger (API v3)
 */
function ganjeh_send_otp_bale($mobile, $code) {
    $bale_enabled = get_option('ganjeh_bale_enabled', '0');
    if ($bale_enabled !== '1') {
        return new WP_Error('bale_disabled', __('ارسال از طریق بله غیرفعال است', 'ganjeh'));
    }

    $api_key = get_option('ganjeh_bale_api_key', '');
    $bot_id = get_option('ganjeh_bale_bot_id', '');

    if (empty($api_key) || empty($bot_id)) {
        return new WP_Error('bale_config', __('تنظیمات بله انجام نشده است', 'ganjeh'));
    }

    // Convert mobile to Bale format: 98XXXXXXXXX (no leading 0)
    $bale_phone = ganjeh_mobile_to_bale_format($mobile);
    if (!$bale_phone) {
        return new WP_Error('bale_phone', __('فرمت شماره تلفن برای بله نامعتبر است', 'ganjeh'));
    }

    $request_id = wp_generate_uuid4();

    $response = wp_remote_post('https://safir.bale.ai/api/v3/send_message', [
        'headers' => [
            'api-access-key' => $api_key,
            'Content-Type'   => 'application/json',
        ],
        'body'    => wp_json_encode([
            'request_id'   => $request_id,
            'bot_id'       => (int) $bot_id,
            'phone_number' => $bale_phone,
            'message_data' => [
                'otp_message' => [
                    'otp' => (string) $code,
                ],
            ],
        ]),
        'timeout' => 15,
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($status_code === 200 && !empty($body['message_id'])) {
        return true;
    }

    $error_msg = isset($body['message']) ? $body['message'] : __('خطا در ارسال کد از طریق بله', 'ganjeh');
    return new WP_Error('bale_send', $error_msg);
}

/**
 * Convert mobile number to Bale format (98XXXXXXXXX)
 */
function ganjeh_mobile_to_bale_format($mobile) {
    // Normalize first
    $mobile = ganjeh_normalize_mobile($mobile);
    if (!$mobile) {
        return false;
    }

    // Convert 09XXXXXXXXX to 989XXXXXXXXX
    if (substr($mobile, 0, 1) === '0') {
        return '98' . substr($mobile, 1);
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
