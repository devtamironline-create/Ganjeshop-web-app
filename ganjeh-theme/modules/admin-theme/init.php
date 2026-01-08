<?php
/**
 * Admin Theme Module - Init
 * نسخه مرحله به مرحله
 * 
 * @package Developer_Starter
 * @subpackage Modules/Admin_Theme
 */

defined('ABSPATH') || exit;

/**
 * کلاس اصلی ماژول Admin Theme
 */
class DST_Admin_Theme {
    
    private $module_path;
    private $module_url;
    private $version;
    
    public function __construct() {
        // گرفتن اطلاعات ماژول
        $module = dst_get_module('admin-theme');
        
        if (!$module) {
            return;
        }
        
        $this->module_path = $module['path'];
        $this->module_url  = $module['url'];
        $this->version     = $module['config']['version'];
        
        // فقط در ادمین
        if (!is_admin()) {
            return;
        }
        
        // هوک‌ها
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets'], 999);
        add_filter('admin_body_class', [$this, 'add_body_classes']);
        
        // لود سیستم آیکون
        $this->load_icon_system();
        
        // لود داشبورد اختصاصی
        $this->load_custom_dashboard();
    }
    
    /**
     * لود داشبورد اختصاصی
     */
    private function load_custom_dashboard() {
        $dashboard_file = $this->module_path . '/includes/custom-dashboard.php';
        if (file_exists($dashboard_file)) {
            require_once $dashboard_file;
        }
    }
    
    /**
     * لود سیستم آیکون
     */
    private function load_icon_system() {
        // انتخاب نوع آیکون (می‌تونی از تنظیمات بخونی)
        $icon_type = 'lucide'; // lucide, fontawesome, svg, none
        
        $icon_file = $this->module_path . '/includes/' . $icon_type . '-icons.php';
        
        if (file_exists($icon_file)) {
            require_once $icon_file;
        }
    }
    
    /**
     * لود Assets
     */
    public function enqueue_assets() {
        // Admin Theme CSS - نسخه نهایی
        wp_enqueue_style(
            'dst-admin-theme',
            $this->module_url . '/assets/css/admin-theme.css',
            [],
            $this->version . '-' . time()
        );
    }
    
    /**
     * اضافه کردن کلاس به body
     */
    public function add_body_classes($classes) {
        return $classes . ' admin-theme-active';
    }
}

// راه‌اندازی
new DST_Admin_Theme();
