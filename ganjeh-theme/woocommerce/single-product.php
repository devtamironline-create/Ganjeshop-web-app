<?php
/**
 * Single Product Template
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

global $product;

if (!$product || !is_a($product, 'WC_Product')) {
    $product = wc_get_product(get_the_ID());
}

if (!$product) {
    get_template_part('template-parts/content', 'none');
    get_footer();
    return;
}

$product_id = $product->get_id();
$gallery_ids = $product->get_gallery_image_ids();
$main_image_id = $product->get_image_id();
$all_images = $main_image_id ? array_merge([$main_image_id], $gallery_ids) : $gallery_ids;
$is_variable = $product->is_type('variable');
$terms = get_the_terms($product_id, 'product_cat');
?>

<main id="main-content" class="single-product-page pb-32">

    <!-- Header -->
    <header class="product-header">
        <div class="product-header-right">
            <a href="javascript:history.back()" class="header-icon-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <h1 class="header-title"><?php _e('جزئیات محصول', 'ganjeh'); ?></h1>
        </div>
        <button type="button" class="header-icon-btn" aria-label="<?php _e('اشتراک‌گذاری', 'ganjeh'); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
        </button>
    </header>

    <!-- Breadcrumb -->
    <nav class="product-breadcrumb">
        <a href="<?php echo home_url('/'); ?>" class="breadcrumb-home">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
        </a>
        <?php if ($terms && !is_wp_error($terms)) :
            $term = $terms[0];
            $ancestors = get_ancestors($term->term_id, 'product_cat');
            $ancestors = array_reverse($ancestors);
        ?>
            <span class="breadcrumb-sep">|</span>
            <?php foreach ($ancestors as $ancestor_id) :
                $ancestor = get_term($ancestor_id, 'product_cat');
            ?>
                <a href="<?php echo get_term_link($ancestor); ?>"><?php echo esc_html($ancestor->name); ?></a>
                <span class="breadcrumb-sep">|</span>
            <?php endforeach; ?>
            <a href="<?php echo get_term_link($term); ?>"><?php echo esc_html($term->name); ?></a>
            <span class="breadcrumb-sep">|</span>
        <?php endif; ?>
        <span class="breadcrumb-current"><?php echo wp_trim_words($product->get_name(), 4); ?></span>
    </nav>

    <!-- Product Images Gallery -->
    <div class="product-gallery-wrapper" x-data="{ lightbox: false, currentImage: 0 }">
        <?php if (!empty($all_images)) :
            $main_image = $all_images[0];
            $thumbnails = array_slice($all_images, 1);
            $total_images = count($all_images);
            $extra_count = $total_images > 3 ? $total_images - 3 : 0;
        ?>
            <div class="gallery-grid">
                <!-- Thumbnails (Left Side) -->
                <?php if (count($all_images) > 1) : ?>
                <div class="gallery-thumbs">
                    <?php
                    $thumb_images = array_slice($all_images, 1, 2);
                    foreach ($thumb_images as $index => $image_id) :
                        $is_last = ($index === 1 && $extra_count > 0);
                    ?>
                        <div class="thumb-item <?php echo $is_last ? 'has-more' : ''; ?>" @click="currentImage = <?php echo $index + 1; ?>; lightbox = true">
                            <?php echo wp_get_attachment_image($image_id, 'thumbnail', false, ['class' => 'thumb-image']); ?>
                            <?php if ($is_last) : ?>
                                <div class="thumb-more"><?php _e('مشاهده بیشتر', 'ganjeh'); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Main Image (Right Side) -->
                <div class="gallery-main" @click="currentImage = 0; lightbox = true">
                    <?php echo wp_get_attachment_image($main_image, 'ganjeh-product-large', false, ['class' => 'main-image']); ?>
                </div>
            </div>

            <!-- Lightbox -->
            <div class="lightbox-overlay" x-show="lightbox" x-cloak @click.self="lightbox = false">
                <div class="lightbox-content">
                    <button class="lightbox-close" @click="lightbox = false">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <div class="lightbox-images">
                        <?php foreach ($all_images as $idx => $image_id) :
                            $img_url = wp_get_attachment_image_url($image_id, 'large');
                        ?>
                            <div class="lightbox-slide" x-show="currentImage === <?php echo $idx; ?>">
                                <img src="<?php echo esc_url($img_url); ?>" alt="">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($all_images) > 1) : ?>
                    <div class="lightbox-nav">
                        <button class="lightbox-btn" @click="currentImage = currentImage > 0 ? currentImage - 1 : <?php echo count($all_images) - 1; ?>">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <span class="lightbox-counter" x-text="(currentImage + 1) + ' / <?php echo count($all_images); ?>'"></span>
                        <button class="lightbox-btn" @click="currentImage = currentImage < <?php echo count($all_images) - 1; ?> ? currentImage + 1 : 0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else : ?>
            <div class="gallery-placeholder">
                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        <?php endif; ?>
    </div>

    <!-- Product Info -->
    <div class="product-info">
        <!-- Delivery Badge (if enabled for this product) -->
        <?php
        $delivery_type = get_post_meta($product_id, '_ganjeh_delivery_type', true);
        if ($delivery_type) :
            $delivery_labels = [
                'in_person' => __('تحویل حضوری', 'ganjeh'),
                'courier' => __('ارسال با پیک', 'ganjeh'),
                'post' => __('ارسال پستی', 'ganjeh'),
                'express' => __('ارسال فوری', 'ganjeh'),
            ];
            $delivery_label = $delivery_labels[$delivery_type] ?? $delivery_type;
        ?>
            <div class="delivery-badge">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span><?php echo esc_html($delivery_label); ?></span>
            </div>
        <?php endif; ?>

        <!-- Category Links -->
        <?php if ($terms && !is_wp_error($terms)) : ?>
            <div class="product-categories">
                <?php
                $cat_links = [];
                foreach (array_slice($terms, 0, 2) as $cat) {
                    $cat_links[] = '<a href="' . get_term_link($cat) . '">' . esc_html($cat->name) . '</a>';
                }
                echo implode(' <span class="cat-sep">|</span> ', $cat_links);
                ?>
            </div>
        <?php endif; ?>

        <!-- Title -->
        <h1 class="product-title"><?php the_title(); ?></h1>
    </div>

    <!-- Short Description -->
    <?php if ($product->get_short_description()) : ?>
        <div class="product-section">
            <h2 class="section-title"><?php _e('توضیحات کوتاه', 'ganjeh'); ?></h2>
            <div class="product-description">
                <?php echo wp_kses_post($product->get_short_description()); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Full Description -->
    <?php if ($product->get_description()) : ?>
        <div class="product-section" x-data="{ showFull: false, needsExpand: false }" x-init="$nextTick(() => { needsExpand = $refs.descContent.scrollHeight > 120 })">
            <h2 class="section-title"><?php _e('توضیحات محصول', 'ganjeh'); ?></h2>
            <div class="product-description" :class="{ 'expanded': showFull }" x-ref="descContent">
                <?php echo wp_kses_post($product->get_description()); ?>
            </div>
            <button type="button" class="show-more-btn" @click="showFull = !showFull" x-show="needsExpand">
                <span x-text="showFull ? 'مشاهده کمتر' : 'مشاهده بیشتر'"></span>
                <svg class="w-4 h-4" :class="{ 'rotate-180': showFull }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
    <?php endif; ?>

    <!-- Variations (for variable products) -->
    <?php if ($is_variable) :
        $available_variations = $product->get_available_variations();
        $variation_attributes = $product->get_variation_attributes();
    ?>
        <div class="product-variations" x-data="productVariations()" x-init="init()">
            <?php foreach ($variation_attributes as $attribute_name => $options) :
                $attribute_label = wc_attribute_label($attribute_name);
                // Use the original attribute name for matching with variations
                $attr_key = $attribute_name;
                $is_color = strpos(strtolower($attribute_name), 'color') !== false || strpos(strtolower($attribute_name), 'رنگ') !== false;
            ?>
                <div class="variation-group">
                    <h3 class="variation-label"><?php echo esc_html($attribute_label); ?></h3>
                    <div class="variation-options">
                        <?php foreach ($options as $option) :
                            $term_obj = get_term_by('slug', $option, $attribute_name);
                            $option_name = $term_obj ? $term_obj->name : $option;

                            // Get color code from term meta or use mapping
                            $color_code = '';
                            if ($is_color && $term_obj) {
                                $color_code = get_term_meta($term_obj->term_id, 'color_code', true);
                            }
                            if (!$color_code && $is_color) {
                                $color_map = [
                                    'آبی' => '#3b82f6', 'آبی روشن' => '#93c5fd', 'سبز' => '#22c55e',
                                    'قرمز' => '#ef4444', 'زرد' => '#eab308', 'نارنجی' => '#f97316',
                                    'مشکی' => '#1f2937', 'سفید' => '#ffffff', 'خاکستری' => '#6b7280',
                                    'نقره ای' => '#cbd5e1', 'طلایی' => '#d4af37', 'صورتی' => '#ec4899',
                                    'بنفش' => '#8b5cf6', 'قهوه ای' => '#78350f', 'کرم' => '#f5f5dc',
                                    'blue' => '#3b82f6', 'red' => '#ef4444', 'green' => '#22c55e',
                                    'black' => '#1f2937', 'white' => '#ffffff', 'gray' => '#6b7280',
                                    'silver' => '#cbd5e1', 'gold' => '#d4af37', 'pink' => '#ec4899'
                                ];
                                $color_code = $color_map[$option_name] ?? $color_map[strtolower($option_name)] ?? '#9ca3af';
                            }
                        ?>
                            <label class="variation-option" :class="{ 'active': selectedAttributes['<?php echo esc_attr($attr_key); ?>'] === '<?php echo esc_attr($option); ?>' }">
                                <input
                                    type="radio"
                                    name="attribute_<?php echo esc_attr($attr_key); ?>"
                                    value="<?php echo esc_attr($option); ?>"
                                    @change="selectAttribute('<?php echo esc_attr($attr_key); ?>', '<?php echo esc_attr($option); ?>')"
                                >
                                <?php if ($is_color && $color_code) : ?>
                                    <span class="color-swatch" style="background-color: <?php echo esc_attr($color_code); ?>"></span>
                                <?php endif; ?>
                                <span class="option-name"><?php echo esc_html($option_name); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <input type="hidden" name="variation_id" x-model="selectedVariation">
        </div>
    <?php endif; ?>

    <!-- Specifications Section -->
    <?php
    $attributes = $product->get_attributes();
    if (!empty($attributes)) :
    ?>
        <div class="product-section specs-section" x-data="{ expanded: false }">
            <h2 class="section-title"><?php _e('مشخصات', 'ganjeh'); ?></h2>
            <div class="specs-table" :class="{ 'expanded': expanded }">
                <?php
                $attr_count = 0;
                foreach ($attributes as $attribute) :
                    if (!$attribute->get_visible()) continue;
                    $attr_count++;
                ?>
                    <div class="spec-row">
                        <div class="spec-label"><?php echo wc_attribute_label($attribute->get_name()); ?></div>
                        <div class="spec-value">
                            <?php
                            if ($attribute->is_taxonomy()) {
                                $values = wc_get_product_terms($product_id, $attribute->get_name(), ['fields' => 'names']);
                                echo implode('، ', $values);
                            } else {
                                echo implode('، ', $attribute->get_options());
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($attr_count > 4) : ?>
                <button type="button" class="show-more-btn" @click="expanded = !expanded">
                    <span x-text="expanded ? 'مشاهده کمتر' : 'مشاهده بیشتر'"></span>
                    <svg class="w-4 h-4" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Reviews Section -->
    <div class="product-section reviews-section">
        <h2 class="section-title"><?php _e('نظرات', 'ganjeh'); ?></h2>

        <?php
        // Get reviews with rating for this product
        global $wpdb;
        $reviews = $wpdb->get_results($wpdb->prepare(
            "SELECT c.* FROM {$wpdb->comments} c
             INNER JOIN {$wpdb->commentmeta} cm ON c.comment_ID = cm.comment_id
             WHERE c.comment_post_ID = %d
             AND cm.meta_key = 'rating'
             AND c.comment_approved = '1'
             ORDER BY c.comment_date DESC
             LIMIT 20",
            $product_id
        ));

        if (empty($reviews)) :
        ?>
            <div class="no-reviews">
                <h3><?php _e('اولین نظر را شما ثبت کنید', 'ganjeh'); ?></h3>
                <p><?php _e('نقطه نظر و تجربیات خود را با دیگران در میان بگذارید.', 'ganjeh'); ?></p>
            </div>
        <?php else : ?>
            <div class="reviews-bubbles">
                <?php foreach ($reviews as $review) :
                    $rating = get_comment_meta($review->comment_ID, 'rating', true);
                    $first_letter = mb_substr($review->comment_author, 0, 1, 'UTF-8');
                ?>
                    <div class="review-bubble">
                        <div class="bubble-avatar"><?php echo esc_html($first_letter); ?></div>
                        <div class="bubble-info">
                            <div class="bubble-header">
                                <span class="bubble-name"><?php echo esc_html($review->comment_author); ?></span>
                                <?php if ($rating) : ?>
                                    <div class="bubble-stars">
                                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                                            <svg class="bubble-star <?php echo $i <= $rating ? 'active' : ''; ?>" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="bubble-text"><?php echo esc_html($review->comment_content); ?></p>
                            <span class="bubble-date"><?php echo human_time_diff(strtotime($review->comment_date), current_time('timestamp')); ?> پیش</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Add Review Button -->
        <button type="button" class="add-review-btn" onclick="<?php echo is_user_logged_in() ? "document.getElementById('review-form-modal').classList.add('show')" : "window.openAuthModal()"; ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span><?php _e('ثبت نظر', 'ganjeh'); ?></span>
        </button>
    </div>

    <!-- Related Products -->
    <?php
    $related_products = wc_get_related_products($product_id, 6);
    if (!empty($related_products)) :
    ?>
        <div class="product-section related-section">
            <h2 class="section-title"><?php _e('محصولات مرتبط', 'ganjeh'); ?></h2>
            <div class="related-products-scroll">
                <?php foreach ($related_products as $related_id) :
                    $related = wc_get_product($related_id);
                    if (!$related) continue;
                    $related_image = $related->get_image_id();
                    $related_price = $related->get_price();
                    $related_regular = $related->get_regular_price();
                    $related_sale = $related->get_sale_price();
                ?>
                    <a href="<?php echo get_permalink($related_id); ?>" class="related-product-card">
                        <div class="related-product-image">
                            <?php if ($related_image) : ?>
                                <?php echo wp_get_attachment_image($related_image, 'thumbnail', false, ['class' => 'related-img']); ?>
                            <?php else : ?>
                                <div class="related-img-placeholder">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <?php if ($related->is_on_sale() && $related_regular) :
                                $discount = round((($related_regular - $related_sale) / $related_regular) * 100);
                            ?>
                                <span class="related-discount"><?php echo $discount; ?>%</span>
                            <?php endif; ?>
                        </div>
                        <div class="related-product-info">
                            <h3 class="related-product-title"><?php echo wp_trim_words($related->get_name(), 5); ?></h3>
                            <div class="related-product-price">
                                <?php if ($related->is_on_sale() && $related_regular) : ?>
                                    <span class="related-old-price"><?php echo number_format($related_regular); ?></span>
                                <?php endif; ?>
                                <span class="related-current-price"><?php echo number_format($related_price); ?> <small><?php _e('تومان', 'ganjeh'); ?></small></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Best Selling Products -->
    <?php
    $best_selling = wc_get_products([
        'limit' => 6,
        'orderby' => 'meta_value_num',
        'meta_key' => 'total_sales',
        'order' => 'DESC',
        'exclude' => [$product_id],
        'status' => 'publish',
    ]);
    if (!empty($best_selling)) :
    ?>
        <div class="product-section related-section">
            <h2 class="section-title"><?php _e('محصولات پرفروش', 'ganjeh'); ?></h2>
            <div class="related-products-scroll">
                <?php foreach ($best_selling as $best_product) :
                    $best_image = $best_product->get_image_id();
                    $best_price = $best_product->get_price();
                    $best_regular = $best_product->get_regular_price();
                    $best_sale = $best_product->get_sale_price();
                ?>
                    <a href="<?php echo get_permalink($best_product->get_id()); ?>" class="related-product-card">
                        <div class="related-product-image">
                            <?php if ($best_image) : ?>
                                <?php echo wp_get_attachment_image($best_image, 'thumbnail', false, ['class' => 'related-img']); ?>
                            <?php else : ?>
                                <div class="related-img-placeholder">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <?php if ($best_product->is_on_sale() && $best_regular) :
                                $discount = round((($best_regular - $best_sale) / $best_regular) * 100);
                            ?>
                                <span class="related-discount"><?php echo $discount; ?>%</span>
                            <?php endif; ?>
                        </div>
                        <div class="related-product-info">
                            <h3 class="related-product-title"><?php echo wp_trim_words($best_product->get_name(), 5); ?></h3>
                            <div class="related-product-price">
                                <?php if ($best_product->is_on_sale() && $best_regular) : ?>
                                    <span class="related-old-price"><?php echo number_format($best_regular); ?></span>
                                <?php endif; ?>
                                <span class="related-current-price"><?php echo number_format($best_price); ?> <small><?php _e('تومان', 'ganjeh'); ?></small></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</main>

<!-- Fixed Bottom Bar - Add to Cart -->
<div class="product-bottom-bar" x-data="{ quantity: 1, loading: false }">
    <div class="bottom-bar-content">
        <!-- Price & Quantity Section -->
        <div class="price-qty-section">
            <!-- Price -->
            <div class="price-values">
                <?php if ($is_variable) :
                    $min_price = $product->get_variation_price('min');
                    $max_price = $product->get_variation_price('max');
                    $min_regular = $product->get_variation_regular_price('min');
                    $has_discount = $min_price < $min_regular;
                ?>
                    <div class="variable-price-display">
                        <?php if ($has_discount) :
                            $discount = round((($min_regular - $min_price) / $min_regular) * 100);
                        ?>
                            <span class="discount-badge"><?php echo $discount; ?>%</span>
                        <?php endif; ?>
                        <div class="price-from">
                            <span class="price-from-label"><?php _e('از', 'ganjeh'); ?></span>
                            <span class="price-amount"><?php echo number_format($min_price); ?></span>
                            <span class="price-currency"><?php _e('تومان', 'ganjeh'); ?></span>
                        </div>
                    </div>
                <?php elseif ($product->is_on_sale()) :
                    $regular_price = $product->get_regular_price();
                    $sale_price = $product->get_sale_price();
                    $discount = round((($regular_price - $sale_price) / $regular_price) * 100);
                ?>
                    <div class="simple-price-display">
                        <div class="original-price-row">
                            <span class="original-price"><?php echo number_format($regular_price); ?></span>
                            <span class="discount-badge"><?php echo $discount; ?>%</span>
                        </div>
                        <div class="current-price-row">
                            <span class="price-amount"><?php echo number_format($sale_price); ?></span>
                            <span class="price-currency"><?php _e('تومان', 'ganjeh'); ?></span>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="simple-price-display">
                        <div class="current-price-row">
                            <span class="price-amount"><?php echo number_format($product->get_price()); ?></span>
                            <span class="price-currency"><?php _e('تومان', 'ganjeh'); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quantity Selector -->
            <div class="quantity-selector">
                <button type="button" class="qty-btn" @click="quantity > 1 ? quantity-- : null" :disabled="quantity <= 1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                </button>
                <span class="qty-value" x-text="quantity"></span>
                <button type="button" class="qty-btn" @click="quantity++">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Add to Cart Button -->
        <?php if ($product->is_in_stock()) : ?>
            <?php if ($product->is_type('simple')) : ?>
                <button
                    type="button"
                    class="add-to-cart-btn"
                    :class="{ 'loading': loading }"
                    :disabled="loading"
                    @click="
                        <?php if (!is_user_logged_in()) : ?>
                        window.openAuthModal({ type: 'add_to_cart', productId: <?php echo $product_id; ?>, quantity: quantity, isVariable: false });
                        <?php else : ?>
                        loading = true;
                        fetch(ganjeh.ajax_url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'ganjeh_add_to_cart',
                                product_id: <?php echo $product_id; ?>,
                                quantity: quantity,
                                nonce: ganjeh.nonce
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            loading = false;
                            if (data.success) {
                                const cartCount = document.querySelector('.ganjeh-cart-count');
                                if (cartCount) cartCount.textContent = data.data.cart_count;
                                window.showCartToast && window.showCartToast(data.data);
                            } else {
                                alert(data.data.message);
                            }
                        })
                        .catch(() => {
                            loading = false;
                        });
                        <?php endif; ?>
                    "
                >
                    <span x-show="!loading"><?php _e('افزودن به سبد خرید', 'ganjeh'); ?></span>
                    <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            <?php elseif ($is_variable) : ?>
                <button
                    type="button"
                    class="add-to-cart-btn"
                    @click="<?php echo is_user_logged_in() ? "document.getElementById('variation-sheet').classList.add('show')" : "window.openAuthModal({ type: 'add_to_cart', productId: " . $product_id . ", isVariable: true })"; ?>"
                >
                    <span><?php _e('افزودن به سبد خرید', 'ganjeh'); ?></span>
                </button>
            <?php else : ?>
                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo $product->add_to_cart_url(); ?>" class="add-to-cart-btn">
                        <?php echo $product->add_to_cart_text(); ?>
                    </a>
                <?php else : ?>
                    <button type="button" class="add-to-cart-btn" onclick="window.openAuthModal({ type: 'add_to_cart', productId: <?php echo $product_id; ?>, isVariable: false })">
                        <?php echo $product->add_to_cart_text(); ?>
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        <?php else : ?>
            <button type="button" class="add-to-cart-btn out-of-stock" disabled>
                <?php _e('ناموجود', 'ganjeh'); ?>
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Review Form Modal -->
<div id="review-form-modal" class="review-modal" x-data="reviewForm()">
    <div class="modal-overlay" @click="closeModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('ثبت نظر', 'ganjeh'); ?></h3>
            <button type="button" @click="closeModal()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <?php if (is_user_logged_in()) : ?>
            <form @submit.prevent="submitReview()" class="review-form">
                <div class="rating-select">
                    <label><?php _e('امتیاز شما', 'ganjeh'); ?></label>
                    <div class="stars-input">
                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                            <button type="button" class="star-btn" @click="rating = <?php echo $i; ?>" @mouseenter="hoverRating = <?php echo $i; ?>" @mouseleave="hoverRating = 0">
                                <svg class="star-icon" :class="{ 'star-active': <?php echo $i; ?> <= (hoverRating || rating) }" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="review-content"><?php _e('متن نظر', 'ganjeh'); ?></label>
                    <textarea id="review-content" x-model="content" rows="4" required placeholder="<?php _e('نظر خود را بنویسید...', 'ganjeh'); ?>"></textarea>
                </div>

                <div class="form-message" x-show="message" :class="messageType" x-text="message"></div>

                <button type="submit" class="submit-review-btn" :disabled="loading" :class="{ 'loading': loading }">
                    <span x-show="!loading"><?php _e('ثبت نظر', 'ganjeh'); ?></span>
                    <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>
        <?php else : ?>
            <div class="login-required">
                <p><?php _e('برای ثبت نظر ابتدا وارد شوید.', 'ganjeh'); ?></p>
                <a href="<?php echo wp_login_url(get_permalink()); ?>" class="login-btn"><?php _e('ورود به حساب کاربری', 'ganjeh'); ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($is_variable) : ?>
<!-- Variation Selection Bottom Sheet -->
<div id="variation-sheet" class="variation-sheet" x-data="variationSheet()">
    <div class="sheet-overlay" @click="closeSheet()"></div>
    <div class="sheet-content">
        <div class="sheet-header">
            <h3><?php _e('انتخاب گزینه‌ها', 'ganjeh'); ?></h3>
            <button type="button" @click="closeSheet()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="sheet-body">
            <?php foreach ($variation_attributes as $attribute_name => $options) :
                $attribute_label = wc_attribute_label($attribute_name);
                $attr_key = $attribute_name;
                $is_color = strpos(strtolower($attribute_name), 'color') !== false || strpos(strtolower($attribute_name), 'رنگ') !== false;
            ?>
                <div class="sheet-variation-group">
                    <h4 class="sheet-variation-label"><?php echo esc_html($attribute_label); ?></h4>
                    <div class="sheet-variation-options">
                        <?php foreach ($options as $option) :
                            $term_obj = get_term_by('slug', $option, $attribute_name);
                            $option_name = $term_obj ? $term_obj->name : $option;

                            $color_code = '';
                            if ($is_color) {
                                $color_map = [
                                    'آبی' => '#3b82f6', 'قرمز' => '#ef4444', 'سبز' => '#22c55e',
                                    'مشکی' => '#1f2937', 'سفید' => '#ffffff', 'زرد' => '#eab308',
                                    'نارنجی' => '#f97316', 'بنفش' => '#8b5cf6', 'صورتی' => '#ec4899',
                                ];
                                $color_code = $color_map[$option_name] ?? '#9ca3af';
                            }
                        ?>
                            <label class="sheet-option" :class="{ 'active': sheetSelected['<?php echo esc_attr($attr_key); ?>'] === '<?php echo esc_attr($option); ?>' }">
                                <input type="radio" name="sheet_<?php echo esc_attr($attr_key); ?>" value="<?php echo esc_attr($option); ?>"
                                    @change="selectOption('<?php echo esc_attr($attr_key); ?>', '<?php echo esc_attr($option); ?>')">
                                <?php if ($is_color && $color_code) : ?>
                                    <span class="sheet-color-swatch" style="background-color: <?php echo esc_attr($color_code); ?>"></span>
                                <?php endif; ?>
                                <span><?php echo esc_html($option_name); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="sheet-footer">
            <div class="sheet-qty-selector">
                <button type="button" class="sheet-qty-btn" @click="sheetQuantity > 1 ? sheetQuantity-- : null" :disabled="sheetQuantity <= 1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                </button>
                <span class="sheet-qty-value" x-text="sheetQuantity"></span>
                <button type="button" class="sheet-qty-btn" @click="sheetQuantity++">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </div>
            <div class="sheet-price">
                <span class="sheet-price-label"><?php _e('قیمت:', 'ganjeh'); ?></span>
                <span class="sheet-price-amount" x-text="sheetPrice ? new Intl.NumberFormat('fa-IR').format(sheetPrice * sheetQuantity) + ' تومان' : '<?php _e('انتخاب کنید', 'ganjeh'); ?>'"></span>
            </div>
            <button type="button" class="sheet-add-btn" :disabled="!sheetVariationId || loading" :class="{ 'loading': loading }" @click="addToCart()">
                <span x-show="!loading"><?php _e('افزودن به سبد خرید', 'ganjeh'); ?></span>
                <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
/* Header */
.product-header {
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
.product-header-right {
    display: flex;
    align-items: center;
    gap: 8px;
}
.header-icon-btn {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #374151;
    background: transparent;
    border: none;
    cursor: pointer;
}
.header-title {
    font-size: 15px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

/* Breadcrumb */
.product-breadcrumb {
    padding: 10px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #6b7280;
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
    background: #f9fafb;
}
.product-breadcrumb::-webkit-scrollbar { display: none; }
.product-breadcrumb a {
    color: #6b7280;
    text-decoration: none;
    white-space: nowrap;
}
.product-breadcrumb a:hover { color: var(--color-primary, #4CB050); }
.breadcrumb-sep { color: #d1d5db; }
.breadcrumb-current { color: #374151; white-space: nowrap; }

/* Gallery */
.product-gallery-wrapper {
    padding: 12px 16px;
    background: white;
}
.gallery-grid {
    display: flex;
    flex-direction: row-reverse;
    gap: 8px;
    height: 200px;
}
.gallery-main {
    width: 70%;
    border-radius: 12px;
    overflow: hidden;
    background: #f9fafb;
    cursor: pointer;
    border: 1px solid #e5e7eb;
}
.gallery-main img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.gallery-thumbs {
    width: 30%;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.thumb-item {
    flex: 1;
    border-radius: 12px;
    overflow: hidden;
    background: #f9fafb;
    cursor: pointer;
    position: relative;
    border: 1px solid #e5e7eb;
}
.thumb-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.thumb-item.has-more::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.6);
    border-radius: 12px;
}
.thumb-more {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 11px;
    font-weight: 600;
    z-index: 2;
    white-space: nowrap;
}
.gallery-placeholder {
    height: 200px;
    background: #f3f4f6;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Lightbox */
.lightbox-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.95);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}
.lightbox-content {
    width: 100%;
    max-width: 515px;
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
}
.lightbox-close {
    position: absolute;
    top: 16px;
    left: 16px;
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.15);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}
.lightbox-images {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 16px 16px;
}
.lightbox-slide {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.lightbox-slide img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 8px;
}
.lightbox-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    padding: 16px;
}
.lightbox-btn {
    width: 44px;
    height: 44px;
    background: rgba(255,255,255,0.15);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
.lightbox-btn:hover {
    background: rgba(255,255,255,0.25);
}
.lightbox-counter {
    color: white;
    font-size: 14px;
    font-weight: 500;
}
[x-cloak] { display: none !important; }

/* Product Info */
.product-info {
    padding: 0 16px 16px;
    background: white;
}
.delivery-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #f9fafb;
    border-radius: 20px;
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 12px;
}
.product-categories {
    font-size: 14px;
    color: var(--color-primary, #4CB050);
    margin-bottom: 8px;
}
.product-categories a {
    color: inherit;
    text-decoration: none;
    font-weight: 500;
}
.product-categories .cat-sep {
    color: #d1d5db;
    margin: 0 6px;
}
.product-title {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.6;
    margin: 0;
}

/* Variations */
.product-variations {
    padding: 16px;
    background: white;
    border-top: 8px solid #f3f4f6;
}
.variation-group {
    margin-bottom: 16px;
}
.variation-group:last-child { margin-bottom: 0; }
.variation-label {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 12px;
}
.variation-options {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.variation-option {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #f9fafb;
    border: 2px solid transparent;
    border-radius: 25px;
    font-size: 13px;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
}
.variation-option input { display: none; }
.variation-option:hover { background: #f3f4f6; }
.variation-option.active {
    border-color: #1f2937;
    background: white;
}
.color-swatch {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 1px #d1d5db;
}

/* Product Sections */
.product-section {
    padding: 16px;
    background: white;
    border-top: 8px solid #f3f4f6;
}
.section-title {
    font-size: 15px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 16px;
}

/* Product Description */
.product-description {
    font-size: 14px;
    color: #4b5563;
    line-height: 1.8;
    max-height: 120px;
    overflow: hidden;
    transition: max-height 0.3s ease;
}
.product-description.expanded {
    max-height: 2000px;
}
.product-description p {
    margin: 0 0 12px;
}
.product-description p:last-child {
    margin-bottom: 0;
}
.product-description ul,
.product-description ol {
    padding-right: 20px;
    margin: 12px 0;
}
.product-description li {
    margin-bottom: 6px;
}

/* Specs */
.specs-section {
    padding: 16px;
}
.specs-table {
    background: #f9fafb;
    border-radius: 12px;
    overflow: hidden;
    max-height: 200px;
    overflow: hidden;
    transition: max-height 0.3s;
}
.specs-table.expanded {
    max-height: 1000px;
}
.spec-row {
    display: flex;
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
}
.spec-row:last-child {
    border-bottom: none;
}
.spec-row:nth-child(odd) {
    background: white;
}
.spec-label {
    width: 40%;
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
}
.spec-value {
    width: 60%;
    font-size: 13px;
    color: #1f2937;
    font-weight: 500;
}
.show-more-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 12px;
    margin-top: 12px;
    background: transparent;
    border: none;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
}
.show-more-btn svg { transition: transform 0.2s; }

/* Related Products */
.related-section {
    padding: 16px;
}
.related-section:last-of-type {
    padding-bottom: 80px; /* Space for fixed bottom bar */
}
.related-products-scroll {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 8px;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.related-products-scroll::-webkit-scrollbar {
    display: none;
}
.related-product-card {
    flex-shrink: 0;
    width: 140px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none;
    border: 1px solid #e5e7eb;
    transition: all 0.2s;
}
.related-product-card:hover {
    border-color: #d1d5db;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.related-product-image {
    position: relative;
    width: 100%;
    aspect-ratio: 1;
    background: #f9fafb;
}
.related-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.related-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.related-discount {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 6px;
    border-radius: 6px;
}
.related-product-info {
    padding: 10px;
}
.related-product-title {
    font-size: 12px;
    font-weight: 500;
    color: #1f2937;
    margin: 0 0 8px;
    line-height: 1.5;
    height: 36px;
    overflow: hidden;
}
.related-product-price {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.related-old-price {
    font-size: 11px;
    color: #9ca3af;
    text-decoration: line-through;
}
.related-current-price {
    font-size: 13px;
    font-weight: 700;
    color: #1f2937;
}
.related-current-price small {
    font-size: 10px;
    font-weight: 400;
    color: #6b7280;
}

/* Reviews */
.no-reviews {
    text-align: center;
    padding: 24px 0;
}
.no-reviews h3 {
    font-size: 15px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 8px;
}
.no-reviews p {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
}
.reviews-bubbles {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 16px;
}
.review-bubble {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #f9fafb;
    border-radius: 12px;
    padding: 14px;
}
.bubble-avatar {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #4CB050, #3d9142);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 700;
    color: white;
}
.bubble-info {
    flex: 1;
    min-width: 0;
}
.bubble-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}
.bubble-name {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
}
.bubble-stars {
    display: flex;
    gap: 1px;
    direction: ltr;
}
.bubble-star {
    width: 12px;
    height: 12px;
    color: #d1d5db;
}
.bubble-star.active {
    color: #f59e0b;
}
.bubble-text {
    font-size: 13px;
    color: #4b5563;
    line-height: 1.6;
    margin: 0 0 6px;
}
.bubble-date {
    font-size: 10px;
    color: #9ca3af;
}
.add-review-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 14px;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
}
.add-review-btn:hover {
    border-color: #d1d5db;
    background: #f9fafb;
}

/* Bottom Bar */
.product-bottom-bar {
    position: fixed;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100%;
    max-width: 515px;
    z-index: 50;
    background: white;
    border-top: 1px solid #e5e7eb;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
}
.bottom-bar-content {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}
/* Price & Quantity Section */
.price-qty-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.price-values {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}
/* Quantity Selector */
.quantity-selector {
    display: flex;
    align-items: center;
    gap: 4px;
    background: #f3f4f6;
    border-radius: 8px;
    padding: 4px;
}
.qty-btn {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: none;
    border-radius: 6px;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
}
.qty-btn:hover:not(:disabled) {
    background: #e5e7eb;
}
.qty-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
.qty-value {
    min-width: 24px;
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
}
/* Variable Product Price - "از X تومان" */
.variable-price-display {
    display: flex;
    align-items: center;
    gap: 10px;
}
.price-from {
    display: flex;
    align-items: baseline;
    gap: 4px;
}
.price-from-label {
    font-size: 13px;
    color: #6b7280;
    font-weight: 400;
}
.price-amount {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
}
.price-currency {
    font-size: 12px;
    color: #6b7280;
    font-weight: 400;
}
/* Simple Product Price */
.simple-price-display {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
}
.original-price-row {
    display: flex;
    align-items: center;
    gap: 6px;
}
.original-price {
    font-size: 12px;
    color: #9ca3af;
    text-decoration: line-through;
}
.current-price-row {
    display: flex;
    align-items: baseline;
    gap: 4px;
}
.discount-badge {
    font-size: 10px;
    font-weight: 700;
    color: #ef4444;
    background: #fef2f2;
    padding: 3px 8px;
    border-radius: 12px;
}
.add-to-cart-btn {
    flex: 1;
    padding: 14px 20px;
    background: linear-gradient(135deg, #f97316, #ea580c);
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
}
.add-to-cart-btn:hover { transform: translateY(-1px); }
.add-to-cart-btn.loading { opacity: 0.7; }
.add-to-cart-btn.out-of-stock {
    background: #d1d5db;
    cursor: not-allowed;
}

/* Review Modal */
.review-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    display: none;
    align-items: flex-end;
    justify-content: center;
}
.review-modal.show { display: flex; }
.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
}
.modal-content {
    position: relative;
    width: 100%;
    max-width: 515px;
    background: white;
    border-radius: 20px 20px 0 0;
    max-height: 80vh;
    overflow-y: auto;
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
}
.modal-header h3 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
}
.modal-header button {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
}
.review-form { padding: 20px; }
.rating-select { margin-bottom: 16px; }
.rating-select label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
}
.stars-input {
    display: flex;
    gap: 4px;
    direction: ltr;
}
.star-btn {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
}
.star-icon {
    width: 28px;
    height: 28px;
    color: #d1d5db;
    transition: color 0.15s;
}
.star-icon.star-active {
    color: #f59e0b;
}
.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
}
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    font-size: 14px;
    resize: none;
}
.submit-review-btn {
    width: 100%;
    padding: 14px;
    background: var(--color-primary, #4CB050);
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.submit-review-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
.submit-review-btn.loading {
    background: #9ca3af;
}
.form-message {
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 13px;
    margin-bottom: 16px;
    text-align: center;
}
.form-message.success {
    background: #d1fae5;
    color: #065f46;
}
.form-message.error {
    background: #fee2e2;
    color: #991b1b;
}
.login-required {
    padding: 40px 20px;
    text-align: center;
}
.login-required p {
    color: #6b7280;
    margin-bottom: 16px;
}
.login-btn {
    display: inline-block;
    padding: 12px 24px;
    background: var(--color-primary, #4CB050);
    border-radius: 12px;
    color: white;
    text-decoration: none;
    font-weight: 500;
}

/* Hide bottom nav on product page */
.single-product-page ~ .bottom-nav,
body.single-product .bottom-nav {
    display: none !important;
}

/* Variation Bottom Sheet */
.variation-sheet {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    display: none;
    align-items: flex-end;
    justify-content: center;
}
.variation-sheet.show {
    display: flex;
}
.sheet-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
}
.sheet-content {
    position: relative;
    width: 100%;
    max-width: 515px;
    background: white;
    border-radius: 20px 20px 0 0;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.3s ease;
}
@keyframes slideUp {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}
.sheet-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
}
.sheet-header h3 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    color: #1f2937;
}
.sheet-header button {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 4px;
}
.sheet-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px 20px;
}
.sheet-variation-group {
    margin-bottom: 20px;
}
.sheet-variation-group:last-child {
    margin-bottom: 0;
}
.sheet-variation-label {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 12px;
}
.sheet-variation-options {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.sheet-option {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: #f3f4f6;
    border: 2px solid transparent;
    border-radius: 25px;
    font-size: 14px;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
}
.sheet-option input {
    display: none;
}
.sheet-option:hover {
    background: #e5e7eb;
}
.sheet-option.active {
    border-color: var(--color-primary, #4CB050);
    background: #f0fdf4;
    color: var(--color-primary, #4CB050);
}
.sheet-color-swatch {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 1px #d1d5db;
}
.sheet-footer {
    padding: 16px 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: white;
}
.sheet-qty-selector {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #f3f4f6;
    border-radius: 8px;
    padding: 4px 6px;
}
.sheet-qty-btn {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: none;
    border-radius: 6px;
    color: #374151;
    cursor: pointer;
}
.sheet-qty-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
.sheet-qty-value {
    min-width: 24px;
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
}
.sheet-price {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.sheet-price-label {
    font-size: 12px;
    color: #6b7280;
}
.sheet-price-amount {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
}
.sheet-add-btn {
    flex: 1;
    max-width: 200px;
    padding: 14px 24px;
    background: linear-gradient(135deg, #f97316, #ea580c);
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.sheet-add-btn:disabled {
    background: #d1d5db;
    cursor: not-allowed;
}
.sheet-add-btn.loading {
    opacity: 0.7;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Close lightbox with escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const wrapper = document.querySelector('.product-gallery-wrapper');
            if (wrapper && wrapper._x_dataStack) {
                wrapper._x_dataStack[0].lightbox = false;
            }
        }
    });
});

<?php if ($is_variable) : ?>
// Variations handler
function productVariations() {
    return {
        selectedAttributes: {},
        selectedVariation: 0,
        currentPrice: 0,
        variations: <?php echo json_encode($available_variations); ?>,
        attributeNames: <?php echo json_encode(array_keys($variation_attributes)); ?>,

        init() {
            // Variations initialized
        },

        selectAttribute(name, value) {
            this.selectedAttributes[name] = value;
            this.findVariation();
        },

        findVariation() {
            const selected = this.selectedAttributes;
            const totalAttrs = this.attributeNames.length;

            if (Object.keys(selected).length < totalAttrs) {
                return;
            }

            for (const variation of this.variations) {
                let match = true;
                const varAttrs = variation.attributes;

                // Check each selected attribute
                for (const [attrKey, selectedValue] of Object.entries(selected)) {
                    let variationValue = '';

                    // Search through all variation attribute keys (they might be URL-encoded)
                    for (const [vKey, vVal] of Object.entries(varAttrs)) {
                        // Try to match by decoding the key
                        try {
                            const decodedKey = decodeURIComponent(vKey);
                            if (decodedKey === 'attribute_' + attrKey) {
                                variationValue = vVal;
                                break;
                            }
                        } catch (e) {
                            // If decode fails, try direct match
                            if (vKey === 'attribute_' + attrKey) {
                                variationValue = vVal;
                                break;
                            }
                        }
                    }

                    // Empty string means "any value"
                    if (variationValue !== '' && variationValue !== selectedValue) {
                        match = false;
                        break;
                    }
                }

                if (match) {
                    this.selectedVariation = variation.variation_id;
                    this.updatePrice(variation);
                    return;
                }
            }

            this.selectedVariation = 0;
        },

        updatePrice(variation) {
            this.currentPrice = variation.display_price;

            const priceEl = document.querySelector('.price-amount');
            const fromLabel = document.querySelector('.price-from-label');

            if (priceEl) {
                priceEl.textContent = new Intl.NumberFormat('fa-IR').format(variation.display_price);
            }

            if (fromLabel) {
                fromLabel.style.display = 'none';
            }
        }
    };
}

// Variation Bottom Sheet handler
function variationSheet() {
    return {
        sheetSelected: {},
        sheetVariationId: 0,
        sheetPrice: 0,
        sheetQuantity: 1,
        loading: false,
        variations: <?php echo json_encode($available_variations); ?>,
        attributeNames: <?php echo json_encode(array_keys($variation_attributes)); ?>,

        selectOption(name, value) {
            this.sheetSelected[name] = value;
            this.findSheetVariation();
        },

        findSheetVariation() {
            const selected = this.sheetSelected;
            const totalAttrs = this.attributeNames.length;

            if (Object.keys(selected).length < totalAttrs) {
                this.sheetVariationId = 0;
                this.sheetPrice = 0;
                return;
            }

            for (const variation of this.variations) {
                let match = true;
                const varAttrs = variation.attributes;

                for (const [attrKey, selectedValue] of Object.entries(selected)) {
                    let variationValue = '';

                    for (const [vKey, vVal] of Object.entries(varAttrs)) {
                        try {
                            const decodedKey = decodeURIComponent(vKey);
                            if (decodedKey === 'attribute_' + attrKey) {
                                variationValue = vVal;
                                break;
                            }
                        } catch (e) {
                            if (vKey === 'attribute_' + attrKey) {
                                variationValue = vVal;
                                break;
                            }
                        }
                    }

                    if (variationValue !== '' && variationValue !== selectedValue) {
                        match = false;
                        break;
                    }
                }

                if (match) {
                    this.sheetVariationId = variation.variation_id;
                    this.sheetPrice = variation.display_price;
                    return;
                }
            }

            this.sheetVariationId = 0;
            this.sheetPrice = 0;
        },

        closeSheet() {
            document.getElementById('variation-sheet').classList.remove('show');
        },

        addToCart() {
            if (!this.sheetVariationId) {
                alert('لطفاً ابتدا گزینه‌ها را انتخاب کنید');
                return;
            }

            this.loading = true;

            const formData = new URLSearchParams({
                action: 'ganjeh_add_to_cart',
                product_id: <?php echo $product_id; ?>,
                variation_id: this.sheetVariationId,
                quantity: this.sheetQuantity,
                nonce: ganjeh.nonce
            });

            fetch(ganjeh.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(r => {
                if (!r.ok) throw new Error('Network error');
                return r.json();
            })
            .then(data => {
                this.loading = false;
                if (data.success) {
                    const cartCount = document.querySelector('.ganjeh-cart-count');
                    if (cartCount) cartCount.textContent = data.data.cart_count;
                    this.closeSheet();
                    window.showCartToast && window.showCartToast(data.data);
                } else {
                    alert(data.data?.message || 'خطا در افزودن به سبد');
                }
            })
            .catch(err => {
                this.loading = false;
                console.error('Add to cart error:', err);
                alert('خطا در ارتباط با سرور');
            });
        }
    };
}
<?php endif; ?>

// Review Form Handler
function reviewForm() {
    return {
        rating: 5,
        hoverRating: 0,
        content: '',
        loading: false,
        message: '',
        messageType: '',

        closeModal() {
            document.getElementById('review-form-modal').classList.remove('show');
        },

        submitReview() {
            if (!this.content.trim()) {
                this.message = 'لطفاً متن نظر را وارد کنید';
                this.messageType = 'error';
                return;
            }

            this.loading = true;
            this.message = '';

            console.log('Submitting review:', {
                product_id: <?php echo $product_id; ?>,
                rating: this.rating,
                content: this.content,
                ajax_url: ganjeh.ajax_url
            });

            fetch(ganjeh.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ganjeh_submit_review',
                    product_id: <?php echo $product_id; ?>,
                    rating: this.rating,
                    content: this.content,
                    nonce: ganjeh.nonce
                })
            })
            .then(r => {
                console.log('Response status:', r.status);
                return r.json();
            })
            .then(data => {
                console.log('Response data:', data);
                this.loading = false;
                if (data.success) {
                    this.message = data.data.message;
                    this.messageType = 'success';
                    this.content = '';
                    this.rating = 5;
                    // Close modal after 2 seconds
                    setTimeout(() => {
                        this.closeModal();
                        this.message = '';
                    }, 2000);
                } else {
                    this.message = data.data.message;
                    this.messageType = 'error';
                }
            })
            .catch((err) => {
                this.loading = false;
                this.message = 'خطا در ارسال: ' + err.message;
                this.messageType = 'error';
                console.error('Review submit error:', err);
            });
        }
    };
}
</script>

<?php
get_footer();
