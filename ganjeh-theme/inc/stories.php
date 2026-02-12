<?php
/**
 * Story Feature - Tab-based with daily rotation
 *
 * Instagram-like stories between search bar and hero slider.
 * Stories are grouped into tabs. Each day shows a different tab (rotating).
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
 * Get story tabs (with backward compatibility)
 */
function ganjeh_get_story_tabs() {
    $tabs = get_option('ganjeh_story_tabs', null);

    // Backward compatibility: migrate old flat stories into a single tab
    if ($tabs === null) {
        $old_stories = get_option('ganjeh_stories', []);
        if (!empty($old_stories) && is_array($old_stories)) {
            $tabs = [
                ['name' => 'تب ۱', 'stories' => $old_stories]
            ];
            update_option('ganjeh_story_tabs', $tabs);
        } else {
            $tabs = [];
        }
    }

    if (!is_array($tabs)) return [];

    // Sort stories within each tab
    foreach ($tabs as &$tab) {
        if (!empty($tab['stories']) && is_array($tab['stories'])) {
            usort($tab['stories'], function($a, $b) {
                return ($a['order'] ?? 0) - ($b['order'] ?? 0);
            });
        } else {
            $tab['stories'] = [];
        }
    }

    return $tabs;
}

/**
 * Get today's stories based on daily tab rotation
 */
function ganjeh_get_today_stories() {
    $tabs = ganjeh_get_story_tabs();
    if (empty($tabs)) return [];

    $active_tabs = [];
    foreach ($tabs as $tab) {
        $active_stories = array_filter($tab['stories'], function($s) {
            return !empty($s['active']) && !empty($s['image']);
        });
        if (!empty($active_stories)) {
            $active_tabs[] = [
                'name' => $tab['name'],
                'stories' => array_values($active_stories),
            ];
        }
    }

    if (empty($active_tabs)) return [];

    $day_of_year = (int) date('z');
    $tab_index = $day_of_year % count($active_tabs);

    return $active_tabs[$tab_index]['stories'];
}

/**
 * Save story tabs via AJAX
 */
function ganjeh_save_stories_ajax() {
    check_ajax_referer('ganjeh_stories_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('دسترسی ندارید');
    }

    $raw_tabs = isset($_POST['tabs']) ? $_POST['tabs'] : [];
    $sanitized_tabs = [];

    foreach ($raw_tabs as $tab) {
        $sanitized_stories = [];
        $stories = isset($tab['stories']) ? $tab['stories'] : [];
        foreach ($stories as $story) {
            $sanitized_stories[] = [
                'title'       => sanitize_text_field($story['title'] ?? ''),
                'image'       => esc_url_raw($story['image'] ?? ''),
                'link'        => esc_url_raw($story['link'] ?? ''),
                'description' => sanitize_textarea_field($story['description'] ?? ''),
                'order'       => absint($story['order'] ?? 0),
                'active'      => !empty($story['active']),
            ];
        }
        $sanitized_tabs[] = [
            'name'    => sanitize_text_field($tab['name'] ?? ''),
            'stories' => $sanitized_stories,
        ];
    }

    update_option('ganjeh_story_tabs', $sanitized_tabs);
    wp_send_json_success('ذخیره شد');
}
add_action('wp_ajax_ganjeh_save_stories', 'ganjeh_save_stories_ajax');

/**
 * Admin page for managing stories
 */
function ganjeh_stories_admin_page() {
    $tabs = ganjeh_get_story_tabs();
    $active_tabs_count = 0;
    foreach ($tabs as $tab) {
        $has_active = false;
        foreach ($tab['stories'] as $s) {
            if (!empty($s['active']) && !empty($s['image'])) { $has_active = true; break; }
        }
        if ($has_active) $active_tabs_count++;
    }
    $day_of_year = (int) date('z');
    $today_tab = $active_tabs_count > 0 ? ($day_of_year % $active_tabs_count) : 0;
    ?>
    <div class="wrap">
        <h1><?php _e('مدیریت استوری‌ها', 'ganjeh'); ?></h1>
        <p><?php _e('استوری‌ها به صورت تب‌بندی هستند. هر روز یک تب به صورت خودکار نمایش داده می‌شود و روز بعد تب بعدی.', 'ganjeh'); ?></p>
        <?php if ($active_tabs_count > 0) : ?>
        <p style="background:#fff3cd;padding:10px 14px;border-radius:6px;border:1px solid #ffc107;display:inline-block;">
            📅 امروز <strong>تب شماره <?php echo $today_tab + 1; ?></strong> نمایش داده می‌شود (از <?php echo $active_tabs_count; ?> تب فعال)
        </p>
        <?php endif; ?>

        <div id="ganjeh-stories-app" style="margin-top:20px;">
            <div id="ganjeh-tab-nav" style="display:flex;gap:0;border-bottom:2px solid #ddd;margin-bottom:0;"></div>
            <div id="ganjeh-tab-content" style="border:1px solid #ddd;border-top:none;padding:20px;background:#fff;"></div>

            <p style="margin-top:15px;display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" class="button" onclick="ganjehAddTab()">+ افزودن تب جدید</button>
                <button type="button" class="button button-primary button-hero" onclick="ganjehSaveTabs()">ذخیره تغییرات</button>
            </p>
            <div id="ganjeh-stories-msg" style="margin-top:10px;"></div>
        </div>
    </div>

    <style>
    .ganjeh-tab-btn {
        padding: 10px 20px;
        border: 1px solid #ddd;
        border-bottom: none;
        background: #f0f0f1;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        color: #50575e;
        margin-bottom: -2px;
        border-radius: 4px 4px 0 0;
        transition: all 0.2s;
    }
    .ganjeh-tab-btn:hover { background: #e5e5e5; }
    .ganjeh-tab-btn.active {
        background: #fff;
        border-bottom-color: #fff;
        color: #1d2327;
    }
    .ganjeh-story-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .ganjeh-story-table th { text-align: right; padding: 8px; border-bottom: 2px solid #ddd; font-size: 13px; }
    .ganjeh-story-table td { padding: 6px 8px; border-bottom: 1px solid #eee; vertical-align: middle; }
    .ganjeh-story-table tr:hover td { background: #f9f9f9; }
    </style>

    <script>
    var ganjehTabs = <?php echo wp_json_encode($tabs); ?> || [];
    var activeTabIndex = 0;

    if (ganjehTabs.length === 0) {
        ganjehTabs.push({name: 'تب ۱', stories: []});
    }

    function ganjehRenderAll() {
        renderTabNav();
        renderTabContent();
    }

    function renderTabNav() {
        var nav = document.getElementById('ganjeh-tab-nav');
        var html = '';
        ganjehTabs.forEach(function(tab, i) {
            var cls = i === activeTabIndex ? ' active' : '';
            html += '<button type="button" class="ganjeh-tab-btn' + cls + '" onclick="ganjehSwitchTab(' + i + ')">'
                + (tab.name || 'تب ' + (i+1))
                + ' <small style="color:#999;">(' + (tab.stories ? tab.stories.length : 0) + ')</small>'
                + '</button>';
        });
        nav.innerHTML = html;
    }

    function escAttr(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML.replace(/"/g, '&quot;');
    }

    function renderTabContent() {
        var content = document.getElementById('ganjeh-tab-content');
        var tab = ganjehTabs[activeTabIndex];
        if (!tab) { content.innerHTML = ''; return; }

        var html = '';
        html += '<div style="display:flex;align-items:center;gap:10px;margin-bottom:15px;">';
        html += '<label style="font-weight:600;">نام تب:</label>';
        html += '<input type="text" value="' + escAttr(tab.name) + '" onchange="ganjehTabs[' + activeTabIndex + '].name=this.value;renderTabNav();" style="width:200px;">';
        if (ganjehTabs.length > 1) {
            html += '<button type="button" class="button" style="color:#b32d2e;" onclick="ganjehRemoveTab(' + activeTabIndex + ')">حذف این تب</button>';
        }
        html += '</div>';

        var stories = tab.stories || [];
        if (stories.length === 0) {
            html += '<p style="color:#999;font-style:italic;">هنوز استوری‌ای در این تب اضافه نشده.</p>';
        } else {
            html += '<table class="ganjeh-story-table"><thead><tr>'
                + '<th style="width:55px;">تصویر</th>'
                + '<th style="width:120px;">عنوان</th>'
                + '<th>توضیحات</th>'
                + '<th style="width:140px;">لینک</th>'
                + '<th style="width:50px;">ترتیب</th>'
                + '<th style="width:40px;">فعال</th>'
                + '<th style="width:100px;">عملیات</th>'
                + '</tr></thead><tbody>';

            stories.forEach(function(s, i) {
                var imgPreview = s.image
                    ? '<img src="' + s.image + '" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">'
                    : '<span style="color:#ccc;">—</span>';
                html += '<tr>'
                    + '<td>' + imgPreview + '</td>'
                    + '<td><input type="text" value="' + escAttr(s.title) + '" onchange="ganjehTabs[' + activeTabIndex + '].stories[' + i + '].title=this.value" style="width:100%;"></td>'
                    + '<td><input type="text" value="' + escAttr(s.description) + '" onchange="ganjehTabs[' + activeTabIndex + '].stories[' + i + '].description=this.value" style="width:100%;" placeholder="توضیحات روی استوری..."></td>'
                    + '<td><input type="url" value="' + escAttr(s.link) + '" onchange="ganjehTabs[' + activeTabIndex + '].stories[' + i + '].link=this.value" style="width:100%;" dir="ltr"></td>'
                    + '<td><input type="number" value="' + (s.order || 0) + '" onchange="ganjehTabs[' + activeTabIndex + '].stories[' + i + '].order=parseInt(this.value)||0" style="width:45px;text-align:center;"></td>'
                    + '<td><input type="checkbox"' + (s.active ? ' checked' : '') + ' onchange="ganjehTabs[' + activeTabIndex + '].stories[' + i + '].active=this.checked"></td>'
                    + '<td>'
                    + '<button type="button" class="button button-small" onclick="ganjehSelectImage(' + activeTabIndex + ',' + i + ')">تصویر</button> '
                    + '<button type="button" class="button button-small" style="color:#b32d2e;" onclick="ganjehRemoveStory(' + activeTabIndex + ',' + i + ')">حذف</button>'
                    + '</td></tr>';
            });
            html += '</tbody></table>';
        }

        html += '<p style="margin-top:12px;"><button type="button" class="button" onclick="ganjehAddStory(' + activeTabIndex + ')">+ افزودن استوری</button></p>';
        content.innerHTML = html;
    }

    function ganjehSwitchTab(i) { activeTabIndex = i; ganjehRenderAll(); }

    function ganjehAddTab() {
        ganjehTabs.push({name: 'تب ' + (ganjehTabs.length + 1), stories: []});
        activeTabIndex = ganjehTabs.length - 1;
        ganjehRenderAll();
    }

    function ganjehRemoveTab(i) {
        if (!confirm('آیا از حذف این تب مطمئنید؟')) return;
        ganjehTabs.splice(i, 1);
        if (activeTabIndex >= ganjehTabs.length) activeTabIndex = ganjehTabs.length - 1;
        if (activeTabIndex < 0) activeTabIndex = 0;
        ganjehRenderAll();
    }

    function ganjehAddStory(tabIndex) {
        if (!ganjehTabs[tabIndex].stories) ganjehTabs[tabIndex].stories = [];
        ganjehTabs[tabIndex].stories.push({title: '', image: '', link: '', description: '', order: ganjehTabs[tabIndex].stories.length, active: true});
        renderTabContent();
    }

    function ganjehRemoveStory(tabIndex, storyIndex) {
        ganjehTabs[tabIndex].stories.splice(storyIndex, 1);
        renderTabContent();
    }

    function ganjehSelectImage(tabIndex, storyIndex) {
        var frame = wp.media({ title: 'انتخاب تصویر استوری', button: {text: 'انتخاب'}, multiple: false });
        frame.on('select', function() {
            ganjehTabs[tabIndex].stories[storyIndex].image = frame.state().get('selection').first().toJSON().url;
            renderTabContent();
        });
        frame.open();
    }

    function ganjehSaveTabs() {
        var data = new FormData();
        data.append('action', 'ganjeh_save_stories');
        data.append('nonce', '<?php echo wp_create_nonce('ganjeh_stories_nonce'); ?>');

        ganjehTabs.forEach(function(tab, t) {
            data.append('tabs[' + t + '][name]', tab.name || '');
            var stories = tab.stories || [];
            stories.forEach(function(s, i) {
                var p = 'tabs[' + t + '][stories][' + i + ']';
                data.append(p + '[title]', s.title || '');
                data.append(p + '[image]', s.image || '');
                data.append(p + '[link]', s.link || '');
                data.append(p + '[description]', s.description || '');
                data.append(p + '[order]', s.order || 0);
                data.append(p + '[active]', s.active ? '1' : '');
            });
            if (stories.length === 0) {
                data.append('tabs[' + t + '][stories]', '');
            }
        });

        fetch(ajaxurl, {method: 'POST', body: data})
            .then(function(r) { return r.json(); })
            .then(function(r) {
                var msg = document.getElementById('ganjeh-stories-msg');
                msg.innerHTML = r.success
                    ? '<div class="notice notice-success"><p>ذخیره شد.</p></div>'
                    : '<div class="notice notice-error"><p>خطا در ذخیره.</p></div>';
                setTimeout(function() { msg.innerHTML = ''; }, 3000);
            });
    }

    ganjehRenderAll();
    </script>
    <?php
}

/**
 * Render stories on frontend (Basalam-style)
 */
function ganjeh_render_stories() {
    $stories = ganjeh_get_today_stories();

    if (empty($stories)) return;

    $stories_json = [];
    foreach ($stories as $s) {
        $stories_json[] = [
            'image'       => esc_url($s['image']),
            'title'       => esc_html($s['title'] ?? ''),
            'link'        => esc_url($s['link'] ?? ''),
            'description' => esc_html($s['description'] ?? ''),
        ];
    }
    ?>
    <section class="stories-section">
        <div class="stories-scroll">
            <?php foreach ($stories as $index => $story) :
                $title = !empty($story['title']) ? $story['title'] : '';
            ?>
                <div class="story-item" onclick="ganjehOpenStory(<?php echo $index; ?>)">
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
        <div class="story-viewer-bg" onclick="ganjehCloseStory()"></div>
        <div class="story-viewer-card">
            <!-- Progress bars -->
            <div class="sv-progress">
                <?php foreach ($stories as $i => $s) : ?>
                    <div class="sv-progress-seg" data-index="<?php echo $i; ?>"><div class="sv-progress-fill"></div></div>
                <?php endforeach; ?>
            </div>

            <!-- Header: close + user info -->
            <div class="sv-header">
                <button type="button" class="sv-close" onclick="ganjehCloseStory()">
                    <svg width="22" height="22" fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="sv-user">
                    <span id="sv-name" class="sv-username"></span>
                    <img id="sv-thumb" src="" alt="" class="sv-avatar">
                </div>
            </div>

            <!-- Story Image -->
            <img id="sv-img" src="" alt="" class="sv-image">

            <!-- Description overlay -->
            <div id="sv-desc" class="sv-description" style="display:none;"></div>

            <!-- Link overlay -->
            <a id="sv-link" href="#" class="sv-link-btn" style="display:none;" target="_blank">
                <span>مشاهده لینک</span>
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </a>

            <!-- Tap zones for navigation -->
            <div class="sv-tap sv-tap-prev" onclick="ganjehPrevStory()"></div>
            <div class="sv-tap sv-tap-next" onclick="ganjehNextStory()"></div>
        </div>
    </div>

    <style>
    /* Story Circles - Basalam style (bigger) */
    .stories-section {
        padding: 12px 0 4px;
        position: relative;
        z-index: 2;
    }
    .stories-scroll {
        display: flex;
        gap: 16px;
        overflow-x: auto;
        padding: 0 16px;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }
    .stories-scroll::-webkit-scrollbar { display: none; }
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
        width: 76px;
        height: 76px;
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
        max-width: 76px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Story Viewer - Basalam style card */
    .story-viewer {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .story-viewer-bg {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.9);
    }
    .story-viewer-card {
        position: relative;
        width: 100%;
        max-width: 420px;
        height: 92vh;
        max-height: 750px;
        background: #000;
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    /* Progress bar */
    .sv-progress {
        display: flex;
        gap: 3px;
        padding: 10px 10px 0;
        z-index: 10;
    }
    .sv-progress-seg {
        flex: 1;
        height: 3px;
        background: rgba(255,255,255,0.3);
        border-radius: 3px;
        overflow: hidden;
    }
    .sv-progress-fill {
        height: 100%;
        width: 0%;
        background: #fff;
        border-radius: 3px;
    }
    .sv-progress-seg.viewed .sv-progress-fill { width: 100%; }
    .sv-progress-seg.active .sv-progress-fill {
        animation: svFill 5s linear forwards;
    }
    .sv-progress-seg.paused .sv-progress-fill {
        animation-play-state: paused !important;
    }
    @keyframes svFill { from { width: 0%; } to { width: 100%; } }

    /* Header */
    .sv-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px 6px;
        z-index: 10;
    }
    .sv-close {
        background: rgba(0,0,0,0.3);
        border: none;
        cursor: pointer;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .sv-user {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .sv-username {
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        text-shadow: 0 1px 3px rgba(0,0,0,0.5);
    }
    .sv-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
    }

    /* Image */
    .sv-image {
        flex: 1;
        width: 100%;
        object-fit: cover;
    }

    /* Description overlay */
    .sv-description {
        position: absolute;
        bottom: 70px;
        left: 0; right: 0;
        padding: 16px 16px 20px;
        background: linear-gradient(transparent, rgba(0,0,0,0.7));
        color: #fff;
        font-size: 14px;
        line-height: 1.7;
        text-align: right;
        z-index: 10;
        text-shadow: 0 1px 3px rgba(0,0,0,0.5);
    }

    /* Link button */
    .sv-link-btn {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(255,255,255,0.95);
        color: #1f2937;
        text-decoration: none;
        padding: 10px 24px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
        z-index: 10;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    /* Tap zones */
    .sv-tap {
        position: absolute;
        top: 70px;
        bottom: 70px;
        width: 40%;
        z-index: 8;
        cursor: pointer;
    }
    .sv-tap-prev { right: 0; }
    .sv-tap-next { left: 0; }
    </style>

    <script>
    (function() {
        var storiesData = <?php echo wp_json_encode($stories_json); ?>;
        var currentIndex = 0;
        var progressTimer = null;
        var STORY_DURATION = 5000;
        var STORAGE_KEY = 'ganjeh_viewed_stories';
        var isPaused = false;
        var pausedTimeLeft = 0;
        var storyStartTime = 0;

        function getViewed() {
            try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; }
            catch(e) { return []; }
        }

        function markViewed(i) {
            var v = getViewed();
            var id = storiesData[i] ? storiesData[i].image : '';
            if (id && v.indexOf(id) === -1) { v.push(id); localStorage.setItem(STORAGE_KEY, JSON.stringify(v)); }
            var ring = document.getElementById('story-ring-' + i);
            if (ring) ring.classList.add('viewed');
        }

        function applyViewed() {
            var v = getViewed();
            storiesData.forEach(function(s, i) {
                if (v.indexOf(s.image) !== -1) {
                    var ring = document.getElementById('story-ring-' + i);
                    if (ring) ring.classList.add('viewed');
                }
            });
        }

        window.ganjehOpenStory = function(i) {
            currentIndex = i;
            isPaused = false;
            document.getElementById('story-viewer').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            showStory(currentIndex);
        };

        window.ganjehCloseStory = function() {
            document.getElementById('story-viewer').style.display = 'none';
            document.body.style.overflow = '';
            isPaused = false;
            clearTimeout(progressTimer);
        };

        window.ganjehNextStory = function() {
            if (currentIndex < storiesData.length - 1) { currentIndex++; showStory(currentIndex); }
            else { ganjehCloseStory(); }
        };

        window.ganjehPrevStory = function() {
            if (currentIndex > 0) { currentIndex--; showStory(currentIndex); }
        };

        // Long press = pause
        var pressTimer = null;
        var card = null;
        function initLongPress() {
            card = document.querySelector('.story-viewer-card');
            if (!card) return;
            card.addEventListener('touchstart', function(e) {
                if (e.target.closest('.sv-close') || e.target.closest('.sv-link-btn')) return;
                pressTimer = setTimeout(function() {
                    isPaused = true;
                    var active = document.querySelector('.sv-progress-seg.active');
                    if (active) active.classList.add('paused');
                    var elapsed = Date.now() - storyStartTime;
                    pausedTimeLeft = Math.max(STORY_DURATION - elapsed, 500);
                    clearTimeout(progressTimer);
                }, 200);
            });
            card.addEventListener('touchend', function() {
                clearTimeout(pressTimer);
                if (isPaused) {
                    isPaused = false;
                    var active = document.querySelector('.sv-progress-seg.active');
                    if (active) active.classList.remove('paused');
                    progressTimer = setTimeout(function() { ganjehNextStory(); }, pausedTimeLeft);
                }
            });
        }

        function showStory(index) {
            var s = storiesData[index];
            if (!s) return;

            isPaused = false;
            storyStartTime = Date.now();
            markViewed(index);

            document.getElementById('sv-img').src = s.image;
            document.getElementById('sv-thumb').src = s.image;
            document.getElementById('sv-name').textContent = s.title;

            // Description
            var desc = document.getElementById('sv-desc');
            if (s.description) {
                desc.textContent = s.description;
                desc.style.display = 'block';
            } else {
                desc.style.display = 'none';
            }

            // Link
            var link = document.getElementById('sv-link');
            if (s.link && s.link !== '' && s.link !== '#') {
                link.href = s.link;
                link.style.display = 'flex';
            } else {
                link.style.display = 'none';
            }

            // Progress
            document.querySelectorAll('.sv-progress-seg').forEach(function(seg, i) {
                seg.classList.remove('active', 'viewed', 'paused');
                var fill = seg.querySelector('.sv-progress-fill');
                fill.style.animation = 'none';
                fill.style.width = '0%';

                if (i < index) {
                    seg.classList.add('viewed');
                    fill.style.width = '100%';
                } else if (i === index) {
                    seg.classList.add('active');
                    void fill.offsetWidth;
                    fill.style.animation = 'svFill ' + (STORY_DURATION / 1000) + 's linear forwards';
                }
            });

            clearTimeout(progressTimer);
            progressTimer = setTimeout(function() { ganjehNextStory(); }, STORY_DURATION);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var v = document.getElementById('story-viewer');
                if (v && v.style.display !== 'none') ganjehCloseStory();
            }
        });

        applyViewed();
        // Init long press after DOM ready
        setTimeout(initLongPress, 100);
    })();
    </script>
    <?php
}
