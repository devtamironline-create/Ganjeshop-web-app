<?php
/**
 * Lucide Icons System - Complete Rewrite
 * استفاده از Lucide Icons برای همه منوهای ادمین
 * https://lucide.dev
 *
 * @package Developer_Starter
 * @version 2.0.0
 */

defined('ABSPATH') || exit;

class DST_Lucide_Icons {

    /**
     * نقشه کامل آیکون‌ها بر اساس slug منو
     */
    private $icon_map = [];

    public function __construct() {
        $this->setup_icon_map();

        add_action('admin_enqueue_scripts', [$this, 'enqueue_icons']);
        add_action('admin_head', [$this, 'output_icon_css']);
        add_action('admin_footer', [$this, 'output_icon_js'], 999);
    }

    /**
     * تنظیم نقشه آیکون‌ها
     */
    private function setup_icon_map() {
        $this->icon_map = [
            // === منوهای اصلی وردپرس ===
            'index.php'                  => 'layout-dashboard',
            'edit.php'                   => 'file-text',
            'upload.php'                 => 'image',
            'edit.php?post_type=page'    => 'file',
            'edit-comments.php'          => 'message-square',
            'themes.php'                 => 'palette',
            'plugins.php'                => 'plug',
            'users.php'                  => 'users',
            'tools.php'                  => 'wrench',
            'options-general.php'        => 'settings',
            'nav-menus.php'              => 'menu',
            'widgets.php'                => 'layout-grid',
            'customize.php'              => 'brush',
            'theme-editor.php'           => 'code',
            'plugin-editor.php'          => 'code-2',
            'update-core.php'            => 'download-cloud',
            'profile.php'                => 'user',
            'user-new.php'               => 'user-plus',
            'import.php'                 => 'upload',
            'export.php'                 => 'download',
            'site-health.php'            => 'activity',
            'export-personal-data.php'   => 'shield',
            'erase-personal-data.php'    => 'trash-2',

            // === منوهای سفارشی قالب ===
            'themes.php?page=dst-modules'  => 'grid-3x3',
            'dst-modules'                  => 'grid-3x3',
            'dst-website-settings'         => 'sliders-horizontal',
            'dst-header-footer'            => 'layout',
            'dst-theme-settings'           => 'palette',
            'dst-others'                   => 'more-horizontal',

            // === ووکامرس ===
            'woocommerce'                => 'shopping-cart',
            'edit.php?post_type=product' => 'package',
            'edit.php?post_type=shop_order' => 'shopping-bag',
            'wc-admin'                   => 'bar-chart-3',
            'wc-admin&path=/analytics/overview' => 'trending-up',
            'wc-reports'                 => 'pie-chart',
            'wc-settings'                => 'settings-2',
            'wc-status'                  => 'activity',
            'wc-addons'                  => 'puzzle',

            // === ACF ===
            'edit.php?post_type=acf-field-group' => 'layers',
            'acf-tools'                  => 'database',
            'acf-settings-updates'       => 'refresh-cw',
            'acf-options'                => 'toggle-right',

            // === یواست سئو ===
            'wpseo_dashboard'            => 'search',
            'wpseo_workouts'             => 'dumbbell',

            // === المنتور ===
            'elementor'                  => 'box',
            'edit.php?post_type=elementor_library' => 'folder',

            // === گرویتی فرمز ===
            'gf_edit_forms'              => 'clipboard-list',
            'gf_entries'                 => 'inbox',
            'gf_settings'                => 'settings',

            // === کانتکت فرم 7 ===
            'wpcf7'                      => 'mail',

            // === WP Forms ===
            'wpforms-overview'           => 'file-input',

            // === رنک مث سئو ===
            'rank-math'                  => 'bar-chart-2',

            // === ریدایرکشن ===
            'redirection.php'            => 'arrow-right-circle',

            // === کش‌ها ===
            'w3tc_dashboard'             => 'zap',
            'wp-rocket'                  => 'rocket',
            'litespeed'                  => 'gauge',
            'wphb'                       => 'gauge',

            // === امنیت ===
            'wordfence'                  => 'shield',
            'sucuri'                     => 'lock',
            'itsec'                      => 'shield-check',

            // === بکاپ ===
            'updraftplus'                => 'hard-drive',
            'duplicator'                 => 'copy',
            'backwpup'                   => 'archive',

            // === سایر پلاگین‌ها ===
            'jetpack'                    => 'zap',
            'tablepress'                 => 'table',
            'smush'                      => 'image',
            'optinmonster'               => 'megaphone',
            'monsterinsights_reports'    => 'bar-chart',
            'mailchimp-for-wp'           => 'mail',
            'newsletter'                 => 'newspaper',
            'ninja-forms'                => 'file-edit',
            'formidable'                 => 'form-input',
            'customize-widgets'          => 'layout-grid',
            'site-editor.php'            => 'blocks',
        ];
    }

    /**
     * لود Lucide از CDN
     */
    public function enqueue_icons() {
        // استفاده از نسخه مشخص برای جلوگیری از کندی
        wp_enqueue_script(
            'lucide-icons',
            'https://unpkg.com/lucide@0.294.0/dist/umd/lucide.min.js',
            [],
            '0.294.0',
            true
        );
    }

    /**
     * CSS برای آیکون‌ها
     */
    public function output_icon_css() {
        ?>
        <style id="dst-lucide-icons-css">
            /* مخفی کردن dashicons در منو */
            body.admin-theme-active #adminmenu .wp-menu-image::before,
            body.admin-theme-active #adminmenu .wp-menu-image.dashicons-before::before {
                display: none !important;
                content: '' !important;
            }

            /* استایل برای آیکون‌های Lucide */
            body.admin-theme-active #adminmenu .wp-menu-image {
                position: relative !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            body.admin-theme-active #adminmenu .wp-menu-image svg.lucide {
                width: 20px !important;
                height: 20px !important;
                stroke: currentColor !important;
                stroke-width: 2 !important;
                fill: none !important;
                transition: all 0.2s ease !important;
            }

            body.admin-theme-active #adminmenu li.current > a .wp-menu-image svg.lucide,
            body.admin-theme-active #adminmenu li:hover > a .wp-menu-image svg.lucide {
                stroke: #fff !important;
            }

            /* رفع مشکل تصاویر آیکون (برای پلاگین‌هایی که از تصویر استفاده می‌کنند) */
            body.admin-theme-active #adminmenu .wp-menu-image img {
                padding: 0 !important;
                opacity: 0.8 !important;
                transition: opacity 0.2s !important;
            }

            body.admin-theme-active #adminmenu li:hover .wp-menu-image img,
            body.admin-theme-active #adminmenu li.current .wp-menu-image img {
                opacity: 1 !important;
            }

            /* === آیکون‌های Topbar === */
            body.admin-theme-active #wpadminbar .ab-icon::before,
            body.admin-theme-active #wpadminbar .ab-item::before {
                display: none !important;
            }

            body.admin-theme-active #wpadminbar .ab-icon {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 26px !important;
                height: 32px !important;
            }

            body.admin-theme-active #wpadminbar .ab-icon svg.lucide {
                width: 18px !important;
                height: 18px !important;
                stroke: currentColor !important;
                stroke-width: 2 !important;
                fill: none !important;
            }

            body.admin-theme-active #wpadminbar #wp-admin-bar-wp-logo .ab-icon svg.lucide {
                width: 20px !important;
                height: 20px !important;
            }

            /* حذف پس‌زمینه آیکون WP */
            body.admin-theme-active #wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon {
                background: none !important;
            }

            /* آیکون آواتار کاربر */
            body.admin-theme-active #wpadminbar #wp-admin-bar-my-account .avatar {
                margin: -2px 8px 0 0 !important;
            }
        </style>
        <?php
    }

    /**
     * JavaScript برای جایگزینی آیکون‌ها
     */
    public function output_icon_js() {
        $icon_map_json = wp_json_encode($this->icon_map);
        ?>
        <script id="dst-lucide-icons-js">
        (function() {
            'use strict';

            // نقشه آیکون‌ها
            const iconMap = <?php echo $icon_map_json; ?>;

            // نقشه پیش‌فرض بر اساس کلاس
            const classIconMap = {
                'dashicons-dashboard': 'layout-dashboard',
                'dashicons-admin-post': 'file-text',
                'dashicons-admin-media': 'image',
                'dashicons-admin-page': 'file',
                'dashicons-admin-comments': 'message-square',
                'dashicons-admin-appearance': 'palette',
                'dashicons-admin-plugins': 'plug',
                'dashicons-admin-users': 'users',
                'dashicons-admin-tools': 'wrench',
                'dashicons-admin-settings': 'settings',
                'dashicons-admin-network': 'globe',
                'dashicons-admin-home': 'home',
                'dashicons-admin-generic': 'circle',
                'dashicons-admin-collapse': 'chevrons-left',
                'dashicons-admin-site': 'globe-2',
                'dashicons-admin-customizer': 'brush',
                'dashicons-welcome-write-blog': 'pen-tool',
                'dashicons-format-standard': 'file-text',
                'dashicons-format-aside': 'file-minus',
                'dashicons-format-image': 'image',
                'dashicons-format-gallery': 'images',
                'dashicons-format-video': 'video',
                'dashicons-format-audio': 'music',
                'dashicons-format-chat': 'message-circle',
                'dashicons-format-quote': 'quote',
                'dashicons-format-status': 'alert-circle',
                'dashicons-camera': 'camera',
                'dashicons-chart-pie': 'pie-chart',
                'dashicons-chart-bar': 'bar-chart-2',
                'dashicons-chart-line': 'trending-up',
                'dashicons-chart-area': 'activity',
                'dashicons-screenoptions': 'grid-3x3',
                'dashicons-menu': 'menu',
                'dashicons-menu-alt': 'align-justify',
                'dashicons-menu-alt2': 'list',
                'dashicons-menu-alt3': 'menu',
                'dashicons-update': 'refresh-cw',
                'dashicons-update-alt': 'rotate-cw',
                'dashicons-redo': 'redo',
                'dashicons-undo': 'undo',
                'dashicons-backup': 'save',
                'dashicons-clock': 'clock',
                'dashicons-calendar': 'calendar',
                'dashicons-calendar-alt': 'calendar-days',
                'dashicons-visibility': 'eye',
                'dashicons-hidden': 'eye-off',
                'dashicons-lock': 'lock',
                'dashicons-unlock': 'unlock',
                'dashicons-shield': 'shield',
                'dashicons-shield-alt': 'shield-check',
                'dashicons-cart': 'shopping-cart',
                'dashicons-store': 'store',
                'dashicons-products': 'package',
                'dashicons-tag': 'tag',
                'dashicons-category': 'folder',
                'dashicons-archive': 'archive',
                'dashicons-tagcloud': 'tags',
                'dashicons-text': 'type',
                'dashicons-text-page': 'file-type',
                'dashicons-media-archive': 'file-archive',
                'dashicons-media-audio': 'file-audio',
                'dashicons-media-code': 'file-code',
                'dashicons-media-default': 'file',
                'dashicons-media-document': 'file-text',
                'dashicons-media-interactive': 'file-input',
                'dashicons-media-spreadsheet': 'file-spreadsheet',
                'dashicons-media-text': 'file-text',
                'dashicons-media-video': 'file-video',
                'dashicons-playlist-audio': 'list-music',
                'dashicons-playlist-video': 'list-video',
                'dashicons-plus': 'plus',
                'dashicons-plus-alt': 'plus-circle',
                'dashicons-plus-alt2': 'plus-square',
                'dashicons-minus': 'minus',
                'dashicons-dismiss': 'x',
                'dashicons-marker': 'map-pin',
                'dashicons-star-filled': 'star',
                'dashicons-star-half': 'star-half',
                'dashicons-star-empty': 'star',
                'dashicons-flag': 'flag',
                'dashicons-warning': 'alert-triangle',
                'dashicons-yes': 'check',
                'dashicons-yes-alt': 'check-circle',
                'dashicons-no': 'x',
                'dashicons-no-alt': 'x-circle',
                'dashicons-email': 'mail',
                'dashicons-email-alt': 'mail',
                'dashicons-email-alt2': 'send',
                'dashicons-phone': 'phone',
                'dashicons-smartphone': 'smartphone',
                'dashicons-tablet': 'tablet',
                'dashicons-desktop': 'monitor',
                'dashicons-laptop': 'laptop',
                'dashicons-ellipsis': 'more-horizontal',
                'dashicons-arrow-up': 'arrow-up',
                'dashicons-arrow-down': 'arrow-down',
                'dashicons-arrow-left': 'arrow-left',
                'dashicons-arrow-right': 'arrow-right',
                'dashicons-arrow-up-alt': 'chevron-up',
                'dashicons-arrow-down-alt': 'chevron-down',
                'dashicons-arrow-left-alt': 'chevron-left',
                'dashicons-arrow-right-alt': 'chevron-right',
                'dashicons-arrow-up-alt2': 'move-up',
                'dashicons-arrow-down-alt2': 'move-down',
                'dashicons-arrow-left-alt2': 'move-left',
                'dashicons-arrow-right-alt2': 'move-right',
                'dashicons-sort': 'arrow-up-down',
                'dashicons-leftright': 'move-horizontal',
                'dashicons-randomize': 'shuffle',
                'dashicons-list-view': 'list',
                'dashicons-exerpt-view': 'align-left',
                'dashicons-grid-view': 'grid',
                'dashicons-move': 'move',
                'dashicons-share': 'share-2',
                'dashicons-share-alt': 'share',
                'dashicons-share-alt2': 'external-link',
                'dashicons-rss': 'rss',
                'dashicons-facebook': 'facebook',
                'dashicons-facebook-alt': 'facebook',
                'dashicons-twitter': 'twitter',
                'dashicons-twitter-alt': 'twitter',
                'dashicons-instagram': 'instagram',
                'dashicons-linkedin': 'linkedin',
                'dashicons-pinterest': 'pin',
                'dashicons-youtube': 'youtube',
                'dashicons-google': 'search',
                'dashicons-wordpress': 'box',
                'dashicons-wordpress-alt': 'box',
                'dashicons-pressthis': 'bookmark',
                'dashicons-cloud': 'cloud',
                'dashicons-cloud-saved': 'cloud-off',
                'dashicons-cloud-upload': 'cloud-upload',
                'dashicons-download': 'download',
                'dashicons-upload': 'upload',
                'dashicons-hammer': 'hammer',
                'dashicons-art': 'paintbrush',
                'dashicons-migrate': 'arrow-right-circle',
                'dashicons-performance': 'gauge',
                'dashicons-universal-access': 'accessibility',
                'dashicons-universal-access-alt': 'user-check',
                'dashicons-tickets': 'ticket',
                'dashicons-nametag': 'badge',
                'dashicons-clipboard': 'clipboard',
                'dashicons-heart': 'heart',
                'dashicons-megaphone': 'megaphone',
                'dashicons-schedule': 'calendar-clock',
                'dashicons-tide': 'waves',
                'dashicons-rest-api': 'code',
                'dashicons-code-standards': 'braces',
                'dashicons-buddicons-activity': 'activity',
                'dashicons-buddicons-bbpress-logo': 'message-circle',
                'dashicons-buddicons-buddypress-logo': 'users',
                'dashicons-buddicons-community': 'users',
                'dashicons-buddicons-forums': 'messages-square',
                'dashicons-buddicons-friends': 'user-plus',
                'dashicons-buddicons-groups': 'users',
                'dashicons-buddicons-pm': 'mail',
                'dashicons-buddicons-replies': 'corner-up-left',
                'dashicons-buddicons-topics': 'file-text',
                'dashicons-buddicons-tracking': 'eye',
                'dashicons-building': 'building-2',
                'dashicons-businessman': 'briefcase',
                'dashicons-businesswoman': 'briefcase',
                'dashicons-businessperson': 'user',
                'dashicons-id': 'badge',
                'dashicons-id-alt': 'credit-card',
                'dashicons-money': 'dollar-sign',
                'dashicons-money-alt': 'banknote',
                'dashicons-bank': 'landmark',
                'dashicons-thumbs-up': 'thumbs-up',
                'dashicons-thumbs-down': 'thumbs-down',
                'dashicons-layout': 'layout',
                'dashicons-align-pull-left': 'align-horizontal-distribute-start',
                'dashicons-align-pull-right': 'align-horizontal-distribute-end',
                'dashicons-block-default': 'square',
                'dashicons-button': 'rectangle-horizontal',
                'dashicons-editor-paragraph': 'pilcrow',
                'dashicons-editor-table': 'table',
                'dashicons-embed-photo': 'image-plus',
                'dashicons-editor-ol': 'list-ordered',
                'dashicons-editor-ol-rtl': 'list-ordered',
                'dashicons-editor-ul': 'list',
                'dashicons-editor-quote': 'quote',
                'dashicons-editor-alignleft': 'align-left',
                'dashicons-editor-aligncenter': 'align-center',
                'dashicons-editor-alignright': 'align-right',
                'dashicons-editor-bold': 'bold',
                'dashicons-editor-italic': 'italic',
                'dashicons-editor-underline': 'underline',
                'dashicons-editor-justify': 'align-justify',
                'dashicons-editor-textcolor': 'palette',
                'dashicons-editor-paste-word': 'clipboard-paste',
                'dashicons-editor-paste-text': 'clipboard',
                'dashicons-editor-removeformatting': 'remove-formatting',
                'dashicons-editor-video': 'video',
                'dashicons-editor-customchar': 'asterisk',
                'dashicons-editor-outdent': 'outdent',
                'dashicons-editor-indent': 'indent',
                'dashicons-editor-help': 'help-circle',
                'dashicons-editor-strikethrough': 'strikethrough',
                'dashicons-editor-unlink': 'unlink',
                'dashicons-editor-rtl': 'arrow-right-left',
                'dashicons-editor-break': 'corner-down-left',
                'dashicons-editor-code': 'code',
                'dashicons-editor-kitchensink': 'more-horizontal',
                'dashicons-editor-ltr': 'arrow-left-right',
                'dashicons-align-left': 'align-left',
                'dashicons-align-right': 'align-right',
                'dashicons-align-center': 'align-center',
                'dashicons-align-none': 'align-justify',
                'dashicons-editor-insertmore': 'more-horizontal',
                'dashicons-editor-spellcheck': 'spell-check',
                'dashicons-editor-expand': 'maximize-2',
                'dashicons-editor-contract': 'minimize-2',
                'dashicons-heading': 'heading',
                'dashicons-info': 'info',
                'dashicons-info-outline': 'info',
                'dashicons-insert': 'plus',
                'dashicons-remove': 'minus',
                'dashicons-database': 'database',
                'dashicons-database-add': 'database',
                'dashicons-database-export': 'database',
                'dashicons-database-import': 'database',
                'dashicons-database-remove': 'database',
                'dashicons-database-view': 'database',
                'dashicons-filter': 'filter',
                'dashicons-admin-links': 'link',
                'dashicons-edit': 'edit',
                'dashicons-edit-page': 'file-edit',
                'dashicons-edit-large': 'pen',
                'dashicons-forms': 'file-input',
                'dashicons-slides': 'presentation',
                'dashicons-analytics': 'line-chart',
                'dashicons-testimonial': 'quote',
                'dashicons-portfolio': 'briefcase',
                'dashicons-book': 'book',
                'dashicons-book-alt': 'book-open',
                'dashicons-groups': 'users',
                'dashicons-location': 'map-pin',
                'dashicons-location-alt': 'navigation',
                'dashicons-images-alt': 'images',
                'dashicons-images-alt2': 'layout-grid',
                'dashicons-video-alt': 'video',
                'dashicons-video-alt2': 'film',
                'dashicons-video-alt3': 'clapperboard',
                'dashicons-vault': 'lock',
                'dashicons-microphone': 'mic',
                'dashicons-index-card': 'file-box',
                'dashicons-carrot': 'carrot',
                'dashicons-food': 'utensils',
                'dashicons-coffee': 'coffee',
                'dashicons-awards': 'award',
                'dashicons-superhero-alt': 'star',
                'dashicons-palmtree': 'tree-palm',
                'dashicons-pets': 'paw-print',
                'dashicons-games': 'gamepad-2',
                'dashicons-hourglass': 'hourglass',
                'dashicons-airplane': 'plane',
                'dashicons-car': 'car',
                'dashicons-sos': 'badge-alert',
                'dashicons-beer': 'beer',
                'dashicons-smiley': 'smile',
                'dashicons-lightbulb': 'lightbulb',
                'dashicons-html': 'code',
                'dashicons-color-picker': 'pipette',
                'dashicons-fullscreen-alt': 'maximize',
                'dashicons-fullscreen-exit-alt': 'minimize',
                'dashicons-networking': 'network',
                'dashicons-open-folder': 'folder-open',
                'dashicons-pdf': 'file-text',
                'dashicons-printer': 'printer',
                'dashicons-podio': 'box',
                'dashicons-spotify': 'music',
                'dashicons-amazon': 'shopping-cart',
                'dashicons-xing': 'link',
                'dashicons-whatsapp': 'message-circle',
                'dashicons-saved': 'check-circle',
                'dashicons-buddicons-pm': 'mail',
                'dashicons-table-col-after': 'table-columns-split',
                'dashicons-table-col-before': 'table-columns-split',
                'dashicons-table-col-delete': 'x',
                'dashicons-table-row-after': 'table-rows-split',
                'dashicons-table-row-before': 'table-rows-split',
                'dashicons-table-row-delete': 'x',
            };

            /**
             * پیدا کردن آیکون مناسب برای یک آیتم منو
             */
            function findIconForMenu(menuItem) {
                // 1. اول slug را بررسی کن
                const link = menuItem.querySelector('a');
                if (link) {
                    const href = link.getAttribute('href') || '';

                    // بررسی دقیق slug
                    for (const [slug, icon] of Object.entries(iconMap)) {
                        if (href.includes(slug) || href.endsWith(slug)) {
                            return icon;
                        }
                    }
                }

                // 2. بررسی ID منو
                const menuId = menuItem.getAttribute('id') || '';
                const idMappings = {
                    'menu-dashboard': 'layout-dashboard',
                    'menu-posts': 'file-text',
                    'menu-media': 'image',
                    'menu-pages': 'file',
                    'menu-comments': 'message-square',
                    'menu-appearance': 'palette',
                    'menu-plugins': 'plug',
                    'menu-users': 'users',
                    'menu-tools': 'wrench',
                    'menu-settings': 'settings',
                    'menu-modules': 'grid-3x3',
                };

                for (const [id, icon] of Object.entries(idMappings)) {
                    if (menuId.includes(id)) {
                        return icon;
                    }
                }

                // 3. بررسی کلاس dashicons
                const menuImage = menuItem.querySelector('.wp-menu-image');
                if (menuImage) {
                    const classes = menuImage.className.split(' ');
                    for (const cls of classes) {
                        if (classIconMap[cls]) {
                            return classIconMap[cls];
                        }
                    }
                }

                // 4. آیکون پیش‌فرض
                return 'circle';
            }

            /**
             * ساخت SVG آیکون
             */
            function createIconElement(iconName) {
                const icon = document.createElement('i');
                icon.setAttribute('data-lucide', iconName);
                return icon;
            }

            /**
             * جایگزینی آیکون‌های منوی اصلی
             */
            function replaceMenuIcons() {
                const menuItems = document.querySelectorAll('#adminmenu > li.menu-top');

                menuItems.forEach(menuItem => {
                    const menuImage = menuItem.querySelector('.wp-menu-image');
                    if (!menuImage || menuImage.dataset.lucideReplaced) return;

                    // بررسی اگر تصویر دارد (مثل ووکامرس)
                    const hasImg = menuImage.querySelector('img');
                    if (hasImg) {
                        menuImage.dataset.lucideReplaced = 'true';
                        return;
                    }

                    const iconName = findIconForMenu(menuItem);
                    const iconEl = createIconElement(iconName);
                    menuImage.appendChild(iconEl);
                    menuImage.dataset.lucideReplaced = 'true';
                });
            }

            /**
             * جایگزینی آیکون‌های Topbar
             */
            function replaceTopbarIcons() {
                const topbarIcons = [
                    { selector: '#wp-admin-bar-wp-logo .ab-icon', icon: 'box' },
                    { selector: '#wp-admin-bar-updates .ab-icon', icon: 'download-cloud' },
                    { selector: '#wp-admin-bar-comments .ab-icon', icon: 'message-square' },
                    { selector: '#wp-admin-bar-new-content .ab-icon', icon: 'plus' },
                    { selector: '#wp-admin-bar-edit .ab-icon', icon: 'edit' },
                    { selector: '#wp-admin-bar-view .ab-icon', icon: 'eye' },
                    { selector: '#wp-admin-bar-site-name .ab-icon', icon: 'home' },
                ];

                topbarIcons.forEach(({ selector, icon }) => {
                    const el = document.querySelector(selector);
                    if (el && !el.dataset.lucideReplaced) {
                        const iconEl = createIconElement(icon);
                        el.appendChild(iconEl);
                        el.dataset.lucideReplaced = 'true';
                    }
                });
            }

            /**
             * فعال‌سازی آیکون‌ها با Lucide
             */
            function initLucide() {
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons({
                        attrs: {
                            'class': ['lucide'],
                            'stroke-width': 2
                        }
                    });
                }
            }

            // جلوگیری از اجرای مکرر
            let isInitializing = false;
            let initTimeout = null;

            /**
             * اجرای اصلی با debounce
             */
            function init() {
                if (isInitializing) return;
                isInitializing = true;

                replaceMenuIcons();
                replaceTopbarIcons();
                initLucide();

                // اجازه اجرای مجدد بعد از 100ms
                setTimeout(function() {
                    isInitializing = false;
                }, 100);
            }

            /**
             * اجرای با تاخیر (debounced)
             */
            function debouncedInit() {
                if (initTimeout) clearTimeout(initTimeout);
                initTimeout = setTimeout(init, 50);
            }

            // اجرا بعد از لود DOM
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }

            // اجرای مجدد یک بار بعد از لود Lucide
            setTimeout(init, 500);
        })();
        </script>
        <?php
    }
}

new DST_Lucide_Icons();
