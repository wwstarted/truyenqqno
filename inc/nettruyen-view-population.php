<?php
/**
 * NetTruyen View Population
 * Populate fake views cho tất cả truyện hiện có
 * 
 * @package NetTruyen
 * @version 1.0.1 - Fixed: Always populate, no skip
 */

if (!defined('ABSPATH')) {
    exit;
}

class NetTruyen_View_Population
{

    /**
     * Populate fake views cho tất cả truyện
     * 
     * @param int $batch_size Số truyện/lần (để tránh timeout)
     * @param int $offset Bắt đầu từ truyện thứ mấy
     * @return array ['success' => bool, 'processed' => int, 'total' => int, 'message' => string]
     */
    public static function populate_all($batch_size = 50, $offset = 0)
    {
        global $wpdb;

        require_once get_template_directory() . '/inc/class-nettruyen-view-calculator.php';

        // Đếm tổng số truyện
        $total_comics = wp_count_posts('nettruyen_comic')->publish;

        if ($total_comics === 0) {
            return array(
                'success' => false,
                'processed' => 0,
                'total' => 0,
                'message' => 'No comics found'
            );
        }

        // Lấy batch truyện
        $args = array(
            'post_type' => 'nettruyen_comic',
            'post_status' => 'publish',
            'posts_per_page' => $batch_size,
            'offset' => $offset,
            'orderby' => 'ID',
            'order' => 'ASC',
            'fields' => 'ids' // Chỉ lấy ID để nhanh
        );

        $comic_ids = get_posts($args);

        if (empty($comic_ids)) {
            return array(
                'success' => true,
                'processed' => 0,
                'total' => $total_comics,
                'message' => 'All comics already processed',
                'is_complete' => true
            );
        }

        $stats_table = $wpdb->prefix . 'nettruyen_view_stats';
        $processed = 0;
        $errors = array();
        $skipped = array();

        foreach ($comic_ids as $post_id) {
            try {
                // Tính fake views
                $fake_views = NetTruyen_View_Calculator::calculate_fake_views($post_id);

                // ✅ FIX: KHÔNG skip truyện có 0 views nữa
                // Calculator đã đảm bảo minimum 500 views

                // ✅ SAFETY CHECK: Nếu vẫn = 0, force minimum
                if ($fake_views === 0) {
                    $fake_views = 500;
                    $title = get_the_title($post_id);
                    $skipped[] = "Post {$post_id} ({$title}) - forced 500 views (no data)";
                }

                // Insert vào stats table
                $result = $wpdb->replace(
                    $stats_table,
                    array(
                        'post_id' => $post_id,
                        'total_real_views' => 0,
                        'total_fake_views' => $fake_views,
                        'total_display_views' => $fake_views,
                        'daily_views' => 0,
                        'weekly_views' => 0,
                        'monthly_views' => 0,
                        'last_view_at' => null,
                        'use_fake_views' => 1,
                        'fake_updated_at' => current_time('mysql')
                    ),
                    array('%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%d', '%s')
                );

                if ($result !== false) {
                    $processed++;
                } else {
                    $errors[] = "Failed to insert post_id {$post_id}: " . $wpdb->last_error;
                }

            } catch (Exception $e) {
                $errors[] = "Error processing post_id {$post_id}: " . $e->getMessage();
            }
        }

        // Check xem đã xong chưa
        $is_complete = ($offset + $batch_size) >= $total_comics;

        $message = sprintf(
            'Processed %d/%d comics (batch %d-%d)',
            $processed,
            $total_comics,
            $offset + 1,
            min($offset + $batch_size, $total_comics)
        );

        if (!empty($errors)) {
            $message .= ' with ' . count($errors) . ' errors';
        }

        if (!empty($skipped)) {
            $message .= ' | ' . count($skipped) . ' forced minimum views';
        }

        return array(
            'success' => true,
            'processed' => $processed,
            'total' => $total_comics,
            'offset' => $offset + $batch_size,
            'message' => $message,
            'is_complete' => $is_complete,
            'errors' => $errors,
            'skipped' => $skipped // ✅ Thêm log để debug
        );
    }

    /**
     * Re-calculate fake views cho một truyện cụ thể
     * 
     * @param int $post_id ID truyện
     * @return bool Success
     */
    public static function recalculate_single($post_id)
    {
        global $wpdb;

        require_once get_template_directory() . '/inc/class-nettruyen-view-calculator.php';

        $fake_views = NetTruyen_View_Calculator::calculate_fake_views($post_id);

        // ✅ FIX: Không skip, force minimum
        if ($fake_views === 0) {
            $fake_views = 500;
        }

        $stats_table = $wpdb->prefix . 'nettruyen_view_stats';

        // Lấy real views hiện tại
        $current = $wpdb->get_row($wpdb->prepare(
            "SELECT total_real_views FROM {$stats_table} WHERE post_id = %d",
            $post_id
        ));

        $real_views = $current ? (int) $current->total_real_views : 0;

        // Update
        $result = $wpdb->replace(
            $stats_table,
            array(
                'post_id' => $post_id,
                'total_real_views' => $real_views,
                'total_fake_views' => $fake_views,
                'total_display_views' => $real_views + $fake_views,
                'use_fake_views' => 1,
                'fake_updated_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%d', '%d', '%s')
        );

        return $result !== false;
    }

    /**
     * Reset tất cả fake views (xóa hết)
     * 
     * @return bool Success
     */
    public static function reset_all()
    {
        global $wpdb;

        $stats_table = $wpdb->prefix . 'nettruyen_view_stats';

        $result = $wpdb->query("TRUNCATE TABLE {$stats_table}");

        return $result !== false;
    }

    /**
     * ✅ NEW: Debug helper - Liệt kê truyện không có manifest
     * 
     * @return array ['post_id' => 'title']
     */
    public static function get_comics_without_manifest()
    {
        $args = array(
            'post_type' => 'nettruyen_comic',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        );

        $all_comics = get_posts($args);
        $no_manifest = array();

        foreach ($all_comics as $post_id) {
            $manifest = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);

            if (empty($manifest)) {
                $no_manifest[$post_id] = get_the_title($post_id);
            }
        }

        return $no_manifest;
    }
}