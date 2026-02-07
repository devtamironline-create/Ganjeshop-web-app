<?php
/**
 * Pricing Block - Template
 */

// دریافت فیلدها
$section_title = get_field('section_title');
$plans = get_field('plans');

// کلاس‌ها
$class_name = 'dst-pricing-block';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}

// اگر پلن نداریم
if (!$plans) {
    echo '<p>لطفاً پلن‌ها را اضافه کنید.</p>';
    return;
}
?>

<section class="<?php echo esc_attr($class_name); ?>">
    <?php if ($section_title): ?>
        <div class="dst-pricing-header">
            <h2 class="dst-pricing-title">
                <?php echo esc_html($section_title); ?>
            </h2>
        </div>
    <?php endif; ?>
    
    <div class="dst-pricing-grid">
        <?php foreach ($plans as $plan): ?>
            <div class="dst-pricing-plan <?php echo $plan['featured'] ? 'dst-plan-featured' : ''; ?>">
                <?php if ($plan['featured'] && !empty($plan['badge'])): ?>
                    <div class="dst-plan-badge">
                        <?php echo esc_html($plan['badge']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="dst-plan-header">
                    <h3 class="dst-plan-name">
                        <?php echo esc_html($plan['name']); ?>
                    </h3>
                    
                    <?php if (!empty($plan['description'])): ?>
                        <p class="dst-plan-description">
                            <?php echo esc_html($plan['description']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <div class="dst-plan-price">
                    <span class="dst-plan-amount">
                        <?php echo esc_html($plan['price']); ?>
                    </span>
                    <?php if (!empty($plan['period'])): ?>
                        <span class="dst-plan-period">
                            / <?php echo esc_html($plan['period']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($plan['features'])): ?>
                    <ul class="dst-plan-features">
                        <?php 
                        $features = explode("\n", $plan['features']);
                        foreach ($features as $feature):
                            $feature = trim($feature);
                            if (empty($feature)) continue;
                        ?>
                            <li>
                                <span class="dst-feature-icon">✓</span>
                                <?php echo esc_html($feature); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <a 
                    href="<?php echo esc_url($plan['button_link'] ?: '#'); ?>" 
                    class="dst-plan-button"
                >
                    <?php echo esc_html($plan['button_text'] ?: 'انتخاب پلن'); ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<style>
/* Pricing Block */
.dst-pricing-block {
    padding: 80px 20px;
    background: #F8FAFC;
}

/* Header */
.dst-pricing-header {
    text-align: center;
    margin-bottom: 60px;
}

.dst-pricing-title {
    font-size: 42px;
    font-weight: 700;
    color: #1e293b;
}

/* Grid */
.dst-pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 32px;
    max-width: 1200px;
    margin: 0 auto;
}

/* Plan Card */
.dst-pricing-plan {
    background: #ffffff;
    border: 2px solid #E2E8F0;
    border-radius: 16px;
    padding: 40px 32px;
    text-align: center;
    position: relative;
    transition: all 0.3s ease;
}

.dst-pricing-plan:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
    border-color: #3C50E0;
}

/* Featured Plan */
.dst-plan-featured {
    border-color: #3C50E0;
    border-width: 3px;
    box-shadow: 0 8px 30px rgba(60, 80, 224, 0.15);
}

.dst-plan-featured:hover {
    transform: translateY(-12px);
    box-shadow: 0 16px 50px rgba(60, 80, 224, 0.2);
}

/* Badge */
.dst-plan-badge {
    position: absolute;
    top: -12px;
    right: 50%;
    transform: translateX(50%);
    background: #3C50E0;
    color: #ffffff;
    padding: 6px 20px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

/* Plan Header */
.dst-plan-header {
    margin-bottom: 24px;
}

.dst-plan-name {
    font-size: 24px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
}

.dst-plan-description {
    font-size: 14px;
    color: #64748b;
}

/* Price */
.dst-plan-price {
    margin-bottom: 32px;
}

.dst-plan-amount {
    font-size: 48px;
    font-weight: 700;
    color: #3C50E0;
    line-height: 1;
}

.dst-plan-period {
    font-size: 16px;
    color: #64748b;
}

/* Features */
.dst-plan-features {
    list-style: none;
    margin: 0 0 32px 0;
    padding: 0;
    text-align: right;
}

.dst-plan-features li {
    padding: 12px 0;
    border-bottom: 1px solid #F1F5F9;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 15px;
    color: #475569;
}

.dst-plan-features li:last-child {
    border-bottom: none;
}

.dst-feature-icon {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    background: #DCFCE7;
    color: #16A34A;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
}

/* Button */
.dst-plan-button {
    display: block;
    width: 100%;
    padding: 14px 32px;
    background: #3C50E0;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
}

.dst-plan-button:hover {
    background: #2d3eb8;
    transform: translateY(-2px);
}

.dst-plan-featured .dst-plan-button {
    background: #3C50E0;
    box-shadow: 0 4px 12px rgba(60, 80, 224, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .dst-pricing-block {
        padding: 60px 20px;
    }
    
    .dst-pricing-title {
        font-size: 32px;
    }
    
    .dst-pricing-grid {
        grid-template-columns: 1fr;
    }
}
</style>
