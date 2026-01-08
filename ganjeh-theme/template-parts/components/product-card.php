<?php
/**
 * Product Card Component
 *
 * @package Ganjeh
 */

global $product;

if (!$product || !is_a($product, 'WC_Product')) {
    return;
}

$product_id = $product->get_id();
$product_name = $product->get_name();
$product_price = $product->get_price_html();
$product_link = $product->get_permalink();
$product_image = $product->get_image('ganjeh-product-thumb', ['class' => 'w-full h-full object-cover', 'loading' => 'lazy']);
$is_on_sale = $product->is_on_sale();
$is_in_stock = $product->is_in_stock();
?>

<article class="product-card bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100" x-data="{ loading: false }">
    <!-- Product Image -->
    <a href="<?php echo esc_url($product_link); ?>" class="block relative aspect-square bg-gray-50">
        <?php echo $product_image; ?>

        <?php if ($is_on_sale) : ?>
            <span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-lg font-bold">
                <?php _e('تخفیف', 'ganjeh'); ?>
            </span>
        <?php endif; ?>

        <?php if (!$is_in_stock) : ?>
            <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                <span class="bg-white text-gray-700 text-xs px-3 py-1 rounded-lg font-medium">
                    <?php _e('ناموجود', 'ganjeh'); ?>
                </span>
            </div>
        <?php endif; ?>
    </a>

    <!-- Product Info -->
    <div class="p-3">
        <!-- Title -->
        <a href="<?php echo esc_url($product_link); ?>">
            <h3 class="text-sm font-medium text-gray-800 line-clamp-2 h-10 leading-5 mb-2">
                <?php echo esc_html($product_name); ?>
            </h3>
        </a>

        <!-- Price & Add Button -->
        <div class="flex items-center justify-between mt-2">
            <div class="text-sm">
                <?php if ($is_in_stock) : ?>
                    <span class="font-bold text-secondary"><?php echo $product_price; ?></span>
                <?php else : ?>
                    <span class="text-gray-400"><?php _e('ناموجود', 'ganjeh'); ?></span>
                <?php endif; ?>
            </div>

            <?php if ($is_in_stock && $product->is_type('simple')) : ?>
                <button
                    type="button"
                    class="w-8 h-8 bg-primary text-white rounded-lg flex items-center justify-center hover:bg-primary-dark transition-colors disabled:opacity-50"
                    :class="{ 'animate-pulse': loading }"
                    :disabled="loading"
                    @click="
                        loading = true;
                        fetch(ganjeh.ajax_url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'ganjeh_add_to_cart',
                                product_id: <?php echo $product_id; ?>,
                                quantity: 1,
                                nonce: ganjeh.nonce
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            loading = false;
                            if (data.success) {
                                document.querySelector('.ganjeh-cart-count').textContent = data.data.cart_count;
                                $dispatch('cart-updated', data.data);
                            }
                        })
                        .catch(() => loading = false);
                    "
                    aria-label="<?php _e('افزودن به سبد خرید', 'ganjeh'); ?>"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!loading">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" x-show="loading" x-cloak>
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            <?php elseif ($is_in_stock) : ?>
                <a href="<?php echo esc_url($product_link); ?>" class="w-8 h-8 bg-primary text-white rounded-lg flex items-center justify-center hover:bg-primary-dark transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
</article>
