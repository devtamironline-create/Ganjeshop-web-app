<?php
/**
 * Promo Banner Component
 *
 * @package Ganjeh
 */

$promo_text = get_theme_mod('ganjeh_promo_text', '');
$promo_link = get_theme_mod('ganjeh_promo_link', '');
$promo_enabled = get_theme_mod('ganjeh_promo_enabled', false);

if (!$promo_enabled || empty($promo_text)) {
    return;
}
?>

<div class="bg-gradient-to-l from-primary to-primary-dark text-white">
    <div class="px-4 py-2.5 flex items-center justify-between">
        <?php if ($promo_link) : ?>
            <a href="<?php echo esc_url($promo_link); ?>" class="flex items-center gap-2 text-sm font-medium">
                <span><?php echo esc_html($promo_text); ?></span>
            </a>
            <a href="<?php echo esc_url($promo_link); ?>" class="bg-white/20 text-white text-xs px-3 py-1 rounded-lg hover:bg-white/30 transition-colors">
                <?php _e('دریافت', 'ganjeh'); ?>
            </a>
        <?php else : ?>
            <span class="text-sm font-medium"><?php echo esc_html($promo_text); ?></span>
        <?php endif; ?>
    </div>
</div>
