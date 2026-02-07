<?php
/**
 * صفحه تنظیمات قالب
 * Theme Settings Page
 * 
 * @package Developer_Starter
 */

defined('ABSPATH') || exit;

function dst_render_theme_settings_page() {
    $settings = get_option('dst_theme_settings', dst_get_default_theme_settings());
    
    // ذخیره تنظیمات
    if (isset($_POST['dst_save_theme_settings'])) {
        check_admin_referer('dst_theme_settings_nonce');
        $settings = dst_save_theme_settings($_POST);
        echo '<div class="notice notice-success is-dismissible"><p>✅ تنظیمات ذخیره شد!</p></div>';
    }
    ?>
    <div class="wrap dst-theme-settings">
        <h1>🎨 تنظیمات قالب</h1>
        <p class="description">تنظیمات ظاهری و رنگ‌بندی قالب</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('dst_theme_settings_nonce'); ?>
            
            <div class="dst-settings-grid">
                
                <!-- لوگو و هویت بصری -->
                <div class="dst-settings-box">
                    <h2>🏠 لوگو و هویت بصری</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th>لوگو سایت</th>
                            <td>
                                <div class="dst-logo-uploader">
                                    <?php if (!empty($settings['logo_url'])): ?>
                                    <img id="logo-preview" src="<?php echo esc_url($settings['logo_url']); ?>" style="max-width: 200px; display: block; margin-bottom: 10px;">
                                    <?php else: ?>
                                    <img id="logo-preview" src="" style="max-width: 200px; display: none; margin-bottom: 10px;">
                                    <?php endif; ?>
                                    
                                    <input type="hidden" name="logo_url" id="logo_url" value="<?php echo esc_attr($settings['logo_url'] ?? ''); ?>">
                                    <button type="button" class="button" id="upload-logo">📤 انتخاب لوگو</button>
                                    <button type="button" class="button" id="remove-logo" style="<?php echo empty($settings['logo_url']) ? 'display:none;' : ''; ?>">🗑️ حذف لوگو</button>
                                </div>
                                <p class="description">لوگوی اصلی سایت (برای header)</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>عنوان سایت</th>
                            <td>
                                <input type="text" name="site_title" value="<?php echo esc_attr($settings['site_title'] ?? get_bloginfo('name')); ?>" class="regular-text">
                                <p class="description">عنوان نمایشی سایت</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>توضیحات سایت</th>
                            <td>
                                <input type="text" name="site_description" value="<?php echo esc_attr($settings['site_description'] ?? get_bloginfo('description')); ?>" class="regular-text">
                                <p class="description">توضیحات کوتاه سایت</p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- رنگ‌بندی -->
                <div class="dst-settings-box">
                    <h2>🎨 رنگ‌بندی</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th>رنگ اصلی</th>
                            <td>
                                <input type="text" name="primary_color" value="<?php echo esc_attr($settings['primary_color'] ?? '#3C50E0'); ?>" class="color-picker">
                                <p class="description">رنگ اصلی سایت (دکمه‌ها، لینک‌ها)</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>رنگ ثانویه</th>
                            <td>
                                <input type="text" name="secondary_color" value="<?php echo esc_attr($settings['secondary_color'] ?? '#10b981'); ?>" class="color-picker">
                                <p class="description">رنگ ثانویه برای برجسته‌سازی</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>رنگ متن</th>
                            <td>
                                <input type="text" name="text_color" value="<?php echo esc_attr($settings['text_color'] ?? '#1e293b'); ?>" class="color-picker">
                                <p class="description">رنگ متن اصلی</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th>رنگ لینک</th>
                            <td>
                                <input type="text" name="link_color" value="<?php echo esc_attr($settings['link_color'] ?? '#3C50E0'); ?>" class="color-picker">
                                <p class="description">رنگ لینک‌ها</p>
                            </td>
                        </tr>
                    </table>
                </div>
                
            </div>
            
            <p class="submit">
                <button type="submit" name="dst_save_theme_settings" class="button button-primary button-large">
                    💾 ذخیره تنظیمات
                </button>
            </p>
        </form>
    </div>
    
    <style>
        .dst-theme-settings {
            max-width: 1200px;
        }
        
        .dst-settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        
        .dst-settings-box {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 8px;
            padding: 20px;
        }
        
        .dst-settings-box h2 {
            margin-top: 0;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f1;
        }
        
        .dst-logo-uploader {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }
        
        @media (max-width: 1200px) {
            .dst-settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Color Picker
        $('.color-picker').wpColorPicker();
        
        // Media Uploader
        var mediaUploader;
        
        $('#upload-logo').click(function(e) {
            e.preventDefault();
            
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            mediaUploader = wp.media({
                title: 'انتخاب لوگو',
                button: {
                    text: 'انتخاب این تصویر'
                },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#logo_url').val(attachment.url);
                $('#logo-preview').attr('src', attachment.url).show();
                $('#remove-logo').show();
            });
            
            mediaUploader.open();
        });
        
        $('#remove-logo').click(function(e) {
            e.preventDefault();
            $('#logo_url').val('');
            $('#logo-preview').hide();
            $(this).hide();
        });
    });
    </script>
    <?php
}

/**
 * تنظیمات پیش‌فرض
 */
function dst_get_default_theme_settings() {
    return [
        'logo_url' => '',
        'site_title' => get_bloginfo('name'),
        'site_description' => get_bloginfo('description'),
        'primary_color' => '#3C50E0',
        'secondary_color' => '#10b981',
        'text_color' => '#1e293b',
        'link_color' => '#3C50E0',
    ];
}

/**
 * ذخیره تنظیمات
 */
function dst_save_theme_settings($post) {
    $settings = [
        'logo_url' => esc_url_raw($post['logo_url'] ?? ''),
        'site_title' => sanitize_text_field($post['site_title'] ?? ''),
        'site_description' => sanitize_text_field($post['site_description'] ?? ''),
        'primary_color' => sanitize_hex_color($post['primary_color'] ?? '#3C50E0'),
        'secondary_color' => sanitize_hex_color($post['secondary_color'] ?? '#10b981'),
        'text_color' => sanitize_hex_color($post['text_color'] ?? '#1e293b'),
        'link_color' => sanitize_hex_color($post['link_color'] ?? '#3C50E0'),
    ];
    
    update_option('dst_theme_settings', $settings);
    return $settings;
}
