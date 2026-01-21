<?php
/**
 * Social Authentication Backend - FIXED VERSION
 * Google & Facebook OAuth Integration
 * 
 * @package TruyenQQ
 * @version 1.0.3 - REMOVED DUPLICATE SETTINGS PAGE
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue Social Login Scripts
 */
add_action('wp_enqueue_scripts', 'truyenqq_enqueue_social_login_scripts');
function truyenqq_enqueue_social_login_scripts()
{

    if (
        !is_page_template('template-login.php') &&
        !is_page_template('template-register.php')
    ) {
        return;
    }

    wp_enqueue_script(
        'truyenqq-social-login',
        get_template_directory_uri() . '/js/social-login.js',
        array('jquery'),
        '1.0.3',
        true
    );


    wp_localize_script('truyenqq-social-login', 'truyenqqOAuth', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('truyenqq_auth_nonce'),
        'google_client_id' => get_option('truyenqq_google_client_id', ''),
        'google_redirect_uri' => home_url('/oauth/google/callback'),
        'facebook_app_id' => get_option('truyenqq_facebook_app_id', ''),
        'facebook_redirect_uri' => home_url('/oauth/facebook/callback'),
    ));
}

/* ========================================================================
   NOTE: OAuth Settings Page được quản lý trong admin-oauth-settings.php
   Đã xóa duplicate code để tránh trùng lặp
   ======================================================================== */

/**
 * Register OAuth Callback Endpoints
 */
add_action('init', 'truyenqq_register_oauth_endpoints');
function truyenqq_register_oauth_endpoints()
{
    add_rewrite_rule(
        '^oauth/google/callback/?$',
        'index.php?oauth_provider=google',
        'top'
    );

    add_rewrite_rule(
        '^oauth/facebook/callback/?$',
        'index.php?oauth_provider=facebook',
        'top'
    );
}

add_filter('query_vars', 'truyenqq_oauth_query_vars');
function truyenqq_oauth_query_vars($vars)
{
    $vars[] = 'oauth_provider';
    return $vars;
}

/**
 * Flush rewrite rules on theme activation
 */
add_action('after_switch_theme', 'truyenqq_oauth_flush_rewrite_rules');
function truyenqq_oauth_flush_rewrite_rules()
{
    truyenqq_register_oauth_endpoints();
    flush_rewrite_rules();
}

/**
 * Handle OAuth Callbacks
 */
add_action('template_redirect', 'truyenqq_handle_oauth_callback');
function truyenqq_handle_oauth_callback()
{
    $provider = get_query_var('oauth_provider');

    if ($provider === 'google') {
        truyenqq_handle_google_callback();
    } elseif ($provider === 'facebook') {
        truyenqq_handle_facebook_callback();
    }
}

/**
 * Handle Google OAuth Callback
 */
function truyenqq_handle_google_callback()
{

    if (isset($_GET['error'])) {
        truyenqq_oauth_error('Đăng nhập Google bị hủy: ' . sanitize_text_field($_GET['error']));
        return;
    }

    if (!isset($_GET['code'])) {
        truyenqq_oauth_error('Không nhận được mã xác thực từ Google');
        return;
    }

    $code = sanitize_text_field($_GET['code']);
    $client_id = get_option('truyenqq_google_client_id');
    $client_secret = get_option('truyenqq_google_client_secret');
    $redirect_uri = home_url('/oauth/google/callback');


    if (empty($client_id) || empty($client_secret)) {
        truyenqq_oauth_error('Google OAuth chưa được cấu hình. Vui lòng liên hệ quản trị viên.');
        return;
    }


    $token_response = wp_remote_post('https://oauth2.googleapis.com/token', array(
        'body' => array(
            'code' => $code,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code',
        ),
        'timeout' => 15,
    ));

    if (is_wp_error($token_response)) {
        truyenqq_oauth_error('Lỗi kết nối Google: ' . $token_response->get_error_message());
        return;
    }

    $token_data = json_decode(wp_remote_retrieve_body($token_response), true);

    if (!isset($token_data['access_token'])) {
        $error_msg = isset($token_data['error_description']) ? $token_data['error_description'] : 'Không nhận được access token';
        truyenqq_oauth_error('Lỗi Google: ' . $error_msg);
        return;
    }


    $user_response = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token_data['access_token'],
        ),
        'timeout' => 15,
    ));

    if (is_wp_error($user_response)) {
        truyenqq_oauth_error('Không thể lấy thông tin người dùng từ Google');
        return;
    }

    $user_data = json_decode(wp_remote_retrieve_body($user_response), true);

    if (!isset($user_data['id'])) {
        truyenqq_oauth_error('Dữ liệu người dùng từ Google không hợp lệ');
        return;
    }


    require_once get_template_directory() . '/inc/class-truyenqq-auth-handler.php';
    $user = TruyenQQ_Auth_Handler::oauth_create_or_login_user($user_data, 'google');

    if (is_wp_error($user)) {
        truyenqq_oauth_error($user->get_error_message());
        return;
    }


    truyenqq_oauth_success();
}

/**
 * Handle Facebook OAuth Callback
 */
function truyenqq_handle_facebook_callback()
{

    if (isset($_GET['error'])) {
        $error_description = isset($_GET['error_description']) ? sanitize_text_field($_GET['error_description']) : sanitize_text_field($_GET['error']);
        truyenqq_oauth_error('Đăng nhập Facebook bị hủy: ' . $error_description);
        return;
    }

    if (!isset($_GET['code'])) {
        truyenqq_oauth_error('Không nhận được mã xác thực từ Facebook');
        return;
    }

    $code = sanitize_text_field($_GET['code']);
    $app_id = get_option('truyenqq_facebook_app_id');
    $app_secret = get_option('truyenqq_facebook_app_secret');
    $redirect_uri = home_url('/oauth/facebook/callback');


    if (empty($app_id) || empty($app_secret)) {
        truyenqq_oauth_error('Facebook OAuth chưa được cấu hình. Vui lòng liên hệ quản trị viên.');
        return;
    }


    $token_url = 'https://graph.facebook.com/v18.0/oauth/access_token?' . http_build_query(array(
        'client_id' => $app_id,
        'client_secret' => $app_secret,
        'redirect_uri' => $redirect_uri,
        'code' => $code,
    ));

    $token_response = wp_remote_get($token_url, array('timeout' => 15));

    if (is_wp_error($token_response)) {
        truyenqq_oauth_error('Lỗi kết nối Facebook: ' . $token_response->get_error_message());
        return;
    }

    $token_data = json_decode(wp_remote_retrieve_body($token_response), true);

    if (!isset($token_data['access_token'])) {
        $error_msg = isset($token_data['error']['message']) ? $token_data['error']['message'] : 'Không nhận được access token';
        truyenqq_oauth_error('Lỗi Facebook: ' . $error_msg);
        return;
    }


    $user_url = 'https://graph.facebook.com/v18.0/me?fields=id,name,email,picture.type(large)&access_token=' . $token_data['access_token'];
    $user_response = wp_remote_get($user_url, array('timeout' => 15));

    if (is_wp_error($user_response)) {
        truyenqq_oauth_error('Không thể lấy thông tin người dùng từ Facebook');
        return;
    }

    $user_data = json_decode(wp_remote_retrieve_body($user_response), true);

    if (!isset($user_data['id'])) {
        truyenqq_oauth_error('Dữ liệu người dùng từ Facebook không hợp lệ');
        return;
    }


    require_once get_template_directory() . '/inc/class-truyenqq-auth-handler.php';
    $user = TruyenQQ_Auth_Handler::oauth_create_or_login_user($user_data, 'facebook');

    if (is_wp_error($user)) {
        truyenqq_oauth_error($user->get_error_message());
        return;
    }


    truyenqq_oauth_success();
}

/**
 * OAuth Success Response
 */
function truyenqq_oauth_success()
{
    ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Đăng nhập thành công</title>
    <style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .container {
        text-align: center;
    }

    .spinner {
        border: 4px solid rgba(255, 255, 255, 0.3);
        border-top: 4px solid white;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="spinner"></div>
        <h2>✓ Đăng nhập thành công!</h2>
        <p>Đang chuyển hướng...</p>
    </div>

    <script>
    if (window.opener) {
        window.opener.postMessage({
            type: '<?php echo esc_js(get_query_var('oauth_provider')); ?>-login-success',
            redirect: '<?php echo esc_url(home_url()); ?>'
        }, window.location.origin);
    }


    setTimeout(function() {
        window.close();
    }, 1000);
    </script>
</body>

</html>
<?php
    exit;
}

/**
 * OAuth Error Response
 */
function truyenqq_oauth_error($message)
{

    error_log('TruyenQQ OAuth Error: ' . $message);
    ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Lỗi đăng nhập</title>
    <style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .container {
        text-align: center;
        max-width: 500px;
        padding: 40px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        backdrop-filter: blur(10px);
    }

    .error-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }

    button {
        margin-top: 20px;
        padding: 12px 24px;
        background: white;
        color: #f5576c;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        font-weight: 600;
    }

    button:hover {
        background: #f0f0f0;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="error-icon">✗</div>
        <h2>Đăng nhập thất bại</h2>
        <p><?php echo esc_html($message); ?></p>
        <button onclick="window.close()">Đóng cửa sổ</button>
    </div>

    <script>
    if (window.opener) {
        window.opener.postMessage({
            type: '<?php echo esc_js(get_query_var('oauth_provider')); ?>-login-error',
            message: '<?php echo esc_js($message); ?>'
        }, window.location.origin);
    }


    setTimeout(function() {
        window.close();
    }, 5000);
    </script>
</body>

</html>
<?php
    exit;
}