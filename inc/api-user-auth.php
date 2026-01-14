<?php
/**
 * User Info REST API
 * Thêm vào file: inc/ajax-auth-handlers.php (hoặc functions.php)
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

// Register REST API endpoints
add_action('rest_api_init', function () {
    // Get current user info
    register_rest_route('nettruyen/v1', '/user/info', array(
        'methods' => 'GET',
        'callback' => 'truyenqq_api_get_user_info',
        'permission_callback' => '__return_true'
    ));

    // Get user notifications (for future)
    register_rest_route('nettruyen/v1', '/user/notifications', array(
        'methods' => 'GET',
        'callback' => 'truyenqq_api_get_notifications',
        'permission_callback' => '__return_true'
    ));
});

/**
 * API: Get current user info
 * 
 * @return array User info or not logged in status
 */
function truyenqq_api_get_user_info()
{
    // Check if user is logged in
    if (!is_user_logged_in()) {
        return array(
            'is_logged_in' => false,
            'user' => null
        );
    }

    $current_user = wp_get_current_user();

    // Get avatar URL (fallback to default)
    $avatar_url = get_avatar_url($current_user->ID, array('size' => 100));
    if (empty($avatar_url) || strpos($avatar_url, 'gravatar') !== false) {
        $avatar_url = 'https://th.bing.com/th/id/OIP.ItvA9eX1ZIYT8NHePqeuCgHaHa?w=159&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3';
    }

    return array(
        'is_logged_in' => true,
        'user' => array(
            'id' => $current_user->ID,
            'username' => $current_user->user_login,
            'email' => $current_user->user_email,
            'display_name' => $current_user->display_name,
            'avatar' => $avatar_url,
            'links' => array(
                'following' => home_url('/truyen-dang-theo-doi'),
                'history' => home_url('/lich-su'),
                'settings' => home_url('/quan-ly-tai-khoan'),
                'logout' => wp_logout_url(home_url())
            )
        )
    );
}

/**
 * API: Get user notifications
 * 
 * @return array Notifications list
 */
function truyenqq_api_get_notifications()
{
    if (!is_user_logged_in()) {
        return array(
            'success' => false,
            'notifications' => array(),
            'unread_count' => 0
        );
    }

    // TODO: Implement notification system in future
    // For now, return empty array

    return array(
        'success' => true,
        'notifications' => array(),
        'unread_count' => 0
    );
}