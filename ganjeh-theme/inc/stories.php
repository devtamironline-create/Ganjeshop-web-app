<?php
/**
 * Story Highlights Feature - Instagram-style
 *
 * Each highlight (tab) contains multiple stories.
 * All highlights are shown as circles on the homepage.
 * Clicking a highlight opens its stories in a viewer.
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
 * Get story tabs/highlights (with backward compatibility)
 */
function ganjeh_get_story_tabs() {
    $tabs = get_option('ganjeh_story_tabs', null);

    // Backward compatibility: migrate old flat stories into a single tab
    if ($tabs === null) {
        $old_stories = get_option('ganjeh_stories', []);
        if (!empty($old_stories) && is_array($old_stories)) {
            $tabs = [
                ['name' => 'هایلایت ۱', 'cover' => '', 'stories' => $old_stories]
            ];
            update_option('ganjeh_story_tabs', $tabs);
        } else {
            $tabs = [];
        }
    }

    if (!is_array($tabs)) return [];

    // Sort stories within each tab and ensure cover field exists
    foreach ($tabs as &$tab) {
        if (!isset($tab['cover'])) $tab['cover'] = '';
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
 * Get all active highlights with their stories for frontend
 */
function ganjeh_get_highlights() {
    $tabs = ganjeh_get_story_tabs();
    if (empty($tabs)) return [];

    $highlights = [];
    foreach ($tabs as $tab) {
        $active_stories = array_filter($tab['stories'], function($s) {
            return !empty($s['active']) && (!empty($s['image']) || !empty($s['products']));
        });
        if (!empty($active_stories)) {
            // Determine cover: use explicit cover, or first story image
            $cover = $tab['cover'] ?? '';
            if (empty($cover)) {
                $first = reset($active_stories);
                $cover = $first['image'] ?? '';
            }
            $highlights[] = [
                'name'    => $tab['name'] ?? '',
                'cover'   => $cover,
                'stories' => array_values($active_stories),
            ];
        }
    }

    return $highlights;
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
        if (is_array($stories)) {
            foreach ($stories as $story) {
                // Sanitize products array
                $products = [];
                if (!empty($story['products']) && is_array($story['products'])) {
                    foreach ($story['products'] as $prod) {
                        $pid = absint($prod['id'] ?? 0);
                        if ($pid) {
                            $products[] = [
                                'id'   => $pid,
                                'name' => sanitize_text_field($prod['name'] ?? ''),
                            ];
                        }
                    }
                }
                $sanitized_stories[] = [
                    'title'       => sanitize_text_field($story['title'] ?? ''),
                    'image'       => esc_url_raw($story['image'] ?? ''),
                    'link'        => esc_url_raw($story['link'] ?? ''),
                    'description' => sanitize_textarea_field($story['description'] ?? ''),
                    'products'    => $products,
                    'order'       => absint($story['order'] ?? 0),
                    'active'      => !empty($story['active']),
                ];
            }
        }
        $sanitized_tabs[] = [
            'name'    => sanitize_text_field($tab['name'] ?? ''),
            'cover'   => esc_url_raw($tab['cover'] ?? ''),
            'stories' => $sanitized_stories,
        ];
    }

    update_option('ganjeh_story_tabs', $sanitized_tabs);
    wp_send_json_success('ذخیره شد');
}
add_action('wp_ajax_ganjeh_save_stories', 'ganjeh_save_stories_ajax');

/**
 * AJAX product search for stories admin
 */
function ganjeh_search_products_ajax() {
    check_ajax_referer('ganjeh_stories_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json([]);
    }

    $term = sanitize_text_field($_GET['term'] ?? '');
    if (mb_strlen($term) < 2) {
        wp_send_json([]);
    }

    $query = new WP_Query([
        'post_type'      => 'product',
        'post_status'    => 'publish',
        's'              => $term,
        'posts_per_page' => 10,
    ]);

    $results = [];
    foreach ($query->posts as $post) {
        $product = wc_get_product($post->ID);
        if ($product) {
            $in_stock = $product->is_in_stock();
            $results[] = [
                'id'       => $post->ID,
                'name'     => $product->get_name(),
                'in_stock' => $in_stock,
                'price'    => $in_stock ? strip_tags(wc_price($product->get_price())) : __('ناموجود', 'ganjeh'),
            ];
        }
    }
    wp_send_json($results);
}
add_action('wp_ajax_ganjeh_search_products', 'ganjeh_search_products_ajax');

/**
 * Admin page for managing stories (highlights)
 */
function ganjeh_stories_admin_page() {
    $tabs = ganjeh_get_story_tabs();

    // Backward compat: convert old product_id to products array
    foreach ($tabs as &$_tab) {
        foreach ($_tab['stories'] as &$_story) {
            if (!isset($_story['products'])) {
                $_story['products'] = [];
                if (!empty($_story['product_id'])) {
                    $p = function_exists('wc_get_product') ? wc_get_product(absint($_story['product_id'])) : null;
                    if ($p) {
                        $_story['products'][] = ['id' => absint($_story['product_id']), 'name' => $p->get_name()];
                    }
                }
            }
            // Resolve missing names
            foreach ($_story['products'] as &$_prod) {
                if (empty($_prod['name']) && !empty($_prod['id'])) {
                    $p = function_exists('wc_get_product') ? wc_get_product(absint($_prod['id'])) : null;
                    $_prod['name'] = $p ? $p->get_name() : '#' . $_prod['id'];
                }
            }
        }
    }
    unset($_tab, $_story, $_prod);
    ?>
    <div class="wrap">
        <h1><?php _e('مدیریت هایلایت‌ها', 'ganjeh'); ?></h1>
        <p><?php _e('هایلایت‌ها مانند اینستاگرام هستند. هر هایلایت شامل چندین استوری است و همه هایلایت‌ها همزمان در صفحه اصلی نمایش داده می‌شوند.', 'ganjeh'); ?></p>

        <div id="ganjeh-stories-app" style="margin-top:20px;">
            <div id="ganjeh-tab-nav" style="display:flex;gap:0;border-bottom:2px solid #ddd;margin-bottom:0;"></div>
            <div id="ganjeh-tab-content" style="border:1px solid #ddd;border-top:none;padding:20px;background:#fff;"></div>

            <p style="margin-top:15px;display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" class="button" onclick="ganjehAddTab()">+ افزودن هایلایت جدید</button>
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
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .ganjeh-tab-btn:hover { background: #e5e5e5; }
    .ganjeh-tab-btn.active {
        background: #fff;
        border-bottom-color: #fff;
        color: #1d2327;
    }
    .ganjeh-tab-cover-mini {
        width: 24px; height: 24px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;
    }
    .ganjeh-tab-cover-preview {
        width: 70px; height: 70px; border-radius: 50%; object-fit: cover;
        border: 3px solid #ddd; cursor: pointer;
    }
    .ganjeh-tab-cover-placeholder {
        width: 70px; height: 70px; border-radius: 50%; border: 3px dashed #ccc;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        background: #f9f9f9; color: #999; font-size: 11px; text-align: center;
    }
    .ganjeh-story-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .ganjeh-story-table th { text-align: right; padding: 8px; border-bottom: 2px solid #ddd; font-size: 13px; }
    .ganjeh-story-table td { padding: 6px 8px; border-bottom: 1px solid #eee; vertical-align: middle; }
    .ganjeh-story-table tr:hover td { background: #f9f9f9; }
    .sp-wrap { position: relative; min-width: 180px; }
    .sp-chips { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 4px; }
    .sp-chip {
        display: inline-flex; align-items: center; gap: 4px;
        background: #e7f3ff; color: #1d2327; padding: 3px 8px;
        border-radius: 4px; font-size: 12px; max-width: 170px;
    }
    .sp-chip-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sp-chip-rm {
        cursor: pointer; color: #b32d2e; font-weight: bold;
        font-size: 14px; line-height: 1; margin-right: 2px;
    }
    .sp-chip-rm:hover { color: #a00; }
    .sp-search { width: 100%; padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; }
    .sp-results {
        position: absolute; top: 100%; right: 0; left: 0;
        background: #fff; border: 1px solid #ddd; border-radius: 4px;
        max-height: 200px; overflow-y: auto; z-index: 1000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1); display: none;
    }
    .sp-result-item {
        padding: 8px 10px; cursor: pointer; font-size: 12px;
        border-bottom: 1px solid #f0f0f1;
        display: flex; justify-content: space-between; align-items: center; gap: 8px;
    }
    .sp-result-item:hover { background: #f0f6fc; }
    .sp-result-item:last-child { border-bottom: none; }
    .sp-result-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sp-result-price { flex-shrink: 0; font-size: 11px; color: #22863a; font-weight: 600; }
    .sp-result-price.sp-unavailable { color: #cf222e; }
    .sp-out-of-stock { opacity: 0.7; }
    .sp-loading { padding: 8px 10px; color: #999; font-size: 12px; text-align: center; }
    </style>

    <script>
    var ganjehTabs = <?php echo wp_json_encode($tabs); ?> || [];
    var activeTabIndex = 0;

    if (ganjehTabs.length === 0) {
        ganjehTabs.push({name: 'هایلایت ۱', cover: '', stories: []});
    }
    // Ensure cover field exists
    ganjehTabs.forEach(function(t) { if (!t.cover) t.cover = ''; });

    function ganjehRenderAll() {
        renderTabNav();
        renderTabContent();
    }

    function renderTabNav() {
        var nav = document.getElementById('ganjeh-tab-nav');
        var html = '';
        ganjehTabs.forEach(function(tab, i) {
            var cls = i === activeTabIndex ? ' active' : '';
            var coverHtml = tab.cover
                ? '<img src="' + tab.cover + '" class="ganjeh-tab-cover-mini">'
                : '';
            html += '<button type="button" class="ganjeh-tab-btn' + cls + '" onclick="ganjehSwitchTab(' + i + ')">'
                + coverHtml
                + (tab.name || 'هایلایت ' + (i+1))
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
        html += '<div style="display:flex;align-items:center;gap:16px;margin-bottom:15px;">';

        // Cover image
        html += '<div style="text-align:center;">';
        if (tab.cover) {
            html += '<img src="' + tab.cover + '" class="ganjeh-tab-cover-preview" onclick="ganjehSelectCover(' + activeTabIndex + ')" title="تغییر تصویر کاور">';
        } else {
            html += '<div class="ganjeh-tab-cover-placeholder" onclick="ganjehSelectCover(' + activeTabIndex + ')">تصویر<br>کاور</div>';
        }
        html += '<div style="margin-top:4px;"><button type="button" class="button button-small" onclick="ganjehSelectCover(' + activeTabIndex + ')">انتخاب کاور</button></div>';
        html += '</div>';

        // Name + delete
        html += '<div>';
        html += '<div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">';
        html += '<label style="font-weight:600;">نام هایلایت:</label>';
        html += '<input type="text" value="' + escAttr(tab.name) + '" onchange="ganjehTabs[' + activeTabIndex + '].name=this.value;renderTabNav();" style="width:200px;">';
        if (ganjehTabs.length > 1) {
            html += '<button type="button" class="button" style="color:#b32d2e;" onclick="ganjehRemoveTab(' + activeTabIndex + ')">حذف هایلایت</button>';
        }
        html += '</div>';
        html += '<p style="color:#666;font-size:12px;margin:0;">تصویر کاور به عنوان آیکون هایلایت در صفحه اصلی نمایش داده می‌شود. اگر انتخاب نکنید، تصویر اولین استوری استفاده می‌شود.</p>';
        html += '</div>';
        html += '</div>';

        var stories = tab.stories || [];
        if (stories.length === 0) {
            html += '<p style="color:#999;font-style:italic;">هنوز استوری‌ای در این هایلایت اضافه نشده.</p>';
        } else {
            html += '<table class="ganjeh-story-table"><thead><tr>'
                + '<th style="width:55px;">تصویر</th>'
                + '<th style="width:120px;">عنوان</th>'
                + '<th>توضیحات</th>'
                + '<th style="min-width:200px;">محصولات</th>'
                + '<th style="width:140px;">لینک</th>'
                + '<th style="width:50px;">ترتیب</th>'
                + '<th style="width:40px;">فعال</th>'
                + '<th style="width:100px;">عملیات</th>'
                + '</tr></thead><tbody>';

            stories.forEach(function(s, i) {
                if (!s.products) s.products = [];
                var imgPreview = s.image
                    ? '<img src="' + s.image + '" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">'
                    : '<span style="color:#ccc;">—</span>';

                // Build product chips
                var chipsHtml = '';
                s.products.forEach(function(prod, pi) {
                    chipsHtml += '<span class="sp-chip">'
                        + '<span class="sp-chip-name">' + escAttr(prod.name) + '</span>'
                        + '<span class="sp-chip-rm" onclick="ganjehRemoveProduct(' + activeTabIndex + ',' + i + ',' + pi + ')">&times;</span>'
                        + '</span>';
                });

                html += '<tr>'
                    + '<td>' + imgPreview + '</td>'
                    + '<td><input type="text" value="' + escAttr(s.title) + '" onchange="ganjehTabs[' + activeTabIndex + '].stories[' + i + '].title=this.value" style="width:100%;"></td>'
                    + '<td><input type="text" value="' + escAttr(s.description) + '" onchange="ganjehTabs[' + activeTabIndex + '].stories[' + i + '].description=this.value" style="width:100%;" placeholder="توضیحات روی استوری..."></td>'
                    + '<td><div class="sp-wrap">'
                    + '<div class="sp-chips">' + chipsHtml + '</div>'
                    + '<input type="text" class="sp-search" placeholder="جستجوی محصول..." oninput="ganjehProductSearch(this,' + activeTabIndex + ',' + i + ')" onfocus="ganjehProductSearch(this,' + activeTabIndex + ',' + i + ')">'
                    + '<div class="sp-results" id="sp-results-' + activeTabIndex + '-' + i + '"></div>'
                    + '</div></td>'
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
        ganjehTabs.push({name: 'هایلایت ' + (ganjehTabs.length + 1), cover: '', stories: []});
        activeTabIndex = ganjehTabs.length - 1;
        ganjehRenderAll();
    }

    function ganjehRemoveTab(i) {
        if (!confirm('آیا از حذف این هایلایت مطمئنید؟')) return;
        ganjehTabs.splice(i, 1);
        if (activeTabIndex >= ganjehTabs.length) activeTabIndex = ganjehTabs.length - 1;
        if (activeTabIndex < 0) activeTabIndex = 0;
        ganjehRenderAll();
    }

    function ganjehAddStory(tabIndex) {
        if (!ganjehTabs[tabIndex].stories) ganjehTabs[tabIndex].stories = [];
        ganjehTabs[tabIndex].stories.push({title: '', image: '', link: '', description: '', products: [], order: ganjehTabs[tabIndex].stories.length, active: true});
        renderTabContent();
    }

    function ganjehRemoveStory(tabIndex, storyIndex) {
        ganjehTabs[tabIndex].stories.splice(storyIndex, 1);
        renderTabContent();
    }

    function ganjehSelectCover(tabIndex) {
        var frame = wp.media({ title: 'انتخاب تصویر کاور هایلایت', button: {text: 'انتخاب'}, multiple: false });
        frame.on('select', function() {
            ganjehTabs[tabIndex].cover = frame.state().get('selection').first().toJSON().url;
            ganjehRenderAll();
        });
        frame.open();
    }

    function ganjehSelectImage(tabIndex, storyIndex) {
        var frame = wp.media({ title: 'انتخاب تصویر استوری', button: {text: 'انتخاب'}, multiple: false });
        frame.on('select', function() {
            ganjehTabs[tabIndex].stories[storyIndex].image = frame.state().get('selection').first().toJSON().url;
            renderTabContent();
        });
        frame.open();
    }

    var spSearchTimer = null;
    var spNonce = '<?php echo wp_create_nonce('ganjeh_stories_nonce'); ?>';

    function ganjehProductSearch(input, tabIdx, storyIdx) {
        var term = input.value.trim();
        var resultsEl = document.getElementById('sp-results-' + tabIdx + '-' + storyIdx);
        if (term.length < 2) { resultsEl.style.display = 'none'; return; }
        clearTimeout(spSearchTimer);
        resultsEl.innerHTML = '<div class="sp-loading">درحال جستجو...</div>';
        resultsEl.style.display = 'block';
        spSearchTimer = setTimeout(function() {
            fetch(ajaxurl + '?action=ganjeh_search_products&nonce=' + spNonce + '&term=' + encodeURIComponent(term))
                .then(function(r) { return r.json(); })
                .then(function(items) {
                    if (!items || items.length === 0) {
                        resultsEl.innerHTML = '<div class="sp-loading">محصولی یافت نشد</div>';
                        return;
                    }
                    var existing = (ganjehTabs[tabIdx].stories[storyIdx].products || []).map(function(p) { return p.id; });
                    var html = '';
                    items.forEach(function(item) {
                        if (existing.indexOf(item.id) !== -1) return;
                        html += '<div class="sp-result-item' + (!item.in_stock ? ' sp-out-of-stock' : '') + '" onclick="ganjehAddProduct(' + tabIdx + ',' + storyIdx + ',' + item.id + ',\'' + escAttr(item.name).replace(/'/g, "\\'") + '\')">'
                            + '<span class="sp-result-name"><strong>#' + item.id + '</strong> ' + escAttr(item.name) + '</span>'
                            + '<span class="sp-result-price' + (!item.in_stock ? ' sp-unavailable' : '') + '">' + escAttr(item.price) + '</span>'
                            + '</div>';
                    });
                    resultsEl.innerHTML = html || '<div class="sp-loading">همه محصولات اضافه شده‌اند</div>';
                });
        }, 300);
    }

    function ganjehAddProduct(tabIdx, storyIdx, productId, productName) {
        if (!ganjehTabs[tabIdx].stories[storyIdx].products) {
            ganjehTabs[tabIdx].stories[storyIdx].products = [];
        }
        ganjehTabs[tabIdx].stories[storyIdx].products.push({id: productId, name: productName});
        renderTabContent();
    }

    function ganjehRemoveProduct(tabIdx, storyIdx, prodIdx) {
        ganjehTabs[tabIdx].stories[storyIdx].products.splice(prodIdx, 1);
        renderTabContent();
    }

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.sp-wrap')) {
            document.querySelectorAll('.sp-results').forEach(function(el) { el.style.display = 'none'; });
        }
    });

    function ganjehSaveTabs() {
        var data = new FormData();
        data.append('action', 'ganjeh_save_stories');
        data.append('nonce', '<?php echo wp_create_nonce('ganjeh_stories_nonce'); ?>');

        ganjehTabs.forEach(function(tab, t) {
            data.append('tabs[' + t + '][name]', tab.name || '');
            data.append('tabs[' + t + '][cover]', tab.cover || '');
            var stories = tab.stories || [];
            stories.forEach(function(s, i) {
                var p = 'tabs[' + t + '][stories][' + i + ']';
                data.append(p + '[title]', s.title || '');
                data.append(p + '[image]', s.image || '');
                data.append(p + '[link]', s.link || '');
                data.append(p + '[description]', s.description || '');
                var prods = s.products || [];
                prods.forEach(function(prod, pi) {
                    data.append(p + '[products][' + pi + '][id]', prod.id || 0);
                    data.append(p + '[products][' + pi + '][name]', prod.name || '');
                });
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
 * Render highlights on frontend (Instagram-style)
 */
function ganjeh_render_stories() {
    $highlights = ganjeh_get_highlights();
    if (empty($highlights)) return;

    // Build full data for each highlight
    $highlights_json = [];
    foreach ($highlights as $h_index => $highlight) {
        $stories_data = [];
        foreach ($highlight['stories'] as &$_s) {
            // Fallback: use first product image when story has no image
            if (empty($_s['image']) && !empty($_s['products']) && function_exists('wc_get_product')) {
                $fp = wc_get_product(absint($_s['products'][0]['id']));
                if ($fp) {
                    $fimg = $fp->get_image_id();
                    if ($fimg) {
                        $_s['image'] = wp_get_attachment_image_url($fimg, 'large');
                    }
                }
            }

            $story_data = [
                'image'       => esc_url($_s['image'] ?? ''),
                'title'       => esc_html($_s['title'] ?? ''),
                'link'        => esc_url($_s['link'] ?? ''),
                'description' => esc_html($_s['description'] ?? ''),
                'products'    => [],
            ];

            // Load WooCommerce product data
            $product_ids = [];
            if (!empty($_s['products']) && is_array($_s['products'])) {
                foreach ($_s['products'] as $prod) {
                    $product_ids[] = absint($prod['id'] ?? 0);
                }
            }

            foreach ($product_ids as $pid) {
                if (!$pid || !function_exists('wc_get_product')) continue;
                $product = wc_get_product($pid);
                if ($product && $product->get_status() === 'publish') {
                    $thumb_id = $product->get_image_id();
                    $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'thumbnail') : '';
                    $story_data['products'][] = [
                        'id'    => $pid,
                        'name'  => $product->get_name(),
                        'price' => strip_tags(wc_price($product->get_price())),
                        'image' => $thumb_url,
                        'url'   => get_permalink($pid),
                    ];
                }
            }

            $stories_data[] = $story_data;
        }
        unset($_s);

        // Update cover fallback
        $cover = $highlight['cover'];
        if (empty($cover) && !empty($stories_data[0]['image'])) {
            $cover = $stories_data[0]['image'];
        }

        $highlights_json[] = [
            'name'    => esc_html($highlight['name']),
            'cover'   => esc_url($cover),
            'stories' => $stories_data,
        ];
    }
    ?>
    <section class="stories-section">
        <div class="stories-scroll">
            <?php foreach ($highlights_json as $h_index => $hl) : ?>
                <div class="story-item" onclick="ganjehOpenHighlight(<?php echo $h_index; ?>)">
                    <div class="story-ring" id="highlight-ring-<?php echo $h_index; ?>">
                        <img src="<?php echo esc_url($hl['cover']); ?>" alt="<?php echo esc_attr($hl['name']); ?>" class="story-img" loading="lazy">
                    </div>
                    <?php if ($hl['name']) : ?>
                        <span class="story-title"><?php echo esc_html($hl['name']); ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Story Viewer Modal -->
    <div id="story-viewer" class="story-viewer" style="display:none;">
        <div class="story-viewer-bg" onclick="ganjehCloseStory()"></div>
        <div class="story-viewer-card">
            <!-- Progress bars (dynamic) -->
            <div class="sv-progress" id="sv-progress"></div>

            <!-- Header: close + pause + highlight info -->
            <div class="sv-header">
                <div class="sv-header-left">
                    <button type="button" class="sv-close" onclick="ganjehCloseStory()">
                        <svg width="22" height="22" fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <button type="button" class="sv-pause-btn" onclick="ganjehTogglePause()">
                        <svg id="sv-icon-pause" width="18" height="18" fill="#fff" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/></svg>
                        <svg id="sv-icon-play" width="18" height="18" fill="#fff" viewBox="0 0 24 24" style="display:none;"><path d="M8 5v14l11-7z"/></svg>
                    </button>
                </div>
                <div class="sv-user">
                    <span id="sv-name" class="sv-username"></span>
                    <img id="sv-thumb" src="" alt="" class="sv-avatar">
                </div>
            </div>

            <!-- Story Image -->
            <img id="sv-img" src="" alt="" class="sv-image">

            <!-- Description overlay -->
            <div id="sv-desc" class="sv-description" style="display:none;"></div>

            <!-- Products overlay -->
            <div id="sv-products" class="sv-products-wrap" style="display:none;"></div>

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
    /* Highlight Circles - Instagram style */
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

    /* Story Viewer */
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
    .sv-header-left {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .sv-close, .sv-pause-btn {
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
        right: 12px;
        left: 12px;
        text-align: right;
        z-index: 10;
    }
    .sv-description span {
        background: rgba(0,0,0,0.55);
        color: #fff;
        font-size: 13px;
        line-height: 2;
        padding: 4px 12px;
        border-radius: 8px;
        -webkit-box-decoration-break: clone;
        box-decoration-break: clone;
    }

    /* Products overlay */
    .sv-products-wrap {
        position: absolute;
        bottom: 16px;
        right: 0;
        left: 0;
        z-index: 10;
        padding: 0 12px;
        direction: rtl;
        display: flex;
        gap: 8px;
        overflow-x: auto;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }
    .sv-products-wrap::-webkit-scrollbar { display: none; }
    .sv-product-card {
        flex-shrink: 0;
        background: rgba(255,255,255,0.95);
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        text-decoration: none;
        color: #1f2937;
        box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        min-width: 220px;
        max-width: 300px;
    }
    .sv-product-thumb {
        width: 52px;
        height: 52px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
    }
    .sv-product-info {
        flex: 1;
        min-width: 0;
        text-align: right;
    }
    .sv-product-name {
        font-size: 13px;
        font-weight: 600;
        color: #1f2937;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .sv-product-price {
        font-size: 13px;
        font-weight: 700;
        color: #059669;
        margin-top: 3px;
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
        var highlightsData = <?php echo wp_json_encode($highlights_json); ?>;
        var currentHighlight = 0;
        var currentStory = 0;
        var progressTimer = null;
        var STORY_DURATION = 5000;
        var STORAGE_KEY = 'ganjeh_viewed_highlights';
        var isPaused = false;
        var pausedTimeLeft = 0;
        var storyStartTime = 0;

        function getViewed() {
            try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {}; }
            catch(e) { return {}; }
        }

        function markHighlightViewed(hIdx) {
            var v = getViewed();
            var hl = highlightsData[hIdx];
            if (!hl) return;
            var key = hl.name + '_' + hl.stories.length;
            if (!v[key]) v[key] = [];
            // Mark all stories in this highlight as viewed
            hl.stories.forEach(function(s) {
                if (s.image && v[key].indexOf(s.image) === -1) v[key].push(s.image);
            });
            localStorage.setItem(STORAGE_KEY, JSON.stringify(v));
        }

        function markStoryViewed(hIdx, sIdx) {
            var v = getViewed();
            var hl = highlightsData[hIdx];
            if (!hl) return;
            var key = hl.name + '_' + hl.stories.length;
            if (!v[key]) v[key] = [];
            var img = hl.stories[sIdx] ? hl.stories[sIdx].image : '';
            if (img && v[key].indexOf(img) === -1) {
                v[key].push(img);
                localStorage.setItem(STORAGE_KEY, JSON.stringify(v));
            }
        }

        function isHighlightFullyViewed(hIdx) {
            var v = getViewed();
            var hl = highlightsData[hIdx];
            if (!hl || !hl.stories.length) return false;
            var key = hl.name + '_' + hl.stories.length;
            if (!v[key]) return false;
            return hl.stories.every(function(s) {
                return !s.image || v[key].indexOf(s.image) !== -1;
            });
        }

        function applyViewed() {
            highlightsData.forEach(function(hl, i) {
                if (isHighlightFullyViewed(i)) {
                    var ring = document.getElementById('highlight-ring-' + i);
                    if (ring) ring.classList.add('viewed');
                }
            });
        }

        function buildProgressBar(storyCount) {
            var container = document.getElementById('sv-progress');
            var html = '';
            for (var i = 0; i < storyCount; i++) {
                html += '<div class="sv-progress-seg" data-index="' + i + '"><div class="sv-progress-fill"></div></div>';
            }
            container.innerHTML = html;
        }

        window.ganjehOpenHighlight = function(hIdx) {
            currentHighlight = hIdx;
            currentStory = 0;
            isPaused = false;

            var hl = highlightsData[hIdx];
            if (!hl || !hl.stories.length) return;

            buildProgressBar(hl.stories.length);
            document.getElementById('story-viewer').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            showStory();
        };

        window.ganjehCloseStory = function() {
            document.getElementById('story-viewer').style.display = 'none';
            document.body.style.overflow = '';
            isPaused = false;
            clearTimeout(progressTimer);

            // Check if all stories viewed, update ring
            if (isHighlightFullyViewed(currentHighlight)) {
                var ring = document.getElementById('highlight-ring-' + currentHighlight);
                if (ring) ring.classList.add('viewed');
            }
        };

        window.ganjehTogglePause = function() {
            if (isPaused) {
                isPaused = false;
                updatePauseIcon();
                var active = document.querySelector('.sv-progress-seg.active');
                if (active) active.classList.remove('paused');
                progressTimer = setTimeout(function() { ganjehNextStory(); }, pausedTimeLeft);
            } else {
                isPaused = true;
                updatePauseIcon();
                var active = document.querySelector('.sv-progress-seg.active');
                if (active) active.classList.add('paused');
                var elapsed = Date.now() - storyStartTime;
                pausedTimeLeft = Math.max(STORY_DURATION - elapsed, 500);
                clearTimeout(progressTimer);
            }
        };

        function updatePauseIcon() {
            var pi = document.getElementById('sv-icon-pause');
            var pl = document.getElementById('sv-icon-play');
            if (isPaused) { pi.style.display = 'none'; pl.style.display = 'block'; }
            else { pi.style.display = 'block'; pl.style.display = 'none'; }
        }

        window.ganjehNextStory = function() {
            var hl = highlightsData[currentHighlight];
            if (!hl) return;
            if (currentStory < hl.stories.length - 1) {
                currentStory++;
                showStory();
            } else {
                // Move to next highlight or close
                if (currentHighlight < highlightsData.length - 1) {
                    currentHighlight++;
                    currentStory = 0;
                    var nextHl = highlightsData[currentHighlight];
                    if (nextHl && nextHl.stories.length) {
                        buildProgressBar(nextHl.stories.length);
                        showStory();
                        // Update viewed ring of previous highlight
                        if (isHighlightFullyViewed(currentHighlight - 1)) {
                            var ring = document.getElementById('highlight-ring-' + (currentHighlight - 1));
                            if (ring) ring.classList.add('viewed');
                        }
                    } else {
                        ganjehCloseStory();
                    }
                } else {
                    ganjehCloseStory();
                }
            }
        };

        window.ganjehPrevStory = function() {
            if (currentStory > 0) {
                currentStory--;
                showStory();
            } else if (currentHighlight > 0) {
                // Go to previous highlight's last story
                currentHighlight--;
                var prevHl = highlightsData[currentHighlight];
                if (prevHl && prevHl.stories.length) {
                    currentStory = prevHl.stories.length - 1;
                    buildProgressBar(prevHl.stories.length);
                    showStory();
                }
            }
        };

        function showStory() {
            var hl = highlightsData[currentHighlight];
            if (!hl) return;
            var s = hl.stories[currentStory];
            if (!s) return;

            isPaused = false;
            updatePauseIcon();
            storyStartTime = Date.now();
            markStoryViewed(currentHighlight, currentStory);

            // Header: show highlight name + cover
            document.getElementById('sv-name').textContent = hl.name;
            document.getElementById('sv-thumb').src = hl.cover || s.image;

            // Story image
            document.getElementById('sv-img').src = s.image;

            // Products
            var productsWrap = document.getElementById('sv-products');
            var hasProducts = s.products && s.products.length > 0;
            if (hasProducts) {
                var phtml = '';
                s.products.forEach(function(prod) {
                    phtml += '<a href="' + (prod.url || '#') + '" class="sv-product-card" onclick="event.stopPropagation();">'
                        + '<div class="sv-product-info">'
                        + '<div class="sv-product-name">' + prod.name.replace(/&/g,'&amp;').replace(/</g,'&lt;') + '</div>'
                        + '<div class="sv-product-price">' + prod.price + '</div>'
                        + '</div>'
                        + (prod.image ? '<img src="' + prod.image + '" alt="" class="sv-product-thumb">' : '')
                        + '</a>';
                });
                productsWrap.innerHTML = phtml;
                productsWrap.style.display = 'flex';
            } else {
                productsWrap.style.display = 'none';
                productsWrap.innerHTML = '';
            }

            // Description
            var desc = document.getElementById('sv-desc');
            if (s.description) {
                desc.innerHTML = '<span>' + s.description.replace(/&/g,'&amp;').replace(/</g,'&lt;') + '</span>';
                desc.style.display = 'block';
                desc.style.bottom = hasProducts ? '100px' : '70px';
            } else {
                desc.style.display = 'none';
            }

            // Link (hide if products)
            var link = document.getElementById('sv-link');
            if (!hasProducts && s.link && s.link !== '' && s.link !== '#') {
                link.href = s.link;
                link.style.display = 'flex';
            } else {
                link.style.display = 'none';
            }

            // Progress bars
            document.querySelectorAll('.sv-progress-seg').forEach(function(seg, i) {
                seg.classList.remove('active', 'viewed', 'paused');
                var fill = seg.querySelector('.sv-progress-fill');
                fill.style.animation = 'none';
                fill.style.width = '0%';

                if (i < currentStory) {
                    seg.classList.add('viewed');
                    fill.style.width = '100%';
                } else if (i === currentStory) {
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
    })();
    </script>
    <?php
}
