<?php
/**
 * Create Reading History Table
 * File: inc/create-reading-history-table.php
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create reading history table
 */
function truyenqq_create_reading_history_table()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'nettruyen_reading_history';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` bigint(20) UNSIGNED NOT NULL,
        `post_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID của truyện',
        `chapter_slug` varchar(100) NOT NULL COMMENT 'Slug của chapter',
        `chapter_name` varchar(255) DEFAULT NULL COMMENT 'Tên chapter',
        `current_page` int(11) DEFAULT 0 COMMENT 'Trang đang đọc (0-based)',
        `total_pages` int(11) DEFAULT 0 COMMENT 'Tổng số trang',
        `read_count` int(11) DEFAULT 1 COMMENT 'Số lần đọc',
        `last_read_at` datetime NOT NULL COMMENT 'Lần đọc cuối',
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `user_post_chapter` (`user_id`, `post_id`, `chapter_slug`),
        KEY `user_id` (`user_id`),
        KEY `post_id` (`post_id`),
        KEY `last_read_at` (`last_read_at`)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Verify table creation
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name) {
        error_log('TruyenQQ: Reading history table created successfully');
        return true;
    } else {
        error_log('TruyenQQ: Failed to create reading history table');
        return false;
    }
}

/**
 * Auto-delete history older than 7 days
 */
function truyenqq_auto_delete_old_history()
{
    global $wpdb;
    $table = $wpdb->prefix . 'nettruyen_reading_history';

    $deleted = $wpdb->query(
        "DELETE FROM {$table} 
         WHERE last_read_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
    );

    if ($deleted !== false) {
        error_log("TruyenQQ: Auto-deleted {$deleted} old reading history records");
    }
}

// Schedule daily cleanup
add_action('truyenqq_daily_cleanup', 'truyenqq_auto_delete_old_history');

if (!wp_next_scheduled('truyenqq_daily_cleanup')) {
    wp_schedule_event(time(), 'daily', 'truyenqq_daily_cleanup');
}

// Create table on theme activation
add_action('after_switch_theme', 'truyenqq_create_reading_history_table');