<?php
/**
 * Notification Bar Component
 * Displays a notification/announcement bar above the header
 *
 * @package Ganjeh
 */

$notif_enabled = get_theme_mod('ganjeh_notification_enabled', false);
$notif_text    = get_theme_mod('ganjeh_notification_text', '');
$notif_link    = get_theme_mod('ganjeh_notification_link', '');
$notif_bg      = get_theme_mod('ganjeh_notification_bg_color', '#1e293b');
$notif_color   = get_theme_mod('ganjeh_notification_text_color', '#ffffff');
$notif_dismiss = get_theme_mod('ganjeh_notification_dismissible', true);

if (!$notif_enabled || empty($notif_text)) {
    return;
}
?>

<div class="ganjeh-notification-bar"
     x-data="{ dismissed: localStorage.getItem('ganjeh_notif_dismissed') === '<?php echo md5($notif_text); ?>' }"
     x-show="!dismissed"
     x-cloak
     style="background-color: <?php echo esc_attr($notif_bg); ?>; color: <?php echo esc_attr($notif_color); ?>;">
    <div class="ganjeh-notification-content">
        <?php if ($notif_link) : ?>
            <a href="<?php echo esc_url($notif_link); ?>" class="ganjeh-notification-link" style="color: <?php echo esc_attr($notif_color); ?>;">
                <span class="ganjeh-notification-icon">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </span>
                <span><?php echo esc_html($notif_text); ?></span>
            </a>
        <?php else : ?>
            <div class="ganjeh-notification-text-wrap">
                <span class="ganjeh-notification-icon">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </span>
                <span><?php echo esc_html($notif_text); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($notif_dismiss) : ?>
            <button class="ganjeh-notification-close"
                    @click="dismissed = true; localStorage.setItem('ganjeh_notif_dismissed', '<?php echo md5($notif_text); ?>')"
                    aria-label="<?php _e('بستن', 'ganjeh'); ?>">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        <?php endif; ?>
    </div>
</div>

<style>
.ganjeh-notification-bar {
    width: 100%;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.4;
}
.ganjeh-notification-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 16px;
    gap: 8px;
}
.ganjeh-notification-link,
.ganjeh-notification-text-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    flex: 1;
    min-width: 0;
}
.ganjeh-notification-link span,
.ganjeh-notification-text-wrap span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ganjeh-notification-icon {
    flex-shrink: 0;
    display: flex;
    align-items: center;
}
.ganjeh-notification-close {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border: none;
    background: rgba(255,255,255,0.15);
    border-radius: 6px;
    color: inherit;
    cursor: pointer;
    transition: background 0.2s;
}
.ganjeh-notification-close:hover {
    background: rgba(255,255,255,0.25);
}
</style>
