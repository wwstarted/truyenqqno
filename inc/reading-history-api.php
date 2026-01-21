<?php
/**
 * Reading History REST API
 * File: inc/reading-history-api.php
 * 
 * @package TruyenQQ
 * @version 1.0.1

 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register REST API routes
 */
add_action('rest_api_init', function () {

    register_rest_route('nettruyen/v1', '/reading-history', array(
        'methods' => 'GET',
        'callback' => 'truyenqq_api_get_reading_history',
        'permission_callback' => 'truyenqq_check_logged_in'
    ));


    register_rest_route('nettruyen/v1', '/reading-history', array(
        'methods' => 'POST',
        'callback' => 'truyenqq_api_save_reading_history',
        'permission_callback' => '__return_true'
    ));


    register_rest_route('nettruyen/v1', '/reading-history/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'truyenqq_api_delete_reading_history',
        'permission_callback' => 'truyenqq_check_logged_in'
    ));


    register_rest_route('nettruyen/v1', '/reading-history/clear', array(
        'methods' => 'POST',
        'callback' => 'truyenqq_api_clear_reading_history',
        'permission_callback' => 'truyenqq_check_logged_in'
    ));
});

/**
 * Permission callback: Check if user is logged in
 */
function truyenqq_check_logged_in()
{
    return is_user_logged_in();
}

/**
 * Get reading history for current user
 */
function truyenqq_api_get_reading_history($request)
{
    if (!is_user_logged_in()) {
        return new WP_Error('not_logged_in', 'Bạn cần đăng nhập để xem lịch sử', array('status' => 401));
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'nettruyen_reading_history';
    $stats_table = $wpdb->prefix . 'nettruyen_view_stats';


    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT h.*, p.post_title, p.guid 
         FROM {$table} h
         LEFT JOIN {$wpdb->posts} p ON h.post_id = p.ID
         WHERE h.user_id = %d AND p.post_status = 'publish'
         ORDER BY h.last_read_at DESC
         LIMIT 50",
        $user_id
    ));

    if (empty($results)) {
        return array(
            'success' => true,
            'data' => array(),
            'message' => 'Chưa có lịch sử đọc truyện'
        );
    }

    $history = array();
    foreach ($results as $row) {
        $post_id = $row->post_id;


        $thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);
        if (empty($thumbnail)) {
            $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
        }
        if (empty($thumbnail)) {
            $thumbnail = 'https://via.placeholder.com/190x247?text=No+Image';
        }


        $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
        $manifest = !empty($manifest_json) ? json_decode($manifest_json, true) : null;

        $total_chapters = !empty($manifest['chapters']) ? count($manifest['chapters']) : 0;


        $follow_count = (int) get_post_meta($post_id, '_nettruyen_follow_count', true);
        $view_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT total_display_views FROM {$stats_table} WHERE post_id = %d",
            $post_id
        ));
        $view_count = $view_stats ? $view_stats->total_display_views : 0;


        $time_ago = human_time_diff(strtotime($row->last_read_at), current_time('timestamp')) . ' trước';

        $history[] = array(
            'id' => (int) $row->id,
            'post_id' => (int) $post_id,
            'post_title' => $row->post_title,
            'post_url' => get_permalink($post_id),
            'thumbnail' => $thumbnail,
            'chapter_slug' => $row->chapter_slug,
            'chapter_name' => $row->chapter_name,
            'current_page' => (int) $row->current_page,
            'total_pages' => (int) $row->total_pages,
            'read_count' => (int) $row->read_count,
            'last_read_at' => $row->last_read_at,
            'time_ago' => $time_ago,
            'total_chapters' => $total_chapters,
            'follow_count' => $follow_count,
            'view_count' => $view_count
        );
    }

    return array(
        'success' => true,
        'data' => $history
    );
}

/**
 * Save/Update reading progress
 */
function truyenqq_api_save_reading_history($request)
{

    if (!is_user_logged_in()) {
        return array(
            'success' => true,
            'message' => 'Bạn cần đăng nhập để lưu lịch sử đọc',
            'guest_mode' => true
        );
    }

    $post_id = $request->get_param('post_id');
    $chapter_slug = $request->get_param('chapter_slug');
    $chapter_name = $request->get_param('chapter_name');
    $current_page = (int) $request->get_param('current_page');
    $total_pages = (int) $request->get_param('total_pages');

    if (empty($post_id) || empty($chapter_slug)) {
        return new WP_Error('missing_params', 'Thiếu thông tin bắt buộc', array('status' => 400));
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'nettruyen_reading_history';


    if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
        return new WP_Error('table_not_exists', 'Bảng lịch sử chưa được tạo', array('status' => 500));
    }


    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table} WHERE user_id = %d AND post_id = %d AND chapter_slug = %s",
        $user_id,
        $post_id,
        $chapter_slug
    ));

    if ($existing) {

        $result = $wpdb->update(
            $table,
            array(
                'current_page' => $current_page,
                'total_pages' => $total_pages,
                'read_count' => $existing->read_count + 1,
                'last_read_at' => current_time('mysql')
            ),
            array(
                'user_id' => $user_id,
                'post_id' => $post_id,
                'chapter_slug' => $chapter_slug
            ),
            array('%d', '%d', '%d', '%s'),
            array('%d', '%d', '%s')
        );

        if ($result === false) {
            return new WP_Error('update_failed', 'Lỗi cập nhật: ' . $wpdb->last_error, array('status' => 500));
        }
    } else {

        $result = $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'post_id' => $post_id,
                'chapter_slug' => $chapter_slug,
                'chapter_name' => $chapter_name,
                'current_page' => $current_page,
                'total_pages' => $total_pages,
                'read_count' => 1,
                'last_read_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s', '%d', '%d', '%d', '%s')
        );

        if ($result === false) {
            return new WP_Error('insert_failed', 'Lỗi thêm mới: ' . $wpdb->last_error, array('status' => 500));
        }
    }

    return array(
        'success' => true,
        'message' => 'Đã lưu lịch sử đọc',
        'data' => array(
            'user_id' => $user_id,
            'post_id' => $post_id,
            'chapter_slug' => $chapter_slug,
            'current_page' => $current_page,
            'total_pages' => $total_pages
        )
    );
}

/**
 * Delete single history item
 */
function truyenqq_api_delete_reading_history($request)
{
    if (!is_user_logged_in()) {
        return new WP_Error('not_logged_in', 'Bạn cần đăng nhập', array('status' => 401));
    }

    $history_id = (int) $request->get_param('id');

    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'nettruyen_reading_history';

    $deleted = $wpdb->delete(
        $table,
        array(
            'id' => $history_id,
            'user_id' => $user_id
        ),
        array('%d', '%d')
    );

    if ($deleted) {
        return array(
            'success' => true,
            'message' => 'Đã xóa lịch sử'
        );
    } else {
        return new WP_Error('delete_failed', 'Xóa thất bại', array('status' => 500));
    }
}

/**
 * Clear all reading history for current user
 */
function truyenqq_api_clear_reading_history($request)
{
    if (!is_user_logged_in()) {
        return new WP_Error('not_logged_in', 'Bạn cần đăng nhập', array('status' => 401));
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'nettruyen_reading_history';

    $deleted = $wpdb->delete(
        $table,
        array('user_id' => $user_id),
        array('%d')
    );

    return array(
        'success' => true,
        'message' => "Đã xóa {$deleted} lịch sử đọc"
    );
}