<?php
/**
 * View Order Details Template
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

$order = wc_get_order($order_id);

if (!$order) {
    wc_print_notice(__('سفارش یافت نشد.', 'ganjeh'), 'error');
    return;
}

$status = $order->get_status();
$status_name = wc_get_order_status_name($status);
$items = $order->get_items();
?>

<div class="view-order-page">
    <!-- Header -->
    <header class="page-header">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="back-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <h1><?php printf(__('سفارش #%s', 'ganjeh'), $order->get_order_number()); ?></h1>
        <div class="spacer"></div>
    </header>

    <!-- Order Status Banner -->
    <div class="status-banner status-<?php echo esc_attr($status); ?>">
        <div class="status-icon">
            <?php if ($status === 'completed') : ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            <?php elseif ($status === 'processing') : ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            <?php elseif ($status === 'on-hold' || $status === 'pending') : ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            <?php elseif ($status === 'cancelled' || $status === 'failed') : ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            <?php else : ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            <?php endif; ?>
        </div>
        <div class="status-info">
            <span class="status-label"><?php _e('وضعیت سفارش', 'ganjeh'); ?></span>
            <span class="status-value"><?php echo esc_html($status_name); ?></span>
        </div>
    </div>

    <div class="order-content">
        <!-- Order Info Card -->
        <div class="info-card">
            <h3 class="card-title"><?php _e('اطلاعات سفارش', 'ganjeh'); ?></h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label"><?php _e('شماره سفارش', 'ganjeh'); ?></span>
                    <span class="info-value">#<?php echo $order->get_order_number(); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><?php _e('تاریخ ثبت', 'ganjeh'); ?></span>
                    <span class="info-value"><?php echo $order->get_date_created()->date_i18n('j F Y - H:i'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label"><?php _e('روش پرداخت', 'ganjeh'); ?></span>
                    <span class="info-value"><?php echo $order->get_payment_method_title() ?: __('رایگان', 'ganjeh'); ?></span>
                </div>
                <?php if ($order->get_shipping_method()) : ?>
                <div class="info-item">
                    <span class="info-label"><?php _e('روش ارسال', 'ganjeh'); ?></span>
                    <span class="info-value"><?php echo $order->get_shipping_method(); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Products Card -->
        <div class="info-card">
            <h3 class="card-title"><?php _e('محصولات', 'ganjeh'); ?></h3>
            <div class="products-list">
                <?php foreach ($items as $item) :
                    $product = $item->get_product();
                    $thumbnail = $product ? wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') : wc_placeholder_img_src();
                    $product_link = $product ? $product->get_permalink() : '#';
                ?>
                <div class="product-item">
                    <a href="<?php echo esc_url($product_link); ?>" class="product-thumb">
                        <img src="<?php echo esc_url($thumbnail); ?>" alt="">
                    </a>
                    <div class="product-details">
                        <a href="<?php echo esc_url($product_link); ?>" class="product-name">
                            <?php echo esc_html($item->get_name()); ?>
                        </a>
                        <div class="product-meta">
                            <span class="qty"><?php printf(__('%s عدد', 'ganjeh'), $item->get_quantity()); ?></span>
                            <span class="price"><?php echo wc_price($item->get_total()); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Totals Card -->
        <div class="info-card">
            <h3 class="card-title"><?php _e('جزئیات پرداخت', 'ganjeh'); ?></h3>
            <div class="totals-list">
                <div class="total-row">
                    <span class="total-label"><?php _e('جمع کالاها', 'ganjeh'); ?></span>
                    <span class="total-value"><?php echo wc_price($order->get_subtotal()); ?></span>
                </div>

                <?php if ($order->get_total_discount() > 0) : ?>
                <div class="total-row discount">
                    <span class="total-label"><?php _e('تخفیف', 'ganjeh'); ?></span>
                    <span class="total-value">-<?php echo wc_price($order->get_total_discount()); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($order->get_shipping_total() > 0) : ?>
                <div class="total-row">
                    <span class="total-label"><?php _e('هزینه ارسال', 'ganjeh'); ?></span>
                    <span class="total-value"><?php echo wc_price($order->get_shipping_total()); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($order->get_total_tax() > 0) : ?>
                <div class="total-row">
                    <span class="total-label"><?php _e('مالیات', 'ganjeh'); ?></span>
                    <span class="total-value"><?php echo wc_price($order->get_total_tax()); ?></span>
                </div>
                <?php endif; ?>

                <div class="total-row final">
                    <span class="total-label"><?php _e('مبلغ کل', 'ganjeh'); ?></span>
                    <span class="total-value"><?php echo $order->get_formatted_order_total(); ?></span>
                </div>
            </div>
        </div>

        <!-- Shipping Address Card -->
        <?php if ($order->has_shipping_address()) : ?>
        <div class="info-card">
            <h3 class="card-title"><?php _e('آدرس ارسال', 'ganjeh'); ?></h3>
            <div class="address-content">
                <div class="address-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="address-text">
                    <?php echo wp_kses_post($order->get_formatted_shipping_address()); ?>
                    <?php if ($order->get_shipping_phone()) : ?>
                        <br><strong><?php _e('تلفن:', 'ganjeh'); ?></strong> <?php echo esc_html($order->get_shipping_phone()); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php elseif ($order->has_billing_address()) : ?>
        <div class="info-card">
            <h3 class="card-title"><?php _e('آدرس', 'ganjeh'); ?></h3>
            <div class="address-content">
                <div class="address-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="address-text">
                    <?php echo wp_kses_post($order->get_formatted_billing_address()); ?>
                    <?php if ($order->get_billing_phone()) : ?>
                        <br><strong><?php _e('تلفن:', 'ganjeh'); ?></strong> <?php echo esc_html($order->get_billing_phone()); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Order Notes -->
        <?php
        $customer_note = $order->get_customer_note();
        if ($customer_note) :
        ?>
        <div class="info-card">
            <h3 class="card-title"><?php _e('یادداشت سفارش', 'ganjeh'); ?></h3>
            <p class="note-text"><?php echo esc_html($customer_note); ?></p>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <?php if ($order->needs_payment()) : ?>
        <div class="order-actions">
            <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="btn-pay">
                <?php _e('پرداخت سفارش', 'ganjeh'); ?>
            </a>
        </div>
        <?php endif; ?>

        <?php if (in_array($status, ['pending', 'on-hold'])) : ?>
        <div class="order-actions">
            <a href="<?php echo esc_url($order->get_cancel_order_url(wc_get_account_endpoint_url('orders'))); ?>" class="btn-cancel" onclick="return confirm('<?php esc_attr_e('آیا از لغو سفارش مطمئن هستید؟', 'ganjeh'); ?>');">
                <?php _e('لغو سفارش', 'ganjeh'); ?>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.view-order-page {
    min-height: 100vh;
    background: #f9fafb;
    padding-bottom: 100px;
}

.page-header {
    position: sticky;
    top: 0;
    z-index: 40;
    background: white;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f3f4f6;
}

.page-header h1 {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.back-btn, .spacer {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #374151;
    text-decoration: none;
}

/* Status Banner */
.status-banner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    margin: 16px;
    border-radius: 16px;
}

.status-banner.status-completed { background: linear-gradient(135deg, #f0fdf4, #dcfce7); }
.status-banner.status-processing { background: linear-gradient(135deg, #eff6ff, #dbeafe); }
.status-banner.status-on-hold,
.status-banner.status-pending { background: linear-gradient(135deg, #fffbeb, #fef3c7); }
.status-banner.status-cancelled,
.status-banner.status-failed { background: linear-gradient(135deg, #fef2f2, #fee2e2); }

.status-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
}

.status-icon svg {
    width: 24px;
    height: 24px;
}

.status-completed .status-icon svg { color: #16a34a; }
.status-processing .status-icon svg { color: #2563eb; }
.status-on-hold .status-icon svg,
.status-pending .status-icon svg { color: #d97706; }
.status-cancelled .status-icon svg,
.status-failed .status-icon svg { color: #dc2626; }

.status-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.status-label {
    font-size: 12px;
    color: #6b7280;
}

.status-value {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
}

/* Content */
.order-content {
    padding: 0 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Info Card */
.info-card {
    background: white;
    border-radius: 16px;
    padding: 16px;
}

.card-title {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f3f4f6;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-item .info-label {
    font-size: 11px;
    color: #9ca3af;
}

.info-item .info-value {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

/* Products List */
.products-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.product-item {
    display: flex;
    gap: 12px;
    align-items: center;
}

.product-thumb {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    overflow: hidden;
    background: #f3f4f6;
    flex-shrink: 0;
}

.product-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-details {
    flex: 1;
    min-width: 0;
}

.product-name {
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    text-decoration: none;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4;
}

.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 6px;
}

.product-meta .qty {
    font-size: 12px;
    color: #9ca3af;
}

.product-meta .price {
    font-size: 13px;
    font-weight: 700;
    color: #4CB050;
}

/* Totals */
.totals-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.total-row .total-label {
    font-size: 13px;
    color: #6b7280;
}

.total-row .total-value {
    font-size: 13px;
    font-weight: 500;
    color: #374151;
}

.total-row.discount .total-value {
    color: #ef4444;
}

.total-row.final {
    padding-top: 10px;
    margin-top: 6px;
    border-top: 1px dashed #e5e7eb;
}

.total-row.final .total-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.total-row.final .total-value {
    font-size: 16px;
    font-weight: 700;
    color: #4CB050;
}

/* Address */
.address-content {
    display: flex;
    gap: 12px;
}

.address-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #f0fdf4;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.address-icon svg {
    width: 20px;
    height: 20px;
    color: #4CB050;
}

.address-text {
    font-size: 13px;
    color: #374151;
    line-height: 1.6;
}

/* Note */
.note-text {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.6;
    margin: 0;
    background: #f9fafb;
    padding: 12px;
    border-radius: 10px;
}

/* Actions */
.order-actions {
    margin-top: 8px;
}

.btn-pay {
    display: block;
    width: 100%;
    padding: 14px;
    background: #4CB050;
    color: white;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    border-radius: 12px;
}

.btn-cancel {
    display: block;
    width: 100%;
    padding: 14px;
    background: #fef2f2;
    color: #dc2626;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    border-radius: 12px;
}
</style>
