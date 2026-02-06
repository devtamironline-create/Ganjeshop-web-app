<?php
/**
 * Pay for Order Form - Direct Payment Page
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

$order = isset($order) ? $order : null;
$order_id = $order ? $order->get_id() : 0;

if (!$order) {
    echo '<p class="woocommerce-error">' . __('سفارش یافت نشد', 'ganjeh') . '</p>';
    return;
}

$order_total = $order->get_total();
$order_items = $order->get_items();
?>

<div class="pay-order-page">
    <!-- Header -->
    <header class="pay-header">
        <div class="spacer"></div>
        <h1><?php _e('پرداخت سفارش', 'ganjeh'); ?></h1>
        <div class="spacer"></div>
    </header>

    <!-- Order Info -->
    <div class="pay-section order-info">
        <div class="order-number">
            <span class="label"><?php _e('شماره سفارش:', 'ganjeh'); ?></span>
            <span class="value">#<?php echo $order->get_order_number(); ?></span>
        </div>
        <div class="order-date">
            <span class="label"><?php _e('تاریخ:', 'ganjeh'); ?></span>
            <span class="value"><?php echo wc_format_datetime($order->get_date_created()); ?></span>
        </div>
    </div>

    <!-- Order Items -->
    <div class="pay-section">
        <h3><?php _e('محصولات سفارش', 'ganjeh'); ?></h3>
        <div class="order-items">
            <?php foreach ($order_items as $item_id => $item) :
                $product = $item->get_product();
                $thumbnail = $product ? wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'thumbnail') : null;
            ?>
            <div class="order-item">
                <div class="item-image">
                    <?php if ($thumbnail) : ?>
                        <img src="<?php echo esc_url($thumbnail[0]); ?>" alt="">
                    <?php else : ?>
                        <div class="no-image"></div>
                    <?php endif; ?>
                </div>
                <div class="item-details">
                    <div class="item-name"><?php echo esc_html($item->get_name()); ?></div>
                    <div class="item-meta">
                        <span class="item-qty"><?php echo $item->get_quantity(); ?> عدد</span>
                        <span class="item-price"><?php echo wc_price($item->get_total()); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Order Totals -->
    <div class="pay-section">
        <h3><?php _e('خلاصه سفارش', 'ganjeh'); ?></h3>
        <div class="order-totals">
            <div class="total-row">
                <span><?php _e('جمع محصولات', 'ganjeh'); ?></span>
                <span><?php echo wc_price($order->get_subtotal()); ?></span>
            </div>

            <?php if ($order->get_shipping_total() > 0) : ?>
            <div class="total-row">
                <span><?php _e('هزینه ارسال', 'ganjeh'); ?></span>
                <span><?php echo wc_price($order->get_shipping_total()); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($order->get_total_discount() > 0) : ?>
            <div class="total-row discount">
                <span><?php _e('تخفیف', 'ganjeh'); ?></span>
                <span>- <?php echo wc_price($order->get_total_discount()); ?></span>
            </div>
            <?php endif; ?>

            <div class="total-row final">
                <span><?php _e('مبلغ قابل پرداخت', 'ganjeh'); ?></span>
                <span><?php echo wc_price($order_total); ?></span>
            </div>
        </div>
    </div>

    <!-- Payment Form -->
    <form id="order_review" method="post">
        <!-- Payment Methods -->
        <div class="pay-section">
            <h3><?php _e('روش پرداخت', 'ganjeh'); ?></h3>
            <div class="payment-methods">
                <?php
                if (!empty($available_gateways)) :
                    $first = true;
                    foreach ($available_gateways as $gateway) :
                ?>
                    <label class="payment-method <?php echo $first ? 'selected' : ''; ?>">
                        <input type="radio"
                               name="payment_method"
                               value="<?php echo esc_attr($gateway->id); ?>"
                               <?php echo $first ? 'checked' : ''; ?>
                               class="payment-method-input">
                        <span class="method-radio"></span>
                        <span class="method-info">
                            <span class="method-label"><?php echo $gateway->get_title(); ?></span>
                            <?php if ($gateway->get_description()) : ?>
                                <span class="method-desc"><?php echo wp_kses_post($gateway->get_description()); ?></span>
                            <?php endif; ?>
                        </span>
                        <?php if ($gateway->get_icon()) : ?>
                            <span class="method-icon"><?php echo $gateway->get_icon(); ?></span>
                        <?php endif; ?>
                    </label>
                <?php
                        $first = false;
                    endforeach;
                else :
                ?>
                    <p class="no-payment"><?php _e('هیچ درگاه پرداختی فعال نیست', 'ganjeh'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="pay-bar">
            <div class="bar-total">
                <span class="label"><?php _e('مبلغ نهایی', 'ganjeh'); ?></span>
                <span class="value"><?php echo wc_price($order_total); ?></span>
            </div>

            <input type="hidden" name="woocommerce_pay" value="1" />
            <?php wp_nonce_field('woocommerce-pay', 'woocommerce-pay-nonce'); ?>

            <button type="submit" class="pay-btn" id="place_order">
                <?php _e('پرداخت', 'ganjeh'); ?>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
        </div>
    </form>
</div>

<style>
.pay-order-page {
    min-height: 100vh;
    background: #f9fafb;
    padding-bottom: 120px;
}

.pay-header {
    position: sticky;
    top: 0;
    z-index: 40;
    background: white;
    padding: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f3f4f6;
}

.pay-header h1 {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.pay-header .spacer {
    width: 40px;
}

.pay-section {
    margin: 16px;
    padding: 16px;
    background: white;
    border-radius: 16px;
}

.pay-section h3 {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 16px;
}

/* Order Info */
.order-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-number, .order-date {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.order-info .label {
    font-size: 12px;
    color: #6b7280;
}

.order-info .value {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
}

/* Order Items */
.order-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.order-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    background: #f9fafb;
    border-radius: 12px;
}

.item-image {
    width: 60px;
    height: 60px;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-image .no-image {
    width: 100%;
    height: 100%;
    background: #e5e7eb;
}

.item-details {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.item-qty {
    font-size: 13px;
    color: #6b7280;
}

.item-price {
    font-size: 14px;
    font-weight: 600;
    color: #4CB050;
}

/* Order Totals */
.order-totals {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    color: #4b5563;
}

.total-row.discount {
    color: #4CB050;
}

.total-row.final {
    border-top: 1px solid #e5e7eb;
    margin-top: 8px;
    padding-top: 12px;
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
}

/* Payment Methods */
.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.payment-method {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: #f9fafb;
    border: 2px solid transparent;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.payment-method.selected {
    border-color: #4CB050;
    background: #f0fdf4;
}

.payment-method-input {
    display: none;
}

.method-radio {
    width: 20px;
    height: 20px;
    border: 2px solid #d1d5db;
    border-radius: 50%;
    position: relative;
    flex-shrink: 0;
}

.payment-method.selected .method-radio {
    border-color: #4CB050;
}

.payment-method.selected .method-radio::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 10px;
    height: 10px;
    background: #4CB050;
    border-radius: 50%;
}

.method-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.method-label {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
}

.method-desc {
    font-size: 12px;
    color: #6b7280;
}

.method-icon {
    flex-shrink: 0;
}

.method-icon img {
    max-height: 24px;
    width: auto;
}

.no-payment {
    padding: 16px;
    background: #fef2f2;
    color: #991b1b;
    border-radius: 10px;
    text-align: center;
    font-size: 13px;
    margin: 0;
}

/* Bottom Bar */
.pay-bar {
    position: fixed;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100%;
    max-width: 515px;
    background: white;
    border-top: 1px solid #e5e7eb;
    padding: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
    z-index: 50;
}

.bar-total {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.bar-total .label {
    font-size: 12px;
    color: #6b7280;
}

.bar-total .value {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
}

.pay-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 32px;
    background: linear-gradient(135deg, #4CB050, #3d9142);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
}

.pay-btn:hover {
    opacity: 0.95;
}
</style>

<script>
// Handle payment method selection
document.querySelectorAll('.payment-method-input').forEach(input => {
    input.addEventListener('change', function() {
        document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('selected'));
        this.closest('.payment-method').classList.add('selected');
    });
});
</script>
