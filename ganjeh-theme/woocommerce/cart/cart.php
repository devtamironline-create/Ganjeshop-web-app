<?php
/**
 * Cart Page Template
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart');
?>

<div class="ganjeh-cart" x-data="cartPage()">

    <!-- Header -->
    <header class="sticky top-0 z-40 bg-white px-4 py-3 flex items-center gap-3 border-b border-gray-100">
        <a href="javascript:history.back()" class="p-2 -mr-2 text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <h1 class="text-lg font-bold text-secondary"><?php _e('سبد خرید', 'ganjeh'); ?></h1>
    </header>

    <?php if (WC()->cart->is_empty()) : ?>

        <!-- Empty Cart -->
        <div class="empty-state px-4 py-16">
            <div class="empty-state-icon">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h2 class="text-lg font-bold text-gray-700 mb-2"><?php _e('سبد خرید شما خالی است', 'ganjeh'); ?></h2>
            <p class="text-gray-500 text-sm mb-6"><?php _e('محصولات مورد نظر خود را به سبد اضافه کنید', 'ganjeh'); ?></p>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="btn-primary inline-flex">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <?php _e('مشاهده فروشگاه', 'ganjeh'); ?>
            </a>
        </div>

    <?php else : ?>

        <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">

            <!-- Cart Items -->
            <div class="px-4 py-4 space-y-3">
                <?php
                foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                    $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                    $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

                    if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) :
                        $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                        ?>
                        <div class="cart-item card p-3" data-key="<?php echo esc_attr($cart_item_key); ?>">
                            <div class="flex gap-3">
                                <!-- Product Image -->
                                <a href="<?php echo esc_url($product_permalink); ?>" class="flex-shrink-0">
                                    <?php
                                    $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('ganjeh-product-thumb', ['class' => 'w-20 h-20 object-cover rounded-lg']), $cart_item, $cart_item_key);
                                    echo $thumbnail;
                                    ?>
                                </a>

                                <!-- Product Info -->
                                <div class="flex-1 min-w-0">
                                    <a href="<?php echo esc_url($product_permalink); ?>" class="text-sm font-medium text-gray-800 line-clamp-2 mb-1">
                                        <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)); ?>
                                    </a>

                                    <div class="text-sm font-bold text-secondary">
                                        <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); ?>
                                    </div>
                                </div>

                                <!-- Remove Button -->
                                <button
                                    type="button"
                                    class="flex-shrink-0 p-2 text-gray-400 hover:text-red-500 transition-colors"
                                    @click="removeItem('<?php echo esc_attr($cart_item_key); ?>')"
                                    aria-label="<?php _e('حذف', 'ganjeh'); ?>"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Quantity Controls -->
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                                <span class="text-sm text-gray-500"><?php _e('تعداد', 'ganjeh'); ?></span>

                                <div class="flex items-center gap-3" data-quantity-container>
                                    <button
                                        type="button"
                                        class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200 transition-colors"
                                        data-quantity-btn="minus"
                                        @click="updateQuantity('<?php echo esc_attr($cart_item_key); ?>', <?php echo $cart_item['quantity']; ?> - 1)"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>

                                    <span class="w-8 text-center font-medium"><?php echo $cart_item['quantity']; ?></span>

                                    <button
                                        type="button"
                                        class="w-8 h-8 rounded-lg bg-primary text-white flex items-center justify-center hover:bg-primary-dark transition-colors"
                                        data-quantity-btn="plus"
                                        @click="updateQuantity('<?php echo esc_attr($cart_item_key); ?>', <?php echo $cart_item['quantity']; ?> + 1)"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Coupon -->
            <div class="px-4 py-4 bg-white mt-2">
                <div class="flex gap-2">
                    <input
                        type="text"
                        name="coupon_code"
                        class="input flex-1"
                        placeholder="<?php _e('کد تخفیف', 'ganjeh'); ?>"
                        x-model="couponCode"
                    >
                    <button
                        type="button"
                        class="btn-outline px-6"
                        @click="applyCoupon()"
                        :disabled="!couponCode || applyingCoupon"
                    >
                        <span x-show="!applyingCoupon"><?php _e('اعمال', 'ganjeh'); ?></span>
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24" x-show="applyingCoupon" x-cloak>
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <?php do_action('woocommerce_cart_contents'); ?>

        </form>

        <!-- Cart Totals -->
        <div class="fixed bottom-0 left-0 right-0 z-50">
            <div class="max-w-md mx-auto bg-white border-t border-gray-200 shadow-lg">
                <div class="px-4 py-3">
                    <!-- Subtotal -->
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-500"><?php _e('جمع سبد خرید', 'ganjeh'); ?></span>
                        <span class="font-bold text-secondary cart-total"><?php wc_cart_totals_subtotal_html(); ?></span>
                    </div>

                    <?php if (WC()->cart->get_discount_total() > 0) : ?>
                        <div class="flex items-center justify-between mb-2 text-green-600">
                            <span><?php _e('تخفیف', 'ganjeh'); ?></span>
                            <span>- <?php echo wc_price(WC()->cart->get_discount_total()); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Buttons -->
                    <div class="flex gap-3 mt-4">
                        <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="btn-outline flex-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <?php _e('فروشگاه', 'ganjeh'); ?>
                        </a>
                        <a href="<?php echo wc_get_checkout_url(); ?>" class="btn-primary flex-1">
                            <?php _e('تایید و تکمیل سفارش', 'ganjeh'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

<script>
function cartPage() {
    return {
        couponCode: '',
        applyingCoupon: false,

        updateQuantity(key, quantity) {
            if (quantity < 1) {
                this.removeItem(key);
                return;
            }

            fetch(ganjeh.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ganjeh_update_cart_item',
                    cart_item_key: key,
                    quantity: quantity,
                    nonce: ganjeh.nonce
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        },

        removeItem(key) {
            if (!confirm('<?php _e('آیا از حذف این محصول اطمینان دارید؟', 'ganjeh'); ?>')) return;

            fetch(ganjeh.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ganjeh_remove_cart_item',
                    cart_item_key: key,
                    nonce: ganjeh.nonce
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        },

        applyCoupon() {
            if (!this.couponCode) return;

            this.applyingCoupon = true;

            fetch(ganjeh.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ganjeh_apply_coupon',
                    coupon_code: this.couponCode,
                    nonce: ganjeh.nonce
                })
            })
            .then(r => r.json())
            .then(data => {
                this.applyingCoupon = false;
                if (data.success) {
                    window.ganjehApp.showToast(data.data.message, 'success');
                    location.reload();
                } else {
                    window.ganjehApp.showToast(data.data.message, 'error');
                }
            })
            .catch(() => {
                this.applyingCoupon = false;
            });
        }
    };
}
</script>

<?php do_action('woocommerce_after_cart'); ?>
