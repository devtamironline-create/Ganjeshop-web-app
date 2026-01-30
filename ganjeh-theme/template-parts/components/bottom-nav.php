<?php
/**
 * Bottom Navigation Component
 *
 * @package Ganjeh
 */

$current_page = '';
if (is_front_page() || is_home()) {
    $current_page = 'home';
} elseif (is_account_page()) {
    $current_page = 'profile';
} elseif (is_page('orders') || (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('orders'))) {
    $current_page = 'orders';
}
?>

<nav class="bottom-nav-fixed">
    <div class="bottom-nav-container">
        <div class="bottom-nav-inner">
            <!-- Home -->
            <a href="<?php echo home_url('/'); ?>" class="bottom-nav-item <?php echo $current_page === 'home' ? 'active' : ''; ?>">
                <svg class="w-6 h-6" fill="<?php echo $current_page === 'home' ? 'currentColor' : 'none'; ?>" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span><?php _e('خانه', 'ganjeh'); ?></span>
            </a>

            <!-- Orders -->
            <a href="<?php echo wc_get_account_endpoint_url('orders'); ?>" class="bottom-nav-item <?php echo $current_page === 'orders' ? 'active' : ''; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span><?php _e('سفارشات', 'ganjeh'); ?></span>
            </a>

            <!-- Profile -->
            <a href="<?php echo wc_get_account_endpoint_url(''); ?>" class="bottom-nav-item <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span><?php _e('پروفایل', 'ganjeh'); ?></span>
            </a>
        </div>
    </div>
</nav>

<style>
.bottom-nav-fixed {
    position: fixed;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100%;
    max-width: 515px;
    z-index: 50;
}

.bottom-nav-container {
    background: white;
    border-top: 1px solid #e5e7eb;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
}

.bottom-nav-inner {
    display: flex;
    align-items: center;
    justify-content: space-around;
    height: 64px;
}

.bottom-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 8px 24px;
    color: #6b7280;
    text-decoration: none;
    transition: color 0.2s;
}

.bottom-nav-item span {
    font-size: 11px;
    font-weight: 500;
}

.bottom-nav-item.active {
    color: var(--color-primary, #4CB050);
}

.bottom-nav-item:hover {
    color: var(--color-primary, #4CB050);
}
</style>
