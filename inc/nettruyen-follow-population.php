<?php
/**
 * NetTruyen Follow Population
 * Populate fake follows cho tất cả truyện — ghi vào wp_nettruyen_follow_stats.
 *
 * Mirrors hoàn toàn nettruyen-view-population.php:
 *   - Chỉ xử lý truyện CHƯA có fake_updated_at
 *   - ON DUPLICATE KEY UPDATE → GIỮ NGUYÊN real_follows
 *   - display = real + fake
 *
 * @package NetTruyen
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class NetTruyen_Follow_Population
{
    /**
     * Populate fake follows cho tất cả truyện CHƯA có fake_updated_at.
     *
     * @param int $batch_size Số truyện xử lý mỗi lần (default 100).
     * @return array
     */
    public static function populate_all($batch_size = 100)
    {
        global $wpdb;

        require_once get_template_directory() . '/inc/class-nettruyen-view-calculator.php';

        $follow_stats_table = $wpdb->prefix . 'nettruyen_follow_stats';

        // Tổng số comic
        $total_comics = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type   = 'nettruyen_comic'
               AND post_status IN ('publish','private')"
        );

        if ($total_comics === 0) {
            return [
                'success' => false,
                'processed' => 0,
                'total' => 0,
                'message' => 'No comics found',
            ];
        }

        // Lấy comic CHƯA có fake follow (chưa có row hoặc fake_updated_at IS NULL)
        $pending_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT p.ID
                 FROM {$wpdb->posts} p
                 LEFT JOIN {$follow_stats_table} fs ON p.ID = fs.post_id
                 WHERE p.post_type   = 'nettruyen_comic'
                   AND p.post_status IN ('publish','private')
                   AND (fs.post_id IS NULL OR fs.fake_updated_at IS NULL)
                 ORDER BY p.ID ASC
                 LIMIT %d",
                $batch_size
            )
        );

        if (empty($pending_ids)) {
            return [
                'success' => true,
                'processed' => 0,
                'total' => $total_comics,
                'message' => 'All comics already have fake follows.',
                'is_complete' => true,
            ];
        }

        $processed = 0;
        $errors = [];
        $skipped = [];

        foreach ($pending_ids as $post_id) {
            $post_id = (int) $post_id;

            try {
                $fake_follows = NetTruyen_View_Calculator::calculate_fake_follows($post_id);

                if ($fake_follows === 0) {
                    $fake_follows = 200;
                    $skipped[] = "Post {$post_id} – forced 200 follows (no data)";
                }

                // INSERT ... ON DUPLICATE KEY UPDATE
                // GIỮ NGUYÊN total_real_follows đã có
                // display = real hiện tại + fake mới
                $result = $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO {$follow_stats_table}
                            (post_id,
                             total_real_follows,
                             total_fake_follows,
                             total_display_follows,
                             use_fake_follows,
                             fake_updated_at)
                         VALUES (%d, 0, %d, %d, 1, %s)
                         ON DUPLICATE KEY UPDATE
                            total_fake_follows    = VALUES(total_fake_follows),
                            -- Tính lại display = real hiện tại + fake mới
                            total_display_follows = total_real_follows + VALUES(total_fake_follows),
                            use_fake_follows      = 1,
                            fake_updated_at       = VALUES(fake_updated_at)",
                        $post_id,
                        $fake_follows,
                        $fake_follows,           // display = fake (vì real = 0 khi INSERT mới)
                        current_time('mysql')
                    )
                );

                if ($result !== false) {
                    $processed++;
                } else {
                    $errors[] = "Failed post_id {$post_id}: " . $wpdb->last_error;
                }

            } catch (Exception $e) {
                $errors[] = "Error post_id {$post_id}: " . $e->getMessage();
            }
        }

        // Đếm còn bao nhiêu pending
        $remaining = (int) $wpdb->get_var(
            "SELECT COUNT(*)
             FROM {$wpdb->posts} p
             LEFT JOIN {$follow_stats_table} fs ON p.ID = fs.post_id
             WHERE p.post_type   = 'nettruyen_comic'
               AND p.post_status IN ('publish','private')
               AND (fs.post_id IS NULL OR fs.fake_updated_at IS NULL)"
        );

        $is_complete = ($remaining === 0);

        $message = sprintf(
            'Processed %d comics. Remaining: %d / %d total.',
            $processed,
            $remaining,
            $total_comics
        );

        if (!empty($errors)) {
            $message .= ' Errors: ' . count($errors);
        }
        if (!empty($skipped)) {
            $message .= ' | Forced-minimum: ' . count($skipped);
        }

        return [
            'success' => true,
            'processed' => $processed,
            'remaining' => $remaining,
            'total' => $total_comics,
            'message' => $message,
            'is_complete' => $is_complete,
            'errors' => $errors,
            'skipped' => $skipped,
        ];
    }

    /**
     * Re-calculate fake follows cho 1 truyện cụ thể.
     * Giữ nguyên real_follows, chỉ cập nhật fake + display.
     *
     * @param int $post_id
     * @return bool
     */
    public static function recalculate_single($post_id)
    {
        global $wpdb;

        require_once get_template_directory() . '/inc/class-nettruyen-view-calculator.php';

        $fake_follows = NetTruyen_View_Calculator::calculate_fake_follows($post_id);
        $fake_follows = max(200, $fake_follows);
        $follow_stats_table = $wpdb->prefix . 'nettruyen_follow_stats';

        $result = $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$follow_stats_table}
                    (post_id, total_real_follows, total_fake_follows, total_display_follows,
                     use_fake_follows, fake_updated_at)
                 VALUES (%d, 0, %d, %d, 1, %s)
                 ON DUPLICATE KEY UPDATE
                    total_fake_follows    = VALUES(total_fake_follows),
                    total_display_follows = total_real_follows + VALUES(total_fake_follows),
                    use_fake_follows      = 1,
                    fake_updated_at       = VALUES(fake_updated_at)",
                $post_id,
                $fake_follows,
                $fake_follows,
                current_time('mysql')
            )
        );

        return $result !== false;
    }

    /**
     * Reset toàn bộ fake follows (TRUNCATE bảng follow_stats).
     * Dùng khi muốn tính lại từ đầu.
     * Lưu ý: real_follows cũng bị xóa → cần chạy bulk_sync_all() sau đó.
     *
     * @return bool
     */
    public static function reset_all()
    {
        global $wpdb;
        $follow_stats_table = $wpdb->prefix . 'nettruyen_follow_stats';

        return $wpdb->query(
            "UPDATE {$follow_stats_table} SET
            total_fake_follows    = 0,
            total_display_follows = total_real_follows,
            use_fake_follows      = 0,
            fake_updated_at       = NULL"
        ) !== false;
    }
}