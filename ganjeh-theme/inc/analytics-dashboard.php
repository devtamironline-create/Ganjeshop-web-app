<?php
/**
 * Analytics Dashboard - آمار بازدید و کاربران
 *
 * @package Ganjeh
 */

defined('ABSPATH') || exit;

// ─── Database Table Setup ───
function ganjeh_analytics_create_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'ganjeh_analytics';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        event_type VARCHAR(30) NOT NULL,
        event_date DATE NOT NULL,
        event_time DATETIME NOT NULL,
        user_id BIGINT(20) UNSIGNED DEFAULT 0,
        ip_address VARCHAR(45) DEFAULT '',
        source VARCHAR(50) DEFAULT '',
        device VARCHAR(20) DEFAULT '',
        page_url VARCHAR(500) DEFAULT '',
        PRIMARY KEY (id),
        KEY event_type (event_type),
        KEY event_date (event_date),
        KEY source (source)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
add_action('after_switch_theme', 'ganjeh_analytics_create_table');
add_action('admin_init', function() {
    if (get_option('ganjeh_analytics_db_version') !== '1.1') {
        ganjeh_analytics_create_table();
        update_option('ganjeh_analytics_db_version', '1.1');
    }
});

// ─── Detect Device Type ───
function ganjeh_detect_device() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/Mobile|Android|iPhone|iPad/i', $ua)) return 'mobile';
    if (preg_match('/Tablet|iPad/i', $ua)) return 'tablet';
    return 'desktop';
}

// ─── Track Page Visits ───
function ganjeh_track_visit() {
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) return;
    if (defined('REST_REQUEST') && REST_REQUEST) return;

    // Don't track bots
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/bot|crawl|spider|slurp|mediapartners/i', $ua)) return;

    global $wpdb;
    $table = $wpdb->prefix . 'ganjeh_analytics';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $now = current_time('mysql');
    $today = current_time('Y-m-d');

    // Rate limit: max 1 visit per IP per 5 minutes
    $recent = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE event_type = 'visit' AND ip_address = %s AND event_time > DATE_SUB(%s, INTERVAL 5 MINUTE)",
        $ip, $now
    ));
    if ($recent > 0) return;

    $wpdb->insert($table, [
        'event_type' => 'visit',
        'event_date' => $today,
        'event_time' => $now,
        'user_id'    => get_current_user_id(),
        'ip_address' => $ip,
        'source'     => ganjeh_detect_referrer_source(),
        'device'     => ganjeh_detect_device(),
        'page_url'   => esc_url_raw($_SERVER['REQUEST_URI'] ?? '/'),
    ]);
}
add_action('template_redirect', 'ganjeh_track_visit');

// ─── Detect Referrer Source ───
function ganjeh_detect_referrer_source() {
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if (empty($ref)) return 'direct';
    $host = parse_url($ref, PHP_URL_HOST) ?: '';
    $site_host = parse_url(home_url(), PHP_URL_HOST) ?: '';
    if ($host === $site_host) return 'internal';
    if (preg_match('/google\./i', $host)) return 'google';
    if (preg_match('/instagram\.com/i', $host)) return 'instagram';
    if (preg_match('/t\.me|telegram/i', $host)) return 'telegram';
    if (preg_match('/twitter\.com|x\.com/i', $host)) return 'twitter';
    if (preg_match('/facebook\.com/i', $host)) return 'facebook';
    return 'other';
}

// ─── Track Registrations ───
function ganjeh_track_registration($user_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'ganjeh_analytics';
    $wpdb->insert($table, [
        'event_type' => 'register',
        'event_date' => current_time('Y-m-d'),
        'event_time' => current_time('mysql'),
        'user_id'    => $user_id,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        'source'     => 'otp',
        'device'     => ganjeh_detect_device(),
    ]);
}
add_action('user_register', 'ganjeh_track_registration');

// ─── Track Logins ───
function ganjeh_track_login($user_login, $user = null) {
    if (!$user) $user = get_user_by('login', $user_login);
    if (!$user) return;

    global $wpdb;
    $table = $wpdb->prefix . 'ganjeh_analytics';
    $wpdb->insert($table, [
        'event_type' => 'login',
        'event_date' => current_time('Y-m-d'),
        'event_time' => current_time('mysql'),
        'user_id'    => $user->ID,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        'source'     => 'otp',
        'device'     => ganjeh_detect_device(),
    ]);
}
add_action('wp_login', 'ganjeh_track_login', 10, 2);

// ─── Admin Menu ───
function ganjeh_analytics_menu() {
    add_submenu_page(
        'dst-website-settings',
        'آمار سایت',
        'آمار سایت',
        'manage_options',
        'ganjeh-analytics',
        'ganjeh_analytics_page'
    );
}
add_action('admin_menu', 'ganjeh_analytics_menu', 10001);

// ─── AJAX: Get Analytics Data ───
function ganjeh_ajax_get_analytics() {
    if (!current_user_can('manage_options')) wp_send_json_error('unauthorized');

    global $wpdb;
    $table = $wpdb->prefix . 'ganjeh_analytics';
    $period = sanitize_text_field($_POST['period'] ?? '30');

    $days = intval($period);
    $start_date = date('Y-m-d', strtotime("-{$days} days"));
    $today = current_time('Y-m-d');

    // Summary counts
    $visits_total = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE event_type = 'visit' AND event_date >= %s", $start_date
    ));
    $visits_today = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE event_type = 'visit' AND event_date = %s", $today
    ));
    $registers_total = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE event_type = 'register' AND event_date >= %s", $start_date
    ));
    $registers_today = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE event_type = 'register' AND event_date = %s", $today
    ));
    $logins_total = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE event_type = 'login' AND event_date >= %s", $start_date
    ));
    $logins_today = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE event_type = 'login' AND event_date = %s", $today
    ));

    // Daily visits chart
    $visits_daily = $wpdb->get_results($wpdb->prepare(
        "SELECT event_date, COUNT(*) as count FROM $table WHERE event_type = 'visit' AND event_date >= %s GROUP BY event_date ORDER BY event_date",
        $start_date
    ));

    // Daily registrations chart
    $registers_daily = $wpdb->get_results($wpdb->prepare(
        "SELECT event_date, COUNT(*) as count FROM $table WHERE event_type = 'register' AND event_date >= %s GROUP BY event_date ORDER BY event_date",
        $start_date
    ));

    // Source breakdown
    $sources = $wpdb->get_results($wpdb->prepare(
        "SELECT source, COUNT(*) as count FROM $table WHERE event_type = 'visit' AND event_date >= %s GROUP BY source ORDER BY count DESC",
        $start_date
    ));

    // Device breakdown
    $devices = $wpdb->get_results($wpdb->prepare(
        "SELECT device, COUNT(*) as count FROM $table WHERE event_type = 'visit' AND event_date >= %s GROUP BY device ORDER BY count DESC",
        $start_date
    ));

    // Fill missing dates
    $visits_map = [];
    $registers_map = [];
    foreach ($visits_daily as $row) $visits_map[$row->event_date] = (int) $row->count;
    foreach ($registers_daily as $row) $registers_map[$row->event_date] = (int) $row->count;

    $labels = [];
    $visits_data = [];
    $registers_data = [];
    for ($i = $days; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $labels[] = date('m/d', strtotime($d));
        $visits_data[] = $visits_map[$d] ?? 0;
        $registers_data[] = $registers_map[$d] ?? 0;
    }

    $source_labels = [];
    $source_data = [];
    $source_names = [
        'direct' => 'ورود مستقیم',
        'google' => 'گوگل',
        'instagram' => 'اینستاگرام',
        'telegram' => 'تلگرام',
        'twitter' => 'توییتر',
        'facebook' => 'فیسبوک',
        'internal' => 'داخلی',
        'other' => 'سایر',
    ];
    foreach ($sources as $row) {
        $source_labels[] = $source_names[$row->source] ?? $row->source;
        $source_data[] = (int) $row->count;
    }

    $device_labels = [];
    $device_data = [];
    $device_names = ['mobile' => 'موبایل', 'desktop' => 'دسکتاپ', 'tablet' => 'تبلت'];
    foreach ($devices as $row) {
        $device_labels[] = $device_names[$row->device] ?? $row->device;
        $device_data[] = (int) $row->count;
    }

    wp_send_json_success([
        'summary' => [
            'visits_total' => $visits_total,
            'visits_today' => $visits_today,
            'registers_total' => $registers_total,
            'registers_today' => $registers_today,
            'logins_total' => $logins_total,
            'logins_today' => $logins_today,
        ],
        'charts' => [
            'labels' => $labels,
            'visits' => $visits_data,
            'registers' => $registers_data,
        ],
        'sources' => [
            'labels' => $source_labels,
            'data' => $source_data,
        ],
        'devices' => [
            'labels' => $device_labels,
            'data' => $device_data,
        ],
    ]);
}
add_action('wp_ajax_ganjeh_get_analytics', 'ganjeh_ajax_get_analytics');

// ─── Dashboard Page ───
function ganjeh_analytics_page() {
?>
<div class="wrap ganjeh-analytics-wrap" dir="rtl">
    <h1>آمار سایت</h1>

    <!-- Period Selector -->
    <div class="analytics-toolbar">
        <select id="analytics-period" onchange="loadAnalytics()">
            <option value="7">۷ روز اخیر</option>
            <option value="30" selected>۳۰ روز اخیر</option>
            <option value="90">۹۰ روز اخیر</option>
        </select>
        <button class="button button-primary" onclick="loadAnalytics()">بروزرسانی</button>
    </div>

    <!-- Summary Cards -->
    <div class="analytics-cards">
        <div class="analytics-card card-visits">
            <div class="card-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </div>
            <div class="card-info">
                <span class="card-label">بازدید</span>
                <span class="card-value" id="stat-visits-total">-</span>
                <span class="card-sub">امروز: <b id="stat-visits-today">-</b></span>
            </div>
        </div>
        <div class="analytics-card card-registers">
            <div class="card-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <div class="card-info">
                <span class="card-label">ثبت‌نام</span>
                <span class="card-value" id="stat-registers-total">-</span>
                <span class="card-sub">امروز: <b id="stat-registers-today">-</b></span>
            </div>
        </div>
        <div class="analytics-card card-logins">
            <div class="card-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            </div>
            <div class="card-info">
                <span class="card-label">ورود</span>
                <span class="card-value" id="stat-logins-total">-</span>
                <span class="card-sub">امروز: <b id="stat-logins-today">-</b></span>
            </div>
        </div>
    </div>

    <!-- Charts Row 1: Visits + Registrations -->
    <div class="analytics-charts-row">
        <div class="analytics-chart-box">
            <h3>نمودار بازدید</h3>
            <canvas id="chart-visits"></canvas>
        </div>
        <div class="analytics-chart-box">
            <h3>نمودار ثبت‌نام</h3>
            <canvas id="chart-registers"></canvas>
        </div>
    </div>

    <!-- Charts Row 2: Sources + Devices -->
    <div class="analytics-charts-row">
        <div class="analytics-chart-box">
            <h3>منبع ورود بازدیدکنندگان</h3>
            <canvas id="chart-sources"></canvas>
        </div>
        <div class="analytics-chart-box">
            <h3>نوع دستگاه</h3>
            <canvas id="chart-devices"></canvas>
        </div>
    </div>
</div>

<style>
.ganjeh-analytics-wrap { max-width: 1200px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, sans-serif; }
.ganjeh-analytics-wrap h1 { font-size: 22px; font-weight: 700; margin-bottom: 20px; }
.analytics-toolbar { display: flex; align-items: center; gap: 10px; margin-bottom: 24px; }
.analytics-toolbar select { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; min-width: 150px; }
.analytics-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 28px; }
.analytics-card { display: flex; align-items: center; gap: 16px; padding: 20px; background: white; border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
.card-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.card-visits .card-icon { background: #eff6ff; color: #3b82f6; }
.card-registers .card-icon { background: #f0fdf4; color: #22c55e; }
.card-logins .card-icon { background: #fefce8; color: #eab308; }
.card-info { display: flex; flex-direction: column; gap: 2px; }
.card-label { font-size: 13px; color: #6b7280; }
.card-value { font-size: 28px; font-weight: 800; color: #1f2937; line-height: 1.2; }
.card-sub { font-size: 12px; color: #9ca3af; }
.card-sub b { color: #4b5563; }
.analytics-charts-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
.analytics-chart-box { background: white; border-radius: 14px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
.analytics-chart-box h3 { font-size: 15px; font-weight: 700; color: #1f2937; margin: 0 0 16px; }
@media (max-width: 960px) {
    .analytics-cards { grid-template-columns: 1fr; }
    .analytics-charts-row { grid-template-columns: 1fr; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
let visitsChart, registersChart, sourcesChart, devicesChart;

function loadAnalytics() {
    const period = document.getElementById('analytics-period').value;
    const fd = new FormData();
    fd.append('action', 'ganjeh_get_analytics');
    fd.append('period', period);
    fd.append('_ajax_nonce', '<?php echo wp_create_nonce('ganjeh_analytics_nonce'); ?>');

    fetch(ajaxurl, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
        if (!res.success) return;
        const d = res.data;

        // Update cards
        document.getElementById('stat-visits-total').textContent = d.summary.visits_total.toLocaleString('fa-IR');
        document.getElementById('stat-visits-today').textContent = d.summary.visits_today.toLocaleString('fa-IR');
        document.getElementById('stat-registers-total').textContent = d.summary.registers_total.toLocaleString('fa-IR');
        document.getElementById('stat-registers-today').textContent = d.summary.registers_today.toLocaleString('fa-IR');
        document.getElementById('stat-logins-total').textContent = d.summary.logins_total.toLocaleString('fa-IR');
        document.getElementById('stat-logins-today').textContent = d.summary.logins_today.toLocaleString('fa-IR');

        // Visits Line Chart
        if (visitsChart) visitsChart.destroy();
        visitsChart = new Chart(document.getElementById('chart-visits'), {
            type: 'line',
            data: {
                labels: d.charts.labels,
                datasets: [{
                    label: 'بازدید',
                    data: d.charts.visits,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#3b82f6',
                    borderWidth: 2,
                }]
            },
            options: chartLineOptions()
        });

        // Registers Line Chart
        if (registersChart) registersChart.destroy();
        registersChart = new Chart(document.getElementById('chart-registers'), {
            type: 'line',
            data: {
                labels: d.charts.labels,
                datasets: [{
                    label: 'ثبت‌نام',
                    data: d.charts.registers,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#22c55e',
                    borderWidth: 2,
                }]
            },
            options: chartLineOptions()
        });

        // Sources Doughnut
        if (sourcesChart) sourcesChart.destroy();
        sourcesChart = new Chart(document.getElementById('chart-sources'), {
            type: 'doughnut',
            data: {
                labels: d.sources.labels,
                datasets: [{
                    data: d.sources.data,
                    backgroundColor: ['#3b82f6','#22c55e','#f59e0b','#8b5cf6','#06b6d4','#ef4444','#6b7280','#ec4899'],
                    borderWidth: 0,
                }]
            },
            options: chartDoughnutOptions()
        });

        // Devices Doughnut
        if (devicesChart) devicesChart.destroy();
        devicesChart = new Chart(document.getElementById('chart-devices'), {
            type: 'doughnut',
            data: {
                labels: d.devices.labels,
                datasets: [{
                    data: d.devices.data,
                    backgroundColor: ['#3b82f6','#22c55e','#f59e0b'],
                    borderWidth: 0,
                }]
            },
            options: chartDoughnutOptions()
        });
    });
}

function chartLineOptions() {
    return {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } },
            x: { ticks: { maxRotation: 0, autoSkipPadding: 12 } }
        }
    };
}

function chartDoughnutOptions() {
    return {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 16, font: { size: 13 } } }
        }
    };
}

// Load on page ready
document.addEventListener('DOMContentLoaded', loadAnalytics);
</script>
<?php
}
