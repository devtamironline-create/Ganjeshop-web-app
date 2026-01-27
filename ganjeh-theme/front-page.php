<?php
/**
 * Front Page Template
 *
 * @package Ganjeh
 */

get_header();
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

    <!-- Featured Products Carousel -->
    <section class="py-4">
        <div class="px-4 flex items-center justify-between mb-3">
            <h2 class="text-base font-bold text-gray-800"><?php _e('محصولات ویژه', 'ganjeh'); ?></h2>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="text-sm text-primary flex items-center gap-1">
                <?php _e('مشاهده بیشتر', 'ganjeh'); ?>
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <?php
        $featured_products = wc_get_products([
            'limit'    => 10,
            'featured' => true,
            'status'   => 'publish',
        ]);

        // Fallback to recent products if no featured
        if (empty($featured_products)) {
            $featured_products = wc_get_products([
                'limit'   => 10,
                'orderby' => 'date',
                'order'   => 'DESC',
                'status'  => 'publish',
            ]);
        }

        if ($featured_products) :
        ?>
            <div class="products-carousel-wrapper">
                <div class="products-carousel">
                    <?php foreach ($featured_products as $product) : ?>
                        <?php
                        $GLOBALS['product'] = $product;
                        get_template_part('template-parts/components/product-card');
                        ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Banners: After Featured -->
    <?php ganjeh_render_banners_at_position('after_featured'); ?>

    <!-- On Sale Products Carousel -->
    <?php
    $sale_products = wc_get_products([
        'limit'   => 10,
        'on_sale' => true,
        'status'  => 'publish',
    ]);

    if ($sale_products) :
    ?>
    <section class="py-4">
        <div class="px-4 flex items-center justify-between mb-3">
            <h2 class="text-base font-bold text-gray-800"><?php _e('تخفیف‌های ویژه', 'ganjeh'); ?></h2>
            <a href="<?php echo home_url('/shop/?on_sale=1'); ?>" class="text-sm text-primary flex items-center gap-1">
                <?php _e('مشاهده بیشتر', 'ganjeh'); ?>
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="products-carousel-wrapper">
            <div class="products-carousel">
                <?php foreach ($sale_products as $product) : ?>
                    <?php
                    $GLOBALS['product'] = $product;
                    get_template_part('template-parts/components/product-card');
                    ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Banners: After Sale -->
    <?php ganjeh_render_banners_at_position('after_sale'); ?>

    <!-- New Products Carousel -->
    <section class="py-4">
        <div class="px-4 flex items-center justify-between mb-3">
            <h2 class="text-base font-bold text-gray-800"><?php _e('جدیدترین محصولات', 'ganjeh'); ?></h2>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>?orderby=date" class="text-sm text-primary flex items-center gap-1">
                <?php _e('مشاهده بیشتر', 'ganjeh'); ?>
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <?php
        $new_products = wc_get_products([
            'limit'   => 10,
            'orderby' => 'date',
            'order'   => 'DESC',
            'status'  => 'publish',
        ]);

        if ($new_products) :
        ?>
            <div class="products-carousel-wrapper">
                <div class="products-carousel">
                    <?php foreach ($new_products as $product) : ?>
                        <?php
                        $GLOBALS['product'] = $product;
                        get_template_part('template-parts/components/product-card');
                        ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Banners: After New Products -->
    <?php ganjeh_render_banners_at_position('after_new'); ?>

</main>

<style>
/* Products Carousel */
.products-carousel-wrapper {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 0 16px;
}

.products-carousel-wrapper::-webkit-scrollbar {
    display: none;
}

.products-carousel {
    display: flex;
    gap: 12px;
    padding-bottom: 4px;
}

.products-carousel .product-card-compact {
    flex-shrink: 0;
}
</style>

<?php
get_footer();
