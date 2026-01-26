<?php
/**
 * Custom Icons System
 * سیستم جایگزینی آیکون‌های وردپرس با Font Awesome
 * 
 * @package Developer_Starter
 * @subpackage Modules/Admin_Theme
 * @version 1.0.0
 */

if (!defined('ABSPATH')) exit;

class DST_Custom_Icons {
    
    /**
     * لیست آیکون‌های منو
     */
    private $menu_icons = [];
    
    /**
     * لیست آیکون‌های Topbar
     */
    private $topbar_icons = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        // تعریف آیکون‌ها
        $this->define_icons();
        
        // Hooks
        add_action('admin_enqueue_scripts', [$this, 'enqueue_icons']);
        add_action('admin_head', [$this, 'render_icon_styles']);
    }
    
    /**
     * تعریف آیکون‌ها
     */
    private function define_icons() {
        // آیکون‌های منوی اصلی وردپرس
        $this->menu_icons = [
            // منوهای پیش‌فرض
            'dashboard'     => '\f015',  // home
            'post'          => '\f303',  // file-lines
            'media'         => '\f03e',  // image
            'page'          => '\f15c',  // file
            'comments'      => '\f075',  // comment
            'appearance'    => '\f53f',  // palette
            'plugins'       => '\f1e6',  // puzzle-piece
            'users'         => '\f0c0',  // users
            'tools'         => '\f0ad',  // wrench
            'settings'      => '\f013',  // gear
            
            // WooCommerce
            'woocommerce'   => '\f07a',  // shopping-cart
            'product'       => '\f291',  // box
            'shop_order'    => '\f0f2',  // list-check
            'shop_coupon'   => '\f3ff',  // ticket
            
            // افزونه‌های محبوب
            'wpcf7'         => '\f0e0',  // envelope (Contact Form 7)
            'elementor'     => '\f1fc',  // layer-group
            'wpseo'         => '\f002',  // magnifying-glass (Yoast SEO)
            'acf'           => '\f0ad',  // wrench (ACF)
            'rank-math'     => '\f201',  // chart-line (Rank Math)
            'smush'         => '\f1c5',  // image (Smush)
            'wordfence'     => '\f3ed',  // shield-alt (Wordfence)
            'wpforms'       => '\f15b',  // file-text (WPForms)
            'mailpoet'      => '\f0e0',  // envelope (MailPoet)
            'jetpack'       => '\f1eb',  // wifi (Jetpack)
        ];
        
        // آیکون‌های Topbar
        $this->topbar_icons = [
            'wp-logo'       => '\f19a',  // wordpress (Brands)
            'updates'       => '\f0f3',  // bell
            'comments'      => '\f4ad',  // comment-dots
            'new-content'   => '\f067',  // plus
            'my-account'    => '\f007',  // user
            'site-name'     => '\f0ac',  // globe
        ];
    }
    
    /**
     * لود Font Awesome
     */
    public function enqueue_icons() {
        // Font Awesome 6 (Local)
        wp_enqueue_style(
            'font-awesome-6',
            get_template_directory_uri() . '/assets/css/vendor/fontawesome/all.min.css',
            [],
            '6.5.1'
        );
    }
    
    /**
     * رندر استایل‌های آیکون
     */
    public function render_icon_styles() {
        ?>
        <style>
        /* ═══════════════════════════════════════════════════════
           SIDEBAR MENU ICONS
           ═══════════════════════════════════════════════════════ */
        
        /* Base Icon Style */
        body.admin-theme-active #adminmenu .wp-menu-image.dashicons-before::before {
            font-family: 'Font Awesome 6 Free' !important;
            font-weight: 900 !important;
            font-size: 20px !important;
            width: 20px !important;
            height: 20px !important;
            line-height: 1 !important;
        }
        
        <?php foreach ($this->menu_icons as $class => $icon): ?>
        /* <?php echo ucfirst($class); ?> */
        body.admin-theme-active #adminmenu .menu-icon-<?php echo $class; ?> .wp-menu-image::before {
            content: '<?php echo $icon; ?>' !important;
        }
        
        <?php endforeach; ?>
        
        /* ═══════════════════════════════════════════════════════
           TOPBAR ICONS
           ═══════════════════════════════════════════════════════ */
        
        /* Base Topbar Icon Style */
        body.admin-theme-active #wpadminbar .ab-icon::before {
            font-family: 'Font Awesome 6 Free' !important;
            font-weight: 900 !important;
            font-size: 20px !important;
        }
        
        /* WordPress Logo (Brands) */
        body.admin-theme-active #wpadminbar #wp-admin-bar-wp-logo .ab-icon::before {
            content: '<?php echo $this->topbar_icons['wp-logo']; ?>' !important;
            font-family: 'Font Awesome 6 Brands' !important;
            color: var(--admin-primary) !important;
        }
        
        /* Updates */
        body.admin-theme-active #wpadminbar #wp-admin-bar-updates .ab-icon::before {
            content: '<?php echo $this->topbar_icons['updates']; ?>' !important;
        }
        
        /* Comments */
        body.admin-theme-active #wpadminbar #wp-admin-bar-comments .ab-icon::before {
            content: '<?php echo $this->topbar_icons['comments']; ?>' !important;
        }
        
        /* New Content */
        body.admin-theme-active #wpadminbar #wp-admin-bar-new-content .ab-icon::before {
            content: '<?php echo $this->topbar_icons['new-content']; ?>' !important;
        }
        
        /* ═══════════════════════════════════════════════════════
           TOPBAR STYLING
           ═══════════════════════════════════════════════════════ */
        
        /* Avatar */
        body.admin-theme-active #wpadminbar #wp-admin-bar-my-account .avatar {
            display: inline-block !important;
            width: 26px !important;
            height: 26px !important;
            border-radius: 50% !important;
            border: 2px solid var(--admin-border) !important;
            vertical-align: middle !important;
            margin-left: 8px !important;
        }
        
        /* Badge Count */
        body.admin-theme-active #wpadminbar .ab-label {
            background: var(--admin-danger) !important;
            color: #ffffff !important;
            border-radius: 10px !important;
            padding: 2px 6px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            margin-right: 6px !important;
            min-width: 18px !important;
            text-align: center !important;
            display: inline-block !important;
        }
        
        /* Icon Container */
        body.admin-theme-active #wpadminbar .ab-icon {
            width: 20px !important;
            height: 20px !important;
            display: inline-block !important;
            text-align: center !important;
        }
        
        /* ═══════════════════════════════════════════════════════
           CUSTOM POST TYPE ICONS
           ═══════════════════════════════════════════════════════ */
        
        /* Default Icon for Custom Post Types */
        body.admin-theme-active #adminmenu .menu-icon-generic .wp-menu-image::before {
            content: '\f15b' !important; /* file-text */
        }
        
        /* ═══════════════════════════════════════════════════════
           SVG ICONS SUPPORT
           ═══════════════════════════════════════════════════════ */
        
        /* اگر منو SVG داره */
        body.admin-theme-active #adminmenu .wp-menu-image img {
            opacity: 0.7 !important;
            max-width: 20px !important;
            max-height: 20px !important;
            width: auto !important;
            height: auto !important;
            filter: grayscale(0) !important;
        }
        
        body.admin-theme-active #adminmenu li:hover .wp-menu-image img {
            opacity: 1 !important;
        }
        
        body.admin-theme-active #adminmenu li.wp-has-current-submenu .wp-menu-image img,
        body.admin-theme-active #adminmenu li.current .wp-menu-image img {
            opacity: 1 !important;
        }
        
        /* ═══════════════════════════════════════════════════════
           SEPARATOR ICONS
           ═══════════════════════════════════════════════════════ */
        
        /* Hide separator default styling */
        body.admin-theme-active #adminmenu .wp-menu-separator {
            background: transparent !important;
            border-top: 1px solid var(--admin-border) !important;
            height: 1px !important;
            margin: 12px 0 !important;
        }
        </style>
        <?php
    }
    
    /**
     * اضافه کردن آیکون سفارشی
     * 
     * @param string $class کلاس منو
     * @param string $icon کد Font Awesome
     */
    public function add_custom_icon($class, $icon) {
        $this->menu_icons[$class] = $icon;
    }
    
    /**
     * دریافت آیکون
     * 
     * @param string $class کلاس منو
     * @return string|null
     */
    public function get_icon($class) {
        return isset($this->menu_icons[$class]) ? $this->menu_icons[$class] : null;
    }
    
    /**
     * دریافت همه آیکون‌ها
     * 
     * @return array
     */
    public function get_all_icons() {
        return $this->menu_icons;
    }
}

// Initialize
new DST_Custom_Icons();