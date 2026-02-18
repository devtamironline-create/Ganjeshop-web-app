<?php
/**
 * Front Page Template
 *
 * @package Ganjeh
 */

get_header();

$sections_settings = ganjeh_get_product_sections_settings();
?>

<main id="main-content" class="pb-20">

    <!-- Stories -->
    <?php ganjeh_render_stories(); ?>

    <!-- Hero Slider -->
    <?php get_template_part('template-parts/components/hero-slider'); ?>

    <!-- Banners: After Slider -->
    <?php ganjeh_render_banners_at_position('after_slider'); ?>

    <!-- Categories Grid -->
    <section class="px-4 py-6">
        <?php get_template_part('template-parts/components/category-grid'); ?>
    </section>

    <!-- Banners: After Categories -->
    <?php ganjeh_render_banners_at_position('after_categories'); ?>

    <!-- Dynamic Product Sections -->
    <?php
    $all_sections = ganjeh_get_sorted_sections();
    $section_num = 0;
    foreach ($all_sections as $section_key => $section_data) :
        if (empty($section_data['enabled'])) continue;
        $section_products = ganjeh_get_section_products($section_key);
        if (empty($section_products)) continue;
        $section_num++;
        $section_title = $section_data['title'] ?? '';
        $section_type = $section_data['type'] ?? 'recent';
    ?>
    <section class="py-4 product-section" data-section="<?php echo esc_attr($section_key); ?>">
        <div class="px-4 flex items-center justify-between mb-3">
            <h2 class="text-base font-bold text-gray-800"><?php echo esc_html($section_title); ?></h2>
            <a href="<?php echo esc_url(ganjeh_get_section_view_more_url($section_key)); ?>" class="text-sm text-primary flex items-center gap-1">
                <?php _e('مشاهده بیشتر', 'ganjeh'); ?>
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="swiper products-swiper" dir="rtl">
            <div class="swiper-wrapper">
                <?php foreach ($section_products as $product) : ?>
                    <div class="swiper-slide">
                        <?php
                        $GLOBALS['product'] = $product;
                        get_template_part('template-parts/components/product-card');
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php
        // Render banners after first section
        if ($section_num === 1) {
            ganjeh_render_banners_at_position('after_featured');
        }
    ?>

    <?php endforeach; ?>

    <!-- Promotional Banners Carousel -->
    <?php ganjeh_render_promo_banners(); ?>

    <!-- Banners: After Products -->
    <?php ganjeh_render_banners_at_position('after_new'); ?>

</main>

<style>
/* Products Section */
.product-section {
    overflow: hidden;
}

/* Products Swiper */
.products-swiper {
    padding: 0 16px;
    overflow: hidden;
}

.products-swiper .swiper-wrapper {
    display: flex;
}

.products-swiper .swiper-slide {
    width: auto;
    flex-shrink: 0;
}

.products-swiper .product-card-compact {
    width: 130px !important;
}
</style>

<?php
get_footer();
