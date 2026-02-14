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

<main id="main-content" class="pb-20">

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
        </div>
        <?php if ($term->description) : ?>
            <p class="category-description"><?php echo wp_kses_post($term->description); ?></p>
        <?php endif; ?>
    </div>

    <!-- Subcategories -->
    <?php if ($has_subcategories) : ?>
    <div class="subcategories-tabs">
        <?php foreach ($subcategories as $subcat) :
            $thumbnail_id = get_term_meta($subcat->term_id, 'thumbnail_id', true);
            $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '';
        ?>
        <a href="<?php echo get_term_link($subcat); ?>" class="subcat-tab">
            <span class="subcat-tab-icon">
                <?php if ($image_url) : ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($subcat->name); ?>">
                <?php else : ?>
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <?php endif; ?>
            </span>
            <span class="subcat-tab-name"><?php echo esc_html($subcat->name); ?></span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Inline Filters -->
    <?php
    $wc_attributes = function_exists('wc_get_attribute_taxonomies') ? wc_get_attribute_taxonomies() : [];
    $current_orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'menu_order';
    $has_any_active_filter = false;

    $attribute_filters = [];

    // Find brand taxonomy dynamically (plugin-based or WC attribute)
    $brand_taxonomy = '';
    $brand_label = 'برند';
    $product_taxonomies = get_object_taxonomies('product', 'objects');
    foreach ($product_taxonomies as $tax_slug => $tax_obj) {
        // Check common brand taxonomy slugs from plugins
        if (in_array($tax_slug, ['pwb-brand', 'product_brand', 'brand', 'yith_product_brand'])) {
            $brand_taxonomy = $tax_slug;
            $brand_label = $tax_obj->labels->singular_name ?: 'برند';
            break;
        }
        // Check by label
        if (in_array($tax_obj->label, ['برندها', 'Brands', 'Brand']) ||
            in_array($tax_obj->labels->singular_name ?? '', ['برند', 'Brand'])) {
            $brand_taxonomy = $tax_slug;
            $brand_label = $tax_obj->labels->singular_name ?: 'برند';
            break;
        }
    }
    // Fallback: check WooCommerce attributes for brand
    if (!$brand_taxonomy && $wc_attributes) {
        foreach ($wc_attributes as $attribute) {
            if ($attribute->attribute_label === 'برند') {
                $brand_taxonomy = 'pa_' . $attribute->attribute_name;
                $brand_label = 'برند';
                break;
            }
        }
    }

    // Get brand terms for products in this category
    $all_cat_ids = array_merge([$term_id], get_term_children($term_id, 'product_cat'));
    global $wpdb;
    $cat_ids_str = implode(',', array_map('intval', $all_cat_ids));

    if ($brand_taxonomy) {
        $brand_terms = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT t.term_id, t.name, t.slug
            FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
            INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
            WHERE tt.taxonomy = %s
            AND tr.object_id IN (
                SELECT DISTINCT tr2.object_id
                FROM {$wpdb->term_relationships} tr2
                INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                INNER JOIN {$wpdb->posts} p ON tr2.object_id = p.ID
                WHERE tt2.taxonomy = 'product_cat'
                AND tt2.term_id IN ({$cat_ids_str})
                AND p.post_type IN ('product', 'product_variation')
                AND p.post_status = 'publish'
            )
            ORDER BY t.name ASC
        ", $brand_taxonomy));

        if (!empty($brand_terms)) {
            $param_name = 'filter_brand';
            $active_terms = !empty($_GET[$param_name]) ? array_map('intval', explode(',', $_GET[$param_name])) : [];
            if (!empty($active_terms)) $has_any_active_filter = true;
            $attribute_filters[] = [
                'label'        => $brand_label,
                'param_name'   => $param_name,
                'taxonomy'     => $brand_taxonomy,
                'terms'        => $brand_terms,
                'active_terms' => $active_terms,
            ];
        }
    }
    ?>
    <div class="inline-filters">
        <div class="filter-chips">
            <button type="button" class="filter-chip" onclick="toggleFilterPanel('sort')" id="chip-sort">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                <?php _e('مرتب‌سازی', 'ganjeh'); ?>
                <svg class="chip-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <?php foreach ($attribute_filters as $af) : ?>
            <button type="button" class="filter-chip <?php echo !empty($af['active_terms']) ? 'has-filter' : ''; ?>" onclick="toggleFilterPanel('<?php echo esc_js($af['param_name']); ?>')" id="chip-<?php echo esc_attr($af['param_name']); ?>">
                <?php echo esc_html($af['label']); ?>
                <?php if (!empty($af['active_terms'])) : ?><span class="chip-badge"><?php echo count($af['active_terms']); ?></span><?php endif; ?>
                <svg class="chip-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <?php endforeach; ?>

            <?php if ($has_any_active_filter) : ?>
            <a href="<?php echo esc_url(get_term_link($term) . (isset($_GET['orderby']) ? '?orderby=' . esc_attr($_GET['orderby']) : '')); ?>" class="filter-chip clear-chip">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                <?php _e('حذف فیلتر', 'ganjeh'); ?>
            </a>
            <?php endif; ?>
        </div>

        <!-- Sort Panel -->
        <div class="filter-panel-dropdown" id="panel-sort">
            <?php
            $orderby_options = [
                'menu_order' => __('پیش‌فرض', 'ganjeh'),
                'date'       => __('جدیدترین', 'ganjeh'),
                'popularity' => __('پرفروش‌ترین', 'ganjeh'),
                'price'      => __('ارزان‌ترین', 'ganjeh'),
                'price-desc' => __('گران‌ترین', 'ganjeh'),
            ];
            foreach ($orderby_options as $value => $label) :
            ?>
            <label class="filter-option">
                <input type="radio" name="orderby" value="<?php echo esc_attr($value); ?>" <?php checked($current_orderby, $value); ?>
                    onchange="window.location.href='<?php echo esc_url(add_query_arg('orderby', '', get_term_link($term))); ?>'.replace('orderby=', 'orderby=' + this.value)">
                <span class="radio-mark"></span>
                <span><?php echo esc_html($label); ?></span>
            </label>
            <?php endforeach; ?>
        </div>

        <!-- Attribute Panels -->
        <?php foreach ($attribute_filters as $af) : ?>
        <div class="filter-panel-dropdown" id="panel-<?php echo esc_attr($af['param_name']); ?>">
            <?php if ($af['param_name'] === 'filter_brand' && !empty($af['taxonomy'])) : ?>
            <input type="hidden" name="brand_tax" value="<?php echo esc_attr($af['taxonomy']); ?>">
            <?php endif; ?>
            <?php foreach ($af['terms'] as $attr_term) : ?>
            <label class="filter-option">
                <input type="checkbox" name="<?php echo esc_attr($af['param_name']); ?>" value="<?php echo esc_attr($attr_term->term_id); ?>"
                    <?php checked(in_array((int)$attr_term->term_id, $af['active_terms'])); ?>
                    onchange="applyFilter('<?php echo esc_js($af['param_name']); ?>')">
                <span class="check-mark"></span>
                <span><?php echo esc_html($attr_term->name); ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
    function toggleFilterPanel(id) {
        var panels = document.querySelectorAll('.filter-panel-dropdown');
        var chips = document.querySelectorAll('.filter-chip');
        var targetPanel = document.getElementById('panel-' + id);
        var targetChip = document.getElementById('chip-' + id);
        var isOpen = targetPanel && targetPanel.classList.contains('open');
        panels.forEach(function(p) { p.classList.remove('open'); });
        chips.forEach(function(c) { c.classList.remove('active'); });
        if (!isOpen && targetPanel) {
            targetPanel.classList.add('open');
            if (targetChip) targetChip.classList.add('active');
        }
    }
    function applyFilter(paramName) {
        var url = new URL(window.location.href);
        var checkboxes = document.querySelectorAll('input[name="' + paramName + '"]:checked');
        var values = [];
        checkboxes.forEach(function(cb) { values.push(cb.value); });
        if (values.length > 0) {
            url.searchParams.set(paramName, values.join(','));
        } else {
            url.searchParams.delete(paramName);
        }
        // Pass brand taxonomy slug so query handler knows exactly which taxonomy to use
        if (paramName === 'filter_brand') {
            var brandTax = document.querySelector('input[name="brand_tax"]');
            if (brandTax && brandTax.value) {
                url.searchParams.set('brand_tax', brandTax.value);
            }
        }
        window.location.href = url.toString();
    }

    // Drag to scroll
    function enableDragScroll(el) {
        var isDown = false, startX, scrollLeft, moved;
        el.addEventListener('dragstart', function(e) { e.preventDefault(); });
        el.addEventListener('mousedown', function(e) {
            isDown = true; moved = false;
            startX = e.pageX - el.offsetLeft;
            scrollLeft = el.scrollLeft;
            el.style.cursor = 'grabbing';
        });
        document.addEventListener('mouseup', function() {
            if (isDown) { isDown = false; el.style.cursor = 'grab'; }
        });
        el.addEventListener('mouseleave', function() { isDown = false; el.style.cursor = 'grab'; });
        el.addEventListener('mousemove', function(e) {
            if (!isDown) return;
            e.preventDefault();
            var x = e.pageX - el.offsetLeft;
            var walk = x - startX;
            if (Math.abs(walk) > 5) moved = true;
            el.scrollLeft = scrollLeft - walk;
        });
        el.addEventListener('click', function(e) {
            if (moved) { e.preventDefault(); e.stopPropagation(); }
        }, true);
        el.style.cursor = 'grab';
    }
    document.querySelectorAll('.subcategories-tabs').forEach(enableDragScroll);
    </script>

    <!-- Products Grid -->
    <?php
    // Apply brand filter directly in template (plugin taxonomy may not work with WP hooks)
    $brand_query = null;
    if (!empty($_GET['filter_brand']) && !empty($_GET['brand_tax'])) {
        $b_tax = sanitize_text_field($_GET['brand_tax']);
        $b_ids = array_filter(array_map('intval', explode(',', $_GET['filter_brand'])));
        if (!empty($b_ids) && taxonomy_exists($b_tax)) {
            $brand_query = new WP_Query([
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'tax_query'      => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'product_cat',
                        'field'    => 'term_id',
                        'terms'    => $all_cat_ids,
                    ],
                    [
                        'taxonomy' => $b_tax,
                        'field'    => 'term_id',
                        'terms'    => $b_ids,
                        'operator' => 'IN',
                    ],
                ],
            ]);
        }
    }
    $has_products = $brand_query ? $brand_query->have_posts() : woocommerce_product_loop();
    ?>
    <?php if ($has_products) : ?>
        <div class="products-grid-section">
            <div class="products-grid">
                <?php
                if ($brand_query) {
                    while ($brand_query->have_posts()) {
                        $brand_query->the_post();
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
                    wp_reset_postdata();
                } else {
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

/* Inline Filters */
.inline-filters {
    background: white;
    border-bottom: 1px solid #f3f4f6;
}
.filter-chips {
    display: flex;
    gap: 8px;
    padding: 10px 16px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.filter-chips::-webkit-scrollbar { display: none; }
.filter-chip {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: #f3f4f6;
    color: #374151;
    font-size: 13px;
    font-weight: 500;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    white-space: nowrap;
    text-decoration: none;
    transition: all 0.2s;
}
.filter-chip svg { color: #9ca3af; }
.filter-chip.active { background: #4CB050; color: white; }
.filter-chip.active svg { color: white; }
.filter-chip.active .chip-arrow { transform: rotate(180deg); }
.filter-chip.has-filter { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.filter-chip.has-filter svg { color: #059669; }
.chip-badge {
    background: #4CB050;
    color: white;
    font-size: 10px;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
}
.chip-arrow { transition: transform 0.3s; }
.clear-chip { background: #fef2f2; color: #ef4444; }
.clear-chip svg { color: #ef4444; }
.filter-panel-dropdown {
    display: none;
    padding: 8px 16px 14px;
    border-top: 1px solid #f3f4f6;
}
.filter-panel-dropdown.open { display: block; }
.filter-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: #f9fafb;
    border-radius: 10px;
    margin-bottom: 6px;
    cursor: pointer;
    font-size: 13px;
}
.filter-option input { display: none; }
.radio-mark {
    width: 18px;
    height: 18px;
    border: 2px solid #d1d5db;
    border-radius: 50%;
    position: relative;
    flex-shrink: 0;
}
.filter-option input:checked + .radio-mark { border-color: #4CB050; }
.filter-option input:checked + .radio-mark::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 9px;
    height: 9px;
    background: #4CB050;
    border-radius: 50%;
}
.check-mark {
    width: 18px;
    height: 18px;
    border: 2px solid #d1d5db;
    border-radius: 5px;
    position: relative;
    flex-shrink: 0;
    transition: all 0.2s;
}
.filter-option input:checked + .check-mark {
    border-color: #4CB050;
    background: #4CB050;
}
.filter-option input:checked + .check-mark::after {
    content: '';
    position: absolute;
    top: 1px;
    left: 5px;
    width: 5px;
    height: 9px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
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
.category-description {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.6;
    margin: 0;
}

/* Subcategories Tabs */
.subcategories-tabs {
    display: flex;
    gap: 10px;
    padding: 12px 16px;
    background: white;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.subcategories-tabs::-webkit-scrollbar { display: none; }
.subcategories-tabs {
    touch-action: pan-x;
    user-select: none;
    -webkit-user-select: none;
}
.subcategories-tabs a,
.subcategories-tabs img {
    -webkit-user-drag: none;
    user-drag: none;
}
.subcategories-tabs img {
    pointer-events: none;
}
.subcat-tab {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 10px 14px;
    background: #f3f4f6;
    color: #6b7280;
    font-size: 12px;
    font-weight: 500;
    border-radius: 14px;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.2s;
    min-width: 70px;
}
.subcat-tab:hover {
    background: #e5e7eb;
}
.subcat-tab-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.subcat-tab-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.subcat-tab-icon svg {
    color: #9ca3af;
}
.subcat-tab-name {
    font-size: 11px;
    font-weight: 600;
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
