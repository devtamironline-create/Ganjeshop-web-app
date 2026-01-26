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

<div class="hero-slider-wrapper px-4 pt-4 pb-2">
    <div class="swiper hero-slider" dir="rtl">
        <div class="swiper-wrapper">
            <?php foreach ($slides as $slide) : ?>
                <div class="swiper-slide">
                    <a href="<?php echo esc_url($slide['link'] ?? '#'); ?>" class="block">
                        <div class="slider-card">
                            <?php if (!empty($slide['image'])) : ?>
                                <img
                                    src="<?php echo esc_url($slide['image']); ?>"
                                    alt="<?php echo esc_attr($slide['title'] ?? ''); ?>"
                                    class="slider-image"
                                    loading="lazy"
                                >
                            <?php else : ?>
                                <div class="slider-placeholder">
                                    <span><?php echo esc_html($slide['title'] ?? 'اسلاید'); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="swiper-pagination hero-pagination"></div>
    </div>
</div>

<style>
.hero-slider-wrapper {
    direction: rtl;
}

.hero-slider {
    border-radius: 16px;
    overflow: visible;
}

.hero-slider .swiper-slide {
    border-radius: 16px;
    overflow: hidden;
}

.hero-slider .slider-card {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
    aspect-ratio: 16 / 7;
}

.hero-slider .slider-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 16px;
}

.hero-slider .slider-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-size: 14px;
}

/* Pagination styling */
.hero-slider .hero-pagination {
    position: relative;
    bottom: auto;
    margin-top: 12px;
    display: flex;
    justify-content: center;
    gap: 6px;
}

.hero-slider .swiper-pagination-bullet {
    width: 8px;
    height: 8px;
    background: #d1d5db;
    opacity: 1;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.hero-slider .swiper-pagination-bullet-active {
    width: 24px;
    background: var(--color-secondary, #1E3A5F);
    border-radius: 4px;
}
</style>
