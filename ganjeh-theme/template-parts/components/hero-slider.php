<?php
/**
 * Hero Slider Component
 *
 * @package Ganjeh
 */

// Get slides from customizer or ACF
$slides = [];

// Try to get from customizer
for ($i = 1; $i <= 5; $i++) {
    $slide_image = get_theme_mod("ganjeh_slider_image_$i");
    $slide_link = get_theme_mod("ganjeh_slider_link_$i");
    $slide_title = get_theme_mod("ganjeh_slider_title_$i");

    if ($slide_image) {
        $slides[] = [
            'image' => $slide_image,
            'link'  => $slide_link,
            'title' => $slide_title,
        ];
    }
}

// Fallback: Use featured products images if no slides configured
if (empty($slides)) {
    $featured_products = wc_get_products([
        'limit'    => 3,
        'featured' => true,
        'status'   => 'publish',
    ]);

    if ($featured_products) {
        foreach ($featured_products as $product) {
            $image_id = $product->get_image_id();
            if ($image_id) {
                $slides[] = [
                    'image' => wp_get_attachment_image_url($image_id, 'ganjeh-slider'),
                    'link'  => $product->get_permalink(),
                    'title' => $product->get_name(),
                ];
            }
        }
    }
}

if (empty($slides)) {
    return;
}
?>

<div class="px-4 py-4">
    <div class="swiper hero-slider rounded-2xl overflow-hidden" dir="rtl">
        <div class="swiper-wrapper">
            <?php foreach ($slides as $slide) : ?>
                <div class="swiper-slide">
                    <a href="<?php echo esc_url($slide['link'] ?? '#'); ?>" class="block relative aspect-[2/1] bg-gradient-to-l from-primary/10 to-secondary/10">
                        <?php if (!empty($slide['image'])) : ?>
                            <img
                                src="<?php echo esc_url($slide['image']); ?>"
                                alt="<?php echo esc_attr($slide['title'] ?? ''); ?>"
                                class="w-full h-full object-cover"
                                loading="lazy"
                            >
                        <?php endif; ?>

                        <?php if (!empty($slide['title'])) : ?>
                            <div class="absolute inset-0 bg-gradient-to-l from-black/50 to-transparent flex items-center">
                                <div class="p-6 text-white">
                                    <h2 class="text-lg font-bold mb-2"><?php echo esc_html($slide['title']); ?></h2>
                                    <span class="inline-block bg-white text-secondary text-sm font-medium px-4 py-2 rounded-lg">
                                        <?php _e('خرید', 'ganjeh'); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="swiper-pagination"></div>
    </div>
</div>
