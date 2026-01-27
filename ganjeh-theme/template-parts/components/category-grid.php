<?php
/**
 * Category Grid Component
 *
 * @package Ganjeh
 */

// Get category settings
$cat_settings = ganjeh_get_category_settings();

// Check if categories are enabled
if (empty($cat_settings['enabled'])) {
    return;
}

// Get visible categories
$categories = ganjeh_get_visible_categories();

if (empty($categories)) {
    return;
}

// Get columns setting
$columns = $cat_settings['columns'] ?? 4;
$grid_cols = $columns == 3 ? 'grid-cols-3' : 'grid-cols-4';
?>

<div class="category-grid grid <?php echo $grid_cols; ?> gap-2">
    <?php foreach ($categories as $category) :
        $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
        $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'ganjeh-category-icon') : '';
        $category_link = get_term_link($category);
        $badge = ganjeh_get_category_badge($category->term_id);
    ?>
        <a href="<?php echo esc_url($category_link); ?>" class="category-card-new">
            <?php if ($badge) : ?>
                <span class="category-badge"><?php echo esc_html($badge); ?></span>
            <?php endif; ?>

            <div class="category-image-box">
                <?php if ($image_url) : ?>
                    <img src="<?php echo esc_url($image_url); ?>"
                         alt="<?php echo esc_attr($category->name); ?>"
                         loading="lazy">
                <?php else : ?>
                    <span class="category-placeholder">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </span>
                <?php endif; ?>
            </div>

            <span class="category-name"><?php echo esc_html($category->name); ?></span>
        </a>
    <?php endforeach; ?>
</div>

<style>
.category-grid {
    direction: rtl;
}

.category-card-new {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 12px 8px 10px;
    background: #f3f4f6;
    border-radius: 16px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.category-card-new:hover {
    background: #e5e7eb;
    transform: translateY(-2px);
}

.category-badge {
    position: absolute;
    top: 6px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--color-primary, #4CB050);
    color: white;
    font-size: 9px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 10px;
    white-space: nowrap;
    z-index: 2;
}

.category-image-box {
    width: 65px;
    height: 65px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
}

.category-image-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.category-placeholder {
    width: 40px;
    height: 40px;
    color: #9ca3af;
}

.category-placeholder svg {
    width: 100%;
    height: 100%;
}

.category-name {
    font-size: 11px;
    font-weight: 600;
    color: #374151;
    text-align: center;
    line-height: 1.3;
    max-height: 2.6em;
    overflow: hidden;
}

/* Responsive adjustments */
@media (max-width: 400px) {
    .category-grid.grid-cols-4 {
        grid-template-columns: repeat(3, 1fr);
    }
    .category-image-box {
        width: 55px;
        height: 55px;
    }
    .category-name {
        font-size: 10px;
    }
}
</style>
