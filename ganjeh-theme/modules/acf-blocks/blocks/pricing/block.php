<?php
/**
 * Pricing Block
 * بلوک نمایش پلن‌های قیمت‌گذاری
 */

// ثبت بلوک
add_action('acf/init', function() {
    
    if (!function_exists('acf_register_block_type')) {
        return;
    }
    
    acf_register_block_type([
        'name'              => 'pricing',
        'title'             => __('Pricing Plans', 'dst'),
        'description'       => __('نمایش پلن‌های قیمت‌گذاری', 'dst'),
        'render_template'   => dirname(__FILE__) . '/render.php',
        'category'          => 'dst-blocks',
        'icon'              => 'cart',
        'keywords'          => ['pricing', 'plans', 'price'],
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
        'key'    => 'group_pricing_block',
        'title'  => 'Pricing Block Settings',
        'fields' => [
            // عنوان بخش
            [
                'key'   => 'field_pricing_section_title',
                'label' => 'عنوان بخش',
                'name'  => 'section_title',
                'type'  => 'text',
                'default_value' => 'پلن‌های قیمت‌گذاری',
            ],
            // پلن‌ها
            [
                'key'    => 'field_pricing_plans',
                'label'  => 'پلن‌ها',
                'name'   => 'plans',
                'type'   => 'repeater',
                'layout' => 'block',
                'button_label' => 'افزودن پلن',
                'sub_fields' => [
                    // نام پلن
                    [
                        'key'      => 'field_plan_name',
                        'label'    => 'نام پلن',
                        'name'     => 'name',
                        'type'     => 'text',
                        'required' => 1,
                    ],
                    // قیمت
                    [
                        'key'      => 'field_plan_price',
                        'label'    => 'قیمت',
                        'name'     => 'price',
                        'type'     => 'text',
                        'required' => 1,
                        'prepend'  => '$',
                    ],
                    // دوره
                    [
                        'key'           => 'field_plan_period',
                        'label'         => 'دوره',
                        'name'          => 'period',
                        'type'          => 'text',
                        'default_value' => 'ماهانه',
                        'prepend'       => '/',
                    ],
                    // توضیحات
                    [
                        'key'   => 'field_plan_description',
                        'label' => 'توضیحات',
                        'name'  => 'description',
                        'type'  => 'textarea',
                        'rows'  => 2,
                    ],
                    // ویژگی‌ها
                    [
                        'key'    => 'field_plan_features',
                        'label'  => 'ویژگی‌ها',
                        'name'   => 'features',
                        'type'   => 'textarea',
                        'rows'   => 5,
                        'instructions' => 'هر ویژگی در یک خط',
                    ],
                    // دکمه
                    [
                        'key'           => 'field_plan_button_text',
                        'label'         => 'متن دکمه',
                        'name'          => 'button_text',
                        'type'          => 'text',
                        'default_value' => 'انتخاب پلن',
                    ],
                    [
                        'key'   => 'field_plan_button_link',
                        'label' => 'لینک دکمه',
                        'name'  => 'button_link',
                        'type'  => 'url',
                    ],
                    // پلن محبوب
                    [
                        'key'           => 'field_plan_featured',
                        'label'         => 'پلن محبوب؟',
                        'name'          => 'featured',
                        'type'          => 'true_false',
                        'ui'            => 1,
                        'default_value' => 0,
                    ],
                    // Badge
                    [
                        'key'               => 'field_plan_badge',
                        'label'             => 'Badge (پلن محبوب)',
                        'name'              => 'badge',
                        'type'              => 'text',
                        'default_value'     => 'محبوب',
                        'conditional_logic' => [
                            [
                                [
                                    'field'    => 'field_plan_featured',
                                    'operator' => '==',
                                    'value'    => '1',
                                ],
                            ],
                        ],
                    ],
                ],
                'min' => 1,
                'max' => 6,
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/pricing',
                ],
            ],
        ],
    ]);
});
