<?php
/**
 * Authentication AJAX Handlers
 */

if (!defined('ABSPATH')) exit;

/**
 * Send OTP to mobile number
 */
add_action('wp_ajax_nopriv_ganjeh_send_otp', 'ganjeh_ajax_send_otp');
add_action('wp_ajax_ganjeh_send_otp', 'ganjeh_ajax_send_otp');
function ganjeh_ajax_send_otp() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    $mobile = isset($_POST['mobile']) ? sanitize_text_field($_POST['mobile']) : '';

    // Normalize mobile
    $mobile = ganjeh_normalize_mobile($mobile);
    if (!$mobile) {
        wp_send_json_error(['message' => __('شماره موبایل نامعتبر است', 'ganjeh')]);
    }

    // Check rate limiting (max 3 OTP per 10 minutes)
    $rate_key = 'ganjeh_otp_rate_' . $mobile;
    $rate_count = (int) get_transient($rate_key);
    if ($rate_count >= 3) {
        wp_send_json_error(['message' => __('تعداد درخواست زیاد است. لطفاً چند دقیقه صبر کنید.', 'ganjeh')]);
    }

    // Generate 4-digit OTP
    $otp = rand(1000, 9999);

    // Store OTP with 2 minutes expiry
    set_transient('ganjeh_otp_' . $mobile, $otp, 2 * MINUTE_IN_SECONDS);

    // Update rate limit
    set_transient($rate_key, $rate_count + 1, 10 * MINUTE_IN_SECONDS);

    // Send OTP via Kavenegar
    $result = ganjeh_send_otp($mobile, $otp);

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    }

    // Check if user exists
    $user = get_user_by('login', $mobile);
    $is_new_user = !$user;

    wp_send_json_success([
        'message' => __('کد تایید ارسال شد', 'ganjeh'),
        'is_new_user' => $is_new_user,
    ]);
}

/**
 * Verify OTP and login/register user
 */
add_action('wp_ajax_nopriv_ganjeh_verify_otp', 'ganjeh_ajax_verify_otp');
add_action('wp_ajax_ganjeh_verify_otp', 'ganjeh_ajax_verify_otp');
function ganjeh_ajax_verify_otp() {
    check_ajax_referer('ganjeh_nonce', 'nonce');

    $mobile = isset($_POST['mobile']) ? sanitize_text_field($_POST['mobile']) : '';
    $otp = isset($_POST['otp']) ? sanitize_text_field($_POST['otp']) : '';
    $full_name = isset($_POST['full_name']) ? sanitize_text_field($_POST['full_name']) : '';

    // Split full name into first and last name
    $name_parts = explode(' ', trim($full_name), 2);
    $first_name = $name_parts[0] ?? '';
    $last_name = $name_parts[1] ?? '';

    // Normalize mobile
    $mobile = ganjeh_normalize_mobile($mobile);
    if (!$mobile) {
        wp_send_json_error(['message' => __('شماره موبایل نامعتبر است', 'ganjeh')]);
    }

    // Verify OTP
    $stored_otp = get_transient('ganjeh_otp_' . $mobile);
    if (!$stored_otp || $stored_otp != $otp) {
        wp_send_json_error(['message' => __('کد تایید نادرست یا منقضی شده است', 'ganjeh')]);
    }

    // Delete used OTP
    delete_transient('ganjeh_otp_' . $mobile);

    // Check if user exists
    $user = get_user_by('login', $mobile);

    if ($user) {
        // Login existing user
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);

        wp_send_json_success([
            'message' => __('با موفقیت وارد شدید', 'ganjeh'),
            'redirect' => false,
            'user_name' => $user->display_name,
        ]);
    } else {
        // Register new user
        if (empty($full_name)) {
            wp_send_json_error([
                'message' => __('لطفاً نام و نام خانوادگی خود را وارد کنید', 'ganjeh'),
            ]);
        }

        $user_id = wp_insert_user([
            'user_login' => $mobile,
            'user_pass' => wp_generate_password(),
            'display_name' => $full_name,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => 'customer',
        ]);

        if (is_wp_error($user_id)) {
            wp_send_json_error(['message' => $user_id->get_error_message()]);
        }

        // Save mobile as billing phone
        update_user_meta($user_id, 'billing_phone', $mobile);
        update_user_meta($user_id, 'billing_first_name', $first_name);
        update_user_meta($user_id, 'billing_last_name', $last_name);

        // Login the new user
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);

        wp_send_json_success([
            'message' => __('ثبت نام با موفقیت انجام شد', 'ganjeh'),
            'redirect' => false,
            'user_name' => $full_name,
        ]);
    }
}

/**
 * Check login status
 */
add_action('wp_ajax_nopriv_ganjeh_check_login', 'ganjeh_ajax_check_login');
add_action('wp_ajax_ganjeh_check_login', 'ganjeh_ajax_check_login');
function ganjeh_ajax_check_login() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        wp_send_json_success([
            'logged_in' => true,
            'user_name' => $user->display_name,
        ]);
    } else {
        wp_send_json_success([
            'logged_in' => false,
        ]);
    }
}

/**
 * Logout user
 */
add_action('wp_ajax_ganjeh_logout', 'ganjeh_ajax_logout');
function ganjeh_ajax_logout() {
    check_ajax_referer('ganjeh_nonce', 'nonce');
    wp_logout();
    wp_send_json_success(['message' => __('با موفقیت خارج شدید', 'ganjeh')]);
}
