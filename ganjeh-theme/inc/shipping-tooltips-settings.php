<?php
/**
 * Shipping Tooltips Settings
 * تنظیمات اطلاعات تکمیلی روش‌های ارسال
 */

if (!defined('ABSPATH')) exit;

/**
 * Get shipping tooltips with defaults
 */
function ganjeh_get_shipping_tooltips() {
    $defaults = [
        'post'       => 'سفارش شما از طریق پست پیشتاز ارسال می‌شود. زمان تحویل ۳ تا ۷ روز کاری بسته به شهر مقصد.',
        'express'    => 'پیک موتوری در سریع‌ترین زمان ممکن سفارش شما را تحویل می‌دهد. فقط مناطق ۲۲ گانه تهران. ارسال شنبه تا چهارشنبه از ساعت ۹ الی ۱۷ و پنج‌شنبه‌ها از ساعت ۹ الی ۱۴ (روزهای کاری ایران و متصل به تقویم).',
        'collection' => 'سفارش شما توسط پیک مجموعه ارسال می‌شود. زمان تحویل حداکثر ۵ روز کاری، فقط مناطق ۲۲ گانه تهران.',
        'pickup'     => 'می‌توانید سفارش خود را حضوری از آدرس مجموعه تحویل بگیرید. حداقل ۲۴ ساعت بعد از ثبت سفارش.',
    ];

    $saved = get_option('ganjeh_shipping_tooltips', []);

    return wp_parse_args($saved, $defaults);
}

/**
 * Register admin menu
 */
add_action('admin_menu', 'ganjeh_shipping_tooltips_menu', 10001);
function ganjeh_shipping_tooltips_menu() {
    add_submenu_page(
        'dst-website-settings',
        __('نکات روش‌های ارسال', 'ganjeh'),
        __('نکات ارسال', 'ganjeh'),
        'manage_options',
        'ganjeh-shipping-tooltips',
        'ganjeh_shipping_tooltips_page'
    );
}

/**
 * Render settings page
 */
function ganjeh_shipping_tooltips_page() {
    // Save
    if (isset($_POST['ganjeh_shipping_tooltips_save']) && check_admin_referer('ganjeh_shipping_tooltips_nonce')) {
        $tooltips = [
            'post'       => sanitize_textarea_field($_POST['tooltip_post'] ?? ''),
            'express'    => sanitize_textarea_field($_POST['tooltip_express'] ?? ''),
            'collection' => sanitize_textarea_field($_POST['tooltip_collection'] ?? ''),
            'pickup'     => sanitize_textarea_field($_POST['tooltip_pickup'] ?? ''),
        ];

        update_option('ganjeh_shipping_tooltips', $tooltips);
        echo '<div class="notice notice-success"><p>' . __('تنظیمات ذخیره شد.', 'ganjeh') . '</p></div>';
    }

    $tooltips = ganjeh_get_shipping_tooltips();

    $methods = [
        'post'       => ['label' => 'ارسال از طریق پست', 'icon' => '📦'],
        'express'    => ['label' => 'پیک فوری در تهران', 'icon' => '🏍️'],
        'collection' => ['label' => 'ارسال عادی', 'icon' => '🚚'],
        'pickup'     => ['label' => 'تحویل حضوری', 'icon' => '🏪'],
    ];
    ?>
    <div class="wrap">
        <h1><?php _e('تنظیمات نکات روش‌های ارسال', 'ganjeh'); ?></h1>
        <p class="description" style="font-size: 14px; margin-bottom: 20px;">
            <?php _e('متن‌هایی که در صفحه پرداخت با هاور روی آیکون ℹ نمایش داده می‌شوند.', 'ganjeh'); ?>
        </p>

        <form method="post" action="">
            <?php wp_nonce_field('ganjeh_shipping_tooltips_nonce'); ?>

            <style>
                .ganjeh-tooltip-card {
                    background: #fff;
                    border: 1px solid #e2e8f0;
                    border-radius: 12px;
                    padding: 20px 24px;
                    margin-bottom: 16px;
                    transition: box-shadow 0.2s;
                }
                .ganjeh-tooltip-card:hover {
                    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                }
                .ganjeh-tooltip-card h3 {
                    margin: 0 0 12px;
                    font-size: 15px;
                    color: #1f2937;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .ganjeh-tooltip-card textarea {
                    width: 100%;
                    min-height: 80px;
                    padding: 12px;
                    border: 1px solid #d1d5db;
                    border-radius: 8px;
                    font-size: 14px;
                    line-height: 1.8;
                    resize: vertical;
                    direction: rtl;
                }
                .ganjeh-tooltip-card textarea:focus {
                    border-color: #4CB050;
                    outline: none;
                    box-shadow: 0 0 0 2px rgba(76,176,80,0.15);
                }
                .ganjeh-tooltip-cards {
                    max-width: 700px;
                }
            </style>

            <div class="ganjeh-tooltip-cards">
                <?php foreach ($methods as $key => $method) : ?>
                <div class="ganjeh-tooltip-card">
                    <h3>
                        <span><?php echo $method['icon']; ?></span>
                        <?php echo esc_html($method['label']); ?>
                    </h3>
                    <textarea name="tooltip_<?php echo esc_attr($key); ?>" rows="3"><?php echo esc_textarea($tooltips[$key]); ?></textarea>
                </div>
                <?php endforeach; ?>
            </div>

            <p class="submit">
                <button type="submit" name="ganjeh_shipping_tooltips_save" class="button button-primary" style="padding: 6px 24px; font-size: 14px;">
                    <?php _e('ذخیره تنظیمات', 'ganjeh'); ?>
                </button>
            </p>
        </form>
    </div>
    <?php
}
