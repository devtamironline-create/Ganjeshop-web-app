<?php
/**
 * Category Slider Settings (in category edit page)
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add slider fields to category edit page
 */
function ganjeh_category_slider_fields($term) {
    $term_id = $term->term_id;
    $slider_images = get_term_meta($term_id, 'ganjeh_category_slider', true) ?: [];
    ?>
    <tr class="form-field">
        <th scope="row"><label>اسلایدر دسته‌بندی</label></th>
        <td>
            <div id="category-slider-container">
                <?php if (!empty($slider_images)) : ?>
                    <?php foreach ($slider_images as $i => $slide) : ?>
                        <div class="category-slide-item" data-index="<?php echo $i; ?>">
                            <div class="slide-preview">
                                <?php if (!empty($slide['image'])) : ?>
                                    <img src="<?php echo esc_url($slide['image']); ?>" alt="">
                                <?php else : ?>
                                    <span class="dashicons dashicons-format-image"></span>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="category_slider[<?php echo $i; ?>][image]" value="<?php echo esc_attr($slide['image'] ?? ''); ?>" class="slide-image-input">
                            <input type="text" name="category_slider[<?php echo $i; ?>][link]" value="<?php echo esc_attr($slide['link'] ?? ''); ?>" placeholder="لینک (اختیاری)" class="slide-link-input">
                            <button type="button" class="button upload-slide-btn">انتخاب تصویر</button>
                            <button type="button" class="button remove-slide-btn">حذف</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" id="add-category-slide" class="button">+ افزودن اسلاید</button>
            <p class="description">تصاویر اسلایدر برای نمایش در بالای صفحه دسته‌بندی</p>

            <style>
                #category-slider-container { margin-bottom: 10px; }
                .category-slide-item {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    padding: 10px;
                    background: #f9f9f9;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    margin-bottom: 8px;
                }
                .slide-preview {
                    width: 80px;
                    height: 50px;
                    background: #e5e5e5;
                    border-radius: 4px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    overflow: hidden;
                }
                .slide-preview img { width: 100%; height: 100%; object-fit: cover; }
                .slide-preview .dashicons { color: #999; font-size: 24px; }
                .slide-link-input { flex: 1; }
                .remove-slide-btn { color: #a00; }
            </style>

            <script>
            jQuery(document).ready(function($) {
                var slideIndex = <?php echo count($slider_images); ?>;

                $('#add-category-slide').on('click', function() {
                    var html = `
                        <div class="category-slide-item" data-index="${slideIndex}">
                            <div class="slide-preview">
                                <span class="dashicons dashicons-format-image"></span>
                            </div>
                            <input type="hidden" name="category_slider[${slideIndex}][image]" value="" class="slide-image-input">
                            <input type="text" name="category_slider[${slideIndex}][link]" value="" placeholder="لینک (اختیاری)" class="slide-link-input">
                            <button type="button" class="button upload-slide-btn">انتخاب تصویر</button>
                            <button type="button" class="button remove-slide-btn">حذف</button>
                        </div>
                    `;
                    $('#category-slider-container').append(html);
                    slideIndex++;
                });

                $(document).on('click', '.remove-slide-btn', function() {
                    $(this).closest('.category-slide-item').remove();
                });

                $(document).on('click', '.upload-slide-btn', function(e) {
                    e.preventDefault();
                    var $item = $(this).closest('.category-slide-item');

                    var frame = wp.media({
                        title: 'انتخاب تصویر اسلاید',
                        button: { text: 'انتخاب' },
                        multiple: false
                    });

                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        $item.find('.slide-image-input').val(attachment.url);
                        $item.find('.slide-preview').html('<img src="' + attachment.url + '" alt="">');
                    });

                    frame.open();
                });
            });
            </script>
        </td>
    </tr>
    <?php
}
add_action('product_cat_edit_form_fields', 'ganjeh_category_slider_fields', 10);

/**
 * Save category slider
 */
function ganjeh_save_category_slider($term_id) {
    if (isset($_POST['category_slider']) && is_array($_POST['category_slider'])) {
        $slides = [];
        foreach ($_POST['category_slider'] as $slide) {
            if (!empty($slide['image'])) {
                $slides[] = [
                    'image' => esc_url_raw($slide['image']),
                    'link' => esc_url_raw($slide['link'] ?? ''),
                ];
            }
        }
        update_term_meta($term_id, 'ganjeh_category_slider', $slides);
    } else {
        delete_term_meta($term_id, 'ganjeh_category_slider');
    }
}
add_action('edited_product_cat', 'ganjeh_save_category_slider');

/**
 * Enqueue media on category edit page
 */
function ganjeh_category_slider_admin_scripts($hook) {
    if ($hook === 'term.php' || $hook === 'edit-tags.php') {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'ganjeh_category_slider_admin_scripts');

/**
 * Get category slider
 */
function ganjeh_get_category_slider($term_id) {
    return get_term_meta($term_id, 'ganjeh_category_slider', true) ?: [];
}

/**
 * Render category slider
 */
function ganjeh_render_category_slider($term_id) {
    $slides = ganjeh_get_category_slider($term_id);

    if (empty($slides)) {
        return;
    }
    ?>
    <div class="category-hero-slider swiper" dir="rtl">
        <div class="swiper-wrapper">
            <?php foreach ($slides as $slide) : ?>
                <div class="swiper-slide">
                    <?php if (!empty($slide['link'])) : ?>
                        <a href="<?php echo esc_url($slide['link']); ?>">
                            <img src="<?php echo esc_url($slide['image']); ?>" alt="" loading="lazy">
                        </a>
                    <?php else : ?>
                        <img src="<?php echo esc_url($slide['image']); ?>" alt="" loading="lazy">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($slides) > 1) : ?>
            <div class="swiper-pagination category-slider-pagination"></div>
        <?php endif; ?>
    </div>

    <style>
    .category-hero-slider {
        margin: 0 16px 16px;
        border-radius: 16px;
        overflow: hidden;
    }
    .category-hero-slider .swiper-slide img {
        width: 100%;
        height: auto;
        display: block;
        border-radius: 16px;
    }
    .category-slider-pagination {
        position: relative;
        margin-top: 10px;
        display: flex;
        justify-content: center;
        gap: 6px;
    }
    .category-slider-pagination .swiper-pagination-bullet {
        width: 8px;
        height: 8px;
        background: #d1d5db;
        opacity: 1;
        border-radius: 4px;
    }
    .category-slider-pagination .swiper-pagination-bullet-active {
        width: 20px;
        background: var(--color-primary, #4CB050);
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swiper !== 'undefined') {
            new Swiper('.category-hero-slider', {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: true,
                autoplay: { delay: 4000, disableOnInteraction: false },
                pagination: { el: '.category-slider-pagination', clickable: true }
            });
        }
    });
    </script>
    <?php
}
