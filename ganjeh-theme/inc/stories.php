<?php
/**
 * Story Feature - Admin Settings & Frontend Rendering
 *
 * Instagram-like stories between search bar and hero slider
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Stories submenu under site settings
 */
function ganjeh_stories_admin_menu() {
    add_submenu_page(
        'dst-website-settings',
        __('مدیریت استوری‌ها', 'ganjeh'),
        __('استوری‌ها', 'ganjeh'),
        'manage_options',
        'ganjeh-stories',
        'ganjeh_stories_admin_page'
    );
}
add_action('admin_menu', 'ganjeh_stories_admin_menu', 10003);

/**
 * Enqueue media uploader on stories page
 */
function ganjeh_stories_enqueue_media() {
    $screen = get_current_screen();
    if ($screen && strpos($screen->id, 'ganjeh-stories') !== false) {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'ganjeh_stories_enqueue_media');

/**
 * Get all stories
 */
function ganjeh_get_stories() {
    $stories = get_option('ganjeh_stories', []);
    if (!is_array($stories)) return [];
    // Sort by order
    usort($stories, function($a, $b) {
        return ($a['order'] ?? 0) - ($b['order'] ?? 0);
    });
    return $stories;
}

/**
 * Save stories via AJAX
 */
function ganjeh_save_stories_ajax() {
    check_ajax_referer('ganjeh_stories_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('دسترسی ندارید');
    }

    $stories = isset($_POST['stories']) ? $_POST['stories'] : [];
    $sanitized = [];

    foreach ($stories as $story) {
        $sanitized[] = [
            'title'    => sanitize_text_field($story['title'] ?? ''),
            'image'    => esc_url_raw($story['image'] ?? ''),
            'link'     => esc_url_raw($story['link'] ?? ''),
            'order'    => absint($story['order'] ?? 0),
            'active'   => !empty($story['active']),
        ];
    }

    update_option('ganjeh_stories', $sanitized);
    wp_send_json_success('ذخیره شد');
}
add_action('wp_ajax_ganjeh_save_stories', 'ganjeh_save_stories_ajax');

/**
 * Admin page for managing stories
 */
function ganjeh_stories_admin_page() {
    $stories = ganjeh_get_stories();
    ?>
    <div class="wrap">
        <h1><?php _e('مدیریت استوری‌ها', 'ganjeh'); ?></h1>
        <p><?php _e('استوری‌ها بین سرچ و بنر اصلی نمایش داده می‌شوند. تصویر دایره‌ای با عنوان زیرش.', 'ganjeh'); ?></p>

        <div id="ganjeh-stories-app">
            <div id="ganjeh-stories-list" style="margin-top:20px;"></div>

            <p style="margin-top:15px;">
                <button type="button" class="button button-primary" onclick="ganjehAddStory()">
                    <?php _e('+ افزودن استوری', 'ganjeh'); ?>
                </button>
                <button type="button" class="button button-hero" onclick="ganjehSaveStories()" style="margin-right:10px;">
                    <?php _e('ذخیره تغییرات', 'ganjeh'); ?>
                </button>
            </p>
            <div id="ganjeh-stories-msg" style="margin-top:10px;"></div>
        </div>
    </div>

    <script>
    var ganjehStories = <?php echo wp_json_encode($stories); ?> || [];

    function ganjehRenderStories() {
        var list = document.getElementById('ganjeh-stories-list');
        if (ganjehStories.length === 0) {
            list.innerHTML = '<p style="color:#999;font-style:italic;">هنوز استوری اضافه نشده است.</p>';
            return;
        }
        var html = '<table class="wp-list-table widefat fixed striped"><thead><tr>'
            + '<th style="width:60px;">تصویر</th>'
            + '<th>عنوان</th>'
            + '<th>لینک</th>'
            + '<th style="width:60px;">ترتیب</th>'
            + '<th style="width:60px;">فعال</th>'
            + '<th style="width:80px;">عملیات</th>'
            + '</tr></thead><tbody>';

        ganjehStories.forEach(function(s, i) {
            var imgPreview = s.image ? '<img src="' + s.image + '" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">' : '<span style="color:#ccc;">—</span>';
            html += '<tr>'
                + '<td>' + imgPreview + '</td>'
                + '<td><input type="text" value="' + (s.title || '') + '" onchange="ganjehStories[' + i + '].title=this.value" style="width:100%;"></td>'
                + '<td><input type="url" value="' + (s.link || '') + '" onchange="ganjehStories[' + i + '].link=this.value" style="width:100%;" dir="ltr"></td>'
                + '<td><input type="number" value="' + (s.order || 0) + '" onchange="ganjehStories[' + i + '].order=parseInt(this.value)||0" style="width:50px;text-align:center;"></td>'
                + '<td><input type="checkbox"' + (s.active ? ' checked' : '') + ' onchange="ganjehStories[' + i + '].active=this.checked"></td>'
                + '<td><button type="button" class="button" onclick="ganjehSelectImage(' + i + ')">تصویر</button> '
                + '<button type="button" class="button" style="color:#b32d2e;" onclick="ganjehRemoveStory(' + i + ')">حذف</button></td>'
                + '</tr>';
        });
        html += '</tbody></table>';
        list.innerHTML = html;
    }

    function ganjehAddStory() {
        ganjehStories.push({title: '', image: '', link: '', order: ganjehStories.length, active: true});
        ganjehRenderStories();
    }

    function ganjehRemoveStory(i) {
        ganjehStories.splice(i, 1);
        ganjehRenderStories();
    }

    function ganjehSelectImage(i) {
        var frame = wp.media({
            title: 'انتخاب تصویر استوری',
            button: {text: 'انتخاب'},
            multiple: false
        });
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            ganjehStories[i].image = attachment.url;
            ganjehRenderStories();
        });
        frame.open();
    }

    function ganjehSaveStories() {
        var data = new FormData();
        data.append('action', 'ganjeh_save_stories');
        data.append('nonce', '<?php echo wp_create_nonce('ganjeh_stories_nonce'); ?>');
        ganjehStories.forEach(function(s, i) {
            data.append('stories[' + i + '][title]', s.title || '');
            data.append('stories[' + i + '][image]', s.image || '');
            data.append('stories[' + i + '][link]', s.link || '');
            data.append('stories[' + i + '][order]', s.order || 0);
            data.append('stories[' + i + '][active]', s.active ? '1' : '');
        });

        fetch(ajaxurl, {method: 'POST', body: data})
            .then(function(r) { return r.json(); })
            .then(function(r) {
                var msg = document.getElementById('ganjeh-stories-msg');
                if (r.success) {
                    msg.innerHTML = '<div class="notice notice-success"><p>استوری‌ها با موفقیت ذخیره شدند.</p></div>';
                } else {
                    msg.innerHTML = '<div class="notice notice-error"><p>خطا در ذخیره.</p></div>';
                }
                setTimeout(function() { msg.innerHTML = ''; }, 3000);
            });
    }

    ganjehRenderStories();
    </script>
    <?php
}

/**
 * Render stories on frontend
 */
function ganjeh_render_stories() {
    $stories = ganjeh_get_stories();
    // Filter active only
    $stories = array_filter($stories, function($s) {
        return !empty($s['active']) && !empty($s['image']);
    });
    $stories = array_values($stories); // re-index

    if (empty($stories)) return;

    // Prepare stories data for JS
    $stories_json = [];
    foreach ($stories as $s) {
        $stories_json[] = [
            'image' => esc_url($s['image']),
            'title' => esc_html($s['title'] ?? ''),
            'link'  => esc_url($s['link'] ?? ''),
        ];
    }
    ?>
    <section class="stories-section">
        <div class="stories-scroll">
            <?php foreach ($stories as $index => $story) :
                $title = !empty($story['title']) ? $story['title'] : '';
            ?>
                <div class="story-item" onclick="ganjehOpenStory(<?php echo $index; ?>)" data-story-index="<?php echo $index; ?>">
                    <div class="story-ring" id="story-ring-<?php echo $index; ?>">
                        <img src="<?php echo esc_url($story['image']); ?>" alt="<?php echo esc_attr($title); ?>" class="story-img" loading="lazy">
                    </div>
                    <?php if ($title) : ?>
                        <span class="story-title"><?php echo esc_html($title); ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Story Viewer Modal -->
    <div id="story-viewer" class="story-viewer" style="display:none;">
        <div class="story-viewer-overlay" onclick="ganjehCloseStory()"></div>
        <div class="story-viewer-content">
            <!-- Progress bars -->
            <div class="story-progress-bar">
                <?php foreach ($stories as $i => $s) : ?>
                    <div class="story-progress-item" data-index="<?php echo $i; ?>">
                        <div class="story-progress-fill"></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Header -->
            <div class="story-viewer-header">
                <div class="story-viewer-user">
                    <img id="story-viewer-thumb" src="" alt="" class="story-viewer-avatar">
                    <span id="story-viewer-name" class="story-viewer-username"></span>
                </div>
                <button type="button" class="story-viewer-close" onclick="ganjehCloseStory()">
                    <svg width="24" height="24" fill="none" stroke="#fff" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Story Image -->
            <img id="story-viewer-img" src="" alt="" class="story-viewer-image">

            <!-- Navigation areas -->
            <div class="story-nav story-nav-prev" onclick="ganjehPrevStory()"></div>
            <div class="story-nav story-nav-next" onclick="ganjehNextStory()"></div>

            <!-- Link Button -->
            <a id="story-viewer-link" href="#" class="story-viewer-link-btn" style="display:none;" target="_blank">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/></svg>
                مشاهده لینک
            </a>
        </div>
    </div>

    <style>
    .stories-section {
        padding: 12px 0 4px;
        position: relative;
        z-index: 2;
    }
    .stories-scroll {
        display: flex;
        gap: 14px;
        overflow-x: auto;
        padding: 0 16px;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }
    .stories-scroll::-webkit-scrollbar {
        display: none;
    }
    .story-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        flex-shrink: 0;
        cursor: pointer;
        -webkit-tap-highlight-color: rgba(0,0,0,0.05);
    }
    .story-ring {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        padding: 3px;
        background: linear-gradient(135deg, #f59e0b, #ef4444, #ec4899, #8b5cf6);
        transition: background 0.3s;
    }
    .story-ring.viewed {
        background: #d1d5db;
    }
    .story-img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
    }
    .story-title {
        font-size: 11px;
        color: #374151;
        text-align: center;
        max-width: 70px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Story Viewer Modal */
    .story-viewer {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .story-viewer-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.95);
    }
    .story-viewer-content {
        position: relative;
        width: 100%;
        max-width: 450px;
        height: 100%;
        max-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    /* Progress Bar */
    .story-progress-bar {
        position: absolute;
        top: 8px;
        left: 8px;
        right: 8px;
        display: flex;
        gap: 4px;
        z-index: 10;
    }
    .story-progress-item {
        flex: 1;
        height: 3px;
        background: rgba(255,255,255,0.3);
        border-radius: 3px;
        overflow: hidden;
    }
    .story-progress-fill {
        height: 100%;
        width: 0%;
        background: #fff;
        border-radius: 3px;
        transition: none;
    }
    .story-progress-item.viewed .story-progress-fill {
        width: 100%;
    }
    .story-progress-item.active .story-progress-fill {
        width: 0%;
        animation: storyProgress 5s linear forwards;
    }

    @keyframes storyProgress {
        from { width: 0%; }
        to { width: 100%; }
    }

    /* Header */
    .story-viewer-header {
        position: absolute;
        top: 18px;
        left: 12px;
        right: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 10;
    }
    .story-viewer-user {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .story-viewer-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
    }
    .story-viewer-username {
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        text-shadow: 0 1px 4px rgba(0,0,0,0.5);
    }
    .story-viewer-close {
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        opacity: 0.9;
    }
    .story-viewer-close:hover {
        opacity: 1;
    }

    /* Story Image */
    .story-viewer-image {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 12px;
        z-index: 5;
    }

    /* Navigation */
    .story-nav {
        position: absolute;
        top: 60px;
        bottom: 80px;
        width: 40%;
        z-index: 8;
        cursor: pointer;
    }
    .story-nav-prev {
        right: 0;
    }
    .story-nav-next {
        left: 0;
    }

    /* Link Button */
    .story-viewer-link-btn {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        color: #fff;
        text-decoration: none;
        padding: 10px 24px;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        z-index: 10;
        border: 1px solid rgba(255,255,255,0.3);
        transition: background 0.2s;
    }
    .story-viewer-link-btn:hover {
        background: rgba(255,255,255,0.35);
        color: #fff;
    }
    </style>

    <script>
    (function() {
        var storiesData = <?php echo wp_json_encode($stories_json); ?>;
        var currentIndex = 0;
        var progressTimer = null;
        var STORY_DURATION = 5000; // 5 seconds per story
        var STORAGE_KEY = 'ganjeh_viewed_stories';

        // Get viewed stories from localStorage
        function getViewedStories() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
            } catch(e) { return []; }
        }

        // Mark a story as viewed
        function markAsViewed(index) {
            var viewed = getViewedStories();
            var storyId = storiesData[index] ? storiesData[index].image : '';
            if (storyId && viewed.indexOf(storyId) === -1) {
                viewed.push(storyId);
                localStorage.setItem(STORAGE_KEY, JSON.stringify(viewed));
            }
            // Update ring style
            var ring = document.getElementById('story-ring-' + index);
            if (ring) ring.classList.add('viewed');
        }

        // On page load, apply viewed state to rings
        function applyViewedState() {
            var viewed = getViewedStories();
            storiesData.forEach(function(story, i) {
                if (viewed.indexOf(story.image) !== -1) {
                    var ring = document.getElementById('story-ring-' + i);
                    if (ring) ring.classList.add('viewed');
                }
            });
        }

        window.ganjehOpenStory = function(index) {
            currentIndex = index;
            var viewer = document.getElementById('story-viewer');
            viewer.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            showStory(currentIndex);
        };

        window.ganjehCloseStory = function() {
            var viewer = document.getElementById('story-viewer');
            viewer.style.display = 'none';
            document.body.style.overflow = '';
            clearTimeout(progressTimer);
        };

        window.ganjehNextStory = function() {
            if (currentIndex < storiesData.length - 1) {
                currentIndex++;
                showStory(currentIndex);
            } else {
                ganjehCloseStory();
            }
        };

        window.ganjehPrevStory = function() {
            if (currentIndex > 0) {
                currentIndex--;
                showStory(currentIndex);
            }
        };

        function showStory(index) {
            var story = storiesData[index];
            if (!story) return;

            // Mark as viewed
            markAsViewed(index);

            // Update image
            document.getElementById('story-viewer-img').src = story.image;
            document.getElementById('story-viewer-img').alt = story.title;

            // Update header
            document.getElementById('story-viewer-thumb').src = story.image;
            document.getElementById('story-viewer-name').textContent = story.title;

            // Update link button
            var linkBtn = document.getElementById('story-viewer-link');
            if (story.link && story.link !== '' && story.link !== '#') {
                linkBtn.href = story.link;
                linkBtn.style.display = 'flex';
            } else {
                linkBtn.style.display = 'none';
            }

            // Update progress bars
            var items = document.querySelectorAll('.story-progress-item');
            items.forEach(function(item, i) {
                item.classList.remove('active', 'viewed');
                var fill = item.querySelector('.story-progress-fill');
                fill.style.animation = 'none';
                fill.style.width = '0%';

                if (i < index) {
                    item.classList.add('viewed');
                    fill.style.width = '100%';
                } else if (i === index) {
                    item.classList.add('active');
                    // Force reflow for animation restart
                    void fill.offsetWidth;
                    fill.style.animation = 'storyProgress ' + (STORY_DURATION / 1000) + 's linear forwards';
                }
            });

            // Auto-advance timer
            clearTimeout(progressTimer);
            progressTimer = setTimeout(function() {
                ganjehNextStory();
            }, STORY_DURATION);
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var viewer = document.getElementById('story-viewer');
                if (viewer.style.display !== 'none') {
                    ganjehCloseStory();
                }
            }
        });

        // Apply viewed state on page load
        applyViewedState();
    })();
    </script>
    <?php
}
