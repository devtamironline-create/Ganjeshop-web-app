<?php
/**
 * My Account Page Template
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

$current_user = wp_get_current_user();
?>

<div class="ganjeh-account pb-20">

    <!-- User Header -->
    <div class="bg-white px-4 py-6 flex items-center gap-4">
        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
            <?php echo get_avatar($current_user->ID, 64, '', '', ['class' => 'w-full h-full rounded-full object-cover']); ?>
        </div>
        <div>
            <h2 class="font-bold text-secondary text-lg">
                <?php echo esc_html($current_user->display_name); ?>
            </h2>
            <p class="text-sm text-gray-500 nums-fa">
                <?php echo esc_html($current_user->user_email); ?>
            </p>
        </div>
    </div>

    <!-- Wallet (Optional) -->
    <?php if (function_exists('woo_wallet') || get_user_meta($current_user->ID, '_wallet_balance', true)) : ?>
        <div class="px-4 py-4 bg-white mt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <span class="font-medium text-secondary"><?php _e('کیف پول', 'ganjeh'); ?></span>
                </div>
                <span class="text-primary font-bold nums-fa">
                    <?php
                    $balance = get_user_meta($current_user->ID, '_wallet_balance', true) ?: 0;
                    echo wc_price($balance);
                    ?>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Menu Items -->
    <nav class="mt-2 bg-white">
        <?php
        $menu_items = [
            'orders' => [
                'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>',
                'label' => __('سفارشات من', 'ganjeh'),
            ],
            'edit-address' => [
                'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                'label' => __('آدرس‌های من', 'ganjeh'),
            ],
            'edit-account' => [
                'icon'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                'label' => __('ویرایش حساب', 'ganjeh'),
            ],
        ];

        foreach ($menu_items as $endpoint => $item) :
            $url = wc_get_account_endpoint_url($endpoint);
        ?>
            <a href="<?php echo esc_url($url); ?>" class="flex items-center gap-4 px-4 py-4 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                <span class="text-gray-500"><?php echo $item['icon']; ?></span>
                <span class="flex-1 font-medium text-gray-700"><?php echo esc_html($item['label']); ?></span>
                <svg class="w-5 h-5 text-gray-400 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Support Section -->
    <nav class="mt-2 bg-white">
        <a href="<?php echo home_url('/faq'); ?>" class="flex items-center gap-4 px-4 py-4 border-b border-gray-100 hover:bg-gray-50 transition-colors">
            <span class="text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <span class="flex-1 font-medium text-gray-700"><?php _e('سوالات متداول', 'ganjeh'); ?></span>
            <svg class="w-5 h-5 text-gray-400 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <a href="<?php echo home_url('/about'); ?>" class="flex items-center gap-4 px-4 py-4 border-b border-gray-100 hover:bg-gray-50 transition-colors">
            <span class="text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <span class="flex-1 font-medium text-gray-700"><?php _e('درباره ما', 'ganjeh'); ?></span>
            <svg class="w-5 h-5 text-gray-400 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <a href="<?php echo home_url('/contact'); ?>" class="flex items-center gap-4 px-4 py-4 border-b border-gray-100 hover:bg-gray-50 transition-colors">
            <span class="text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </span>
            <span class="flex-1 font-medium text-gray-700"><?php _e('پشتیبانی', 'ganjeh'); ?></span>
            <svg class="w-5 h-5 text-gray-400 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </nav>

    <!-- Logout -->
    <div class="mt-2 bg-white">
        <a href="<?php echo esc_url(wc_logout_url()); ?>" class="flex items-center gap-4 px-4 py-4 text-red-500 hover:bg-red-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span class="font-medium"><?php _e('خروج از حساب', 'ganjeh'); ?></span>
        </a>
    </div>

</div>
