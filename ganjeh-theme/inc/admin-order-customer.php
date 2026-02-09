<?php
/**
 * Admin Order - Create New Customer
 *
 * Adds functionality to create new customers directly from order edit page
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add "Create New Customer" button and modal to order edit page
 */
function ganjeh_add_create_customer_to_order_page() {
    global $pagenow, $post_type;

    // Check if we're on order edit page
    $is_order_page = false;

    // Legacy order storage
    if ($pagenow === 'post.php' && isset($_GET['post']) && get_post_type($_GET['post']) === 'shop_order') {
        $is_order_page = true;
    }

    // New order
    if ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'shop_order') {
        $is_order_page = true;
    }

    // HPOS order storage
    if (isset($_GET['page']) && $_GET['page'] === 'wc-orders') {
        $is_order_page = true;
    }

    if (!$is_order_page) {
        return;
    }

    $nonce = wp_create_nonce('ganjeh_create_customer_nonce');
    ?>

    <!-- Create Customer Modal -->
    <div id="ganjeh-create-customer-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 100000; justify-content: center; align-items: center;">
        <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
            <div style="padding: 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin: 0; font-size: 18px;"><?php _e('ایجاد مشتری جدید', 'ganjeh'); ?></h2>
                <button type="button" onclick="ganjehCloseCustomerModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
            </div>

            <form id="ganjeh-create-customer-form" style="padding: 20px;">
                <input type="hidden" name="nonce" value="<?php echo $nonce; ?>">

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php _e('نام', 'ganjeh'); ?> <span style="color: red;">*</span></label>
                    <input type="text" name="first_name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php _e('نام خانوادگی', 'ganjeh'); ?> <span style="color: red;">*</span></label>
                    <input type="text" name="last_name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php _e('شماره موبایل', 'ganjeh'); ?> <span style="color: red;">*</span></label>
                    <input type="text" name="phone" required placeholder="09123456789" dir="ltr" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php _e('ایمیل', 'ganjeh'); ?></label>
                    <input type="email" name="email" dir="ltr" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <p style="margin: 5px 0 0; font-size: 12px; color: #666;"><?php _e('اگر خالی باشد، از شماره موبایل به عنوان یوزرنیم استفاده می‌شود', 'ganjeh'); ?></p>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php _e('استان', 'ganjeh'); ?></label>
                    <input type="text" name="state" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php _e('شهر', 'ganjeh'); ?></label>
                    <input type="text" name="city" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php _e('آدرس', 'ganjeh'); ?></label>
                    <textarea name="address" rows="2" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php _e('کد پستی', 'ganjeh'); ?></label>
                    <input type="text" name="postcode" dir="ltr" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div id="ganjeh-create-customer-message" style="display: none; padding: 10px; border-radius: 4px; margin-bottom: 15px;"></div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" id="ganjeh-create-customer-btn" class="button button-primary" style="flex: 1; padding: 10px;">
                        <?php _e('ایجاد مشتری', 'ganjeh'); ?>
                    </button>
                    <button type="button" onclick="ganjehCloseCustomerModal()" class="button" style="padding: 10px;">
                        <?php _e('انصراف', 'ganjeh'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Add "Create New Customer" button next to customer dropdown
        var $customerField = $('#customer_user, select[name="customer_user"], .wc-customer-search');

        if ($customerField.length) {
            var $createBtn = $('<button type="button" class="button" id="ganjeh-open-create-customer" style="margin-right: 10px; vertical-align: middle;"><?php _e("+ مشتری جدید", "ganjeh"); ?></button>');

            // Try different positions based on WooCommerce version
            var $parent = $customerField.closest('.form-field, .wc-order-data-row, p');
            if ($parent.length) {
                $parent.append($createBtn);
            } else {
                $customerField.after($createBtn);
            }

            $createBtn.on('click', function(e) {
                e.preventDefault();
                ganjehOpenCustomerModal();
            });
        }

        // Also add to HPOS order page
        setTimeout(function() {
            if ($('#ganjeh-open-create-customer').length === 0) {
                var $customerSection = $('.woocommerce_order_items_wrapper, .order_data_column, #order_data');
                var $customerSelect = $customerSection.find('select[name="customer_user"], .wc-customer-search');

                if ($customerSelect.length && !$customerSelect.next('#ganjeh-open-create-customer').length) {
                    var $createBtn2 = $('<button type="button" class="button" id="ganjeh-open-create-customer" style="margin-right: 10px; margin-top: 5px;"><?php _e("+ مشتری جدید", "ganjeh"); ?></button>');
                    $customerSelect.closest('p, .form-field').append($createBtn2);

                    $createBtn2.on('click', function(e) {
                        e.preventDefault();
                        ganjehOpenCustomerModal();
                    });
                }
            }
        }, 500);

        // Form submission
        $('#ganjeh-create-customer-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $btn = $('#ganjeh-create-customer-btn');
            var $message = $('#ganjeh-create-customer-message');

            $btn.prop('disabled', true).text('<?php _e("در حال ایجاد...", "ganjeh"); ?>');
            $message.hide();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ganjeh_create_customer',
                    nonce: $form.find('input[name="nonce"]').val(),
                    first_name: $form.find('input[name="first_name"]').val(),
                    last_name: $form.find('input[name="last_name"]').val(),
                    phone: $form.find('input[name="phone"]').val(),
                    email: $form.find('input[name="email"]').val(),
                    state: $form.find('input[name="state"]').val(),
                    city: $form.find('input[name="city"]').val(),
                    address: $form.find('textarea[name="address"]').val(),
                    postcode: $form.find('input[name="postcode"]').val()
                },
                success: function(response) {
                    $btn.prop('disabled', false).text('<?php _e("ایجاد مشتری", "ganjeh"); ?>');

                    if (response.success) {
                        $message.css('background', '#d4edda').css('color', '#155724').html('✓ ' + response.data.message).show();

                        // Select the newly created customer
                        var userId = response.data.user_id;
                        var userName = response.data.user_name;

                        // For Select2/WooCommerce customer search
                        var $customerSelect = $('select[name="customer_user"], .wc-customer-search');
                        if ($customerSelect.length) {
                            // Add new option and select it
                            var newOption = new Option(userName + ' (#' + userId + ')', userId, true, true);
                            $customerSelect.append(newOption).trigger('change');
                        }

                        // Close modal after short delay
                        setTimeout(function() {
                            ganjehCloseCustomerModal();
                            // Reset form
                            $form[0].reset();
                            $message.hide();
                        }, 1500);

                    } else {
                        $message.css('background', '#f8d7da').css('color', '#721c24').html('✗ ' + response.data.message).show();
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text('<?php _e("ایجاد مشتری", "ganjeh"); ?>');
                    $message.css('background', '#f8d7da').css('color', '#721c24').html('<?php _e("خطا در ارتباط با سرور", "ganjeh"); ?>').show();
                }
            });
        });
    });

    function ganjehOpenCustomerModal() {
        document.getElementById('ganjeh-create-customer-modal').style.display = 'flex';
    }

    function ganjehCloseCustomerModal() {
        document.getElementById('ganjeh-create-customer-modal').style.display = 'none';
    }

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            ganjehCloseCustomerModal();
        }
    });

    // Close modal on background click
    document.getElementById('ganjeh-create-customer-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            ganjehCloseCustomerModal();
        }
    });
    </script>
    <?php
}
add_action('admin_footer', 'ganjeh_add_create_customer_to_order_page');

/**
 * AJAX Handler - Create new customer
 */
function ganjeh_create_customer_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'ganjeh_create_customer_nonce')) {
        wp_send_json_error(['message' => __('خطای امنیتی', 'ganjeh')]);
    }

    // Check permissions
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => __('دسترسی غیرمجاز', 'ganjeh')]);
    }

    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name = sanitize_text_field($_POST['last_name'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $state = sanitize_text_field($_POST['state'] ?? '');
    $city = sanitize_text_field($_POST['city'] ?? '');
    $address = sanitize_textarea_field($_POST['address'] ?? '');
    $postcode = sanitize_text_field($_POST['postcode'] ?? '');

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($phone)) {
        wp_send_json_error(['message' => __('لطفاً نام، نام خانوادگی و شماره موبایل را وارد کنید', 'ganjeh')]);
    }

    // Clean phone number
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) < 10) {
        wp_send_json_error(['message' => __('شماره موبایل نامعتبر است', 'ganjeh')]);
    }

    // Use phone as username if no email provided
    $username = $phone;
    if (empty($email)) {
        $email = $phone . '@customer.local';
    }

    // Check if user already exists
    if (username_exists($username)) {
        // Try to find existing user and return their ID
        $existing_user = get_user_by('login', $username);
        if ($existing_user) {
            wp_send_json_success([
                'message' => __('این کاربر قبلاً وجود دارد و انتخاب شد', 'ganjeh'),
                'user_id' => $existing_user->ID,
                'user_name' => $existing_user->display_name,
            ]);
        }
    }

    if (email_exists($email) && strpos($email, '@customer.local') === false) {
        wp_send_json_error(['message' => __('این ایمیل قبلاً ثبت شده است', 'ganjeh')]);
    }

    // Generate random password
    $password = wp_generate_password(12, false);

    // Create user
    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => $user_id->get_error_message()]);
    }

    // Set user role to customer
    $user = new WP_User($user_id);
    $user->set_role('customer');

    // Update user meta
    $display_name = $first_name . ' ' . $last_name;

    wp_update_user([
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'display_name' => $display_name,
    ]);

    // Update billing info
    update_user_meta($user_id, 'billing_first_name', $first_name);
    update_user_meta($user_id, 'billing_last_name', $last_name);
    update_user_meta($user_id, 'billing_phone', $phone);
    update_user_meta($user_id, 'billing_email', $email);
    update_user_meta($user_id, 'billing_state', $state);
    update_user_meta($user_id, 'billing_city', $city);
    update_user_meta($user_id, 'billing_address_1', $address);
    update_user_meta($user_id, 'billing_postcode', $postcode);
    update_user_meta($user_id, 'billing_country', 'IR');

    // Also save to shipping
    update_user_meta($user_id, 'shipping_first_name', $first_name);
    update_user_meta($user_id, 'shipping_last_name', $last_name);
    update_user_meta($user_id, 'shipping_state', $state);
    update_user_meta($user_id, 'shipping_city', $city);
    update_user_meta($user_id, 'shipping_address_1', $address);
    update_user_meta($user_id, 'shipping_postcode', $postcode);
    update_user_meta($user_id, 'shipping_country', 'IR');

    wp_send_json_success([
        'message' => __('مشتری با موفقیت ایجاد شد', 'ganjeh'),
        'user_id' => $user_id,
        'user_name' => $display_name,
    ]);
}
add_action('wp_ajax_ganjeh_create_customer', 'ganjeh_create_customer_ajax');
