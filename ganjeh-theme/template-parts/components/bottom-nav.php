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

<nav class="fixed bottom-0 left-0 right-0 z-50">
    <div class="max-w-md mx-auto bg-white border-t border-gray-200 shadow-lg">
        <div class="flex items-center justify-around h-16">
            <!-- Home -->
            <a href="<?php echo home_url('/'); ?>" class="flex flex-col items-center justify-center gap-1 px-6 py-2 <?php echo $current_page === 'home' ? 'text-primary' : 'text-gray-500'; ?>">
                <svg class="w-6 h-6" fill="<?php echo $current_page === 'home' ? 'currentColor' : 'none'; ?>" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-xs font-medium"><?php _e('خانه', 'ganjeh'); ?></span>
            </a>

            <!-- Orders -->
            <a href="<?php echo wc_get_account_endpoint_url('orders'); ?>" class="flex flex-col items-center justify-center gap-1 px-6 py-2 <?php echo $current_page === 'orders' ? 'text-primary' : 'text-gray-500'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span class="text-xs font-medium"><?php _e('سفارشات', 'ganjeh'); ?></span>
            </a>

            <!-- Profile -->
            <a href="<?php echo wc_get_account_endpoint_url(''); ?>" class="flex flex-col items-center justify-center gap-1 px-6 py-2 <?php echo $current_page === 'profile' ? 'text-primary' : 'text-gray-500'; ?>">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-xs font-medium"><?php _e('پروفایل', 'ganjeh'); ?></span>
            </a>
        </div>
    </div>
</nav>
