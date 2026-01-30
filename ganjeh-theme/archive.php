<?php
/**
 * Blog Archive Template
 *
 * @package Ganjeh
 */

get_header();

// Get blog categories
$blog_categories = get_categories([
    'hide_empty' => true,
    'orderby' => 'count',
    'order' => 'DESC',
]);

$current_cat = get_query_var('cat');
?>

<div class="blog-page">
    <!-- Header -->
    <header class="blog-header">
        <h1><?php _e('مجله گنجه', 'ganjeh'); ?></h1>
        <p><?php _e('آخرین مقالات و اخبار', 'ganjeh'); ?></p>
    </header>

    <!-- Categories Tabs -->
    <?php if (!empty($blog_categories)) : ?>
    <div class="blog-categories">
        <div class="categories-scroll">
            <a href="<?php echo get_permalink(get_option('page_for_posts')); ?>"
               class="cat-tab <?php echo !$current_cat ? 'active' : ''; ?>">
                <?php _e('همه', 'ganjeh'); ?>
            </a>
            <?php foreach ($blog_categories as $cat) : ?>
            <a href="<?php echo get_category_link($cat->term_id); ?>"
               class="cat-tab <?php echo $current_cat == $cat->term_id ? 'active' : ''; ?>">
                <?php echo esc_html($cat->name); ?>
                <span class="cat-count"><?php echo $cat->count; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Posts Grid -->
    <div class="blog-content">
        <?php if (have_posts()) : ?>

            <!-- Featured Post (First Post) -->
            <?php if (!is_paged() && !$current_cat) :
                the_post();
                $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'large') ?: GANJEH_URI . '/assets/images/placeholder.jpg';
            ?>
            <article class="featured-post">
                <a href="<?php the_permalink(); ?>" class="featured-image">
                    <img src="<?php echo esc_url($featured_image); ?>" alt="<?php the_title_attribute(); ?>">
                    <div class="featured-overlay">
                        <span class="featured-badge"><?php _e('ویژه', 'ganjeh'); ?></span>
                    </div>
                </a>
                <div class="featured-content">
                    <?php
                    $categories = get_the_category();
                    if ($categories) : ?>
                    <a href="<?php echo get_category_link($categories[0]->term_id); ?>" class="post-category">
                        <?php echo esc_html($categories[0]->name); ?>
                    </a>
                    <?php endif; ?>
                    <h2 class="featured-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                    <p class="featured-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                    <div class="post-meta">
                        <span class="post-date">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <?php echo get_the_date('j F Y'); ?>
                        </span>
                        <span class="post-read-time">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <?php echo ganjeh_reading_time(get_the_content()); ?>
                        </span>
                    </div>
                </div>
            </article>
            <?php endif; ?>

            <!-- Posts List -->
            <div class="posts-grid">
                <?php while (have_posts()) : the_post();
                    $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: GANJEH_URI . '/assets/images/placeholder.jpg';
                    $categories = get_the_category();
                ?>
                <article class="post-card">
                    <a href="<?php the_permalink(); ?>" class="post-thumb">
                        <img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>">
                    </a>
                    <div class="post-content">
                        <?php if ($categories) : ?>
                        <a href="<?php echo get_category_link($categories[0]->term_id); ?>" class="post-category">
                            <?php echo esc_html($categories[0]->name); ?>
                        </a>
                        <?php endif; ?>
                        <h3 class="post-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <div class="post-meta">
                            <span class="post-date"><?php echo get_the_date('j F'); ?></span>
                            <span class="dot">·</span>
                            <span class="post-read-time"><?php echo ganjeh_reading_time(get_the_content()); ?></span>
                        </div>
                    </div>
                </article>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <div class="blog-pagination">
                <?php
                the_posts_pagination([
                    'mid_size' => 2,
                    'prev_text' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>',
                    'next_text' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>',
                ]);
                ?>
            </div>

        <?php else : ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                </div>
                <h2><?php _e('مقاله‌ای یافت نشد', 'ganjeh'); ?></h2>
                <p><?php _e('در حال حاضر مقاله‌ای در این بخش وجود ندارد.', 'ganjeh'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.blog-page {
    min-height: 100vh;
    background: #f9fafb;
    padding-bottom: 100px;
}

/* Header */
.blog-header {
    background: linear-gradient(135deg, #4CB050, #3d9142);
    padding: 24px 16px;
    text-align: center;
    color: white;
}

.blog-header h1 {
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 4px;
}

.blog-header p {
    font-size: 13px;
    opacity: 0.9;
    margin: 0;
}

/* Categories */
.blog-categories {
    background: white;
    border-bottom: 1px solid #f3f4f6;
    position: sticky;
    top: 0;
    z-index: 30;
}

.categories-scroll {
    display: flex;
    gap: 8px;
    padding: 12px 16px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.categories-scroll::-webkit-scrollbar {
    display: none;
}

.cat-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #f3f4f6;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    color: #4b5563;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.2s;
}

.cat-tab:hover {
    background: #e5e7eb;
}

.cat-tab.active {
    background: #4CB050;
    color: white;
}

.cat-tab .cat-count {
    font-size: 11px;
    background: rgba(0,0,0,0.1);
    padding: 2px 6px;
    border-radius: 10px;
}

.cat-tab.active .cat-count {
    background: rgba(255,255,255,0.2);
}

/* Content */
.blog-content {
    padding: 16px;
}

/* Featured Post */
.featured-post {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.featured-image {
    display: block;
    position: relative;
    aspect-ratio: 16/9;
    overflow: hidden;
}

.featured-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.featured-overlay {
    position: absolute;
    top: 12px;
    right: 12px;
}

.featured-badge {
    background: #4CB050;
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
}

.featured-content {
    padding: 16px;
}

.post-category {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    color: #4CB050;
    background: #f0fdf4;
    padding: 4px 10px;
    border-radius: 6px;
    text-decoration: none;
    margin-bottom: 10px;
}

.featured-title {
    font-size: 17px;
    font-weight: 700;
    line-height: 1.5;
    margin: 0 0 10px;
}

.featured-title a {
    color: #1f2937;
    text-decoration: none;
}

.featured-excerpt {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.7;
    margin: 0 0 12px;
}

.post-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 12px;
    color: #9ca3af;
}

.post-meta svg {
    width: 14px;
    height: 14px;
    margin-left: 4px;
}

.post-meta .dot {
    color: #d1d5db;
}

.post-date,
.post-read-time {
    display: flex;
    align-items: center;
}

/* Posts Grid */
.posts-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.post-card {
    display: flex;
    gap: 12px;
    background: white;
    border-radius: 14px;
    padding: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.post-thumb {
    width: 100px;
    height: 100px;
    border-radius: 10px;
    overflow: hidden;
    flex-shrink: 0;
}

.post-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.post-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.post-content .post-category {
    margin-bottom: 6px;
    font-size: 10px;
    padding: 2px 8px;
}

.post-title {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.5;
    margin: 0 0 8px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.post-title a {
    color: #1f2937;
    text-decoration: none;
}

.post-card .post-meta {
    gap: 6px;
    font-size: 11px;
}

/* Pagination */
.blog-pagination {
    margin-top: 24px;
}

.blog-pagination .nav-links {
    display: flex;
    justify-content: center;
    gap: 8px;
}

.blog-pagination .page-numbers {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: white;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #4b5563;
    text-decoration: none;
    transition: all 0.2s;
}

.blog-pagination .page-numbers:hover {
    background: #f3f4f6;
}

.blog-pagination .page-numbers.current {
    background: #4CB050;
    color: white;
}

.blog-pagination .page-numbers svg {
    width: 18px;
    height: 18px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 16px;
    color: #d1d5db;
}

.empty-icon svg {
    width: 100%;
    height: 100%;
}

.empty-state h2 {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 8px;
}

.empty-state p {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
}
</style>

<?php get_footer(); ?>
