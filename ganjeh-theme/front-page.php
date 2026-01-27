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

    <!-- Promotional Banners Carousel -->
    <?php ganjeh_render_promo_banners(); ?>

    <!-- Product Section 1 (Featured) -->
    <?php if (ganjeh_is_section_enabled('featured')) :
        $featured_products = ganjeh_get_section_products('featured');
        $featured_title = ganjeh_get_section_title('featured');
        if ($featured_products) :
    ?>
    <section class="py-4 product-section" data-section="featured">
        <div class="px-4 flex items-center justify-between mb-3">
            <h2 class="text-base font-bold text-gray-800"><?php echo esc_html($featured_title); ?></h2>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="text-sm text-primary flex items-center gap-1">
                <?php _e('مشاهده بیشتر', 'ganjeh'); ?>
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="swiper products-swiper" dir="rtl">
            <div class="swiper-wrapper">
                <?php foreach ($featured_products as $product) : ?>
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
    <?php endif; endif; ?>

    <!-- Banners: After Featured -->
    <?php ganjeh_render_banners_at_position('after_featured'); ?>

    <!-- Product Section 2 (Sale) -->
    <?php if (ganjeh_is_section_enabled('sale')) :
        $sale_products = ganjeh_get_section_products('sale');
        $sale_title = ganjeh_get_section_title('sale');
        if ($sale_products) :
    ?>
    <section class="py-4 product-section" data-section="sale">
        <div class="px-4 flex items-center justify-between mb-3">
            <h2 class="text-base font-bold text-gray-800"><?php echo esc_html($sale_title); ?></h2>
            <a href="<?php echo home_url('/shop/?on_sale=1'); ?>" class="text-sm text-primary flex items-center gap-1">
                <?php _e('مشاهده بیشتر', 'ganjeh'); ?>
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="swiper products-swiper" dir="rtl">
            <div class="swiper-wrapper">
                <?php foreach ($sale_products as $product) : ?>
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
    <?php endif; endif; ?>

    <!-- Banners: After Sale -->
    <?php ganjeh_render_banners_at_position('after_sale'); ?>

    <!-- Product Section 3 (New) -->
    <?php if (ganjeh_is_section_enabled('new')) :
        $new_products = ganjeh_get_section_products('new');
        $new_title = ganjeh_get_section_title('new');
        if ($new_products) :
    ?>
    <section class="py-4 product-section" data-section="new">
        <div class="px-4 flex items-center justify-between mb-3">
            <h2 class="text-base font-bold text-gray-800"><?php echo esc_html($new_title); ?></h2>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>?orderby=date" class="text-sm text-primary flex items-center gap-1">
                <?php _e('مشاهده بیشتر', 'ganjeh'); ?>
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="swiper products-swiper" dir="rtl">
            <div class="swiper-wrapper">
                <?php foreach ($new_products as $product) : ?>
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
    <?php endif; endif; ?>

    <!-- Banners: After New Products -->
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
    width: 145px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all product swipers
    if (typeof Swiper !== 'undefined') {
        document.querySelectorAll('.products-swiper').forEach(function(el) {
            new Swiper(el, {
                slidesPerView: 'auto',
                spaceBetween: 12,
                freeMode: true,
                grabCursor: true,
                resistance: true,
                resistanceRatio: 0.5
            });
        });
    }
});
</script>

<?php
get_footer();
