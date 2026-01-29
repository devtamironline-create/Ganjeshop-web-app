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

                <!-- Postal Code -->
                <div class="form-field">
                    <label for="billing_postcode"><?php _e('کد پستی', 'ganjeh'); ?> <span class="required">*</span></label>
                    <input type="text" name="billing_postcode" id="billing_postcode" class="form-input" value="<?php echo esc_attr($checkout->get_value('billing_postcode')); ?>" maxlength="10" dir="ltr" inputmode="numeric" pattern="[0-9]{10}" placeholder="<?php _e('۱۰ رقم', 'ganjeh'); ?>" required>
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

        <!-- Coupon Code -->
        <div class="checkout-section coupon-section" x-data="couponHandler()">
            <div class="coupon-toggle" @click="open = !open">
                <div class="coupon-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <span><?php _e('کد تخفیف دارید؟', 'ganjeh'); ?></span>
                <svg class="chevron w-5 h-5" :class="{ 'rotate': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div class="coupon-form" x-show="open" x-collapse>
                <div class="coupon-input-wrap">
                    <input type="text" x-model="couponCode" class="coupon-input" placeholder="<?php _e('کد تخفیف را وارد کنید', 'ganjeh'); ?>" dir="ltr">
                    <button type="button" class="apply-coupon-btn" :disabled="loading" @click="applyCoupon()">
                        <span x-show="!loading"><?php _e('اعمال', 'ganjeh'); ?></span>
                        <span x-show="loading" class="loading-spinner"></span>
                    </button>
                </div>
                <p class="coupon-message" x-show="message" :class="{ 'success': success, 'error': !success }" x-text="message"></p>
                <?php
                // Show applied coupons
                $applied_coupons = WC()->cart->get_applied_coupons();
                if (!empty($applied_coupons)) :
                ?>
                <div class="applied-coupons">
                    <?php foreach ($applied_coupons as $coupon_code) : ?>
                    <div class="applied-coupon">
                        <span class="coupon-tag">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <?php echo esc_html($coupon_code); ?>
                        </span>
                        <button type="button" class="remove-coupon" @click="removeCoupon('<?php echo esc_attr($coupon_code); ?>')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
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

/* Coupon Section */
.coupon-section { padding: 0 !important; overflow: hidden; }
.coupon-toggle { display: flex; align-items: center; gap: 10px; padding: 16px; cursor: pointer; }
.coupon-icon { width: 36px; height: 36px; background: #f0fdf4; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #4CB050; }
.coupon-toggle span { flex: 1; font-size: 14px; font-weight: 600; color: #1f2937; }
.coupon-toggle .chevron { color: #9ca3af; transition: transform 0.2s; }
.coupon-toggle .chevron.rotate { transform: rotate(180deg); }
.coupon-form { padding: 0 16px 16px; border-top: 1px solid #f3f4f6; }
.coupon-input-wrap { display: flex; gap: 10px; margin-top: 12px; }
.coupon-input { flex: 1; padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; text-transform: uppercase; }
.coupon-input:focus { outline: none; border-color: #4CB050; }
.apply-coupon-btn { padding: 12px 20px; background: #4CB050; color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; min-width: 70px; display: flex; align-items: center; justify-content: center; }
.apply-coupon-btn:disabled { opacity: 0.7; cursor: not-allowed; }
.loading-spinner { width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.coupon-message { margin: 10px 0 0; padding: 10px 12px; border-radius: 8px; font-size: 13px; }
.coupon-message.success { background: #f0fdf4; color: #166534; }
.coupon-message.error { background: #fef2f2; color: #991b1b; }
.applied-coupons { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px; }
.applied-coupon { display: flex; align-items: center; gap: 6px; padding: 6px 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; }
.coupon-tag { display: flex; align-items: center; gap: 4px; font-size: 13px; font-weight: 600; color: #166534; text-transform: uppercase; }
.coupon-tag svg { color: #4CB050; }
.remove-coupon { background: none; border: none; padding: 2px; cursor: pointer; color: #991b1b; display: flex; }

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

// Coupon handler Alpine component
function couponHandler() {
    return {
        open: false,
        loading: false,
        message: '',
        success: false,
        couponCode: '',

        applyCoupon() {
            if (!this.couponCode.trim()) {
                this.message = '<?php _e('لطفاً کد تخفیف را وارد کنید', 'ganjeh'); ?>';
                this.success = false;
                return;
            }

            this.loading = true;
            this.message = '';

            // Use WooCommerce's built-in coupon application
            const formData = new FormData();
            formData.append('coupon_code', this.couponCode.trim());

            fetch('<?php echo wc_get_cart_url(); ?>?wc-ajax=apply_coupon', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(r => r.text())
            .then(html => {
                this.loading = false;
                // Check if response contains error or success
                if (html.includes('woocommerce-error') || html.includes('کد تخفیف') && html.includes('نامعتبر')) {
                    this.message = '<?php _e('کد تخفیف نامعتبر است', 'ganjeh'); ?>';
                    this.success = false;
                } else {
                    this.message = '<?php _e('کد تخفیف اعمال شد', 'ganjeh'); ?>';
                    this.success = true;
                    setTimeout(() => location.reload(), 1000);
                }
            })
            .catch(() => {
                this.loading = false;
                this.message = '<?php _e('خطا در اعمال کد تخفیف', 'ganjeh'); ?>';
                this.success = false;
            });
        },

        removeCoupon(code) {
            const formData = new FormData();
            formData.append('coupon', code);

            fetch('<?php echo wc_get_cart_url(); ?>?wc-ajax=remove_coupon', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(() => location.reload());
        }
    }
}
</script>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
