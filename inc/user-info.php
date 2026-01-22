<?php
/**
 * User Settings Pages - Enqueue Scripts & Styles
 * Thêm vào cuối file functions.php
 *
 * @package TruyenQQ
 * @version 1.0.0
 */


// Include User Settings API
require_once get_template_directory() . '/inc/class-user-settings-api.php';

/**
 * Enqueue User Settings Assets
 */
function truyenqq_enqueue_user_settings_assets()
{
    // Only load on user settings page
    if (!is_page_template('template-user-settings.php')) {
        return;
    }

    // Enqueue CSS
    wp_enqueue_style(
        'truyenqq-user-settings',
        get_template_directory_uri() . '/css/user-settings.css',
        array('toyota-global'),
        '1.0.0'
    );

    // Enqueue JavaScript
    wp_enqueue_script(
        'truyenqq-user-settings',
        get_template_directory_uri() . '/js/user-settings.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // Note: userSettingsData is already localized in the template file
}
add_action('wp_enqueue_scripts', 'truyenqq_enqueue_user_settings_assets');

/**
 * Create User Settings Page on Theme Activation
 */
function truyenqq_create_user_settings_page()
{
    // Check if page already exists
    $page_check = get_page_by_path('quan-ly-tai-khoan');

    if (!$page_check) {
        $page_id = wp_insert_post(array(
            'post_title' => 'Quản lý tài khoản',
            'post_name' => 'quan-ly-tai-khoan',
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1,
            'page_template' => 'template-user-settings.php'
        ));

        if ($page_id) {
            update_option('truyenqq_user_settings_page_id', $page_id);
        }
    }
}
add_action('after_switch_theme', 'truyenqq_create_user_settings_page');

/**
 * Initialize default user meta on registration
 * Hook this into your existing registration process
 */
function truyenqq_init_user_settings_meta($user_id)
{
    // Set default values for new users
    update_user_meta($user_id, 'gender', '0');
    update_user_meta($user_id, 'rank', '0');
    update_user_meta($user_id, 'points', 0);
    update_user_meta($user_id, 'level', 1);
    update_user_meta($user_id, 'level_progress', 0);
}
// Hook this to your registration functions


/**
 * Add User Settings link to admin bar
 */
function truyenqq_add_settings_to_admin_bar($wp_admin_bar)
{
    if (!is_user_logged_in()) {
        return;
    }

    $user_settings_url = home_url('/quan-ly-tai-khoan');

    $wp_admin_bar->add_node(array(
        'id' => 'user-settings',
        'title' => '<span class="ab-icon dashicons dashicons-admin-users"></span> Cài đặt tài khoản',
        'href' => $user_settings_url,
        'parent' => 'user-actions',
    ));
}
add_action('admin_bar_menu', 'truyenqq_add_settings_to_admin_bar', 100);