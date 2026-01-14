<?php
/**
 * Database Migration - Authentication Tables
 * Tạo tables cho OTP và user authentication
 * 
 * @package TruyenQQ
 * @version 1.0.0
 * 
 * Usage: Thêm vào functions.php hoặc chạy khi activate theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create authentication tables
 */
function truyenqq_create_auth_tables()
{
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $charset_collate = $wpdb->get_charset_collate();

    // 1. OTP Codes Table
    $otp_table = $wpdb->prefix . 'nettruyen_otp_codes';
    $otp_sql = "CREATE TABLE IF NOT EXISTS {$otp_table} (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        email varchar(100) NOT NULL,
        otp_code varchar(6) NOT NULL,
        purpose varchar(20) NOT NULL COMMENT 'register, reset_password',
        expires_at datetime NOT NULL,
        created_at datetime NOT NULL,
        verified_at datetime DEFAULT NULL,
        ip_address varchar(45) DEFAULT NULL,
        PRIMARY KEY (id),
        KEY email_purpose (email, purpose),
        KEY expires_at (expires_at)
    ) $charset_collate;";

    dbDelta($otp_sql);

    // 2. User Login History (optional, for security tracking)
    $login_history_table = $wpdb->prefix . 'nettruyen_login_history';
    $login_sql = "CREATE TABLE IF NOT EXISTS {$login_history_table} (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        login_time datetime NOT NULL,
        ip_address varchar(45) DEFAULT NULL,
        user_agent varchar(255) DEFAULT NULL,
        login_status varchar(20) NOT NULL COMMENT 'success, failed',
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY login_time (login_time)
    ) $charset_collate;";

    dbDelta($login_sql);

    // 3. User Meta for additional fields (nếu cần thêm fields ngoài WP User)
    $user_meta_table = $wpdb->prefix . 'nettruyen_user_meta';
    $meta_sql = "CREATE TABLE IF NOT EXISTS {$user_meta_table} (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        email_verified tinyint(1) DEFAULT 0,
        email_verified_at datetime DEFAULT NULL,
        two_factor_enabled tinyint(1) DEFAULT 0,
        last_active_at datetime DEFAULT NULL,
        created_at datetime NOT NULL,
        updated_at datetime DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY user_id (user_id)
    ) $charset_collate;";

    dbDelta($meta_sql);

    // Update version
    update_option('truyenqq_auth_db_version', '1.0.0');
}

/**
 * Run migration on theme activation
 */
add_action('after_switch_theme', 'truyenqq_create_auth_tables');

/**
 * Cleanup expired OTP codes (chạy daily)
 */
function truyenqq_cleanup_expired_otps()
{
    global $wpdb;
    $otp_table = $wpdb->prefix . 'nettruyen_otp_codes';

    $deleted = $wpdb->query(
        "DELETE FROM {$otp_table} WHERE expires_at < NOW()"
    );

    if ($deleted) {
        error_log("TruyenQQ: Cleaned up {$deleted} expired OTP codes");
    }
}

// Schedule daily cleanupa
if (!wp_next_scheduled('truyenqq_cleanup_otps_hook')) {
    wp_schedule_event(time(), 'daily', 'truyenqq_cleanup_otps_hook');
}
add_action('truyenqq_cleanup_otps_hook', 'truyenqq_cleanup_expired_otps');

/**
 * Deactivation cleanup
 */
function truyenqq_auth_deactivate()
{
    wp_clear_scheduled_hook('truyenqq_cleanup_otps_hook');
}
register_deactivation_hook(__FILE__, 'truyenqq_auth_deactivate');