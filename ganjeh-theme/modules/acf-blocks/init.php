<?php
/**
 * ACF Blocks Module
 * سیستم ساخت بلوک‌های سفارشی
 * 
 * @package Developer_Starter
 * @subpackage Modules/ACF_Blocks
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

class DST_ACF_Blocks {
    
    private $module_path;
    private $module_url;
    private $blocks = [];
    
    public function __construct() {
        // دریافت اطلاعات ماژول
        $module = dst_get_module('acf-blocks');
        
        if (!$module) {
            return;
        }
        
        $this->module_path = $module['path'];
        $this->module_url  = $module['url'];
        
        // لود Helper Functions
        require_once $this->module_path . '/helpers.php';
        
        // چک کردن ACF
        if (!function_exists('acf_register_block_type')) {
            add_action('admin_notices', [$this, 'acf_required_notice']);
            return;
        }
        
        // Hooks
        add_action('acf/init', [$this, 'register_blocks']);
        add_action('acf/init', [$this, 'register_block_category']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        add_filter('block_categories_all', [$this, 'add_block_category'], 10, 2);
        
        // لود بلوک‌ها
        $this->load_blocks();
    }
    
    /**
     * لود فایل‌های بلوک‌ها
     */
    private function load_blocks() {
        $blocks_dir = $this->module_path . '/blocks';
        
        if (!is_dir($blocks_dir)) {
            return;
        }
        
        // اسکن پوشه بلوک‌ها
        $block_dirs = glob($blocks_dir . '/*', GLOB_ONLYDIR);
        
        foreach ($block_dirs as $block_dir) {
            $block_file = $block_dir . '/block.php';
            
            if (file_exists($block_file)) {
                require_once $block_file;
                
                // ذخیره نام بلوک
                $block_name = basename($block_dir);
                $this->blocks[] = $block_name;
            }
        }
    }
    
    /**
     * ثبت بلوک‌ها
     */
    public function register_blocks() {
        // هر بلوک خودش ثبت می‌شه از طریق block.php
        do_action('dst/blocks/register', $this);
    }
    
    /**
     * ثبت دسته‌بندی بلوک
     */
    public function register_block_category() {
        // این از طریق فیلتر add_block_category انجام میشه
    }
    
    /**
     * اضافه کردن دسته‌بندی بلوک
     */
    public function add_block_category($categories, $post) {
        return array_merge(
            [
                [
                    'slug'  => 'dst-blocks',
                    'title' => '🚀 Developer Blocks',
                    'icon'  => 'layout',
                ],
            ],
            $categories
        );
    }
    
    /**
     * لود Assets ادیتور
     */
    public function enqueue_editor_assets() {
        // CSS ادیتور
        wp_enqueue_style(
            'dst-blocks-editor',
            $this->module_url . '/assets/css/editor.css',
            [],
            '1.0.0'
        );
        
        // JS ادیتور
        wp_enqueue_script(
            'dst-blocks-editor',
            $this->module_url . '/assets/js/editor.js',
            ['wp-blocks', 'wp-element', 'wp-editor'],
            '1.0.0',
            true
        );
    }
    
    /**
     * پیام نیاز به ACF
     */
    public function acf_required_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <strong>ACF Blocks Module:</strong>
                برای استفاده از این ماژول نیاز به نصب 
                <a href="https://www.advancedcustomfields.com/" target="_blank">Advanced Custom Fields Pro</a>
                دارید.
            </p>
        </div>
        <?php
    }
    
    /**
     * دریافت لیست بلوک‌ها
     */
    public function get_blocks() {
        return $this->blocks;
    }
    
    /**
     * دریافت مسیر ماژول
     */
    public function get_module_path() {
        return $this->module_path;
    }
    
    /**
     * دریافت URL ماژول
     */
    public function get_module_url() {
        return $this->module_url;
    }
}

// Initialize
new DST_ACF_Blocks();
