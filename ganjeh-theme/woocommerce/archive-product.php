<?php
/**
 * Archive Product Template - Shop Page
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

get_header();

// Get current category
$current_cat = get_queried_object();
$is_category = is_product_category();
$is_search = is_search() || !empty($_GET['product_search']);
$search_query = !empty($_GET['product_search']) ? sanitize_text_field($_GET['product_search']) : get_search_query();
$page_title = $is_search ? sprintf(__('جستجو: %s', 'ganjeh'), $search_query) : ($is_category ? $current_cat->name : __('فروشگاه', 'ganjeh'));

// Get categories for filter
$product_categories = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'parent'     => 0,
]);
?>

<div class="shop-page">
    <!-- Header -->
    <header class="shop-header">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="back-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <h1><?php echo esc_html($page_title); ?></h1>
        <?php if (!$is_search) : ?>
        <button type="button" class="filter-btn" onclick="document.getElementById('filtersModal').classList.add('open')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
        </button>
        <?php else : ?>
        <div style="width:40px;"></div>
        <?php endif; ?>
    </header>

    <!-- Categories Tabs -->
    <?php if (!$is_search && $product_categories && !is_wp_error($product_categories)) : ?>
    <div class="categories-tabs">
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="cat-tab <?php echo !$is_category ? 'active' : ''; ?>">
            <?php _e('همه', 'ganjeh'); ?>
        </a>
        <?php foreach ($product_categories as $cat) : ?>
        <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="cat-tab <?php echo ($is_category && $current_cat->term_id === $cat->term_id) ? 'active' : ''; ?>">
            <?php echo esc_html($cat->name); ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php
    $current_stock = isset($_GET['stock_filter']) ? sanitize_text_field($_GET['stock_filter']) : 'instock';
    $is_outofstock_page = ($current_stock === 'outofstock');
    ?>

    <!-- Products Grid -->
    <?php if (woocommerce_product_loop()) : ?>
    <div class="products-grid">
        <?php
        while (have_posts()) {
            the_post();
            global $product;
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

    <?php if ($is_search) : ?>
    <!-- Search Pagination -->
    <div class="shop-pagination">
        <?php
        the_posts_pagination([
            'prev_text' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>',
            'next_text' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>',
        ]);
        ?>
    </div>
    <?php else : ?>
    <!-- Stock Pagination (Page 1 = In-stock, Page 2 = Out-of-stock) -->
    <div class="shop-pagination">
        <?php
        $base_url = remove_query_arg('stock_filter');
        $page1_url = add_query_arg('stock_filter', 'instock', $base_url);
        $page2_url = add_query_arg('stock_filter', 'outofstock', $base_url);
        ?>
        <?php if ($is_outofstock_page) : ?>
            <a href="<?php echo esc_url($page1_url); ?>" class="page-numbers">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        <?php endif; ?>
        <a href="<?php echo esc_url($page1_url); ?>" class="page-numbers <?php echo !$is_outofstock_page ? 'current' : ''; ?>">1</a>
        <a href="<?php echo esc_url($page2_url); ?>" class="page-numbers <?php echo $is_outofstock_page ? 'current' : ''; ?>">2</a>
        <?php if (!$is_outofstock_page) : ?>
            <a href="<?php echo esc_url($page2_url); ?>" class="page-numbers">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else : ?>
    <!-- No Products -->
    <div class="empty-products">
        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <h2><?php _e('محصولی یافت نشد', 'ganjeh'); ?></h2>
        <p><?php echo $is_search ? __('نتیجه‌ای برای جستجوی شما یافت نشد.', 'ganjeh') : __('در این دسته‌بندی محصولی وجود ندارد.', 'ganjeh'); ?></p>
    </div>
    <?php endif; ?>

    <!-- Filters Modal -->
    <div id="filtersModal" class="filters-modal" onclick="if(event.target===this) this.classList.remove('open')">
        <div class="filters-content">
            <div class="filters-header">
                <h3><?php _e('فیلتر و مرتب‌سازی', 'ganjeh'); ?></h3>
                <button type="button" onclick="document.getElementById('filtersModal').classList.remove('open')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="filters-body">
                <!-- Sort -->
                <div class="filter-section">
                    <button type="button" class="filter-accordion" onclick="this.parentElement.classList.toggle('open')">
                        <h4><?php _e('مرتب‌سازی', 'ganjeh'); ?></h4>
                        <svg class="accordion-arrow" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="filter-section-content">
                    <?php
                    $orderby_options = [
                        'menu_order' => __('پیش‌فرض', 'ganjeh'),
                        'date'       => __('جدیدترین', 'ganjeh'),
                        'popularity' => __('پرفروش‌ترین', 'ganjeh'),
                        'price'      => __('ارزان‌ترین', 'ganjeh'),
                        'price-desc' => __('گران‌ترین', 'ganjeh'),
                    ];
                    $current_orderby = isset($_GET['orderby']) ? wc_clean($_GET['orderby']) : 'menu_order';
                    foreach ($orderby_options as $value => $label) :
                    ?>
                    <label class="filter-option">
                        <input type="radio" name="orderby" value="<?php echo esc_attr($value); ?>" <?php checked($current_orderby, $value); ?>
                            onchange="window.location.href='<?php echo esc_url(add_query_arg('orderby', '')); ?>'.replace('orderby=', 'orderby=' + this.value)">
                        <span class="radio-mark"></span>
                        <span><?php echo esc_html($label); ?></span>
                    </label>
                    <?php endforeach; ?>
                    </div>
                </div>

                <!-- Categories -->
                <?php if ($product_categories && !is_wp_error($product_categories)) : ?>
                <div class="filter-section">
                    <button type="button" class="filter-accordion" onclick="this.parentElement.classList.toggle('open')">
                        <h4><?php _e('دسته‌بندی‌ها', 'ganjeh'); ?></h4>
                        <svg class="accordion-arrow" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="filter-section-content">
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="filter-cat <?php echo !$is_category ? 'active' : ''; ?>">
                        <span><?php _e('همه محصولات', 'ganjeh'); ?></span>
                        <span class="count"><?php echo wp_count_posts('product')->publish; ?></span>
                    </a>
                    <?php foreach ($product_categories as $cat) : ?>
                    <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="filter-cat <?php echo ($is_category && $current_cat->term_id === $cat->term_id) ? 'active' : ''; ?>">
                        <span><?php echo esc_html($cat->name); ?></span>
                        <span class="count"><?php echo $cat->count; ?></span>
                    </a>
                    <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.shop-page {
    min-height: 100vh;
    background: #f9fafb;
    padding-bottom: 80px;
}

/* Header */
.shop-header {
    position: sticky;
    top: 0;
    z-index: 40;
    background: white;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f3f4f6;
}

.shop-header h1 {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.back-btn, .filter-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #374151;
    background: none;
    border: none;
    cursor: pointer;
}

/* Categories Tabs */
.categories-tabs {
    display: flex;
    gap: 8px;
    padding: 12px 16px;
    background: white;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.categories-tabs::-webkit-scrollbar {
    display: none;
}

.cat-tab {
    flex-shrink: 0;
    padding: 8px 16px;
    background: #f3f4f6;
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
    border-radius: 20px;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.2s;
}

.cat-tab.active {
    background: #4CB050;
    color: white;
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    padding: 16px;
}

/* Product Card */
.product-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.product-image {
    position: relative;
    aspect-ratio: 1;
    background: #f9fafb;
    display: block;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-image .no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d1d5db;
}

.product-image .no-image svg {
    width: 40%;
    height: 40%;
}

.badge-discount {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #ef4444;
    color: white;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 8px;
}

.overlay-stock {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.overlay-stock span {
    background: white;
    color: #374151;
    font-size: 12px;
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 500;
}

.product-info {
    padding: 12px;
}

.product-name {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    line-height: 1.5;
    height: 40px;
    overflow: hidden;
    text-decoration: none;
    margin-bottom: 10px;
}

.product-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.btn-add {
    width: 36px;
    height: 36px;
    min-width: 36px;
    background: #4CB050;
    color: white;
    border: none;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
}

.btn-add:hover {
    background: #3d9142;
}

.btn-add.loading {
    opacity: 0.7;
}

.btn-add .btn-spinner {
    animation: spin 1s linear infinite;
}

.product-price {
    flex: 1;
    text-align: left;
    font-size: 13px;
    font-weight: 700;
    color: #4CB050;
    direction: ltr;
}

.product-price del {
    color: #9ca3af;
    font-size: 11px;
    font-weight: 400;
    display: block;
}

.product-price ins {
    text-decoration: none;
}

.text-gray {
    color: #9ca3af;
}

/* Pagination */
.shop-pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    padding: 20px 16px;
}

.shop-pagination .page-numbers {
    min-width: 36px;
    height: 36px;
    padding: 0 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    color: #6b7280;
    font-size: 14px;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
    border: 1px solid #e5e7eb;
}

.shop-pagination .page-numbers.current {
    background: #4CB050;
    color: white;
    border-color: #4CB050;
}

.shop-pagination .nav-links {
    display: flex;
    gap: 8px;
}

/* Empty State */
.empty-products {
    text-align: center;
    padding: 60px 20px;
    color: #9ca3af;
}

.empty-products svg {
    margin: 0 auto 16px;
}

.empty-products h2 {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 8px;
}

.empty-products p {
    font-size: 14px;
    margin: 0;
}

/* Filters Modal */
.filters-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 100;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
}

.filters-modal.open {
    opacity: 1;
    visibility: visible;
}

.filters-content {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%) translateY(100%);
    width: 100%;
    max-width: 515px;
    max-height: 80vh;
    background: white;
    border-radius: 20px 20px 0 0;
    transition: transform 0.3s;
}

.filters-modal.open .filters-content {
    transform: translateX(-50%) translateY(0);
}

.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #f3f4f6;
}

.filters-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
}

.filters-header button {
    padding: 6px;
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    color: #6b7280;
    cursor: pointer;
}

.filters-body {
    padding: 16px 20px;
    max-height: 60vh;
    overflow-y: auto;
}

.filter-section {
    border-bottom: 1px solid #f3f4f6;
}

.filter-section:last-child {
    border-bottom: none;
}

.filter-accordion {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 0;
    background: none;
    border: none;
    cursor: pointer;
}

.filter-accordion h4 {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.accordion-arrow {
    color: #9ca3af;
    transition: transform 0.3s;
}

.filter-section.open .accordion-arrow {
    transform: rotate(180deg);
}

.filter-section-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.filter-section.open .filter-section-content {
    max-height: 500px;
    padding-bottom: 16px;
}

.filter-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    background: #f9fafb;
    border-radius: 10px;
    margin-bottom: 8px;
    cursor: pointer;
}

.filter-option input {
    display: none;
}

.radio-mark {
    width: 20px;
    height: 20px;
    border: 2px solid #d1d5db;
    border-radius: 50%;
    position: relative;
}

.filter-option input:checked + .radio-mark {
    border-color: #4CB050;
}

.filter-option input:checked + .radio-mark::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 10px;
    height: 10px;
    background: #4CB050;
    border-radius: 50%;
}

.filter-cat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #f9fafb;
    border-radius: 10px;
    margin-bottom: 8px;
    text-decoration: none;
    color: #374151;
    font-size: 14px;
}

.filter-cat.active {
    background: #f0fdf4;
    color: #166534;
}

.filter-cat .count {
    font-size: 12px;
    color: #9ca3af;
}

.filter-cat.active .count {
    color: #4CB050;
}

/* Animation */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

[x-cloak] { display: none !important; }
</style>

<?php get_footer(); ?>
