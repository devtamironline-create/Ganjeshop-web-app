<?php
/**
 * Postcode Backfill Tool
 * Adds an admin page to fix missing postcodes on old orders
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu page under WooCommerce
 */
add_action('admin_menu', function () {
    add_submenu_page(
        'woocommerce',
        __('اصلاح کد پستی سفارشات', 'ganjeh'),
        __('اصلاح کد پستی', 'ganjeh'),
        'manage_woocommerce',
        'ganjeh-postcode-backfill',
        'ganjeh_postcode_backfill_page'
    );
});

/**
 * Convert Persian/Arabic digits to Latin
 */
if (!function_exists('ganjeh_persian_to_latin_digits')) {
    function ganjeh_persian_to_latin_digits($string) {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $latin   = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $string = str_replace($persian, $latin, $string);
        $string = str_replace($arabic, $latin, $string);

        return $string;
    }
}

/**
 * AJAX handler for running backfill
 */
add_action('wp_ajax_ganjeh_run_postcode_backfill', 'ganjeh_run_postcode_backfill');
function ganjeh_run_postcode_backfill() {
    // Clean any previous output (PHP notices/warnings)
    if (ob_get_length()) {
        ob_clean();
    }

    check_ajax_referer('ganjeh_postcode_backfill', 'nonce');

    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => 'دسترسی غیرمجاز']);
        return;
    }

    try {
        $results = [
            'total_orders'       => 0,
            'missing_postcode'   => 0,
            'fixed_from_user'    => 0,
            'fixed_from_address' => 0,
            'fixed_from_regex'   => 0,
            'still_missing'      => 0,
            'missing_ids'        => [],
        ];

        // Get all orders using WooCommerce API (works with both HPOS and legacy)
        $all_orders = wc_get_orders([
            'limit'  => -1,
            'return' => 'ids',
            'type'   => 'shop_order',
        ]);

        $results['total_orders'] = count($all_orders);

        // Find orders without postcode
        $orders_without_postcode = [];
        foreach ($all_orders as $order_id) {
            $order = wc_get_order($order_id);
            if (!$order) continue;

            $billing_postcode = $order->get_billing_postcode();
            if (empty($billing_postcode)) {
                $orders_without_postcode[] = $order;
            }
        }

        $results['missing_postcode'] = count($orders_without_postcode);

        // Process each order without postcode
        foreach ($orders_without_postcode as $order) {
            $order_id = $order->get_id();
            $customer_id = $order->get_customer_id();
            $postcode = '';
            $source = '';

            // Method 1: Try from customer user meta
            if ($customer_id > 0) {
                $user_postcode = get_user_meta($customer_id, 'billing_postcode', true);
                if (!empty($user_postcode)) {
                    $postcode = sanitize_text_field($user_postcode);
                    $source = 'user_meta';
                }
            }

            // Method 2: Try from saved addresses (ganjeh custom addresses)
            if (empty($postcode) && $customer_id > 0) {
                if (function_exists('ganjeh_get_user_addresses')) {
                    $addresses = ganjeh_get_user_addresses($customer_id);
                    if (!empty($addresses) && is_array($addresses)) {
                        foreach ($addresses as $addr) {
                            if (!empty($addr['postcode'])) {
                                $postcode = sanitize_text_field($addr['postcode']);
                                $source = 'saved_address';
                                break;
                            }
                        }
                    }
                }
            }

            // Method 3: Try regex from address fields or order notes
            if (empty($postcode)) {
                $texts_to_search = [];

                $billing_addr = $order->get_billing_address_1();
                if (!empty($billing_addr)) {
                    $texts_to_search[] = $billing_addr;
                }

                $shipping_addr = $order->get_shipping_address_1();
                if (!empty($shipping_addr)) {
                    $texts_to_search[] = $shipping_addr;
                }

                $customer_note = $order->get_customer_note();
                if (!empty($customer_note)) {
                    $texts_to_search[] = $customer_note;
                }

                foreach ($texts_to_search as $text) {
                    // Convert Persian/Arabic digits first
                    $latin_text = ganjeh_persian_to_latin_digits($text);

                    // Iranian postcodes: 10 digits
                    if (preg_match('/(\d{10})/', $latin_text, $matches)) {
                        $postcode = $matches[1];
                        $source = 'regex';
                        break;
                    }
                }
            }

            // Save the postcode if found
            if (!empty($postcode)) {
                $order->set_billing_postcode($postcode);
                $order->set_shipping_postcode($postcode);
                $order->save();

                switch ($source) {
                    case 'user_meta':
                        $results['fixed_from_user']++;
                        break;
                    case 'saved_address':
                        $results['fixed_from_address']++;
                        break;
                    case 'regex':
                        $results['fixed_from_regex']++;
                        break;
                }
            } else {
                $results['still_missing']++;
                $results['missing_ids'][] = $order_id;
            }
        }

        // Clean output buffer before sending JSON
        if (ob_get_length()) {
            ob_clean();
        }

        wp_send_json_success($results);

    } catch (Exception $e) {
        if (ob_get_length()) {
            ob_clean();
        }
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

/**
 * Render admin page
 */
function ganjeh_postcode_backfill_page() {
    $nonce = wp_create_nonce('ganjeh_postcode_backfill');
    ?>
    <div class="wrap" style="max-width: 800px;">
        <h1><?php _e('اصلاح کد پستی سفارشات', 'ganjeh'); ?></h1>
        <p style="font-size: 14px; color: #666; margin-bottom: 20px;">
            <?php _e('این ابزار سفارشاتی که کد پستی ندارند را پیدا کرده و سعی می‌کند از پروفایل مشتری، آدرس‌های ذخیره شده، یا متن آدرس/یادداشت سفارش کد پستی را استخراج و ذخیره کند.', 'ganjeh'); ?>
        </p>

        <div id="backfill-status" style="display:none; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px;">
            <div id="backfill-loading" style="text-align: center; padding: 20px;">
                <span class="spinner is-active" style="float: none;"></span>
                <p><?php _e('در حال پردازش سفارشات...', 'ganjeh'); ?></p>
            </div>
            <div id="backfill-results" style="display: none;">
                <h3 style="margin-top: 0;"><?php _e('نتایج', 'ganjeh'); ?></h3>
                <table class="widefat" style="margin-bottom: 16px;">
                    <tbody>
                        <tr><td><?php _e('کل سفارشات', 'ganjeh'); ?></td><td id="res-total" style="font-weight: bold;"></td></tr>
                        <tr><td><?php _e('سفارشات بدون کد پستی', 'ganjeh'); ?></td><td id="res-missing" style="font-weight: bold; color: #d63638;"></td></tr>
                        <tr style="background: #f0fdf4;"><td><?php _e('اصلاح شده از پروفایل مشتری', 'ganjeh'); ?></td><td id="res-user" style="font-weight: bold; color: #00a32a;"></td></tr>
                        <tr style="background: #f0fdf4;"><td><?php _e('اصلاح شده از آدرس ذخیره شده', 'ganjeh'); ?></td><td id="res-address" style="font-weight: bold; color: #00a32a;"></td></tr>
                        <tr style="background: #f0fdf4;"><td><?php _e('اصلاح شده از متن (regex)', 'ganjeh'); ?></td><td id="res-regex" style="font-weight: bold; color: #00a32a;"></td></tr>
                        <tr style="background: #fef2f2;"><td><?php _e('هنوز بدون کد پستی (نیاز به ورود دستی)', 'ganjeh'); ?></td><td id="res-still" style="font-weight: bold; color: #d63638;"></td></tr>
                    </tbody>
                </table>
                <div id="res-missing-list" style="display: none;">
                    <h4><?php _e('شماره سفارشات بدون کد پستی:', 'ganjeh'); ?></h4>
                    <div id="res-missing-ids" style="padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 13px;"></div>
                </div>
            </div>
        </div>

        <button type="button" id="run-backfill" class="button button-primary button-hero" style="font-size: 16px;">
            <?php _e('اجرای اصلاح کد پستی', 'ganjeh'); ?>
        </button>

        <script>
        document.getElementById('run-backfill').addEventListener('click', function() {
            var btn = this;
            btn.disabled = true;
            btn.textContent = '<?php _e('در حال اجرا...', 'ganjeh'); ?>';

            var statusEl = document.getElementById('backfill-status');
            var loadingEl = document.getElementById('backfill-loading');
            var resultsEl = document.getElementById('backfill-results');

            statusEl.style.display = 'block';
            loadingEl.style.display = 'block';
            resultsEl.style.display = 'none';

            var formData = new FormData();
            formData.append('action', 'ganjeh_run_postcode_backfill');
            formData.append('nonce', '<?php echo esc_attr($nonce); ?>');

            fetch(ajaxurl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(r) {
                var contentType = r.headers.get('content-type');
                if (contentType && contentType.indexOf('application/json') !== -1) {
                    return r.json();
                } else {
                    return r.text().then(function(text) {
                        throw new Error(text.substring(0, 200));
                    });
                }
            })
            .then(function(data) {
                loadingEl.style.display = 'none';
                resultsEl.style.display = 'block';

                if (data.success) {
                    var d = data.data;
                    document.getElementById('res-total').textContent = d.total_orders;
                    document.getElementById('res-missing').textContent = d.missing_postcode;
                    document.getElementById('res-user').textContent = d.fixed_from_user;
                    document.getElementById('res-address').textContent = d.fixed_from_address;
                    document.getElementById('res-regex').textContent = d.fixed_from_regex;
                    document.getElementById('res-still').textContent = d.still_missing;

                    if (d.missing_ids && d.missing_ids.length > 0) {
                        document.getElementById('res-missing-list').style.display = 'block';
                        var links = d.missing_ids.map(function(id) {
                            return '<a href="<?php echo esc_url(admin_url('post.php?action=edit&post=')); ?>' + id + '" target="_blank">#' + id + '</a>';
                        });
                        document.getElementById('res-missing-ids').innerHTML = links.join(' , ');
                    }

                    var totalFixed = d.fixed_from_user + d.fixed_from_address + d.fixed_from_regex;
                    btn.textContent = totalFixed + ' <?php _e('سفارش اصلاح شد', 'ganjeh'); ?>';
                    btn.style.background = '#00a32a';
                } else {
                    resultsEl.innerHTML = '<p style="color: #d63638;"><?php _e('خطا', 'ganjeh'); ?>: ' + (data.data && data.data.message ? data.data.message : '<?php _e('خطای نامشخص', 'ganjeh'); ?>') + '</p>';
                    btn.disabled = false;
                    btn.textContent = '<?php _e('تلاش مجدد', 'ganjeh'); ?>';
                }
            })
            .catch(function(err) {
                loadingEl.style.display = 'none';
                resultsEl.style.display = 'block';
                resultsEl.innerHTML = '<p style="color: #d63638;"><?php _e('خطا', 'ganjeh'); ?>: ' + err.message + '</p>';
                btn.disabled = false;
                btn.textContent = '<?php _e('تلاش مجدد', 'ganjeh'); ?>';
            });
        });
        </script>
    </div>
    <?php
}
