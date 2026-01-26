<?php
/**
 * Banner Slider Settings for Homepage
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get banner slider settings
 */
function ganjeh_get_banner_settings() {
    return get_option('ganjeh_banner_slider', [
        'enabled' => false,
        'position' => 'after_categories',
        'autoplay' => true,
        'banners' => []
    ]);
}

/**
 * Render banner slider at specific position
 */
function ganjeh_render_banners_at_position($position) {
    $settings = ganjeh_get_banner_settings();
    if (!empty($settings['enabled']) && $settings['position'] === $position) {
        ganjeh_render_banner_slider();
    }
}

/**
 * Render banner slider
 */
function ganjeh_render_banner_slider() {
    $settings = ganjeh_get_banner_settings();

    if (!$settings['enabled'] || empty($settings['banners'])) {
        return;
    }

    // Filter out empty banners
    $banners = array_filter($settings['banners'], function($item) {
        return !empty($item['image']);
    });

    if (empty($banners)) {
        return;
    }

    $autoplay = $settings['autoplay'] ? 'true' : 'false';
    ?>
    <section class="ganjeh-banner-slider-section px-4 py-4">
        <div class="swiper banner-slider" dir="rtl" data-autoplay="<?php echo $autoplay; ?>">
            <div class="swiper-wrapper">
                <?php foreach ($banners as $banner) : ?>
                    <div class="swiper-slide">
                        <a href="<?php echo esc_url($banner['link'] ?: '#'); ?>" class="block">
                            <div class="banner-card">
                                <img
                                    src="<?php echo esc_url($banner['image']); ?>"
                                    alt="<?php echo esc_attr($banner['alt'] ?: 'بنر'); ?>"
                                    class="banner-image"
                                    loading="lazy"
                                >
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($banners) > 1) : ?>
                <div class="swiper-pagination banner-pagination"></div>
            <?php endif; ?>
        </div>
    </section>

    <style>
    .banner-slider {
        border-radius: 16px;
        overflow: visible;
    }
    .banner-slider .swiper-slide {
        border-radius: 16px;
        overflow: hidden;
    }
    .banner-slider .banner-card {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    .banner-slider .banner-image {
        width: 100%;
        height: auto;
        display: block;
        border-radius: 16px;
    }
    .banner-slider .banner-pagination {
        position: relative;
        bottom: auto;
        margin-top: 12px;
        display: flex;
        justify-content: center;
        gap: 6px;
    }
    .banner-slider .swiper-pagination-bullet {
        width: 8px;
        height: 8px;
        background: #d1d5db;
        opacity: 1;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    .banner-slider .swiper-pagination-bullet-active {
        width: 24px;
        background: var(--color-primary, #C9A227);
        border-radius: 4px;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var bannerSlider = document.querySelector('.banner-slider');
        if (bannerSlider && typeof Swiper !== 'undefined') {
            var autoplay = bannerSlider.dataset.autoplay === 'true';
            new Swiper('.banner-slider', {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: true,
                autoplay: autoplay ? { delay: 4000, disableOnInteraction: false } : false,
                pagination: {
                    el: '.banner-pagination',
                    clickable: true
                }
            });
        }
    });
    </script>
    <?php
}

/**
 * Admin page for banner settings
 */
function ganjeh_render_banner_settings_page() {
    $settings = ganjeh_get_banner_settings();

    // Save settings
    if (isset($_POST['ganjeh_save_banners'])) {
        check_admin_referer('ganjeh_banner_settings_nonce');
        $settings = ganjeh_save_banner_settings($_POST);
        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات بنرها ذخیره شد!</p></div>';
    }

    $positions = [
        'after_slider' => 'بعد از اسلایدر اصلی',
        'after_categories' => 'بعد از دسته‌بندی‌ها',
        'after_featured' => 'بعد از محصولات ویژه',
        'after_sale' => 'بعد از تخفیف‌ها',
        'after_new' => 'بعد از محصولات جدید',
    ];

    $banners = $settings['banners'] ?? [];
    ?>
    <div class="wrap ganjeh-banner-settings">
        <h1>مدیریت بنرها</h1>
        <p class="description">اسلایدر بنرهای تبلیغاتی صفحه اصلی را مدیریت کنید</p>

        <form method="post" action="" id="banner-form">
            <?php wp_nonce_field('ganjeh_banner_settings_nonce'); ?>

            <div class="ganjeh-banner-box">
                <div class="ganjeh-banner-header">
                    <label class="ganjeh-toggle">
                        <input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled']); ?>>
                        <span class="ganjeh-toggle-slider"></span>
                    </label>
                    <h2>اسلایدر بنرها</h2>
                </div>

                <div class="ganjeh-banner-content">
                    <div class="ganjeh-banner-row">
                        <div class="ganjeh-banner-field">
                            <label>موقعیت نمایش:</label>
                            <select name="position">
                                <?php foreach ($positions as $pos_key => $pos_label) : ?>
                                    <option value="<?php echo $pos_key; ?>" <?php selected($settings['position'], $pos_key); ?>>
                                        <?php echo $pos_label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="ganjeh-banner-field">
                            <label>
                                <input type="checkbox" name="autoplay" value="1" <?php checked($settings['autoplay'] ?? true); ?>>
                                پخش خودکار اسلایدها
                            </label>
                        </div>
                    </div>

                    <h3>بنرها</h3>
                    <div id="banners-container">
                        <?php
                        if (!empty($banners)) :
                            foreach ($banners as $i => $banner) :
                        ?>
                            <div class="ganjeh-banner-item" data-index="<?php echo $i; ?>">
                                <div class="banner-item-header">
                                    <span class="banner-number">بنر <?php echo $i + 1; ?></span>
                                    <button type="button" class="button-link remove-banner">حذف</button>
                                </div>

                                <div class="ganjeh-banner-preview">
                                    <?php if (!empty($banner['image'])) : ?>
                                        <img src="<?php echo esc_url($banner['image']); ?>" alt="">
                                    <?php else : ?>
                                        <div class="ganjeh-banner-placeholder">
                                            <span class="dashicons dashicons-format-image"></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <input type="hidden" name="banners[<?php echo $i; ?>][image]" value="<?php echo esc_attr($banner['image']); ?>" class="banner-image-input">

                                <div class="ganjeh-banner-fields">
                                    <input type="text" name="banners[<?php echo $i; ?>][link]" value="<?php echo esc_attr($banner['link']); ?>" placeholder="لینک بنر (اختیاری)">
                                    <input type="text" name="banners[<?php echo $i; ?>][alt]" value="<?php echo esc_attr($banner['alt']); ?>" placeholder="متن جایگزین (alt)">
                                </div>

                                <div class="ganjeh-banner-actions">
                                    <button type="button" class="button upload-banner">انتخاب تصویر</button>
                                </div>
                            </div>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </div>

                    <button type="button" id="add-banner" class="button button-secondary">
                        + افزودن بنر جدید
                    </button>
                </div>
            </div>

            <p class="submit">
                <button type="submit" name="ganjeh_save_banners" class="button button-primary button-large">
                    ذخیره تنظیمات
                </button>
            </p>
        </form>
    </div>

    <style>
        .ganjeh-banner-settings { max-width: 900px; }
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
        .ganjeh-banner-header h2 { margin: 0; font-size: 16px; }
        .ganjeh-banner-content { padding: 20px; }
        .ganjeh-banner-content h3 {
            margin: 20px 0 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .ganjeh-banner-row {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .ganjeh-banner-field { flex: 1; }
        .ganjeh-banner-field label { display: block; margin-bottom: 5px; font-weight: 500; }
        .ganjeh-banner-field select { width: 100%; padding: 8px 12px; border-radius: 6px; }

        #banners-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .ganjeh-banner-item {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 15px;
            background: #fafafa;
            transition: all 0.3s;
        }
        .ganjeh-banner-item:hover { border-color: #C9A227; }
        .banner-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .banner-number { font-weight: 600; color: #374151; }
        .remove-banner { color: #ef4444 !important; }
        .ganjeh-banner-preview {
            width: 100%;
            aspect-ratio: 16/9;
            background: #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ganjeh-banner-preview img { width: 100%; height: 100%; object-fit: cover; }
        .ganjeh-banner-placeholder { color: #9ca3af; }
        .ganjeh-banner-placeholder .dashicons { font-size: 40px; width: 40px; height: 40px; }
        .ganjeh-banner-fields { display: flex; flex-direction: column; gap: 8px; margin-bottom: 10px; }
        .ganjeh-banner-fields input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
        }
        .ganjeh-banner-actions { text-align: center; }

        #add-banner {
            width: 100%;
            padding: 15px;
            border: 2px dashed #d1d5db;
            background: transparent;
            border-radius: 12px;
            font-size: 14px;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.3s;
        }
        #add-banner:hover { border-color: #C9A227; color: #C9A227; background: #fffbeb; }

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
        .ganjeh-toggle input:checked + .ganjeh-toggle-slider { background-color: #C9A227; }
        .ganjeh-toggle input:checked + .ganjeh-toggle-slider:before { transform: translateX(24px); }

        @media (max-width: 600px) {
            #banners-container { grid-template-columns: 1fr; }
            .ganjeh-banner-row { flex-direction: column; }
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        var bannerIndex = <?php echo count($banners); ?>;

        // Add new banner
        $('#add-banner').on('click', function() {
            var html = `
                <div class="ganjeh-banner-item" data-index="${bannerIndex}">
                    <div class="banner-item-header">
                        <span class="banner-number">بنر ${bannerIndex + 1}</span>
                        <button type="button" class="button-link remove-banner">حذف</button>
                    </div>
                    <div class="ganjeh-banner-preview">
                        <div class="ganjeh-banner-placeholder">
                            <span class="dashicons dashicons-format-image"></span>
                        </div>
                    </div>
                    <input type="hidden" name="banners[${bannerIndex}][image]" value="" class="banner-image-input">
                    <div class="ganjeh-banner-fields">
                        <input type="text" name="banners[${bannerIndex}][link]" value="" placeholder="لینک بنر (اختیاری)">
                        <input type="text" name="banners[${bannerIndex}][alt]" value="" placeholder="متن جایگزین (alt)">
                    </div>
                    <div class="ganjeh-banner-actions">
                        <button type="button" class="button upload-banner">انتخاب تصویر</button>
                    </div>
                </div>
            `;
            $('#banners-container').append(html);
            bannerIndex++;
        });

        // Remove banner
        $(document).on('click', '.remove-banner', function() {
            $(this).closest('.ganjeh-banner-item').remove();
            updateBannerNumbers();
        });

        function updateBannerNumbers() {
            $('.ganjeh-banner-item').each(function(i) {
                $(this).find('.banner-number').text('بنر ' + (i + 1));
            });
        }

        // Upload banner image
        $(document).on('click', '.upload-banner', function(e) {
            e.preventDefault();
            var $item = $(this).closest('.ganjeh-banner-item');

            var frame = wp.media({
                title: 'انتخاب تصویر بنر',
                button: { text: 'انتخاب' },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $item.find('.banner-image-input').val(attachment.url);
                $item.find('.ganjeh-banner-preview').html('<img src="' + attachment.url + '" alt="">');
            });

            frame.open();
        });
    });
    </script>
    <?php
}

/**
 * Save banner settings
 */
function ganjeh_save_banner_settings($post) {
    $settings = [
        'enabled' => isset($post['enabled']),
        'position' => sanitize_text_field($post['position'] ?? 'after_categories'),
        'autoplay' => isset($post['autoplay']),
        'banners' => []
    ];

    if (isset($post['banners']) && is_array($post['banners'])) {
        foreach ($post['banners'] as $banner) {
            if (!empty($banner['image'])) {
                $settings['banners'][] = [
                    'image' => esc_url_raw($banner['image']),
                    'link' => esc_url_raw($banner['link'] ?? ''),
                    'alt' => sanitize_text_field($banner['alt'] ?? ''),
                ];
            }
        }
    }

    update_option('ganjeh_banner_slider', $settings);
    return $settings;
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

/**
 * Enqueue media library for banner page
 */
function ganjeh_banner_admin_scripts($hook) {
    if (strpos($hook, 'ganjeh-banners') === false) {
        return;
    }
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'ganjeh_banner_admin_scripts');
