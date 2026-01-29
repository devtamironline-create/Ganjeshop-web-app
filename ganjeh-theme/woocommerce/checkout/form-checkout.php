<?php
/**
 * Checkout Form Template - Minimal Design
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wp_redirect(wc_get_page_permalink('myaccount'));
    exit;
}

do_action('woocommerce_before_checkout_form', $checkout);

// Get current user data
$current_user = wp_get_current_user();
$user_phone = get_user_meta($current_user->ID, 'billing_phone', true) ?: $current_user->user_login;
$user_name = trim($current_user->first_name . ' ' . $current_user->last_name) ?: $current_user->display_name;
?>

<div class="checkout-page">
    <!-- Header -->
    <header class="checkout-header">
        <a href="<?php echo wc_get_cart_url(); ?>" class="back-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <h1><?php _e('تکمیل خرید', 'ganjeh'); ?></h1>
        <div class="spacer"></div>
    </header>

    <!-- Step Indicator -->
    <div class="steps">
        <div class="step done">
            <div class="step-num">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span><?php _e('سبد خرید', 'ganjeh'); ?></span>
        </div>
        <div class="step-line done"></div>
        <div class="step active">
            <div class="step-num">۲</div>
            <span><?php _e('پرداخت', 'ganjeh'); ?></span>
        </div>
    </div>

    <form name="checkout" method="post" class="checkout-form woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

        <!-- Shipping Info -->
        <div class="checkout-section">
            <h3><?php _e('اطلاعات تحویل', 'ganjeh'); ?></h3>

            <div class="form-fields">
                <!-- Full Name -->
                <div class="form-field">
                    <label for="billing_full_name"><?php _e('نام و نام خانوادگی', 'ganjeh'); ?> <span class="required">*</span></label>
                    <input type="text" name="billing_full_name" id="billing_full_name" class="form-input" value="<?php echo esc_attr($user_name); ?>" required>
                </div>

                <!-- Phone (readonly - from registration) -->
                <div class="form-field">
                    <label for="billing_phone"><?php _e('موبایل', 'ganjeh'); ?></label>
                    <input type="tel" name="billing_phone" id="billing_phone" class="form-input" value="<?php echo esc_attr($user_phone); ?>" dir="ltr" readonly style="background:#f3f4f6;">
                </div>

                <!-- State -->
                <div class="form-field">
                    <label for="billing_state"><?php _e('استان', 'ganjeh'); ?> <span class="required">*</span></label>
                    <select name="billing_state" id="billing_state" class="form-input" required>
                        <option value=""><?php _e('انتخاب استان', 'ganjeh'); ?></option>
                        <?php
                        $states = WC()->countries->get_states('IR');
                        $saved_state = $checkout->get_value('billing_state');
                        foreach ($states as $key => $state) :
                        ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($saved_state, $key); ?>><?php echo esc_html($state); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- City -->
                <div class="form-field">
                    <label for="billing_city"><?php _e('شهر', 'ganjeh'); ?> <span class="required">*</span></label>
                    <input type="text" name="billing_city" id="billing_city" class="form-input" value="<?php echo esc_attr($checkout->get_value('billing_city')); ?>" required>
                </div>

                <!-- Full Address -->
                <div class="form-field">
                    <label for="billing_address_1"><?php _e('آدرس کامل', 'ganjeh'); ?> <span class="required">*</span></label>
                    <textarea name="billing_address_1" id="billing_address_1" class="form-input" rows="2" placeholder="<?php _e('خیابان، کوچه، پلاک، واحد', 'ganjeh'); ?>" required><?php echo esc_attr($checkout->get_value('billing_address_1')); ?></textarea>
                </div>

                <!-- Order Notes (optional) -->
                <div class="form-field">
                    <label for="order_comments"><?php _e('توضیحات (اختیاری)', 'ganjeh'); ?></label>
                    <input type="text" name="order_comments" id="order_comments" class="form-input" placeholder="<?php _e('مثلاً: زنگ طبقه سوم', 'ganjeh'); ?>">
                </div>
            </div>

            <!-- Hidden fields -->
            <input type="hidden" name="billing_country" value="IR">
            <input type="hidden" name="billing_email" value="<?php echo esc_attr($current_user->user_email ?: $user_phone . '@ganjeh.local'); ?>">
            <input type="hidden" name="billing_first_name" value="">
            <input type="hidden" name="billing_last_name" value="">
            <input type="hidden" name="billing_postcode" value="0000000000">
        </div>

        <!-- Payment Methods -->
        <div class="checkout-section">
            <h3><?php _e('روش پرداخت', 'ganjeh'); ?></h3>
            <div id="payment" class="payment-methods">
                <?php if (WC()->cart->needs_payment()) : ?>
                    <ul class="payment-list">
                        <?php
                        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                        if (!empty($available_gateways)) :
                            foreach ($available_gateways as $gateway) :
                        ?>
                            <li class="payment-method">
                                <label>
                                    <input type="radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" <?php checked($gateway->chosen, true); ?>>
                                    <span class="method-title"><?php echo $gateway->get_title(); ?></span>
                                </label>
                            </li>
                        <?php
                            endforeach;
                        else :
                        ?>
                            <li class="no-payment"><?php _e('هیچ درگاه پرداختی فعال نیست', 'ganjeh'); ?></li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Totals -->
        <div class="checkout-totals">
            <div class="total-row">
                <span><?php _e('جمع سفارش', 'ganjeh'); ?></span>
                <span><?php echo wc_price(WC()->cart->get_subtotal()); ?></span>
            </div>
            <?php if (WC()->cart->get_discount_total() > 0) : ?>
            <div class="total-row discount">
                <span><?php _e('تخفیف', 'ganjeh'); ?></span>
                <span>- <?php echo wc_price(WC()->cart->get_discount_total()); ?></span>
            </div>
            <?php endif; ?>
            <div class="total-row final">
                <span><?php _e('قابل پرداخت', 'ganjeh'); ?></span>
                <span><?php echo WC()->cart->get_total(); ?></span>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="checkout-bar">
            <div class="bar-total">
                <span class="label"><?php _e('مبلغ نهایی', 'ganjeh'); ?></span>
                <span class="value"><?php echo WC()->cart->get_total(); ?></span>
            </div>
            <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
            <button type="submit" class="pay-btn" name="woocommerce_checkout_place_order" id="place_order">
                <?php _e('پرداخت', 'ganjeh'); ?>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
        </div>

    </form>
</div>

<style>
.checkout-page { min-height: 100vh; background: #f9fafb; padding-bottom: 100px; }
.checkout-header { position: sticky; top: 0; z-index: 40; background: white; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f3f4f6; }
.checkout-header h1 { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; }
.back-btn, .spacer { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; color: #374151; text-decoration: none; }

/* Steps */
.steps { display: flex; align-items: center; justify-content: center; padding: 20px 16px; background: white; border-bottom: 1px solid #f3f4f6; }
.step { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.step-num { width: 32px; height: 32px; border-radius: 50%; background: #e5e7eb; color: #9ca3af; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; }
.step.active .step-num, .step.done .step-num { background: #4CB050; color: white; }
.step span { font-size: 12px; color: #6b7280; font-weight: 500; }
.step.active span, .step.done span { color: #4CB050; font-weight: 600; }
.step-line { width: 60px; height: 2px; background: #e5e7eb; margin: 0 12px; margin-bottom: 22px; }
.step-line.done { background: #4CB050; }

/* Sections */
.checkout-section { margin: 16px; padding: 16px; background: white; border-radius: 16px; }
.checkout-section h3 { font-size: 14px; font-weight: 700; color: #1f2937; margin: 0 0 16px; }

/* Form Fields */
.form-fields { display: flex; flex-direction: column; gap: 14px; }
.form-field { display: flex; flex-direction: column; gap: 6px; }
.form-field label { font-size: 13px; font-weight: 500; color: #374151; }
.form-field label .required { color: #ef4444; }
.form-input { width: 100%; padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; color: #1f2937; background: white; transition: all 0.2s; }
.form-input:focus { outline: none; border-color: #4CB050; }
select.form-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: left 12px center; background-size: 16px; padding-left: 36px; }
textarea.form-input { resize: none; }

/* Payment Methods */
.payment-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
.payment-method label { display: flex; align-items: center; gap: 12px; padding: 14px; background: #f9fafb; border: 2px solid transparent; border-radius: 12px; cursor: pointer; transition: all 0.2s; }
.payment-method input { display: none; }
.payment-method:has(input:checked) label { border-color: #4CB050; background: #f0fdf4; }
.method-title { font-size: 14px; font-weight: 600; color: #1f2937; }
.no-payment { padding: 16px; background: #fef2f2; color: #991b1b; border-radius: 10px; text-align: center; font-size: 13px; list-style: none; }

/* Totals */
.checkout-totals { margin: 16px; padding: 16px; background: white; border-radius: 16px; }
.total-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 14px; color: #4b5563; }
.total-row.discount { color: #4CB050; }
.total-row.final { border-top: 1px solid #e5e7eb; margin-top: 8px; padding-top: 12px; font-size: 16px; font-weight: 700; color: #1f2937; }

/* Bottom Bar */
.checkout-bar { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 515px; background: white; border-top: 1px solid #e5e7eb; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: 0 -4px 20px rgba(0,0,0,0.08); z-index: 50; }
.bar-total { display: flex; flex-direction: column; gap: 2px; }
.bar-total .label { font-size: 12px; color: #6b7280; }
.bar-total .value { font-size: 18px; font-weight: 700; color: #1f2937; }
.pay-btn { display: flex; align-items: center; gap: 8px; padding: 14px 32px; background: linear-gradient(135deg, #4CB050, #3d9142); color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; }

/* Hide WooCommerce defaults */
.woocommerce-form-coupon-toggle, .woocommerce-form-login-toggle { display: none; }
</style>

<script>
// Split full name to first/last name before submit
document.querySelector('.checkout-form').addEventListener('submit', function(e) {
    const fullName = document.getElementById('billing_full_name').value.trim();
    const nameParts = fullName.split(' ');
    document.querySelector('input[name="billing_first_name"]').value = nameParts[0] || '';
    document.querySelector('input[name="billing_last_name"]').value = nameParts.slice(1).join(' ') || '';
});
</script>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
