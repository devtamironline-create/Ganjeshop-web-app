<?php
/**
 * Banner Settings for Homepage
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get banner settings
 */
function ganjeh_get_banner_settings() {
    return get_option('ganjeh_banners', ganjeh_get_default_banners());
}

/**
 * Default banner structure
 */
function ganjeh_get_default_banners() {
    return [
        'banner_section_1' => [
            'enabled' => false,
            'layout' => 'single', // single, double, triple
            'position' => 'after_slider',
            'banners' => [
                ['image' => '', 'link' => '', 'alt' => ''],
            ]
        ],
        'banner_section_2' => [
            'enabled' => false,
            'layout' => 'double',
            'position' => 'after_categories',
            'banners' => [
                ['image' => '', 'link' => '', 'alt' => ''],
                ['image' => '', 'link' => '', 'alt' => ''],
            ]
        ],
        'banner_section_3' => [
            'enabled' => false,
            'layout' => 'triple',
            'position' => 'after_featured',
            'banners' => [
                ['image' => '', 'link' => '', 'alt' => ''],
                ['image' => '', 'link' => '', 'alt' => ''],
                ['image' => '', 'link' => '', 'alt' => ''],
            ]
        ],
        'banner_section_4' => [
            'enabled' => false,
            'layout' => 'single',
            'position' => 'after_sale',
            'banners' => [
                ['image' => '', 'link' => '', 'alt' => ''],
            ]
        ],
    ];
}

/**
 * Render banner section
 */
function ganjeh_render_banner_section($section_id) {
    $banners = ganjeh_get_banner_settings();

    if (!isset($banners[$section_id]) || !$banners[$section_id]['enabled']) {
        return;
    }

    $section = $banners[$section_id];
    $layout = $section['layout'];
    $items = $section['banners'];

    // Filter out empty banners
    $items = array_filter($items, function($item) {
        return !empty($item['image']);
    });

    if (empty($items)) {
        return;
    }

    $grid_class = 'grid gap-3 px-4 py-4';
    switch ($layout) {
        case 'single':
            $grid_class .= ' grid-cols-1';
            break;
        case 'double':
            $grid_class .= ' grid-cols-2';
            break;
        case 'triple':
            $grid_class .= ' grid-cols-3';
            break;
    }
    ?>
    <section class="ganjeh-banner-section ganjeh-banner-<?php echo esc_attr($section_id); ?>">
        <div class="<?php echo esc_attr($grid_class); ?>">
            <?php foreach ($items as $banner) : ?>
                <?php if (!empty($banner['image'])) : ?>
                    <a href="<?php echo esc_url($banner['link'] ?: '#'); ?>" class="block rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <img
                            src="<?php echo esc_url($banner['image']); ?>"
                            alt="<?php echo esc_attr($banner['alt'] ?: 'بنر'); ?>"
                            class="w-full h-auto object-cover"
                            loading="lazy"
                        >
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

/**
 * Admin page for banner settings
 */
function ganjeh_render_banner_settings_page() {
    $banners = ganjeh_get_banner_settings();

    // Save settings
    if (isset($_POST['ganjeh_save_banners'])) {
        check_admin_referer('ganjeh_banner_settings_nonce');
        $banners = ganjeh_save_banner_settings($_POST);
        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات بنرها ذخیره شد!</p></div>';
    }

    $positions = [
        'after_slider' => 'بعد از اسلایدر',
        'after_categories' => 'بعد از دسته‌بندی‌ها',
        'after_featured' => 'بعد از محصولات ویژه',
        'after_sale' => 'بعد از تخفیف‌ها',
        'after_new' => 'بعد از محصولات جدید',
    ];

    $layouts = [
        'single' => 'تک بنر (تمام عرض)',
        'double' => 'دو بنر کنار هم',
        'triple' => 'سه بنر کنار هم',
    ];
    ?>
    <div class="wrap ganjeh-banner-settings">
        <h1>مدیریت بنرها</h1>
        <p class="description">بنرهای تبلیغاتی صفحه اصلی را مدیریت کنید</p>

        <form method="post" action="">
            <?php wp_nonce_field('ganjeh_banner_settings_nonce'); ?>

            <?php foreach ($banners as $section_id => $section) :
                $section_num = str_replace('banner_section_', '', $section_id);
            ?>
            <div class="ganjeh-banner-box">
                <div class="ganjeh-banner-header">
                    <label class="ganjeh-toggle">
                        <input type="checkbox"
                               name="banners[<?php echo $section_id; ?>][enabled]"
                               value="1"
                               <?php checked($section['enabled']); ?>>
                        <span class="ganjeh-toggle-slider"></span>
                    </label>
                    <h2>بخش بنر <?php echo $section_num; ?></h2>
                </div>

                <div class="ganjeh-banner-content" style="<?php echo !$section['enabled'] ? 'opacity: 0.5;' : ''; ?>">
                    <div class="ganjeh-banner-row">
                        <div class="ganjeh-banner-field">
                            <label>موقعیت نمایش:</label>
                            <select name="banners[<?php echo $section_id; ?>][position]">
                                <?php foreach ($positions as $pos_key => $pos_label) : ?>
                                    <option value="<?php echo $pos_key; ?>" <?php selected($section['position'], $pos_key); ?>>
                                        <?php echo $pos_label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="ganjeh-banner-field">
                            <label>نوع چیدمان:</label>
                            <select name="banners[<?php echo $section_id; ?>][layout]" class="ganjeh-layout-select" data-section="<?php echo $section_id; ?>">
                                <?php foreach ($layouts as $layout_key => $layout_label) : ?>
                                    <option value="<?php echo $layout_key; ?>" <?php selected($section['layout'], $layout_key); ?>>
                                        <?php echo $layout_label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="ganjeh-banners-grid" id="banners-<?php echo $section_id; ?>">
                        <?php
                        $max_banners = 3;
                        for ($i = 0; $i < $max_banners; $i++) :
                            $banner = isset($section['banners'][$i]) ? $section['banners'][$i] : ['image' => '', 'link' => '', 'alt' => ''];
                            $show_class = '';
                            if ($section['layout'] === 'single' && $i > 0) $show_class = 'hidden';
                            if ($section['layout'] === 'double' && $i > 1) $show_class = 'hidden';
                        ?>
                        <div class="ganjeh-banner-item <?php echo $show_class; ?>" data-index="<?php echo $i; ?>">
                            <div class="ganjeh-banner-preview">
                                <?php if (!empty($banner['image'])) : ?>
                                    <img src="<?php echo esc_url($banner['image']); ?>" alt="">
                                <?php else : ?>
                                    <div class="ganjeh-banner-placeholder">
                                        <span class="dashicons dashicons-format-image"></span>
                                        <span>بنر <?php echo $i + 1; ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <input type="hidden"
                                   name="banners[<?php echo $section_id; ?>][banners][<?php echo $i; ?>][image]"
                                   value="<?php echo esc_attr($banner['image']); ?>"
                                   class="banner-image-input">

                            <div class="ganjeh-banner-fields">
                                <input type="text"
                                       name="banners[<?php echo $section_id; ?>][banners][<?php echo $i; ?>][link]"
                                       value="<?php echo esc_attr($banner['link']); ?>"
                                       placeholder="لینک بنر (اختیاری)">

                                <input type="text"
                                       name="banners[<?php echo $section_id; ?>][banners][<?php echo $i; ?>][alt]"
                                       value="<?php echo esc_attr($banner['alt']); ?>"
                                       placeholder="متن جایگزین (alt)">
                            </div>

                            <div class="ganjeh-banner-actions">
                                <button type="button" class="button ganjeh-upload-banner">انتخاب تصویر</button>
                                <button type="button" class="button ganjeh-remove-banner" style="<?php echo empty($banner['image']) ? 'display:none;' : ''; ?>">حذف</button>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <p class="submit">
                <button type="submit" name="ganjeh_save_banners" class="button button-primary button-large">
                    ذخیره تنظیمات بنرها
                </button>
            </p>
        </form>
    </div>

    <style>
        .ganjeh-banner-settings {
            max-width: 1000px;
        }
        .ganjeh-banner-box {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            margin: 20px 0;
            overflow: hidden;
        }
        .ganjeh-banner-header {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        .ganjeh-banner-header h2 {
            margin: 0;
            font-size: 16px;
        }
        .ganjeh-banner-content {
            padding: 20px;
            transition: opacity 0.3s;
        }
        .ganjeh-banner-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .ganjeh-banner-field {
            flex: 1;
        }
        .ganjeh-banner-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .ganjeh-banner-field select {
            width: 100%;
            padding: 8px 12px;
            border-radius: 6px;
        }
        .ganjeh-banners-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .ganjeh-banner-item {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s;
        }
        .ganjeh-banner-item:hover {
            border-color: #C9A227;
        }
        .ganjeh-banner-item.hidden {
            display: none;
        }
        .ganjeh-banner-preview {
            width: 100%;
            aspect-ratio: 16/9;
            background: #f5f5f5;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ganjeh-banner-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .ganjeh-banner-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            color: #999;
        }
        .ganjeh-banner-placeholder .dashicons {
            font-size: 32px;
            width: 32px;
            height: 32px;
        }
        .ganjeh-banner-fields {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 10px;
        }
        .ganjeh-banner-fields input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
        }
        .ganjeh-banner-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        /* Toggle Switch */
        .ganjeh-toggle {
            position: relative;
            width: 50px;
            height: 26px;
        }
        .ganjeh-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .ganjeh-toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            border-radius: 26px;
            transition: 0.3s;
        }
        .ganjeh-toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            border-radius: 50%;
            transition: 0.3s;
        }
        .ganjeh-toggle input:checked + .ganjeh-toggle-slider {
            background-color: #C9A227;
        }
        .ganjeh-toggle input:checked + .ganjeh-toggle-slider:before {
            transform: translateX(24px);
        }
        @media (max-width: 768px) {
            .ganjeh-banners-grid {
                grid-template-columns: 1fr;
            }
            .ganjeh-banner-row {
                flex-direction: column;
            }
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Toggle section
        $('.ganjeh-toggle input').on('change', function() {
            var $content = $(this).closest('.ganjeh-banner-box').find('.ganjeh-banner-content');
            $content.css('opacity', this.checked ? '1' : '0.5');
        });

        // Layout change
        $('.ganjeh-layout-select').on('change', function() {
            var layout = $(this).val();
            var section = $(this).data('section');
            var $items = $('#banners-' + section + ' .ganjeh-banner-item');

            $items.each(function(index) {
                if (layout === 'single' && index > 0) {
                    $(this).addClass('hidden');
                } else if (layout === 'double' && index > 1) {
                    $(this).addClass('hidden');
                } else {
                    $(this).removeClass('hidden');
                }
            });
        });

        // Media uploader
        var mediaUploader;

        $(document).on('click', '.ganjeh-upload-banner', function(e) {
            e.preventDefault();
            var $item = $(this).closest('.ganjeh-banner-item');

            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            mediaUploader = wp.media({
                title: 'انتخاب تصویر بنر',
                button: { text: 'انتخاب' },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $item.find('.banner-image-input').val(attachment.url);
                $item.find('.ganjeh-banner-preview').html('<img src="' + attachment.url + '" alt="">');
                $item.find('.ganjeh-remove-banner').show();
            });

            mediaUploader.open();
        });

        // Remove banner
        $(document).on('click', '.ganjeh-remove-banner', function(e) {
            e.preventDefault();
            var $item = $(this).closest('.ganjeh-banner-item');
            var index = $item.data('index');

            $item.find('.banner-image-input').val('');
            $item.find('.ganjeh-banner-preview').html(
                '<div class="ganjeh-banner-placeholder">' +
                '<span class="dashicons dashicons-format-image"></span>' +
                '<span>بنر ' + (index + 1) + '</span>' +
                '</div>'
            );
            $(this).hide();
        });
    });
    </script>
    <?php
}

/**
 * Save banner settings
 */
function ganjeh_save_banner_settings($post) {
    $banners = ganjeh_get_default_banners();

    if (isset($post['banners']) && is_array($post['banners'])) {
        foreach ($post['banners'] as $section_id => $section_data) {
            if (isset($banners[$section_id])) {
                $banners[$section_id]['enabled'] = isset($section_data['enabled']);
                $banners[$section_id]['layout'] = sanitize_text_field($section_data['layout'] ?? 'single');
                $banners[$section_id]['position'] = sanitize_text_field($section_data['position'] ?? 'after_slider');

                if (isset($section_data['banners']) && is_array($section_data['banners'])) {
                    $banners[$section_id]['banners'] = [];
                    foreach ($section_data['banners'] as $banner) {
                        $banners[$section_id]['banners'][] = [
                            'image' => esc_url_raw($banner['image'] ?? ''),
                            'link' => esc_url_raw($banner['link'] ?? ''),
                            'alt' => sanitize_text_field($banner['alt'] ?? ''),
                        ];
                    }
                }
            }
        }
    }

    update_option('ganjeh_banners', $banners);
    return $banners;
}

/**
 * Register banner settings page
 */
function ganjeh_register_banner_page() {
    add_submenu_page(
        'dst-website-settings',
        'مدیریت بنرها',
        'بنرها',
        'manage_options',
        'ganjeh-banners',
        'ganjeh_render_banner_settings_page'
    );
}
add_action('admin_menu', 'ganjeh_register_banner_page', 10001);
