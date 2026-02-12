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

    // Filter tabs that have at least one active story
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

    // Daily rotation: day_of_year % number_of_active_tabs
    $day_of_year = (int) date('z'); // 0-365
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
                'title'  => sanitize_text_field($story['title'] ?? ''),
                'image'  => esc_url_raw($story['image'] ?? ''),
                'link'   => esc_url_raw($story['link'] ?? ''),
                'order'  => absint($story['order'] ?? 0),
                'active' => !empty($story['active']),
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
    // Determine today's active tab index for preview info
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
            <!-- Tab Navigation -->
            <div id="ganjeh-tab-nav" style="display:flex;gap:0;border-bottom:2px solid #ddd;margin-bottom:0;"></div>

            <!-- Tab Content -->
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

    // Ensure tabs have proper structure
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

    function renderTabContent() {
        var content = document.getElementById('ganjeh-tab-content');
        var tab = ganjehTabs[activeTabIndex];
        if (!tab) { content.innerHTML = ''; return; }

        var html = '';

        // Tab name + delete
        html += '<div style="display:flex;align-items:center;gap:10px;margin-bottom:15px;">';
        html += '<label style="font-weight:600;">نام تب:</label>';
        html += '<input type="text" value="' + (tab.name || '') + '" onchange="ganjehTabs[' + activeTabIndex + '].name=this.value;renderTabNav();" style="width:200px;">';
        if (ganjehTabs.length > 1) {
            html += '<button type="button" class="button" style="color:#b32d2e;" onclick="ganjehRemoveTab(' + activeTabIndex + ')">حذف این تب</button>';
        }
        html += '</div>';

        // Stories table
        var stories = tab.stories || [];
        if (stories.length === 0) {
            html += '<p style="color:#999;font-style:italic;">هنوز استوری‌ای در این تب اضافه نشده.</p>';
        } else {
            html += '<table class="ganjeh-story-table"><thead><tr>'
                + '<th style="width:55px;">تصویر</th>'
                + '<th>عنوان</th>'
                + '<th>لینک</th>'
                + '<th style="width:55px;">ترتیب</th>'
                + '<th style="width:50px;">فعال</th>'
                + '<th style="width:100px;">عملیات</th>'
                + '</tr></thead><tbody>';

            stories.forEach(function(s, i) {
                var imgPreview = s.image
                    ? '<img src="' + s.image + '" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">'
                    : '<span style="color:#ccc;">—</span>';
                html += '<tr>'
                    + '<td>' + imgPreview + '</td>'
                    + '<td><input type="text" value="' + (s.title || '') + '" onchange="ganjehTabs[' + activeTabIndex + '].stories[' + i + '].title=this.value" style="width:100%;"></td>'
                    + '<td><input type="url" value="' + (s.link || '') + '" onchange="ganjehTabs[' + activeTabIndex + '].stories[' + i + '].link=this.value" style="width:100%;" dir="ltr"></td>'
                    + '<td><input type="number" value="' + (s.order || 0) + '" onchange="ganjehTabs[' + activeTabIndex + '].stories[' + i + '].order=parseInt(this.value)||0" style="width:50px;text-align:center;"></td>'
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

    function ganjehSwitchTab(i) {
        activeTabIndex = i;
        ganjehRenderAll();
    }

    function ganjehAddTab() {
        var num = ganjehTabs.length + 1;
        ganjehTabs.push({name: 'تب ' + num, stories: []});
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
        ganjehTabs[tabIndex].stories.push({title: '', image: '', link: '', order: ganjehTabs[tabIndex].stories.length, active: true});
        renderTabContent();
    }

    function ganjehRemoveStory(tabIndex, storyIndex) {
        ganjehTabs[tabIndex].stories.splice(storyIndex, 1);
        renderTabContent();
    }

    function ganjehSelectImage(tabIndex, storyIndex) {
        var frame = wp.media({
            title: 'انتخاب تصویر استوری',
            button: {text: 'انتخاب'},
            multiple: false
        });
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            ganjehTabs[tabIndex].stories[storyIndex].image = attachment.url;
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
                data.append('tabs[' + t + '][stories][' + i + '][title]', s.title || '');
                data.append('tabs[' + t + '][stories][' + i + '][image]', s.image || '');
                data.append('tabs[' + t + '][stories][' + i + '][link]', s.link || '');
                data.append('tabs[' + t + '][stories][' + i + '][order]', s.order || 0);
                data.append('tabs[' + t + '][stories][' + i + '][active]', s.active ? '1' : '');
            });
            if (stories.length === 0) {
                data.append('tabs[' + t + '][stories]', '');
            }
        });

        fetch(ajaxurl, {method: 'POST', body: data})
            .then(function(r) { return r.json(); })
            .then(function(r) {
                var msg = document.getElementById('ganjeh-stories-msg');
                if (r.success) {
                    msg.innerHTML = '<div class="notice notice-success"><p>تب‌ها و استوری‌ها با موفقیت ذخیره شدند.</p></div>';
                } else {
                    msg.innerHTML = '<div class="notice notice-error"><p>خطا در ذخیره.</p></div>';
                }
                setTimeout(function() { msg.innerHTML = ''; }, 3000);
            });
    }

    ganjehRenderAll();
    </script>
    <?php
}

/**
 * Render stories on frontend
 */
function ganjeh_render_stories() {
    $stories = ganjeh_get_today_stories();

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
                <div class="story-viewer-header-actions">
                    <span id="story-viewer-timer" class="story-viewer-timer">5</span>
                    <button type="button" class="story-viewer-pause" id="story-pause-btn" onclick="ganjehTogglePause()">
                        <svg id="story-icon-pause" width="20" height="20" fill="#fff" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/></svg>
                        <svg id="story-icon-play" width="20" height="20" fill="#fff" viewBox="0 0 24 24" style="display:none;"><path d="M8 5v14l11-7z"/></svg>
                    </button>
                    <button type="button" class="story-viewer-close" onclick="ganjehCloseStory()">
                        <svg width="24" height="24" fill="none" stroke="#fff" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Story Image -->
            <img id="story-viewer-img" src="" alt="" class="story-viewer-image">

            <!-- Navigation areas (tap zones) -->
            <div class="story-nav story-nav-prev" onclick="ganjehPrevStory()"></div>
            <div class="story-nav story-nav-next" onclick="ganjehNextStory()"></div>

            <!-- Visible Navigation Arrows -->
            <button type="button" class="story-arrow story-arrow-prev" onclick="ganjehPrevStory()" id="story-arrow-prev">
                <svg width="24" height="24" fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
            <button type="button" class="story-arrow story-arrow-next" onclick="ganjehNextStory()" id="story-arrow-next">
                <svg width="24" height="24" fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

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
    .story-progress-item.paused .story-progress-fill {
        animation-play-state: paused !important;
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
    .story-viewer-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .story-viewer-timer {
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        background: rgba(0,0,0,0.4);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: sans-serif;
    }
    .story-viewer-pause {
        background: rgba(0,0,0,0.4);
        border: none;
        cursor: pointer;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.9;
        transition: background 0.2s;
    }
    .story-viewer-pause:hover {
        background: rgba(0,0,0,0.6);
        opacity: 1;
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

    /* Navigation tap zones */
    .story-nav {
        position: absolute;
        top: 60px;
        bottom: 80px;
        width: 35%;
        z-index: 8;
        cursor: pointer;
    }
    .story-nav-prev {
        right: 0;
    }
    .story-nav-next {
        left: 0;
    }

    /* Visible Arrow Buttons */
    .story-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0,0,0,0.35);
        border: none;
        cursor: pointer;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 12;
        transition: background 0.2s;
    }
    .story-arrow:hover {
        background: rgba(0,0,0,0.6);
    }
    .story-arrow-prev {
        right: 8px;
    }
    .story-arrow-next {
        left: 8px;
    }
    .story-arrow.hidden {
        display: none;
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
        var countdownInterval = null;
        var isPaused = false;
        var pausedTimeLeft = 0;
        var storyStartTime = 0;
        var STORY_DURATION = 5000;
        var STORAGE_KEY = 'ganjeh_viewed_stories';

        function getViewedStories() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
            } catch(e) { return []; }
        }

        function markAsViewed(index) {
            var viewed = getViewedStories();
            var storyId = storiesData[index] ? storiesData[index].image : '';
            if (storyId && viewed.indexOf(storyId) === -1) {
                viewed.push(storyId);
                localStorage.setItem(STORAGE_KEY, JSON.stringify(viewed));
            }
            var ring = document.getElementById('story-ring-' + index);
            if (ring) ring.classList.add('viewed');
        }

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
            isPaused = false;
            updatePauseIcon();
            var viewer = document.getElementById('story-viewer');
            viewer.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            showStory(currentIndex);
        };

        window.ganjehCloseStory = function() {
            var viewer = document.getElementById('story-viewer');
            viewer.style.display = 'none';
            document.body.style.overflow = '';
            isPaused = false;
            clearTimeout(progressTimer);
            clearInterval(countdownInterval);
        };

        window.ganjehTogglePause = function() {
            if (isPaused) {
                isPaused = false;
                updatePauseIcon();
                var activeItem = document.querySelector('.story-progress-item.active');
                if (activeItem) activeItem.classList.remove('paused');
                var timerEl = document.getElementById('story-viewer-timer');
                var secondsLeft = parseInt(timerEl.textContent) || 1;
                countdownInterval = setInterval(function() {
                    secondsLeft--;
                    if (secondsLeft <= 0) {
                        clearInterval(countdownInterval);
                        timerEl.textContent = '0';
                    } else {
                        timerEl.textContent = secondsLeft;
                    }
                }, 1000);
                progressTimer = setTimeout(function() {
                    ganjehNextStory();
                }, pausedTimeLeft);
            } else {
                isPaused = true;
                updatePauseIcon();
                var activeItem = document.querySelector('.story-progress-item.active');
                if (activeItem) activeItem.classList.add('paused');
                var elapsed = Date.now() - storyStartTime;
                pausedTimeLeft = Math.max(STORY_DURATION - elapsed, 500);
                clearTimeout(progressTimer);
                clearInterval(countdownInterval);
            }
        };

        function updatePauseIcon() {
            var pauseIcon = document.getElementById('story-icon-pause');
            var playIcon = document.getElementById('story-icon-play');
            if (isPaused) {
                pauseIcon.style.display = 'none';
                playIcon.style.display = 'block';
            } else {
                pauseIcon.style.display = 'block';
                playIcon.style.display = 'none';
            }
        }

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

            isPaused = false;
            updatePauseIcon();
            storyStartTime = Date.now();

            markAsViewed(index);

            document.getElementById('story-viewer-img').src = story.image;
            document.getElementById('story-viewer-img').alt = story.title;

            document.getElementById('story-viewer-thumb').src = story.image;
            document.getElementById('story-viewer-name').textContent = story.title;

            var linkBtn = document.getElementById('story-viewer-link');
            if (story.link && story.link !== '' && story.link !== '#') {
                linkBtn.href = story.link;
                linkBtn.style.display = 'flex';
            } else {
                linkBtn.style.display = 'none';
            }

            var items = document.querySelectorAll('.story-progress-item');
            items.forEach(function(item, i) {
                item.classList.remove('active', 'viewed', 'paused');
                var fill = item.querySelector('.story-progress-fill');
                fill.style.animation = 'none';
                fill.style.width = '0%';

                if (i < index) {
                    item.classList.add('viewed');
                    fill.style.width = '100%';
                } else if (i === index) {
                    item.classList.add('active');
                    void fill.offsetWidth;
                    fill.style.animation = 'storyProgress ' + (STORY_DURATION / 1000) + 's linear forwards';
                }
            });

            var prevArrow = document.getElementById('story-arrow-prev');
            var nextArrow = document.getElementById('story-arrow-next');
            if (index === 0) {
                prevArrow.classList.add('hidden');
            } else {
                prevArrow.classList.remove('hidden');
            }
            if (index >= storiesData.length - 1) {
                nextArrow.classList.add('hidden');
            } else {
                nextArrow.classList.remove('hidden');
            }

            var timerEl = document.getElementById('story-viewer-timer');
            var secondsLeft = Math.ceil(STORY_DURATION / 1000);
            timerEl.textContent = secondsLeft;
            clearInterval(countdownInterval);
            countdownInterval = setInterval(function() {
                secondsLeft--;
                if (secondsLeft <= 0) {
                    clearInterval(countdownInterval);
                    timerEl.textContent = '0';
                } else {
                    timerEl.textContent = secondsLeft;
                }
            }, 1000);

            clearTimeout(progressTimer);
            progressTimer = setTimeout(function() {
                ganjehNextStory();
            }, STORY_DURATION);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                var viewer = document.getElementById('story-viewer');
                if (viewer.style.display !== 'none') {
                    ganjehCloseStory();
                }
            }
        });

        applyViewedState();
    })();
    </script>
    <?php
}
