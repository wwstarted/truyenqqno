<?php
/**
 * AJAX Authentication Handlers with reCAPTCHA
 * 
 * @package TruyenQQ
 * @version 1.0.1 - FIXED
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include required classes
require_once get_template_directory() . '/inc/class-truyenqq-otp-manager.php';
require_once get_template_directory() . '/inc/class-truyenqq-auth-handler.php';

/**
 * Verify Google reCAPTCHA
 * 
 * @param string $response reCAPTCHA response token
 * @return bool True if valid, False if invalid
 */
function truyenqq_verify_recaptcha($response)
{
    if (empty($response)) {
        return false;
    }

    $secret_key = get_option('truyenqq_recaptcha_secret_key');

    if (empty($secret_key)) {
        // If no secret key configured, skip verification (for testing)
        error_log('TruyenQQ: reCAPTCHA secret key not configured');
        return true;
    }

    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';

    $response = wp_remote_post($verify_url, array(
        'body' => array(
            'secret' => $secret_key,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        )
    ));

    if (is_wp_error($response)) {
        error_log('TruyenQQ reCAPTCHA Error: ' . $response->get_error_message());
        return false;
    }

    $response_body = wp_remote_retrieve_body($response);
    $result = json_decode($response_body, true);

    return isset($result['success']) && $result['success'] === true;
}

/**
 * AJAX: Register - Send OTP
 */
function truyenqq_ajax_register_send_otp()
{
    check_ajax_referer('truyenqq_auth_nonce', 'nonce');

    // Verify reCAPTCHA
    $recaptcha_response = isset($_POST['recaptcha_response']) ? $_POST['recaptcha_response'] : '';
    if (!truyenqq_verify_recaptcha($recaptcha_response)) {
        wp_send_json(array(
            'success' => false,
            'message' => 'Xác thực reCAPTCHA thất bại. Vui lòng thử lại.'
        ));
    }

    $username = sanitize_user($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];

    $result = TruyenQQ_Auth_Handler::register_send_otp($username, $email, $password);

    wp_send_json($result);
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

    wp_send_json($result);
}
add_action('wp_ajax_nopriv_register_verify_otp', 'truyenqq_ajax_register_verify_otp');

/**
 * AJAX: Login
 */
function truyenqq_ajax_login()
{
    check_ajax_referer('truyenqq_auth_nonce', 'nonce');

    // Verify reCAPTCHA
    $recaptcha_response = isset($_POST['recaptcha_response']) ? $_POST['recaptcha_response'] : '';
    if (!truyenqq_verify_recaptcha($recaptcha_response)) {
        wp_send_json(array(
            'success' => false,
            'message' => 'Xác thực reCAPTCHA thất bại. Vui lòng thử lại.'
        ));
    }

    $username = sanitize_text_field($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? (bool) $_POST['remember'] : false;

    $result = TruyenQQ_Auth_Handler::login($username, $password, $remember);

    wp_send_json($result);
}
add_action('wp_ajax_nopriv_login', 'truyenqq_ajax_login');

/**
 * AJAX: Logout
 */
function truyenqq_ajax_logout()
{
    check_ajax_referer('truyenqq_auth_nonce', 'nonce');

    $result = TruyenQQ_Auth_Handler::logout();

    wp_send_json($result);
}
add_action('wp_ajax_logout', 'truyenqq_ajax_logout');

/**
 * AJAX: Forgot Password - Send OTP
 */
function truyenqq_ajax_forgot_password_send_otp()
{
    check_ajax_referer('truyenqq_auth_nonce', 'nonce');

    // Verify reCAPTCHA
    $recaptcha_response = isset($_POST['recaptcha_response']) ? $_POST['recaptcha_response'] : '';
    if (!truyenqq_verify_recaptcha($recaptcha_response)) {
        wp_send_json(array(
            'success' => false,
            'message' => 'Xác thực reCAPTCHA thất bại. Vui lòng thử lại.'
        ));
    }

    $email = sanitize_email($_POST['email']);

    $result = TruyenQQ_Auth_Handler::forgot_password_send_otp($email);

    wp_send_json($result);
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

    wp_send_json($result);
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

    wp_send_json($result);
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

    wp_send_json($result);
}
add_action('wp_ajax_nopriv_resend_otp', 'truyenqq_ajax_resend_otp');

/**
 * Enqueue scripts and styles - FIXED VERSION
 */
function truyenqq_enqueue_auth_scripts()
{
    // Check if on auth pages
    $is_auth_page = is_page_template('template-login.php') ||
        is_page_template('template-register.php') ||
        is_page_template('template-forgot-password.php');

    if (!$is_auth_page) {
        return;
    }

    // Enqueue Google reCAPTCHA
    wp_enqueue_script(
        'google-recaptcha',
        'https://www.google.com/recaptcha/api.js',
        array(),
        null,
        true
    );

    // Enqueue auth pages CSS
    wp_enqueue_style(
        'truyenqq-auth-pages',
        get_template_directory_uri() . '/css/auth-pages.css',
        array(),
        '1.0.1'
    );

    // Enqueue auth pages JS - DEPENDENCY: jquery AND google-recaptcha
    wp_enqueue_script(
        'truyenqq-auth-pages',
        get_template_directory_uri() . '/js/auth-pages.js',
        array('jquery'), // Removed google-recaptcha from dependency
        '1.0.1',
        true // Load in footer
    );

    // Localize script - MUST be after wp_enqueue_script
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

/**
 * Debug helper - Remove after testing
 */
function truyenqq_debug_scripts()
{
    if (
        !is_page_template('template-login.php') &&
        !is_page_template('template-register.php') &&
        !is_page_template('template-forgot-password.php')
    ) {
        return;
    }

    global $wp_scripts;
    echo '<!-- DEBUG: Enqueued Scripts -->';
    echo '<script>console.log("WordPress Scripts:", ' . json_encode(array_keys($wp_scripts->registered)) . ');</script>';
}
add_action('wp_footer', 'truyenqq_debug_scripts', 999);