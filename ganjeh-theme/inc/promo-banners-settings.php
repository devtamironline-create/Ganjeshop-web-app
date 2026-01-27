<?php
/**
 * Promotional Banners Settings
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get promotional banners settings
 */
function ganjeh_get_promo_banners_settings() {
    return get_option('ganjeh_promo_banners', [
        'enabled' => false,
        'title' => 'پیشنهادهای شگفت‌انگیز',
        'banners' => []
    ]);
}

/**
 * Render promotional banners carousel
 */
function ganjeh_render_promo_banners() {
    $settings = ganjeh_get_promo_banners_settings();

    if (empty($settings['enabled']) || empty($settings['banners'])) {
        return;
    }

    // Filter out empty banners
    $banners = array_filter($settings['banners'], function($item) {
        return !empty($item['image']);
    });

    if (empty($banners)) {
        return;
    }
    ?>
    <section class="promo-banners-section py-4 overflow-hidden">
        <div class="px-4 flex items-center justify-between mb-3">
            <h2 class="text-base font-bold text-gray-800"><?php echo esc_html($settings['title']); ?></h2>
        </div>

        <div class="swiper promo-banners-swiper" dir="rtl">
            <div class="swiper-wrapper">
                <?php foreach ($banners as $banner) : ?>
                    <div class="swiper-slide">
                        <a href="<?php echo esc_url($banner['link'] ?: '#'); ?>" class="promo-card">
                            <div class="promo-image">
                                <img src="<?php echo esc_url($banner['image']); ?>"
                                     alt="<?php echo esc_attr($banner['title'] ?: 'پیشنهاد ویژه'); ?>"
                                     loading="lazy">
                                <?php if (!empty($banner['badge'])) : ?>
                                    <span class="promo-badge"><?php echo esc_html($banner['badge']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="promo-content">
                                <?php if (!empty($banner['title'])) : ?>
                                    <h3 class="promo-title"><?php echo esc_html($banner['title']); ?></h3>
                                <?php endif; ?>
                                <?php if (!empty($banner['subtitle'])) : ?>
                                    <p class="promo-subtitle"><?php echo esc_html($banner['subtitle']); ?></p>
                                <?php endif; ?>
                                <span class="promo-btn">مشاهده</span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <style>
    .promo-banners-swiper {
        padding: 0 16px;
        overflow: hidden;
    }

    .promo-banners-swiper .swiper-slide {
        width: 200px;
        flex-shrink: 0;
    }

    .promo-card {
        display: block;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-radius: 16px;
        overflow: hidden;
        text-decoration: none;
        transition: transform 0.2s;
    }

    .promo-card:hover {
        transform: translateY(-2px);
    }

    .promo-image {
        position: relative;
        width: 100%;
        aspect-ratio: 4/3;
        overflow: hidden;
    }

    .promo-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .promo-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #ef4444;
        color: white;
        font-size: 10px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 8px;
    }

    .promo-content {
        padding: 12px;
        text-align: center;
    }

    .promo-title {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .promo-subtitle {
        font-size: 14px;
        font-weight: 700;
        color: #ef4444;
        margin-bottom: 8px;
    }

    .promo-btn {
        display: inline-block;
        background: white;
        color: #374151;
        font-size: 11px;
        font-weight: 600;
        padding: 6px 16px;
        border-radius: 20px;
        border: 1px solid #e5e7eb;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swiper !== 'undefined') {
            new Swiper('.promo-banners-swiper', {
                slidesPerView: 'auto',
                spaceBetween: 12,
                freeMode: true,
                grabCursor: true
            });
        }
    });
    </script>
    <?php
}

/**
 * Admin page for promotional banners
 */
function ganjeh_render_promo_banners_page() {
    $settings = ganjeh_get_promo_banners_settings();

    // Save settings
    if (isset($_POST['ganjeh_save_promo_banners'])) {
        check_admin_referer('ganjeh_promo_banners_nonce');
        $settings = ganjeh_save_promo_banners_settings($_POST);
        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات پیشنهادات ویژه ذخیره شد!</p></div>';
    }

    $banners = $settings['banners'] ?? [];
    ?>
    <div class="wrap ganjeh-promo-settings">
        <h1>پیشنهادات ویژه</h1>
        <p class="description">کاروسل پیشنهادات ویژه در صفحه اصلی - هر کارت شامل عکس، عنوان و لینک</p>

        <form method="post" action="">
            <?php wp_nonce_field('ganjeh_promo_banners_nonce'); ?>

            <div class="ganjeh-promo-box">
                <div class="ganjeh-promo-header">
                    <label class="ganjeh-toggle">
                        <input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled']); ?>>
                        <span class="ganjeh-toggle-slider"></span>
                    </label>
                    <h2>نمایش پیشنهادات ویژه</h2>
                </div>

                <div class="ganjeh-promo-content">
                    <div class="ganjeh-field" style="max-width: 400px;">
                        <label>عنوان بخش:</label>
                        <input type="text" name="title" value="<?php echo esc_attr($settings['title']); ?>" placeholder="پیشنهادهای شگفت‌انگیز">
                    </div>

                    <h3>کارت‌های پیشنهاد</h3>
                    <div id="promo-banners-container">
                        <?php
                        if (!empty($banners)) :
                            foreach ($banners as $i => $banner) :
                        ?>
                            <div class="ganjeh-promo-item" data-index="<?php echo $i; ?>">
                                <div class="promo-item-header">
                                    <span class="promo-number">کارت <?php echo $i + 1; ?></span>
                                    <button type="button" class="button-link remove-promo">حذف</button>
                                </div>

                                <div class="promo-item-content">
                                    <div class="promo-image-col">
                                        <div class="ganjeh-promo-preview">
                                            <?php if (!empty($banner['image'])) : ?>
                                                <img src="<?php echo esc_url($banner['image']); ?>" alt="">
                                            <?php else : ?>
                                                <div class="ganjeh-promo-placeholder">
                                                    <span class="dashicons dashicons-format-image"></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="hidden" name="banners[<?php echo $i; ?>][image]" value="<?php echo esc_attr($banner['image']); ?>" class="promo-image-input">
                                        <button type="button" class="button upload-promo">انتخاب تصویر</button>
                                    </div>

                                    <div class="promo-fields-col">
                                        <div class="ganjeh-field">
                                            <label>عنوان کارت:</label>
                                            <input type="text" name="banners[<?php echo $i; ?>][title]" value="<?php echo esc_attr($banner['title'] ?? ''); ?>" placeholder="مثلا: محصولات ظرفشویی">
                                        </div>
                                        <div class="ganjeh-field">
                                            <label>زیرعنوان (مثلا تخفیف):</label>
                                            <input type="text" name="banners[<?php echo $i; ?>][subtitle]" value="<?php echo esc_attr($banner['subtitle'] ?? ''); ?>" placeholder="مثلا: تا ۵۰٪ تخفیف">
                                        </div>
                                        <div class="ganjeh-field">
                                            <label>برچسب گوشه:</label>
                                            <input type="text" name="banners[<?php echo $i; ?>][badge]" value="<?php echo esc_attr($banner['badge'] ?? ''); ?>" placeholder="مثلا: جدید، پرفروش">
                                        </div>
                                        <div class="ganjeh-field">
                                            <label>لینک:</label>
                                            <input type="url" name="banners[<?php echo $i; ?>][link]" value="<?php echo esc_attr($banner['link'] ?? ''); ?>" placeholder="https://...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </div>

                    <button type="button" id="add-promo" class="button button-secondary">
                        + افزودن کارت جدید
                    </button>
                </div>
            </div>

            <p class="submit">
                <button type="submit" name="ganjeh_save_promo_banners" class="button button-primary button-large">
                    ذخیره تنظیمات
                </button>
            </p>
        </form>
    </div>

    <style>
        .ganjeh-promo-settings { max-width: 1000px; }
        .ganjeh-promo-box {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            margin: 20px 0;
            overflow: hidden;
        }
        .ganjeh-promo-header {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        .ganjeh-promo-header h2 { margin: 0; font-size: 16px; }
        .ganjeh-promo-content { padding: 20px; }
        .ganjeh-promo-content h3 {
            margin: 25px 0 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .ganjeh-field { margin-bottom: 12px; }
        .ganjeh-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #374151;
            font-size: 13px;
        }
        .ganjeh-field input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
        }

        #promo-banners-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 15px;
        }
        .ganjeh-promo-item {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 15px;
            background: #fafafa;
        }
        .ganjeh-promo-item:hover { border-color: #4CB050; }
        .promo-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .promo-number { font-weight: 600; color: #374151; }
        .remove-promo { color: #ef4444 !important; }

        .promo-item-content {
            display: flex;
            gap: 20px;
        }
        .promo-image-col {
            width: 150px;
            flex-shrink: 0;
        }
        .promo-fields-col {
            flex: 1;
        }
        .ganjeh-promo-preview {
            width: 150px;
            height: 112px;
            background: #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ganjeh-promo-preview img { width: 100%; height: 100%; object-fit: cover; }
        .ganjeh-promo-placeholder { color: #9ca3af; }
        .ganjeh-promo-placeholder .dashicons { font-size: 40px; width: 40px; height: 40px; }

        #add-promo {
            width: 100%;
            padding: 15px;
            border: 2px dashed #d1d5db;
            background: transparent;
            border-radius: 12px;
            font-size: 14px;
            color: #6b7280;
            cursor: pointer;
        }
        #add-promo:hover { border-color: #4CB050; color: #4CB050; background: #f0fdf4; }

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
            .promo-item-content { flex-direction: column; }
            .promo-image-col { width: 100%; }
            .ganjeh-promo-preview { width: 100%; height: 150px; }
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        var promoIndex = <?php echo count($banners); ?>;

        // Add new promo card
        $('#add-promo').on('click', function() {
            var html = `
                <div class="ganjeh-promo-item" data-index="${promoIndex}">
                    <div class="promo-item-header">
                        <span class="promo-number">کارت ${promoIndex + 1}</span>
                        <button type="button" class="button-link remove-promo">حذف</button>
                    </div>
                    <div class="promo-item-content">
                        <div class="promo-image-col">
                            <div class="ganjeh-promo-preview">
                                <div class="ganjeh-promo-placeholder">
                                    <span class="dashicons dashicons-format-image"></span>
                                </div>
                            </div>
                            <input type="hidden" name="banners[${promoIndex}][image]" value="" class="promo-image-input">
                            <button type="button" class="button upload-promo">انتخاب تصویر</button>
                        </div>
                        <div class="promo-fields-col">
                            <div class="ganjeh-field">
                                <label>عنوان کارت:</label>
                                <input type="text" name="banners[${promoIndex}][title]" value="" placeholder="مثلا: محصولات ظرفشویی">
                            </div>
                            <div class="ganjeh-field">
                                <label>زیرعنوان (مثلا تخفیف):</label>
                                <input type="text" name="banners[${promoIndex}][subtitle]" value="" placeholder="مثلا: تا ۵۰٪ تخفیف">
                            </div>
                            <div class="ganjeh-field">
                                <label>برچسب گوشه:</label>
                                <input type="text" name="banners[${promoIndex}][badge]" value="" placeholder="مثلا: جدید، پرفروش">
                            </div>
                            <div class="ganjeh-field">
                                <label>لینک:</label>
                                <input type="url" name="banners[${promoIndex}][link]" value="" placeholder="https://...">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#promo-banners-container').append(html);
            promoIndex++;
        });

        // Remove promo card
        $(document).on('click', '.remove-promo', function() {
            $(this).closest('.ganjeh-promo-item').remove();
            updatePromoNumbers();
        });

        function updatePromoNumbers() {
            $('.ganjeh-promo-item').each(function(i) {
                $(this).find('.promo-number').text('کارت ' + (i + 1));
            });
        }

        // Upload promo image
        $(document).on('click', '.upload-promo', function(e) {
            e.preventDefault();
            var $item = $(this).closest('.promo-image-col');

            var frame = wp.media({
                title: 'انتخاب تصویر',
                button: { text: 'انتخاب' },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $item.find('.promo-image-input').val(attachment.url);
                $item.find('.ganjeh-promo-preview').html('<img src="' + attachment.url + '" alt="">');
            });

            frame.open();
        });
    });
    </script>
    <?php
}

/**
 * Save promotional banners settings
 */
function ganjeh_save_promo_banners_settings($post) {
    $settings = [
        'enabled' => isset($post['enabled']),
        'title' => sanitize_text_field($post['title'] ?? 'پیشنهادهای شگفت‌انگیز'),
        'banners' => []
    ];

    if (isset($post['banners']) && is_array($post['banners'])) {
        foreach ($post['banners'] as $banner) {
            if (!empty($banner['image'])) {
                $settings['banners'][] = [
                    'image' => esc_url_raw($banner['image']),
                    'title' => sanitize_text_field($banner['title'] ?? ''),
                    'subtitle' => sanitize_text_field($banner['subtitle'] ?? ''),
                    'badge' => sanitize_text_field($banner['badge'] ?? ''),
                    'link' => esc_url_raw($banner['link'] ?? ''),
                ];
            }
        }
    }

    update_option('ganjeh_promo_banners', $settings);
    return $settings;
}

/**
 * Register promotional banners page
 */
function ganjeh_register_promo_banners_page() {
    add_submenu_page(
        'dst-website-settings',
        'پیشنهادات ویژه',
        'پیشنهادات ویژه',
        'manage_options',
        'ganjeh-promo-banners',
        'ganjeh_render_promo_banners_page'
    );
}
add_action('admin_menu', 'ganjeh_register_promo_banners_page', 10004);

/**
 * Enqueue media for promo banners page
 */
function ganjeh_promo_admin_scripts($hook) {
    if (strpos($hook, 'ganjeh-promo-banners') === false) {
        return;
    }
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'ganjeh_promo_admin_scripts');
