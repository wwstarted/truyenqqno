<?php
/**
 * AJAX Authentication Handlers with reCAPTCHA
 * 
 * @package TruyenQQ
 * @version 1.0.3 - COMPLETE FIXED
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/class-truyenqq-otp-manager.php';
require_once get_template_directory() . '/inc/class-truyenqq-auth-handler.php';

/**
 * Verify Google reCAPTCHA
 * 
 * @param string $recaptcha_token reCAPTCHA response token
 * @return bool True if valid, False if invalid
 */
function truyenqq_verify_recaptcha($recaptcha_token)
{
    // Allow empty for testing - remove in production
    if (empty($recaptcha_token)) {
        error_log('TruyenQQ: Empty reCAPTCHA token');
        return false;
    }

    $secret_key = get_option('truyenqq_recaptcha_secret_key');

    if (empty($secret_key)) {
        error_log('TruyenQQ: reCAPTCHA secret key not configured');
        return true; // Allow if not configured
    }

    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';

    $api_response = wp_remote_post($verify_url, array(
        'body' => array(
            'secret' => $secret_key,
            'response' => $recaptcha_token,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ),
        'timeout' => 10
    ));

    if (is_wp_error($api_response)) {
        error_log('TruyenQQ reCAPTCHA Error: ' . $api_response->get_error_message());
        return false;
    }

    $response_body = wp_remote_retrieve_body($api_response);
    $result = json_decode($response_body, true);

    if (isset($result['success']) && $result['success'] === true) {
        return true;
    }

    // Log error codes if any
    if (isset($result['error-codes'])) {
        error_log('TruyenQQ reCAPTCHA Errors: ' . implode(', ', $result['error-codes']));
    }

    return false;
}

/**
 * AJAX: Register - Send OTP
 */
function truyenqq_ajax_register_send_otp()
{
    check_ajax_referer('truyenqq_auth_nonce', 'nonce');

    // Verify reCAPTCHA
    $recaptcha_response = isset($_POST['recaptcha_response']) ? sanitize_text_field($_POST['recaptcha_response']) : '';

    if (!truyenqq_verify_recaptcha($recaptcha_response)) {
        wp_send_json_error(array(
            'message' => 'Xác thực reCAPTCHA thất bại. Vui lòng thử lại.'
        ));
        return;
    }

    $username = sanitize_user($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];

    $result = TruyenQQ_Auth_Handler::register_send_otp($username, $email, $password);

    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
add_action('wp_ajax_nopriv_register_send_otp', 'truyenqq_ajax_register_send_otp');

/**
 * AJAX: Register - Verify OTP
 */
function truyenqq_ajax_register_verify_otp()
{
    check_ajax_referer('truyenqq_auth_nonce', 'nonce');

    $email = sanitize_email($_POST['email']);
    $otp_code = sanitize_text_field($_POST['otp_code']);

    $result = TruyenQQ_Auth_Handler::register_verify_otp($email, $otp_code);

    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
add_action('wp_ajax_nopriv_register_verify_otp', 'truyenqq_ajax_register_verify_otp');

/**
 * AJAX: Login - FIXED VERSION
 */
function truyenqq_ajax_login()
{
    check_ajax_referer('truyenqq_auth_nonce', 'nonce');

    // Verify reCAPTCHA FIRST
    $recaptcha_response = isset($_POST['recaptcha_response']) ? sanitize_text_field($_POST['recaptcha_response']) : '';

    if (!truyenqq_verify_recaptcha($recaptcha_response)) {
        wp_send_json_error(array(
            'message' => 'Xác thực reCAPTCHA thất bại. Vui lòng thử lại.'
        ));
        return; // Important: stop execution here
    }

    // If reCAPTCHA is valid, proceed with login
    $username = sanitize_text_field($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? (bool) $_POST['remember'] : false;

    $result = TruyenQQ_Auth_Handler::login($username, $password, $remember);

    // Send result without checking reCAPTCHA again
    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
add_action('wp_ajax_nopriv_login', 'truyenqq_ajax_login');

/**
 * AJAX: Logout
 */
function truyenqq_ajax_logout()
{
    check_ajax_referer('truyenqq_auth_nonce', 'nonce');

    $result = TruyenQQ_Auth_Handler::logout();

    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
add_action('wp_ajax_logout', 'truyenqq_ajax_logout');

/**
 * AJAX: Forgot Password - Send OTP
 */
function truyenqq_ajax_forgot_password_send_otp()
{
    check_ajax_referer('truyenqq_auth_nonce', 'nonce');

    // Verify reCAPTCHA
    $recaptcha_response = isset($_POST['recaptcha_response']) ? sanitize_text_field($_POST['recaptcha_response']) : '';

    if (!truyenqq_verify_recaptcha($recaptcha_response)) {
        wp_send_json_error(array(
            'message' => 'Xác thực reCAPTCHA thất bại. Vui lòng thử lại.'
        ));
        return;
    }

    $email = sanitize_email($_POST['email']);

    $result = TruyenQQ_Auth_Handler::forgot_password_send_otp($email);

    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
add_action('wp_ajax_nopriv_forgot_password_send_otp', 'truyenqq_ajax_forgot_password_send_otp');

/**
 * AJAX: Forgot Password - Verify OTP
 */
function truyenqq_ajax_forgot_password_verify_otp()
{
    check_ajax_referer('truyenqq_auth_nonce', 'nonce');

    $email = sanitize_email($_POST['email']);
    $otp_code = sanitize_text_field($_POST['otp_code']);

    $result = TruyenQQ_Auth_Handler::forgot_password_verify_otp($email, $otp_code);

    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
add_action('wp_ajax_nopriv_forgot_password_verify_otp', 'truyenqq_ajax_forgot_password_verify_otp');

/**
 * AJAX: Reset Password
 */
function truyenqq_ajax_reset_password()
{
    check_ajax_referer('truyenqq_auth_nonce', 'nonce');

    $reset_token = sanitize_text_field($_POST['reset_token']);
    $new_password = $_POST['new_password'];

    $result = TruyenQQ_Auth_Handler::reset_password($reset_token, $new_password);

    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
add_action('wp_ajax_nopriv_reset_password', 'truyenqq_ajax_reset_password');

/**
 * AJAX: Resend OTP
 */
function truyenqq_ajax_resend_otp()
{
    check_ajax_referer('truyenqq_auth_nonce', 'nonce');

    $email = sanitize_email($_POST['email']);
    $purpose = sanitize_text_field($_POST['purpose']);

    $result = TruyenQQ_OTP_Manager::send_otp($email, $purpose);

    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
add_action('wp_ajax_nopriv_resend_otp', 'truyenqq_ajax_resend_otp');

/**
 * Enqueue scripts and styles - FIXED
 */
function truyenqq_enqueue_auth_scripts()
{
    $is_auth_page = is_page_template('template-login.php') ||
        is_page_template('template-register.php') ||
        is_page_template('template-forgot-password.php');

    if (!$is_auth_page) {
        return;
    }

    // Google reCAPTCHA
    wp_enqueue_script(
        'google-recaptcha',
        'https://www.google.com/recaptcha/api.js',
        array(),
        null,
        true
    );

    // Auth CSS
    wp_enqueue_style(
        'truyenqq-auth-pages',
        get_template_directory_uri() . '/css/auth-pages.css',
        array(),
        '1.0.3'
    );

    // Auth JS
    wp_enqueue_script(
        'truyenqq-auth-pages',
        get_template_directory_uri() . '/js/auth-pages.js',
        array('jquery'),
        '1.0.3',
        true
    );

    // Localize script
    wp_localize_script('truyenqq-auth-pages', 'truyenqqAuth', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('truyenqq_auth_nonce'),
        'is_logged_in' => is_user_logged_in(),
        'current_user' => TruyenQQ_Auth_Handler::get_current_user()
    ));
}
add_action('wp_enqueue_scripts', 'truyenqq_enqueue_auth_scripts');

/**
 * Add reCAPTCHA settings to admin
 */
function truyenqq_add_recaptcha_settings()
{
    add_settings_section(
        'truyenqq_recaptcha_section',
        'Google reCAPTCHA Settings',
        'truyenqq_recaptcha_section_callback',
        'general'
    );

    add_settings_field(
        'truyenqq_recaptcha_site_key',
        'reCAPTCHA Site Key',
        'truyenqq_recaptcha_site_key_callback',
        'general',
        'truyenqq_recaptcha_section'
    );

    add_settings_field(
        'truyenqq_recaptcha_secret_key',
        'reCAPTCHA Secret Key',
        'truyenqq_recaptcha_secret_key_callback',
        'general',
        'truyenqq_recaptcha_section'
    );

    register_setting('general', 'truyenqq_recaptcha_site_key');
    register_setting('general', 'truyenqq_recaptcha_secret_key');
}
add_action('admin_init', 'truyenqq_add_recaptcha_settings');

function truyenqq_recaptcha_section_callback()
{
    echo '<p>Nhập Site Key và Secret Key từ <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin</a></p>';
}

function truyenqq_recaptcha_site_key_callback()
{
    $value = get_option('truyenqq_recaptcha_site_key', '');
    echo '<input type="text" name="truyenqq_recaptcha_site_key" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Public key để hiển thị reCAPTCHA widget</p>';
}

function truyenqq_recaptcha_secret_key_callback()
{
    $value = get_option('truyenqq_recaptcha_secret_key', '');
    echo '<input type="text" name="truyenqq_recaptcha_secret_key" value="' . esc_attr($value) . '" class="regular-text" />';
    echo '<p class="description">Secret key để verify reCAPTCHA ở backend</p>';
}