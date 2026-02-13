<?php
/**
 * Search Results Template
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

// If searching for products, use the shop-style layout
$post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
$search_query = get_search_query();

get_header();
?>

<div class="shop-page">
    <!-- Header -->
    <header class="shop-header">
        <a href="javascript:history.back()" class="back-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <h1><?php printf(__('جستجو: %s', 'ganjeh'), esc_html($search_query)); ?></h1>
        <div style="width:40px;"></div>
    </header>

    <!-- Products Grid -->
    <?php if (have_posts()) : ?>
    <div class="products-grid">
        <?php
        while (have_posts()) {
            the_post();

            // Only show products
            if (get_post_type() !== 'product') continue;

            $product = wc_get_product(get_the_ID());
            if (!$product || !is_a($product, 'WC_Product')) continue;

            $product_id = $product->get_id();
            $product_name = $product->get_name();
            $product_price = $product->get_price_html();
            $product_link = $product->get_permalink();
            $is_on_sale = $product->is_on_sale();
            $is_in_stock = $product->is_in_stock();

            // Discount
            $discount_percent = 0;
            if ($is_on_sale && $product->is_type('simple')) {
                $regular_price = (float) $product->get_regular_price();
                $sale_price = (float) $product->get_sale_price();
                if ($regular_price > 0) {
                    $discount_percent = round((($regular_price - $sale_price) / $regular_price) * 100);
                }
            }
            ?>
            <article class="product-card">
                <a href="<?php echo esc_url($product_link); ?>" class="product-image">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php echo $product->get_image('woocommerce_thumbnail', ['loading' => 'lazy']); ?>
                    <?php else : ?>
                        <div class="no-image">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="1.5"/>
                                <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/>
                                <path d="M21 15l-5-5L5 21" stroke-width="1.5"/>
                            </svg>
                        </div>
                    <?php endif; ?>

                    <?php if ($discount_percent > 0) : ?>
                        <span class="badge-discount"><?php echo $discount_percent; ?>%</span>
                    <?php endif; ?>

                    <?php if (!$is_in_stock) : ?>
                        <div class="overlay-stock">
                            <span><?php _e('ناموجود', 'ganjeh'); ?></span>
                        </div>
                    <?php endif; ?>
                </a>

                <div class="product-info">
                    <a href="<?php echo esc_url($product_link); ?>" class="product-name">
                        <?php echo esc_html($product_name); ?>
                    </a>

                    <div class="product-bottom">
                        <?php if ($is_in_stock && $product->is_type('simple')) : ?>
                        <button type="button" class="btn-add" data-product-id="<?php echo $product_id; ?>" onclick="window.ganjehAddToCart(this, <?php echo $product_id; ?>)">
                            <svg class="btn-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v12m6-6H6"/>
                            </svg>
                            <svg class="btn-spinner w-4 h-4" viewBox="0 0 24 24" style="display:none;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </button>
                        <?php elseif ($is_in_stock && $product->is_type('variable')) : ?>
                        <a href="<?php echo esc_url($product_link); ?>" class="btn-add">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <?php endif; ?>

                        <div class="product-price">
                            <?php if ($is_in_stock) : ?>
                                <?php echo $product_price; ?>
                            <?php else : ?>
                                <span class="text-gray"><?php _e('ناموجود', 'ganjeh'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </article>
        <?php } ?>
    </div>

    <!-- Pagination -->
    <div class="shop-pagination">
        <?php
        the_posts_pagination([
            'prev_text' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>',
            'next_text' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>',
        ]);
        ?>
    </div>

    <?php else : ?>
    <!-- No Results -->
    <div class="empty-products">
        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <h2><?php _e('محصولی یافت نشد', 'ganjeh'); ?></h2>
        <p><?php printf(__('نتیجه‌ای برای «%s» یافت نشد.', 'ganjeh'), esc_html($search_query)); ?></p>
    </div>
    <?php endif; ?>
</div>

<style>
.shop-page { min-height: 100vh; background: #f9fafb; padding-bottom: 80px; }
.shop-header { position: sticky; top: 0; z-index: 40; background: white; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f3f4f6; }
.shop-header h1 { font-size: 16px; font-weight: 700; color: #1f2937; margin: 0; flex: 1; text-align: center; }
.back-btn { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; color: #374151; background: none; border: none; cursor: pointer; text-decoration: none; }
.products-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; padding: 16px; }
.product-card { background: white; border-radius: 16px; overflow: hidden; border: 1px solid #e5e7eb; }
.product-image { position: relative; aspect-ratio: 1; background: #f9fafb; display: block; }
.product-image img { width: 100%; height: 100%; object-fit: cover; }
.product-image .no-image { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #d1d5db; }
.product-image .no-image svg { width: 40%; height: 40%; }
.badge-discount { position: absolute; top: 8px; right: 8px; background: #ef4444; color: white; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 8px; }
.overlay-stock { position: absolute; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; }
.overlay-stock span { background: white; color: #374151; font-size: 12px; padding: 6px 12px; border-radius: 8px; font-weight: 500; }
.product-info { padding: 12px; }
.product-name { display: block; font-size: 13px; font-weight: 500; color: #374151; line-height: 1.5; height: 40px; overflow: hidden; text-decoration: none; margin-bottom: 10px; }
.product-bottom { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.btn-add { width: 36px; height: 36px; min-width: 36px; background: #4CB050; color: white; border: none; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; }
.btn-add:hover { background: #3d9142; }
.btn-add.loading { opacity: 0.7; }
.btn-add .btn-spinner { animation: spin 1s linear infinite; }
.product-price { flex: 1; text-align: left; font-size: 13px; font-weight: 700; color: #4CB050; direction: ltr; }
.product-price del { color: #9ca3af; font-size: 11px; font-weight: 400; display: block; }
.product-price ins { text-decoration: none; }
.text-gray { color: #9ca3af; }
.empty-products { text-align: center; padding: 60px 20px; color: #9ca3af; }
.empty-products svg { margin: 0 auto 16px; }
.empty-products h2 { font-size: 16px; font-weight: 600; color: #374151; margin: 0 0 8px; }
.empty-products p { font-size: 14px; margin: 0; }
.shop-pagination { display: flex; justify-content: center; gap: 8px; padding: 20px 16px; }
.shop-pagination .page-numbers { min-width: 36px; height: 36px; padding: 0 14px; display: flex; align-items: center; justify-content: center; background: white; color: #6b7280; font-size: 14px; font-weight: 600; border-radius: 10px; text-decoration: none; border: 1px solid #e5e7eb; }
.shop-pagination .page-numbers.current { background: #4CB050; color: white; border-color: #4CB050; }
.shop-pagination .nav-links { display: flex; gap: 8px; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

<?php get_footer(); ?>
