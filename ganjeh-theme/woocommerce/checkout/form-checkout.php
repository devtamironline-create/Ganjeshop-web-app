<?php
/**
 * Checkout Form Template - Minimal 2-Step Design
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wp_redirect(wc_get_page_permalink('myaccount'));
    exit;
}

do_action('woocommerce_before_checkout_form', $checkout);

if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('برای تکمیل خرید باید وارد حساب کاربری شوید.', 'ganjeh')));
    return;
}
?>

<div class="checkout-page" x-data="checkoutPage()">
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

        <!-- Order Summary -->
        <div class="order-summary">
            <h3><?php _e('خلاصه سفارش', 'ganjeh'); ?></h3>
            <div class="summary-items">
                <?php foreach (WC()->cart->get_cart() as $cart_item) :
                    $product = $cart_item['data'];
                    $quantity = $cart_item['quantity'];
                    $thumbnail = $product->get_image_id();
                ?>
                    <div class="summary-item">
                        <div class="item-img">
                            <?php if ($thumbnail) echo wp_get_attachment_image($thumbnail, 'thumbnail'); ?>
                            <span class="item-qty"><?php echo $quantity; ?></span>
                        </div>
                        <div class="item-info">
                            <span class="item-name"><?php echo wp_trim_words($product->get_name(), 4); ?></span>
                            <span class="item-price"><?php echo WC()->cart->get_product_subtotal($product, $quantity); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Shipping Address -->
        <div class="checkout-section">
            <h3><?php _e('آدرس تحویل', 'ganjeh'); ?></h3>

            <?php if ($checkout->get_checkout_fields('billing')) : ?>
                <div class="form-fields">
                    <?php
                    $fields = $checkout->get_checkout_fields('billing');

                    // Simplified fields
                    $show_fields = ['billing_first_name', 'billing_last_name', 'billing_phone', 'billing_state', 'billing_city', 'billing_address_1', 'billing_postcode'];

                    foreach ($show_fields as $field_key) :
                        if (isset($fields[$field_key])) :
                            $field = $fields[$field_key];
                            $value = $checkout->get_value($field_key);
                    ?>
                        <div class="form-field <?php echo isset($field['class']) ? implode(' ', $field['class']) : ''; ?>">
                            <label for="<?php echo $field_key; ?>">
                                <?php echo $field['label']; ?>
                                <?php if (!empty($field['required'])) : ?><span class="required">*</span><?php endif; ?>
                            </label>
                            <?php if ($field_key === 'billing_state') : ?>
                                <select name="<?php echo $field_key; ?>" id="<?php echo $field_key; ?>" class="form-input" <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                                    <option value=""><?php _e('انتخاب استان', 'ganjeh'); ?></option>
                                    <?php
                                    $states = WC()->countries->get_states('IR');
                                    foreach ($states as $key => $state) :
                                    ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($value, $key); ?>><?php echo esc_html($state); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif ($field['type'] === 'textarea') : ?>
                                <textarea name="<?php echo $field_key; ?>" id="<?php echo $field_key; ?>" class="form-input" placeholder="<?php echo isset($field['placeholder']) ? $field['placeholder'] : ''; ?>" <?php echo !empty($field['required']) ? 'required' : ''; ?>><?php echo esc_attr($value); ?></textarea>
                            <?php else : ?>
                                <input type="<?php echo $field['type'] ?? 'text'; ?>" name="<?php echo $field_key; ?>" id="<?php echo $field_key; ?>" class="form-input" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo isset($field['placeholder']) ? $field['placeholder'] : ''; ?>" <?php echo !empty($field['required']) ? 'required' : ''; ?>>
                            <?php endif; ?>
                        </div>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            <?php endif; ?>

            <!-- Hidden billing fields -->
            <input type="hidden" name="billing_country" value="IR">
            <input type="hidden" name="billing_email" value="<?php echo wp_get_current_user()->user_email ?: 'customer@example.com'; ?>">
        </div>

        <!-- Order Notes -->
        <div class="checkout-section">
            <h3><?php _e('توضیحات سفارش', 'ganjeh'); ?></h3>
            <div class="form-field">
                <textarea name="order_comments" id="order_comments" class="form-input" placeholder="<?php _e('توضیحات اختیاری درباره سفارش...', 'ganjeh'); ?>" rows="3"></textarea>
            </div>
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
                                    <?php if ($gateway->has_fields() || $gateway->get_description()) : ?>
                                        <span class="method-desc"><?php echo wp_kses_post($gateway->get_description()); ?></span>
                                    <?php endif; ?>
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
                <span><?php _e('جمع کل', 'ganjeh'); ?></span>
                <span><?php echo wc_price(WC()->cart->get_subtotal()); ?></span>
            </div>
            <?php if (WC()->cart->get_discount_total() > 0) : ?>
            <div class="total-row discount">
                <span><?php _e('تخفیف', 'ganjeh'); ?></span>
                <span>- <?php echo wc_price(WC()->cart->get_discount_total()); ?></span>
            </div>
            <?php endif; ?>
            <?php if (WC()->cart->get_shipping_total() > 0) : ?>
            <div class="total-row">
                <span><?php _e('هزینه ارسال', 'ganjeh'); ?></span>
                <span><?php echo wc_price(WC()->cart->get_shipping_total()); ?></span>
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
.checkout-page { min-height: 100vh; background: #f9fafb; padding-bottom: 120px; }
.checkout-header { position: sticky; top: 0; z-index: 40; background: white; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f3f4f6; }
.checkout-header h1 { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; }
.back-btn, .spacer { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; color: #374151; text-decoration: none; }

/* Steps */
.steps { display: flex; align-items: center; justify-content: center; padding: 20px 16px; background: white; border-bottom: 1px solid #f3f4f6; }
.step { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.step-num { width: 32px; height: 32px; border-radius: 50%; background: #e5e7eb; color: #9ca3af; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; }
.step.active .step-num { background: #4CB050; color: white; }
.step.done .step-num { background: #4CB050; color: white; }
.step span { font-size: 12px; color: #6b7280; font-weight: 500; }
.step.active span, .step.done span { color: #4CB050; font-weight: 600; }
.step-line { width: 60px; height: 2px; background: #e5e7eb; margin: 0 12px; margin-bottom: 22px; }
.step-line.done { background: #4CB050; }

/* Order Summary */
.order-summary { margin: 16px; padding: 16px; background: white; border-radius: 16px; }
.order-summary h3 { font-size: 14px; font-weight: 700; color: #1f2937; margin: 0 0 12px; }
.summary-items { display: flex; gap: 12px; overflow-x: auto; padding-bottom: 4px; }
.summary-item { display: flex; gap: 10px; flex-shrink: 0; }
.summary-item .item-img { position: relative; width: 60px; height: 60px; border-radius: 10px; overflow: hidden; background: #f3f4f6; }
.summary-item .item-img img { width: 100%; height: 100%; object-fit: cover; }
.summary-item .item-qty { position: absolute; top: -6px; right: -6px; width: 20px; height: 20px; background: #4CB050; color: white; border-radius: 50%; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
.summary-item .item-info { display: flex; flex-direction: column; gap: 2px; }
.summary-item .item-name { font-size: 12px; color: #374151; font-weight: 500; }
.summary-item .item-price { font-size: 12px; color: #4CB050; font-weight: 600; }

/* Sections */
.checkout-section { margin: 16px; padding: 16px; background: white; border-radius: 16px; }
.checkout-section h3 { font-size: 14px; font-weight: 700; color: #1f2937; margin: 0 0 16px; }

/* Form Fields */
.form-fields { display: flex; flex-direction: column; gap: 12px; }
.form-field { display: flex; flex-direction: column; gap: 6px; }
.form-field label { font-size: 13px; font-weight: 500; color: #374151; }
.form-field label .required { color: #ef4444; }
.form-input { width: 100%; padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; color: #1f2937; background: #f9fafb; transition: all 0.2s; }
.form-input:focus { outline: none; border-color: #4CB050; background: white; }
select.form-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: left 12px center; background-size: 16px; padding-left: 36px; }
textarea.form-input { resize: none; min-height: 80px; }

/* Payment Methods */
.payment-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
.payment-method label { display: flex; align-items: center; gap: 12px; padding: 14px; background: #f9fafb; border: 2px solid transparent; border-radius: 12px; cursor: pointer; transition: all 0.2s; }
.payment-method input { display: none; }
.payment-method input:checked + .method-title { color: #4CB050; }
.payment-method:has(input:checked) label { border-color: #4CB050; background: #f0fdf4; }
.method-title { font-size: 14px; font-weight: 600; color: #1f2937; }
.method-desc { font-size: 12px; color: #6b7280; margin-right: auto; }
.no-payment { padding: 16px; background: #fef2f2; color: #991b1b; border-radius: 10px; text-align: center; font-size: 13px; }

/* Totals */
.checkout-totals { margin: 16px; padding: 16px; background: white; border-radius: 16px; }
.total-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 14px; color: #4b5563; }
.total-row.discount { color: #4CB050; }
.total-row.final { border-top: 1px solid #e5e7eb; margin-top: 8px; padding-top: 16px; font-size: 16px; font-weight: 700; color: #1f2937; }

/* Bottom Bar */
.checkout-bar { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 515px; background: white; border-top: 1px solid #e5e7eb; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: 0 -4px 20px rgba(0,0,0,0.08); }
.bar-total { display: flex; flex-direction: column; gap: 2px; }
.bar-total .label { font-size: 12px; color: #6b7280; }
.bar-total .value { font-size: 18px; font-weight: 700; color: #1f2937; }
.pay-btn { display: flex; align-items: center; gap: 8px; padding: 14px 32px; background: linear-gradient(135deg, #4CB050, #3d9142); color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; }

/* Hide WooCommerce defaults */
.woocommerce-form-coupon-toggle, .woocommerce-form-login-toggle { display: none; }
</style>

<script>
function checkoutPage() {
    return {
        // Any checkout specific logic
    };
}
</script>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
