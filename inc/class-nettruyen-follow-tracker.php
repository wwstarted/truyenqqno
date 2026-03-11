<?php
/**
 * NetTruyen Follow Tracker
 * Cập nhật real follow count vào follow_stats table khi user bookmark/unbookmark.
 *
 * Flow:
 *   bookmarks-api.php (add/remove bookmark)
 *       ↓
 *   NetTruyen_Follow_Tracker::sync_follow_count($post_id)
 *       ↓ COUNT(*) từ wp_nettruyen_bookmarks
 *   wp_nettruyen_follow_stats.total_real_follows = real count
 *   wp_nettruyen_follow_stats.total_display_follows = real + fake
 *
 * Tương tự NetTruyen_View_Tracker::update_stats_cache()
 *
 * @package NetTruyen
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class NetTruyen_Follow_Tracker
{
    /**
     * Đồng bộ real follow count sau khi user bookmark hoặc unbookmark.
     *
     * Gọi hàm này từ bookmarks-api.php sau mỗi INSERT hoặc DELETE bookmark.
     * Đây là hàm duy nhất cần gọi từ bên ngoài.
     *
     * @param int $post_id ID truyện vừa được bookmark/unbookmark.
     * @return bool True nếu thành công.
     */
    public static function sync_follow_count($post_id)
    {
        global $wpdb;

        $post_id = (int) $post_id;
        $bookmarks_table = $wpdb->prefix . 'nettruyen_bookmarks';
        $stats_table = $wpdb->prefix . 'nettruyen_follow_stats';

        // 1. Đếm follow thật từ bookmarks table
        $real_follows = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$bookmarks_table} WHERE post_id = %d",
            $post_id
        ));

        // 2. Lấy fake_follows hiện tại (giữ nguyên, không tính lại)
        $fake_follows = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT total_fake_follows FROM {$stats_table} WHERE post_id = %d",
            $post_id
        ));

        // 3. Tính display = real + fake
        $display_follows = $real_follows + $fake_follows;

        // 4. Upsert vào stats table — GIỮ NGUYÊN fake_follows, chỉ cập nhật real + display
        $result = $wpdb->query($wpdb->prepare(
            "INSERT INTO {$stats_table}
                (post_id, total_real_follows, total_fake_follows, total_display_follows, last_updated)
             VALUES (%d, %d, 0, %d, %s)
             ON DUPLICATE KEY UPDATE
                total_real_follows    = VALUES(total_real_follows),
                -- Display = real mới + fake đang có (không clobber fake)
                total_display_follows = VALUES(total_real_follows) + total_fake_follows,
                last_updated          = VALUES(last_updated)",
            $post_id,
            $real_follows,
            $display_follows,
            current_time('mysql')
        ));

        return $result !== false;
    }

    /**
     * Batch lấy display follow count cho nhiều truyện.
     * Dùng trong template để tránh N+1 query — giống pattern view_stats.
     *
     * Cách dùng trong template:
     *   $follow_batch = NetTruyen_Follow_Tracker::get_display_follows_batch($post_ids);
     *   // trong loop:
     *   $follow_count = $follow_batch[$post_id] ?? 0;
     *
     * @param int[] $post_ids Mảng post ID.
     * @return array [post_id => total_display_follows]
     */
    public static function get_display_follows_batch(array $post_ids)
    {
        if (empty($post_ids)) {
            return [];
        }

        global $wpdb;

        $post_ids = array_map('intval', $post_ids);
        $stats_table = $wpdb->prefix . 'nettruyen_follow_stats';
        $placeholders = implode(',', array_fill(0, count($post_ids), '%d'));

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id, total_display_follows
                 FROM {$stats_table}
                 WHERE post_id IN ({$placeholders})",
                ...$post_ids
            )
        );

        $batch = [];
        foreach ($rows as $row) {
            $batch[(int) $row->post_id] = (int) $row->total_display_follows;
        }

        return $batch;
    }

    /**
     * Lấy display follow count cho 1 truyện (dùng trong single page).
     *
     * @param int $post_id
     * @return int
     */
    public static function get_display_follows($post_id)
    {
        $batch = self::get_display_follows_batch([(int) $post_id]);
        return $batch[(int) $post_id] ?? 0;
    }

    /**
     * Bulk sync — đếm lại real follows từ bookmarks cho TẤT CẢ truyện.
     * Dùng trong admin tool hoặc khi cần re-sync toàn bộ.
     *
     * @return int Số truyện đã được sync.
     */
    public static function bulk_sync_all()
    {
        global $wpdb;

        $bookmarks_table = $wpdb->prefix . 'nettruyen_bookmarks';
        $stats_table = $wpdb->prefix . 'nettruyen_follow_stats';

        // Đếm real follows từng truyện một lần duy nhất
        $rows = $wpdb->get_results(
            "SELECT post_id, COUNT(*) as real_count
             FROM {$bookmarks_table}
             GROUP BY post_id"
        );

        if (empty($rows)) {
            return 0;
        }

        $synced = 0;
        foreach ($rows as $row) {
            $post_id = (int) $row->post_id;
            $real_follows = (int) $row->real_count;

            $result = $wpdb->query($wpdb->prepare(
                "INSERT INTO {$stats_table}
                    (post_id, total_real_follows, total_fake_follows, total_display_follows, last_updated)
                 VALUES (%d, %d, 0, %d, %s)
                 ON DUPLICATE KEY UPDATE
                    total_real_follows    = VALUES(total_real_follows),
                    total_display_follows = VALUES(total_real_follows) + total_fake_follows,
                    last_updated          = VALUES(last_updated)",
                $post_id,
                $real_follows,
                $real_follows,   // display tạm = real; ON DUPLICATE sẽ tính đúng
                current_time('mysql')
            ));

            if ($result !== false) {
                $synced++;
            }
        }

        return $synced;
    }
}