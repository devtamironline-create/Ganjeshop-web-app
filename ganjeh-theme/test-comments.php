<?php
/**
 * Test file to check comments in database
 * Access via: /wp-content/themes/ganjeh-theme/test-comments.php
 */

require_once('../../../wp-load.php');

global $wpdb;

echo "<h2>آخرین 10 کامنت در دیتابیس:</h2>";
echo "<pre dir='ltr' style='background:#f5f5f5; padding:20px; font-size:12px;'>";

$comments = $wpdb->get_results("SELECT * FROM {$wpdb->comments} ORDER BY comment_ID DESC LIMIT 10");

if (empty($comments)) {
    echo "هیچ کامنتی در دیتابیس نیست!";
} else {
    foreach ($comments as $c) {
        echo "ID: {$c->comment_ID}\n";
        echo "Post ID: {$c->comment_post_ID}\n";
        echo "Author: {$c->comment_author}\n";
        echo "Content: {$c->comment_content}\n";
        echo "Type: '{$c->comment_type}'\n";
        echo "Approved: {$c->comment_approved}\n";
        echo "Date: {$c->comment_date}\n";

        $rating = get_comment_meta($c->comment_ID, 'rating', true);
        echo "Rating Meta: " . ($rating ?: 'ندارد') . "\n";
        echo "----------------------------\n";
    }
}

echo "</pre>";

echo "<h2>اطلاعات جدول:</h2>";
echo "<pre dir='ltr' style='background:#f5f5f5; padding:20px;'>";
echo "Table name: {$wpdb->comments}\n";
echo "Table prefix: {$wpdb->prefix}\n";
echo "</pre>";
