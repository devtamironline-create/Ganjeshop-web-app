<?php
/**
 * Category Display Settings
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get category display settings
 */
function ganjeh_get_category_settings() {
    return get_option('ganjeh_category_settings', [
        'enabled' => true,
        'columns' => 4,
        'show_all' => true,
        'visible_categories' => [],
        'category_badges' => []
    ]);
}

/**
 * Get visible categories for frontend
 */
function ganjeh_get_visible_categories() {
    $settings = ganjeh_get_category_settings();

    // If not showing all, get categories in the exact saved order
    if (empty($settings['show_all']) && !empty($settings['visible_categories'])) {
        $ordered_categories = [];
        foreach ($settings['visible_categories'] as $cat_id) {
            $cat = get_term($cat_id, 'product_cat');
            if ($cat && !is_wp_error($cat)) {
                $ordered_categories[] = $cat;
            }
        }
        return $ordered_categories;
    }

    // Default: show only parent categories
    $args = [
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'parent'     => 0,
        'orderby'    => 'menu_order',
        'order'      => 'ASC',
    ];

    $categories = get_terms($args);

    if (is_wp_error($categories)) {
        return [];
    }

    return $categories;
}

/**
 * Get badge for category
 */
function ganjeh_get_category_badge($term_id) {
    $settings = ganjeh_get_category_settings();
    return $settings['category_badges'][$term_id] ?? '';
}

/**
 * Admin page for category settings
 */
function ganjeh_render_category_settings_page() {
    $settings = ganjeh_get_category_settings();

    // Save settings
    if (isset($_POST['ganjeh_save_categories'])) {
        check_admin_referer('ganjeh_category_settings_nonce');
        $settings = ganjeh_save_category_settings($_POST);
        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات دسته‌بندی‌ها ذخیره شد!</p></div>';
    }

    // Get all product categories (hierarchical)
    $parent_categories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'parent'     => 0,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    // Build hierarchical list of all categories
    $all_categories = [];
    if (!empty($parent_categories) && !is_wp_error($parent_categories)) {
        foreach ($parent_categories as $parent) {
            $all_categories[] = $parent;
            // Get children
            $children = get_terms([
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
                'parent'     => $parent->term_id,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ]);
            if (!empty($children) && !is_wp_error($children)) {
                foreach ($children as $child) {
                    $child->is_child = true;
                    $child->parent_name = $parent->name;
                    $all_categories[] = $child;
                }
            }
        }
    }

    $visible_cats = $settings['visible_categories'] ?? [];
    $badges = $settings['category_badges'] ?? [];
    ?>
    <div class="wrap ganjeh-category-settings">
        <h1>تنظیمات دسته‌بندی‌ها</h1>
        <p class="description">تعیین کنید کدام دسته‌بندی‌ها در صفحه اصلی نمایش داده شوند</p>

        <form method="post" action="" id="category-form">
            <?php wp_nonce_field('ganjeh_category_settings_nonce'); ?>

            <div class="ganjeh-cat-box">
                <div class="ganjeh-cat-header">
                    <label class="ganjeh-toggle">
                        <input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled']); ?>>
                        <span class="ganjeh-toggle-slider"></span>
                    </label>
                    <h2>نمایش دسته‌بندی‌ها</h2>
                </div>

                <div class="ganjeh-cat-content">
                    <div class="ganjeh-cat-row">
                        <div class="ganjeh-cat-field">
                            <label>تعداد ستون:</label>
                            <select name="columns">
                                <option value="3" <?php selected($settings['columns'], 3); ?>>۳ ستون</option>
                                <option value="4" <?php selected($settings['columns'], 4); ?>>۴ ستون</option>
                            </select>
                        </div>

                        <div class="ganjeh-cat-field">
                            <label>
                                <input type="checkbox" name="show_all" value="1" <?php checked($settings['show_all'] ?? true); ?> id="show-all-toggle">
                                نمایش همه دسته‌بندی‌های اصلی
                            </label>
                        </div>
                    </div>

                    <div id="categories-selection" style="<?php echo ($settings['show_all'] ?? true) ? 'display:none;' : ''; ?>">
                        <h3>انتخاب دسته‌بندی‌ها</h3>
                        <p class="description">دسته‌بندی‌هایی که می‌خواهید در صفحه اصلی نمایش داده شوند را انتخاب کنید</p>

                        <div class="ganjeh-categories-grid">
                            <?php if (!empty($all_categories)) : ?>
                                <?php foreach ($all_categories as $cat) :
                                    $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
                                    $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '';
                                    $is_visible = in_array($cat->term_id, $visible_cats);
                                    $badge = $badges[$cat->term_id] ?? '';
                                    $is_child = !empty($cat->is_child);
                                ?>
                                    <div class="ganjeh-cat-item <?php echo $is_visible ? 'selected' : ''; ?> <?php echo $is_child ? 'is-child' : 'is-parent'; ?>" data-id="<?php echo $cat->term_id; ?>">
                                        <label class="cat-checkbox">
                                            <input type="checkbox" class="cat-select-checkbox" value="<?php echo $cat->term_id; ?>" <?php checked($is_visible); ?> data-name="<?php echo esc_attr($cat->name); ?>" data-image="<?php echo esc_url($image_url); ?>" data-parent="<?php echo $is_child ? esc_attr($cat->parent_name) : ''; ?>">
                                            <span class="checkmark"></span>
                                        </label>

                                        <div class="cat-preview">
                                            <?php if ($image_url) : ?>
                                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($cat->name); ?>">
                                            <?php else : ?>
                                                <div class="cat-placeholder">
                                                    <span class="dashicons dashicons-category"></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="cat-name">
                                            <?php if ($is_child) : ?>
                                                <span class="parent-indicator"><?php echo esc_html($cat->parent_name); ?> ←</span>
                                            <?php endif; ?>
                                            <?php echo esc_html($cat->name); ?>
                                        </div>

                                        <div class="cat-badge-field">
                                            <input type="text"
                                                   name="category_badges[<?php echo $cat->term_id; ?>]"
                                                   value="<?php echo esc_attr($badge); ?>"
                                                   placeholder="برچسب (مثلا: جدید)">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <p>هیچ دسته‌بندی یافت نشد.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Sortable selected categories -->
                        <div id="selected-categories-section" style="<?php echo empty($visible_cats) ? 'display:none;' : ''; ?>">
                            <h3>ترتیب نمایش <small>(برای تغییر ترتیب، بکشید و رها کنید)</small></h3>
                            <p class="description">دسته‌بندی‌های انتخاب شده به ترتیب زیر نمایش داده می‌شوند</p>

                            <ul id="sortable-categories" class="sortable-list">
                                <?php
                                // Show selected categories in order
                                foreach ($visible_cats as $cat_id) :
                                    $cat = get_term($cat_id, 'product_cat');
                                    if (!$cat || is_wp_error($cat)) continue;
                                    $thumbnail_id = get_term_meta($cat_id, 'thumbnail_id', true);
                                    $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '';
                                    $parent_cat = $cat->parent ? get_term($cat->parent, 'product_cat') : null;
                                ?>
                                    <li class="sortable-item" data-id="<?php echo $cat_id; ?>">
                                        <input type="hidden" name="visible_categories[]" value="<?php echo $cat_id; ?>">
                                        <span class="drag-handle">☰</span>
                                        <?php if ($image_url) : ?>
                                            <img src="<?php echo esc_url($image_url); ?>" alt="">
                                        <?php endif; ?>
                                        <span class="item-name">
                                            <?php if ($parent_cat) : ?>
                                                <small><?php echo esc_html($parent_cat->name); ?> ←</small>
                                            <?php endif; ?>
                                            <?php echo esc_html($cat->name); ?>
                                        </span>
                                        <button type="button" class="remove-item" data-id="<?php echo $cat_id; ?>">×</button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <div id="badges-only-section" style="<?php echo ($settings['show_all'] ?? true) ? '' : 'display:none;'; ?>">
                        <h3>برچسب دسته‌بندی‌ها</h3>
                        <p class="description">برچسب‌های نمایشی روی هر دسته‌بندی (مانند: جدید، پرفروش، خرید قسطی)</p>

                        <div class="ganjeh-badges-grid">
                            <?php if (!empty($all_categories)) : ?>
                                <?php foreach ($all_categories as $cat) :
                                    $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
                                    $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '';
                                    $badge = $badges[$cat->term_id] ?? '';
                                    $is_child = !empty($cat->is_child);
                                ?>
                                    <div class="ganjeh-badge-item <?php echo $is_child ? 'is-child' : ''; ?>">
                                        <div class="badge-cat-info">
                                            <?php if ($image_url) : ?>
                                                <img src="<?php echo esc_url($image_url); ?>" alt="">
                                            <?php endif; ?>
                                            <span>
                                                <?php if ($is_child) : ?>
                                                    <small class="parent-name"><?php echo esc_html($cat->parent_name); ?> ←</small>
                                                <?php endif; ?>
                                                <?php echo esc_html($cat->name); ?>
                                            </span>
                                        </div>
                                        <input type="text"
                                               name="category_badges[<?php echo $cat->term_id; ?>]"
                                               value="<?php echo esc_attr($badge); ?>"
                                               placeholder="برچسب">
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <p class="submit">
                <button type="submit" name="ganjeh_save_categories" class="button button-primary button-large">
                    ذخیره تنظیمات
                </button>
            </p>
        </form>
    </div>

    <style>
        .ganjeh-category-settings { max-width: 1000px; }
        .ganjeh-cat-box {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            margin: 20px 0;
            overflow: hidden;
        }
        .ganjeh-cat-header {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        .ganjeh-cat-header h2 { margin: 0; font-size: 16px; }
        .ganjeh-cat-content { padding: 20px; }
        .ganjeh-cat-content h3 {
            margin: 20px 0 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .ganjeh-cat-row {
            display: flex;
            gap: 30px;
            align-items: center;
            flex-wrap: wrap;
        }
        .ganjeh-cat-field label { font-weight: 500; }
        .ganjeh-cat-field select { padding: 8px 12px; border-radius: 6px; min-width: 120px; }

        /* Categories Grid */
        .ganjeh-categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .ganjeh-cat-item {
            position: relative;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 15px;
            background: #fafafa;
            transition: all 0.3s;
            text-align: center;
        }
        .ganjeh-cat-item:hover { border-color: #4CB050; }
        .ganjeh-cat-item.selected { border-color: #4CB050; background: #f0fdf4; }

        .cat-checkbox {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 22px;
            height: 22px;
        }
        .cat-checkbox input { opacity: 0; position: absolute; }
        .cat-checkbox .checkmark {
            position: absolute;
            top: 0; left: 0;
            width: 22px; height: 22px;
            background: #fff;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .cat-checkbox input:checked + .checkmark {
            background: #4CB050;
            border-color: #4CB050;
        }
        .cat-checkbox input:checked + .checkmark:after {
            content: '';
            position: absolute;
            left: 6px; top: 2px;
            width: 6px; height: 12px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .cat-preview {
            width: 70px;
            height: 70px;
            margin: 10px auto;
            border-radius: 10px;
            overflow: hidden;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cat-preview img { width: 100%; height: 100%; object-fit: cover; }
        .cat-placeholder { color: #9ca3af; }
        .cat-placeholder .dashicons { font-size: 30px; width: 30px; height: 30px; }

        .cat-name {
            font-weight: 600;
            font-size: 13px;
            color: #374151;
            margin: 10px 0;
        }
        .cat-badge-field input {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 12px;
            text-align: center;
        }

        /* Badges Grid */
        .ganjeh-badges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        .ganjeh-badge-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .badge-cat-info {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 120px;
        }
        .badge-cat-info img {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            object-fit: cover;
        }
        .badge-cat-info span { font-weight: 500; font-size: 13px; }
        .ganjeh-badge-item input {
            width: 100px;
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 12px;
        }

        /* Child category styles */
        .ganjeh-cat-item.is-child {
            background: #fff;
            border-style: dashed;
        }
        .ganjeh-cat-item.is-child .cat-name {
            font-size: 12px;
        }
        .ganjeh-cat-item .parent-indicator {
            display: block;
            font-size: 10px;
            color: #9ca3af;
            font-weight: 400;
            margin-bottom: 2px;
        }
        .ganjeh-badge-item.is-child {
            padding-right: 25px;
            background: #fff;
        }
        .ganjeh-badge-item .parent-name {
            color: #9ca3af;
            font-size: 11px;
            display: block;
        }

        /* Sortable list styles */
        #selected-categories-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #4CB050;
        }
        #selected-categories-section h3 {
            color: #4CB050;
            border-bottom: none;
        }
        #selected-categories-section h3 small {
            font-weight: 400;
            font-size: 12px;
            color: #9ca3af;
        }
        .sortable-list {
            list-style: none;
            padding: 0;
            margin: 15px 0;
            max-width: 500px;
        }
        .sortable-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            margin-bottom: 8px;
            cursor: move;
            transition: all 0.2s;
        }
        .sortable-item:hover {
            border-color: #4CB050;
            box-shadow: 0 2px 8px rgba(76, 176, 80, 0.15);
        }
        .sortable-item.ui-sortable-helper {
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            background: #f0fdf4;
        }
        .sortable-item .drag-handle {
            color: #9ca3af;
            font-size: 16px;
            cursor: grab;
        }
        .sortable-item img {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            object-fit: cover;
        }
        .sortable-item .item-name {
            flex: 1;
            font-weight: 500;
            font-size: 14px;
        }
        .sortable-item .item-name small {
            display: block;
            font-size: 11px;
            color: #9ca3af;
            font-weight: 400;
        }
        .sortable-item .remove-item {
            width: 28px;
            height: 28px;
            border: none;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            line-height: 1;
            transition: all 0.2s;
        }
        .sortable-item .remove-item:hover {
            background: #dc2626;
            color: #fff;
        }
        .ui-sortable-placeholder {
            background: #f0fdf4 !important;
            border: 2px dashed #4CB050 !important;
            visibility: visible !important;
            height: 60px;
            border-radius: 10px;
            margin-bottom: 8px;
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

        @media (max-width: 600px) {
            .ganjeh-categories-grid { grid-template-columns: repeat(2, 1fr); }
            .ganjeh-badges-grid { grid-template-columns: 1fr; }
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Toggle show all categories
        $('#show-all-toggle').on('change', function() {
            if ($(this).is(':checked')) {
                $('#categories-selection').hide();
                $('#badges-only-section').show();
            } else {
                $('#categories-selection').show();
                $('#badges-only-section').hide();
            }
        });

        // Initialize sortable
        $('#sortable-categories').sortable({
            handle: '.drag-handle',
            placeholder: 'ui-sortable-placeholder',
            animation: 150
        });

        // Handle category selection
        $('.cat-select-checkbox').on('change', function() {
            var $item = $(this).closest('.ganjeh-cat-item');
            var catId = $(this).val();
            var catName = $(this).data('name');
            var catImage = $(this).data('image');
            var catParent = $(this).data('parent');

            $item.toggleClass('selected', $(this).is(':checked'));

            if ($(this).is(':checked')) {
                // Add to sortable list
                var imageHtml = catImage ? '<img src="' + catImage + '" alt="">' : '';
                var parentHtml = catParent ? '<small>' + catParent + ' ←</small>' : '';

                var newItem = '<li class="sortable-item" data-id="' + catId + '">' +
                    '<input type="hidden" name="visible_categories[]" value="' + catId + '">' +
                    '<span class="drag-handle">☰</span>' +
                    imageHtml +
                    '<span class="item-name">' + parentHtml + catName + '</span>' +
                    '<button type="button" class="remove-item" data-id="' + catId + '">×</button>' +
                    '</li>';

                $('#sortable-categories').append(newItem);
                $('#selected-categories-section').show();
            } else {
                // Remove from sortable list
                $('#sortable-categories .sortable-item[data-id="' + catId + '"]').remove();

                if ($('#sortable-categories .sortable-item').length === 0) {
                    $('#selected-categories-section').hide();
                }
            }
        });

        // Handle remove button in sortable list
        $(document).on('click', '.remove-item', function() {
            var catId = $(this).data('id');

            // Uncheck the checkbox
            $('.cat-select-checkbox[value="' + catId + '"]').prop('checked', false);
            $('.ganjeh-cat-item[data-id="' + catId + '"]').removeClass('selected');

            // Remove from list
            $(this).closest('.sortable-item').remove();

            if ($('#sortable-categories .sortable-item').length === 0) {
                $('#selected-categories-section').hide();
            }
        });
    });
    </script>
    <?php
}

/**
 * Save category settings
 */
function ganjeh_save_category_settings($post) {
    $settings = [
        'enabled' => isset($post['enabled']),
        'columns' => intval($post['columns'] ?? 4),
        'show_all' => isset($post['show_all']),
        'visible_categories' => [],
        'category_badges' => []
    ];

    if (isset($post['visible_categories']) && is_array($post['visible_categories'])) {
        $settings['visible_categories'] = array_map('intval', $post['visible_categories']);
    }

    if (isset($post['category_badges']) && is_array($post['category_badges'])) {
        foreach ($post['category_badges'] as $term_id => $badge) {
            $badge = sanitize_text_field($badge);
            if (!empty($badge)) {
                $settings['category_badges'][intval($term_id)] = $badge;
            }
        }
    }

    update_option('ganjeh_category_settings', $settings);
    return $settings;
}

/**
 * Register category settings page
 */
function ganjeh_register_category_page() {
    add_submenu_page(
        'dst-website-settings',
        'تنظیمات دسته‌بندی‌ها',
        'دسته‌بندی‌ها',
        'manage_options',
        'ganjeh-categories',
        'ganjeh_render_category_settings_page'
    );
}
add_action('admin_menu', 'ganjeh_register_category_page', 10002);

/**
 * Enqueue media library for category page
 */
function ganjeh_category_admin_scripts($hook) {
    if (strpos($hook, 'ganjeh-categories') === false) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');
}
add_action('admin_enqueue_scripts', 'ganjeh_category_admin_scripts');
