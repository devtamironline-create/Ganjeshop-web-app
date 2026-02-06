<?php
/**
 * Cart Page Template - Minimal 2-Step Design
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart');

$cart_items = WC()->cart->get_cart();
$cart_total = WC()->cart->get_total();
$cart_subtotal = WC()->cart->get_subtotal();
?>

<div class="cart-page" x-data="cartPage()" x-init="init()">
    <!-- Header -->
    <header class="cart-header">
        <a href="javascript:history.back()" class="back-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <h1><?php _e('سبد خرید', 'ganjeh'); ?></h1>
        <div class="spacer"></div>
    </header>

    <!-- Step Indicator -->
    <div class="steps">
        <div class="step active">
            <div class="step-num">۱</div>
            <span><?php _e('سبد خرید', 'ganjeh'); ?></span>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-num">۲</div>
            <span><?php _e('پرداخت', 'ganjeh'); ?></span>
        </div>
    </div>

    <?php if (WC()->cart->is_empty()) : ?>
        <!-- Empty Cart -->
        <div class="empty-cart">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <h2><?php _e('سبد خرید شما خالی است', 'ganjeh'); ?></h2>
            <p><?php _e('محصولات مورد نظر خود را به سبد اضافه کنید', 'ganjeh'); ?></p>
            <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="shop-btn">
                <?php _e('مشاهده محصولات', 'ganjeh'); ?>
            </a>
        </div>
    <?php else : ?>
        <!-- Cart Items -->
        <div class="cart-items">
            <?php foreach ($cart_items as $cart_item_key => $cart_item) :
                $product = $cart_item['data'];
                $product_id = $cart_item['product_id'];
                $quantity = $cart_item['quantity'];
                $subtotal = WC()->cart->get_product_subtotal($product, $quantity);
                $thumbnail = $product->get_image_id();
                $variation_id = $cart_item['variation_id'] ?? 0;

                // Get variation attributes
                $variation_text = '';
                if ($variation_id && !empty($cart_item['variation'])) {
                    $attrs = [];
                    foreach ($cart_item['variation'] as $attr_name => $attr_value) {
                        // Decode URL-encoded attribute name and get proper label
                        $attr_name_clean = urldecode(str_replace('attribute_', '', $attr_name));
                        $attr_label = wc_attribute_label($attr_name_clean, $product);
                        $attr_value_decoded = urldecode($attr_value);
                        $attrs[] = $attr_label . ': ' . $attr_value_decoded;
                    }
                    $variation_text = implode(' | ', $attrs);
                }
            ?>
                <div class="cart-item" data-key="<?php echo esc_attr($cart_item_key); ?>">
                    <div class="item-img">
                        <?php if ($thumbnail) : ?>
                            <?php echo wp_get_attachment_image($thumbnail, 'thumbnail'); ?>
                        <?php else : ?>
                            <div class="no-img">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="item-info">
                        <a href="<?php echo get_permalink($product_id); ?>" class="item-name">
                            <?php echo wp_trim_words($product->get_name(), 6); ?>
                        </a>
                        <?php if ($variation_text) : ?>
                            <span class="item-var"><?php echo esc_html($variation_text); ?></span>
                        <?php endif; ?>
                        <div class="item-price"><?php echo $subtotal; ?></div>
                    </div>
                    <div class="item-ctrl">
                        <div class="qty-box">
                            <button type="button" @click="updateQty('<?php echo esc_attr($cart_item_key); ?>', <?php echo $quantity - 1; ?>)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                            </button>
                            <span><?php echo $quantity; ?></span>
                            <button type="button" @click="updateQty('<?php echo esc_attr($cart_item_key); ?>', <?php echo $quantity + 1; ?>)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>
                        <button type="button" class="del-btn" @click="removeItem('<?php echo esc_attr($cart_item_key); ?>')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Summary -->
        <div class="cart-summary">
            <div class="sum-row">
                <span><?php _e('جمع سبد', 'ganjeh'); ?></span>
                <span><?php echo wc_price($cart_subtotal); ?></span>
            </div>
            <?php if (WC()->cart->get_discount_total() > 0) : ?>
            <div class="sum-row discount">
                <span><?php _e('تخفیف', 'ganjeh'); ?></span>
                <span>- <?php echo wc_price(WC()->cart->get_discount_total()); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Shipping Methods -->
        <div class="shipping-methods">
            <h3 class="shipping-title"><?php _e('روش ارسال', 'ganjeh'); ?></h3>
            <div class="shipping-options">
                <label class="shipping-option" :class="{ 'active': shippingMethod === 'post' }">
                    <input type="radio" name="shipping_method" value="post" x-model="shippingMethod" @change="updateShipping()">
                    <div class="option-content">
                        <div class="option-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="option-info">
                            <span class="option-name"><?php _e('ارسال از طریق پست', 'ganjeh'); ?></span>
                            <span class="option-desc"><?php _e('ارسال به سراسر کشور', 'ganjeh'); ?></span>
                        </div>
                    </div>
                </label>

                <label class="shipping-option" :class="{ 'active': shippingMethod === 'courier' }">
                    <input type="radio" name="shipping_method" value="courier" x-model="shippingMethod" @change="updateShipping()">
                    <div class="option-content">
                        <div class="option-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                            </svg>
                        </div>
                        <div class="option-info">
                            <span class="option-name"><?php _e('ارسال با پیک در تهران', 'ganjeh'); ?></span>
                            <span class="option-desc"><?php _e('تحویل در همان روز', 'ganjeh'); ?></span>
                        </div>
                        <span class="option-price"><?php echo wc_price(200000); ?></span>
                    </div>
                </label>

                <label class="shipping-option" :class="{ 'active': shippingMethod === 'pickup' }">
                    <input type="radio" name="shipping_method" value="pickup" x-model="shippingMethod" @change="updateShipping()">
                    <div class="option-content">
                        <div class="option-icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="option-info">
                            <span class="option-name"><?php _e('دریافت حضوری', 'ganjeh'); ?></span>
                            <span class="option-desc"><?php _e('یا اسنپ از طرف مشتری', 'ganjeh'); ?></span>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="cart-bar">
            <div class="bar-total">
                <span class="label"><?php _e('قابل پرداخت', 'ganjeh'); ?></span>
                <span class="value"><?php echo $cart_total; ?></span>
            </div>
            <a href="<?php echo wc_get_checkout_url(); ?>" class="next-btn">
                <?php _e('ادامه خرید', 'ganjeh'); ?>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>
    <?php endif; ?>

    <!-- Loading -->
    <div class="loading" x-show="loading">
        <svg class="spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
</div>

<style>
.cart-page { min-height: 100vh; background: #f9fafb; padding-bottom: 100px; }
.cart-header { position: sticky; top: 0; z-index: 40; background: white; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f3f4f6; }
.cart-header h1 { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; }
.back-btn, .spacer { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; color: #374151; }

/* Steps */
.steps { display: flex; align-items: center; justify-content: center; padding: 20px 16px; background: white; border-bottom: 1px solid #f3f4f6; }
.step { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.step-num { width: 32px; height: 32px; border-radius: 50%; background: #e5e7eb; color: #9ca3af; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; }
.step.active .step-num { background: #4CB050; color: white; }
.step span { font-size: 12px; color: #6b7280; font-weight: 500; }
.step.active span { color: #4CB050; font-weight: 600; }
.step-line { width: 60px; height: 2px; background: #e5e7eb; margin: 0 12px; margin-bottom: 22px; }

/* Empty */
.empty-cart { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px; text-align: center; }
.empty-icon { width: 80px; height: 80px; color: #d1d5db; margin-bottom: 20px; }
.empty-cart h2 { font-size: 18px; font-weight: 700; color: #1f2937; margin: 0 0 8px; }
.empty-cart p { font-size: 14px; color: #6b7280; margin: 0 0 24px; }
.shop-btn { padding: 14px 32px; background: #4CB050; color: white; border-radius: 12px; font-size: 14px; font-weight: 600; text-decoration: none; }

/* Items */
.cart-items { padding: 16px; display: flex; flex-direction: column; gap: 12px; }
.cart-item { display: flex; gap: 12px; background: white; border-radius: 16px; padding: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.item-img { width: 80px; height: 80px; border-radius: 12px; overflow: hidden; background: #f3f4f6; flex-shrink: 0; }
.item-img img { width: 100%; height: 100%; object-fit: cover; }
.no-img { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #d1d5db; }
.item-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 4px; }
.item-name { font-size: 14px; font-weight: 600; color: #1f2937; text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.item-var { font-size: 11px; color: #6b7280; }
.item-price { font-size: 14px; font-weight: 700; color: #4CB050; margin-top: auto; }
.item-ctrl { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
.qty-box { display: flex; align-items: center; gap: 6px; background: #f3f4f6; border-radius: 8px; padding: 4px; }
.qty-box button { width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: white; border: none; border-radius: 6px; color: #374151; cursor: pointer; }
.qty-box span { min-width: 24px; text-align: center; font-size: 14px; font-weight: 600; color: #1f2937; }
.del-btn { background: none; border: none; color: #ef4444; cursor: pointer; padding: 4px; }

/* Summary */
.cart-summary { margin: 0 16px; padding: 16px; background: white; border-radius: 16px; }
.sum-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 14px; color: #4b5563; }
.sum-row.discount { color: #4CB050; }

/* Shipping Methods */
.shipping-methods { margin: 16px; padding: 16px; background: white; border-radius: 16px; }
.shipping-title { font-size: 15px; font-weight: 700; color: #1f2937; margin: 0 0 16px; }
.shipping-options { display: flex; flex-direction: column; gap: 10px; }
.shipping-option { cursor: pointer; }
.shipping-option input { display: none; }
.shipping-option .option-content {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: #f9fafb;
    border: 2px solid transparent;
    border-radius: 12px;
    transition: all 0.2s;
}
.shipping-option.active .option-content {
    border-color: #4CB050;
    background: #f0fdf4;
}
.option-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    flex-shrink: 0;
}
.shipping-option.active .option-icon {
    background: #4CB050;
    color: white;
}
.option-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.option-name {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
}
.option-desc {
    font-size: 12px;
    color: #6b7280;
}
.option-price {
    font-size: 13px;
    font-weight: 700;
    color: #4CB050;
    white-space: nowrap;
}

/* Bottom Bar */
.cart-bar { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 515px; background: white; border-top: 1px solid #e5e7eb; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: 0 -4px 20px rgba(0,0,0,0.08); }
.bar-total { display: flex; flex-direction: column; gap: 2px; }
.bar-total .label { font-size: 12px; color: #6b7280; }
.bar-total .value { font-size: 18px; font-weight: 700; color: #1f2937; }
.next-btn { display: flex; align-items: center; gap: 8px; padding: 14px 28px; background: linear-gradient(135deg, #4CB050, #3d9142); color: white; border-radius: 12px; font-size: 14px; font-weight: 600; text-decoration: none; }

/* Loading */
.loading { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 9999; }
.loading svg { width: 40px; height: 40px; color: #4CB050; }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

<script>
function cartPage() {
    return {
        loading: false,
        shippingMethod: localStorage.getItem('ganjeh_shipping_method') || 'post',
        shippingCosts: {
            'post': 0,
            'courier': 200000,
            'pickup': 0
        },

        init() {
            // Apply stored shipping method on load
            this.updateShipping();
        },

        updateShipping() {
            // Save to localStorage
            localStorage.setItem('ganjeh_shipping_method', this.shippingMethod);

            // Update total display
            const shippingCost = this.shippingCosts[this.shippingMethod] || 0;
            const baseTotal = <?php echo WC()->cart->get_total('edit'); ?>;
            const newTotal = baseTotal + shippingCost;

            // Update displayed total
            const totalEl = document.querySelector('.bar-total .value');
            if (totalEl) {
                totalEl.textContent = new Intl.NumberFormat('fa-IR').format(newTotal) + ' تومان';
            }
        },

        updateQty(key, qty) {
            if (qty < 1) { this.removeItem(key); return; }
            this.loading = true;
            fetch(ganjeh.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'ganjeh_update_cart', cart_key: key, quantity: qty, nonce: ganjeh.nonce })
            })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); else { this.loading = false; alert(data.data.message); } })
            .catch(() => this.loading = false);
        },

        removeItem(key) {
            if (!confirm('<?php _e('آیا از حذف این محصول مطمئن هستید؟', 'ganjeh'); ?>')) return;
            this.loading = true;
            fetch(ganjeh.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'ganjeh_remove_cart_item', cart_key: key, nonce: ganjeh.nonce })
            })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); else { this.loading = false; alert(data.data.message); } })
            .catch(() => this.loading = false);
        }
    };
}
</script>

<?php do_action('woocommerce_after_cart'); ?>
