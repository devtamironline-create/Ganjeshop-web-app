<?php
/**
 * Features Block - Template
 */

// دریافت فیلدها
$section_title = get_field('section_title');
$section_description = get_field('section_description');
$features = get_field('features');
$settings = get_field('settings');

// تنظیمات
$columns = $settings['columns'] ?? '3';
$style = $settings['style'] ?? 'card';

// کلاس‌ها
$class_name = 'dst-features-block';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}
$class_name .= ' dst-features-columns-' . $columns;
$class_name .= ' dst-features-style-' . $style;

// اگر ویژگی نداریم
if (!$features) {
    echo '<p>لطفاً ویژگی‌ها را اضافه کنید.</p>';
    return;
}
?>

<section class="<?php echo esc_attr($class_name); ?>">
    <?php if ($section_title || $section_description): ?>
        <div class="dst-features-header">
            <?php if ($section_title): ?>
                <h2 class="dst-features-title">
                    <?php echo esc_html($section_title); ?>
                </h2>
            <?php endif; ?>
            
            <?php if ($section_description): ?>
                <p class="dst-features-description">
                    <?php echo esc_html($section_description); ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="dst-features-grid">
        <?php foreach ($features as $feature): ?>
            <div class="dst-feature-item">
                <div class="dst-feature-icon">
                    <?php echo $feature['icon']; ?>
                </div>
                
                <h3 class="dst-feature-title">
                    <?php echo esc_html($feature['title']); ?>
                </h3>
                
                <?php if (!empty($feature['description'])): ?>
                    <p class="dst-feature-description">
                        <?php echo esc_html($feature['description']); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<style>
/* Features Block */
.dst-features-block {
    padding: 80px 20px;
    background: #ffffff;
}

/* Header */
.dst-features-header {
    text-align: center;
    max-width: 800px;
    margin: 0 auto 60px;
}

.dst-features-title {
    font-size: 42px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 16px;
}

.dst-features-description {
    font-size: 18px;
    color: #64748b;
    line-height: 1.6;
}

/* Grid */
.dst-features-grid {
    display: grid;
    gap: 32px;
    max-width: 1200px;
    margin: 0 auto;
}

.dst-features-columns-2 .dst-features-grid {
    grid-template-columns: repeat(2, 1fr);
}

.dst-features-columns-3 .dst-features-grid {
    grid-template-columns: repeat(3, 1fr);
}

.dst-features-columns-4 .dst-features-grid {
    grid-template-columns: repeat(4, 1fr);
}

/* Feature Item */
.dst-feature-item {
    text-align: center;
    padding: 32px 24px;
    transition: all 0.3s ease;
}

/* Style: Card */
.dst-features-style-card .dst-feature-item {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.dst-features-style-card .dst-feature-item:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

/* Style: Minimal */
.dst-features-style-minimal .dst-feature-item {
    background: transparent;
}

.dst-features-style-minimal .dst-feature-item:hover {
    transform: scale(1.02);
}

/* Style: Boxed */
.dst-features-style-boxed .dst-feature-item {
    background: #F8FAFC;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
}

.dst-features-style-boxed .dst-feature-item:hover {
    border-color: #3C50E0;
    background: #ffffff;
}

/* Icon */
.dst-feature-icon {
    font-size: 48px;
    margin-bottom: 20px;
    line-height: 1;
}

/* Title */
.dst-feature-title {
    font-size: 20px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 12px;
}

/* Description */
.dst-feature-description {
    font-size: 15px;
    color: #64748b;
    line-height: 1.6;
}

/* Responsive */
@media (max-width: 992px) {
    .dst-features-columns-4 .dst-features-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .dst-features-block {
        padding: 60px 20px;
    }
    
    .dst-features-title {
        font-size: 32px;
    }
    
    .dst-features-grid {
        grid-template-columns: 1fr !important;
        gap: 24px;
    }
}
</style>
