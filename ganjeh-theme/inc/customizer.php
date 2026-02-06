<?php
/**
 * Theme Customizer
 *
 * @package Ganjeh
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register customizer settings
 */
function ganjeh_customize_register($wp_customize) {

    // Ganjeh Settings Panel
    $wp_customize->add_panel('ganjeh_settings', [
        'title'    => __('تنظیمات گنجه مارکت', 'ganjeh'),
        'priority' => 30,
    ]);

    // === Logo Section ===
    $wp_customize->add_section('ganjeh_logo', [
        'title'    => __('لوگو', 'ganjeh'),
        'panel'    => 'ganjeh_settings',
        'priority' => 1,
    ]);

    // Logo Image
    $wp_customize->add_setting('ganjeh_logo', [
        'default'           => '',
        'sanitize_callback' => 'absint',
    ]);
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'ganjeh_logo', [
        'label'     => __('لوگوی سایت', 'ganjeh'),
        'section'   => 'ganjeh_logo',
        'mime_type' => 'image',
    ]));

    // Logo Width
    $wp_customize->add_setting('ganjeh_logo_width', [
        'default'           => '120',
        'sanitize_callback' => 'absint',
    ]);
    $wp_customize->add_control('ganjeh_logo_width', [
        'type'        => 'number',
        'label'       => __('عرض لوگو (پیکسل)', 'ganjeh'),
        'section'     => 'ganjeh_logo',
        'input_attrs' => [
            'min'  => 40,
            'max'  => 200,
            'step' => 5,
        ],
    ]);

    // === Colors Section ===
    $wp_customize->add_section('ganjeh_colors', [
        'title' => __('رنگ‌ها', 'ganjeh'),
        'panel' => 'ganjeh_settings',
    ]);

    // Primary Color
    $wp_customize->add_setting('ganjeh_primary_color', [
        'default'           => '#4CB050',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'ganjeh_primary_color', [
        'label'   => __('رنگ اصلی (طلایی)', 'ganjeh'),
        'section' => 'ganjeh_colors',
    ]));

    // Secondary Color
    $wp_customize->add_setting('ganjeh_secondary_color', [
        'default'           => '#2E7D32',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'ganjeh_secondary_color', [
        'label'   => __('رنگ ثانویه (سرمه‌ای)', 'ganjeh'),
        'section' => 'ganjeh_colors',
    ]));

    // === Promo Banner Section ===
    $wp_customize->add_section('ganjeh_promo', [
        'title' => __('بنر تبلیغاتی', 'ganjeh'),
        'panel' => 'ganjeh_settings',
    ]);

    // Promo Enabled
    $wp_customize->add_setting('ganjeh_promo_enabled', [
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ]);
    $wp_customize->add_control('ganjeh_promo_enabled', [
        'type'    => 'checkbox',
        'label'   => __('نمایش بنر تبلیغاتی', 'ganjeh'),
        'section' => 'ganjeh_promo',
    ]);

    // Promo Text
    $wp_customize->add_setting('ganjeh_promo_text', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('ganjeh_promo_text', [
        'type'    => 'text',
        'label'   => __('متن بنر', 'ganjeh'),
        'section' => 'ganjeh_promo',
    ]);

    // Promo Link
    $wp_customize->add_setting('ganjeh_promo_link', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('ganjeh_promo_link', [
        'type'    => 'url',
        'label'   => __('لینک بنر', 'ganjeh'),
        'section' => 'ganjeh_promo',
    ]);

    // === Slider Section ===
    $wp_customize->add_section('ganjeh_slider', [
        'title' => __('اسلایدر صفحه اصلی', 'ganjeh'),
        'panel' => 'ganjeh_settings',
    ]);

    // Slider Slides (up to 5)
    for ($i = 1; $i <= 5; $i++) {
        // Slide Image
        $wp_customize->add_setting("ganjeh_slider_image_$i", [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ]);
        $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, "ganjeh_slider_image_$i", [
            'label'   => sprintf(__('تصویر اسلاید %d', 'ganjeh'), $i),
            'section' => 'ganjeh_slider',
        ]));

        // Slide Link
        $wp_customize->add_setting("ganjeh_slider_link_$i", [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ]);
        $wp_customize->add_control("ganjeh_slider_link_$i", [
            'type'    => 'url',
            'label'   => sprintf(__('لینک اسلاید %d', 'ganjeh'), $i),
            'section' => 'ganjeh_slider',
        ]);

        // Slide Title
        $wp_customize->add_setting("ganjeh_slider_title_$i", [
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        $wp_customize->add_control("ganjeh_slider_title_$i", [
            'type'    => 'text',
            'label'   => sprintf(__('عنوان اسلاید %d', 'ganjeh'), $i),
            'section' => 'ganjeh_slider',
        ]);
    }

    // === Footer Section ===
    $wp_customize->add_section('ganjeh_footer', [
        'title' => __('فوتر', 'ganjeh'),
        'panel' => 'ganjeh_settings',
    ]);

    // Phone Number
    $wp_customize->add_setting('ganjeh_footer_phone', [
        'default'           => '021-12345678',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('ganjeh_footer_phone', [
        'type'    => 'text',
        'label'   => __('شماره تماس', 'ganjeh'),
        'section' => 'ganjeh_footer',
    ]);

    // Address
    $wp_customize->add_setting('ganjeh_footer_address', [
        'default'           => 'تهران، خیابان نمونه، پلاک ۱۲۳',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('ganjeh_footer_address', [
        'type'    => 'text',
        'label'   => __('آدرس', 'ganjeh'),
        'section' => 'ganjeh_footer',
    ]);

    // Email
    $wp_customize->add_setting('ganjeh_footer_email', [
        'default'           => 'info@ganjemarket.com',
        'sanitize_callback' => 'sanitize_email',
    ]);
    $wp_customize->add_control('ganjeh_footer_email', [
        'type'    => 'email',
        'label'   => __('ایمیل', 'ganjeh'),
        'section' => 'ganjeh_footer',
    ]);

    // === Social Links Section ===
    $wp_customize->add_section('ganjeh_social', [
        'title' => __('شبکه‌های اجتماعی', 'ganjeh'),
        'panel' => 'ganjeh_settings',
    ]);

    // Instagram
    $wp_customize->add_setting('ganjeh_social_instagram', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('ganjeh_social_instagram', [
        'type'    => 'url',
        'label'   => __('اینستاگرام', 'ganjeh'),
        'section' => 'ganjeh_social',
    ]);

    // Telegram
    $wp_customize->add_setting('ganjeh_social_telegram', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('ganjeh_social_telegram', [
        'type'    => 'url',
        'label'   => __('تلگرام', 'ganjeh'),
        'section' => 'ganjeh_social',
    ]);

    // WhatsApp
    $wp_customize->add_setting('ganjeh_social_whatsapp', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('ganjeh_social_whatsapp', [
        'type'    => 'url',
        'label'   => __('واتساپ', 'ganjeh'),
        'section' => 'ganjeh_social',
    ]);

    // === Location Section ===
    $wp_customize->add_section('ganjeh_location', [
        'title' => __('موقعیت', 'ganjeh'),
        'panel' => 'ganjeh_settings',
    ]);

    // Default City
    $wp_customize->add_setting('ganjeh_default_city', [
        'default'           => 'تهران',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('ganjeh_default_city', [
        'type'    => 'text',
        'label'   => __('شهر پیش‌فرض', 'ganjeh'),
        'section' => 'ganjeh_location',
    ]);
}
add_action('customize_register', 'ganjeh_customize_register');

/**
 * Output custom CSS from customizer
 */
function ganjeh_customizer_css() {
    $primary = get_theme_mod('ganjeh_primary_color', '#4CB050');
    $secondary = get_theme_mod('ganjeh_secondary_color', '#2E7D32');

    // Generate darker shade for hover states
    $primary_dark = ganjeh_adjust_brightness($primary, -20);
    ?>
    <style id="ganjeh-customizer-css">
        :root {
            --color-primary: <?php echo $primary; ?>;
            --color-primary-dark: <?php echo $primary_dark; ?>;
            --color-secondary: <?php echo $secondary; ?>;
        }
    </style>
    <?php
}
add_action('wp_head', 'ganjeh_customizer_css');

/**
 * Adjust color brightness
 */
function ganjeh_adjust_brightness($hex, $steps) {
    $hex = str_replace('#', '', $hex);

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));

    return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
}
