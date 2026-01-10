<?php
/**
 * NetTruyen View Count System - Database Migration
 * 
 * Tạo 3 tables:
 * - wp_nettruyen_chapter_views (Chi tiết view từng chapter theo ngày)
 * - wp_nettruyen_comic_views (Tổng hợp view truyện theo ngày)
 * - wp_nettruyen_view_stats (Cache stats performance)
 * 
 * @package NetTruyen
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class NetTruyen_View_Migration
{

    /**
     * Run migration
     */
    public static function run()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Table 1: Chapter Views (Chi tiết)
        $table_chapter_views = $wpdb->prefix . 'nettruyen_chapter_views';
        $sql1 = "CREATE TABLE {$table_chapter_views} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT UNSIGNED NOT NULL COMMENT 'ID truyện',
            chapter_slug VARCHAR(50) NOT NULL COMMENT 'Slug chapter (từ manifest)',
            view_date DATE NOT NULL COMMENT 'Ngày xem',
            view_count INT UNSIGNED DEFAULT 0 COMMENT 'Số lượt xem thật',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_view (post_id, chapter_slug, view_date),
            INDEX idx_post_date (post_id, view_date),
            INDEX idx_chapter (chapter_slug),
            INDEX idx_date (view_date)
        ) ENGINE=InnoDB {$charset_collate};";

        dbDelta($sql1);

        // Table 2: Comic Views Summary (Aggregate)
        $table_comic_views = $wpdb->prefix . 'nettruyen_comic_views';
        $sql2 = "CREATE TABLE {$table_comic_views} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT UNSIGNED NOT NULL COMMENT 'ID truyện',
            view_date DATE NOT NULL COMMENT 'Ngày',
            view_count INT UNSIGNED DEFAULT 0 COMMENT 'Tổng view thật trong ngày',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_comic_date (post_id, view_date),
            INDEX idx_post (post_id),
            INDEX idx_date (view_date)
        ) ENGINE=InnoDB {$charset_collate};";

        dbDelta($sql2);

        // Table 3: View Statistics Cache
        $table_stats = $wpdb->prefix . 'nettruyen_view_stats';
        $sql3 = "CREATE TABLE {$table_stats} (
            post_id BIGINT UNSIGNED PRIMARY KEY,
            total_real_views BIGINT UNSIGNED DEFAULT 0 COMMENT 'Tổng view thật',
            total_fake_views BIGINT UNSIGNED DEFAULT 0 COMMENT 'View giả (tính từ công thức)',
            total_display_views BIGINT UNSIGNED DEFAULT 0 COMMENT 'View hiển thị (real + fake)',
            daily_views INT UNSIGNED DEFAULT 0 COMMENT 'View hôm nay',
            weekly_views INT UNSIGNED DEFAULT 0 COMMENT 'View 7 ngày',
            monthly_views INT UNSIGNED DEFAULT 0 COMMENT 'View 30 ngày',
            last_view_at DATETIME COMMENT 'Lần view cuối',
            use_fake_views TINYINT(1) DEFAULT 1 COMMENT '1=dùng fake, 0=chỉ dùng real',
            fake_updated_at DATETIME COMMENT 'Lần cuối tính fake views',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_display_views (total_display_views),
            INDEX idx_daily (daily_views),
            INDEX idx_weekly (weekly_views),
            INDEX idx_use_fake (use_fake_views)
        ) ENGINE=InnoDB {$charset_collate};";

        dbDelta($sql3);

        // Verify tables created
        $tables = array($table_chapter_views, $table_comic_views, $table_stats);
        $success = true;

        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
                $success = false;
                error_log("NetTruyen View: Failed to create table {$table}");
            }
        }

        if ($success) {
            // Set migration flag
            update_option('nettruyen_view_migration_version', '1.0.0');
            update_option('nettruyen_view_migration_date', current_time('mysql'));

            return true;
        }

        return false;
    }

    /**
     * Check if migration needed
     */
    public static function needs_migration()
    {
        return !get_option('nettruyen_view_migration_version');
    }

    /**
     * Rollback migration (xóa tables)
     */
    public static function rollback()
    {
        global $wpdb;

        $tables = array(
            $wpdb->prefix . 'nettruyen_chapter_views',
            $wpdb->prefix . 'nettruyen_comic_views',
            $wpdb->prefix . 'nettruyen_view_stats'
        );

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }

        delete_option('nettruyen_view_migration_version');
        delete_option('nettruyen_view_migration_date');

        return true;
    }
}