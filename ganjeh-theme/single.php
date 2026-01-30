<?php
/**
 * Single Blog Post Template
 *
 * @package Ganjeh
 */

get_header();

while (have_posts()) : the_post();
    $categories = get_the_category();
    $tags = get_the_tags();
    $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
    $author_id = get_the_author_meta('ID');
    $author_name = get_the_author();
    $author_avatar = get_avatar_url($author_id, ['size' => 48]);

    // Related posts
    $related_posts = [];
    if ($categories) {
        $related_posts = get_posts([
            'category__in' => wp_list_pluck($categories, 'term_id'),
            'post__not_in' => [get_the_ID()],
            'posts_per_page' => 3,
            'orderby' => 'rand',
        ]);
    }
?>

<article class="single-post">
    <!-- Header -->
    <header class="post-header">
        <a href="javascript:history.back()" class="back-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <div class="header-actions">
            <button type="button" class="action-btn share-btn" onclick="sharePost()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
            </button>
            <button type="button" class="action-btn bookmark-btn" onclick="bookmarkPost()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </button>
        </div>
    </header>

    <!-- Featured Image -->
    <?php if ($featured_image) : ?>
    <div class="post-featured-image">
        <img src="<?php echo esc_url($featured_image); ?>" alt="<?php the_title_attribute(); ?>">
    </div>
    <?php endif; ?>

    <!-- Content -->
    <div class="post-container">
        <!-- Category & Title -->
        <div class="post-intro">
            <?php if ($categories) : ?>
            <a href="<?php echo get_category_link($categories[0]->term_id); ?>" class="post-category">
                <?php echo esc_html($categories[0]->name); ?>
            </a>
            <?php endif; ?>

            <h1 class="post-title"><?php the_title(); ?></h1>

            <!-- Meta -->
            <div class="post-meta-box">
                <div class="author-info">
                    <img src="<?php echo esc_url($author_avatar); ?>" alt="<?php echo esc_attr($author_name); ?>" class="author-avatar">
                    <div>
                        <span class="author-name"><?php echo esc_html($author_name); ?></span>
                        <span class="post-date"><?php echo get_the_date('j F Y'); ?></span>
                    </div>
                </div>
                <div class="post-stats">
                    <span class="read-time">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?php echo ganjeh_reading_time(get_the_content()); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Post Content -->
        <div class="post-content">
            <?php the_content(); ?>
        </div>

        <!-- Tags -->
        <?php if ($tags) : ?>
        <div class="post-tags">
            <span class="tags-label"><?php _e('برچسب‌ها:', 'ganjeh'); ?></span>
            <div class="tags-list">
                <?php foreach ($tags as $tag) : ?>
                <a href="<?php echo get_tag_link($tag->term_id); ?>" class="tag-item">
                    #<?php echo esc_html($tag->name); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Author Box -->
        <div class="author-box">
            <img src="<?php echo esc_url($author_avatar); ?>" alt="<?php echo esc_attr($author_name); ?>" class="author-avatar-lg">
            <div class="author-details">
                <span class="author-label"><?php _e('نویسنده', 'ganjeh'); ?></span>
                <h4 class="author-name-lg"><?php echo esc_html($author_name); ?></h4>
                <?php if (get_the_author_meta('description')) : ?>
                <p class="author-bio"><?php echo esc_html(get_the_author_meta('description')); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Share Box -->
        <div class="share-box">
            <span class="share-label"><?php _e('اشتراک‌گذاری:', 'ganjeh'); ?></span>
            <div class="share-buttons">
                <a href="https://t.me/share/url?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" target="_blank" class="share-btn-item telegram">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.18 1.897-.962 6.502-1.359 8.627-.168.9-.5 1.201-.82 1.23-.697.064-1.226-.461-1.901-.903-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.139-5.062 3.345-.479.329-.913.489-1.302.481-.428-.009-1.252-.242-1.865-.442-.752-.244-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.831-2.529 6.998-3.015 3.333-1.386 4.025-1.627 4.477-1.635.099-.002.321.023.465.141.121.099.154.232.17.325.015.094.034.31.019.478z"/></svg>
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" target="_blank" class="share-btn-item twitter">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
                <a href="https://wa.me/?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>" target="_blank" class="share-btn-item whatsapp">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </a>
                <button type="button" class="share-btn-item copy" onclick="copyLink()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Related Posts -->
        <?php if (!empty($related_posts)) : ?>
        <div class="related-posts">
            <h3 class="related-title"><?php _e('مطالب مرتبط', 'ganjeh'); ?></h3>
            <div class="related-grid">
                <?php foreach ($related_posts as $post) : setup_postdata($post);
                    $thumb = get_the_post_thumbnail_url($post->ID, 'medium') ?: GANJEH_URI . '/assets/images/placeholder.jpg';
                ?>
                <a href="<?php echo get_permalink($post->ID); ?>" class="related-card">
                    <div class="related-thumb">
                        <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr(get_the_title($post->ID)); ?>">
                    </div>
                    <h4><?php echo get_the_title($post->ID); ?></h4>
                    <span class="related-date"><?php echo get_the_date('j F', $post->ID); ?></span>
                </a>
                <?php endforeach; wp_reset_postdata(); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Comments -->
        <?php if (comments_open() || get_comments_number()) : ?>
        <div class="post-comments">
            <?php comments_template(); ?>
        </div>
        <?php endif; ?>
    </div>
</article>

<script>
function sharePost() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo esc_js(get_the_title()); ?>',
            url: '<?php echo esc_js(get_permalink()); ?>'
        });
    } else {
        copyLink();
    }
}

function bookmarkPost() {
    alert('<?php echo esc_js(__('این قابلیت به زودی فعال می‌شود', 'ganjeh')); ?>');
}

function copyLink() {
    navigator.clipboard.writeText('<?php echo esc_js(get_permalink()); ?>').then(() => {
        alert('<?php echo esc_js(__('لینک کپی شد', 'ganjeh')); ?>');
    });
}
</script>

<style>
.single-post {
    min-height: 100vh;
    background: #f9fafb;
    padding-bottom: 100px;
}

/* Header */
.post-header {
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

.back-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #374151;
}

.back-btn svg {
    width: 20px;
    height: 20px;
}

.header-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: none;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #4b5563;
}

.action-btn svg {
    width: 20px;
    height: 20px;
}

/* Featured Image */
.post-featured-image {
    width: 100%;
    aspect-ratio: 16/9;
    overflow: hidden;
}

.post-featured-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Container */
.post-container {
    padding: 0 16px;
}

/* Intro */
.post-intro {
    background: white;
    margin: -20px 0 16px;
    padding: 20px 16px;
    border-radius: 20px 20px 16px 16px;
    position: relative;
    z-index: 10;
}

.post-category {
    display: inline-block;
    font-size: 12px;
    font-weight: 600;
    color: #4CB050;
    background: #f0fdf4;
    padding: 6px 12px;
    border-radius: 8px;
    text-decoration: none;
    margin-bottom: 12px;
}

.post-title {
    font-size: 20px;
    font-weight: 700;
    line-height: 1.5;
    color: #1f2937;
    margin: 0 0 16px;
}

.post-meta-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid #f3f4f6;
}

.author-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.author-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.author-name {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
}

.post-date {
    font-size: 12px;
    color: #9ca3af;
}

.post-stats .read-time {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: #6b7280;
}

.post-stats svg {
    width: 16px;
    height: 16px;
}

/* Content */
.post-content {
    background: white;
    padding: 20px 16px;
    border-radius: 16px;
    margin-bottom: 16px;
}

.post-content p {
    font-size: 15px;
    line-height: 1.9;
    color: #374151;
    margin: 0 0 16px;
}

.post-content h2,
.post-content h3,
.post-content h4 {
    color: #1f2937;
    margin: 24px 0 12px;
    line-height: 1.4;
}

.post-content h2 { font-size: 18px; }
.post-content h3 { font-size: 16px; }
.post-content h4 { font-size: 15px; }

.post-content img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin: 16px 0;
}

.post-content ul,
.post-content ol {
    padding-right: 20px;
    margin: 16px 0;
}

.post-content li {
    font-size: 14px;
    line-height: 1.8;
    color: #4b5563;
    margin-bottom: 8px;
}

.post-content blockquote {
    background: #f9fafb;
    border-right: 4px solid #4CB050;
    padding: 16px;
    margin: 16px 0;
    border-radius: 0 12px 12px 0;
}

.post-content blockquote p {
    margin: 0;
    font-style: italic;
    color: #4b5563;
}

/* Tags */
.post-tags {
    background: white;
    padding: 16px;
    border-radius: 16px;
    margin-bottom: 16px;
}

.tags-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 10px;
}

.tags-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.tag-item {
    font-size: 13px;
    color: #4CB050;
    background: #f0fdf4;
    padding: 6px 12px;
    border-radius: 8px;
    text-decoration: none;
}

/* Author Box */
.author-box {
    background: white;
    padding: 16px;
    border-radius: 16px;
    margin-bottom: 16px;
    display: flex;
    gap: 14px;
}

.author-avatar-lg {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.author-details {
    flex: 1;
}

.author-label {
    font-size: 11px;
    color: #9ca3af;
}

.author-name-lg {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
    margin: 2px 0 6px;
}

.author-bio {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.5;
    margin: 0;
}

/* Share Box */
.share-box {
    background: white;
    padding: 16px;
    border-radius: 16px;
    margin-bottom: 16px;
}

.share-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
}

.share-buttons {
    display: flex;
    gap: 10px;
}

.share-btn-item {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    color: white;
}

.share-btn-item svg {
    width: 20px;
    height: 20px;
}

.share-btn-item.telegram { background: #0088cc; }
.share-btn-item.twitter { background: #1da1f2; }
.share-btn-item.whatsapp { background: #25d366; }
.share-btn-item.copy { background: #6b7280; }

/* Related Posts */
.related-posts {
    background: white;
    padding: 16px;
    border-radius: 16px;
    margin-bottom: 16px;
}

.related-title {
    font-size: 15px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 16px;
}

.related-grid {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 4px;
    -webkit-overflow-scrolling: touch;
}

.related-card {
    flex-shrink: 0;
    width: 160px;
    text-decoration: none;
}

.related-thumb {
    width: 100%;
    aspect-ratio: 4/3;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 8px;
}

.related-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-card h4 {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
    line-height: 1.4;
    margin: 0 0 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.related-date {
    font-size: 11px;
    color: #9ca3af;
}

/* Comments */
.post-comments {
    background: white;
    padding: 16px;
    border-radius: 16px;
}
</style>

<?php
endwhile;
get_footer();
?>
