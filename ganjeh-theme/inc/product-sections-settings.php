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
    return get_option('ganjeh_product_sections', [
        'featured' => [
            'enabled' => true,
            'title' => 'محصولات ویژه',
            'type' => 'featured', // featured, recent, on_sale, best_selling, category
            'category_id' => 0,
            'limit' => 10,
            'order' => 1
        ],
        'sale' => [
            'enabled' => true,
            'title' => 'تخفیف‌های ویژه',
            'type' => 'on_sale',
            'category_id' => 0,
            'limit' => 10,
            'order' => 2
        ],
        'new' => [
            'enabled' => true,
            'title' => 'جدیدترین محصولات',
            'type' => 'recent',
            'category_id' => 0,
            'limit' => 10,
            'order' => 3
        ]
    ]);
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

    // For on_sale, get all products and filter
    if ($section['type'] === 'on_sale') {
        $all_products = wc_get_products([
            'limit' => 500,
            'status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $filtered_products = [];
        foreach ($all_products as $product) {
            if (!$product->is_in_stock()) {
                continue;
            }

            // Check if product is on sale
            $is_on_sale = false;

            if ($product->is_type('variable')) {
                // For variable products, check variation prices
                $sale_price = $product->get_variation_sale_price('min');
                $regular_price = $product->get_variation_regular_price('min');
                if ($sale_price && $regular_price && floatval($sale_price) < floatval($regular_price)) {
                    $is_on_sale = true;
                }
            } else {
                $is_on_sale = $product->is_on_sale();
            }

            if ($is_on_sale) {
                $filtered_products[] = $product;
                if (count($filtered_products) >= $limit) {
                    break;
                }
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
 * Admin page for product sections settings
 */
function ganjeh_render_product_sections_page() {
    $settings = ganjeh_get_product_sections_settings();

    // Save settings
    if (isset($_POST['ganjeh_save_product_sections'])) {
        check_admin_referer('ganjeh_product_sections_nonce');
        $settings = ganjeh_save_product_sections_settings($_POST);
        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات بخش‌های محصولات ذخیره شد!</p></div>';
    }

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

    $sections = [
        'featured' => 'بخش اول',
        'sale' => 'بخش دوم',
        'new' => 'بخش سوم',
    ];
    ?>
    <div class="wrap ganjeh-product-sections-settings">
        <h1>تنظیمات بخش‌های محصولات</h1>
        <p class="description">مدیریت بخش‌های نمایش محصولات در صفحه اصلی</p>

        <form method="post" action="">
            <?php wp_nonce_field('ganjeh_product_sections_nonce'); ?>

            <?php foreach ($sections as $key => $label) :
                $section = $settings[$key] ?? [];
            ?>
            <div class="ganjeh-section-box">
                <div class="ganjeh-section-header">
                    <label class="ganjeh-toggle">
                        <input type="checkbox" name="sections[<?php echo $key; ?>][enabled]" value="1" <?php checked(!empty($section['enabled'])); ?>>
                        <span class="ganjeh-toggle-slider"></span>
                    </label>
                    <h2><?php echo $label; ?></h2>
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
                            <input type="number" name="sections[<?php echo $key; ?>][limit]" value="<?php echo esc_attr($section['limit'] ?? 10); ?>" min="1" max="20">
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

            <p class="submit">
                <button type="submit" name="ganjeh_save_product_sections" class="button button-primary button-large">
                    ذخیره تنظیمات
                </button>
            </p>
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
        .ganjeh-section-header h2 { margin: 0; font-size: 16px; }
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

        /* Toggle */
        .ganjeh-toggle { position: relative; width: 50px; height: 26px; }
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
        $('.section-type-select').on('change', function() {
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

    foreach (['featured', 'sale', 'new'] as $key) {
        $section = $sections[$key] ?? [];
        $settings[$key] = [
            'enabled' => isset($section['enabled']),
            'title' => sanitize_text_field($section['title'] ?? ''),
            'type' => sanitize_text_field($section['type'] ?? 'recent'),
            'category_id' => intval($section['category_id'] ?? 0),
            'limit' => min(20, max(1, intval($section['limit'] ?? 10))),
            'order' => array_search($key, ['featured', 'sale', 'new']) + 1
        ];
    }

    update_option('ganjeh_product_sections', $settings);
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
