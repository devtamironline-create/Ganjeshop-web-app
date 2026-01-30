<?php
/**
 * Orders List Template
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

$customer_orders = wc_get_orders([
    'customer' => get_current_user_id(),
    'limit' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
]);
?>

<div class="orders-page">
    <!-- Header -->
    <header class="page-header">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>" class="back-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <h1><?php _e('سفارشات من', 'ganjeh'); ?></h1>
        <div class="spacer"></div>
    </header>

    <?php if (!empty($customer_orders)) : ?>
    <div class="orders-container">
        <?php foreach ($customer_orders as $order) :
            $status = $order->get_status();
            $status_name = wc_get_order_status_name($status);
            $items_count = $order->get_item_count();
        ?>
        <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="order-card">
            <div class="order-header">
                <div class="order-number-wrap">
                    <span class="order-label"><?php _e('شماره سفارش', 'ganjeh'); ?></span>
                    <span class="order-number">#<?php echo $order->get_order_number(); ?></span>
                </div>
                <span class="order-status status-<?php echo esc_attr($status); ?>"><?php echo esc_html($status_name); ?></span>
            </div>

            <div class="order-body">
                <div class="order-products">
                    <?php
                    $items = $order->get_items();
                    $shown = 0;
                    foreach ($items as $item) :
                        if ($shown >= 3) break;
                        $product = $item->get_product();
                        $thumbnail = $product ? wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') : wc_placeholder_img_src();
                    ?>
                    <div class="product-thumb">
                        <img src="<?php echo esc_url($thumbnail); ?>" alt="">
                        <?php if ($item->get_quantity() > 1) : ?>
                            <span class="qty-badge"><?php echo $item->get_quantity(); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php
                        $shown++;
                    endforeach;
                    if (count($items) > 3) :
                    ?>
                    <div class="more-items">+<?php echo count($items) - 3; ?></div>
                    <?php endif; ?>
                </div>

                <div class="order-info">
                    <div class="info-row">
                        <span class="info-label"><?php _e('تاریخ', 'ganjeh'); ?></span>
                        <span class="info-value"><?php echo $order->get_date_created()->date_i18n('j F Y'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><?php _e('تعداد', 'ganjeh'); ?></span>
                        <span class="info-value"><?php printf(__('%s کالا', 'ganjeh'), $items_count); ?></span>
                    </div>
                </div>
            </div>

            <div class="order-footer">
                <div class="order-total">
                    <span class="total-label"><?php _e('مبلغ کل', 'ganjeh'); ?></span>
                    <span class="total-value"><?php echo $order->get_formatted_order_total(); ?></span>
                </div>
                <span class="view-details">
                    <?php _e('جزئیات', 'ganjeh'); ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?php else : ?>
    <!-- No Orders -->
    <div class="empty-state">
        <div class="empty-icon">
            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <h2><?php _e('سفارشی ثبت نشده', 'ganjeh'); ?></h2>
        <p><?php _e('هنوز هیچ سفارشی ثبت نکردید.', 'ganjeh'); ?></p>
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn-shop">
            <?php _e('شروع خرید', 'ganjeh'); ?>
        </a>
    </div>
    <?php endif; ?>
</div>

<style>
.orders-page {
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

/* Orders Container */
.orders-container {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Order Card */
.order-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    text-decoration: none;
    display: block;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    border-bottom: 1px solid #f3f4f6;
}

.order-number-wrap {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.order-label {
    font-size: 11px;
    color: #6b7280;
}

.order-number {
    font-size: 15px;
    font-weight: 700;
    color: #1f2937;
}

.order-status {
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 8px;
    font-weight: 500;
}

.order-status.status-completed { background: #f0fdf4; color: #166534; }
.order-status.status-processing { background: #dbeafe; color: #1e40af; }
.order-status.status-on-hold { background: #fef3c7; color: #92400e; }
.order-status.status-pending { background: #fef3c7; color: #92400e; }
.order-status.status-cancelled { background: #fef2f2; color: #991b1b; }
.order-status.status-failed { background: #fef2f2; color: #991b1b; }

.order-body {
    padding: 14px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-products {
    display: flex;
    gap: 8px;
}

.product-thumb {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    overflow: hidden;
    background: #f3f4f6;
    position: relative;
}

.product-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.qty-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    width: 18px;
    height: 18px;
    background: #4CB050;
    color: white;
    font-size: 10px;
    font-weight: 700;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.more-items {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
    text-align: left;
}

.info-row {
    display: flex;
    gap: 8px;
    align-items: center;
}

.info-label {
    font-size: 12px;
    color: #9ca3af;
}

.info-value {
    font-size: 12px;
    color: #374151;
    font-weight: 500;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    background: #f9fafb;
}

.order-total {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.total-label {
    font-size: 11px;
    color: #6b7280;
}

.total-value {
    font-size: 15px;
    font-weight: 700;
    color: #4CB050;
}

.view-details {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    color: #4CB050;
    font-weight: 500;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    color: #d1d5db;
    margin-bottom: 16px;
}

.empty-state h2 {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 8px;
}

.empty-state p {
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
