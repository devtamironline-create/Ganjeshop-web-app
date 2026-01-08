<?php
/**
 * ماژول مدیریت منوی ادمین
 * Admin Menu Manager Module
 * 
 * ترتیب منوها:
 * 1. پیشخوان
 * 2. ماژول‌ها
 * 3. تنظیمات وب‌سایت
 * ---جداکننده---
 * 4. نوشته‌ها
 * 5. رسانه
 * 6. برگه‌ها
 * 7. فهرست‌ها
 * ---جداکننده---
 * 8. کاربران
 * ---جداکننده---
 * 9. سایر (شامل بقیه منوها)
 * 
 * @package Developer_Starter
 * @subpackage Modules/Admin_Menu_Manager
 * @version 4.0.0
 */

defined('ABSPATH') || exit;

class DST_Admin_Menu_Manager {
    
    /**
     * مسیر ماژول
     */
    private $module_path;
    
    /**
     * URL ماژول
     */
    private $module_url;
    
    /**
     * منوهایی که باید در "سایر" قرار بگیرند
     */
    private $others_menus = [];
    
    /**
     * منوهایی که در سایدبار اصلی نمایش داده شوند
     */
    private $main_sidebar_menus = [];

    /**
     * منوهای پیش‌فرض سایدبار اصلی
     */
    private $default_main_menus = [
        'index.php',
        'edit.php',
        'upload.php',
        'edit.php?post_type=page',
        'users.php',
        'nav-menus.php',
    ];

    /**
     * سازنده
     */
    public function __construct() {
        $module = dst_get_module('admin-menu-manager');
        if (!$module) {
            return;
        }

        $this->module_path = $module['path'];
        $this->module_url  = $module['url'];

        // بارگذاری تنظیمات - اگر ذخیره نشده، از پیش‌فرض استفاده کن
        $saved = get_option('dst_main_sidebar_menus', null);
        $this->main_sidebar_menus = $saved !== null ? $saved : $this->default_main_menus;

        // هوک‌ها
        add_action('admin_menu', [$this, 'reorganize_admin_menu'], 9999);
        add_action('admin_menu', [$this, 'add_settings_page'], 10000); // بعد از reorganize
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_ajax_dst_save_menu_settings', [$this, 'ajax_save_settings']);
    }

    /**
     * ثبت تنظیمات
     */
    public function register_settings() {
        register_setting('dst_menu_settings', 'dst_main_sidebar_menus');
    }

    /**
     * اضافه کردن صفحه تنظیمات منو
     */
    public function add_settings_page() {
        add_submenu_page(
            'dst-website-settings',
            'تنظیمات منو',
            'تنظیمات منو',
            'manage_options',
            'dst-menu-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * لود فایل‌های CSS و JS
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'dst-menu-style',
            $this->module_url . '/assets/css/menu-style.css',
            [],
            '4.0.0'
        );
        
        wp_enqueue_script(
            'dst-menu-click',
            $this->module_url . '/assets/js/menu-click.js',
            ['jquery'],
            '4.0.0',
            true
        );
    }

    /**
     * بازسازی کامل منوی ادمین
     */
    public function reorganize_admin_menu() {
        global $menu, $submenu;

        // ذخیره منوهای فعلی
        $original_menu = $menu;
        $original_submenu = $submenu;

        // پاک کردن منوی فعلی
        $menu = [];

        // === بخش اول: منوهای ثابت قالب ===

        // 1. پیشخوان (همیشه نمایش داده می‌شود)
        $this->force_restore_menu_item($original_menu, 'index.php', 2);

        // 2. ماژول‌ها
        global $menu;
        $menu[3] = [
            'ماژول‌ها',
            'manage_options',
            'themes.php?page=dst-modules',
            'ماژول‌ها',
            'menu-top menu-icon-generic',
            'menu-modules',
            'dashicons-screenoptions'
        ];

        // 3. تنظیمات وب‌سایت
        add_menu_page(
            'تنظیمات وب‌سایت',
            'تنظیمات وب‌سایت',
            'manage_options',
            'dst-website-settings',
            [$this, 'render_website_settings_page'],
            'dashicons-admin-generic',
            4
        );

        // زیرمنوهای تنظیمات وب‌سایت
        add_submenu_page(
            'dst-website-settings',
            'تنظیمات وب‌سایت',
            'داشبورد',
            'manage_options',
            'dst-website-settings'
        );

        add_submenu_page(
            'dst-website-settings',
            'هدر و فوتر',
            'هدر و فوتر',
            'manage_options',
            'admin.php?page=dst-header-footer'
        );

        add_submenu_page(
            'dst-website-settings',
            'تنظیمات قالب',
            'تنظیمات قالب',
            'manage_options',
            'admin.php?page=dst-theme-settings'
        );

        add_submenu_page(
            'dst-website-settings',
            'هویت سایت',
            'هویت سایت',
            'manage_options',
            'customize.php?autofocus[section]=title_tagline'
        );

        // === جداکننده 1 ===
        $menu[5] = ['', 'read', 'separator1', '', 'wp-menu-separator'];

        // === بخش دوم: منوهای انتخاب شده توسط کاربر ===
        $position = 10;
        foreach ($this->main_sidebar_menus as $slug) {
            // پیشخوان رو قبلاً اضافه کردیم
            if ($slug === 'index.php') continue;

            // بازیابی منو از لیست اصلی
            if ($this->force_restore_menu_item($original_menu, $slug, $position)) {
                $position += 5;
            }
        }

        // === جداکننده 2 ===
        $menu[95] = ['', 'read', 'separator2', '', 'wp-menu-separator'];

        // === بخش سوم: همه امکانات ===
        add_menu_page(
            'همه امکانات',
            'همه امکانات',
            'read',
            'dst-all-features',
            [$this, 'render_all_features_page'],
            'dashicons-screenoptions',
            100
        );

        // جمع‌آوری همه منوها برای صفحه "همه امکانات"
        $this->collect_all_menus($original_menu);

        // مرتب‌سازی منو
        ksort($menu);
    }

    /**
     * بازگرداندن یک آیتم منو (بدون چک کردن main_sidebar_menus)
     */
    private function force_restore_menu_item($original_menu, $slug, $position) {
        global $menu;

        foreach ($original_menu as $item) {
            if (isset($item[2]) && $item[2] === $slug) {
                $menu[$position] = $item;
                return true;
            }
        }

        return false;
    }
    
    /**
     * بازگرداندن یک آیتم منو از منوی اصلی
     */
    private function restore_menu_item($original_menu, $slug, $position) {
        global $menu;

        // فقط منوهایی که در سایدبار اصلی هستند نمایش داده شوند
        if (!in_array($slug, $this->main_sidebar_menus)) {
            return false;
        }

        foreach ($original_menu as $item) {
            if (isset($item[2]) && $item[2] === $slug) {
                $menu[$position] = $item;
                return true;
            }
        }

        return false;
    }
    
    /**
     * جمع‌آوری همه منوها برای صفحه "همه امکانات"
     */
    private function collect_all_menus($original_menu) {
        global $submenu;

        // منوهایی که نباید نمایش داده شوند
        $excluded = [
            'dst-header-footer',
            'dst-theme-settings',
            'separator1',
            'separator2',
            'separator3',
            'separator-last',
        ];

        foreach ($original_menu as $item) {
            if (!isset($item[2])) continue;

            $slug = $item[2];

            // رد کردن جداکننده‌ها و موارد مخفی
            if (in_array($slug, $excluded)) continue;
            if (strpos($slug, 'separator') !== false) continue;

            // ساخت URL صحیح
            $url = $this->build_menu_url($slug);

            // جمع‌آوری زیرمنوها
            $sub_items = [];
            if (isset($submenu[$slug]) && is_array($submenu[$slug])) {
                foreach ($submenu[$slug] as $sub) {
                    if (!isset($sub[2])) continue;

                    $sub_slug = $sub[2];
                    $sub_url = $this->build_submenu_url($slug, $sub_slug);

                    $sub_items[] = [
                        'title' => strip_tags($sub[0]),
                        'capability' => $sub[1],
                        'slug' => $sub_slug,
                        'url' => $sub_url,
                    ];
                }
            }

            // ذخیره برای نمایش در صفحه همه امکانات
            $this->others_menus[] = [
                'title' => strip_tags($item[0]),
                'capability' => $item[1],
                'slug' => $slug,
                'url' => $url,
                'icon' => $item[6] ?? 'dashicons-admin-generic',
                'submenus' => $sub_items,
            ];
        }
    }

    /**
     * ساخت URL صحیح برای زیرمنو
     */
    private function build_submenu_url($parent_slug, $sub_slug) {
        // اگر فایل PHP است
        if (strpos($sub_slug, '.php') !== false) {
            return admin_url($sub_slug);
        }

        // اگر URL کامل است
        if (strpos($sub_slug, 'http') === 0) {
            return $sub_slug;
        }

        // اگر پرنت فایل PHP است
        if (strpos($parent_slug, '.php') !== false) {
            return admin_url($parent_slug . '?page=' . $sub_slug);
        }

        // در غیر این صورت admin.php?page=
        return admin_url('admin.php?page=' . $sub_slug);
    }

    /**
     * ساخت URL صحیح برای یک slug منو
     */
    private function build_menu_url($slug) {
        // اگر فایل PHP است
        if (strpos($slug, '.php') !== false) {
            return admin_url($slug);
        }

        // اگر URL کامل است
        if (strpos($slug, 'http') === 0) {
            return $slug;
        }

        // در غیر این صورت admin.php?page=
        return admin_url('admin.php?page=' . $slug);
    }
    
    /**
     * صفحه تنظیمات وب‌سایت
     */
    public function render_website_settings_page() {
        ?>
        <div class="wrap dst-settings-wrap">
            <h1>⚙️ تنظیمات وب‌سایت</h1>
            <p class="description">تنظیمات کلی قالب و وب‌سایت</p>
            
            <div class="dst-settings-grid">
                
                <a href="<?php echo admin_url('admin.php?page=dst-theme-settings'); ?>" class="dst-setting-card">
                    <span class="dashicons dashicons-art"></span>
                    <h3>تنظیمات قالب</h3>
                    <p>لوگو، رنگ‌بندی، فونت و تنظیمات عمومی</p>
                </a>
                
                <a href="<?php echo admin_url('admin.php?page=dst-header-footer'); ?>" class="dst-setting-card">
                    <span class="dashicons dashicons-admin-customizer"></span>
                    <h3>هدر و فوتر</h3>
                    <p>انتخاب نوع هدر و فوتر</p>
                </a>
                
                <a href="<?php echo admin_url('customize.php?autofocus[section]=title_tagline'); ?>" class="dst-setting-card">
                    <span class="dashicons dashicons-admin-site"></span>
                    <h3>هویت سایت</h3>
                    <p>عنوان سایت و توضیحات</p>
                </a>
                
                <a href="<?php echo admin_url('widgets.php'); ?>" class="dst-setting-card">
                    <span class="dashicons dashicons-screenoptions"></span>
                    <h3>ابزارک‌ها</h3>
                    <p>مدیریت ابزارک‌های سایدبار و فوتر</p>
                </a>
                
                <a href="<?php echo admin_url('nav-menus.php'); ?>" class="dst-setting-card">
                    <span class="dashicons dashicons-menu"></span>
                    <h3>فهرست‌ها</h3>
                    <p>مدیریت منوها و فهرست‌های سایت</p>
                </a>
                
            </div>
        </div>
        
        <style>
            .dst-settings-wrap {
                max-width: 1000px;
            }
            .dst-settings-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .dst-setting-card {
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 24px;
                text-decoration: none;
                transition: all 0.3s;
                display: block;
            }
            .dst-setting-card:hover {
                border-color: #2271b1;
                box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                transform: translateY(-2px);
            }
            .dst-setting-card .dashicons {
                font-size: 32px;
                width: 32px;
                height: 32px;
                color: #2271b1;
                margin-bottom: 12px;
            }
            .dst-setting-card h3 {
                margin: 0 0 8px 0;
                color: #1e293b;
                font-size: 16px;
            }
            .dst-setting-card p {
                margin: 0;
                color: #6b7280;
                font-size: 13px;
            }
        </style>
        <?php
    }

    /**
     * صفحه همه امکانات - نمایش تمام منوهای وردپرس
     */
    public function render_all_features_page() {
        ?>
        <div class="wrap dst-all-features-wrap">
            <div class="dst-all-features-header">
                <h1>
                    <i data-lucide="layout-grid"></i>
                    همه امکانات
                </h1>
                <p class="description">دسترسی سریع به تمام بخش‌های مدیریت وردپرس</p>
            </div>

            <?php if (empty($this->others_menus)): ?>
                <div class="dst-all-features-empty">
                    <i data-lucide="inbox"></i>
                    <p>هیچ منویی یافت نشد</p>
                </div>
            <?php else: ?>
                <div class="dst-all-features-grid">
                    <?php foreach ($this->others_menus as $menu_item):
                        if (!current_user_can($menu_item['capability'])) continue;
                        $lucide_icon = $this->get_lucide_icon($menu_item['slug']);
                        $is_in_sidebar = in_array($menu_item['slug'], $this->main_sidebar_menus);
                        $has_submenus = !empty($menu_item['submenus']) && count($menu_item['submenus']) > 1;
                        ?>
                        <div class="dst-feature-card <?php echo $is_in_sidebar ? 'in-sidebar' : ''; ?> <?php echo $has_submenus ? 'has-submenus' : ''; ?>">
                            <a href="<?php echo esc_url($menu_item['url']); ?>" class="dst-feature-item">
                                <span class="dst-feature-icon">
                                    <i data-lucide="<?php echo esc_attr($lucide_icon); ?>"></i>
                                </span>
                                <span class="dst-feature-title"><?php echo esc_html($menu_item['title']); ?></span>
                                <?php if ($is_in_sidebar): ?>
                                    <span class="dst-feature-badge">در منو</span>
                                <?php endif; ?>
                            </a>
                            <?php if ($has_submenus): ?>
                                <button type="button" class="dst-submenu-toggle" title="زیرمنوها">
                                    <i data-lucide="chevron-down"></i>
                                </button>
                                <div class="dst-submenu-dropdown">
                                    <?php foreach ($menu_item['submenus'] as $sub):
                                        if (!current_user_can($sub['capability'])) continue;
                                    ?>
                                        <a href="<?php echo esc_url($sub['url']); ?>" class="dst-submenu-item">
                                            <?php echo esc_html($sub['title']); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <style>
            .dst-all-features-wrap {
                max-width: 1400px;
                margin: 20px auto 0;
            }
            .dst-all-features-header {
                margin-bottom: 30px;
            }
            .dst-all-features-header h1 {
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: 26px;
                font-weight: 600;
                color: #1e293b;
                margin: 0 0 8px;
            }
            .dst-all-features-header h1 svg {
                width: 32px;
                height: 32px;
                stroke: #3C50E0;
            }
            .dst-all-features-header .description {
                font-size: 14px;
                color: #64748b;
                margin: 0;
            }
            .dst-all-features-empty {
                text-align: center;
                padding: 60px 20px;
                background: #fff;
                border: 1px dashed #e2e8f0;
                border-radius: 12px;
            }
            .dst-all-features-empty svg {
                width: 48px;
                height: 48px;
                stroke: #94a3b8;
                margin-bottom: 16px;
            }
            .dst-all-features-empty p {
                color: #64748b;
                font-size: 15px;
                margin: 0;
            }
            .dst-all-features-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 16px;
            }
            .dst-feature-card {
                position: relative;
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                transition: all 0.2s ease;
            }
            .dst-feature-card:hover {
                border-color: #3C50E0;
                box-shadow: 0 4px 12px rgba(60, 80, 224, 0.1);
            }
            .dst-feature-card.in-sidebar {
                border-color: #86efac;
                background: #f0fdf4;
            }
            .dst-feature-card.in-sidebar:hover {
                border-color: #10b981;
            }
            .dst-feature-item {
                padding: 20px;
                text-decoration: none;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 12px;
            }
            .dst-feature-icon {
                width: 52px;
                height: 52px;
                background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
            }
            .dst-feature-icon svg {
                width: 26px;
                height: 26px;
                stroke: #64748b;
                transition: all 0.2s ease;
            }
            .dst-feature-card:hover .dst-feature-icon {
                background: linear-gradient(135deg, #3C50E0 0%, #5B6CE0 100%);
            }
            .dst-feature-card:hover .dst-feature-icon svg {
                stroke: #fff;
            }
            .dst-feature-card.in-sidebar .dst-feature-icon {
                background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            }
            .dst-feature-card.in-sidebar .dst-feature-icon svg {
                stroke: #fff;
            }
            .dst-feature-title {
                color: #1e293b;
                font-size: 14px;
                font-weight: 500;
                text-align: center;
            }
            .dst-feature-card:hover .dst-feature-title {
                color: #3C50E0;
            }
            .dst-feature-badge {
                position: absolute;
                top: 8px;
                left: 8px;
                background: #10b981;
                color: #fff;
                font-size: 10px;
                padding: 3px 8px;
                border-radius: 4px;
                z-index: 1;
            }
            /* زیرمنوها */
            .dst-submenu-toggle {
                position: absolute;
                top: 8px;
                right: 8px;
                width: 28px;
                height: 28px;
                border: none;
                background: rgba(60, 80, 224, 0.1);
                border-radius: 6px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
                z-index: 2;
            }
            .dst-submenu-toggle:hover {
                background: #3C50E0;
            }
            .dst-submenu-toggle svg {
                width: 16px;
                height: 16px;
                stroke: #3C50E0;
                transition: all 0.2s ease;
            }
            .dst-submenu-toggle:hover svg {
                stroke: #fff;
            }
            .dst-feature-card.submenu-open .dst-submenu-toggle {
                background: #3C50E0;
            }
            .dst-feature-card.submenu-open .dst-submenu-toggle svg {
                stroke: #fff;
                transform: rotate(180deg);
            }
            .dst-submenu-dropdown {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #fff;
                border: 1px solid #e2e8f0;
                border-top: none;
                border-radius: 0 0 12px 12px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.12);
                z-index: 100;
                max-height: 300px;
                overflow-y: auto;
            }
            .dst-feature-card.submenu-open .dst-submenu-dropdown {
                display: block;
            }
            .dst-submenu-item {
                display: block;
                padding: 12px 16px;
                color: #475569;
                text-decoration: none;
                font-size: 13px;
                border-bottom: 1px solid #f1f5f9;
                transition: all 0.15s ease;
            }
            .dst-submenu-item:last-child {
                border-bottom: none;
                border-radius: 0 0 12px 12px;
            }
            .dst-submenu-item:hover {
                background: #f8fafc;
                color: #3C50E0;
                padding-right: 20px;
            }
            @media screen and (max-width: 782px) {
                .dst-all-features-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
            @media screen and (max-width: 480px) {
                .dst-all-features-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // زیرمنو toggle
            document.querySelectorAll('.dst-submenu-toggle').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var card = this.closest('.dst-feature-card');
                    var wasOpen = card.classList.contains('submenu-open');

                    // بستن همه dropdown های دیگر
                    document.querySelectorAll('.dst-feature-card.submenu-open').forEach(function(c) {
                        c.classList.remove('submenu-open');
                    });

                    // باز/بسته کردن این dropdown
                    if (!wasOpen) {
                        card.classList.add('submenu-open');
                    }
                });
            });

            // بستن dropdown با کلیک بیرون
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dst-feature-card')) {
                    document.querySelectorAll('.dst-feature-card.submenu-open').forEach(function(c) {
                        c.classList.remove('submenu-open');
                    });
                }
            });
        });
        </script>
        <?php
    }

    /**
     * گرفتن آیکون Lucide مناسب برای هر منو
     */
    private function get_lucide_icon($slug) {
        $icons = [
            // وردپرس
            'edit-comments.php' => 'message-square',
            'themes.php' => 'palette',
            'plugins.php' => 'plug',
            'tools.php' => 'wrench',
            'options-general.php' => 'settings',
            'widgets.php' => 'layout-grid',
            'customize.php' => 'brush',
            'theme-editor.php' => 'code',
            'plugin-editor.php' => 'code-2',
            'update-core.php' => 'download-cloud',
            'site-health.php' => 'activity',

            // ووکامرس
            'woocommerce' => 'shopping-cart',
            'wc-admin' => 'bar-chart-3',
            'wc-settings' => 'settings',
            'wc-status' => 'activity',
            'edit.php?post_type=product' => 'package',

            // ACF
            'edit.php?post_type=acf-field-group' => 'layers',
            'acf-tools' => 'database',

            // سئو
            'wpseo_dashboard' => 'search',
            'rank-math' => 'bar-chart-2',

            // فرم‌ها
            'wpcf7' => 'mail',
            'wpforms-overview' => 'file-input',
            'gf_edit_forms' => 'clipboard-list',
            'ninja-forms' => 'file-edit',

            // المنتور
            'elementor' => 'box',

            // کش و عملکرد
            'wp-rocket' => 'rocket',
            'w3tc_dashboard' => 'zap',
            'litespeed' => 'gauge',

            // امنیت
            'wordfence' => 'shield',
            'sucuri' => 'lock',
            'itsec' => 'shield-check',

            // بکاپ
            'updraftplus' => 'hard-drive',
            'duplicator' => 'copy',
        ];

        foreach ($icons as $key => $icon) {
            if (strpos($slug, $key) !== false) {
                return $icon;
            }
        }

        return 'circle';
    }

    /**
     * صفحه تنظیمات منو
     */
    public function render_settings_page() {
        global $menu;

        // لیست همه منوها
        $all_menus = $this->get_all_menu_items();
        $in_sidebar = $this->main_sidebar_menus;
        ?>
        <div class="wrap dst-menu-settings-wrap">
            <div class="dst-menu-settings-header">
                <h1>
                    <i data-lucide="settings-2"></i>
                    تنظیمات منو
                </h1>
                <p class="description">منوهایی که تیک دارند در سایدبار اصلی نمایش داده می‌شوند. بقیه در بخش «سایر» قابل دسترسی هستند.</p>
            </div>

            <form method="post" action="" id="dst-menu-settings-form">
                <?php wp_nonce_field('dst_menu_settings_nonce', 'dst_menu_nonce'); ?>

                <div class="dst-menu-settings-grid">
                    <?php foreach ($all_menus as $menu_item):
                        $slug = $menu_item['slug'];
                        $is_in_sidebar = in_array($slug, $in_sidebar);
                        $lucide_icon = $this->get_lucide_icon($slug);
                        ?>
                        <div class="dst-menu-settings-item <?php echo $is_in_sidebar ? 'is-active' : 'is-other'; ?>">
                            <label class="dst-menu-toggle">
                                <input type="checkbox"
                                       name="dst_main_sidebar_menus[]"
                                       value="<?php echo esc_attr($slug); ?>"
                                       <?php checked($is_in_sidebar); ?>>
                                <span class="dst-menu-toggle-slider"></span>
                            </label>
                            <span class="dst-menu-icon">
                                <i data-lucide="<?php echo esc_attr($lucide_icon); ?>"></i>
                            </span>
                            <span class="dst-menu-name"><?php echo esc_html($menu_item['title']); ?></span>
                            <?php if ($is_in_sidebar): ?>
                                <span class="dst-menu-badge dst-badge-active">سایدبار</span>
                            <?php else: ?>
                                <span class="dst-menu-badge dst-badge-other">سایر</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="dst-menu-settings-footer">
                    <button type="submit" class="button button-primary dst-save-btn">
                        <i data-lucide="save"></i>
                        ذخیره تنظیمات
                    </button>
                    <span class="dst-save-message"></span>
                </div>
            </form>
        </div>

        <style>
            .dst-menu-settings-wrap {
                max-width: 900px;
                margin: 20px auto 0;
            }
            .dst-menu-settings-header {
                margin-bottom: 30px;
            }
            .dst-menu-settings-header h1 {
                display: flex;
                align-items: center;
                gap: 12px;
                font-size: 24px;
                font-weight: 600;
                color: #1e293b;
                margin: 0 0 8px;
            }
            .dst-menu-settings-header h1 svg {
                width: 28px;
                height: 28px;
                stroke: #3C50E0;
            }
            .dst-menu-settings-header .description {
                font-size: 14px;
                color: #64748b;
                margin: 0;
            }
            .dst-menu-settings-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 12px;
                margin-bottom: 30px;
            }
            .dst-menu-settings-item {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                padding: 16px;
                display: flex;
                align-items: center;
                gap: 12px;
                transition: all 0.2s ease;
            }
            .dst-menu-settings-item:hover {
                border-color: #cbd5e1;
                box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            }
            .dst-menu-settings-item.is-active {
                background: #f0fdf4;
                border-color: #86efac;
            }
            .dst-menu-settings-item.is-other {
                background: #f8fafc;
                border-color: #e2e8f0;
            }
            .dst-menu-toggle {
                position: relative;
                width: 44px;
                height: 24px;
                flex-shrink: 0;
            }
            .dst-menu-toggle input {
                opacity: 0;
                width: 0;
                height: 0;
            }
            .dst-menu-toggle-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #94a3b8;
                border-radius: 24px;
                transition: 0.3s;
            }
            .dst-menu-toggle-slider:before {
                position: absolute;
                content: "";
                height: 18px;
                width: 18px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                border-radius: 50%;
                transition: 0.3s;
            }
            .dst-menu-toggle input:checked + .dst-menu-toggle-slider {
                background-color: #10b981;
            }
            .dst-menu-toggle input:checked + .dst-menu-toggle-slider:before {
                transform: translateX(20px);
            }
            .dst-menu-icon {
                width: 36px;
                height: 36px;
                background: #f1f5f9;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .dst-menu-icon svg {
                width: 18px;
                height: 18px;
                stroke: #64748b;
            }
            .dst-menu-settings-item.is-active .dst-menu-icon {
                background: #dcfce7;
            }
            .dst-menu-settings-item.is-active .dst-menu-icon svg {
                stroke: #10b981;
            }
            .dst-menu-name {
                flex: 1;
                font-size: 14px;
                font-weight: 500;
                color: #1e293b;
            }
            .dst-menu-badge {
                font-size: 11px;
                padding: 4px 8px;
                border-radius: 4px;
            }
            .dst-badge-active {
                background: #10b981;
                color: #fff;
            }
            .dst-badge-other {
                background: #94a3b8;
                color: #fff;
            }
            .dst-menu-settings-footer {
                display: flex;
                align-items: center;
                gap: 16px;
                padding-top: 20px;
                border-top: 1px solid #e2e8f0;
            }
            .dst-save-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 24px !important;
                height: auto !important;
                font-size: 14px !important;
            }
            .dst-save-btn svg {
                width: 18px;
                height: 18px;
            }
            .dst-save-message {
                font-size: 14px;
                color: #10b981;
            }
            @media screen and (max-width: 782px) {
                .dst-menu-settings-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Toggle visual state
            $('.dst-menu-toggle input').on('change', function() {
                var $item = $(this).closest('.dst-menu-settings-item');
                var $badge = $item.find('.dst-menu-badge');

                if (this.checked) {
                    // در سایدبار اصلی
                    $item.removeClass('is-other').addClass('is-active');
                    $badge.removeClass('dst-badge-other').addClass('dst-badge-active').text('سایدبار');
                } else {
                    // در سایر
                    $item.removeClass('is-active').addClass('is-other');
                    $badge.removeClass('dst-badge-active').addClass('dst-badge-other').text('سایر');
                }
            });

            // AJAX save
            $('#dst-menu-settings-form').on('submit', function(e) {
                e.preventDefault();

                var $form = $(this);
                var $btn = $form.find('.dst-save-btn');
                var $msg = $form.find('.dst-save-message');

                $btn.prop('disabled', true);
                $msg.text('در حال ذخیره...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'dst_save_menu_settings',
                        nonce: $('#dst_menu_nonce').val(),
                        main_sidebar_menus: $form.find('input[name="dst_main_sidebar_menus[]"]:checked').map(function() {
                            return $(this).val();
                        }).get()
                    },
                    success: function(response) {
                        if (response.success) {
                            $msg.css('color', '#10b981').text('ذخیره شد! صفحه در حال بارگذاری مجدد...');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            $msg.css('color', '#ef4444').text('خطا در ذخیره');
                        }
                    },
                    error: function() {
                        $msg.css('color', '#ef4444').text('خطا در ارتباط');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });

            // Init Lucide
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
        </script>
        <?php
    }

    /**
     * گرفتن لیست همه آیتم‌های منو
     */
    private function get_all_menu_items() {
        global $menu;

        $items = [];
        $excluded = ['separator', 'dst-'];

        if (!is_array($menu)) return $items;

        // منوهای فعلی
        foreach ($menu as $item) {
            if (!isset($item[2]) || empty($item[0])) continue;

            $slug = $item[2];

            // رد کردن جداکننده‌ها
            $skip = false;
            foreach ($excluded as $ex) {
                if (strpos($slug, $ex) !== false) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;

            $items[$slug] = [
                'title' => strip_tags($item[0]),
                'slug' => $slug,
                'icon' => $item[6] ?? 'dashicons-admin-generic',
            ];
        }

        // لیست کامل منوهای شناخته شده وردپرس و پلاگین‌ها
        $known_menus = [
            // منوهای اصلی وردپرس
            'index.php' => 'پیشخوان',
            'edit.php' => 'نوشته‌ها',
            'upload.php' => 'رسانه',
            'edit.php?post_type=page' => 'برگه‌ها',
            'edit-comments.php' => 'دیدگاه‌ها',
            'themes.php' => 'نمایش',
            'plugins.php' => 'افزونه‌ها',
            'users.php' => 'کاربران',
            'tools.php' => 'ابزارها',
            'options-general.php' => 'تنظیمات',
            'nav-menus.php' => 'فهرست‌ها',
            'widgets.php' => 'ابزارک‌ها',
            'customize.php' => 'سفارشی‌سازی',
            'update-core.php' => 'به‌روزرسانی‌ها',
            'site-health.php' => 'سلامت سایت',

            // ووکامرس
            'woocommerce' => 'ووکامرس',
            'edit.php?post_type=product' => 'محصولات',
            'edit.php?post_type=shop_order' => 'سفارشات',
            'wc-admin' => 'تحلیل‌ها',
            'wc-admin&path=/analytics/overview' => 'گزارشات',

            // ACF
            'edit.php?post_type=acf-field-group' => 'فیلدهای ACF',

            // سئو
            'wpseo_dashboard' => 'یواست سئو',
            'rank-math' => 'رنک مث',

            // فرم‌ها
            'wpcf7' => 'فرم تماس 7',
            'wpforms-overview' => 'WP Forms',
            'gf_edit_forms' => 'گرویتی فرمز',

            // المنتور
            'elementor' => 'المنتور',
            'edit.php?post_type=elementor_library' => 'قالب‌های المنتور',

            // سایر پلاگین‌ها
            'jetpack' => 'جت‌پک',
            'wordfence' => 'وردفنس',
            'updraftplus' => 'آپدرافت پلاس',
            'redirection.php' => 'ریدایرکشن',
            'smush' => 'اسماش',
            'w3tc_dashboard' => 'کش W3',
            'wp-rocket' => 'راکت',
            'litespeed' => 'لایت‌اسپید',
        ];

        // اضافه کردن همه منوهای شناخته شده که در لیست نیستند
        foreach ($known_menus as $slug => $title) {
            if (!isset($items[$slug])) {
                $items[$slug] = [
                    'title' => $title,
                    'slug' => $slug,
                    'icon' => 'dashicons-admin-generic',
                ];
            }
        }

        // اضافه کردن منوهای سایدبار اصلی که ممکن است در لیست نباشند
        foreach ($this->main_sidebar_menus as $slug) {
            if (!isset($items[$slug])) {
                $title = isset($known_menus[$slug]) ? $known_menus[$slug] : $slug;
                $items[$slug] = [
                    'title' => $title,
                    'slug' => $slug,
                    'icon' => 'dashicons-admin-generic',
                ];
            }
        }

        return array_values($items);
    }

    /**
     * ذخیره تنظیمات با AJAX
     */
    public function ajax_save_settings() {
        // بررسی nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'dst_menu_settings_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce']);
        }

        // بررسی دسترسی
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Access denied']);
        }

        // ذخیره تنظیمات - منوهایی که در سایدبار اصلی نمایش داده شوند
        $main_sidebar_menus = isset($_POST['main_sidebar_menus']) ? array_map('sanitize_text_field', $_POST['main_sidebar_menus']) : [];
        update_option('dst_main_sidebar_menus', $main_sidebar_menus);

        wp_send_json_success(['message' => 'Settings saved']);
    }
}

// راه‌اندازی ماژول
new DST_Admin_Menu_Manager();
