<?php
/**
 * Archive Product Template
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="main-content" class="pb-20" x-data="{ filtersOpen: false }" @open-filters.window="filtersOpen = true">

    <?php
    /**
     * Hook: woocommerce_before_main_content.
     */
    do_action('woocommerce_before_main_content');
    ?>

    <?php if (woocommerce_product_loop()) : ?>

        <?php
        /**
         * Hook: woocommerce_before_shop_loop.
         */
        do_action('woocommerce_before_shop_loop');
        ?>

        <!-- Products Grid -->
        <div class="px-4 py-4">
            <?php
            woocommerce_product_loop_start();

            if (wc_get_loop_prop('total')) {
                while (have_posts()) {
                    the_post();

                    /**
                     * Hook: woocommerce_shop_loop.
                     */
                    do_action('woocommerce_shop_loop');

                    wc_get_template_part('content', 'product');
                }
            }

            woocommerce_product_loop_end();
            ?>
        </div>

        <?php
        /**
         * Hook: woocommerce_after_shop_loop.
         */
        do_action('woocommerce_after_shop_loop');
        ?>

        <!-- Pagination -->
        <div class="px-4 pb-4">
            <?php woocommerce_pagination(); ?>
        </div>

    <?php else : ?>

        <!-- No Products -->
        <div class="empty-state px-4 py-16">
            <div class="empty-state-icon">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            <h2 class="text-lg font-bold text-gray-700 mb-2"><?php _e('محصولی یافت نشد', 'ganjeh'); ?></h2>
            <p class="text-gray-500 text-sm mb-6"><?php _e('متأسفانه محصولی مطابق با جستجوی شما پیدا نشد.', 'ganjeh'); ?></p>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="btn-primary inline-flex">
                <?php _e('مشاهده همه محصولات', 'ganjeh'); ?>
            </a>
        </div>

    <?php endif; ?>

    <?php
    /**
     * Hook: woocommerce_after_main_content.
     */
    do_action('woocommerce_after_main_content');
    ?>

    <!-- Filters Modal -->
    <div
        class="modal-backdrop"
        x-show="filtersOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="filtersOpen = false"
        x-cloak
    ></div>

    <div
        class="modal-content"
        x-show="filtersOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="transform translate-y-full"
        x-transition:enter-end="transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="transform translate-y-0"
        x-transition:leave-end="transform translate-y-full"
        x-cloak
    >
        <div class="modal-header">
            <h3 class="text-lg font-bold text-secondary"><?php _e('فیلتر محصولات', 'ganjeh'); ?></h3>
            <button type="button" class="p-2 text-gray-500" @click="filtersOpen = false">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="px-4 py-4">
            <!-- Sort By -->
            <div class="mb-6">
                <h4 class="font-medium text-secondary mb-3"><?php _e('مرتب‌سازی', 'ganjeh'); ?></h4>
                <div class="space-y-2">
                    <?php
                    $orderby_options = [
                        'menu_order' => __('پیش‌فرض', 'ganjeh'),
                        'popularity' => __('پرفروش‌ترین', 'ganjeh'),
                        'rating'     => __('بالاترین امتیاز', 'ganjeh'),
                        'date'       => __('جدیدترین', 'ganjeh'),
                        'price'      => __('ارزان‌ترین', 'ganjeh'),
                        'price-desc' => __('گران‌ترین', 'ganjeh'),
                    ];
                    $current_orderby = isset($_GET['orderby']) ? wc_clean($_GET['orderby']) : 'menu_order';

                    foreach ($orderby_options as $value => $label) :
                    ?>
                        <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer">
                            <input
                                type="radio"
                                name="orderby"
                                value="<?php echo esc_attr($value); ?>"
                                class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                <?php checked($current_orderby, $value); ?>
                                onchange="window.location.href = '<?php echo esc_url(remove_query_arg('orderby')); ?>' + (this.value !== 'menu_order' ? '&orderby=' + this.value : '')"
                            >
                            <span class="text-sm text-gray-700"><?php echo esc_html($label); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Categories -->
            <?php
            $product_categories = get_terms([
                'taxonomy'   => 'product_cat',
                'hide_empty' => true,
                'parent'     => 0,
            ]);

            if ($product_categories && !is_wp_error($product_categories)) :
            ?>
                <div class="mb-6">
                    <h4 class="font-medium text-secondary mb-3"><?php _e('دسته‌بندی', 'ganjeh'); ?></h4>
                    <div class="space-y-2">
                        <?php foreach ($product_categories as $category) : ?>
                            <a
                                href="<?php echo get_term_link($category); ?>"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors"
                            >
                                <span class="text-sm text-gray-700"><?php echo esc_html($category->name); ?></span>
                                <span class="text-xs text-gray-400"><?php echo $category->count; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</main>

<?php
get_footer();
