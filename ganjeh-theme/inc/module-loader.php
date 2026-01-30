<?php
/**
 * Module Loader System
 * سیستم بارگذاری ماژول‌های قالب
 *
 * @package Ganjeh
 * @subpackage Modules
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * کلاس مدیریت ماژول‌ها
 */
class Ganjeh_Module_Loader {

    /**
     * نمونه singleton
     */
    private static $instance = null;

    /**
     * لیست ماژول‌های بارگذاری شده
     */
    private $modules = [];

    /**
     * مسیر فولدر ماژول‌ها
     */
    private $modules_path;

    /**
     * URL فولدر ماژول‌ها
     */
    private $modules_url;

    /**
     * گرفتن نمونه singleton
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * سازنده
     */
    private function __construct() {
        $this->modules_path = GANJEH_DIR . '/modules';
        $this->modules_url = GANJEH_URI . '/modules';

        // بارگذاری ماژول‌ها
        $this->load_modules();

        // صفحه مدیریت ماژول‌ها در ادمین
        add_action('admin_menu', [$this, 'add_modules_page'], 5);
    }

    /**
     * بارگذاری همه ماژول‌های فعال
     */
    private function load_modules() {
        // چک کردن وجود فولدر ماژول‌ها
        if (!is_dir($this->modules_path)) {
            return;
        }

        // اسکن فولدر ماژول‌ها
        $modules_dirs = glob($this->modules_path . '/*', GLOB_ONLYDIR);

        foreach ($modules_dirs as $module_dir) {
            $module_name = basename($module_dir);
            $config_file = $module_dir . '/module.json';
            $init_file = $module_dir . '/init.php';

            // چک کردن وجود فایل‌های لازم
            if (!file_exists($config_file) || !file_exists($init_file)) {
                continue;
            }

            // خواندن تنظیمات ماژول
            $config = json_decode(file_get_contents($config_file), true);

            if (!$config) {
                continue;
            }

            // چک کردن فعال بودن ماژول
            $is_enabled = $this->is_module_enabled($module_name, $config);

            if (!$is_enabled) {
                continue;
            }

            // ذخیره اطلاعات ماژول
            $this->modules[$module_name] = [
                'name'   => $module_name,
                'path'   => $module_dir,
                'url'    => $this->modules_url . '/' . $module_name,
                'config' => $config,
                'active' => true,
            ];

            // بارگذاری ماژول
            require_once $init_file;
        }
    }

    /**
     * چک کردن فعال بودن ماژول
     */
    private function is_module_enabled($module_name, $config) {
        // اول چک کردن تنظیمات ذخیره شده
        $saved_modules = get_option('ganjeh_active_modules', []);

        if (!empty($saved_modules)) {
            return in_array($module_name, $saved_modules);
        }

        // اگر تنظیمات نبود، از module.json استفاده کن
        return isset($config['enabled']) ? $config['enabled'] : true;
    }

    /**
     * گرفتن اطلاعات یک ماژول
     */
    public function get_module($name) {
        return isset($this->modules[$name]) ? $this->modules[$name] : null;
    }

    /**
     * گرفتن لیست همه ماژول‌ها
     */
    public function get_all_modules() {
        $all_modules = [];

        if (!is_dir($this->modules_path)) {
            return $all_modules;
        }

        $modules_dirs = glob($this->modules_path . '/*', GLOB_ONLYDIR);

        foreach ($modules_dirs as $module_dir) {
            $module_name = basename($module_dir);
            $config_file = $module_dir . '/module.json';

            if (!file_exists($config_file)) {
                continue;
            }

            $config = json_decode(file_get_contents($config_file), true);

            if (!$config) {
                continue;
            }

            $all_modules[$module_name] = [
                'name'   => $module_name,
                'path'   => $module_dir,
                'url'    => $this->modules_url . '/' . $module_name,
                'config' => $config,
                'active' => isset($this->modules[$module_name]),
            ];
        }

        return $all_modules;
    }

    /**
     * صفحه مدیریت ماژول‌ها
     */
    public function add_modules_page() {
        add_theme_page(
            __('ماژول‌ها', 'ganjeh'),
            __('ماژول‌ها', 'ganjeh'),
            'manage_options',
            'dst-modules',
            [$this, 'render_modules_page']
        );
    }

    /**
     * رندر صفحه ماژول‌ها
     */
    public function render_modules_page() {
        $all_modules = $this->get_all_modules();

        // ذخیره تنظیمات
        if (isset($_POST['ganjeh_save_modules']) && check_admin_referer('ganjeh_modules_nonce')) {
            $active_modules = isset($_POST['active_modules']) ? array_map('sanitize_text_field', $_POST['active_modules']) : [];
            update_option('ganjeh_active_modules', $active_modules);
            echo '<div class="notice notice-success"><p>' . __('تنظیمات ذخیره شد. صفحه را رفرش کنید.', 'ganjeh') . '</p></div>';
        }

        $saved_modules = get_option('ganjeh_active_modules', []);
        ?>
        <div class="wrap ganjeh-modules-wrap">
            <h1>
                <span class="dashicons dashicons-screenoptions"></span>
                <?php _e('ماژول‌های قالب', 'ganjeh'); ?>
            </h1>
            <p class="description"><?php _e('ماژول‌های فعال و غیرفعال قالب را مدیریت کنید.', 'ganjeh'); ?></p>

            <form method="post">
                <?php wp_nonce_field('ganjeh_modules_nonce'); ?>

                <div class="ganjeh-modules-grid">
                    <?php foreach ($all_modules as $module_name => $module) :
                        $config = $module['config'];
                        $is_active = empty($saved_modules) ? ($config['enabled'] ?? true) : in_array($module_name, $saved_modules);
                        ?>
                        <div class="ganjeh-module-card <?php echo $is_active ? 'is-active' : ''; ?>">
                            <div class="module-header">
                                <label class="module-toggle">
                                    <input type="checkbox" name="active_modules[]" value="<?php echo esc_attr($module_name); ?>" <?php checked($is_active); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <h3><?php echo esc_html($config['title'] ?? $module_name); ?></h3>
                                <span class="module-version">v<?php echo esc_html($config['version'] ?? '1.0.0'); ?></span>
                            </div>
                            <p class="module-description">
                                <?php echo esc_html($config['description'] ?? ''); ?>
                            </p>
                            <?php if (!empty($config['features'])) : ?>
                                <ul class="module-features">
                                    <?php foreach (array_slice($config['features'], 0, 3) as $feature) : ?>
                                        <li><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html($feature); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <p class="submit">
                    <button type="submit" name="ganjeh_save_modules" class="button button-primary button-large">
                        <span class="dashicons dashicons-saved"></span>
                        <?php _e('ذخیره تنظیمات', 'ganjeh'); ?>
                    </button>
                </p>
            </form>
        </div>

        <style>
            .ganjeh-modules-wrap h1 {
                display: flex;
                align-items: center;
                gap: 10px;
                font-size: 24px;
            }
            .ganjeh-modules-wrap h1 .dashicons {
                font-size: 28px;
                width: 28px;
                height: 28px;
                color: var(--color-primary, #4CB050);
            }
            .ganjeh-modules-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .ganjeh-module-card {
                background: #fff;
                border: 2px solid #e5e7eb;
                border-radius: 12px;
                padding: 20px;
                transition: all 0.3s;
            }
            .ganjeh-module-card.is-active {
                border-color: #10b981;
                background: linear-gradient(to bottom, #f0fdf4, #fff);
            }
            .module-header {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 12px;
            }
            .module-header h3 {
                flex: 1;
                margin: 0;
                font-size: 16px;
                color: #1e293b;
            }
            .module-version {
                font-size: 11px;
                color: #64748b;
                background: #f1f5f9;
                padding: 2px 8px;
                border-radius: 4px;
            }
            .module-toggle {
                position: relative;
                width: 44px;
                height: 24px;
                flex-shrink: 0;
            }
            .module-toggle input {
                opacity: 0;
                width: 0;
                height: 0;
            }
            .toggle-slider {
                position: absolute;
                cursor: pointer;
                inset: 0;
                background-color: #cbd5e1;
                border-radius: 24px;
                transition: 0.3s;
            }
            .toggle-slider:before {
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
            .module-toggle input:checked + .toggle-slider {
                background-color: #10b981;
            }
            .module-toggle input:checked + .toggle-slider:before {
                transform: translateX(20px);
            }
            .module-description {
                color: #64748b;
                font-size: 13px;
                margin: 0 0 12px;
                line-height: 1.6;
            }
            .module-features {
                margin: 0;
                padding: 0;
                list-style: none;
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }
            .module-features li {
                display: flex;
                align-items: center;
                gap: 4px;
                font-size: 12px;
                color: #10b981;
                background: #ecfdf5;
                padding: 4px 10px;
                border-radius: 4px;
            }
            .module-features li .dashicons {
                font-size: 14px;
                width: 14px;
                height: 14px;
            }
            .submit button {
                display: inline-flex !important;
                align-items: center;
                gap: 8px;
            }
            .submit button .dashicons {
                font-size: 18px;
            }
        </style>
        <?php
    }
}

/**
 * تابع کمکی برای دسترسی به ماژول‌ها
 * این تابع توسط ماژول‌ها استفاده می‌شود
 */
function dst_get_module($name) {
    return Ganjeh_Module_Loader::instance()->get_module($name);
}

/**
 * راه‌اندازی Module Loader
 */
function ganjeh_init_modules() {
    Ganjeh_Module_Loader::instance();
}
add_action('after_setup_theme', 'ganjeh_init_modules', 5);
