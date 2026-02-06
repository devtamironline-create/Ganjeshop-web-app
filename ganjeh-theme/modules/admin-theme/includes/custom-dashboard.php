<?php
/**
 * Custom Dashboard
 * داشبورد اختصاصی با ویجت‌های حرفه‌ای
 */

if (!defined('ABSPATH')) exit;

class DST_Custom_Dashboard {
    
    public function __construct() {
        add_action('wp_dashboard_setup', [$this, 'remove_default_widgets']);
        add_action('wp_dashboard_setup', [$this, 'add_custom_widgets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_chart_library']);
    }
    
    /**
     * حذف ویجت‌های پیش‌فرض
     */
    public function remove_default_widgets() {
        // حذف همه ویجت‌های پیش‌فرض
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        
        // حذف Welcome Panel
        remove_action('welcome_panel', 'wp_welcome_panel');
    }
    
    /**
     * اضافه کردن ویجت‌های سفارشی
     */
    public function add_custom_widgets() {
        // ردیف 1: چارت بازدید (تمام عرض)
        wp_add_dashboard_widget(
            'dst_chart_visits',
            '📈 آمار بازدید (30 روز اخیر)',
            [$this, 'chart_visits_widget'],
            null,
            null,
            'normal',
            'high'
        );
        
        // ردیف 2: آمار کلی + آخرین پست‌ها
        wp_add_dashboard_widget(
            'dst_stats_overview',
            '📊 آمار کلی',
            [$this, 'stats_overview_widget'],
            null,
            null,
            'normal',
            'default'
        );
        
        wp_add_dashboard_widget(
            'dst_recent_posts',
            '📝 آخرین نوشته‌ها',
            [$this, 'recent_posts_widget'],
            null,
            null,
            'side',
            'default'
        );
        
        // ردیف 3: آخرین کاربران + آمار دسته‌بندی‌ها
        wp_add_dashboard_widget(
            'dst_recent_users',
            '👥 آخرین کاربران',
            [$this, 'recent_users_widget'],
            null,
            null,
            'normal',
            'low'
        );
        
        wp_add_dashboard_widget(
            'dst_categories_stats',
            '📁 آمار دسته‌بندی‌ها',
            [$this, 'categories_stats_widget'],
            null,
            null,
            'side',
            'low'
        );
        
        // ردیف 4: دسترسی سریع
        wp_add_dashboard_widget(
            'dst_quick_actions',
            '⚡ دسترسی سریع',
            [$this, 'quick_actions_widget'],
            null,
            null,
            'normal',
            'low'
        );
    }
    
    /**
     * لود کتابخانه Chart.js
     */
    public function enqueue_chart_library() {
        $screen = get_current_screen();
        if ($screen->id !== 'dashboard') {
            return;
        }
        
        wp_enqueue_script(
            'chartjs',
            get_template_directory_uri() . '/assets/js/vendor/chart.min.js',
            [],
            '4.4.0',
            true
        );
    }
    
    /**
     * ویجت آمار کلی
     */
    public function stats_overview_widget() {
        // دریافت آمار
        $posts_count = wp_count_posts('post')->publish;
        $pages_count = wp_count_posts('page')->publish;
        $users_count = count_users()['total_users'];
        $comments_count = wp_count_comments()->approved;
        
        ?>
        <div class="dst-stats-grid">
            <div class="dst-stat-card dst-stat-primary">
                <div class="dst-stat-icon">📝</div>
                <div class="dst-stat-info">
                    <div class="dst-stat-number"><?php echo number_format($posts_count); ?></div>
                    <div class="dst-stat-label">نوشته</div>
                </div>
            </div>
            
            <div class="dst-stat-card dst-stat-success">
                <div class="dst-stat-icon">📄</div>
                <div class="dst-stat-info">
                    <div class="dst-stat-number"><?php echo number_format($pages_count); ?></div>
                    <div class="dst-stat-label">برگه</div>
                </div>
            </div>
            
            <div class="dst-stat-card dst-stat-warning">
                <div class="dst-stat-icon">👥</div>
                <div class="dst-stat-info">
                    <div class="dst-stat-number"><?php echo number_format($users_count); ?></div>
                    <div class="dst-stat-label">کاربر</div>
                </div>
            </div>
            
            <div class="dst-stat-card dst-stat-info">
                <div class="dst-stat-icon">💬</div>
                <div class="dst-stat-info">
                    <div class="dst-stat-number"><?php echo number_format($comments_count); ?></div>
                    <div class="dst-stat-label">دیدگاه</div>
                </div>
            </div>
        </div>
        
        <style>
        .dst-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 16px;
            margin: -12px;
        }
        
        .dst-stat-card {
            background: var(--admin-bg-white);
            border: 1px solid var(--admin-border);
            border-radius: 8px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.2s ease;
        }
        
        .dst-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--admin-shadow-2);
        }
        
        .dst-stat-icon {
            font-size: 32px;
            line-height: 1;
        }
        
        .dst-stat-number {
            font-size: 24px;
            font-weight: 700;
            color: var(--admin-text-dark);
            line-height: 1;
            margin-bottom: 4px;
        }
        
        .dst-stat-label {
            font-size: 13px;
            color: var(--admin-text-body);
        }
        
        .dst-stat-primary .dst-stat-icon { opacity: 0.9; }
        .dst-stat-success .dst-stat-icon { opacity: 0.9; }
        .dst-stat-warning .dst-stat-icon { opacity: 0.9; }
        .dst-stat-info .dst-stat-icon { opacity: 0.9; }
        </style>
        <?php
    }
    
    /**
     * ویجت آخرین پست‌ها
     */
    public function recent_posts_widget() {
        $posts = get_posts([
            'posts_per_page' => 5,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        if (empty($posts)) {
            echo '<p style="color: var(--admin-text-light); text-align: center; padding: 20px;">هیچ نوشته‌ای یافت نشد</p>';
            return;
        }
        
        ?>
        <div class="dst-posts-list">
            <?php foreach ($posts as $post): ?>
                <div class="dst-post-item">
                    <div class="dst-post-content">
                        <a href="<?php echo get_edit_post_link($post->ID); ?>" class="dst-post-title">
                            <?php echo esc_html($post->post_title); ?>
                        </a>
                        <div class="dst-post-meta">
                            <span>👤 <?php echo get_the_author_meta('display_name', $post->post_author); ?></span>
                            <span>📅 <?php echo human_time_diff(get_the_time('U', $post), current_time('timestamp')); ?> پیش</span>
                            <span>💬 <?php echo get_comments_number($post->ID); ?> دیدگاه</span>
                        </div>
                    </div>
                    <span class="dst-post-status dst-status-<?php echo $post->post_status; ?>">
                        <?php echo $post->post_status === 'publish' ? '✓ منتشر شده' : '○ پیش‌نویس'; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <style>
        .dst-posts-list {
            margin: -12px;
        }
        
        .dst-post-item {
            padding: 16px 0;
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }
        
        .dst-post-item:last-child {
            border-bottom: none;
        }
        
        .dst-post-content {
            flex: 1;
        }
        
        .dst-post-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--admin-text-dark);
            text-decoration: none;
            display: block;
            margin-bottom: 8px;
        }
        
        .dst-post-title:hover {
            color: var(--admin-primary);
        }
        
        .dst-post-meta {
            display: flex;
            gap: 16px;
            font-size: 13px;
            color: var(--admin-text-body);
        }
        
        .dst-post-status {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .dst-status-publish {
            background: #D1FAE5;
            color: #065F46;
        }
        
        .dst-status-draft {
            background: #FEF3C7;
            color: #92400E;
        }
        </style>
        <?php
    }
    
    /**
     * ویجت آخرین کاربران
     */
    public function recent_users_widget() {
        $users = get_users([
            'number' => 5,
            'orderby' => 'registered',
            'order' => 'DESC'
        ]);
        
        if (empty($users)) {
            echo '<p style="color: var(--admin-text-light); text-align: center; padding: 20px;">هیچ کاربری یافت نشد</p>';
            return;
        }
        
        ?>
        <div class="dst-users-list">
            <?php foreach ($users as $user): 
                $user_info = get_userdata($user->ID);
                $post_count = count_user_posts($user->ID);
            ?>
                <div class="dst-user-item">
                    <div class="dst-user-avatar">
                        <?php echo get_avatar($user->ID, 48); ?>
                    </div>
                    <div class="dst-user-info">
                        <a href="<?php echo get_edit_user_link($user->ID); ?>" class="dst-user-name">
                            <?php echo esc_html($user->display_name); ?>
                        </a>
                        <div class="dst-user-meta">
                            <span>📧 <?php echo esc_html($user->user_email); ?></span>
                            <span>🗓️ عضویت: <?php echo human_time_diff(strtotime($user->user_registered), current_time('timestamp')); ?> پیش</span>
                        </div>
                    </div>
                    <div class="dst-user-stats">
                        <span class="dst-user-role"><?php echo $user_info->roles[0]; ?></span>
                        <span class="dst-user-posts"><?php echo $post_count; ?> نوشته</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <style>
        .dst-users-list {
            margin: -12px;
        }
        
        .dst-user-item {
            padding: 16px 0;
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            gap: 16px;
            align-items: center;
        }
        
        .dst-user-item:last-child {
            border-bottom: none;
        }
        
        .dst-user-avatar img {
            border-radius: 50%;
            border: 2px solid var(--admin-border);
        }
        
        .dst-user-info {
            flex: 1;
        }
        
        .dst-user-name {
            font-size: 15px;
            font-weight: 600;
            color: var(--admin-text-dark);
            text-decoration: none;
            display: block;
            margin-bottom: 6px;
        }
        
        .dst-user-name:hover {
            color: var(--admin-primary);
        }
        
        .dst-user-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 13px;
            color: var(--admin-text-body);
        }
        
        .dst-user-stats {
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .dst-user-role {
            padding: 4px 10px;
            background: var(--admin-primary-light);
            color: var(--admin-primary);
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .dst-user-posts {
            font-size: 12px;
            color: var(--admin-text-body);
            text-align: center;
        }
        </style>
        <?php
    }
    
    /**
     * ویجت چارت بازدید (دمو)
     */
    public function chart_visits_widget() {
        // دیتای نمونه - در پروژه واقعی از Google Analytics یا داده واقعی استفاده کن
        $days = [];
        $visits = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $days[] = date_i18n('j M', strtotime("-$i days"));
            $visits[] = rand(50, 200); // دیتای نمونه
        }
        
        ?>
        <div style="position: relative; height: 300px;">
            <canvas id="dst-chart-visits"></canvas>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('dst-chart-visits');
            if (!ctx) return;
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($days); ?>,
                    datasets: [{
                        label: 'بازدید',
                        data: <?php echo json_encode($visits); ?>,
                        borderColor: 'rgb(60, 80, 224)',
                        backgroundColor: 'rgba(60, 80, 224, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#e2e8f0'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * ویجت آمار دسته‌بندی‌ها
     */
    public function categories_stats_widget() {
        $categories = get_categories([
            'orderby' => 'count',
            'order' => 'DESC',
            'number' => 5,
            'hide_empty' => false
        ]);
        
        if (empty($categories)) {
            echo '<p style="color: var(--admin-text-light); text-align: center; padding: 20px;">هیچ دسته‌بندی یافت نشد</p>';
            return;
        }
        
        $total = array_sum(wp_list_pluck($categories, 'count'));
        
        ?>
        <div class="dst-categories-list">
            <?php foreach ($categories as $cat): 
                $percentage = $total > 0 ? round(($cat->count / $total) * 100) : 0;
            ?>
                <div class="dst-category-item">
                    <div class="dst-category-header">
                        <a href="<?php echo get_category_link($cat->term_id); ?>" class="dst-category-name">
                            <?php echo esc_html($cat->name); ?>
                        </a>
                        <span class="dst-category-count"><?php echo $cat->count; ?> نوشته</span>
                    </div>
                    <div class="dst-category-bar">
                        <div class="dst-category-progress" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <style>
        .dst-categories-list {
            margin: -12px;
        }
        
        .dst-category-item {
            padding: 12px 0;
            border-bottom: 1px solid var(--admin-border);
        }
        
        .dst-category-item:last-child {
            border-bottom: none;
        }
        
        .dst-category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .dst-category-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--admin-text-dark);
            text-decoration: none;
        }
        
        .dst-category-name:hover {
            color: var(--admin-primary);
        }
        
        .dst-category-count {
            font-size: 12px;
            color: var(--admin-text-body);
        }
        
        .dst-category-bar {
            height: 6px;
            background: var(--admin-bg-gray);
            border-radius: 3px;
            overflow: hidden;
        }
        
        .dst-category-progress {
            height: 100%;
            background: linear-gradient(90deg, var(--admin-primary) 0%, var(--admin-primary-hover) 100%);
            transition: width 0.5s ease;
        }
        </style>
        <?php
    }
    
    /**
     * ویجت دسترسی سریع
     */
    public function quick_actions_widget() {
        ?>
        <div class="dst-quick-actions">
            <a href="<?php echo admin_url('post-new.php'); ?>" class="dst-action-btn dst-action-primary">
                <span class="dst-action-icon">✏️</span>
                <span class="dst-action-text">نوشته جدید</span>
            </a>
            
            <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>" class="dst-action-btn dst-action-success">
                <span class="dst-action-icon">📄</span>
                <span class="dst-action-text">برگه جدید</span>
            </a>
            
            <a href="<?php echo admin_url('user-new.php'); ?>" class="dst-action-btn dst-action-warning">
                <span class="dst-action-icon">👤</span>
                <span class="dst-action-text">کاربر جدید</span>
            </a>
            
            <a href="<?php echo admin_url('edit-comments.php'); ?>" class="dst-action-btn dst-action-info">
                <span class="dst-action-icon">💬</span>
                <span class="dst-action-text">مدیریت دیدگاه‌ها</span>
            </a>
        </div>
        
        <style>
        .dst-quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin: -12px;
        }
        
        .dst-action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: var(--admin-bg-white);
            border: 2px solid var(--admin-border);
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .dst-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--admin-shadow-2);
            border-color: var(--admin-primary);
        }
        
        .dst-action-icon {
            font-size: 24px;
            line-height: 1;
        }
        
        .dst-action-text {
            font-size: 14px;
            font-weight: 600;
            color: var(--admin-text-dark);
        }
        </style>
        <?php
    }
}

new DST_Custom_Dashboard();
