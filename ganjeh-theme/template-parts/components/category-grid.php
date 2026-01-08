<?php
/**
 * Category Grid Component
 *
 * @package Ganjeh
 */

$categories = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => false,
    'parent'     => 0,
    'orderby'    => 'menu_order',
    'order'      => 'ASC',
    'number'     => 12,
]);

if (empty($categories) || is_wp_error($categories)) {
    return;
}

// Icons mapping for categories (can be customized via admin)
$default_icons = [
    'شوینده' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>',
    'لوازم خانگی' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
    'لوازم جانبی' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
];

$default_icon = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>';
?>

<div class="grid grid-cols-3 gap-3">
    <?php foreach ($categories as $category) :
        $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
        $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'ganjeh-category-icon') : '';
        $category_link = get_term_link($category);
        $badge = get_term_meta($category->term_id, 'ganjeh_badge', true);

        // Find matching icon
        $icon = $default_icon;
        foreach ($default_icons as $key => $svg) {
            if (strpos($category->name, $key) !== false) {
                $icon = $svg;
                break;
            }
        }
    ?>
        <a href="<?php echo esc_url($category_link); ?>" class="category-card relative bg-white rounded-xl p-4 flex flex-col items-center justify-center gap-2 shadow-sm border border-gray-100 hover:shadow-md hover:border-primary/20 transition-all">
            <?php if ($badge) : ?>
                <span class="absolute -top-2 right-2 bg-primary text-white text-[10px] px-2 py-0.5 rounded-full font-bold">
                    <?php echo esc_html($badge); ?>
                </span>
            <?php endif; ?>

            <div class="w-16 h-16 flex items-center justify-center">
                <?php if ($image_url) : ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($category->name); ?>" class="w-full h-full object-contain" loading="lazy">
                <?php else : ?>
                    <span class="text-primary"><?php echo $icon; ?></span>
                <?php endif; ?>
            </div>

            <span class="text-xs font-medium text-gray-700 text-center leading-tight">
                <?php echo esc_html($category->name); ?>
            </span>
        </a>
    <?php endforeach; ?>
</div>
