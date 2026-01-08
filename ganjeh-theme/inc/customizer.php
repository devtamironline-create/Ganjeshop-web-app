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

    // === Colors Section ===
    $wp_customize->add_section('ganjeh_colors', [
        'title' => __('رنگ‌ها', 'ganjeh'),
        'panel' => 'ganjeh_settings',
    ]);

    // Primary Color
    $wp_customize->add_setting('ganjeh_primary_color', [
        'default'           => '#C9A227',
        'sanitize_callback' => 'sanitize_hex_color',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'ganjeh_primary_color', [
        'label'   => __('رنگ اصلی (طلایی)', 'ganjeh'),
        'section' => 'ganjeh_colors',
    ]));

    // Secondary Color
    $wp_customize->add_setting('ganjeh_secondary_color', [
        'default'           => '#1E3A5F',
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

    // === Social Links Section ===
    $wp_customize->add_section('ganjeh_social', [
        'title' => __('شبکه‌های اجتماعی', 'ganjeh'),
        'panel' => 'ganjeh_settings',
    ]);

    // Instagram
    $wp_customize->add_setting('ganjeh_instagram', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('ganjeh_instagram', [
        'type'    => 'url',
        'label'   => __('اینستاگرام', 'ganjeh'),
        'section' => 'ganjeh_social',
    ]);

    // Telegram
    $wp_customize->add_setting('ganjeh_telegram', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('ganjeh_telegram', [
        'type'    => 'url',
        'label'   => __('تلگرام', 'ganjeh'),
        'section' => 'ganjeh_social',
    ]);

    // WhatsApp
    $wp_customize->add_setting('ganjeh_whatsapp', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('ganjeh_whatsapp', [
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
    $primary = get_theme_mod('ganjeh_primary_color', '#C9A227');
    $secondary = get_theme_mod('ganjeh_secondary_color', '#1E3A5F');

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
