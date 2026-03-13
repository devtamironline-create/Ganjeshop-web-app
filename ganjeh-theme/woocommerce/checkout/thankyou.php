<?php
/**
 * Thankyou page - Order Confirmation
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

// Get order data
$order = false;
if ($order_id) {
    $order = wc_get_order($order_id);
}
?>

<div class="thankyou-page">
    <?php if ($order) :
        $order_status = $order->get_status();
        $is_paid = in_array($order_status, ['processing', 'completed', 'on-hold']);
    ?>

    <!-- Success Header -->
    <div class="thankyou-header">
        <div class="success-icon">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1><?php _e('سفارش شما با موفقیت ثبت شد', 'ganjeh'); ?></h1>
        <p class="order-number"><?php printf(__('شماره سفارش: %s', 'ganjeh'), '<strong>' . $order->get_order_number() . '</strong>'); ?></p>
        <p class="order-date"><?php echo $order->get_date_created()->date_i18n('j F Y - H:i'); ?></p>
    </div>

    <!-- Order Status -->
    <div class="order-status-card">
        <div class="status-indicator <?php echo esc_attr($order_status); ?>">
            <?php if ($order_status === 'processing' || $order_status === 'completed') : ?>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span><?php _e('پرداخت موفق', 'ganjeh'); ?></span>
            <?php elseif ($order_status === 'on-hold') : ?>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span><?php _e('در انتظار بررسی', 'ganjeh'); ?></span>
            <?php else : ?>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span><?php echo wc_get_order_status_name($order_status); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Items -->
    <div class="invoice-section">
        <h3><?php _e('اقلام سفارش', 'ganjeh'); ?></h3>
        <div class="invoice-items">
            <?php foreach ($order->get_items() as $item_id => $item) :
                $product = $item->get_product();
                $thumbnail = $product ? wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') : wc_placeholder_img_src();
            ?>
            <div class="invoice-item">
                <div class="item-image">
                    <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($item->get_name()); ?>">
                    <span class="item-qty"><?php echo $item->get_quantity(); ?></span>
                </div>
                <div class="item-details">
                    <div class="item-name"><?php echo esc_html($item->get_name()); ?></div>
                    <?php
                    // Show variation attributes
                    if ($product && $product->is_type('variation')) {
                        $attributes = $product->get_variation_attributes();
                        if (!empty($attributes)) {
                            echo '<div class="item-meta">';
                            foreach ($attributes as $attr_name => $attr_value) {
                                $taxonomy = str_replace('attribute_', '', $attr_name);
                                $term = get_term_by('slug', $attr_value, $taxonomy);
                                $label = $term ? $term->name : $attr_value;
                                echo '<span>' . esc_html($label) . '</span>';
                            }
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                <div class="item-price"><?php echo $order->get_formatted_line_subtotal($item); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Order Totals -->
    <div class="invoice-section">
        <h3><?php _e('جزئیات پرداخت', 'ganjeh'); ?></h3>
        <div class="invoice-totals">
            <div class="total-row">
                <span><?php _e('جمع کل', 'ganjeh'); ?></span>
                <span><?php echo wc_price($order->get_subtotal()); ?></span>
            </div>

            <?php if ($order->get_total_discount() > 0) : ?>
            <div class="total-row discount">
                <span><?php _e('تخفیف', 'ganjeh'); ?></span>
                <span>- <?php echo wc_price($order->get_total_discount()); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($order->get_shipping_total() > 0) : ?>
            <div class="total-row">
                <span><?php _e('هزینه ارسال', 'ganjeh'); ?></span>
                <span><?php echo wc_price($order->get_shipping_total()); ?></span>
            </div>
            <?php endif; ?>

            <?php
            // Show applied coupons
            $coupons = $order->get_coupon_codes();
            if (!empty($coupons)) : ?>
            <div class="applied-coupons-row">
                <span><?php _e('کد تخفیف:', 'ganjeh'); ?></span>
                <span class="coupon-codes"><?php echo implode(', ', $coupons); ?></span>
            </div>
            <?php endif; ?>

            <div class="total-row final">
                <span><?php _e('مبلغ پرداخت شده', 'ganjeh'); ?></span>
                <span><?php echo $order->get_formatted_order_total(); ?></span>
            </div>

            <?php if ($order->get_payment_method_title()) : ?>
            <div class="payment-method-row">
                <span><?php _e('روش پرداخت:', 'ganjeh'); ?></span>
                <span><?php echo esc_html($order->get_payment_method_title()); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Shipping Address -->
    <div class="invoice-section">
        <h3><?php _e('آدرس تحویل', 'ganjeh'); ?></h3>
        <div class="shipping-address-card">
            <div class="address-icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="address-details">
                <div class="receiver-name">
                    <?php echo esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()); ?>
                </div>
                <div class="receiver-phone"><?php echo esc_html($order->get_billing_phone()); ?></div>
                <div class="full-address">
                    <?php
                    $state = $order->get_billing_state();
                    $states = WC()->countries->get_states('IR');
                    $state_name = isset($states[$state]) ? $states[$state] : $state;
                    echo esc_html($state_name . '، ' . $order->get_billing_city() . ' - ' . $order->get_billing_address_1());
                    ?>
                </div>
                <?php if ($order->get_billing_postcode()) : ?>
                <div class="postcode"><?php printf(__('کد پستی: %s', 'ganjeh'), $order->get_billing_postcode()); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="thankyou-actions">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="btn-orders">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <?php _e('مشاهده سفارشات', 'ganjeh'); ?>
        </a>
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn-continue">
            <?php _e('ادامه خرید', 'ganjeh'); ?>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
    </div>

    <?php else : ?>

    <!-- No Order Found -->
    <div class="no-order">
        <div class="no-order-icon">
            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2><?php _e('سفارشی یافت نشد', 'ganjeh'); ?></h2>
        <p><?php _e('متأسفانه سفارش مورد نظر یافت نشد.', 'ganjeh'); ?></p>
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn-shop">
            <?php _e('بازگشت به فروشگاه', 'ganjeh'); ?>
        </a>
    </div>

    <?php endif; ?>
</div>

<style>
.thankyou-page {
    min-height: 100vh;
    background: #f9fafb;
    padding: 20px 16px 40px;
}

/* Success Header */
.thankyou-header {
    text-align: center;
    padding: 30px 20px;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-radius: 20px;
    margin-bottom: 20px;
}

.success-icon {
    width: 80px;
    height: 80px;
    background: #4CB050;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    color: white;
    animation: scaleIn 0.5s ease;
}

@keyframes scaleIn {
    from { transform: scale(0); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.success-icon svg {
    width: 40px;
    height: 40px;
}

.thankyou-header h1 {
    font-size: 18px;
    font-weight: 700;
    color: #166534;
    margin: 0 0 12px;
}

.order-number {
    font-size: 14px;
    color: #15803d;
    margin: 0 0 4px;
}

.order-number strong {
    font-weight: 700;
}

.order-date {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
}

/* Order Status Card */
.order-status-card {
    background: white;
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 16px;
}

.status-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
}

.status-indicator.processing,
.status-indicator.completed {
    background: #f0fdf4;
    color: #166534;
}

.status-indicator.on-hold {
    background: #fffbeb;
    color: #92400e;
}

.status-indicator.pending,
.status-indicator.failed {
    background: #fef2f2;
    color: #991b1b;
}

/* Invoice Sections */
.invoice-section {
    background: white;
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 16px;
}

.invoice-section h3 {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 14px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f3f4f6;
}

/* Invoice Items */
.invoice-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.invoice-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    background: #f9fafb;
    border-radius: 12px;
}

.item-image {
    position: relative;
    width: 60px;
    height: 60px;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.item-qty {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 22px;
    height: 22px;
    background: #4CB050;
    color: white;
    font-size: 11px;
    font-weight: 700;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.item-details {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.item-meta {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.item-meta span {
    font-size: 11px;
    color: #6b7280;
    background: #e5e7eb;
    padding: 2px 8px;
    border-radius: 4px;
}

.item-price {
    font-size: 13px;
    font-weight: 700;
    color: #4CB050;
    white-space: nowrap;
}

/* Invoice Totals */
.invoice-totals {
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
    padding-top: 12px;
    margin-top: 8px;
    border-top: 2px solid #f3f4f6;
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
}

.applied-coupons-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: #6b7280;
    padding: 8px 10px;
    background: #f0fdf4;
    border-radius: 8px;
}

.coupon-codes {
    font-weight: 600;
    color: #166534;
    text-transform: uppercase;
}

.payment-method-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    color: #6b7280;
    padding-top: 8px;
}

/* Shipping Address */
.shipping-address-card {
    display: flex;
    gap: 12px;
    padding: 14px;
    background: #f9fafb;
    border-radius: 12px;
}

.address-icon {
    width: 40px;
    height: 40px;
    background: #f0fdf4;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4CB050;
    flex-shrink: 0;
}

.address-details {
    flex: 1;
}

.receiver-name {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.receiver-phone {
    font-size: 13px;
    color: #4CB050;
    font-weight: 500;
    margin-bottom: 6px;
    direction: ltr;
    text-align: right;
}

.full-address {
    font-size: 13px;
    color: #4b5563;
    line-height: 1.5;
    margin-bottom: 4px;
}

.postcode {
    font-size: 12px;
    color: #6b7280;
}

/* Action Buttons */
.thankyou-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

.btn-orders, .btn-continue {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-orders {
    background: white;
    color: #4CB050;
    border: 2px solid #4CB050;
}

.btn-continue {
    background: linear-gradient(135deg, #4CB050, #3d9142);
    color: white;
    border: none;
}

/* No Order Found */
.no-order {
    text-align: center;
    padding: 60px 20px;
}

.no-order-icon {
    color: #d1d5db;
    margin-bottom: 20px;
}

.no-order h2 {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 8px;
}

.no-order p {
    font-size: 14px;
    color: #6b7280;
    margin: 0 0 24px;
}

.btn-shop {
    display: inline-flex;
    align-items: center;
    padding: 14px 32px;
    background: #4CB050;
    color: white;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
}
</style>
