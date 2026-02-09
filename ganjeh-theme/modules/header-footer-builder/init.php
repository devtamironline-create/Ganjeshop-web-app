<?php
/**
 * ماژول سازنده هدر و فوتر
 * Header Footer Builder Module
 *
 * @package Developer_Starter
 * @subpackage Modules/Header_Footer_Builder
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

class DST_Header_Footer_Builder {

    private $module_path;
    private $module_url;
    private $option_name = 'dst_hf_builder_settings';
    private $settings;

    /**
     * المان‌های موجود
     */
    private $elements = [];

    /**
     * سازنده
     */
    public function __construct() {
        // تنظیم مسیرها مستقیم
        $this->module_path = dirname(__FILE__);
        $this->module_url  = get_template_directory_uri() . '/modules/header-footer-builder';
        $this->settings    = get_option($this->option_name, $this->get_default_settings());

        // ثبت المان‌ها
        $this->register_elements();

        // هوک‌ها - زیرمنوی تنظیمات وب‌سایت (priority بالاتر از 9999 که parent menu ساخته میشه)
        add_action('admin_menu', [$this, 'add_admin_menu'], 10001);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_assets']);
        add_action('wp_ajax_dst_builder_save', [$this, 'ajax_save']);
        add_action('wp_ajax_dst_builder_preview', [$this, 'ajax_preview']);

        // پیش‌نمایش - باید بعد از init اجرا بشه که user شناسایی شده باشه
        add_action('init', [$this, 'handle_preview_mode'], 1);
    }

    /**
     * تنظیمات پیش‌فرض
     */
    private function get_default_settings() {
        return [
            'header' => [
                'enabled' => true,
                'rows' => [
                    [
                        'id' => 'main',
                        'columns' => 3,
                        'layout' => '1-2-1', // نسبت ستون‌ها
                        'elements' => [
                            'left' => [['type' => 'logo', 'settings' => []]],
                            'center' => [['type' => 'menu', 'settings' => ['menu' => 'primary']]],
                            'right' => [['type' => 'button', 'settings' => ['text' => 'تماس با ما', 'url' => '#']]]
                        ],
                        'settings' => [
                            'bg_color' => '#ffffff',
                            'padding' => '15px 0',
                            'sticky' => false
                        ]
                    ]
                ],
                'mobile_menu' => 'offcanvas',
                'settings' => [
                    'width_type' => 'contained',
                    'container_width' => 1200,
                    'bg_color' => '#ffffff',
                    'sticky' => false,
                    'shadow_enabled' => true,
                    'shadow_color' => 'rgba(0,0,0,0.08)',
                    'shadow_x' => 0,
                    'shadow_y' => 2,
                    'shadow_blur' => 10,
                    'shadow_spread' => 0,
                    'border_enabled' => false,
                    'border_color' => '#e5e7eb',
                    'border_width' => 1,
                    'border_style' => 'solid'
                ]
            ],
            'footer' => [
                'enabled' => true,
                'rows' => [
                    [
                        'id' => 'main',
                        'columns' => 4,
                        'layout' => '1-1-1-1',
                        'elements' => [
                            'col1' => [['type' => 'logo', 'settings' => []], ['type' => 'text', 'settings' => ['content' => 'توضیحات سایت']]],
                            'col2' => [['type' => 'menu', 'settings' => ['menu' => 'footer-1', 'title' => 'لینک‌های مفید']]],
                            'col3' => [['type' => 'menu', 'settings' => ['menu' => 'footer-2', 'title' => 'خدمات']]],
                            'col4' => [['type' => 'contact_info', 'settings' => []]]
                        ],
                        'settings' => [
                            'bg_color' => '#1f2937',
                            'text_color' => '#ffffff',
                            'padding' => '60px 0'
                        ]
                    ],
                    [
                        'id' => 'copyright',
                        'columns' => 2,
                        'layout' => '1-1',
                        'elements' => [
                            'col1' => [['type' => 'copyright', 'settings' => []]],
                            'col2' => [['type' => 'social', 'settings' => []]]
                        ],
                        'settings' => [
                            'bg_color' => '#111827',
                            'text_color' => '#9ca3af',
                            'padding' => '20px 0'
                        ]
                    ]
                ],
                'settings' => [
                    'width_type' => 'contained',
                    'container_width' => 1200,
                    'bg_color' => '#1f2937',
                    'border_enabled' => false,
                    'border_color' => '#374151',
                    'border_width' => 1,
                    'border_style' => 'solid'
                ]
            ]
        ];
    }

    /**
     * ثبت المان‌ها
     */
    private function register_elements() {
        $this->elements = [
            'logo' => [
                'title' => 'لوگو',
                'icon' => 'image',
                'category' => 'basic',
                'settings' => [
                    'max_height' => ['type' => 'number', 'label' => 'حداکثر ارتفاع (px)', 'default' => 50],
                    'max_width' => ['type' => 'number', 'label' => 'حداکثر عرض (px)', 'default' => 200],
                    'custom_logo' => ['type' => 'image', 'label' => 'لوگوی سفارشی', 'default' => ''],
                    'show_site_title' => ['type' => 'checkbox', 'label' => 'نمایش نام سایت', 'default' => false],
                    'title_color' => ['type' => 'color', 'label' => 'رنگ نام سایت', 'default' => '#1e293b'],
                    'title_size' => ['type' => 'number', 'label' => 'اندازه نام سایت (px)', 'default' => 24],
                ]
            ],
            'menu' => [
                'title' => 'منو',
                'icon' => 'menu',
                'category' => 'basic',
                'settings' => [
                    'menu' => ['type' => 'select', 'label' => 'انتخاب منو', 'options' => 'menus', 'default' => ''],
                    'title' => ['type' => 'text', 'label' => 'عنوان (برای فوتر)', 'default' => ''],
                    'style' => ['type' => 'select', 'label' => 'استایل', 'options' => ['horizontal' => 'افقی', 'vertical' => 'عمودی'], 'default' => 'horizontal'],
                    'font_size' => ['type' => 'number', 'label' => 'اندازه فونت (px)', 'default' => 14],
                    'font_weight' => ['type' => 'select', 'label' => 'وزن فونت', 'options' => ['400' => 'معمولی', '500' => 'متوسط', '600' => 'نیمه‌بولد', '700' => 'بولد'], 'default' => '500'],
                    'text_color' => ['type' => 'color', 'label' => 'رنگ متن', 'default' => '#333333'],
                    'hover_color' => ['type' => 'color', 'label' => 'رنگ هاور', 'default' => '#2563eb'],
                    'gap' => ['type' => 'number', 'label' => 'فاصله آیتم‌ها (px)', 'default' => 25],
                ]
            ],
            'search' => [
                'title' => 'جستجو',
                'icon' => 'search',
                'category' => 'basic',
                'settings' => [
                    'style' => ['type' => 'select', 'label' => 'نوع نمایش', 'options' => ['icon' => 'فقط آیکون', 'form' => 'فرم کامل', 'expandable' => 'قابل گسترش'], 'default' => 'icon'],
                    'placeholder' => ['type' => 'text', 'label' => 'متن راهنما', 'default' => 'جستجو...'],
                ]
            ],
            'button' => [
                'title' => 'دکمه',
                'icon' => 'square',
                'category' => 'basic',
                'settings' => [
                    'text' => ['type' => 'text', 'label' => 'متن دکمه', 'default' => 'کلیک کنید'],
                    'url' => ['type' => 'text', 'label' => 'لینک', 'default' => '#'],
                    'target' => ['type' => 'checkbox', 'label' => 'باز شدن در تب جدید', 'default' => false],
                    'style' => ['type' => 'select', 'label' => 'استایل', 'options' => ['primary' => 'اصلی', 'secondary' => 'ثانویه', 'outline' => 'خط‌دار', 'custom' => 'سفارشی'], 'default' => 'primary'],
                    'bg_color' => ['type' => 'color', 'label' => 'رنگ پس‌زمینه', 'default' => '#2563eb'],
                    'text_color' => ['type' => 'color', 'label' => 'رنگ متن', 'default' => '#ffffff'],
                    'border_radius' => ['type' => 'number', 'label' => 'گردی گوشه (px)', 'default' => 8],
                    'font_size' => ['type' => 'number', 'label' => 'اندازه فونت (px)', 'default' => 14],
                    'padding_x' => ['type' => 'number', 'label' => 'پدینگ افقی (px)', 'default' => 24],
                    'padding_y' => ['type' => 'number', 'label' => 'پدینگ عمودی (px)', 'default' => 12],
                ]
            ],
            'text' => [
                'title' => 'متن',
                'icon' => 'type',
                'category' => 'basic',
                'settings' => [
                    'content' => ['type' => 'textarea', 'label' => 'محتوا', 'default' => ''],
                    'tag' => ['type' => 'select', 'label' => 'تگ HTML', 'options' => ['p' => 'پاراگراف', 'span' => 'Span', 'div' => 'Div', 'h4' => 'H4', 'h5' => 'H5'], 'default' => 'p'],
                ]
            ],
            'html' => [
                'title' => 'HTML سفارشی',
                'icon' => 'code',
                'category' => 'advanced',
                'settings' => [
                    'content' => ['type' => 'textarea', 'label' => 'کد HTML', 'default' => ''],
                ]
            ],
            'social' => [
                'title' => 'شبکه‌های اجتماعی',
                'icon' => 'share-2',
                'category' => 'basic',
                'settings' => [
                    'style' => ['type' => 'select', 'label' => 'استایل', 'options' => ['icon' => 'فقط آیکون', 'icon-text' => 'آیکون و متن', 'text' => 'فقط متن'], 'default' => 'icon'],
                    'size' => ['type' => 'select', 'label' => 'اندازه', 'options' => ['sm' => 'کوچک', 'md' => 'متوسط', 'lg' => 'بزرگ'], 'default' => 'md'],
                    'instagram' => ['type' => 'text', 'label' => 'اینستاگرام', 'default' => ''],
                    'telegram' => ['type' => 'text', 'label' => 'تلگرام', 'default' => ''],
                    'whatsapp' => ['type' => 'text', 'label' => 'واتساپ', 'default' => ''],
                    'twitter' => ['type' => 'text', 'label' => 'توییتر', 'default' => ''],
                    'linkedin' => ['type' => 'text', 'label' => 'لینکدین', 'default' => ''],
                    'youtube' => ['type' => 'text', 'label' => 'یوتیوب', 'default' => ''],
                ]
            ],
            'cart' => [
                'title' => 'سبد خرید',
                'icon' => 'shopping-cart',
                'category' => 'woocommerce',
                'settings' => [
                    'show_count' => ['type' => 'checkbox', 'label' => 'نمایش تعداد', 'default' => true],
                    'show_total' => ['type' => 'checkbox', 'label' => 'نمایش مبلغ', 'default' => false],
                    'style' => ['type' => 'select', 'label' => 'استایل', 'options' => ['icon' => 'آیکون', 'dropdown' => 'با دراپ‌داون'], 'default' => 'icon'],
                ]
            ],
            'account' => [
                'title' => 'حساب کاربری',
                'icon' => 'user',
                'category' => 'woocommerce',
                'settings' => [
                    'logged_in_text' => ['type' => 'text', 'label' => 'متن برای کاربر وارد شده', 'default' => 'حساب من'],
                    'logged_out_text' => ['type' => 'text', 'label' => 'متن برای مهمان', 'default' => 'ورود / ثبت‌نام'],
                ]
            ],
            'wishlist' => [
                'title' => 'لیست علاقه‌مندی',
                'icon' => 'heart',
                'category' => 'woocommerce',
                'settings' => [
                    'show_count' => ['type' => 'checkbox', 'label' => 'نمایش تعداد', 'default' => true],
                ]
            ],
            'contact_info' => [
                'title' => 'اطلاعات تماس',
                'icon' => 'phone',
                'category' => 'basic',
                'settings' => [
                    'title' => ['type' => 'text', 'label' => 'عنوان', 'default' => 'تماس با ما'],
                    'phone' => ['type' => 'text', 'label' => 'تلفن', 'default' => ''],
                    'email' => ['type' => 'text', 'label' => 'ایمیل', 'default' => ''],
                    'address' => ['type' => 'textarea', 'label' => 'آدرس', 'default' => ''],
                ]
            ],
            'copyright' => [
                'title' => 'کپی‌رایت',
                'icon' => 'copyright',
                'category' => 'basic',
                'settings' => [
                    'text' => ['type' => 'text', 'label' => 'متن', 'default' => '© {year} {site_name}. تمامی حقوق محفوظ است.'],
                ]
            ],
            'divider' => [
                'title' => 'جداکننده',
                'icon' => 'minus',
                'category' => 'layout',
                'settings' => [
                    'style' => ['type' => 'select', 'label' => 'استایل', 'options' => ['solid' => 'خط', 'dashed' => 'خط‌چین', 'dotted' => 'نقطه‌چین'], 'default' => 'solid'],
                    'color' => ['type' => 'color', 'label' => 'رنگ', 'default' => '#e5e7eb'],
                    'width' => ['type' => 'text', 'label' => 'عرض', 'default' => '100%'],
                ]
            ],
            'spacer' => [
                'title' => 'فاصله',
                'icon' => 'move-vertical',
                'category' => 'layout',
                'settings' => [
                    'height' => ['type' => 'number', 'label' => 'ارتفاع (px)', 'default' => 20],
                ]
            ],
            'image' => [
                'title' => 'تصویر',
                'icon' => 'image',
                'category' => 'basic',
                'settings' => [
                    'image' => ['type' => 'image', 'label' => 'تصویر', 'default' => ''],
                    'url' => ['type' => 'text', 'label' => 'لینک', 'default' => ''],
                    'alt' => ['type' => 'text', 'label' => 'متن جایگزین', 'default' => ''],
                    'max_width' => ['type' => 'text', 'label' => 'حداکثر عرض', 'default' => '100%'],
                ]
            ],
        ];

        // فیلتر برای افزودن المان‌های سفارشی
        $this->elements = apply_filters('dst_builder_elements', $this->elements);

        // اضافه کردن تنظیمات ریسپانسیو به همه المان‌ها
        $this->inject_visibility_settings();
    }

    /**
     * تزریق تنظیمات نمایش به همه المان‌ها
     */
    private function inject_visibility_settings() {
        $visibility_settings = [
            'hide_desktop' => ['type' => 'checkbox', 'label' => 'مخفی در دسکتاپ', 'default' => false],
            'hide_tablet' => ['type' => 'checkbox', 'label' => 'مخفی در تبلت', 'default' => false],
            'hide_mobile' => ['type' => 'checkbox', 'label' => 'مخفی در موبایل', 'default' => false],
        ];

        foreach ($this->elements as $key => $element) {
            $this->elements[$key]['settings'] = array_merge(
                $element['settings'] ?? [],
                $visibility_settings
            );
        }
    }

    /**
     * حالت پیش‌نمایش
     */
    public function handle_preview_mode() {
        if (!isset($_GET['dst_builder_preview'])) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        // بارگذاری تنظیمات موقت برای پیش‌نمایش
        $transient_key = 'dst_builder_preview_' . get_current_user_id();
        $preview_settings = get_transient($transient_key);

        if ($preview_settings && is_array($preview_settings)) {
            $this->settings = $preview_settings;

            // Debug: Output settings to console
            add_action('wp_footer', function() use ($preview_settings) {
                echo '<script>console.log("📋 Preview loaded settings:", ' . wp_json_encode($preview_settings) . ');</script>';
            }, 999);
        } else {
            // Debug: No transient found
            add_action('wp_footer', function() use ($transient_key) {
                echo '<script>console.warn("⚠️ No preview transient found for: ' . esc_js($transient_key) . '");</script>';
            }, 999);
        }

        // مخفی کردن ادمین بار
        add_filter('show_admin_bar', '__return_false');
        add_action('wp_head', function() {
            echo '<style>
                html { margin-top: 0 !important; }
                #wpadminbar { display: none !important; }
                body.admin-bar { margin-top: 0 !important; }
            </style>';
        }, 999);

        // اضافه کردن کلاس preview mode به body
        add_filter('body_class', function($classes) {
            $classes[] = 'dst-preview-mode';
            return $classes;
        });
    }

    /**
     * اضافه کردن منو
     */
    public function add_admin_menu() {
        // زیرمنوی تنظیمات وب‌سایت
        add_submenu_page(
            'dst-website-settings',
            'سازنده هدر و فوتر',
            'سازنده هدر/فوتر',
            'manage_options',
            'dst-hf-builder',
            [$this, 'render_admin_page']
        );
    }

    /**
     * فایل‌های ادمین
     */
    public function admin_assets($hook) {
        // برای submenu، hook به شکل 'parent_page_slug' میشه
        if ($hook !== 'dst-website-settings_page_dst-hf-builder' && strpos($hook, 'dst-hf-builder') === false) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_style(
            'dst-builder-admin',
            $this->module_url . '/assets/css/builder-admin.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'dst-builder-admin',
            $this->module_url . '/assets/js/builder-admin.js',
            ['jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable'],
            '1.0.0',
            true
        );

        wp_localize_script('dst-builder-admin', 'dstBuilder', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dst_builder_nonce'),
            'previewUrl' => home_url('/?dst_builder_preview=1'),
            'elements' => $this->elements,
            'settings' => $this->settings,
            'menus' => $this->get_menus_list(),
            'i18n' => [
                'save' => 'ذخیره',
                'saving' => 'در حال ذخیره...',
                'saved' => 'ذخیره شد!',
                'error' => 'خطا در ذخیره',
                'confirm_delete' => 'آیا مطمئن هستید؟',
                'add_element' => 'افزودن المان',
                'add_row' => 'افزودن ردیف',
            ]
        ]);
    }

    /**
     * لیست منوها
     */
    private function get_menus_list() {
        $list = ['' => 'انتخاب کنید...'];

        // Theme locations
        $locations = get_registered_nav_menus();
        if (!empty($locations)) {
            foreach ($locations as $location => $name) {
                $list['location:' . $location] = '📍 ' . $name;
            }
        }

        // Custom menus
        $menus = wp_get_nav_menus();
        if (!empty($menus)) {
            foreach ($menus as $menu) {
                $list['menu:' . $menu->term_id] = '📋 ' . $menu->name;
            }
        }

        return $list;
    }

    /**
     * فایل‌های فرانت‌اند
     */
    public function frontend_assets() {
        wp_enqueue_style(
            'dst-builder-frontend',
            $this->module_url . '/assets/css/builder-frontend.css',
            [],
            '1.0.0'
        );
    }

    /**
     * ذخیره با AJAX
     */
    public function ajax_save() {
        check_ajax_referer('dst_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        // خواندن از JSON برای حفظ ساختار nested
        $settings = [];
        if (isset($_POST['settings_json'])) {
            $settings = json_decode(stripslashes($_POST['settings_json']), true);
        } elseif (isset($_POST['settings'])) {
            $settings = $_POST['settings'];
        }

        if (empty($settings) || !is_array($settings)) {
            wp_send_json_error('Invalid settings');
        }

        // پاکسازی داده‌ها
        $sanitized = $this->sanitize_settings($settings);

        update_option($this->option_name, $sanitized);
        $this->settings = $sanitized;

        // پاک کردن transient پیش‌نمایش
        delete_transient('dst_builder_preview_' . get_current_user_id());

        wp_send_json_success(['message' => 'ذخیره شد!']);
    }

    /**
     * پیش‌نمایش زنده با AJAX
     * ذخیره تنظیمات موقت برای پیش‌نمایش
     */
    public function ajax_preview() {
        check_ajax_referer('dst_builder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        // خواندن از JSON برای حفظ ساختار nested
        $settings = [];
        if (isset($_POST['settings_json'])) {
            $settings = json_decode(stripslashes($_POST['settings_json']), true);
        } elseif (isset($_POST['settings'])) {
            $settings = $_POST['settings'];
        }

        if (empty($settings) || !is_array($settings)) {
            wp_send_json_error([
                'message' => 'Invalid settings',
                'has_json' => isset($_POST['settings_json']),
                'has_settings' => isset($_POST['settings'])
            ]);
        }

        // پاکسازی داده‌ها
        $preview_settings = $this->sanitize_settings($settings);

        // ذخیره موقت تنظیمات (2 دقیقه)
        $transient_key = 'dst_builder_preview_' . get_current_user_id();
        $saved = set_transient($transient_key, $preview_settings, 120);

        wp_send_json_success([
            'preview_url' => home_url('/?dst_builder_preview=1&t=' . time()),
            'message' => 'تنظیمات برای پیش‌نمایش ذخیره شد',
            'transient_saved' => $saved,
            'transient_key' => $transient_key,
            'header_bg' => $preview_settings['header']['settings']['bg_color'] ?? 'not set'
        ]);
    }

    /**
     * پاکسازی تنظیمات
     */
    private function sanitize_settings($settings) {
        // بازگشتی پاکسازی آرایه
        if (is_array($settings)) {
            foreach ($settings as $key => $value) {
                if (is_array($value)) {
                    $settings[$key] = $this->sanitize_settings($value);
                } else {
                    $settings[$key] = wp_kses_post($value);
                }
            }
        }
        return $settings;
    }

    /**
     * صفحه ادمین
     */
    public function render_admin_page() {
        ?>
        <div class="wrap dst-builder-wrap">
            <div class="builder-header">
                <div class="builder-header-right">
                    <h1>
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/></svg>
                        سازنده هدر و فوتر
                    </h1>
                </div>
                <div class="builder-header-center">
                    <div class="builder-tabs">
                        <button type="button" class="builder-tab active" data-tab="header">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/></svg>
                            هدر
                        </button>
                        <button type="button" class="builder-tab" data-tab="footer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="7" x="3" y="14" rx="1"/><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/></svg>
                            فوتر
                        </button>
                    </div>
                </div>
                <div class="builder-header-left">
                    <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="builder-btn outline">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        مشاهده سایت
                    </a>
                    <button type="button" class="builder-btn primary" id="save-builder">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span>ذخیره تغییرات</span>
                    </button>
                </div>
            </div>

            <div class="builder-main">
                <!-- پنل چپ: المان‌ها -->
                <div class="builder-sidebar">
                    <!-- تنظیمات کلی هدر/فوتر -->
                    <div class="global-settings-panel accordion-panel" id="global-settings-panel">
                        <div class="panel-header accordion-header" data-target="global-settings-content">
                            <h3>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                <span id="global-settings-title">تنظیمات هدر</span>
                            </h3>
                            <svg class="accordion-arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="global-settings-content accordion-content" id="global-settings-content">
                            <!-- تنظیمات هدر -->
                            <div class="global-settings-group" id="header-global-settings">
                                <div class="setting-row">
                                    <label class="setting-toggle">
                                        <input type="checkbox" id="header-enabled" checked>
                                        <span class="toggle-slider"></span>
                                        <span class="toggle-label">فعال بودن هدر</span>
                                    </label>
                                </div>

                                <div class="setting-row">
                                    <label>عرض محتوا</label>
                                    <div class="container-width-options">
                                        <button type="button" class="width-option" data-width="boxed" data-target="header">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/></svg>
                                            <span>جعبه‌ای</span>
                                        </button>
                                        <button type="button" class="width-option active" data-width="contained" data-target="header">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/></svg>
                                            <span>محدود</span>
                                        </button>
                                        <button type="button" class="width-option" data-width="full" data-target="header">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="8" width="22" height="8"/></svg>
                                            <span>تمام عرض</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="setting-row container-width-value" id="header-container-width-row">
                                    <label>عرض کانتینر (px)</label>
                                    <input type="number" id="header-container-width" value="1200" min="960" max="1920" step="10">
                                </div>

                                <div class="setting-row">
                                    <label>رنگ پس‌زمینه</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" id="header-bg-color" value="#ffffff">
                                        <input type="text" id="header-bg-color-text" value="#ffffff">
                                    </div>
                                </div>

                                <div class="setting-row">
                                    <label class="setting-toggle">
                                        <input type="checkbox" id="header-sticky">
                                        <span class="toggle-slider"></span>
                                        <span class="toggle-label">هدر چسبان (Sticky)</span>
                                    </label>
                                </div>

                                <h4 class="settings-section-title">سایه (Box Shadow)</h4>
                                <div class="setting-row">
                                    <label class="setting-toggle">
                                        <input type="checkbox" id="header-shadow-enabled" checked>
                                        <span class="toggle-slider"></span>
                                        <span class="toggle-label">فعال</span>
                                    </label>
                                </div>
                                <div class="shadow-settings" id="header-shadow-settings">
                                    <div class="setting-row">
                                        <label>رنگ سایه</label>
                                        <div class="color-input-wrapper">
                                            <input type="color" id="header-shadow-color" value="#000000">
                                            <input type="text" id="header-shadow-color-text" value="rgba(0,0,0,0.08)">
                                        </div>
                                    </div>
                                    <div class="setting-row-grid">
                                        <div class="setting-mini">
                                            <label>X</label>
                                            <input type="number" id="header-shadow-x" value="0" min="-50" max="50">
                                        </div>
                                        <div class="setting-mini">
                                            <label>Y</label>
                                            <input type="number" id="header-shadow-y" value="2" min="-50" max="50">
                                        </div>
                                        <div class="setting-mini">
                                            <label>Blur</label>
                                            <input type="number" id="header-shadow-blur" value="10" min="0" max="100">
                                        </div>
                                        <div class="setting-mini">
                                            <label>Spread</label>
                                            <input type="number" id="header-shadow-spread" value="0" min="-50" max="50">
                                        </div>
                                    </div>
                                </div>

                                <h4 class="settings-section-title">خط پایین (Border)</h4>
                                <div class="setting-row">
                                    <label class="setting-toggle">
                                        <input type="checkbox" id="header-border-enabled">
                                        <span class="toggle-slider"></span>
                                        <span class="toggle-label">فعال</span>
                                    </label>
                                </div>
                                <div class="border-settings hidden" id="header-border-settings">
                                    <div class="setting-row">
                                        <label>رنگ خط</label>
                                        <div class="color-input-wrapper">
                                            <input type="color" id="header-border-color" value="#e5e7eb">
                                            <input type="text" id="header-border-color-text" value="#e5e7eb">
                                        </div>
                                    </div>
                                    <div class="setting-row-grid cols-2">
                                        <div class="setting-mini">
                                            <label>ضخامت (px)</label>
                                            <input type="number" id="header-border-width" value="1" min="1" max="10">
                                        </div>
                                        <div class="setting-mini">
                                            <label>استایل</label>
                                            <select id="header-border-style">
                                                <option value="solid">خط</option>
                                                <option value="dashed">خط‌چین</option>
                                                <option value="dotted">نقطه‌چین</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- تنظیمات فوتر -->
                            <div class="global-settings-group hidden" id="footer-global-settings">
                                <div class="setting-row">
                                    <label class="setting-toggle">
                                        <input type="checkbox" id="footer-enabled" checked>
                                        <span class="toggle-slider"></span>
                                        <span class="toggle-label">فعال بودن فوتر</span>
                                    </label>
                                </div>

                                <div class="setting-row">
                                    <label>عرض محتوا</label>
                                    <div class="container-width-options">
                                        <button type="button" class="width-option" data-width="boxed" data-target="footer">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/></svg>
                                            <span>جعبه‌ای</span>
                                        </button>
                                        <button type="button" class="width-option active" data-width="contained" data-target="footer">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/></svg>
                                            <span>محدود</span>
                                        </button>
                                        <button type="button" class="width-option" data-width="full" data-target="footer">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="8" width="22" height="8"/></svg>
                                            <span>تمام عرض</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="setting-row container-width-value" id="footer-container-width-row">
                                    <label>عرض کانتینر (px)</label>
                                    <input type="number" id="footer-container-width" value="1200" min="960" max="1920" step="10">
                                </div>

                                <div class="setting-row">
                                    <label>رنگ پس‌زمینه</label>
                                    <div class="color-input-wrapper">
                                        <input type="color" id="footer-bg-color" value="#1f2937">
                                        <input type="text" id="footer-bg-color-text" value="#1f2937">
                                    </div>
                                </div>

                                <h4 class="settings-section-title">خط بالا (Border)</h4>
                                <div class="setting-row">
                                    <label class="setting-toggle">
                                        <input type="checkbox" id="footer-border-enabled">
                                        <span class="toggle-slider"></span>
                                        <span class="toggle-label">فعال</span>
                                    </label>
                                </div>
                                <div class="border-settings hidden" id="footer-border-settings">
                                    <div class="setting-row">
                                        <label>رنگ خط</label>
                                        <div class="color-input-wrapper">
                                            <input type="color" id="footer-border-color" value="#374151">
                                            <input type="text" id="footer-border-color-text" value="#374151">
                                        </div>
                                    </div>
                                    <div class="setting-row-grid cols-2">
                                        <div class="setting-mini">
                                            <label>ضخامت (px)</label>
                                            <input type="number" id="footer-border-width" value="1" min="1" max="10">
                                        </div>
                                        <div class="setting-mini">
                                            <label>استایل</label>
                                            <select id="footer-border-style">
                                                <option value="solid">خط</option>
                                                <option value="dashed">خط‌چین</option>
                                                <option value="dotted">نقطه‌چین</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="elements-panel accordion-panel">
                        <div class="panel-header accordion-header" data-target="elements-list-content">
                            <h3>المان‌ها</h3>
                            <svg class="accordion-arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="accordion-content" id="elements-list-content">
                            <input type="text" id="element-search" placeholder="جستجو..." class="element-search-input">
                            <div class="elements-list">
                                <?php $this->render_elements_list(); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- وسط: کانوس ساخت -->
                <div class="builder-canvas-wrapper">
                    <div class="canvas-toolbar">
                        <div class="device-switcher">
                            <button type="button" class="device-btn active" data-device="desktop" title="دسکتاپ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                            </button>
                            <button type="button" class="device-btn" data-device="tablet" title="تبلت">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="16" height="20" x="4" y="2" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                            </button>
                            <button type="button" class="device-btn" data-device="mobile" title="موبایل">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="20" x="5" y="2" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                            </button>
                        </div>
                        <button type="button" class="refresh-preview" title="بارگذاری مجدد">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                        </button>
                    </div>

                    <!-- کانوس هدر -->
                    <div class="builder-canvas" id="header-canvas" data-type="header">
                        <div class="canvas-label">هدر</div>
                        <div class="canvas-content">
                            <div class="rows-container" id="header-rows">
                                <!-- ردیف‌ها اینجا رندر می‌شوند -->
                            </div>
                            <button type="button" class="add-row-btn" data-type="header">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                افزودن ردیف
                            </button>
                        </div>
                    </div>

                    <!-- پیش‌نمایش -->
                    <div class="builder-preview">
                        <div class="preview-frame-wrapper" data-device="desktop">
                            <iframe id="preview-frame" src="<?php echo esc_url(home_url('/?dst_builder_preview=1')); ?>"></iframe>
                        </div>
                    </div>

                    <!-- کانوس فوتر -->
                    <div class="builder-canvas hidden" id="footer-canvas" data-type="footer">
                        <div class="canvas-label">فوتر</div>
                        <div class="canvas-content">
                            <div class="rows-container" id="footer-rows">
                                <!-- ردیف‌ها اینجا رندر می‌شوند -->
                            </div>
                            <button type="button" class="add-row-btn" data-type="footer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                افزودن ردیف
                            </button>
                        </div>
                    </div>
                </div>

                <!-- پنل راست: تنظیمات -->
                <div class="builder-settings-panel">
                    <div class="settings-panel-header">
                        <h3>تنظیمات</h3>
                        <button type="button" class="close-settings">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <div class="settings-panel-body" id="settings-content">
                        <div class="empty-settings">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.26.604.852.997 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            <p>یک المان یا ردیف را انتخاب کنید</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal انتخاب Layout -->
        <div class="builder-modal" id="layout-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>انتخاب چیدمان ستون‌ها</h3>
                    <button type="button" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="layout-options">
                        <button type="button" class="layout-option" data-layout="1" data-columns="1">
                            <div class="layout-preview"><span></span></div>
                            <span>1 ستون</span>
                        </button>
                        <button type="button" class="layout-option" data-layout="1-1" data-columns="2">
                            <div class="layout-preview"><span></span><span></span></div>
                            <span>2 ستون</span>
                        </button>
                        <button type="button" class="layout-option" data-layout="1-2" data-columns="2">
                            <div class="layout-preview"><span style="flex:1"></span><span style="flex:2"></span></div>
                            <span>1/3 - 2/3</span>
                        </button>
                        <button type="button" class="layout-option" data-layout="2-1" data-columns="2">
                            <div class="layout-preview"><span style="flex:2"></span><span style="flex:1"></span></div>
                            <span>2/3 - 1/3</span>
                        </button>
                        <button type="button" class="layout-option" data-layout="1-1-1" data-columns="3">
                            <div class="layout-preview"><span></span><span></span><span></span></div>
                            <span>3 ستون</span>
                        </button>
                        <button type="button" class="layout-option" data-layout="1-2-1" data-columns="3">
                            <div class="layout-preview"><span style="flex:1"></span><span style="flex:2"></span><span style="flex:1"></span></div>
                            <span>1/4 - 2/4 - 1/4</span>
                        </button>
                        <button type="button" class="layout-option" data-layout="1-1-1-1" data-columns="4">
                            <div class="layout-preview"><span></span><span></span><span></span><span></span></div>
                            <span>4 ستون</span>
                        </button>
                        <button type="button" class="layout-option" data-layout="1-1-1-1-1" data-columns="5">
                            <div class="layout-preview"><span></span><span></span><span></span><span></span><span></span></div>
                            <span>5 ستون</span>
                        </button>
                        <button type="button" class="layout-option" data-layout="1-1-1-1-1-1" data-columns="6">
                            <div class="layout-preview"><span></span><span></span><span></span><span></span><span></span><span></span></div>
                            <span>6 ستون</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    /**
     * رندر لیست المان‌ها
     */
    private function render_elements_list() {
        $categories = [
            'basic' => 'پایه',
            'woocommerce' => 'ووکامرس',
            'layout' => 'چیدمان',
            'advanced' => 'پیشرفته',
        ];

        foreach ($categories as $cat_key => $cat_label) {
            $cat_elements = array_filter($this->elements, function($el) use ($cat_key) {
                return ($el['category'] ?? 'basic') === $cat_key;
            });

            if (empty($cat_elements)) continue;

            echo '<div class="element-category">';
            echo '<h4>' . esc_html($cat_label) . '</h4>';
            echo '<div class="element-items">';

            foreach ($cat_elements as $key => $element) {
                ?>
                <div class="element-item" draggable="true" data-element="<?php echo esc_attr($key); ?>">
                    <div class="element-icon">
                        <?php echo $this->get_icon_svg($element['icon']); ?>
                    </div>
                    <span><?php echo esc_html($element['title']); ?></span>
                </div>
                <?php
            }

            echo '</div></div>';
        }
    }

    /**
     * دریافت آیکون SVG
     */
    private function get_icon_svg($icon) {
        $icons = [
            'image' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>',
            'menu' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="18" x2="20" y2="18"/></svg>',
            'search' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
            'square' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/></svg>',
            'type' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>',
            'code' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
            'share-2' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',
            'shopping-cart' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>',
            'user' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
            'heart' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
            'phone' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
            'copyright' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9.354a4 4 0 1 0 0 5.292"/></svg>',
            'minus' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/></svg>',
            'move-vertical' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="8 18 12 22 16 18"/><polyline points="8 6 12 2 16 6"/><line x1="12" y1="2" x2="12" y2="22"/></svg>',
        ];

        return $icons[$icon] ?? $icons['square'];
    }

    /**
     * رندر هدر
     */
    public function render_header() {
        if (!$this->settings['header']['enabled']) {
            return;
        }

        $header = $this->settings['header'];
        $settings = $header['settings'] ?? [];

        // Debug: output settings to console
        echo '<!-- DEBUG Header Settings: ' . esc_html(wp_json_encode($settings)) . ' -->';

        $classes = ['dst-builder-header'];

        // Width type - support both old (full_width) and new (width_type) keys
        $width_type = $settings['width_type'] ?? 'contained';
        // Backward compatibility: full_width: "true" => width_type: "full"
        if (isset($settings['full_width']) && $settings['full_width'] === 'true') {
            $width_type = 'full';
        }
        if ($width_type === 'full') $classes[] = 'full-width';
        if ($width_type === 'boxed') $classes[] = 'boxed-width';

        if (!empty($settings['sticky'])) $classes[] = 'is-sticky';

        // Build inline styles
        $styles = [];

        // Custom container width
        if ($width_type === 'contained' && !empty($settings['container_width'])) {
            $styles[] = '--container-width:' . intval($settings['container_width']) . 'px';
        }

        // Background color - always apply if set
        if (!empty($settings['bg_color'])) {
            $styles[] = 'background-color:' . esc_attr($settings['bg_color']) . ' !important';
        } else {
            // Default background if not set
            $styles[] = 'background-color:#ffffff !important';
        }

        // Box Shadow - support both old (shadow) and new (shadow_enabled) keys
        $has_shadow = !empty($settings['shadow_enabled']) ||
                      (isset($settings['shadow']) && ($settings['shadow'] === true || $settings['shadow'] === 'true'));
        if ($has_shadow) {
            $shadow_color = $settings['shadow_color'] ?? 'rgba(0,0,0,0.08)';
            $shadow_x = intval($settings['shadow_x'] ?? 0);
            $shadow_y = intval($settings['shadow_y'] ?? 2);
            $shadow_blur = intval($settings['shadow_blur'] ?? 10);
            $shadow_spread = intval($settings['shadow_spread'] ?? 0);
            $styles[] = 'box-shadow:' . $shadow_x . 'px ' . $shadow_y . 'px ' . $shadow_blur . 'px ' . $shadow_spread . 'px ' . esc_attr($shadow_color);
        }

        // Border - support both old (border_bottom) and new (border_enabled) keys
        $has_border = !empty($settings['border_enabled']) ||
                      (isset($settings['border_bottom']) && ($settings['border_bottom'] === true || $settings['border_bottom'] === 'true'));
        if ($has_border) {
            $border_color = $settings['border_color'] ?? '#e5e7eb';
            $border_width = intval($settings['border_width'] ?? 1);
            $border_style = $settings['border_style'] ?? 'solid';
            $styles[] = 'border-bottom:' . $border_width . 'px ' . esc_attr($border_style) . ' ' . esc_attr($border_color);
        }

        $style_attr = !empty($styles) ? ' style="' . implode(';', $styles) . '"' : '';

        echo '<header class="' . esc_attr(implode(' ', $classes)) . '"' . $style_attr . '>';

        foreach ($header['rows'] as $row) {
            $this->render_row($row, 'header');
        }

        echo '</header>';
    }

    /**
     * رندر فوتر
     */
    public function render_footer() {
        if (!$this->settings['footer']['enabled']) {
            return;
        }

        $footer = $this->settings['footer'];
        $settings = $footer['settings'] ?? [];
        $classes = ['dst-builder-footer'];

        // Width type - support both old (full_width) and new (width_type) keys
        $width_type = $settings['width_type'] ?? 'contained';
        // Backward compatibility: full_width: "true" => width_type: "full"
        if (isset($settings['full_width']) && $settings['full_width'] === 'true') {
            $width_type = 'full';
        }
        if ($width_type === 'full') $classes[] = 'full-width';
        if ($width_type === 'boxed') $classes[] = 'boxed-width';

        // Build inline styles
        $styles = [];

        // Custom container width
        if ($width_type === 'contained' && !empty($settings['container_width'])) {
            $styles[] = '--container-width:' . intval($settings['container_width']) . 'px';
        }

        // Background color - always apply if set
        if (!empty($settings['bg_color'])) {
            $styles[] = 'background-color:' . esc_attr($settings['bg_color']) . ' !important';
        } else {
            // Default background for footer
            $styles[] = 'background-color:#1f2937 !important';
        }

        // Border - support both old (border_top) and new (border_enabled) keys
        $has_border = !empty($settings['border_enabled']) ||
                      (isset($settings['border_top']) && ($settings['border_top'] === true || $settings['border_top'] === 'true'));
        if ($has_border) {
            $border_color = $settings['border_color'] ?? '#374151';
            $border_width = intval($settings['border_width'] ?? 1);
            $border_style = $settings['border_style'] ?? 'solid';
            $styles[] = 'border-top:' . $border_width . 'px ' . esc_attr($border_style) . ' ' . esc_attr($border_color);
        }

        $style_attr = !empty($styles) ? ' style="' . implode(';', $styles) . '"' : '';

        echo '<footer class="' . esc_attr(implode(' ', $classes)) . '"' . $style_attr . '>';

        foreach ($footer['rows'] as $row) {
            $this->render_row($row, 'footer');
        }

        echo '</footer>';
    }

    /**
     * رندر ردیف
     */
    private function render_row($row, $type) {
        $settings = $row['settings'] ?? [];
        $style = '';

        if (!empty($settings['bg_color'])) $style .= 'background-color:' . $settings['bg_color'] . ';';
        if (!empty($settings['text_color'])) $style .= 'color:' . $settings['text_color'] . ';';
        if (!empty($settings['padding'])) $style .= 'padding:' . $settings['padding'] . ';';

        $classes = ['builder-row', 'layout-' . ($row['layout'] ?? '1')];
        if (!empty($settings['sticky'])) $classes[] = 'is-sticky';

        echo '<div class="' . esc_attr(implode(' ', $classes)) . '" style="' . esc_attr($style) . '">';
        echo '<div class="builder-container">';
        echo '<div class="builder-row-inner columns-' . intval($row['columns'] ?? 1) . '">';

        // رندر ستون‌ها
        $col_index = 1;
        foreach ($row['elements'] as $col_key => $elements) {
            echo '<div class="builder-column col-' . $col_index . '">';
            foreach ($elements as $element) {
                $this->render_element($element);
            }
            echo '</div>';
            $col_index++;
        }

        echo '</div></div></div>';
    }

    /**
     * رندر المان
     */
    private function render_element($element) {
        $type = $element['type'] ?? '';
        $settings = $element['settings'] ?? [];

        if (empty($type) || !isset($this->elements[$type])) {
            return;
        }

        // کلاس‌های نمایش ریسپانسیو
        $classes = ['builder-element', 'element-' . $type];
        if (!empty($settings['hide_desktop'])) $classes[] = 'hide-desktop';
        if (!empty($settings['hide_tablet'])) $classes[] = 'hide-tablet';
        if (!empty($settings['hide_mobile'])) $classes[] = 'hide-mobile';

        echo '<div class="' . esc_attr(implode(' ', $classes)) . '">';

        switch ($type) {
            case 'logo':
                $this->render_logo($settings);
                break;
            case 'menu':
                $this->render_menu($settings);
                break;
            case 'search':
                $this->render_search($settings);
                break;
            case 'button':
                $this->render_button($settings);
                break;
            case 'text':
                $this->render_text($settings);
                break;
            case 'html':
                echo wp_kses_post($settings['content'] ?? '');
                break;
            case 'social':
                $this->render_social($settings);
                break;
            case 'cart':
                $this->render_cart($settings);
                break;
            case 'account':
                $this->render_account($settings);
                break;
            case 'contact_info':
                $this->render_contact_info($settings);
                break;
            case 'copyright':
                $this->render_copyright($settings);
                break;
            case 'divider':
                $this->render_divider($settings);
                break;
            case 'spacer':
                echo '<div style="height:' . intval($settings['height'] ?? 20) . 'px"></div>';
                break;
            case 'image':
                $this->render_image($settings);
                break;
        }

        echo '</div>';
    }

    // متدهای رندر المان‌ها
    private function render_logo($settings) {
        $max_height = $settings['max_height'] ?? 50;
        $max_width = $settings['max_width'] ?? 200;
        $custom_logo = $settings['custom_logo'] ?? '';
        $show_site_title = !empty($settings['show_site_title']);
        $title_color = $settings['title_color'] ?? '#1e293b';
        $title_size = $settings['title_size'] ?? 24;

        $logo_style = sprintf('max-height:%dpx;max-width:%dpx;', intval($max_height), intval($max_width));

        echo '<a href="' . esc_url(home_url('/')) . '" class="builder-logo" style="' . $logo_style . '">';
        if ($custom_logo) {
            echo '<img src="' . esc_url($custom_logo) . '" alt="' . esc_attr(get_bloginfo('name')) . '" style="max-height:' . intval($max_height) . 'px;max-width:' . intval($max_width) . 'px;">';
        } elseif (has_custom_logo()) {
            the_custom_logo();
        }

        if ($show_site_title || (!$custom_logo && !has_custom_logo())) {
            $title_style = sprintf('font-size:%dpx;color:%s;', intval($title_size), esc_attr($title_color));
            echo '<span class="site-title" style="' . $title_style . '">' . esc_html(get_bloginfo('name')) . '</span>';
        }
        echo '</a>';
    }

    private function render_menu($settings) {
        $menu_value = $settings['menu'] ?? '';
        $title = $settings['title'] ?? '';
        $style = $settings['style'] ?? 'horizontal';

        // Custom styles
        $font_size = $settings['font_size'] ?? 14;
        $font_weight = $settings['font_weight'] ?? '500';
        $text_color = $settings['text_color'] ?? '#333333';
        $hover_color = $settings['hover_color'] ?? '#2563eb';
        $gap = $settings['gap'] ?? 25;

        // CSS variables for styling
        $css_vars = sprintf(
            '--menu-font-size:%dpx;--menu-font-weight:%s;--menu-color:%s;--menu-hover:%s;--menu-gap:%dpx;',
            intval($font_size),
            esc_attr($font_weight),
            esc_attr($text_color),
            esc_attr($hover_color),
            intval($gap)
        );

        if ($title) {
            echo '<h4 class="menu-title">' . esc_html($title) . '</h4>';
        }

        if (empty($menu_value)) {
            // اگه منو انتخاب نشده، منوی primary رو نشون بده
            if (has_nav_menu('primary')) {
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container' => 'nav',
                    'container_class' => 'builder-nav style-' . $style,
                    'container_attr' => ['style' => $css_vars],
                    'fallback_cb' => false,
                ]);
            } else {
                // Fallback: نمایش پیام یا منوی پیش‌فرض
                echo '<nav class="builder-nav style-' . esc_attr($style) . '" style="' . esc_attr($css_vars) . '">';
                echo '<ul><li><a href="' . esc_url(home_url('/')) . '">خانه</a></li></ul>';
                echo '</nav>';
            }
            return;
        }

        $menu_args = [
            'container' => 'nav',
            'container_class' => 'builder-nav style-' . $style,
            'container_attr' => ['style' => $css_vars],
            'fallback_cb' => false,
            'echo' => true,
        ];

        // چک کنید که location هست یا menu
        if (strpos($menu_value, 'location:') === 0) {
            // Theme location
            $location = str_replace('location:', '', $menu_value);
            $menu_args['theme_location'] = $location;
        } elseif (strpos($menu_value, 'menu:') === 0) {
            // Menu by ID
            $menu_id = str_replace('menu:', '', $menu_value);
            $menu_args['menu'] = intval($menu_id);
        } else {
            // فرمت قدیمی - اول به عنوان slug، بعد name، بعد ID امتحان کن
            $menu = wp_get_nav_menu_object($menu_value);
            if ($menu) {
                $menu_args['menu'] = $menu->term_id;
            } else {
                // شاید theme location باشه
                if (has_nav_menu($menu_value)) {
                    $menu_args['theme_location'] = $menu_value;
                } else {
                    // آخرین تلاش - مستقیم بده
                    $menu_args['menu'] = $menu_value;
                }
            }
        }

        wp_nav_menu($menu_args);
    }

    private function render_search($settings) {
        $style = $settings['style'] ?? 'icon';
        $placeholder = $settings['placeholder'] ?? 'جستجو...';

        echo '<div class="builder-search style-' . esc_attr($style) . '">';
        if ($style === 'icon') {
            echo '<button type="button" class="search-toggle"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></button>';
        }
        echo '<form role="search" method="get" action="' . esc_url(home_url('/')) . '">';
        echo '<input type="search" name="s" placeholder="' . esc_attr($placeholder) . '">';
        echo '<button type="submit"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></button>';
        echo '</form></div>';
    }

    private function render_button($settings) {
        $text = $settings['text'] ?? 'کلیک کنید';
        $url = $settings['url'] ?? '#';
        $target = !empty($settings['target']) ? ' target="_blank"' : '';
        $style = $settings['style'] ?? 'primary';

        // Custom styles
        $inline_style = '';
        if ($style === 'custom') {
            $bg_color = $settings['bg_color'] ?? '#2563eb';
            $text_color = $settings['text_color'] ?? '#ffffff';
            $border_radius = $settings['border_radius'] ?? 8;
            $font_size = $settings['font_size'] ?? 14;
            $padding_x = $settings['padding_x'] ?? 24;
            $padding_y = $settings['padding_y'] ?? 12;

            $inline_style = sprintf(
                'background:%s;color:%s;border-radius:%dpx;font-size:%dpx;padding:%dpx %dpx;',
                esc_attr($bg_color),
                esc_attr($text_color),
                intval($border_radius),
                intval($font_size),
                intval($padding_y),
                intval($padding_x)
            );
        }

        $style_attr = $inline_style ? ' style="' . $inline_style . '"' : '';
        echo '<a href="' . esc_url($url) . '" class="builder-button style-' . esc_attr($style) . '"' . $target . $style_attr . '>' . esc_html($text) . '</a>';
    }

    private function render_text($settings) {
        $content = $settings['content'] ?? '';
        $tag = $settings['tag'] ?? 'p';
        $allowed_tags = ['p', 'span', 'div', 'h4', 'h5'];
        $tag = in_array($tag, $allowed_tags) ? $tag : 'p';

        echo '<' . $tag . ' class="builder-text">' . wp_kses_post($content) . '</' . $tag . '>';
    }

    private function render_social($settings) {
        $style = $settings['style'] ?? 'icon';
        $size = $settings['size'] ?? 'md';
        $networks = ['instagram', 'telegram', 'whatsapp', 'twitter', 'linkedin', 'youtube'];

        echo '<div class="builder-social style-' . esc_attr($style) . ' size-' . esc_attr($size) . '">';
        foreach ($networks as $network) {
            if (!empty($settings[$network])) {
                echo '<a href="' . esc_url($settings[$network]) . '" target="_blank" rel="noopener" class="social-' . $network . '">';
                echo $this->get_social_icon($network);
                if ($style !== 'icon') {
                    echo '<span>' . ucfirst($network) . '</span>';
                }
                echo '</a>';
            }
        }
        echo '</div>';
    }

    private function get_social_icon($network) {
        $icons = [
            'instagram' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
            'telegram' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
            'whatsapp' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
            'twitter' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
            'linkedin' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
            'youtube' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
        ];
        return $icons[$network] ?? '';
    }

    private function render_cart($settings) {
        if (!class_exists('WooCommerce')) {
            echo '<span class="woo-notice">ووکامرس فعال نیست</span>';
            return;
        }

        $count = WC()->cart->get_cart_contents_count();
        $style = $settings['style'] ?? 'icon';

        echo '<a href="' . esc_url(wc_get_cart_url()) . '" class="builder-cart style-' . esc_attr($style) . '">';
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>';
        if (!empty($settings['show_count']) && $count > 0) {
            echo '<span class="cart-count">' . $count . '</span>';
        }
        if (!empty($settings['show_total'])) {
            echo '<span class="cart-total">' . WC()->cart->get_cart_total() . '</span>';
        }
        echo '</a>';
    }

    private function render_account($settings) {
        $logged_in_text = $settings['logged_in_text'] ?? 'حساب من';
        $logged_out_text = $settings['logged_out_text'] ?? 'ورود / ثبت‌نام';

        $url = class_exists('WooCommerce') ? wc_get_account_endpoint_url('dashboard') : wp_login_url();
        $text = is_user_logged_in() ? $logged_in_text : $logged_out_text;

        echo '<a href="' . esc_url($url) . '" class="builder-account">';
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
        echo '<span>' . esc_html($text) . '</span>';
        echo '</a>';
    }

    private function render_contact_info($settings) {
        $title = $settings['title'] ?? '';

        if ($title) {
            echo '<h4 class="contact-title">' . esc_html($title) . '</h4>';
        }

        echo '<div class="builder-contact-info">';
        if (!empty($settings['phone'])) {
            echo '<div class="contact-item"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg><a href="tel:' . esc_attr($settings['phone']) . '">' . esc_html($settings['phone']) . '</a></div>';
        }
        if (!empty($settings['email'])) {
            echo '<div class="contact-item"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg><a href="mailto:' . esc_attr($settings['email']) . '">' . esc_html($settings['email']) . '</a></div>';
        }
        if (!empty($settings['address'])) {
            echo '<div class="contact-item"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg><span>' . esc_html($settings['address']) . '</span></div>';
        }
        echo '</div>';
    }

    private function render_copyright($settings) {
        $text = $settings['text'] ?? '© {year} {site_name}. تمامی حقوق محفوظ است.';
        $text = str_replace('{year}', date('Y'), $text);
        $text = str_replace('{site_name}', get_bloginfo('name'), $text);

        echo '<div class="builder-copyright">' . wp_kses_post($text) . '</div>';
    }

    private function render_divider($settings) {
        $style = $settings['style'] ?? 'solid';
        $color = $settings['color'] ?? '#e5e7eb';
        $width = $settings['width'] ?? '100%';

        echo '<hr class="builder-divider" style="border-style:' . esc_attr($style) . ';border-color:' . esc_attr($color) . ';width:' . esc_attr($width) . '">';
    }

    private function render_image($settings) {
        $image = $settings['image'] ?? '';
        $url = $settings['url'] ?? '';
        $alt = $settings['alt'] ?? '';
        $max_width = $settings['max_width'] ?? '100%';

        if (!$image) return;

        $img = '<img src="' . esc_url($image) . '" alt="' . esc_attr($alt) . '" style="max-width:' . esc_attr($max_width) . '">';

        if ($url) {
            echo '<a href="' . esc_url($url) . '" class="builder-image">' . $img . '</a>';
        } else {
            echo '<div class="builder-image">' . $img . '</div>';
        }
    }
}

// راه‌اندازی
global $dst_hf_builder;
$dst_hf_builder = new DST_Header_Footer_Builder();

/**
 * توابع کمکی
 */
function dst_builder_header() {
    global $dst_hf_builder;
    if ($dst_hf_builder) {
        $dst_hf_builder->render_header();
    }
}

function dst_builder_footer() {
    global $dst_hf_builder;
    if ($dst_hf_builder) {
        $dst_hf_builder->render_footer();
    }
}
