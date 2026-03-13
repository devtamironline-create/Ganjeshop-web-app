<?php
/**
 * Hero Block - Template
 */

// دریافت فیلدها
$title = get_field('title') ?: 'میزبانی وب حرفه‌ای';
$description = get_field('description') ?: 'بهترین سرویس میزبانی';
$button_1 = get_field('button_1');
$button_2 = get_field('button_2');
$image = get_field('image');
$settings = get_field('settings');

// تنظیمات
$height = $settings['height'] ?? 'medium';
$alignment = $settings['alignment'] ?? 'right';
$bg_color = $settings['bg_color'] ?? '#F1F5F9';

// کلاس‌ها و ID
$classes = dst_block_classes($block, 'dst-hero-block', [
    'dst-hero-' . $height,
    'dst-hero-align-' . $alignment
]);
$block_id = dst_block_id($block, 'dst-hero');

// لود Assets
dst_block_assets('hero', true, true);
?>

<section 
    id="<?php echo esc_attr($block_id); ?>" 
    class="<?php echo esc_attr($classes); ?>"
    style="background-color: <?php echo esc_attr($bg_color); ?>;"
>
    <div class="dst-hero-container">
        <div class="dst-hero-content">
            <h1 class="dst-hero-title">
                <?php echo esc_html($title); ?>
            </h1>
            
            <?php if ($description): ?>
                <p class="dst-hero-description">
                    <?php echo esc_html($description); ?>
                </p>
            <?php endif; ?>
            
            <?php if ($button_1 || $button_2): ?>
                <div class="dst-hero-buttons">
                    <?php if ($button_1 && $button_1['text']): ?>
                        <a 
                            href="<?php echo esc_url($button_1['link'] ?: '#'); ?>" 
                            class="dst-btn dst-btn-<?php echo esc_attr($button_1['style'] ?: 'primary'); ?>"
                        >
                            <?php echo esc_html($button_1['text']); ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($button_2 && $button_2['text']): ?>
                        <a 
                            href="<?php echo esc_url($button_2['link'] ?: '#'); ?>" 
                            class="dst-btn dst-btn-<?php echo esc_attr($button_2['style'] ?: 'outline'); ?>"
                        >
                            <?php echo esc_html($button_2['text']); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($image): ?>
            <div class="dst-hero-image">
                <img 
                    src="<?php echo esc_url($image['url']); ?>" 
                    alt="<?php echo esc_attr($image['alt'] ?: $title); ?>"
                    loading="lazy"
                >
            </div>
        <?php endif; ?>
    </div>
</section>
