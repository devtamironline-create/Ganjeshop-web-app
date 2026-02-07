<?php
/**
 * Product Card Component - Compact Carousel Style
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
$is_on_sale = $product->is_on_sale();
$is_in_stock = $product->is_in_stock();
$has_image = has_post_thumbnail($product_id);

// Calculate discount percentage
$discount_percent = 0;
if ($is_on_sale && $product->is_type('simple')) {
    $regular_price = (float) $product->get_regular_price();
    $sale_price = (float) $product->get_sale_price();
    if ($regular_price > 0) {
        $discount_percent = round((($regular_price - $sale_price) / $regular_price) * 100);
    }
}
?>

<article class="product-card-compact" x-data="{ loading: false }">
    <!-- Product Image -->
    <a href="<?php echo esc_url($product_link); ?>" class="product-image-wrapper">
        <?php if ($has_image) : ?>
            <?php echo $product->get_image('ganjeh-product-thumb', ['class' => 'product-image', 'loading' => 'lazy']); ?>
        <?php else : ?>
            <div class="product-placeholder">
                <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="80" height="80" rx="12" fill="#f3f4f6"/>
                    <path d="M28 52L36 44L42 50L52 40L56 44V56H24V52H28Z" fill="#d1d5db"/>
                    <circle cx="34" cy="34" r="6" fill="#d1d5db"/>
                    <rect x="22" y="22" width="36" height="36" rx="4" stroke="#9ca3af" stroke-width="2" stroke-dasharray="4 4"/>
                </svg>
            </div>
        <?php endif; ?>

        <?php if ($discount_percent > 0) : ?>
            <span class="discount-badge"><?php echo $discount_percent; ?>%</span>
        <?php endif; ?>

        <?php if (!$is_in_stock) : ?>
            <div class="out-of-stock-overlay">
                <span><?php _e('ناموجود', 'ganjeh'); ?></span>
            </div>
        <?php endif; ?>
    </a>

    <!-- Product Info -->
    <div class="product-info">
        <!-- Title -->
        <a href="<?php echo esc_url($product_link); ?>" class="product-title">
            <?php echo esc_html($product_name); ?>
        </a>

        <!-- Price & Add Button -->
        <div class="product-footer">
            <?php if ($is_in_stock && ($product->is_type('simple') || $product->is_type('variable'))) : ?>
                <button
                    type="button"
                    class="add-to-cart-btn"
                    :class="{ 'loading': loading }"
                    :disabled="loading"
                    @click="
                        <?php if ($product->is_type('simple')) : ?>
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
                                const cartCount = document.querySelector('.ganjeh-cart-count');
                                if (cartCount) {
                                    cartCount.textContent = data.data.cart_count;
                                    cartCount.style.display = data.data.cart_count > 0 ? 'flex' : 'none';
                                }
                                if (window.showCartToast) window.showCartToast({ message: data.data.message || 'به سبد خرید اضافه شد' });
                            } else {
                                alert(data.data?.message || 'خطا در افزودن به سبد');
                            }
                        })
                        .catch(() => { loading = false; alert('خطا در ارتباط با سرور'); });
                        <?php else : ?>
                        window.location.href = '<?php echo esc_url($product_link); ?>';
                        <?php endif; ?>
                    "
                >
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" x-show="!loading">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
                    </svg>
                    <svg class="btn-spinner" viewBox="0 0 24 24" x-show="loading" x-cloak>
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </button>
            <?php endif; ?>

            <div class="price-wrapper">
                <?php if ($is_in_stock) : ?>
                    <?php if ($product->is_type('variable')) :
                        $min_price = $product->get_variation_price('min');
                    ?>
                        <span class="price-from"><?php _e('از', 'ganjeh'); ?></span>
                        <span class="price-amount"><?php echo number_format($min_price); ?></span>
                    <?php elseif ($product->is_on_sale()) :
                        $sale_price = $product->get_sale_price();
                    ?>
                        <span class="price-amount"><?php echo number_format($sale_price); ?></span>
                        <span class="price-currency"><?php _e('تومان', 'ganjeh'); ?></span>
                    <?php else :
                        $price = $product->get_price();
                    ?>
                        <span class="price-amount"><?php echo number_format($price); ?></span>
                        <span class="price-currency"><?php _e('تومان', 'ganjeh'); ?></span>
                    <?php endif; ?>
                <?php else : ?>
                    <span class="out-of-stock-text"><?php _e('ناموجود', 'ganjeh'); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</article>

<style>
.product-card-compact {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    width: 130px;
    height: 100%;
}

.product-image-wrapper {
    position: relative;
    aspect-ratio: 1;
    background: #f9fafb;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.product-image-wrapper .product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-placeholder {
    width: 70%;
    height: 70%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-placeholder svg {
    width: 100%;
    height: 100%;
}

.discount-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 8px;
}

.out-of-stock-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.out-of-stock-overlay span {
    background: white;
    color: #374151;
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 8px;
    font-weight: 500;
}

.product-info {
    padding: 10px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.product-title {
    font-size: 12px;
    font-weight: 500;
    color: #374151;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-decoration: none;
    min-height: 34px;
    margin-bottom: 8px;
}

.product-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: auto;
}

.add-to-cart-btn {
    width: 32px;
    height: 32px;
    min-width: 32px;
    background: var(--color-primary, #4CB050);
    color: white;
    border: none;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.add-to-cart-btn:hover {
    background: var(--color-primary-dark, #3D9142);
    transform: scale(1.05);
}

.add-to-cart-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.add-to-cart-btn.loading {
    animation: pulse 1s infinite;
}

.btn-icon {
    width: 18px;
    height: 18px;
}

.btn-spinner {
    width: 16px;
    height: 16px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.price-wrapper {
    font-size: 11px;
    font-weight: 700;
    color: var(--color-primary, #4CB050);
    text-align: left;
    direction: ltr;
    flex: 1;
    display: flex;
    align-items: baseline;
    gap: 2px;
    flex-wrap: nowrap;
}

.price-wrapper .price-from {
    font-size: 9px;
    font-weight: 400;
    color: #6b7280;
}

.price-wrapper .price-amount {
    font-weight: 700;
    color: var(--color-primary, #4CB050);
}

.price-wrapper .price-currency {
    font-size: 8px;
    font-weight: 400;
    color: #6b7280;
}

.out-of-stock-text {
    color: #9ca3af;
    font-weight: 500;
}

[x-cloak] {
    display: none !important;
}
</style>
