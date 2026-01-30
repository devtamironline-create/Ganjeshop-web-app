<?php
/**
 * Bottom Navigation Component
 *
 * @package Ganjeh
 */

$current_page = '';
if (is_front_page() || is_home()) {
    $current_page = 'home';
} elseif (is_shop() || is_product_category()) {
    $current_page = 'categories';
} elseif (is_account_page()) {
    $current_page = 'profile';
}

$is_logged_in = is_user_logged_in();

// Get product categories with hierarchy
$categories = get_terms([
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
    'parent' => 0,
]);
?>

<nav class="bottom-nav-fixed" x-data="{ showCategories: false }">
    <div class="bottom-nav-container">
        <div class="bottom-nav-inner">
            <!-- Home -->
            <a href="<?php echo home_url('/'); ?>" class="bottom-nav-item <?php echo $current_page === 'home' ? 'active' : ''; ?>">
                <svg class="nav-icon" fill="<?php echo $current_page === 'home' ? 'currentColor' : 'none'; ?>" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span><?php _e('خانه', 'ganjeh'); ?></span>
            </a>

            <!-- Categories -->
            <button type="button" class="bottom-nav-item <?php echo $current_page === 'categories' ? 'active' : ''; ?>" @click="showCategories = true">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span><?php _e('دسته‌بندی', 'ganjeh'); ?></span>
            </button>

            <!-- Profile / Login -->
            <?php if ($is_logged_in) : ?>
            <a href="<?php echo wc_get_account_endpoint_url(''); ?>" class="bottom-nav-item <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span><?php _e('پروفایل', 'ganjeh'); ?></span>
            </a>
            <?php else : ?>
            <a href="<?php echo wc_get_account_endpoint_url(''); ?>" class="bottom-nav-item">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                <span><?php _e('ورود', 'ganjeh'); ?></span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Categories Modal -->
    <div class="categories-modal-overlay" x-show="showCategories" x-cloak @click.self="showCategories = false" x-transition:enter="fade-enter" x-transition:leave="fade-leave">
        <div class="categories-modal" x-show="showCategories" x-transition:enter="slide-enter" x-transition:leave="slide-leave">
            <div class="categories-modal-header">
                <h3><?php _e('دسته‌بندی محصولات', 'ganjeh'); ?></h3>
                <button type="button" class="close-modal" @click="showCategories = false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="categories-modal-body">
                <!-- All Products -->
                <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="category-item all-products">
                    <div class="category-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </div>
                    <span><?php _e('همه محصولات', 'ganjeh'); ?></span>
                    <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>

                <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
                    <?php foreach ($categories as $parent_cat) :
                        $thumbnail_id = get_term_meta($parent_cat->term_id, 'thumbnail_id', true);
                        $image = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : wc_placeholder_img_src();

                        // Get child categories
                        $children = get_terms([
                            'taxonomy' => 'product_cat',
                            'hide_empty' => false,
                            'parent' => $parent_cat->term_id,
                        ]);
                    ?>
                    <div class="category-group" x-data="{ expanded: false }">
                        <div class="category-item parent-category" @click="<?php echo !empty($children) && !is_wp_error($children) ? 'expanded = !expanded' : ''; ?>">
                            <div class="category-icon">
                                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($parent_cat->name); ?>">
                            </div>
                            <span class="category-name"><?php echo esc_html($parent_cat->name); ?></span>
                            <?php if (!empty($children) && !is_wp_error($children)) : ?>
                            <span class="category-count"><?php echo count($children); ?></span>
                            <svg class="expand-icon" :class="{ 'rotated': expanded }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                            <?php else : ?>
                            <a href="<?php echo get_term_link($parent_cat); ?>" class="category-link" @click.stop>
                                <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($children) && !is_wp_error($children)) : ?>
                        <div class="sub-categories" x-show="expanded" x-collapse>
                            <a href="<?php echo get_term_link($parent_cat); ?>" class="sub-category-item">
                                <span><?php printf(__('همه %s', 'ganjeh'), $parent_cat->name); ?></span>
                                <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                            <?php foreach ($children as $child_cat) : ?>
                            <a href="<?php echo get_term_link($child_cat); ?>" class="sub-category-item">
                                <span><?php echo esc_html($child_cat->name); ?></span>
                                <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<style>
.bottom-nav-fixed {
    position: fixed;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100%;
    max-width: 515px;
    z-index: 50;
}

.bottom-nav-container {
    background: white;
    border-top: 1px solid #e5e7eb;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
}

.bottom-nav-inner {
    display: flex;
    align-items: center;
    justify-content: space-around;
    height: 64px;
}

.bottom-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 8px 24px;
    color: #6b7280;
    text-decoration: none;
    background: none;
    border: none;
    cursor: pointer;
    transition: color 0.2s;
}

.bottom-nav-item .nav-icon {
    width: 24px;
    height: 24px;
}

.bottom-nav-item span {
    font-size: 11px;
    font-weight: 500;
}

.bottom-nav-item.active {
    color: var(--color-primary, #4CB050);
}

.bottom-nav-item:hover {
    color: var(--color-primary, #4CB050);
}

/* Categories Modal */
.categories-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 100;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}

.categories-modal {
    background: white;
    border-radius: 24px 24px 0 0;
    width: 100%;
    max-width: 515px;
    max-height: 80vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.categories-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #f3f4f6;
    flex-shrink: 0;
}

.categories-modal-header h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.close-modal {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.close-modal svg {
    width: 18px;
    height: 18px;
    color: #6b7280;
}

.categories-modal-body {
    padding: 12px 16px 80px;
    overflow-y: auto;
}

/* Category Items */
.category-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 12px;
    text-decoration: none;
    color: #1f2937;
    transition: background 0.2s;
}

.category-item:hover,
.category-item:active {
    background: #f9fafb;
}

.category-item.all-products {
    background: #f0fdf4;
    margin-bottom: 8px;
}

.category-item.all-products:hover {
    background: #dcfce7;
}

.category-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.category-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-icon svg {
    width: 22px;
    height: 22px;
    color: #4CB050;
}

.category-item span,
.category-name {
    flex: 1;
    font-size: 14px;
    font-weight: 500;
}

.category-count {
    font-size: 12px;
    color: #9ca3af;
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 10px;
}

.arrow-icon {
    width: 18px;
    height: 18px;
    color: #9ca3af;
}

.expand-icon {
    width: 20px;
    height: 20px;
    color: #9ca3af;
    transition: transform 0.2s;
}

.expand-icon.rotated {
    transform: rotate(180deg);
}

.category-link {
    display: flex;
    align-items: center;
}

/* Parent Category */
.parent-category {
    cursor: pointer;
}

/* Sub Categories */
.sub-categories {
    padding-right: 56px;
    overflow: hidden;
}

.sub-category-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    border-radius: 8px;
    text-decoration: none;
    color: #4b5563;
    font-size: 13px;
    transition: background 0.2s;
}

.sub-category-item:hover {
    background: #f9fafb;
    color: #4CB050;
}

/* Animations */
[x-cloak] { display: none !important; }

.fade-enter { animation: fadeIn 0.2s ease-out; }
.fade-leave { animation: fadeOut 0.2s ease-out; }
.slide-enter { animation: slideUp 0.3s ease-out; }
.slide-leave { animation: slideDown 0.2s ease-out; }

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}
@keyframes slideUp {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}
@keyframes slideDown {
    from { transform: translateY(0); }
    to { transform: translateY(100%); }
}
</style>
