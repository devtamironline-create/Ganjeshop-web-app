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

    <!-- Categories Grid -->
    <section class="px-4 py-6">
        <?php get_template_part('template-parts/components/category-grid'); ?>
    </section>

    <!-- Featured Products -->
    <section class="px-4 py-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-secondary"><?php _e('محصولات ویژه', 'ganjeh'); ?></h2>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="text-sm text-primary flex items-center gap-1">
                <?php _e('مشاهده بیشتر', 'ganjeh'); ?>
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <?php
        $featured_products = wc_get_products([
            'limit'    => 8,
            'featured' => true,
            'status'   => 'publish',
        ]);

        if ($featured_products) :
        ?>
            <div class="grid grid-cols-2 gap-3">
                <?php foreach ($featured_products as $product) : ?>
                    <?php
                    $GLOBALS['product'] = $product;
                    get_template_part('template-parts/components/product-card');
                    ?>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <?php
            // Fallback to recent products
            $recent_products = wc_get_products([
                'limit'   => 8,
                'orderby' => 'date',
                'order'   => 'DESC',
                'status'  => 'publish',
            ]);

            if ($recent_products) :
            ?>
                <div class="grid grid-cols-2 gap-3">
                    <?php foreach ($recent_products as $product) : ?>
                        <?php
                        $GLOBALS['product'] = $product;
                        get_template_part('template-parts/components/product-card');
                        ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <!-- On Sale Products -->
    <section class="px-4 py-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-secondary"><?php _e('تخفیف‌های ویژه', 'ganjeh'); ?></h2>
            <a href="<?php echo home_url('/shop/?on_sale=1'); ?>" class="text-sm text-primary flex items-center gap-1">
                <?php _e('مشاهده بیشتر', 'ganjeh'); ?>
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <?php
        $sale_products = wc_get_products([
            'limit'   => 6,
            'on_sale' => true,
            'status'  => 'publish',
        ]);

        if ($sale_products) :
        ?>
            <div class="overflow-x-auto scrollbar-hide -mx-4 px-4">
                <div class="flex gap-3" style="width: max-content;">
                    <?php foreach ($sale_products as $product) : ?>
                        <div class="w-40">
                            <?php
                            $GLOBALS['product'] = $product;
                            get_template_part('template-parts/components/product-card');
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- New Products -->
    <section class="px-4 py-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-secondary"><?php _e('جدیدترین محصولات', 'ganjeh'); ?></h2>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>?orderby=date" class="text-sm text-primary flex items-center gap-1">
                <?php _e('مشاهده بیشتر', 'ganjeh'); ?>
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <?php
        $new_products = wc_get_products([
            'limit'   => 8,
            'orderby' => 'date',
            'order'   => 'DESC',
            'status'  => 'publish',
        ]);

        if ($new_products) :
        ?>
            <div class="grid grid-cols-2 gap-3">
                <?php foreach ($new_products as $product) : ?>
                    <?php
                    $GLOBALS['product'] = $product;
                    get_template_part('template-parts/components/product-card');
                    ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php
get_footer();
