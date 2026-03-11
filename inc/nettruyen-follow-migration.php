<?php
/**
 * NetTruyen Follow Stats Migration
 * Tạo bảng wp_nettruyen_follow_stats
 *
 * Architecture giống hệt view stats:
 *   wp_nettruyen_bookmarks   → raw data (real follows per user)
 *   wp_nettruyen_follow_stats → cache display (real + fake tổng hợp)
 *
 * @package NetTruyen
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class NetTruyen_Follow_Migration
{
    /**
     * Tạo bảng follow_stats.
     * Gọi trong after_switch_theme hoặc admin migration tool.
     *
     * @return bool
     */
    public static function run()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nettruyen_follow_stats';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
            `post_id`              bigint(20) UNSIGNED NOT NULL COMMENT 'ID truyện',
            `total_real_follows`   bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Follow thật từ bookmarks table',
            `total_fake_follows`   bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Follow fake từ calculator',
            `total_display_follows` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Hiển thị = real + fake',
            `use_fake_follows`     tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = cộng fake vào display',
            `fake_updated_at`      datetime DEFAULT NULL COMMENT 'Lần cuối populate fake',
            `last_updated`         datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`post_id`),
            KEY `total_display_follows` (`total_display_follows`),
            KEY `fake_updated_at` (`fake_updated_at`)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        $exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;

        if ($exists) {
            error_log('NetTruyen: follow_stats table created/verified successfully.');
        } else {
            error_log('NetTruyen: FAILED to create follow_stats table. Last error: ' . $wpdb->last_error);
        }

        return $exists;
    }

    /**
     * Kiểm tra bảng đã tồn tại chưa.
     *
     * @return bool
     */
    public static function table_exists()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'nettruyen_follow_stats';
        return $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;
    }
}