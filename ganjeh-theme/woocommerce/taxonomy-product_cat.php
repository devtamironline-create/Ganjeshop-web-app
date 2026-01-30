<?php
/**
 * Product Category Archive Template
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

get_header();

$term = get_queried_object();
$term_id = $term->term_id;

// Get subcategories
$subcategories = get_terms([
    'taxonomy' => 'product_cat',
    'parent' => $term_id,
    'hide_empty' => false,
]);
$has_subcategories = !empty($subcategories) && !is_wp_error($subcategories);

// Get parent categories for breadcrumb
$ancestors = get_ancestors($term_id, 'product_cat', 'taxonomy');
$ancestors = array_reverse($ancestors);
?>

<main id="main-content" class="pb-20" x-data="{ showFilters: false }" @open-filters.window="showFilters = true">

    <!-- Filter Panel -->
    <div x-show="showFilters" x-cloak class="filter-overlay" @click.self="showFilters = false">
        <div class="filter-panel" x-show="showFilters" x-transition:enter="filter-enter" x-transition:leave="filter-leave">
            <div class="filter-header">
                <h3>فیلتر و مرتب‌سازی</h3>
                <button @click="showFilters = false" class="filter-close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="filter-body">
                <div class="filter-section">
                    <h4>مرتب‌سازی</h4>
                    <div class="sort-options">
                        <?php
                        $current_orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date';
                        $sort_options = [
                            'date' => 'جدیدترین',
                            'price' => 'ارزان‌ترین',
                            'price-desc' => 'گران‌ترین',
                            'popularity' => 'پرفروش‌ترین',
                            'rating' => 'بالاترین امتیاز'
                        ];
                        $base_url = remove_query_arg('orderby');
                        foreach ($sort_options as $value => $label) :
                            $is_active = $current_orderby === $value;
                            $url = add_query_arg('orderby', $value, $base_url);
                        ?>
                            <a href="<?php echo esc_url($url); ?>" class="sort-option <?php echo $is_active ? 'active' : ''; ?>">
                                <?php echo esc_html($label); ?>
                                <?php if ($is_active) : ?>
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav class="category-breadcrumb">
        <a href="<?php echo home_url('/'); ?>">خانه</a>
        <span class="sep">‹</span>
        <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>">فروشگاه</a>
        <?php foreach ($ancestors as $ancestor_id) :
            $ancestor = get_term($ancestor_id, 'product_cat');
        ?>
            <span class="sep">‹</span>
            <a href="<?php echo get_term_link($ancestor); ?>"><?php echo esc_html($ancestor->name); ?></a>
        <?php endforeach; ?>
        <span class="sep">‹</span>
        <span class="current"><?php echo esc_html($term->name); ?></span>
    </nav>

    <!-- Category Slider -->
    <?php ganjeh_render_category_slider($term_id); ?>

    <!-- Category Header -->
    <div class="category-header">
        <div class="category-title-row">
            <h1 class="category-title"><?php echo esc_html($term->name); ?></h1>
            <button type="button" class="filter-btn" @click="$dispatch('open-filters')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
            </button>
        </div>
        <?php if ($term->description) : ?>
            <p class="category-description"><?php echo wp_kses_post($term->description); ?></p>
        <?php endif; ?>
    </div>

    <!-- Subcategories Carousel -->
    <?php if ($has_subcategories) : ?>
    <section class="subcategories-section">
        <div class="swiper subcategories-swiper" dir="rtl">
            <div class="swiper-wrapper">
                <?php foreach ($subcategories as $subcat) :
                    $thumbnail_id = get_term_meta($subcat->term_id, 'thumbnail_id', true);
                    $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '';
                ?>
                    <div class="swiper-slide">
                        <a href="<?php echo get_term_link($subcat); ?>" class="subcat-card">
                            <div class="subcat-image">
                                <?php if ($image_url) : ?>
                                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($subcat->name); ?>">
                                <?php else : ?>
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"/>
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <span class="subcat-name"><?php echo esc_html($subcat->name); ?></span>
                            <span class="subcat-count"><?php echo $subcat->count; ?> محصول</span>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Products Grid -->
    <?php if (woocommerce_product_loop()) : ?>
        <div class="products-grid-section">
            <div class="products-grid">
                <?php
                while (have_posts()) {
                    the_post();
                    global $product;
                    ?>
                    <div class="product-grid-item">
                        <?php
                        $GLOBALS['product'] = wc_get_product(get_the_ID());
                        get_template_part('template-parts/components/product-card-grid');
                        ?>
                    </div>
                    <?php
                }
                ?>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                <?php
                $total_pages = wc_get_loop_prop('total_pages');
                if ($total_pages > 1) :
                    $current_page = max(1, get_query_var('paged'));
                ?>
                    <div class="ganjeh-pagination">
                        <?php if ($current_page > 1) : ?>
                            <a href="<?php echo get_pagenum_link($current_page - 1); ?>" class="page-link prev">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <?php if ($i == $current_page) : ?>
                                <span class="page-link current"><?php echo $i; ?></span>
                            <?php elseif ($i == 1 || $i == $total_pages || abs($i - $current_page) <= 1) : ?>
                                <a href="<?php echo get_pagenum_link($i); ?>" class="page-link"><?php echo $i; ?></a>
                            <?php elseif (abs($i - $current_page) == 2) : ?>
                                <span class="page-dots">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages) : ?>
                            <a href="<?php echo get_pagenum_link($current_page + 1); ?>" class="page-link next">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else : ?>
        <div class="empty-products">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-gray-500 text-center">محصولی در این دسته‌بندی یافت نشد</p>
        </div>
    <?php endif; ?>

</main>

<style>
/* Breadcrumb */
.category-breadcrumb {
    padding: 12px 16px;
    font-size: 12px;
    color: #6b7280;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    background: #f9fafb;
}
.category-breadcrumb a {
    color: #6b7280;
    text-decoration: none;
}
.category-breadcrumb a:hover {
    color: var(--color-primary, #4CB050);
}
.category-breadcrumb .sep {
    color: #d1d5db;
}
.category-breadcrumb .current {
    color: #374151;
    font-weight: 500;
}

/* Filter Panel */
.filter-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}
.filter-panel {
    background: white;
    border-radius: 20px 20px 0 0;
    width: 100%;
    max-width: 515px;
    max-height: 70vh;
    overflow: hidden;
}
.filter-enter {
    transition: transform 0.3s ease-out;
}
.filter-leave {
    transition: transform 0.2s ease-in;
}
.filter-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
}
.filter-header h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}
.filter-close {
    width: 32px;
    height: 32px;
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    cursor: pointer;
}
.filter-body {
    padding: 16px 20px;
    overflow-y: auto;
}
.filter-section h4 {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 12px;
}
.sort-options {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.sort-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: #f9fafb;
    border-radius: 12px;
    font-size: 14px;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s;
}
.sort-option:hover {
    background: #f3f4f6;
}
.sort-option.active {
    background: rgba(76, 176, 80, 0.1);
    color: var(--color-primary, #4CB050);
    font-weight: 500;
}
[x-cloak] { display: none !important; }

/* Category Header */
.category-header {
    padding: 12px 16px;
}
.category-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.category-title {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}
.filter-btn {
    width: 40px;
    height: 40px;
    background: #f3f4f6;
    border: none;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    cursor: pointer;
}
.filter-btn:hover {
    background: #e5e7eb;
}
.category-description {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.6;
    margin: 0;
}

/* Subcategories */
.subcategories-section {
    padding: 0 0 16px;
    overflow: hidden;
}
.subcategories-swiper {
    padding: 0 16px;
}
.subcategories-swiper .swiper-slide {
    width: auto;
}
.subcat-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 12px;
    background: #f9fafb;
    border-radius: 16px;
    text-decoration: none;
    min-width: 90px;
    transition: all 0.2s;
}
.subcat-card:hover {
    background: #f3f4f6;
}
.subcat-image {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 8px;
}
.subcat-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.subcat-name {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    text-align: center;
    margin-bottom: 2px;
}
.subcat-count {
    font-size: 10px;
    color: #9ca3af;
}

/* Products Grid */
.products-grid-section {
    padding: 0 16px;
}
.products-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
.product-grid-item {
    width: 100%;
}

/* Pagination */
.pagination-wrapper {
    padding: 20px 0;
}
.ganjeh-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.page-link {
    min-width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s;
}
.page-link:hover {
    background: #e5e7eb;
}
.page-link.current {
    background: var(--color-primary, #4CB050);
    color: white;
}
.page-link.prev,
.page-link.next {
    color: #6b7280;
}
.page-dots {
    color: #9ca3af;
    font-size: 13px;
}

/* Empty State */
.empty-products {
    padding: 60px 16px;
    text-align: center;
}
</style>

<?php
get_footer();
