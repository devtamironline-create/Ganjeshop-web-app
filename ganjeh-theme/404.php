<?php
/**
 * 404 Page Template
 *
 * @package Ganjeh
 */

get_header();
?>

<main id="main-content" class="pb-20">
    <div class="empty-state px-4 py-16">
        <div class="empty-state-icon">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-6xl font-bold text-primary mb-4">۴۰۴</h1>
        <h2 class="text-lg font-bold text-gray-700 mb-2"><?php _e('صفحه مورد نظر یافت نشد', 'ganjeh'); ?></h2>
        <p class="text-gray-500 text-sm mb-6"><?php _e('متأسفانه صفحه‌ای که به دنبال آن هستید وجود ندارد.', 'ganjeh'); ?></p>
        <a href="<?php echo home_url('/'); ?>" class="btn-primary inline-flex">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <?php _e('بازگشت به صفحه اصلی', 'ganjeh'); ?>
        </a>
    </div>
</main>

<?php
get_footer();
