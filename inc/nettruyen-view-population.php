<?php
/**
 * NetTruyen View Population
 * Populate fake views cho tất cả truyện hiện có
 *
 * @package NetTruyen
 * @version 1.0.3
 * 
 * CHANGELOG:
 * - FIX 1: populate_all() không còn xóa real_views khi re-run
 * - FIX 2: đếm comic bằng trực tiếp SQL thay vì wp_count_posts()
 *          để tránh bỏ sót comic ở status khác (private, custom...)
 * - FIX 2b: chỉ xử lý comic CHƯA có fake_views thay vì dùng offset
 *            → không bao giờ bị "kẹt" ở giữa chừng
 * - v1.0.3: Thêm populate fake follow count (_nettruyen_follow_count post meta)
 *            → Hoàn toàn độc lập, không ảnh hưởng fake views system
 */

if (!defined('ABSPATH')) {
    exit;
}

class NetTruyen_View_Population
{

    /**
     * Populate fake views cho tất cả truyện CHƯA có fake_views.
     *
     * Thay đổi so với v1.0.1:
     *  - Dùng direct SQL COUNT thay wp_count_posts() → đếm đúng mọi status.
     *  - Chỉ lấy comic chưa có record trong stats table (hoặc fake_updated_at IS NULL)
     *    → không dùng offset nữa → không bao giờ bị stuck.
     *  - Dùng INSERT ... ON DUPLICATE KEY UPDATE thay replace()
     *    → GIỮ NGUYÊN real_views, daily_views... đã có.
     *
     * @param int $batch_size Số truyện xử lý mỗi lần gọi (default 100).
     * @return array
     */
    public static function populate_all($batch_size = 100)
    {
        global $wpdb;

        require_once get_template_directory() . '/inc/class-nettruyen-view-calculator.php';

        $stats_table = $wpdb->prefix . 'nettruyen_view_stats';

        // ── FIX 2: đếm bằng SQL trực tiếp, không qua wp_count_posts() ──────────
        // wp_count_posts() chỉ đếm 'publish', bỏ sót các status tùy chỉnh.
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

        // ── FIX 2b: chỉ lấy comic CHƯA được fake (không phụ thuộc offset) ──────
        // Một comic được coi là "chưa fake" nếu:
        //   • chưa có row trong stats, HOẶC
        //   • fake_updated_at IS NULL
        $pending_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT p.ID
				 FROM {$wpdb->posts} p
				 LEFT JOIN {$stats_table} s ON p.ID = s.post_id
				 WHERE p.post_type   = 'nettruyen_comic'
				   AND p.post_status IN ('publish','private')
				   AND (s.post_id IS NULL OR s.fake_updated_at IS NULL)
				 ORDER BY p.ID ASC
				 LIMIT %d",
                $batch_size
            )
        );

        // Không còn comic nào cần xử lý
        if (empty($pending_ids)) {
            return [
                'success' => true,
                'processed' => 0,
                'total' => $total_comics,
                'message' => 'All comics already have fake views.',
                'is_complete' => true,
            ];
        }

        $processed = 0;
        $errors = [];
        $skipped = [];

        foreach ($pending_ids as $post_id) {
            $post_id = (int) $post_id;

            try {
                $fake_views = NetTruyen_View_Calculator::calculate_fake_views($post_id);

                if ($fake_views === 0) {
                    $fake_views = 500;
                    $skipped[] = "Post {$post_id} – forced 500 views (no data)";
                }

                // ── FIX 1: INSERT ... ON DUPLICATE KEY UPDATE ────────────────────
                // Nếu row đã tồn tại → CHỈ cập nhật các cột fake, KHÔNG đụng vào
                // total_real_views / daily_views / weekly_views / monthly_views.
                $result = $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO {$stats_table}
						    (post_id,
						     total_real_views,
						     total_fake_views,
						     total_display_views,
						     daily_views,
						     weekly_views,
						     monthly_views,
						     use_fake_views,
						     fake_updated_at)
						 VALUES
						    (%d, 0, %d, %d, 0, 0, 0, 1, %s)
						 ON DUPLICATE KEY UPDATE
						    total_fake_views    = VALUES(total_fake_views),
						    -- Tính lại display_views = real hiện tại + fake mới
						    total_display_views = total_real_views + VALUES(total_fake_views),
						    use_fake_views      = 1,
						    fake_updated_at     = VALUES(fake_updated_at)",
                        $post_id,
                        $fake_views,
                        $fake_views,          // display = fake (vì real = 0 khi INSERT mới)
                        current_time('mysql')
                    )
                );

                if ($result !== false) {
                    $processed++;

                    // ── v1.0.3: Populate fake follow count ──────────────────────
                    // Tính toán độc lập, lưu vào post meta.
                    // Không ảnh hưởng stats_table hay fake views.
                    $fake_follows = NetTruyen_View_Calculator::calculate_fake_follows($post_id);
                    update_post_meta($post_id, '_nettruyen_follow_count', $fake_follows);
                    // ── Kết thúc fake follow ─────────────────────────────────────

                } else {
                    $errors[] = "Failed post_id {$post_id}: " . $wpdb->last_error;
                }

            } catch (Exception $e) {
                $errors[] = "Error post_id {$post_id}: " . $e->getMessage();
            }
        }

        // Kiểm tra còn comic nào pending không
        $remaining = (int) $wpdb->get_var(
            "SELECT COUNT(*)
			 FROM {$wpdb->posts} p
			 LEFT JOIN {$stats_table} s ON p.ID = s.post_id
			 WHERE p.post_type   = 'nettruyen_comic'
			   AND p.post_status IN ('publish','private')
			   AND (s.post_id IS NULL OR s.fake_updated_at IS NULL)"
        );

        $is_complete = ($remaining === 0);

        $message = sprintf(
            'Processed %d comics (views + follows). Remaining: %d / %d total.',
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
     * Re-calculate fake views cho một truyện cụ thể.
     * Giữ nguyên real_views, chỉ cập nhật fake + display.
     * v1.0.3: Đồng thời cập nhật lại fake follow count.
     *
     * @param int $post_id
     * @return bool
     */
    public static function recalculate_single($post_id)
    {
        global $wpdb;

        require_once get_template_directory() . '/inc/class-nettruyen-view-calculator.php';

        $fake_views = NetTruyen_View_Calculator::calculate_fake_views($post_id);
        $fake_views = max(500, $fake_views);
        $stats_table = $wpdb->prefix . 'nettruyen_view_stats';

        $result = $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$stats_table}
				    (post_id, total_real_views, total_fake_views, total_display_views,
				     use_fake_views, fake_updated_at)
				 VALUES (%d, 0, %d, %d, 1, %s)
				 ON DUPLICATE KEY UPDATE
				    total_fake_views    = VALUES(total_fake_views),
				    total_display_views = total_real_views + VALUES(total_fake_views),
				    use_fake_views      = 1,
				    fake_updated_at     = VALUES(fake_updated_at)",
                $post_id,
                $fake_views,
                $fake_views,
                current_time('mysql')
            )
        );

        if ($result !== false) {
            // ── v1.0.3: Cập nhật lại fake follow count ──────────────────────────
            $fake_follows = NetTruyen_View_Calculator::calculate_fake_follows($post_id);
            update_post_meta($post_id, '_nettruyen_follow_count', $fake_follows);
            // ── Kết thúc fake follow ─────────────────────────────────────────────
        }

        return $result !== false;
    }

    /**
     * Reset tất cả fake views (xóa hết toàn bộ bảng stats).
     * Dùng khi muốn tính lại từ đầu hoàn toàn.
     * Lưu ý: KHÔNG xóa _nettruyen_follow_count meta (giữ lại).
     *
     * @return bool
     */
    public static function reset_all()
    {
        global $wpdb;
        $stats_table = $wpdb->prefix . 'nettruyen_view_stats';
        return $wpdb->query("TRUNCATE TABLE {$stats_table}") !== false;
    }

    /**
     * Liệt kê truyện không có chapter manifest (debug helper).
     *
     * @return array [post_id => title]
     */
    public static function get_comics_without_manifest()
    {
        $ids = get_posts([
            'post_type' => 'nettruyen_comic',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);
        $no_manifest = [];
        foreach ($ids as $post_id) {
            if (empty(get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true))) {
                $no_manifest[$post_id] = get_the_title($post_id);
            }
        }
        return $no_manifest;
    }
}