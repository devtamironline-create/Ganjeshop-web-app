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
        <div style="width:40px;"></div>
    </header>

    <!-- Categories Tabs -->
    <?php if (!$is_search && $product_categories && !is_wp_error($product_categories)) : ?>
    <div class="categories-tabs">
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="cat-tab <?php echo !$is_category ? 'active' : ''; ?>">
            <span class="cat-tab-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            </span>
            <span class="cat-tab-name"><?php _e('همه', 'ganjeh'); ?></span>
        </a>
        <?php foreach ($product_categories as $cat) :
            $thumb_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
            $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'thumbnail') : '';
        ?>
        <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="cat-tab <?php echo ($is_category && $current_cat->term_id === $cat->term_id) ? 'active' : ''; ?>">
            <span class="cat-tab-icon">
                <?php if ($thumb_url) : ?>
                    <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($cat->name); ?>">
                <?php else : ?>
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <?php endif; ?>
            </span>
            <span class="cat-tab-name"><?php echo esc_html($cat->name); ?></span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Inline Filters -->
    <?php if (!$is_search) : ?>
    <?php
    // Get WooCommerce product attributes for filters
    $wc_attributes = function_exists('wc_get_attribute_taxonomies') ? wc_get_attribute_taxonomies() : [];
    // Get categories for filter (only on shop page, not on category pages)
    $filter_categories = [];
    if (!$is_category) {
        $filter_categories = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
        ]);
        if (is_wp_error($filter_categories)) $filter_categories = [];
    }
    // Check active filters
    $active_cats = !empty($_GET['filter_cat']) ? array_map('sanitize_text_field', explode(',', $_GET['filter_cat'])) : [];
    $has_any_active_filter = !empty($active_cats);
    $current_orderby = isset($_GET['orderby']) ? wc_clean($_GET['orderby']) : 'menu_order';

    // Build brand filter data
    $attribute_filters = [];

    // Find brand taxonomy dynamically (plugin-based or WC attribute)
    $brand_taxonomy = '';
    $brand_label = 'برند';
    $product_taxonomies = get_object_taxonomies('product', 'objects');
    foreach ($product_taxonomies as $tax_slug => $tax_obj) {
        if (in_array($tax_slug, ['pwb-brand', 'product_brand', 'brand', 'yith_product_brand'])) {
            $brand_taxonomy = $tax_slug;
            $brand_label = $tax_obj->labels->singular_name ?: 'برند';
            break;
        }
        if (in_array($tax_obj->label, ['برندها', 'Brands', 'Brand']) ||
            in_array($tax_obj->labels->singular_name ?? '', ['برند', 'Brand'])) {
            $brand_taxonomy = $tax_slug;
            $brand_label = $tax_obj->labels->singular_name ?: 'برند';
            break;
        }
    }
    if (!$brand_taxonomy && $wc_attributes) {
        foreach ($wc_attributes as $attribute) {
            if ($attribute->attribute_label === 'برند') {
                $brand_taxonomy = 'pa_' . $attribute->attribute_name;
                $brand_label = 'برند';
                break;
            }
        }
    }

    // Get brand terms (context-aware via SQL)
    global $wpdb;
    if ($brand_taxonomy) {
        $cat_filter_sql = '';
        if ($is_category && $current_cat) {
            $all_cat_ids = array_merge([$current_cat->term_id], get_term_children($current_cat->term_id, 'product_cat'));
            $cat_ids_str = implode(',', array_map('intval', $all_cat_ids));
            $cat_filter_sql = "AND tr.object_id IN (
                SELECT DISTINCT tr2.object_id FROM {$wpdb->term_relationships} tr2
                INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                INNER JOIN {$wpdb->posts} p ON tr2.object_id = p.ID
                WHERE tt2.taxonomy = 'product_cat' AND tt2.term_id IN ({$cat_ids_str})
                AND p.post_type IN ('product','product_variation') AND p.post_status = 'publish'
            )";
        } elseif (!empty($active_cats)) {
            $cat_slugs_str = implode("','", array_map('esc_sql', $active_cats));
            $cat_filter_sql = "AND tr.object_id IN (
                SELECT DISTINCT tr2.object_id FROM {$wpdb->term_relationships} tr2
                INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                INNER JOIN {$wpdb->terms} t2 ON tt2.term_id = t2.term_id
                INNER JOIN {$wpdb->posts} p ON tr2.object_id = p.ID
                WHERE tt2.taxonomy = 'product_cat' AND t2.slug IN ('{$cat_slugs_str}')
                AND p.post_type IN ('product','product_variation') AND p.post_status = 'publish'
            )";
        }

        if ($cat_filter_sql) {
            $brand_terms = $wpdb->get_results($wpdb->prepare("
                SELECT DISTINCT t.term_id, t.name, t.slug
                FROM {$wpdb->terms} t
                INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                WHERE tt.taxonomy = %s {$cat_filter_sql}
                ORDER BY t.name ASC
            ", $brand_taxonomy));
        } else {
            $brand_terms = get_terms(['taxonomy' => $brand_taxonomy, 'hide_empty' => true]);
        }

        if ($brand_terms && !is_wp_error($brand_terms) && !empty($brand_terms)) {
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
        <!-- Filter Chips Row -->
        <div class="filter-chips">
            <button type="button" class="filter-chip" onclick="toggleFilterPanel('sort')" id="chip-sort">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                <?php _e('مرتب‌سازی', 'ganjeh'); ?>
                <svg class="chip-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <?php if (!empty($filter_categories)) : ?>
            <button type="button" class="filter-chip <?php echo !empty($active_cats) ? 'has-filter' : ''; ?>" onclick="toggleFilterPanel('cat')" id="chip-cat">
                <?php _e('دسته‌بندی', 'ganjeh'); ?>
                <?php if (!empty($active_cats)) : ?><span class="chip-badge"><?php echo count($active_cats); ?></span><?php endif; ?>
                <svg class="chip-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <?php endif; ?>

            <?php foreach ($attribute_filters as $af) : ?>
            <button type="button" class="filter-chip <?php echo !empty($af['active_terms']) ? 'has-filter' : ''; ?>" onclick="toggleFilterPanel('<?php echo esc_js($af['param_name']); ?>')" id="chip-<?php echo esc_attr($af['param_name']); ?>">
                <?php echo esc_html($af['label']); ?>
                <?php if (!empty($af['active_terms'])) : ?><span class="chip-badge"><?php echo count($af['active_terms']); ?></span><?php endif; ?>
                <svg class="chip-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <?php endforeach; ?>

            <?php if ($has_any_active_filter) : ?>
            <a href="<?php echo esc_url(($is_category ? get_term_link($current_cat) : wc_get_page_permalink('shop')) . (isset($_GET['orderby']) ? '?orderby=' . esc_attr($_GET['orderby']) : '')); ?>" class="filter-chip clear-chip">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                <?php _e('حذف فیلتر', 'ganjeh'); ?>
            </a>
            <?php endif; ?>
        </div>

        <!-- Sort Panel -->
        <div class="filter-panel" id="panel-sort">
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
                    onchange="applySort(this.value)">
                <span class="radio-mark"></span>
                <span><?php echo esc_html($label); ?></span>
            </label>
            <?php endforeach; ?>
        </div>

        <!-- Category Panel -->
        <?php if (!empty($filter_categories)) : ?>
        <div class="filter-panel" id="panel-cat">
            <?php foreach ($filter_categories as $cat) : ?>
            <label class="filter-option">
                <input type="checkbox" name="filter_cat" value="<?php echo esc_attr($cat->slug); ?>"
                    <?php checked(in_array($cat->slug, $active_cats)); ?>
                    onchange="applyFilter('filter_cat')">
                <span class="check-mark"></span>
                <span><?php echo esc_html($cat->name); ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Attribute Panels -->
        <?php foreach ($attribute_filters as $af) : ?>
        <div class="filter-panel" id="panel-<?php echo esc_attr($af['param_name']); ?>">
            <?php if ($af['param_name'] === 'filter_brand' && !empty($af['taxonomy'])) : ?>
            <input type="hidden" name="brand_tax" value="<?php echo esc_attr($af['taxonomy']); ?>">
            <?php endif; ?>
            <?php foreach ($af['terms'] as $term) : ?>
            <label class="filter-option">
                <input type="checkbox" name="<?php echo esc_attr($af['param_name']); ?>" value="<?php echo esc_attr($term->term_id); ?>"
                    <?php checked(in_array((int)$term->term_id, $af['active_terms'])); ?>
                    onchange="applyFilter('<?php echo esc_js($af['param_name']); ?>')">
                <span class="check-mark"></span>
                <span><?php echo esc_html($term->name); ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
    function toggleFilterPanel(id) {
        var panels = document.querySelectorAll('.filter-panel');
        var chips = document.querySelectorAll('.filter-chip');
        var targetPanel = document.getElementById('panel-' + id);
        var targetChip = document.getElementById('chip-' + id);
        var isOpen = targetPanel && targetPanel.classList.contains('open');

        // Close all panels
        panels.forEach(function(p) { p.classList.remove('open'); });
        chips.forEach(function(c) { c.classList.remove('active'); });

        // Toggle target
        if (!isOpen && targetPanel) {
            targetPanel.classList.add('open');
            if (targetChip) targetChip.classList.add('active');
        }
    }

    function applySort(value) {
        var url = new URL(window.location.href);
        url.searchParams.set('orderby', value);
        window.location.href = url.toString();
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
        url.searchParams.delete('stock_filter');
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
    document.querySelectorAll('.categories-tabs').forEach(enableDragScroll);
    </script>
    <?php endif; ?>

    <?php
    $current_stock = isset($_GET['stock_filter']) ? sanitize_text_field($_GET['stock_filter']) : 'instock';
    $is_outofstock_page = ($current_stock === 'outofstock');

    // On category pages, WooCommerce hooks may not fire - apply all filters directly
    if ($is_category && !$is_search) {
        global $wp_query;
        $query_args = $wp_query->query;
        $query_args['post_type'] = 'product';
        $query_args['post_status'] = 'publish';
        $query_args['posts_per_page'] = -1;

        // Build meta_query with named clauses (stock + optional sort meta)
        $stock_value = $current_stock === 'outofstock' ? 'outofstock' : 'instock';
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'menu_order';

        switch ($orderby) {
            case 'date':
                $query_args['meta_query'] = [
                    'stock_clause' => ['key' => '_stock_status', 'value' => $stock_value],
                ];
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'DESC';
                break;
            case 'popularity':
                $query_args['meta_query'] = [
                    'relation' => 'AND',
                    'stock_clause' => ['key' => '_stock_status', 'value' => $stock_value],
                    'sales_clause' => ['key' => 'total_sales', 'type' => 'NUMERIC'],
                ];
                $query_args['orderby'] = 'sales_clause';
                $query_args['order'] = 'DESC';
                break;
            case 'price':
                $query_args['meta_query'] = [
                    'relation' => 'AND',
                    'stock_clause' => ['key' => '_stock_status', 'value' => $stock_value],
                    'price_clause' => ['key' => '_price', 'type' => 'NUMERIC'],
                ];
                $query_args['orderby'] = 'price_clause';
                $query_args['order'] = 'ASC';
                break;
            case 'price-desc':
                $query_args['meta_query'] = [
                    'relation' => 'AND',
                    'stock_clause' => ['key' => '_stock_status', 'value' => $stock_value],
                    'price_clause' => ['key' => '_price', 'type' => 'NUMERIC'],
                ];
                $query_args['orderby'] = 'price_clause';
                $query_args['order'] = 'DESC';
                break;
            default:
                $query_args['meta_query'] = [
                    'stock_clause' => ['key' => '_stock_status', 'value' => $stock_value],
                ];
                $query_args['orderby'] = 'menu_order title';
                $query_args['order'] = 'ASC';
        }

        // Brand filter
        if (!empty($_GET['filter_brand']) && !empty($_GET['brand_tax'])) {
            $b_tax = sanitize_text_field($_GET['brand_tax']);
            $b_ids = array_filter(array_map('intval', explode(',', $_GET['filter_brand'])));
            if (!empty($b_ids) && taxonomy_exists($b_tax)) {
                $query_args['tax_query'] = [
                    [
                        'taxonomy' => $b_tax,
                        'field'    => 'term_id',
                        'terms'    => $b_ids,
                        'operator' => 'IN',
                    ],
                ];
            }
        }

        query_posts($query_args);
    }
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
    gap: 10px;
    padding: 12px 16px;
    background: white;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.categories-tabs::-webkit-scrollbar {
    display: none;
}

.categories-tabs {
    touch-action: pan-x;
    user-select: none;
    -webkit-user-select: none;
}

.categories-tabs a,
.categories-tabs img {
    -webkit-user-drag: none;
    user-drag: none;
}

.categories-tabs img {
    pointer-events: none;
}

.cat-tab {
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

.cat-tab-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.cat-tab-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cat-tab-icon svg {
    color: #9ca3af;
}

.cat-tab-name {
    font-size: 11px;
    font-weight: 600;
}

.cat-tab.active {
    background: #4CB050;
    color: white;
}

.cat-tab.active .cat-tab-icon {
    background: rgba(255,255,255,0.25);
}

.cat-tab.active .cat-tab-icon svg {
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

/* Inline Filters */
.inline-filters {
    background: white;
    border-bottom: 1px solid #f3f4f6;
}

/* Filter Chips Row */
.filter-chips {
    display: flex;
    gap: 8px;
    padding: 10px 16px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.filter-chips::-webkit-scrollbar {
    display: none;
}

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

.filter-chip svg {
    color: #9ca3af;
}

.filter-chip.active {
    background: #4CB050;
    color: white;
}

.filter-chip.active svg {
    color: white;
}

.filter-chip.active .chip-arrow {
    transform: rotate(180deg);
}

.filter-chip.has-filter {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

.filter-chip.has-filter svg {
    color: #059669;
}

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

.chip-arrow {
    transition: transform 0.3s;
}

.clear-chip {
    background: #fef2f2;
    color: #ef4444;
}

.clear-chip svg {
    color: #ef4444;
}

/* Filter Panel (dropdown) */
.filter-panel {
    display: none;
    padding: 8px 16px 14px;
    border-top: 1px solid #f3f4f6;
}

.filter-panel.open {
    display: block;
}

/* Filter Options */
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

.filter-option input {
    display: none;
}

.radio-mark {
    width: 18px;
    height: 18px;
    border: 2px solid #d1d5db;
    border-radius: 50%;
    position: relative;
    flex-shrink: 0;
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
    width: 9px;
    height: 9px;
    background: #4CB050;
    border-radius: 50%;
}

/* Checkbox Mark */
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
