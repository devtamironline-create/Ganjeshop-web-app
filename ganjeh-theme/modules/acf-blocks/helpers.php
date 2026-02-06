<?php
/**
 * ACF Blocks - Helper Functions
 * توابع کمکی برای بلوک‌ها
 */

if (!defined('ABSPATH')) exit;

/**
 * لود Assets بلوک
 *
 * @param string $block_name نام بلوک
 * @param bool $css لود CSS
 * @param bool $js لود JS
 */
if (!function_exists('dst_block_assets')) {
    function dst_block_assets($block_name, $css = true, $js = false) {
        $module = dst_get_module('acf-blocks');

        if (!$module) {
            return;
        }

        $block_url = $module['url'] . '/blocks/' . $block_name . '/assets';

        // لود CSS
        if ($css) {
            wp_enqueue_style(
                'dst-' . $block_name . '-block',
                $block_url . '/css/style.css',
                [],
                '1.0.0'
            );
        }

        // لود JS
        if ($js) {
            wp_enqueue_script(
                'dst-' . $block_name . '-block',
                $block_url . '/js/script.js',
                [],
                '1.0.0',
                true
            );
        }
    }
}

/**
 * دریافت کلاس‌های بلوک
 *
 * @param array $block اطلاعات بلوک
 * @param string $base_class کلاس پایه
 * @param array $extra_classes کلاس‌های اضافی
 * @return string
 */
if (!function_exists('dst_block_classes')) {
    function dst_block_classes($block, $base_class, $extra_classes = []) {
        $classes = [$base_class];

        // Custom Class
        if (!empty($block['className'])) {
            $classes[] = $block['className'];
        }

        // Alignment
        if (!empty($block['align'])) {
            $classes[] = 'align' . $block['align'];
        }

        // Extra Classes
        if (!empty($extra_classes)) {
            $classes = array_merge($classes, $extra_classes);
        }

        return implode(' ', $classes);
    }
}

/**
 * دریافت ID بلوک
 *
 * @param array $block اطلاعات بلوک
 * @param string $prefix پیشوند
 * @return string
 */
if (!function_exists('dst_block_id')) {
    function dst_block_id($block, $prefix = 'block') {
        return $prefix . '-' . $block['id'];
    }
}
