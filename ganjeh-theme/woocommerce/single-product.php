<?php
/**
 * Single Product Template
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

global $product;

if (!$product || !is_a($product, 'WC_Product')) {
    $product = wc_get_product(get_the_ID());
}

if (!$product) {
    get_template_part('template-parts/content', 'none');
    get_footer();
    return;
}

$product_id = $product->get_id();
$gallery_ids = $product->get_gallery_image_ids();
$main_image_id = $product->get_image_id();
$all_images = $main_image_id ? array_merge([$main_image_id], $gallery_ids) : $gallery_ids;
?>

<main id="main-content" class="pb-32" x-data="{ quantity: 1 }">

    <!-- Header -->
    <header class="sticky top-0 z-40 bg-white px-4 py-3 flex items-center justify-between border-b border-gray-100">
        <div class="flex items-center gap-3">
            <a href="javascript:history.back()" class="p-2 -mr-2 text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <h1 class="text-base font-bold text-secondary"><?php _e('جزئیات محصول', 'ganjeh'); ?></h1>
        </div>
        <button type="button" class="p-2 text-gray-600" aria-label="<?php _e('اشتراک‌گذاری', 'ganjeh'); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
        </button>
    </header>

    <!-- Breadcrumb -->
    <nav class="px-4 py-2 text-xs text-gray-500 flex items-center gap-1 overflow-x-auto scrollbar-hide">
        <a href="<?php echo home_url('/'); ?>" class="flex items-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
        </a>
        <span>|</span>
        <?php
        $terms = get_the_terms($product_id, 'product_cat');
        if ($terms && !is_wp_error($terms)) :
            $term = $terms[0];
            $ancestors = get_ancestors($term->term_id, 'product_cat');
            $ancestors = array_reverse($ancestors);

            foreach ($ancestors as $ancestor_id) :
                $ancestor = get_term($ancestor_id, 'product_cat');
                ?>
                <a href="<?php echo get_term_link($ancestor); ?>" class="whitespace-nowrap hover:text-primary"><?php echo esc_html($ancestor->name); ?></a>
                <span>|</span>
            <?php endforeach; ?>

            <a href="<?php echo get_term_link($term); ?>" class="whitespace-nowrap hover:text-primary"><?php echo esc_html($term->name); ?></a>
            <span>|</span>
        <?php endif; ?>
        <span class="text-gray-700 whitespace-nowrap"><?php echo wp_trim_words($product->get_name(), 5); ?></span>
    </nav>

    <!-- Product Images -->
    <div class="px-4 py-4">
        <?php if (!empty($all_images)) : ?>
            <div class="swiper product-gallery rounded-2xl overflow-hidden bg-white" dir="rtl">
                <div class="swiper-wrapper">
                    <?php foreach ($all_images as $image_id) : ?>
                        <div class="swiper-slide">
                            <div class="aspect-square bg-gray-50 flex items-center justify-center p-4">
                                <?php echo wp_get_attachment_image($image_id, 'ganjeh-product-large', false, ['class' => 'max-w-full max-h-full object-contain']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        <?php else : ?>
            <div class="aspect-square bg-gray-100 rounded-2xl flex items-center justify-center">
                <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        <?php endif; ?>
    </div>

    <!-- Product Info -->
    <div class="px-4">
        <!-- Category -->
        <?php if ($terms && !is_wp_error($terms)) : ?>
            <a href="<?php echo get_term_link($terms[0]); ?>" class="text-sm text-primary font-medium">
                <?php echo esc_html($terms[0]->name); ?>
            </a>
        <?php endif; ?>

        <!-- Title -->
        <h1 class="text-lg font-bold text-secondary mt-2 mb-3">
            <?php the_title(); ?>
        </h1>

        <!-- Rating -->
        <?php if ($product->get_average_rating() > 0) : ?>
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <div class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span class="font-medium"><?php echo number_format($product->get_average_rating(), 1); ?> <?php _e('از', 'ganjeh'); ?> ۵</span>
                </div>
                <span class="text-gray-400">|</span>
                <span><?php echo $product->get_review_count(); ?> <?php _e('نظر', 'ganjeh'); ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Short Description -->
    <?php if ($product->get_short_description()) : ?>
        <div class="px-4 py-4 mt-4 bg-white">
            <h3 class="font-bold text-secondary mb-2"><?php _e('توضیحات', 'ganjeh'); ?></h3>
            <div class="text-sm text-gray-600 leading-relaxed">
                <?php echo wp_kses_post($product->get_short_description()); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Attributes -->
    <?php
    $attributes = $product->get_attributes();
    if (!empty($attributes)) :
    ?>
        <div class="px-4 py-4 mt-2 bg-white">
            <h3 class="font-bold text-secondary mb-3"><?php _e('مشخصات', 'ganjeh'); ?></h3>
            <div class="space-y-2">
                <?php foreach ($attributes as $attribute) : ?>
                    <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 last:border-0">
                        <span class="text-gray-500"><?php echo wc_attribute_label($attribute->get_name()); ?></span>
                        <span class="font-medium text-gray-700">
                            <?php
                            if ($attribute->is_taxonomy()) {
                                $values = wc_get_product_terms($product_id, $attribute->get_name(), ['fields' => 'names']);
                                echo implode('، ', $values);
                            } else {
                                echo implode('، ', $attribute->get_options());
                            }
                            ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Full Description -->
    <?php if ($product->get_description()) : ?>
        <div class="px-4 py-4 mt-2 bg-white" x-data="{ open: false }">
            <button
                type="button"
                class="w-full flex items-center justify-between"
                @click="open = !open"
            >
                <h3 class="font-bold text-secondary"><?php _e('توضیحات کامل', 'ganjeh'); ?></h3>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div class="text-sm text-gray-600 leading-relaxed mt-3" x-show="open" x-collapse>
                <?php echo wp_kses_post($product->get_description()); ?>
            </div>
        </div>
    <?php endif; ?>

</main>

<!-- Fixed Bottom Bar -->
<div class="fixed bottom-0 left-0 right-0 z-50">
    <div class="max-w-md mx-auto bg-white border-t border-gray-200 shadow-lg px-4 py-3">
        <!-- Price -->
        <div class="flex items-center justify-between mb-3">
            <span class="text-gray-500"><?php _e('قیمت', 'ganjeh'); ?></span>
            <div class="text-left">
                <?php if ($product->is_on_sale()) : ?>
                    <span class="text-sm text-gray-400 line-through block"><?php echo wc_price($product->get_regular_price()); ?></span>
                <?php endif; ?>
                <span class="text-lg font-bold text-secondary"><?php echo $product->get_price_html(); ?></span>
            </div>
        </div>

        <!-- Add to Cart -->
        <?php if ($product->is_in_stock()) : ?>
            <?php if ($product->is_type('simple')) : ?>
                <button
                    type="button"
                    class="w-full btn-primary"
                    x-data="{ loading: false }"
                    :class="{ 'opacity-75': loading }"
                    :disabled="loading"
                    @click="
                        loading = true;
                        fetch(ganjeh.ajax_url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'ganjeh_add_to_cart',
                                product_id: <?php echo $product_id; ?>,
                                quantity: quantity,
                                nonce: ganjeh.nonce
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            loading = false;
                            if (data.success) {
                                document.querySelector('.ganjeh-cart-count').textContent = data.data.cart_count;
                                window.ganjehApp.showToast(data.data.message, 'success');
                            } else {
                                window.ganjehApp.showToast(data.data.message, 'error');
                            }
                        })
                        .catch(() => {
                            loading = false;
                            window.ganjehApp.showToast(ganjeh.i18n.error, 'error');
                        });
                    "
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!loading">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="!loading"><?php _e('افزودن به سبد خرید', 'ganjeh'); ?></span>
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" x-show="loading" x-cloak>
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            <?php else : ?>
                <a href="<?php echo $product->add_to_cart_url(); ?>" class="w-full btn-primary block text-center">
                    <?php echo $product->add_to_cart_text(); ?>
                </a>
            <?php endif; ?>
        <?php else : ?>
            <button type="button" class="w-full btn bg-gray-300 text-gray-500 cursor-not-allowed" disabled>
                <?php _e('ناموجود', 'ganjeh'); ?>
            </button>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Product Gallery Swiper
    if (typeof Swiper !== 'undefined' && document.querySelector('.product-gallery')) {
        new Swiper('.product-gallery', {
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
        });
    }
});
</script>

<?php
get_footer();
