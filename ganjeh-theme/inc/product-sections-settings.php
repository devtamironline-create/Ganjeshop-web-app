<?php
/**
 * Product Sections Settings
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get product sections settings
 */
function ganjeh_get_product_sections_settings() {
    $default = [
        'section_1' => [
            'enabled' => true,
            'title' => 'محصولات ویژه',
            'type' => 'featured',
            'category_id' => 0,
            'limit' => 10,
            'order' => 1
        ],
        'section_2' => [
            'enabled' => true,
            'title' => 'تخفیف‌های ویژه',
            'type' => 'on_sale',
            'category_id' => 0,
            'limit' => 10,
            'order' => 2
        ],
        'section_3' => [
            'enabled' => true,
            'title' => 'جدیدترین محصولات',
            'type' => 'recent',
            'category_id' => 0,
            'limit' => 10,
            'order' => 3
        ]
    ];

    $saved = get_option('ganjeh_product_sections_v2', null);

    if ($saved !== null) {
        return $saved;
    }

    // Migrate from old format
    $old = get_option('ganjeh_product_sections', null);
    if ($old !== null && is_array($old)) {
        $migrated = [];
        $i = 1;
        foreach ($old as $key => $section) {
            $migrated['section_' . $i] = [
                'enabled' => !empty($section['enabled']),
                'title' => $section['title'] ?? '',
                'type' => $section['type'] ?? 'recent',
                'category_id' => intval($section['category_id'] ?? 0),
                'limit' => intval($section['limit'] ?? 10),
                'order' => $i,
            ];
            $i++;
        }
        update_option('ganjeh_product_sections_v2', $migrated);
        return $migrated;
    }

    return $default;
}

/**
 * Get sorted sections (by order field)
 */
function ganjeh_get_sorted_sections() {
    $settings = ganjeh_get_product_sections_settings();
    uasort($settings, function($a, $b) {
        $oa = intval($a['order'] ?? 0);
        $ob = intval($b['order'] ?? 0);
        return $oa - $ob;
    });
    return $settings;
}

/**
 * Get products for a section
 */
function ganjeh_get_section_products($section_key) {
    $settings = ganjeh_get_product_sections_settings();
    $section = $settings[$section_key] ?? null;

    if (!$section || empty($section['enabled'])) {
        return [];
    }

    $limit = intval($section['limit'] ?? 10);
    if ($limit < 1) $limit = 10;

    // For category type, use WP_Query to include child categories
    if ($section['type'] === 'category' && !empty($section['category_id'])) {
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 100,
            'tax_query' => [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $section['category_id'],
                    'include_children' => true,
                ],
            ],
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        $query = new WP_Query($args);
        $filtered_products = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product = wc_get_product(get_the_ID());
                if ($product && $product->is_in_stock()) {
                    $filtered_products[] = $product;
                    if (count($filtered_products) >= $limit) {
                        break;
                    }
                }
            }
            wp_reset_postdata();
        }

        return $filtered_products;
    }

    // For on_sale, use WooCommerce's built-in function
    if ($section['type'] === 'on_sale') {
        // Clear the transient to get fresh data
        delete_transient('wc_products_onsale');

        // Get all on-sale product IDs using WooCommerce function
        $on_sale_ids = wc_get_product_ids_on_sale();

        if (empty($on_sale_ids)) {
            return [];
        }

        $filtered_products = [];
        foreach ($on_sale_ids as $product_id) {
            $product = wc_get_product($product_id);

            if (!$product || !$product->is_in_stock()) {
                continue;
            }

            $filtered_products[] = $product;

            if (count($filtered_products) >= $limit) {
                break;
            }
        }

        return $filtered_products;
    }

    // For other types (featured, recent, best_selling)
    $args = [
        'limit' => 100,
        'status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    ];

    switch ($section['type']) {
        case 'featured':
            $args['featured'] = true;
            break;
        case 'best_selling':
            $args['orderby'] = 'popularity';
            $args['meta_query'] = [
                [
                    'key' => 'total_sales',
                    'value' => 0,
                    'compare' => '>',
                    'type' => 'NUMERIC',
                ],
            ];
            break;
        case 'recent':
        default:
            break;
    }

    $all_products = wc_get_products($args);
    $filtered_products = [];

    foreach ($all_products as $product) {
        if ($product->is_in_stock()) {
            $filtered_products[] = $product;
            if (count($filtered_products) >= $limit) {
                break;
            }
        }
    }

    return $filtered_products;
}

/**
 * Check if section is enabled
 */
function ganjeh_is_section_enabled($section_key) {
    $settings = ganjeh_get_product_sections_settings();
    return !empty($settings[$section_key]['enabled']);
}

/**
 * Get section title
 */
function ganjeh_get_section_title($section_key) {
    $settings = ganjeh_get_product_sections_settings();
    return $settings[$section_key]['title'] ?? '';
}

/**
 * Get "view more" URL for a section based on its type
 */
function ganjeh_get_section_view_more_url($section_key) {
    $settings = ganjeh_get_product_sections_settings();
    $section = $settings[$section_key] ?? null;
    $shop_url = get_permalink(wc_get_page_id('shop'));

    if (!$section) {
        return $shop_url;
    }

    $type = $section['type'] ?? 'recent';

    switch ($type) {
        case 'best_selling':
            return $shop_url . '?orderby=popularity';
        case 'on_sale':
            return home_url('/shop/?on_sale=1');
        case 'recent':
            return $shop_url . '?orderby=date';
        case 'category':
            if (!empty($section['category_id'])) {
                $term = get_term($section['category_id'], 'product_cat');
                if ($term && !is_wp_error($term)) {
                    return get_term_link($term);
                }
            }
            return $shop_url;
        case 'featured':
        default:
            return $shop_url;
    }
}

/**
 * Admin page for product sections settings
 */
function ganjeh_render_product_sections_page() {
    $settings = ganjeh_get_product_sections_settings();

    // Handle delete
    if (isset($_GET['delete_section']) && isset($_GET['_wpnonce'])) {
        if (wp_verify_nonce($_GET['_wpnonce'], 'ganjeh_delete_section')) {
            $del_key = sanitize_text_field($_GET['delete_section']);
            if (isset($settings[$del_key])) {
                unset($settings[$del_key]);
                update_option('ganjeh_product_sections_v2', $settings);
                echo '<div class="notice notice-success is-dismissible"><p>بخش حذف شد!</p></div>';
            }
        }
    }

    // Handle add
    if (isset($_POST['ganjeh_add_section'])) {
        check_admin_referer('ganjeh_product_sections_nonce');
        $next_id = 1;
        foreach (array_keys($settings) as $k) {
            if (preg_match('/section_(\d+)/', $k, $m)) {
                $next_id = max($next_id, intval($m[1]) + 1);
            }
        }
        $new_key = 'section_' . $next_id;
        $settings[$new_key] = [
            'enabled' => true,
            'title' => 'بخش جدید',
            'type' => 'recent',
            'category_id' => 0,
            'limit' => 10,
            'order' => count($settings) + 1,
        ];
        update_option('ganjeh_product_sections_v2', $settings);
        echo '<div class="notice notice-success is-dismissible"><p>بخش جدید اضافه شد!</p></div>';
    }

    // Save settings
    if (isset($_POST['ganjeh_save_product_sections'])) {
        check_admin_referer('ganjeh_product_sections_nonce');
        $settings = ganjeh_save_product_sections_settings($_POST);
        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات بخش‌های محصولات ذخیره شد!</p></div>';
    }

    // Sort by order
    uasort($settings, function($a, $b) {
        return intval($a['order'] ?? 0) - intval($b['order'] ?? 0);
    });

    // Get all product categories
    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'orderby' => 'name',
    ]);

    $section_types = [
        'featured' => 'محصولات ویژه',
        'recent' => 'جدیدترین محصولات',
        'on_sale' => 'محصولات تخفیف‌دار',
        'best_selling' => 'پرفروش‌ترین‌ها',
        'category' => 'دسته‌بندی خاص',
    ];

    $section_count = count($settings);
    ?>
    <div class="wrap ganjeh-product-sections-settings">
        <h1>تنظیمات بخش‌های محصولات</h1>
        <p class="description">مدیریت بخش‌های نمایش محصولات در صفحه اصلی - هر چند بخش که بخواهید اضافه یا حذف کنید</p>

        <form method="post" action="">
            <?php wp_nonce_field('ganjeh_product_sections_nonce'); ?>

            <div id="sections-container">
            <?php $index = 0; foreach ($settings as $key => $section) : $index++; ?>
            <div class="ganjeh-section-box" data-key="<?php echo esc_attr($key); ?>">
                <div class="ganjeh-section-header">
                    <label class="ganjeh-toggle">
                        <input type="checkbox" name="sections[<?php echo $key; ?>][enabled]" value="1" <?php checked(!empty($section['enabled'])); ?>>
                        <span class="ganjeh-toggle-slider"></span>
                    </label>
                    <h2>بخش <?php echo $index; ?> — <small><?php echo esc_html($section['title'] ?? ''); ?></small></h2>
                    <div class="ganjeh-section-actions">
                        <input type="hidden" name="sections[<?php echo $key; ?>][order]" value="<?php echo $index; ?>" class="section-order-input">
                        <?php if ($section_count > 1) : ?>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=ganjeh-product-sections&delete_section=' . $key), 'ganjeh_delete_section'); ?>"
                           class="ganjeh-delete-btn" onclick="return confirm('آیا مطمئنید؟');" title="حذف بخش">
                            <span class="dashicons dashicons-trash"></span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="ganjeh-section-content">
                    <div class="ganjeh-section-row">
                        <div class="ganjeh-field">
                            <label>عنوان بخش:</label>
                            <input type="text" name="sections[<?php echo $key; ?>][title]" value="<?php echo esc_attr($section['title'] ?? ''); ?>" placeholder="عنوان نمایشی">
                        </div>

                        <div class="ganjeh-field">
                            <label>نوع محصولات:</label>
                            <select name="sections[<?php echo $key; ?>][type]" class="section-type-select" data-section="<?php echo $key; ?>">
                                <?php foreach ($section_types as $type_key => $type_label) : ?>
                                    <option value="<?php echo $type_key; ?>" <?php selected($section['type'] ?? '', $type_key); ?>>
                                        <?php echo $type_label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="ganjeh-field">
                            <label>تعداد محصولات:</label>
                            <input type="number" name="sections[<?php echo $key; ?>][limit]" value="<?php echo esc_attr($section['limit'] ?? 10); ?>" min="1" max="50">
                        </div>
                    </div>

                    <div class="ganjeh-field category-field" id="category-field-<?php echo $key; ?>" style="<?php echo ($section['type'] ?? '') !== 'category' ? 'display:none;' : ''; ?>">
                        <label>انتخاب دسته‌بندی:</label>
                        <select name="sections[<?php echo $key; ?>][category_id]">
                            <option value="">انتخاب کنید...</option>
                            <?php foreach ($categories as $cat) : ?>
                                <option value="<?php echo $cat->term_id; ?>" <?php selected($section['category_id'] ?? '', $cat->term_id); ?>>
                                    <?php echo esc_html($cat->name); ?> (<?php echo $cat->count; ?> محصول)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            </div>

            <div class="ganjeh-buttons-row">
                <button type="submit" name="ganjeh_add_section" class="button button-secondary button-large ganjeh-add-btn">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    افزودن بخش جدید
                </button>

                <button type="submit" name="ganjeh_save_product_sections" class="button button-primary button-large">
                    <span class="dashicons dashicons-saved"></span>
                    ذخیره تنظیمات
                </button>
            </div>
        </form>
    </div>

    <style>
        .ganjeh-product-sections-settings { max-width: 900px; }
        .ganjeh-section-box {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            margin: 20px 0;
            overflow: hidden;
        }
        .ganjeh-section-header {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        .ganjeh-section-header h2 { margin: 0; font-size: 16px; flex: 1; }
        .ganjeh-section-header h2 small { color: #888; font-weight: 400; }
        .ganjeh-section-actions { display: flex; gap: 8px; align-items: center; }
        .ganjeh-delete-btn {
            color: #d63638;
            text-decoration: none;
            padding: 4px;
            border-radius: 4px;
            display: flex;
            align-items: center;
        }
        .ganjeh-delete-btn:hover { background: #fee; color: #a00; }
        .ganjeh-section-content { padding: 20px; }
        .ganjeh-section-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .ganjeh-field { margin-bottom: 15px; }
        .ganjeh-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #374151;
        }
        .ganjeh-field input[type="text"],
        .ganjeh-field input[type="number"],
        .ganjeh-field select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }
        .ganjeh-field input:focus,
        .ganjeh-field select:focus {
            outline: none;
            border-color: #4CB050;
            box-shadow: 0 0 0 3px rgba(76, 176, 80, 0.1);
        }
        .ganjeh-buttons-row {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .ganjeh-add-btn {
            display: flex !important;
            align-items: center;
            gap: 6px;
        }
        .ganjeh-add-btn .dashicons,
        .ganjeh-buttons-row .button-primary .dashicons {
            font-size: 18px;
            width: 18px;
            height: 18px;
            margin-top: 2px;
        }

        /* Toggle */
        .ganjeh-toggle { position: relative; width: 50px; height: 26px; flex-shrink: 0; }
        .ganjeh-toggle input { opacity: 0; width: 0; height: 0; }
        .ganjeh-toggle-slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc; border-radius: 26px; transition: 0.3s;
        }
        .ganjeh-toggle-slider:before {
            position: absolute; content: ""; height: 20px; width: 20px;
            left: 3px; bottom: 3px; background-color: white;
            border-radius: 50%; transition: 0.3s;
        }
        .ganjeh-toggle input:checked + .ganjeh-toggle-slider { background-color: #4CB050; }
        .ganjeh-toggle input:checked + .ganjeh-toggle-slider:before { transform: translateX(24px); }

        @media (max-width: 768px) {
            .ganjeh-section-row { grid-template-columns: 1fr; }
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Toggle category field visibility
        $(document).on('change', '.section-type-select', function() {
            var section = $(this).data('section');
            var value = $(this).val();
            if (value === 'category') {
                $('#category-field-' + section).show();
            } else {
                $('#category-field-' + section).hide();
            }
        });
    });
    </script>
    <?php
}

/**
 * Save product sections settings
 */
function ganjeh_save_product_sections_settings($post) {
    $sections = $post['sections'] ?? [];
    $settings = [];

    foreach ($sections as $key => $section) {
        $settings[$key] = [
            'enabled' => isset($section['enabled']),
            'title' => sanitize_text_field($section['title'] ?? ''),
            'type' => sanitize_text_field($section['type'] ?? 'recent'),
            'category_id' => intval($section['category_id'] ?? 0),
            'limit' => min(50, max(1, intval($section['limit'] ?? 10))),
            'order' => intval($section['order'] ?? 1),
        ];
    }

    // Re-sort by order
    uasort($settings, function($a, $b) {
        return intval($a['order'] ?? 0) - intval($b['order'] ?? 0);
    });

    update_option('ganjeh_product_sections_v2', $settings);
    return $settings;
}

/**
 * Register product sections settings page
 */
function ganjeh_register_product_sections_page() {
    add_submenu_page(
        'dst-website-settings',
        'بخش‌های محصولات',
        'بخش‌های محصولات',
        'manage_options',
        'ganjeh-product-sections',
        'ganjeh_render_product_sections_page'
    );
}
add_action('admin_menu', 'ganjeh_register_product_sections_page', 10003);
