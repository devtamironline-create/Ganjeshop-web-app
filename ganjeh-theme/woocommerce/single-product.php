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
                <div class="gallery-thumbs">
                    <?php
                    $thumb_images = array_slice($all_images, 1, 2);
                    foreach ($thumb_images as $index => $image_id) :
                        $is_last = ($index === 1 && $extra_count > 0);
                    ?>
                        <div class="thumb-item <?php echo $is_last ? 'has-more' : ''; ?>" @click="currentImage = <?php echo $index + 1; ?>; lightbox = true">
                            <?php echo wp_get_attachment_image($image_id, 'thumbnail', false, ['class' => 'thumb-image']); ?>
                            <?php if ($is_last) : ?>
                                <div class="thumb-more">+<?php echo $extra_count + 1; ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Main Image (Right Side) -->
                <div class="gallery-main" @click="currentImage = 0; lightbox = true">
                    <?php echo wp_get_attachment_image($main_image, 'ganjeh-product-large', false, ['class' => 'main-image']); ?>
                </div>
            </div>

            <!-- Lightbox -->
            <div class="lightbox-overlay" x-show="lightbox" x-cloak @click.self="lightbox = false" x-transition>
                <div class="lightbox-container">
                    <button class="lightbox-close" @click="lightbox = false">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <div class="lightbox-swiper swiper" dir="rtl">
                        <div class="swiper-wrapper">
                            <?php foreach ($all_images as $image_id) : ?>
                                <div class="swiper-slide">
                                    <?php echo wp_get_attachment_image($image_id, 'large', false, ['class' => 'lightbox-image']); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="lightbox-nav">
                        <button class="lightbox-prev">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                        <div class="lightbox-pagination"></div>
                        <button class="lightbox-next">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                    </div>
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
        <!-- Delivery Badge -->
        <div class="delivery-badge">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span><?php _e('تحویل حضوری', 'ganjeh'); ?></span>
        </div>

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

    <!-- Variations (for variable products) -->
    <?php if ($is_variable) :
        $available_variations = $product->get_available_variations();
        $variation_attributes = $product->get_variation_attributes();
    ?>
        <div class="product-variations" x-data="productVariations()" x-init="init()">
            <?php foreach ($variation_attributes as $attribute_name => $options) :
                $attribute_label = wc_attribute_label($attribute_name);
                $attribute_slug = sanitize_title($attribute_name);
            ?>
                <div class="variation-group">
                    <h3 class="variation-label"><?php echo esc_html($attribute_label); ?></h3>
                    <div class="variation-options">
                        <?php foreach ($options as $option) :
                            $term_obj = get_term_by('slug', $option, $attribute_name);
                            $option_name = $term_obj ? $term_obj->name : $option;
                            $is_color = strpos(strtolower($attribute_name), 'color') !== false || strpos(strtolower($attribute_name), 'رنگ') !== false;

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
                            <label class="variation-option" :class="{ 'active': selectedAttributes['<?php echo esc_attr($attribute_slug); ?>'] === '<?php echo esc_attr($option); ?>' }">
                                <input
                                    type="radio"
                                    name="attribute_<?php echo esc_attr($attribute_slug); ?>"
                                    value="<?php echo esc_attr($option); ?>"
                                    @change="selectAttribute('<?php echo esc_attr($attribute_slug); ?>', '<?php echo esc_attr($option); ?>')"
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
        <div class="product-section" x-data="{ expanded: false }">
            <h2 class="section-title"><?php _e('مشخصات', 'ganjeh'); ?></h2>
            <ul class="specs-list" :class="{ 'expanded': expanded }">
                <?php
                $attr_count = 0;
                foreach ($attributes as $attribute) :
                    if (!$attribute->get_visible()) continue;
                    $attr_count++;
                ?>
                    <li class="spec-item">
                        <span class="spec-label"><?php echo wc_attribute_label($attribute->get_name()); ?>:</span>
                        <span class="spec-value">
                            <?php
                            if ($attribute->is_taxonomy()) {
                                $values = wc_get_product_terms($product_id, $attribute->get_name(), ['fields' => 'names']);
                                echo implode('، ', $values);
                            } else {
                                echo implode('، ', $attribute->get_options());
                            }
                            ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
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
        $reviews = get_comments([
            'post_id' => $product_id,
            'status' => 'approve',
            'type' => 'review',
            'number' => 5
        ]);

        if (empty($reviews)) :
        ?>
            <div class="no-reviews">
                <h3><?php _e('اولین نظر را شما ثبت کنید', 'ganjeh'); ?></h3>
                <p><?php _e('نقطه نظر و تجربیات خود را با دیگران در میان بگذارید.', 'ganjeh'); ?></p>
            </div>
        <?php else : ?>
            <div class="reviews-list">
                <?php foreach ($reviews as $review) :
                    $rating = get_comment_meta($review->comment_ID, 'rating', true);
                ?>
                    <div class="review-item">
                        <div class="review-header">
                            <span class="reviewer-name"><?php echo esc_html($review->comment_author); ?></span>
                            <?php if ($rating) : ?>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                                        <svg class="w-3 h-3 <?php echo $i <= $rating ? 'text-yellow-400' : 'text-gray-300'; ?>" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="review-content"><?php echo esc_html($review->comment_content); ?></p>
                        <span class="review-date"><?php echo human_time_diff(strtotime($review->comment_date), current_time('timestamp')); ?> پیش</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Add Review Button -->
        <button type="button" class="add-review-btn" onclick="document.getElementById('review-form-modal').classList.add('show')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span><?php _e('ثبت نظر', 'ganjeh'); ?></span>
        </button>
    </div>

</main>

<!-- Fixed Bottom Bar - Add to Cart -->
<div class="product-bottom-bar" x-data="{ quantity: 1, loading: false }">
    <div class="bottom-bar-content">
        <!-- Price Section -->
        <div class="price-section">
            <span class="price-label"><?php _e('قیمت', 'ganjeh'); ?></span>
            <div class="price-values">
                <?php if ($product->is_on_sale()) :
                    $regular_price = $is_variable ? $product->get_variation_regular_price('min') : $product->get_regular_price();
                    $sale_price = $is_variable ? $product->get_variation_sale_price('min') : $product->get_sale_price();
                    $discount = round((($regular_price - $sale_price) / $regular_price) * 100);
                ?>
                    <div class="original-price-row">
                        <span class="original-price"><?php echo number_format($regular_price); ?></span>
                        <span class="discount-badge"><?php echo $discount; ?>%</span>
                    </div>
                    <span class="sale-price"><?php echo number_format($sale_price); ?> <?php _e('تومان', 'ganjeh'); ?></span>
                <?php else : ?>
                    <span class="sale-price"><?php echo $product->get_price_html(); ?></span>
                <?php endif; ?>
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
                                document.querySelector('.ganjeh-cart-count').textContent = data.data.cart_count;
                                window.ganjehApp && window.ganjehApp.showToast(data.data.message, 'success');
                            } else {
                                window.ganjehApp && window.ganjehApp.showToast(data.data.message, 'error');
                            }
                        })
                        .catch(() => {
                            loading = false;
                        });
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
                    x-data="{ loading: false }"
                    :class="{ 'loading': loading }"
                    :disabled="loading"
                    @click="
                        const variationId = document.querySelector('input[name=variation_id]')?.value;
                        if (!variationId || variationId === '0') {
                            window.ganjehApp && window.ganjehApp.showToast('<?php _e('لطفا یک گزینه انتخاب کنید', 'ganjeh'); ?>', 'error');
                            return;
                        }
                        loading = true;
                        fetch(ganjeh.ajax_url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'ganjeh_add_to_cart',
                                product_id: <?php echo $product_id; ?>,
                                variation_id: variationId,
                                quantity: 1,
                                nonce: ganjeh.nonce
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            loading = false;
                            if (data.success) {
                                document.querySelector('.ganjeh-cart-count').textContent = data.data.cart_count;
                                window.ganjehApp && window.ganjehApp.showToast(data.data.message, 'success');
                            } else {
                                window.ganjehApp && window.ganjehApp.showToast(data.data.message, 'error');
                            }
                        })
                        .catch(() => {
                            loading = false;
                        });
                    "
                >
                    <span x-show="!loading"><?php _e('افزودن به سبد خرید', 'ganjeh'); ?></span>
                    <svg x-show="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            <?php else : ?>
                <a href="<?php echo $product->add_to_cart_url(); ?>" class="add-to-cart-btn">
                    <?php echo $product->add_to_cart_text(); ?>
                </a>
            <?php endif; ?>
        <?php else : ?>
            <button type="button" class="add-to-cart-btn out-of-stock" disabled>
                <?php _e('ناموجود', 'ganjeh'); ?>
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Review Form Modal -->
<div id="review-form-modal" class="review-modal">
    <div class="modal-overlay" onclick="this.parentElement.classList.remove('show')"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('ثبت نظر', 'ganjeh'); ?></h3>
            <button type="button" onclick="this.closest('.review-modal').classList.remove('show')">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <?php if (is_user_logged_in()) : ?>
            <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" class="review-form">
                <input type="hidden" name="action" value="ganjeh_submit_review">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <?php wp_nonce_field('ganjeh_review_nonce', 'review_nonce'); ?>

                <div class="rating-select">
                    <label><?php _e('امتیاز شما', 'ganjeh'); ?></label>
                    <div class="stars-input" x-data="{ rating: 0, hover: 0 }">
                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                            <button type="button" @click="rating = <?php echo $i; ?>" @mouseenter="hover = <?php echo $i; ?>" @mouseleave="hover = 0">
                                <svg class="w-6 h-6" :class="{ 'text-yellow-400': <?php echo $i; ?> <= (hover || rating), 'text-gray-300': <?php echo $i; ?> > (hover || rating) }" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        <?php endfor; ?>
                        <input type="hidden" name="rating" x-model="rating">
                    </div>
                </div>

                <div class="form-group">
                    <label for="review-content"><?php _e('متن نظر', 'ganjeh'); ?></label>
                    <textarea id="review-content" name="content" rows="4" required placeholder="<?php _e('نظر خود را بنویسید...', 'ganjeh'); ?>"></textarea>
                </div>

                <button type="submit" class="submit-review-btn"><?php _e('ثبت نظر', 'ganjeh'); ?></button>
            </form>
        <?php else : ?>
            <div class="login-required">
                <p><?php _e('برای ثبت نظر ابتدا وارد شوید.', 'ganjeh'); ?></p>
                <a href="<?php echo wp_login_url(get_permalink()); ?>" class="login-btn"><?php _e('ورود به حساب کاربری', 'ganjeh'); ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>

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
    gap: 8px;
    height: 200px;
}
.gallery-thumbs {
    width: 30%;
    display: flex;
    flex-direction: column;
    gap: 8px;
    order: 1;
}
.thumb-item {
    flex: 1;
    border-radius: 12px;
    overflow: hidden;
    background: #f9fafb;
    cursor: pointer;
    position: relative;
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
    background: rgba(0,0,0,0.5);
}
.thumb-more {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 18px;
    font-weight: 700;
    z-index: 2;
}
.gallery-main {
    width: 70%;
    border-radius: 12px;
    overflow: hidden;
    background: #f9fafb;
    cursor: pointer;
    order: 2;
}
.gallery-main img {
    width: 100%;
    height: 100%;
    object-fit: contain;
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
.lightbox-container {
    width: 100%;
    max-width: 515px;
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 16px;
}
.lightbox-close {
    position: absolute;
    top: 16px;
    left: 16px;
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.1);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}
.lightbox-swiper {
    flex: 1;
    display: flex;
    align-items: center;
}
.lightbox-swiper .swiper-slide {
    display: flex;
    align-items: center;
    justify-content: center;
}
.lightbox-image {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
}
.lightbox-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 16px 0;
}
.lightbox-prev,
.lightbox-next {
    width: 44px;
    height: 44px;
    background: rgba(255,255,255,0.1);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.lightbox-prev:hover,
.lightbox-next:hover {
    background: rgba(255,255,255,0.2);
}
.lightbox-pagination {
    display: flex;
    gap: 6px;
}
.lightbox-pagination .swiper-pagination-bullet {
    width: 8px;
    height: 8px;
    background: rgba(255,255,255,0.3);
    border-radius: 50%;
    opacity: 1;
}
.lightbox-pagination .swiper-pagination-bullet-active {
    background: white;
}

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

/* Specs */
.specs-list {
    list-style: none;
    padding: 0;
    margin: 0;
    max-height: 160px;
    overflow: hidden;
    transition: max-height 0.3s;
}
.specs-list.expanded { max-height: 1000px; }
.spec-item {
    display: flex;
    gap: 8px;
    padding: 8px 0;
    font-size: 13px;
    line-height: 1.6;
}
.spec-item::before {
    content: "•";
    color: #9ca3af;
}
.spec-label {
    color: #1f2937;
    font-weight: 500;
}
.spec-value { color: #6b7280; }
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
.reviews-list { margin-bottom: 16px; }
.review-item {
    padding: 12px 0;
    border-bottom: 1px solid #f3f4f6;
}
.review-item:last-child { border-bottom: none; }
.review-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.reviewer-name {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
}
.review-rating { display: flex; gap: 2px; }
.review-content {
    font-size: 13px;
    color: #4b5563;
    line-height: 1.6;
    margin: 0 0 6px;
}
.review-date {
    font-size: 11px;
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
    left: 0;
    right: 0;
    z-index: 50;
    background: white;
    border-top: 1px solid #e5e7eb;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
}
.bottom-bar-content {
    max-width: 515px;
    margin: 0 auto;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}
.price-section {
    display: flex;
    align-items: center;
    gap: 16px;
}
.price-label {
    font-size: 13px;
    color: #6b7280;
}
.price-values { text-align: left; }
.original-price-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 2px;
}
.original-price {
    font-size: 12px;
    color: #9ca3af;
    text-decoration: line-through;
}
.discount-badge {
    font-size: 11px;
    font-weight: 600;
    color: #f59e0b;
    background: #fef3c7;
    padding: 2px 6px;
    border-radius: 10px;
}
.sale-price {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
}
.add-to-cart-btn {
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
    transition: all 0.2s;
    text-align: center;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
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
}
.stars-input button {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lightbox Gallery Swiper
    let lightboxSwiper = null;

    // Initialize lightbox swiper when opened
    document.querySelectorAll('.gallery-main, .thumb-item').forEach(el => {
        el.addEventListener('click', function() {
            setTimeout(() => {
                if (!lightboxSwiper && document.querySelector('.lightbox-swiper')) {
                    lightboxSwiper = new Swiper('.lightbox-swiper', {
                        slidesPerView: 1,
                        spaceBetween: 0,
                        loop: <?php echo count($all_images) > 1 ? 'true' : 'false'; ?>,
                        navigation: {
                            nextEl: '.lightbox-next',
                            prevEl: '.lightbox-prev',
                        },
                        pagination: {
                            el: '.lightbox-pagination',
                            clickable: true,
                        },
                    });
                }
            }, 100);
        });
    });

    // Close lightbox with escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const overlay = document.querySelector('.lightbox-overlay');
            if (overlay && overlay.style.display !== 'none') {
                overlay.__x.$data.lightbox = false;
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
        variations: <?php echo json_encode($available_variations); ?>,

        init() {
            // Pre-select first options
        },

        selectAttribute(name, value) {
            this.selectedAttributes[name] = value;
            this.findVariation();
        },

        findVariation() {
            const selected = this.selectedAttributes;
            const keys = Object.keys(selected);

            for (const variation of this.variations) {
                let match = true;
                for (const key of keys) {
                    const attrKey = 'attribute_' + key;
                    if (variation.attributes[attrKey] !== '' && variation.attributes[attrKey] !== selected[key]) {
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
            const priceEl = document.querySelector('.sale-price');
            if (priceEl && variation.display_price) {
                priceEl.textContent = new Intl.NumberFormat('fa-IR').format(variation.display_price) + ' تومان';
            }
        }
    };
}
<?php endif; ?>
</script>

<?php
get_footer();
