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

    if (empty($stories)) return;
    ?>
    <section class="stories-section">
        <div class="stories-scroll">
            <?php foreach ($stories as $story) :
                $link = !empty($story['link']) ? $story['link'] : '#';
                $title = !empty($story['title']) ? $story['title'] : '';
            ?>
                <a href="<?php echo esc_url($link); ?>" class="story-item">
                    <div class="story-ring">
                        <img src="<?php echo esc_url($story['image']); ?>" alt="<?php echo esc_attr($title); ?>" class="story-img" loading="lazy">
                    </div>
                    <?php if ($title) : ?>
                        <span class="story-title"><?php echo esc_html($title); ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <style>
    .stories-section {
        padding: 12px 0 4px;
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
    }
    .story-ring {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        padding: 3px;
        background: linear-gradient(135deg, #f59e0b, #ef4444, #ec4899, #8b5cf6);
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
    </style>
    <?php
}
