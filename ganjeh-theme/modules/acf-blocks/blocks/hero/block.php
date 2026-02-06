<?php
/**
 * Hero Block
 * بلوک Hero Section با تصویر و متن
 */

// ثبت بلوک
add_action('acf/init', function() {
    
    // چک کردن تابع
    if (!function_exists('acf_register_block_type')) {
        return;
    }
    
    // ثبت بلوک
    acf_register_block_type([
        'name'              => 'hero',
        'title'             => __('Hero Section', 'dst'),
        'description'       => __('بخش Hero با تصویر و متن', 'dst'),
        'render_template'   => dirname(__FILE__) . '/render.php',
        'category'          => 'dst-blocks',
        'icon'              => 'cover-image',
        'keywords'          => ['hero', 'banner', 'header'],
        'supports'          => [
            'align'     => ['wide', 'full'],
            'mode'      => true,
            'jsx'       => true,
        ],
        'example'           => [
            'attributes' => [
                'mode' => 'preview',
                'data' => [
                    'preview_image' => get_template_directory_uri() . '/modules/acf-blocks/blocks/hero/preview.jpg',
                ],
            ],
        ],
    ]);
});

// ثبت فیلدهای ACF
add_action('acf/init', function() {
    
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    
    acf_add_local_field_group([
        'key'       => 'group_hero_block',
        'title'     => 'Hero Block Settings',
        'fields'    => [
            // عنوان
            [
                'key'           => 'field_hero_title',
                'label'         => 'عنوان',
                'name'          => 'title',
                'type'          => 'text',
                'required'      => 1,
                'default_value' => 'میزبانی وب حرفه‌ای',
            ],
            // توضیحات
            [
                'key'           => 'field_hero_description',
                'label'         => 'توضیحات',
                'name'          => 'description',
                'type'          => 'textarea',
                'rows'          => 3,
                'default_value' => 'بهترین سرویس میزبانی برای کسب و کارتان',
            ],
            // دکمه اول
            [
                'key'   => 'field_hero_button_1',
                'label' => 'دکمه اصلی',
                'name'  => 'button_1',
                'type'  => 'group',
                'layout' => 'block',
                'sub_fields' => [
                    [
                        'key'           => 'field_hero_button_1_text',
                        'label'         => 'متن',
                        'name'          => 'text',
                        'type'          => 'text',
                        'default_value' => 'شروع کنید',
                    ],
                    [
                        'key'   => 'field_hero_button_1_link',
                        'label' => 'لینک',
                        'name'  => 'link',
                        'type'  => 'url',
                    ],
                    [
                        'key'           => 'field_hero_button_1_style',
                        'label'         => 'استایل',
                        'name'          => 'style',
                        'type'          => 'select',
                        'choices'       => [
                            'primary'   => 'آبی (Primary)',
                            'secondary' => 'خاکستری (Secondary)',
                            'outline'   => 'Outline',
                        ],
                        'default_value' => 'primary',
                    ],
                ],
            ],
            // دکمه دوم (اختیاری)
            [
                'key'   => 'field_hero_button_2',
                'label' => 'دکمه ثانویه (اختیاری)',
                'name'  => 'button_2',
                'type'  => 'group',
                'layout' => 'block',
                'sub_fields' => [
                    [
                        'key'   => 'field_hero_button_2_text',
                        'label' => 'متن',
                        'name'  => 'text',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_hero_button_2_link',
                        'label' => 'لینک',
                        'name'  => 'link',
                        'type'  => 'url',
                    ],
                    [
                        'key'           => 'field_hero_button_2_style',
                        'label'         => 'استایل',
                        'name'          => 'style',
                        'type'          => 'select',
                        'choices'       => [
                            'primary'   => 'آبی (Primary)',
                            'secondary' => 'خاکستری (Secondary)',
                            'outline'   => 'Outline',
                        ],
                        'default_value' => 'outline',
                    ],
                ],
            ],
            // تصویر
            [
                'key'           => 'field_hero_image',
                'label'         => 'تصویر',
                'name'          => 'image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ],
            // تنظیمات پیشرفته
            [
                'key'   => 'field_hero_settings',
                'label' => 'تنظیمات',
                'name'  => 'settings',
                'type'  => 'group',
                'layout' => 'block',
                'sub_fields' => [
                    [
                        'key'           => 'field_hero_height',
                        'label'         => 'ارتفاع',
                        'name'          => 'height',
                        'type'          => 'select',
                        'choices'       => [
                            'small'  => 'کوچک (400px)',
                            'medium' => 'متوسط (600px)',
                            'large'  => 'بزرگ (800px)',
                        ],
                        'default_value' => 'medium',
                    ],
                    [
                        'key'           => 'field_hero_alignment',
                        'label'         => 'تراز محتوا',
                        'name'          => 'alignment',
                        'type'          => 'select',
                        'choices'       => [
                            'left'   => 'چپ',
                            'center' => 'وسط',
                            'right'  => 'راست',
                        ],
                        'default_value' => 'right',
                    ],
                    [
                        'key'           => 'field_hero_bg_color',
                        'label'         => 'رنگ پس‌زمینه',
                        'name'          => 'bg_color',
                        'type'          => 'color_picker',
                        'default_value' => '#F1F5F9',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/hero',
                ],
            ],
        ],
    ]);
});
