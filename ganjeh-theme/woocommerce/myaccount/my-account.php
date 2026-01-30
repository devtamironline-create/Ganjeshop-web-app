<?php
/**
 * My Account Page Template - Complete Profile
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$user_email = $current_user->user_email;
$user_phone = get_user_meta($user_id, 'billing_phone', true) ?: $current_user->user_login;
$user_name = trim($current_user->first_name . ' ' . $current_user->last_name) ?: $current_user->display_name;

// Get current endpoint
$current_endpoint = WC()->query->get_current_endpoint();

// Get orders using WooCommerce built-in function
$orders_count = wc_get_customer_order_count($user_id);

// Get recent orders
$recent_orders = wc_get_orders([
    'customer' => $user_id,
    'limit' => 3,
    'orderby' => 'date',
    'order' => 'DESC',
    'status' => ['completed', 'processing', 'on-hold', 'pending', 'cancelled', 'refunded', 'failed'],
]);

// If no orders by user ID, try by email
if ($orders_count == 0 && !empty($user_email)) {
    $email_orders = wc_get_orders([
        'billing_email' => $user_email,
        'limit' => -1,
        'return' => 'ids',
    ]);
    $orders_count = count($email_orders);

    if ($orders_count > 0) {
        $recent_orders = wc_get_orders([
            'billing_email' => $user_email,
            'limit' => 3,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
    }
}

// Get addresses from our custom storage
$saved_addresses = ganjeh_get_user_addresses($user_id);
$addresses_count = count($saved_addresses);

// Also check WooCommerce default billing address if no custom addresses
if ($addresses_count == 0) {
    $billing_address = get_user_meta($user_id, 'billing_address_1', true);
    if (!empty($billing_address)) {
        $addresses_count = 1;
    }
}
?>

<div class="profile-page">
    <!-- Header -->
    <header class="profile-header">
        <h1><?php _e('پروفایل', 'ganjeh'); ?></h1>
    </header>

    <!-- User Info Card -->
    <div class="user-card">
        <div class="user-avatar">
            <?php echo get_avatar($current_user->ID, 80, '', '', ['class' => 'avatar-img']); ?>
            <div class="avatar-badge">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>
        <div class="user-info">
            <h2 class="user-name"><?php echo esc_html($user_name); ?></h2>
            <p class="user-phone" dir="ltr"><?php echo esc_html($user_phone); ?></p>
        </div>
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>" class="edit-profile-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats">
        <div class="stat-item">
            <div class="stat-icon orders">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $orders_count; ?></span>
                <span class="stat-label"><?php _e('سفارش', 'ganjeh'); ?></span>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon addresses">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $addresses_count; ?></span>
                <span class="stat-label"><?php _e('آدرس', 'ganjeh'); ?></span>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon favorites">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <div class="stat-info">
                <span class="stat-value">۰</span>
                <span class="stat-label"><?php _e('علاقه‌مندی', 'ganjeh'); ?></span>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <?php if (!empty($recent_orders)) : ?>
    <div class="profile-section">
        <div class="section-header">
            <h3><?php _e('سفارشات اخیر', 'ganjeh'); ?></h3>
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="view-all">
                <?php _e('مشاهده همه', 'ganjeh'); ?>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
        <div class="orders-list">
            <?php foreach ($recent_orders as $order) :
                $status = $order->get_status();
                $status_name = wc_get_order_status_name($status);
            ?>
            <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="order-item">
                <div class="order-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="order-info">
                    <span class="order-number"><?php printf(__('سفارش #%s', 'ganjeh'), $order->get_order_number()); ?></span>
                    <span class="order-date"><?php echo $order->get_date_created()->date_i18n('j F Y'); ?></span>
                </div>
                <div class="order-meta">
                    <span class="order-status status-<?php echo esc_attr($status); ?>"><?php echo esc_html($status_name); ?></span>
                    <span class="order-total"><?php echo $order->get_formatted_order_total(); ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Menu Items -->
    <div class="profile-section">
        <h3 class="section-title"><?php _e('حساب کاربری', 'ganjeh'); ?></h3>
        <nav class="menu-list">
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="menu-item">
                <div class="menu-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span class="menu-label"><?php _e('سفارشات من', 'ganjeh'); ?></span>
                <svg class="menu-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>

            <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>" class="menu-item">
                <div class="menu-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="menu-label"><?php _e('آدرس‌های من', 'ganjeh'); ?></span>
                <svg class="menu-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>

            <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>" class="menu-item">
                <div class="menu-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <span class="menu-label"><?php _e('ویرایش اطلاعات', 'ganjeh'); ?></span>
                <svg class="menu-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </nav>
    </div>

    <!-- Support Section -->
    <div class="profile-section">
        <h3 class="section-title"><?php _e('پشتیبانی', 'ganjeh'); ?></h3>
        <nav class="menu-list">
            <a href="<?php echo home_url('/faq'); ?>" class="menu-item">
                <div class="menu-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="menu-label"><?php _e('سوالات متداول', 'ganjeh'); ?></span>
                <svg class="menu-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>

            <a href="<?php echo home_url('/contact'); ?>" class="menu-item">
                <div class="menu-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="menu-label"><?php _e('تماس با ما', 'ganjeh'); ?></span>
                <svg class="menu-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>

            <a href="<?php echo home_url('/about'); ?>" class="menu-item">
                <div class="menu-icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="menu-label"><?php _e('درباره ما', 'ganjeh'); ?></span>
                <svg class="menu-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </nav>
    </div>

    <!-- Logout -->
    <div class="profile-section">
        <a href="<?php echo esc_url(wc_logout_url()); ?>" class="logout-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <?php _e('خروج از حساب', 'ganjeh'); ?>
        </a>
    </div>

    <!-- App Version -->
    <p class="app-version"><?php printf(__('نسخه %s', 'ganjeh'), '1.0.0'); ?></p>
</div>

<style>
.profile-page {
    min-height: 100vh;
    background: #f9fafb;
    padding-bottom: 100px;
}

/* Header */
.profile-header {
    background: white;
    padding: 16px;
    text-align: center;
    border-bottom: 1px solid #f3f4f6;
}

.profile-header h1 {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

/* User Card */
.user-card {
    background: linear-gradient(135deg, #4CB050, #3d9142);
    margin: 16px;
    padding: 20px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    position: relative;
}

.user-avatar {
    position: relative;
}

.user-avatar .avatar-img,
.user-avatar img {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,0.3);
    object-fit: cover;
}

.avatar-badge {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 22px;
    height: 22px;
    background: #fbbf24;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    border: 2px solid white;
}

.user-info {
    flex: 1;
}

.user-name {
    font-size: 18px;
    font-weight: 700;
    color: white;
    margin: 0 0 4px;
}

.user-phone {
    font-size: 14px;
    color: rgba(255,255,255,0.8);
    margin: 0;
}

.edit-profile-btn {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

/* Quick Stats */
.quick-stats {
    display: flex;
    gap: 12px;
    margin: 0 16px 16px;
}

.stat-item {
    flex: 1;
    background: white;
    padding: 16px 12px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon.orders {
    background: #dbeafe;
    color: #2563eb;
}

.stat-icon.addresses {
    background: #fef3c7;
    color: #d97706;
}

.stat-icon.favorites {
    background: #fce7f3;
    color: #db2777;
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
}

.stat-label {
    font-size: 11px;
    color: #6b7280;
}

/* Profile Sections */
.profile-section {
    background: white;
    margin: 0 16px 16px;
    border-radius: 16px;
    overflow: hidden;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid #f3f4f6;
}

.section-header h3 {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.view-all {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    color: #4CB050;
    text-decoration: none;
    font-weight: 500;
}

.section-title {
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    padding: 12px 16px 8px;
    margin: 0;
}

/* Orders List */
.orders-list {
    padding: 8px;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f9fafb;
    border-radius: 12px;
    margin-bottom: 8px;
    text-decoration: none;
}

.order-item:last-child {
    margin-bottom: 0;
}

.order-icon {
    width: 40px;
    height: 40px;
    background: #f0fdf4;
    color: #4CB050;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.order-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.order-number {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
}

.order-date {
    font-size: 12px;
    color: #6b7280;
}

.order-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}

.order-status {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 6px;
    font-weight: 500;
}

.order-status.status-completed { background: #f0fdf4; color: #166534; }
.order-status.status-processing { background: #dbeafe; color: #1e40af; }
.order-status.status-on-hold { background: #fef3c7; color: #92400e; }
.order-status.status-pending { background: #fef3c7; color: #92400e; }
.order-status.status-cancelled { background: #fef2f2; color: #991b1b; }
.order-status.status-failed { background: #fef2f2; color: #991b1b; }

.order-total {
    font-size: 13px;
    font-weight: 700;
    color: #4CB050;
}

/* Menu List */
.menu-list {
    padding: 0;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    text-decoration: none;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.2s;
}

.menu-item:last-child {
    border-bottom: none;
}

.menu-item:hover {
    background: #f9fafb;
}

.menu-icon {
    width: 36px;
    height: 36px;
    background: #f3f4f6;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
}

.menu-label {
    flex: 1;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

.menu-arrow {
    width: 20px;
    height: 20px;
    color: #9ca3af;
}

/* Logout Button */
.logout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 16px;
    color: #dc2626;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s;
}

.logout-btn:hover {
    background: #fef2f2;
}

/* App Version */
.app-version {
    text-align: center;
    font-size: 12px;
    color: #9ca3af;
    margin: 20px 0;
}
</style>
