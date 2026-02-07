<?php
/**
 * Features Block
 * بلوک نمایش ویژگی‌ها با آیکون
 */

// ثبت بلوک
add_action('acf/init', function() {
    
    if (!function_exists('acf_register_block_type')) {
        return;
    }
    
    acf_register_block_type([
        'name'              => 'features',
        'title'             => __('Features Grid', 'dst'),
        'description'       => __('نمایش ویژگی‌ها در قالب Grid', 'dst'),
        'render_template'   => dirname(__FILE__) . '/render.php',
        'category'          => 'dst-blocks',
        'icon'              => 'grid-view',
        'keywords'          => ['features', 'services', 'benefits'],
        'supports'          => [
            'align' => ['wide', 'full'],
            'mode'  => true,
        ],
    ]);
});

// ثبت فیلدهای ACF
add_action('acf/init', function() {
    
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    
    acf_add_local_field_group([
        'key'    => 'group_features_block',
        'title'  => 'Features Block Settings',
        'fields' => [
            // عنوان بخش
            [
                'key'   => 'field_features_section_title',
                'label' => 'عنوان بخش',
                'name'  => 'section_title',
                'type'  => 'text',
            ],
            // توضیحات بخش
            [
                'key'   => 'field_features_section_desc',
                'label' => 'توضیحات',
                'name'  => 'section_description',
                'type'  => 'textarea',
                'rows'  => 2,
            ],
            // لیست ویژگی‌ها
            [
                'key'    => 'field_features_items',
                'label'  => 'ویژگی‌ها',
                'name'   => 'features',
                'type'   => 'repeater',
                'layout' => 'block',
                'button_label' => 'افزودن ویژگی',
                'sub_fields' => [
                    // آیکون
                    [
                        'key'           => 'field_feature_icon',
                        'label'         => 'آیکون',
                        'name'          => 'icon',
                        'type'          => 'select',
                        'choices'       => [
                            'check'       => '✓ تیک',
                            'star'        => '⭐ ستاره',
                            'rocket'      => '🚀 موشک',
                            'shield'      => '🛡️ سپر',
                            'lightning'   => '⚡ برق',
                            'heart'       => '❤️ قلب',
                            'fire'        => '🔥 آتش',
                            'trophy'      => '🏆 جام',
                        ],
                        'default_value' => 'check',
                    ],
                    // عنوان
                    [
                        'key'      => 'field_feature_title',
                        'label'    => 'عنوان',
                        'name'     => 'title',
                        'type'     => 'text',
                        'required' => 1,
                    ],
                    // توضیحات
                    [
                        'key'   => 'field_feature_description',
                        'label' => 'توضیحات',
                        'name'  => 'description',
                        'type'  => 'textarea',
                        'rows'  => 3,
                    ],
                ],
                'min'  => 1,
                'max'  => 12,
            ],
            // تنظیمات
            [
                'key'   => 'field_features_settings',
                'label' => 'تنظیمات',
                'name'  => 'settings',
                'type'  => 'group',
                'sub_fields' => [
                    [
                        'key'           => 'field_features_columns',
                        'label'         => 'تعداد ستون',
                        'name'          => 'columns',
                        'type'          => 'select',
                        'choices'       => [
                            '2' => '2 ستونی',
                            '3' => '3 ستونی',
                            '4' => '4 ستونی',
                        ],
                        'default_value' => '3',
                    ],
                    [
                        'key'           => 'field_features_style',
                        'label'         => 'استایل',
                        'name'          => 'style',
                        'type'          => 'select',
                        'choices'       => [
                            'card'    => 'کارت',
                            'minimal' => 'مینیمال',
                            'boxed'   => 'با Border',
                        ],
                        'default_value' => 'card',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/features',
                ],
            ],
        ],
    ]);
});
