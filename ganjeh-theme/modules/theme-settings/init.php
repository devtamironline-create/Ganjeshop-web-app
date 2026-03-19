<?php
/**
 * ماژول تنظیمات قالب
 * Theme Settings Module
 * 
 * تنظیمات اصلی قالب شامل:
 * - لوگو و فاوآیکون
 * - رنگ‌بندی
 * - فونت‌ها
 * - تنظیمات عمومی
 * - شبکه‌های اجتماعی
 * - کدهای سفارشی
 * 
 * @package Developer_Starter
 * @subpackage Modules/Theme_Settings
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

class DST_Theme_Settings {
    
    /**
     * مسیر ماژول
     */
    private $module_path;
    
    /**
     * URL ماژول
     */
    private $module_url;
    
    /**
     * نام option در دیتابیس
     */
    private $option_name = 'dst_theme_settings';
    
    /**
     * تنظیمات فعلی
     */
    private $settings;
    
    /**
     * تب‌های تنظیمات
     */
    private $tabs = [];
    
    /**
     * سازنده
     */
    public function __construct() {
        $module = dst_get_module('theme-settings');
        if (!$module) {
            return;
        }
        
        $this->module_path = $module['path'];
        $this->module_url  = $module['url'];
        $this->settings    = get_option($this->option_name, $this->get_default_settings());
        
        // تعریف تب‌ها
        $this->define_tabs();
        
        // هوک‌ها
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('admin_init', [$this, 'handle_save_settings']);
        add_action('wp_head', [$this, 'output_custom_css'], 100);
        add_action('wp_head', [$this, 'output_head_scripts'], 999);
        add_action('wp_footer', [$this, 'output_footer_scripts'], 999);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_fonts']);
    }
    
    /**
     * تعریف تب‌ها و فیلدها
     */
    private function define_tabs() {
        $this->tabs = [
            'general' => [
                'title' => 'عمومی',
                'icon' => 'dashicons-admin-settings',
                'fields' => [
                    'site_logo' => [
                        'type' => 'image',
                        'label' => 'لوگوی سایت',
                        'description' => 'لوگوی اصلی سایت (پیشنهاد: PNG شفاف)',
                    ],
                    'site_logo_light' => [
                        'type' => 'image',
                        'label' => 'لوگوی روشن',
                        'description' => 'لوگو برای پس‌زمینه تیره',
                    ],
                    'favicon' => [
                        'type' => 'image',
                        'label' => 'فاوآیکون',
                        'description' => 'آیکون سایت (پیشنهاد: 32x32 یا 64x64 پیکسل)',
                    ],
                    'site_description' => [
                        'type' => 'textarea',
                        'label' => 'توضیحات سایت',
                        'description' => 'توضیح کوتاه درباره سایت (برای فوتر)',
                    ],
                    'copyright_text' => [
                        'type' => 'text',
                        'label' => 'متن کپی‌رایت',
                        'default' => '© تمامی حقوق محفوظ است.',
                        'description' => 'متن کپی‌رایت در فوتر',
                    ],
                ],
            ],
            
            'colors' => [
                'title' => 'رنگ‌بندی',
                'icon' => 'dashicons-art',
                'fields' => [
                    'primary_color' => [
                        'type' => 'color',
                        'label' => 'رنگ اصلی',
                        'default' => '#3C50E0',
                        'description' => 'رنگ اصلی قالب (دکمه‌ها، لینک‌ها و ...)',
                    ],
                    'secondary_color' => [
                        'type' => 'color',
                        'label' => 'رنگ ثانویه',
                        'default' => '#10B981',
                        'description' => 'رنگ ثانویه قالب',
                    ],
                    'accent_color' => [
                        'type' => 'color',
                        'label' => 'رنگ تأکیدی',
                        'default' => '#F59E0B',
                        'description' => 'رنگ برای هایلایت و تأکید',
                    ],
                    'text_color' => [
                        'type' => 'color',
                        'label' => 'رنگ متن',
                        'default' => '#1e293b',
                        'description' => 'رنگ اصلی متن‌ها',
                    ],
                    'text_light_color' => [
                        'type' => 'color',
                        'label' => 'رنگ متن روشن',
                        'default' => '#64748b',
                        'description' => 'رنگ متن‌های فرعی',
                    ],
                    'background_color' => [
                        'type' => 'color',
                        'label' => 'رنگ پس‌زمینه',
                        'default' => '#ffffff',
                        'description' => 'رنگ پس‌زمینه اصلی',
                    ],
                    'header_bg_color' => [
                        'type' => 'color',
                        'label' => 'رنگ پس‌زمینه هدر',
                        'default' => '#ffffff',
                    ],
                    'header_text_color' => [
                        'type' => 'color',
                        'label' => 'رنگ متن هدر',
                        'default' => '#1e293b',
                    ],
                    'menu_text_color' => [
                        'type' => 'color',
                        'label' => 'رنگ آیتم‌های منو',
                        'default' => '#1e293b',
                    ],
                    'menu_hover_color' => [
                        'type' => 'color',
                        'label' => 'رنگ منو در هاور',
                        'default' => '#3C50E0',
                    ],
                    'menu_active_color' => [
                        'type' => 'color',
                        'label' => 'رنگ آیتم فعال منو',
                        'default' => '#3C50E0',
                    ],
                    'button_bg_color' => [
                        'type' => 'color',
                        'label' => 'رنگ پس‌زمینه دکمه',
                        'default' => '#3C50E0',
                    ],
                    'button_text_color' => [
                        'type' => 'color',
                        'label' => 'رنگ متن دکمه',
                        'default' => '#ffffff',
                    ],
                    'button_hover_bg_color' => [
                        'type' => 'color',
                        'label' => 'رنگ دکمه در هاور',
                        'default' => '#2D3FBD',
                    ],
                    'footer_bg_color' => [
                        'type' => 'color',
                        'label' => 'رنگ پس‌زمینه فوتر',
                        'default' => '#1e293b',
                    ],
                    'footer_text_color' => [
                        'type' => 'color',
                        'label' => 'رنگ متن فوتر',
                        'default' => '#e2e8f0',
                    ],
                    '_color_preview' => [
                        'type' => 'preview',
                        'label' => '',
                    ],
                ],
            ],
            
            'typography' => [
                'title' => 'فونت‌ها',
                'icon' => 'dashicons-editor-textcolor',
                'fields' => [
                    'body_font' => [
                        'type' => 'select',
                        'label' => 'فونت متن',
                        'default' => 'Vazirmatn',
                        'options' => [
                            'Vazirmatn' => 'وزیرمتن',
                            'IRANSans' => 'ایران سنس',
                            'Yekan' => 'یکان',
                            'Samim' => 'صمیم',
                            'Shabnam' => 'شبنم',
                            'Tahoma' => 'تاهوما',
                        ],
                    ],
                    'heading_font' => [
                        'type' => 'select',
                        'label' => 'فونت عناوین',
                        'default' => 'Vazirmatn',
                        'options' => [
                            'Vazirmatn' => 'وزیرمتن',
                            'IRANSans' => 'ایران سنس',
                            'Yekan' => 'یکان',
                            'Samim' => 'صمیم',
                            'Shabnam' => 'شبنم',
                            'Tahoma' => 'تاهوما',
                        ],
                    ],
                    'base_font_size' => [
                        'type' => 'number',
                        'label' => 'اندازه فونت پایه',
                        'default' => 16,
                        'min' => 12,
                        'max' => 24,
                        'description' => 'اندازه فونت پایه به پیکسل',
                    ],
                    'line_height' => [
                        'type' => 'select',
                        'label' => 'فاصله خطوط',
                        'default' => '1.7',
                        'options' => [
                            '1.4' => 'فشرده',
                            '1.5' => 'متوسط',
                            '1.7' => 'نرمال',
                            '1.9' => 'باز',
                            '2.0' => 'خیلی باز',
                        ],
                    ],
                ],
            ],
            
            'layout' => [
                'title' => 'چیدمان',
                'icon' => 'dashicons-layout',
                'fields' => [
                    'container_width' => [
                        'type' => 'number',
                        'label' => 'عرض محتوا',
                        'default' => 1280,
                        'min' => 960,
                        'max' => 1600,
                        'description' => 'عرض حداکثر محتوا به پیکسل',
                    ],
                    'sidebar_position' => [
                        'type' => 'select',
                        'label' => 'موقعیت سایدبار',
                        'default' => 'right',
                        'options' => [
                            'right' => 'راست',
                            'left' => 'چپ',
                            'none' => 'بدون سایدبار',
                        ],
                    ],
                    'content_padding' => [
                        'type' => 'number',
                        'label' => 'فاصله داخلی محتوا',
                        'default' => 30,
                        'min' => 0,
                        'max' => 100,
                        'description' => 'فاصله داخلی بخش‌ها به پیکسل',
                    ],
                    'border_radius' => [
                        'type' => 'number',
                        'label' => 'گردی گوشه‌ها',
                        'default' => 8,
                        'min' => 0,
                        'max' => 30,
                        'description' => 'میزان گردی گوشه‌ها به پیکسل',
                    ],
                ],
            ],
            
            'social' => [
                'title' => 'شبکه‌های اجتماعی',
                'icon' => 'dashicons-share',
                'fields' => [
                    'instagram' => [
                        'type' => 'url',
                        'label' => 'اینستاگرام',
                        'placeholder' => 'https://instagram.com/username',
                    ],
                    'telegram' => [
                        'type' => 'url',
                        'label' => 'تلگرام',
                        'placeholder' => 'https://t.me/username',
                    ],
                    'whatsapp' => [
                        'type' => 'text',
                        'label' => 'واتساپ',
                        'placeholder' => '989123456789',
                        'description' => 'شماره بدون + و بدون صفر اول',
                    ],
                    'twitter' => [
                        'type' => 'url',
                        'label' => 'توییتر/X',
                        'placeholder' => 'https://twitter.com/username',
                    ],
                    'linkedin' => [
                        'type' => 'url',
                        'label' => 'لینکدین',
                        'placeholder' => 'https://linkedin.com/in/username',
                    ],
                    'youtube' => [
                        'type' => 'url',
                        'label' => 'یوتیوب',
                        'placeholder' => 'https://youtube.com/@channel',
                    ],
                    'aparat' => [
                        'type' => 'url',
                        'label' => 'آپارات',
                        'placeholder' => 'https://aparat.com/username',
                    ],
                    'github' => [
                        'type' => 'url',
                        'label' => 'گیت‌هاب',
                        'placeholder' => 'https://github.com/username',
                    ],
                ],
            ],
            
            'contact' => [
                'title' => 'اطلاعات تماس',
                'icon' => 'dashicons-phone',
                'fields' => [
                    'phone' => [
                        'type' => 'text',
                        'label' => 'شماره تلفن',
                        'placeholder' => '021-12345678',
                    ],
                    'mobile' => [
                        'type' => 'text',
                        'label' => 'شماره موبایل',
                        'placeholder' => '0912-123-4567',
                    ],
                    'email' => [
                        'type' => 'email',
                        'label' => 'ایمیل',
                        'placeholder' => 'info@example.com',
                    ],
                    'address' => [
                        'type' => 'textarea',
                        'label' => 'آدرس',
                        'rows' => 2,
                    ],
                    'working_hours' => [
                        'type' => 'text',
                        'label' => 'ساعت کاری',
                        'placeholder' => 'شنبه تا چهارشنبه: ۹ صبح - ۶ عصر',
                    ],
                    'map_lat' => [
                        'type' => 'text',
                        'label' => 'عرض جغرافیایی',
                        'placeholder' => '35.6892',
                    ],
                    'map_lng' => [
                        'type' => 'text',
                        'label' => 'طول جغرافیایی',
                        'placeholder' => '51.3890',
                    ],
                ],
            ],
            
            'cta' => [
                'title' => 'دکمه‌های CTA',
                'icon' => 'dashicons-megaphone',
                'fields' => [
                    'cta_style' => [
                        'type' => 'select',
                        'label' => 'استایل پیش‌فرض CTA',
                        'default' => 'solid',
                        'options' => [
                            'solid' => 'توپر (Solid)',
                            'outline' => 'خط‌دار (Outline)',
                            'gradient' => 'گرادیانت',
                            'rounded' => 'گرد (Pill)',
                            'shadow' => 'با سایه',
                        ],
                    ],
                    'cta_size' => [
                        'type' => 'select',
                        'label' => 'اندازه پیش‌فرض',
                        'default' => 'md',
                        'options' => [
                            'sm' => 'کوچک',
                            'md' => 'متوسط',
                            'lg' => 'بزرگ',
                            'xl' => 'خیلی بزرگ',
                        ],
                    ],
                    'cta_primary_text' => [
                        'type' => 'text',
                        'label' => 'متن CTA اصلی',
                        'default' => 'شروع کنید',
                        'description' => 'متن پیش‌فرض دکمه اصلی',
                    ],
                    'cta_primary_url' => [
                        'type' => 'url',
                        'label' => 'لینک CTA اصلی',
                        'placeholder' => '/contact',
                    ],
                    'cta_primary_icon' => [
                        'type' => 'select',
                        'label' => 'آیکون CTA اصلی',
                        'default' => 'none',
                        'options' => [
                            'none' => 'بدون آیکون',
                            'arrow-left' => 'فلش چپ',
                            'arrow-right' => 'فلش راست',
                            'phone' => 'تلفن',
                            'cart' => 'سبد خرید',
                            'download' => 'دانلود',
                            'play' => 'پخش',
                            'check' => 'تیک',
                        ],
                    ],
                    'cta_secondary_text' => [
                        'type' => 'text',
                        'label' => 'متن CTA ثانویه',
                        'default' => 'اطلاعات بیشتر',
                    ],
                    'cta_secondary_url' => [
                        'type' => 'url',
                        'label' => 'لینک CTA ثانویه',
                        'placeholder' => '/about',
                    ],
                    'cta_whatsapp_text' => [
                        'type' => 'text',
                        'label' => 'متن دکمه واتساپ',
                        'default' => 'چت در واتساپ',
                    ],
                    'cta_phone_text' => [
                        'type' => 'text',
                        'label' => 'متن دکمه تماس',
                        'default' => 'تماس بگیرید',
                    ],
                ],
            ],
            
            'advanced' => [
                'title' => 'پیشرفته',
                'icon' => 'dashicons-admin-tools',
                'fields' => [
                    'custom_css' => [
                        'type' => 'code',
                        'label' => 'CSS سفارشی',
                        'language' => 'css',
                        'rows' => 10,
                        'description' => 'کدهای CSS اضافی',
                    ],
                    'head_scripts' => [
                        'type' => 'code',
                        'label' => 'کد در Head',
                        'language' => 'html',
                        'rows' => 6,
                        'description' => 'کدهای قبل از &lt;/head&gt; (مثل Google Analytics)',
                    ],
                    'footer_scripts' => [
                        'type' => 'code',
                        'label' => 'کد در Footer',
                        'language' => 'html',
                        'rows' => 6,
                        'description' => 'کدهای قبل از &lt;/body&gt;',
                    ],
                    'enable_preloader' => [
                        'type' => 'checkbox',
                        'label' => 'فعال‌سازی پری‌لودر',
                        'default' => false,
                    ],
                    'enable_back_to_top' => [
                        'type' => 'checkbox',
                        'label' => 'دکمه بازگشت به بالا',
                        'default' => true,
                    ],
                    'enable_smooth_scroll' => [
                        'type' => 'checkbox',
                        'label' => 'اسکرول نرم',
                        'default' => true,
                    ],
                ],
            ],
        ];
    }
    
    /**
     * تنظیمات پیش‌فرض
     */
    private function get_default_settings() {
        $defaults = [];
        
        foreach ($this->tabs as $tab_id => $tab) {
            foreach ($tab['fields'] as $field_id => $field) {
                $defaults[$field_id] = isset($field['default']) ? $field['default'] : '';
            }
        }
        
        return $defaults;
    }
    
    /**
     * اضافه کردن منو
     */
    public function add_admin_menu() {
        add_menu_page(
            'تنظیمات قالب',
            'تنظیمات قالب',
            'manage_options',
            'dst-theme-settings',
            [$this, 'render_admin_page'],
            'dashicons-admin-customizer',
            999
        );
    }
    
    /**
     * لود فایل‌های ادمین
     */
    public function admin_assets($hook) {
        if (strpos($hook, 'dst-theme-settings') === false) {
            return;
        }
        
        // WordPress Media Uploader
        wp_enqueue_media();
        
        // CSS
        wp_enqueue_style(
            'dst-theme-settings-admin',
            $this->module_url . '/assets/css/admin.css',
            [],
            '1.0.0'
        );
        
        // JS
        wp_enqueue_script(
            'dst-theme-settings-admin',
            $this->module_url . '/assets/js/admin.js',
            ['jquery', 'wp-color-picker'],
            '1.0.0',
            true
        );
        
        // Color Picker
        wp_enqueue_style('wp-color-picker');
    }
    
    /**
 * ذخیره تنظیمات
 */
    public function handle_save_settings() {
        if (!isset($_POST['dst_theme_settings_save']) || !current_user_can('manage_options')) {
            return;
        }
        
        check_admin_referer('dst_theme_settings_nonce');
        
        // گرفتن تنظیمات قبلی
        $old_settings = get_option($this->option_name, []);
        $new_settings = [];
        
        foreach ($this->tabs as $tab_id => $tab) {
            foreach ($tab['fields'] as $field_id => $field) {
                $type = $field['type'];
                
                // نادیده گرفتن فیلدهای خاص
                if ($type === 'preview') {
                    continue;
                }
                
                switch ($type) {
                    case 'checkbox':
                        $new_settings[$field_id] = isset($_POST[$field_id]) && $_POST[$field_id] ? true : false;
                        break;
                        
                    case 'number':
                        $new_settings[$field_id] = isset($_POST[$field_id]) ? intval($_POST[$field_id]) : ($field['default'] ?? 0);
                        break;
                        
                    case 'email':
                        $new_settings[$field_id] = isset($_POST[$field_id]) ? sanitize_email($_POST[$field_id]) : '';
                        break;
                        
                    case 'url':
                        $new_settings[$field_id] = isset($_POST[$field_id]) ? esc_url_raw($_POST[$field_id]) : '';
                        break;
                        
                    case 'textarea':
                    case 'code':
                        $new_settings[$field_id] = isset($_POST[$field_id]) ? wp_unslash($_POST[$field_id]) : '';
                        break;
                        
                    case 'color':
                        // اگر مقدار خالی بود، از مقدار قبلی یا پیش‌فرض استفاده کن
                        if (isset($_POST[$field_id]) && !empty($_POST[$field_id])) {
                            $new_settings[$field_id] = sanitize_hex_color($_POST[$field_id]);
                        } else {
                            $new_settings[$field_id] = isset($old_settings[$field_id]) ? $old_settings[$field_id] : ($field['default'] ?? '');
                        }
                        break;
                        
                    case 'image':
                        // اگر مقدار خالی بود و قبلاً مقدار داشت، حفظ کن
                        if (isset($_POST[$field_id]) && !empty($_POST[$field_id])) {
                            $new_settings[$field_id] = esc_url_raw($_POST[$field_id]);
                        } elseif (isset($_POST[$field_id]) && $_POST[$field_id] === '') {
                            // اگر صریحاً خالی شده (دکمه حذف زده شده)
                            $new_settings[$field_id] = '';
                        } else {
                            // حفظ مقدار قبلی
                            $new_settings[$field_id] = isset($old_settings[$field_id]) ? $old_settings[$field_id] : '';
                        }
                        break;
                        
                    default:
                        $new_settings[$field_id] = isset($_POST[$field_id]) ? sanitize_text_field($_POST[$field_id]) : '';
                }
            }
        }
        
        update_option($this->option_name, $new_settings);
        $this->settings = $new_settings;
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>✅ تنظیمات قالب با موفقیت ذخیره شد.</p></div>';
        });
    }
    
    /**
     * صفحه ادمین
     */
    public function render_admin_page() {
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
        ?>
        <div class="wrap dst-theme-settings-wrap">
            <h1>🎨 تنظیمات قالب</h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('dst_theme_settings_nonce'); ?>
                
                <div class="dst-settings-container">
                    <!-- تب‌ها -->
                    <div class="dst-settings-tabs">
                        <?php foreach ($this->tabs as $tab_id => $tab): ?>
                            <a href="?page=dst-theme-settings&tab=<?php echo $tab_id; ?>" 
                               class="dst-tab <?php echo $current_tab === $tab_id ? 'active' : ''; ?>">
                                <span class="dashicons <?php echo $tab['icon']; ?>"></span>
                                <?php echo $tab['title']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- محتوای تب -->
                    <div class="dst-settings-content">
                        <?php if (isset($this->tabs[$current_tab])): ?>
                            <div class="dst-tab-content">
                                <h2><?php echo $this->tabs[$current_tab]['title']; ?></h2>
                                
                                <table class="form-table">
                                    <?php foreach ($this->tabs[$current_tab]['fields'] as $field_id => $field): ?>
                                        <tr>
                                            <th scope="row">
                                                <label for="<?php echo $field_id; ?>">
                                                    <?php echo $field['label']; ?>
                                                </label>
                                            </th>
                                            <td>
                                                <?php $this->render_field($field_id, $field); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        <?php endif; ?>
                        
                        <p class="submit">
                            <button type="submit" name="dst_theme_settings_save" class="button button-primary button-large">
                                💾 ذخیره تنظیمات
                            </button>
                        </p>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * رندر فیلد
     */
    private function render_field($field_id, $field) {
        $type = $field['type'];
        $value = isset($this->settings[$field_id]) ? $this->settings[$field_id] : ($field['default'] ?? '');
        
        switch ($type) {
            case 'text':
            case 'url':
            case 'email':
                ?>
                <input type="<?php echo $type === 'url' ? 'url' : ($type === 'email' ? 'email' : 'text'); ?>" 
                       id="<?php echo $field_id; ?>" 
                       name="<?php echo $field_id; ?>" 
                       value="<?php echo esc_attr($value); ?>" 
                       class="regular-text"
                       <?php echo isset($field['placeholder']) ? 'placeholder="' . esc_attr($field['placeholder']) . '"' : ''; ?>>
                <?php
                break;
                
            case 'number':
                ?>
                <input type="number" 
                       id="<?php echo $field_id; ?>" 
                       name="<?php echo $field_id; ?>" 
                       value="<?php echo esc_attr($value); ?>" 
                       class="small-text"
                       <?php echo isset($field['min']) ? 'min="' . $field['min'] . '"' : ''; ?>
                       <?php echo isset($field['max']) ? 'max="' . $field['max'] . '"' : ''; ?>>
                <?php
                break;
                
            case 'textarea':
                $rows = isset($field['rows']) ? $field['rows'] : 4;
                ?>
                <textarea id="<?php echo $field_id; ?>" 
                          name="<?php echo $field_id; ?>" 
                          rows="<?php echo $rows; ?>" 
                          class="large-text"><?php echo esc_textarea($value); ?></textarea>
                <?php
                break;
                
            case 'code':
                $rows = isset($field['rows']) ? $field['rows'] : 8;
                ?>
                <textarea id="<?php echo $field_id; ?>" 
                          name="<?php echo $field_id; ?>" 
                          rows="<?php echo $rows; ?>" 
                          class="large-text code"
                          dir="ltr"
                          style="font-family: monospace;"><?php echo esc_textarea($value); ?></textarea>
                <?php
                break;
                
            case 'select':
                ?>
                <select id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>">
                    <?php foreach ($field['options'] as $opt_value => $opt_label): ?>
                        <option value="<?php echo esc_attr($opt_value); ?>" <?php selected($value, $opt_value); ?>>
                            <?php echo esc_html($opt_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php
                break;
                
            case 'checkbox':
                ?>
                <label>
                    <input type="checkbox" 
                           id="<?php echo $field_id; ?>" 
                           name="<?php echo $field_id; ?>" 
                           value="1" 
                           <?php checked($value, true); ?>>
                    فعال
                </label>
                <?php
                break;
                
            case 'color':
                ?>
                <input type="text" 
                       id="<?php echo $field_id; ?>" 
                       name="<?php echo $field_id; ?>" 
                       value="<?php echo esc_attr($value); ?>" 
                       class="dst-color-picker"
                       data-default-color="<?php echo esc_attr($field['default'] ?? '#000000'); ?>">
                <?php
                break;
                
            case 'image':
                ?>
                <div class="dst-image-upload" data-field="<?php echo $field_id; ?>">
                    <input type="hidden" id="<?php echo $field_id; ?>" name="<?php echo $field_id; ?>" value="<?php echo esc_attr($value); ?>">
                    
                    <div class="dst-image-preview">
                        <?php if ($value): ?>
                            <img src="<?php echo esc_url($value); ?>" alt="">
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" class="button dst-upload-btn">
                        <span class="dashicons dashicons-upload"></span>
                        انتخاب تصویر
                    </button>
                    
                    <button type="button" class="button dst-remove-btn" <?php echo !$value ? 'style="display:none;"' : ''; ?>>
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
                <?php
                break;
                case 'preview':
                ?>
                <div class="dst-color-preview">
                    <h3>🎨 پیش‌نمایش رنگ‌ها</h3>
                    <div class="dst-color-preview-grid">
                        <div class="dst-color-preview-item">
                            <div class="dst-color-preview-box" data-preview="primary"></div>
                            <span>رنگ اصلی</span>
                        </div>
                        <div class="dst-color-preview-item">
                            <div class="dst-color-preview-box" data-preview="secondary"></div>
                            <span>رنگ ثانویه</span>
                        </div>
                        <div class="dst-color-preview-item">
                            <div class="dst-color-preview-box" data-preview="accent"></div>
                            <span>رنگ تأکیدی</span>
                        </div>
                        <div class="dst-color-preview-item">
                            <div class="dst-color-preview-box" data-preview="text"></div>
                            <span>رنگ متن</span>
                        </div>
                        <div class="dst-color-preview-item">
                            <div class="dst-color-preview-box" data-preview="headerBg"></div>
                            <span>پس‌زمینه هدر</span>
                        </div>
                        <div class="dst-color-preview-item">
                            <div class="dst-color-preview-box" data-preview="footerBg"></div>
                            <span>پس‌زمینه فوتر</span>
                        </div>
                    </div>
                    
                    <div class="dst-theme-preview">
                        <div class="dst-theme-preview-header">
                            <span>لوگو</span>
                            <span>منو</span>
                        </div>
                        <div class="dst-theme-preview-content">
                            <h4>نمونه محتوا</h4>
                            <p>این یک متن نمونه است. <span class="dst-theme-preview-accent">متن تأکیدی</span></p>
                            <div style="margin-top: 15px;">
                                <span class="dst-theme-preview-btn">دکمه اصلی</span>
                                <span class="dst-theme-preview-btn secondary">دکمه ثانویه</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                break;
        
            }
        
        // توضیحات
        if (isset($field['description'])) {
            echo '<p class="description">' . $field['description'] . '</p>';
        }
    }
    
    /**
     * خروجی CSS سفارشی
     */
    public function output_custom_css() {
        $css = '';
        
        // CSS Variables
        $css .= ':root {';
        $css .= '--dst-primary: ' . ($this->get('primary_color') ?: '#3C50E0') . ';';
        $css .= '--dst-secondary: ' . ($this->get('secondary_color') ?: '#10B981') . ';';
        $css .= '--dst-accent: ' . ($this->get('accent_color') ?: '#F59E0B') . ';';
        $css .= '--dst-text: ' . ($this->get('text_color') ?: '#1e293b') . ';';
        $css .= '--dst-text-light: ' . ($this->get('text_light_color') ?: '#64748b') . ';';
        $css .= '--dst-bg: ' . ($this->get('background_color') ?: '#ffffff') . ';';
        $css .= '--dst-header-bg: ' . ($this->get('header_bg_color') ?: '#ffffff') . ';';
        $css .= '--dst-header-text: ' . ($this->get('header_text_color') ?: '#1e293b') . ';';
        $css .= '--dst-menu-text: ' . ($this->get('menu_text_color') ?: '#1e293b') . ';';
        $css .= '--dst-menu-hover: ' . ($this->get('menu_hover_color') ?: '#3C50E0') . ';';
        $css .= '--dst-menu-active: ' . ($this->get('menu_active_color') ?: '#3C50E0') . ';';
        $css .= '--dst-btn-bg: ' . ($this->get('button_bg_color') ?: '#3C50E0') . ';';
        $css .= '--dst-btn-text: ' . ($this->get('button_text_color') ?: '#ffffff') . ';';
        $css .= '--dst-btn-hover: ' . ($this->get('button_hover_bg_color') ?: '#2D3FBD') . ';';
        $css .= '--dst-footer-bg: ' . ($this->get('footer_bg_color') ?: '#1e293b') . ';';
        $css .= '--dst-footer-text: ' . ($this->get('footer_text_color') ?: '#e2e8f0') . ';';
        $css .= '--dst-container: ' . ($this->get('container_width') ?: 1280) . 'px;';
        $css .= '--dst-radius: ' . ($this->get('border_radius') ?: 8) . 'px;';
        $css .= '--dst-font-size: ' . ($this->get('base_font_size') ?: 16) . 'px;';
        $css .= '--dst-line-height: ' . ($this->get('line_height') ?: '1.7') . ';';
        $css .= '}';
        
        // Body styles
        $body_font = $this->get('body_font') ?: 'Vazirmatn';
        $heading_font = $this->get('heading_font') ?: 'Vazirmatn';
        
        $css .= 'body { font-family: "' . $body_font . '", sans-serif; font-size: var(--dst-font-size); line-height: var(--dst-line-height); color: var(--dst-text); background-color: var(--dst-bg)!important; }';
        $css .= 'h1, h2, h3, h4, h5, h6 { font-family: "' . $heading_font . '", sans-serif; }';
        $css .= '.dst-container { max-width: var(--dst-container); margin: 0 auto; padding: 0 20px; }';

        // Menu styles
        $css .= '.hf-nav-menu > li > a { color: var(--dst-menu-text); }';
        $css .= '.hf-nav-menu > li > a:hover { color: var(--dst-menu-hover); }';
        $css .= '.hf-nav-menu > li.current-menu-item > a, .hf-nav-menu > li.current-menu-ancestor > a { color: var(--dst-menu-active); }';

        // Button styles
        $css .= '.hf-btn-primary { background-color: var(--dst-btn-bg); color: var(--dst-btn-text); }';
        $css .= '.hf-btn-primary:hover { background-color: var(--dst-btn-hover); }';
        $css .= '.hf-badge-primary { background-color: var(--dst-btn-bg); color: var(--dst-btn-text); }';

        // Admin bar offset for fixed/absolute headers
        $css .= '
        body.admin-bar .hf-header-sticky,
        body.admin-bar .hf-header-fixed,
        body.admin-bar header[style*="position: fixed"],
        body.admin-bar header[style*="position:fixed"],
        body.admin-bar header.fixed,
        body.admin-bar header.sticky {
            top: 32px !important;
        }
        @media screen and (max-width: 782px) {
            body.admin-bar .hf-header-sticky,
            body.admin-bar .hf-header-fixed,
            body.admin-bar header[style*="position: fixed"],
            body.admin-bar header[style*="position:fixed"],
            body.admin-bar header.fixed,
            body.admin-bar header.sticky {
                top: 46px !important;
            }
        }
        ';

        // Custom CSS
        $custom_css = $this->get('custom_css');
        if ($custom_css) {
            $css .= $custom_css;
        }
        
        if ($css) {
            echo '<style id="dst-theme-settings-css">' . $css . '</style>';
        }
    }
    
    /**
     * خروجی اسکریپت Head
     */
    public function output_head_scripts() {
        $scripts = $this->get('head_scripts');
        if ($scripts) {
            echo $scripts;
        }
    }
    
    /**
     * خروجی اسکریپت Footer
     */
    public function output_footer_scripts() {
        $scripts = $this->get('footer_scripts');
        if ($scripts) {
            echo $scripts;
        }
    }
    
    /**
     * لود فونت‌ها
     */
    public function enqueue_fonts() {
        $body_font = $this->get('body_font') ?: 'Vazirmatn';
        
        // Vazirmatn from local files
        if (in_array($body_font, ['Vazirmatn'])) {
            wp_enqueue_style(
                'vazirmatn-font',
                get_template_directory_uri() . '/assets/fonts/vazirmatn/font.css',
                [],
                null
            );
        }
    }
    
    /**
     * گرفتن یک تنظیم
     */
    public function get($key, $default = null) {
        if (isset($this->settings[$key])) {
            return $this->settings[$key];
        }
        return $default;
    }
    
    /**
     * گرفتن همه تنظیمات
     */
    public function get_all() {
        return $this->settings;
    }
}

// راه‌اندازی
global $dst_theme_settings;
$dst_theme_settings = new DST_Theme_Settings();

/**
 * توابع کمکی
 */

/**
 * گرفتن تنظیم قالب
 */
function dst_get_setting($key, $default = null) {
    global $dst_theme_settings;
    if ($dst_theme_settings) {
        return $dst_theme_settings->get($key, $default);
    }
    return $default;
}

/**
 * گرفتن لوگو
 */
if (!function_exists('dst_get_logo')) {
    function dst_get_logo($type = 'default') {
        if ($type === 'light') {
            return dst_get_setting('site_logo_light') ?: dst_get_setting('site_logo');
        }
        return dst_get_setting('site_logo');
    }
}

/**
 * نمایش لوگو
 */
if (!function_exists('dst_logo')) {
    function dst_logo($type = 'default', $class = '') {
        $logo = dst_get_logo($type);

        if ($logo) {
            echo '<a href="' . esc_url(home_url('/')) . '" class="dst-logo ' . esc_attr($class) . '">';
            echo '<img src="' . esc_url($logo) . '" alt="' . esc_attr(get_bloginfo('name')) . '">';
            echo '</a>';
        } else {
            echo '<a href="' . esc_url(home_url('/')) . '" class="dst-logo dst-logo-text ' . esc_attr($class) . '">';
            echo esc_html(get_bloginfo('name'));
            echo '</a>';
        }
    }
}

/**
 * گرفتن شبکه‌های اجتماعی
 */
if (!function_exists('dst_get_socials')) {
    function dst_get_socials() {
        $socials = [];

        $networks = ['instagram', 'telegram', 'whatsapp', 'twitter', 'linkedin', 'youtube', 'aparat', 'github'];

        foreach ($networks as $network) {
            $value = dst_get_setting($network);
            if ($value) {
                $url = $value;

                // WhatsApp special handling
                if ($network === 'whatsapp') {
                    $url = 'https://wa.me/' . $value;
                }

                $socials[$network] = $url;
            }
        }

        return $socials;
    }
}

/**
 * نمایش آیکون‌های شبکه‌های اجتماعی
 */
if (!function_exists('dst_social_icons')) {
    function dst_social_icons($class = '') {
        $socials = dst_get_socials();

        if (empty($socials)) {
            return;
        }

        $icons = [
            'instagram' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
            'telegram' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
            'whatsapp' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
            'twitter' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
            'linkedin' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
            'youtube' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
            'aparat' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.001 1.594c-9.27-.003-13.913 11.203-7.36 17.758a10.403 10.403 0 0 0 14.715 0c6.556-6.547 1.918-17.754-7.355-17.758zm.002 4.073a2.26 2.26 0 1 1 0 4.52 2.26 2.26 0 0 1 0-4.52zm-4.488 3.1a1.503 1.503 0 1 1 0 3.005 1.503 1.503 0 0 1 0-3.006zm8.976 0a1.503 1.503 0 1 1 0 3.005 1.503 1.503 0 0 1 0-3.006zm-4.489 3.1a2.26 2.26 0 1 1 .001 4.52 2.26 2.26 0 0 1 0-4.52z"/></svg>',
            'github' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>',
        ];

        echo '<div class="dst-social-icons ' . esc_attr($class) . '">';
        foreach ($socials as $network => $url) {
            echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" class="dst-social-icon dst-social-' . $network . '" title="' . ucfirst($network) . '">';
            echo $icons[$network] ?? '';
            echo '</a>';
        }
        echo '</div>';
    }
}

/**
 * گرفتن اطلاعات تماس
 */
if (!function_exists('dst_get_contact')) {
    function dst_get_contact($key = null) {
        $contact = [
            'phone' => dst_get_setting('phone'),
            'mobile' => dst_get_setting('mobile'),
            'email' => dst_get_setting('email'),
            'address' => dst_get_setting('address'),
            'working_hours' => dst_get_setting('working_hours'),
            'map_lat' => dst_get_setting('map_lat'),
            'map_lng' => dst_get_setting('map_lng'),
        ];

        if ($key) {
            return isset($contact[$key]) ? $contact[$key] : null;
        }

        return $contact;
    }
}

/**
 * گرفتن متن کپی‌رایت
 */
if (!function_exists('dst_get_copyright')) {
    function dst_get_copyright() {
        $text = dst_get_setting('copyright_text') ?: '© تمامی حقوق محفوظ است.';
        return str_replace('{year}', date('Y'), $text);
    }
}

/**
 * ===================================
 * توابع CTA (Call to Action)
 * ===================================
 */

/**
 * آیکون‌های CTA
 */
function dst_get_cta_icon($icon) {
    $icons = [
        'arrow-left' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>',
        'arrow-right' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>',
        'phone' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>',
        'cart' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>',
        'download' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>',
        'play' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>',
        'check' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>',
        'whatsapp' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
        'telegram' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
    ];
    
    return isset($icons[$icon]) ? $icons[$icon] : '';
}

/**
 * نمایش CTA اصلی
 * 
 * @param array $args آرگومان‌های سفارشی
 */
function dst_cta($args = []) {
    $defaults = [
        'type' => 'primary',        // primary, secondary, whatsapp, phone, telegram, custom
        'text' => null,             // متن دکمه (null = از تنظیمات)
        'url' => null,              // لینک (null = از تنظیمات)
        'style' => null,            // solid, outline, gradient, rounded, shadow (null = از تنظیمات)
        'size' => null,             // sm, md, lg, xl (null = از تنظیمات)
        'icon' => null,             // آیکون (null = از تنظیمات)
        'icon_position' => 'start', // start, end
        'class' => '',              // کلاس اضافی
        'target' => '_self',        // _self, _blank
        'echo' => true,             // چاپ یا برگرداندن
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    // گرفتن مقادیر از تنظیمات
    $style = $args['style'] ?: dst_get_setting('cta_style') ?: 'solid';
    $size = $args['size'] ?: dst_get_setting('cta_size') ?: 'md';
    
    // تعیین متن و لینک بر اساس نوع
    switch ($args['type']) {
        case 'primary':
            $text = $args['text'] ?: dst_get_setting('cta_primary_text') ?: 'شروع کنید';
            $url = $args['url'] ?: dst_get_setting('cta_primary_url') ?: '#';
            $icon = $args['icon'] ?: dst_get_setting('cta_primary_icon') ?: 'none';
            break;
            
        case 'secondary':
            $text = $args['text'] ?: dst_get_setting('cta_secondary_text') ?: 'اطلاعات بیشتر';
            $url = $args['url'] ?: dst_get_setting('cta_secondary_url') ?: '#';
            $icon = $args['icon'] ?: 'none';
            $style = 'outline'; // ثانویه همیشه outline
            break;
            
        case 'whatsapp':
            $text = $args['text'] ?: dst_get_setting('cta_whatsapp_text') ?: 'چت در واتساپ';
            $whatsapp = dst_get_setting('whatsapp');
            $url = $args['url'] ?: ($whatsapp ? 'https://wa.me/' . $whatsapp : '#');
            $icon = 'whatsapp';
            $args['target'] = '_blank';
            break;
            
        case 'phone':
            $text = $args['text'] ?: dst_get_setting('cta_phone_text') ?: 'تماس بگیرید';
            $phone = dst_get_setting('phone') ?: dst_get_setting('mobile');
            $url = $args['url'] ?: ($phone ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '#');
            $icon = 'phone';
            break;
            
        case 'telegram':
            $text = $args['text'] ?: 'تلگرام';
            $url = $args['url'] ?: dst_get_setting('telegram') ?: '#';
            $icon = 'telegram';
            $args['target'] = '_blank';
            break;
            
        default: // custom
            $text = $args['text'] ?: 'کلیک کنید';
            $url = $args['url'] ?: '#';
            $icon = $args['icon'] ?: 'none';
    }
    
    // ساخت کلاس‌ها
    $classes = [
        'dst-cta',
        'dst-cta-' . $args['type'],
        'dst-cta-style-' . $style,
        'dst-cta-size-' . $size,
    ];
    
    if ($args['class']) {
        $classes[] = $args['class'];
    }
    
    // ساخت HTML
    $html = '<a href="' . esc_url($url) . '" class="' . esc_attr(implode(' ', $classes)) . '"';
    
    if ($args['target'] === '_blank') {
        $html .= ' target="_blank" rel="noopener noreferrer"';
    }
    
    $html .= '>';
    
    // آیکون قبل از متن
    if ($icon && $icon !== 'none' && $args['icon_position'] === 'start') {
        $html .= '<span class="dst-cta-icon">' . dst_get_cta_icon($icon) . '</span>';
    }
    
    $html .= '<span class="dst-cta-text">' . esc_html($text) . '</span>';
    
    // آیکون بعد از متن
    if ($icon && $icon !== 'none' && $args['icon_position'] === 'end') {
        $html .= '<span class="dst-cta-icon">' . dst_get_cta_icon($icon) . '</span>';
    }
    
    $html .= '</a>';
    
    if ($args['echo']) {
        echo $html;
    }
    
    return $html;
}

/**
 * نمایش گروه CTA (اصلی + ثانویه)
 */
function dst_cta_group($args = []) {
    $defaults = [
        'primary' => true,
        'secondary' => true,
        'class' => '',
        'gap' => 'md', // sm, md, lg
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    $classes = ['dst-cta-group', 'dst-cta-gap-' . $args['gap']];
    if ($args['class']) {
        $classes[] = $args['class'];
    }
    
    echo '<div class="' . esc_attr(implode(' ', $classes)) . '">';
    
    if ($args['primary']) {
        dst_cta(['type' => 'primary']);
    }
    
    if ($args['secondary']) {
        dst_cta(['type' => 'secondary']);
    }
    
    echo '</div>';
}

/**
 * دکمه واتساپ شناور
 */
function dst_floating_whatsapp($args = []) {
    $whatsapp = dst_get_setting('whatsapp');
    if (!$whatsapp) {
        return;
    }
    
    $defaults = [
        'position' => 'bottom-left', // bottom-left, bottom-right
        'text' => dst_get_setting('cta_whatsapp_text') ?: 'چت در واتساپ',
        'show_text' => true,
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    $url = 'https://wa.me/' . $whatsapp;
    ?>
    <a href="<?php echo esc_url($url); ?>" 
       class="dst-floating-whatsapp dst-floating-<?php echo esc_attr($args['position']); ?>" 
       target="_blank" 
       rel="noopener noreferrer"
       title="<?php echo esc_attr($args['text']); ?>">
        <?php echo dst_get_cta_icon('whatsapp'); ?>
        <?php if ($args['show_text']): ?>
            <span class="dst-floating-text"><?php echo esc_html($args['text']); ?></span>
        <?php endif; ?>
    </a>
    <?php
}
