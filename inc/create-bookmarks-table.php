<?php
/**
 * Create Bookmarks Table
 * File: inc/create-bookmarks-table.php
 * 
 * @package TruyenQQ
 * @version 1.0.1
 * 
 * CHANGELOG:
 * - v1.0.1: Thêm hook tạo bảng follow_stats khi switch theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create bookmarks table
 */
function truyenqq_create_bookmarks_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'nettruyen_bookmarks';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID người dùng',
        `post_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID truyện',
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian thêm',
        PRIMARY KEY (`id`),
        UNIQUE KEY `user_post` (`user_id`, `post_id`),
        KEY `user_id` (`user_id`),
        KEY `post_id` (`post_id`),
        KEY `created_at` (`created_at`)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);


    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name) {
        error_log('TruyenQQ: Bookmarks table created successfully');
        return true;
    } else {
        error_log('TruyenQQ: Failed to create bookmarks table');
        return false;
    }
}

/**
 * Get bookmark count for a post
 * 
 * @param int $post_id Post ID
 * @return int Bookmark count
 */
function truyenqq_get_bookmark_count($post_id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'nettruyen_bookmarks';

    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE post_id = %d",
        $post_id
    ));

    return (int) $count;
}

/**
 * Check if current user has bookmarked a post
 * 
 * @param int $post_id Post ID
 * @return bool True if bookmarked
 */
function truyenqq_is_bookmarked($post_id)
{
    if (!is_user_logged_in()) {
        return false;
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'nettruyen_bookmarks';

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND post_id = %d",
        $user_id,
        $post_id
    ));

    return (int) $exists > 0;
}

/**
 * Update bookmark count in post meta (for display)
 * Runs daily via cron
 */
function truyenqq_update_bookmark_counts()
{
    global $wpdb;
    $table = $wpdb->prefix . 'nettruyen_bookmarks';


    $results = $wpdb->get_results(
        "SELECT post_id, COUNT(*) as count 
         FROM {$table} 
         GROUP BY post_id"
    );

    foreach ($results as $row) {
        update_post_meta($row->post_id, '_nettruyen_bookmark_count', $row->count);
    }

    error_log('TruyenQQ: Updated bookmark counts for ' . count($results) . ' posts');
}


add_action('truyenqq_daily_bookmark_update', 'truyenqq_update_bookmark_counts');

if (!wp_next_scheduled('truyenqq_daily_bookmark_update')) {
    wp_schedule_event(time(), 'daily', 'truyenqq_daily_bookmark_update');
}


// ── v1.0.1: Tạo cả 2 bảng khi switch theme ──────────────────────────────────
add_action('after_switch_theme', function () {

    // Bảng bookmarks (cũ - giữ nguyên)
    truyenqq_create_bookmarks_table();

    // Bảng follow_stats (mới)
    if (class_exists('NetTruyen_Follow_Migration')) {
        NetTruyen_Follow_Migration::run();
    }
});
// ── Kết thúc v1.0.1 ──────────────────────────────────────────────────────────