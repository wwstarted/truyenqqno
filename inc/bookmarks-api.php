<?php
/**
 * Bookmarks REST API (FIXED VERSION)
 * File: inc/bookmarks-api.php
 * 
 * @package TruyenQQ
 * @version 1.0.1
 * ✅ Fixed: Permission callback issues
 * ✅ Fixed: Better error handling
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Permission callback - More lenient for debugging
 */
function truyenqq_check_logged_in_flexible($request)
{
    // Always return true for GET (we'll check inside the function)
    if ($request->get_method() === 'GET') {
        return true;
    }

    return is_user_logged_in();
}

/**
 * Register REST API routes
 */
add_action('rest_api_init', function () {
    // Get user's bookmarks
    register_rest_route('nettruyen/v1', '/bookmarks', array(
        'methods' => 'GET',
        'callback' => 'truyenqq_api_get_bookmarks',
        'permission_callback' => '__return_true' // ← FIX: Allow access, check inside
    ));

    // Toggle bookmark (add/remove)
    register_rest_route('nettruyen/v1', '/bookmarks/toggle', array(
        'methods' => 'POST',
        'callback' => 'truyenqq_api_toggle_bookmark',
        'permission_callback' => '__return_true' // ← FIX: Allow, validate inside
    ));

    // Check bookmark status
    register_rest_route('nettruyen/v1', '/bookmarks/check', array(
        'methods' => 'POST',
        'callback' => 'truyenqq_api_check_bookmark',
        'permission_callback' => '__return_true'
    ));

    // Batch check multiple bookmarks
    register_rest_route('nettruyen/v1', '/bookmarks/check-batch', array(
        'methods' => 'POST',
        'callback' => 'truyenqq_api_check_bookmarks_batch',
        'permission_callback' => '__return_true'
    ));

    // Delete bookmark
    register_rest_route('nettruyen/v1', '/bookmarks/(?P<post_id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'truyenqq_api_delete_bookmark',
        'permission_callback' => '__return_true'
    ));

    // Clear all bookmarks
    register_rest_route('nettruyen/v1', '/bookmarks/clear-all', array(
        'methods' => 'POST',
        'callback' => 'truyenqq_api_clear_bookmarks',
        'permission_callback' => '__return_true'
    ));
});

/**
 * Get user's bookmarks
 */
function truyenqq_api_get_bookmarks($request)
{
    // Check login inside function
    if (!is_user_logged_in()) {
        return array(
            'success' => false,
            'message' => 'Bạn cần đăng nhập để xem truyện theo dõi',
            'data' => array(),
            'guest_mode' => true
        );
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'nettruyen_bookmarks';
    $stats_table = $wpdb->prefix . 'nettruyen_view_stats';

    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
        return new WP_Error(
            'table_not_exists',
            'Bảng bookmarks chưa được tạo. Vui lòng kích hoạt lại theme.',
            array('status' => 500)
        );
    }

    // Get bookmarks with post data
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT b.*, p.post_title, p.guid 
         FROM {$table} b
         LEFT JOIN {$wpdb->posts} p ON b.post_id = p.ID
         WHERE b.user_id = %d AND p.post_status = 'publish'
         ORDER BY b.created_at DESC
         LIMIT 100",
        $user_id
    ));

    if (empty($results)) {
        return array(
            'success' => true,
            'data' => array(),
            'message' => 'Chưa có truyện theo dõi'
        );
    }

    $bookmarks = array();
    foreach ($results as $row) {
        $post_id = $row->post_id;

        // Get thumbnail
        $thumbnail = get_post_meta($post_id, '_nettruyen_thumbnail', true);
        if (empty($thumbnail)) {
            $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
        }
        if (empty($thumbnail)) {
            $thumbnail = 'https://via.placeholder.com/190x247?text=No+Image';
        }

        // Get manifest for chapter info
        $manifest_json = get_post_meta($post_id, '_nettruyen_chapter_manifest_json', true);
        $manifest = !empty($manifest_json) ? json_decode($manifest_json, true) : null;

        $latest_chapter = 'Đang cập nhật';
        $total_chapters = 0;
        if (!empty($manifest['chapters'])) {
            $chapters = $manifest['chapters'];
            $total_chapters = count($chapters);
            $latest = end($chapters);
            $latest_chapter = 'Chương ' . $latest['name'];
        }

        $updated_at = !empty($manifest['updated_at']) ? $manifest['updated_at'] : get_post_modified_time('U', false, $post_id);
        $time_ago = human_time_diff(strtotime($updated_at), current_time('timestamp')) . ' trước';

        // Get stats
        $follow_count = (int) get_post_meta($post_id, '_nettruyen_follow_count', true);
        $bookmark_count = truyenqq_get_bookmark_count($post_id);

        $view_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT total_display_views FROM {$stats_table} WHERE post_id = %d",
            $post_id
        ));
        $view_count = $view_stats ? $view_stats->total_display_views : 0;

        $bookmarks[] = array(
            'id' => (int) $row->id,
            'post_id' => (int) $post_id,
            'post_title' => $row->post_title,
            'post_url' => get_permalink($post_id),
            'thumbnail' => $thumbnail,
            'latest_chapter' => $latest_chapter,
            'total_chapters' => $total_chapters,
            'time_ago' => $time_ago,
            'updated_at' => $updated_at,
            'created_at' => $row->created_at,
            'follow_count' => $follow_count,
            'bookmark_count' => $bookmark_count,
            'view_count' => $view_count
        );
    }

    return array(
        'success' => true,
        'data' => $bookmarks,
        'count' => count($bookmarks)
    );
}

/**
 * Toggle bookmark (add/remove)
 */
function truyenqq_api_toggle_bookmark($request)
{
    // Guest users get friendly message
    if (!is_user_logged_in()) {
        return array(
            'success' => false,
            'message' => 'Vui lòng đăng nhập để theo dõi truyện',
            'guest_mode' => true,
            'bookmarked' => false
        );
    }

    $post_id = (int) $request->get_param('post_id');

    if (empty($post_id)) {
        return new WP_Error('missing_post_id', 'Thiếu ID truyện', array('status' => 400));
    }

    // Verify post exists
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'nettruyen_comic') {
        return new WP_Error('invalid_post', 'Truyện không tồn tại', array('status' => 404));
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'nettruyen_bookmarks';

    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") != $table) {
        return new WP_Error(
            'table_not_exists',
            'Bảng bookmarks chưa được tạo',
            array('status' => 500)
        );
    }

    // Check if already bookmarked
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND post_id = %d",
        $user_id,
        $post_id
    ));

    if ($exists) {
        // Remove bookmark
        $deleted = $wpdb->delete(
            $table,
            array('user_id' => $user_id, 'post_id' => $post_id),
            array('%d', '%d')
        );

        if ($deleted !== false) {
            // Update count in post meta
            $new_count = truyenqq_get_bookmark_count($post_id);
            update_post_meta($post_id, '_nettruyen_bookmark_count', $new_count);

            return array(
                'success' => true,
                'bookmarked' => false,
                'message' => 'Đã bỏ theo dõi',
                'bookmark_count' => $new_count
            );
        } else {
            return new WP_Error('delete_failed', 'Lỗi bỏ theo dõi: ' . $wpdb->last_error, array('status' => 500));
        }
    } else {
        // Add bookmark
        $inserted = $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'post_id' => $post_id,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s')
        );

        if ($inserted !== false) {
            // Update count in post meta
            $new_count = truyenqq_get_bookmark_count($post_id);
            update_post_meta($post_id, '_nettruyen_bookmark_count', $new_count);

            return array(
                'success' => true,
                'bookmarked' => true,
                'message' => 'Đã thêm vào theo dõi',
                'bookmark_count' => $new_count
            );
        } else {
            return new WP_Error('insert_failed', 'Lỗi thêm theo dõi: ' . $wpdb->last_error, array('status' => 500));
        }
    }
}

/**
 * Check bookmark status for single post
 */
function truyenqq_api_check_bookmark($request)
{
    if (!is_user_logged_in()) {
        return array(
            'success' => true,
            'bookmarked' => false
        );
    }

    $post_id = (int) $request->get_param('post_id');

    if (empty($post_id)) {
        return new WP_Error('missing_post_id', 'Thiếu ID truyện', array('status' => 400));
    }

    $bookmarked = truyenqq_is_bookmarked($post_id);

    return array(
        'success' => true,
        'bookmarked' => $bookmarked,
        'post_id' => $post_id
    );
}

/**
 * Check bookmark status for multiple posts (batch)
 */
function truyenqq_api_check_bookmarks_batch($request)
{
    if (!is_user_logged_in()) {
        return array(
            'success' => true,
            'bookmarks' => array()
        );
    }

    $post_ids = $request->get_param('post_ids');

    if (empty($post_ids) || !is_array($post_ids)) {
        return new WP_Error('missing_post_ids', 'Thiếu danh sách ID', array('status' => 400));
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'nettruyen_bookmarks';

    // Build placeholders
    $placeholders = implode(',', array_fill(0, count($post_ids), '%d'));

    // Prepare query
    $query = $wpdb->prepare(
        "SELECT post_id FROM {$table} WHERE user_id = %d AND post_id IN ($placeholders)",
        array_merge(array($user_id), $post_ids)
    );

    $bookmarked_posts = $wpdb->get_col($query);

    // Convert to associative array
    $result = array();
    foreach ($post_ids as $post_id) {
        $result[$post_id] = in_array($post_id, $bookmarked_posts);
    }

    return array(
        'success' => true,
        'bookmarks' => $result
    );
}

/**
 * Delete single bookmark
 */
function truyenqq_api_delete_bookmark($request)
{
    if (!is_user_logged_in()) {
        return array(
            'success' => false,
            'message' => 'Bạn cần đăng nhập',
            'guest_mode' => true
        );
    }

    $post_id = (int) $request->get_param('post_id');

    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'nettruyen_bookmarks';

    $deleted = $wpdb->delete(
        $table,
        array('user_id' => $user_id, 'post_id' => $post_id),
        array('%d', '%d')
    );

    if ($deleted !== false) {
        // Update count
        $new_count = truyenqq_get_bookmark_count($post_id);
        update_post_meta($post_id, '_nettruyen_bookmark_count', $new_count);

        return array(
            'success' => true,
            'message' => 'Đã xóa khỏi theo dõi'
        );
    } else {
        return new WP_Error('delete_failed', 'Xóa thất bại', array('status' => 500));
    }
}

/**
 * Clear all bookmarks
 */
function truyenqq_api_clear_bookmarks($request)
{
    if (!is_user_logged_in()) {
        return array(
            'success' => false,
            'message' => 'Bạn cần đăng nhập',
            'guest_mode' => true
        );
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'nettruyen_bookmarks';

    $deleted = $wpdb->delete(
        $table,
        array('user_id' => $user_id),
        array('%d')
    );

    return array(
        'success' => true,
        'message' => "Đã xóa {$deleted} truyện theo dõi",
        'deleted_count' => $deleted
    );
}