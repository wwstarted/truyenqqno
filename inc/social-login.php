<?php
/**
 * Social Authentication Backend
 * Google & Facebook OAuth Integration
 * 
 * @package TruyenQQ
 * @version 1.0.0
 * 
 * Thêm vào functions.php hoặc tạo file riêng và require_once
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue Social Login Scripts
 */
add_action('wp_enqueue_scripts', 'truyenqq_enqueue_social_login_scripts');
function truyenqq_enqueue_social_login_scripts()
{
    // Only load on auth pages
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
        '1.0.0',
        true
    );

    // Localize script with OAuth config
    wp_localize_script('truyenqq-social-login', 'truyenqqAuth', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('truyenqq_auth_nonce'),
        'google_client_id' => get_option('truyenqq_google_client_id', ''),
        'google_redirect_uri' => home_url('/oauth/google/callback'),
        'facebook_app_id' => get_option('truyenqq_facebook_app_id', ''),
        'facebook_redirect_uri' => home_url('/oauth/facebook/callback'),
    ));
}

/**
 * Register OAuth Settings in Admin
 */
add_action('admin_menu', 'truyenqq_add_oauth_settings_page');
function truyenqq_add_oauth_settings_page()
{
    add_options_page(
        'Cài đặt OAuth',
        'OAuth Settings',
        'manage_options',
        'truyenqq-oauth-settings',
        'truyenqq_oauth_settings_page'
    );
}

/**
 * OAuth Settings Page HTML
 */
function truyenqq_oauth_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings
    if (isset($_POST['truyenqq_oauth_settings_nonce'])) {
        check_admin_referer('truyenqq_oauth_settings', 'truyenqq_oauth_settings_nonce');

        update_option('truyenqq_google_client_id', sanitize_text_field($_POST['google_client_id']));
        update_option('truyenqq_google_client_secret', sanitize_text_field($_POST['google_client_secret']));
        update_option('truyenqq_facebook_app_id', sanitize_text_field($_POST['facebook_app_id']));
        update_option('truyenqq_facebook_app_secret', sanitize_text_field($_POST['facebook_app_secret']));

        echo '<div class="notice notice-success"><p>Đã lưu cài đặt OAuth!</p></div>';
    }

    $google_client_id = get_option('truyenqq_google_client_id', '');
    $google_client_secret = get_option('truyenqq_google_client_secret', '');
    $facebook_app_id = get_option('truyenqq_facebook_app_id', '');
    $facebook_app_secret = get_option('truyenqq_facebook_app_secret', '');
    ?>

<div class="wrap">
    <h1>Cài đặt OAuth - Social Login</h1>

    <form method="post" action="">
        <?php wp_nonce_field('truyenqq_oauth_settings', 'truyenqq_oauth_settings_nonce'); ?>

        <h2>Google OAuth 2.0</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="google_client_id">Google Client ID</label>
                </th>
                <td>
                    <input type="text" id="google_client_id" name="google_client_id"
                        value="<?php echo esc_attr($google_client_id); ?>" class="regular-text">
                    <p class="description">
                        Lấy từ <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud
                            Console</a>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="google_client_secret">Google Client Secret</label>
                </th>
                <td>
                    <input type="text" id="google_client_secret" name="google_client_secret"
                        value="<?php echo esc_attr($google_client_secret); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Redirect URI</th>
                <td>
                    <code><?php echo home_url('/oauth/google/callback'); ?></code>
                    <p class="description">Copy URL này vào Google Console</p>
                </td>
            </tr>
        </table>

        <h2>Facebook OAuth</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="facebook_app_id">Facebook App ID</label>
                </th>
                <td>
                    <input type="text" id="facebook_app_id" name="facebook_app_id"
                        value="<?php echo esc_attr($facebook_app_id); ?>" class="regular-text">
                    <p class="description">
                        Lấy từ <a href="https://developers.facebook.com/apps/" target="_blank">Facebook Developers</a>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="facebook_app_secret">Facebook App Secret</label>
                </th>
                <td>
                    <input type="text" id="facebook_app_secret" name="facebook_app_secret"
                        value="<?php echo esc_attr($facebook_app_secret); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Redirect URI</th>
                <td>
                    <code><?php echo home_url('/oauth/facebook/callback'); ?></code>
                    <p class="description">Copy URL này vào Facebook App Settings</p>
                </td>
            </tr>
        </table>

        <?php submit_button('Lưu cài đặt'); ?>
    </form>

    <hr>

    <h2>Hướng dẫn cấu hình</h2>

    <h3>Google OAuth:</h3>
    <ol>
        <li>Truy cập <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
        <li>Tạo project mới hoặc chọn project có sẵn</li>
        <li>Vào <strong>APIs & Services → Credentials</strong></li>
        <li>Click <strong>Create Credentials → OAuth 2.0 Client ID</strong></li>
        <li>Chọn <strong>Web application</strong></li>
        <li>Thêm <strong>Authorized redirect URIs</strong>:
            <code><?php echo home_url('/oauth/google/callback'); ?></code>
        </li>
        <li>Copy <strong>Client ID</strong> và <strong>Client Secret</strong> vào form trên</li>
    </ol>

    <h3>Facebook OAuth:</h3>
    <ol>
        <li>Truy cập <a href="https://developers.facebook.com/" target="_blank">Facebook Developers</a></li>
        <li>Tạo app mới hoặc chọn app có sẵn</li>
        <li>Vào <strong>Settings → Basic</strong></li>
        <li>Copy <strong>App ID</strong> và <strong>App Secret</strong></li>
        <li>Vào <strong>Facebook Login → Settings</strong></li>
        <li>Thêm <strong>Valid OAuth Redirect URIs</strong>:
            <code><?php echo home_url('/oauth/facebook/callback'); ?></code>
        </li>
        <li>Paste App ID và Secret vào form trên</li>
    </ol>
</div>

<?php
}

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
    if (!isset($_GET['code'])) {
        truyenqq_oauth_error('Không nhận được mã xác thực từ Google');
        return;
    }

    $code = sanitize_text_field($_GET['code']);
    $client_id = get_option('truyenqq_google_client_id');
    $client_secret = get_option('truyenqq_google_client_secret');
    $redirect_uri = home_url('/oauth/google/callback');

    // Exchange code for access token
    $token_response = wp_remote_post('https://oauth2.googleapis.com/token', array(
        'body' => array(
            'code' => $code,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code',
        ),
    ));

    if (is_wp_error($token_response)) {
        truyenqq_oauth_error('Lỗi kết nối Google: ' . $token_response->get_error_message());
        return;
    }

    $token_data = json_decode(wp_remote_retrieve_body($token_response), true);

    if (!isset($token_data['access_token'])) {
        truyenqq_oauth_error('Không nhận được access token từ Google');
        return;
    }

    // Get user info
    $user_response = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token_data['access_token'],
        ),
    ));

    if (is_wp_error($user_response)) {
        truyenqq_oauth_error('Không thể lấy thông tin người dùng từ Google');
        return;
    }

    $user_data = json_decode(wp_remote_retrieve_body($user_response), true);

    // Create or login user
    $user = truyenqq_oauth_create_or_login_user($user_data, 'google');

    if (is_wp_error($user)) {
        truyenqq_oauth_error($user->get_error_message());
        return;
    }

    // Login success
    truyenqq_oauth_success();
}

/**
 * Handle Facebook OAuth Callback
 */
function truyenqq_handle_facebook_callback()
{
    if (!isset($_GET['code'])) {
        truyenqq_oauth_error('Không nhận được mã xác thực từ Facebook');
        return;
    }

    $code = sanitize_text_field($_GET['code']);
    $app_id = get_option('truyenqq_facebook_app_id');
    $app_secret = get_option('truyenqq_facebook_app_secret');
    $redirect_uri = home_url('/oauth/facebook/callback');

    // Exchange code for access token
    $token_url = 'https://graph.facebook.com/v18.0/oauth/access_token?' . http_build_query(array(
        'client_id' => $app_id,
        'client_secret' => $app_secret,
        'redirect_uri' => $redirect_uri,
        'code' => $code,
    ));

    $token_response = wp_remote_get($token_url);

    if (is_wp_error($token_response)) {
        truyenqq_oauth_error('Lỗi kết nối Facebook: ' . $token_response->get_error_message());
        return;
    }

    $token_data = json_decode(wp_remote_retrieve_body($token_response), true);

    if (!isset($token_data['access_token'])) {
        truyenqq_oauth_error('Không nhận được access token từ Facebook');
        return;
    }

    // Get user info
    $user_url = 'https://graph.facebook.com/v18.0/me?fields=id,name,email&access_token=' . $token_data['access_token'];
    $user_response = wp_remote_get($user_url);

    if (is_wp_error($user_response)) {
        truyenqq_oauth_error('Không thể lấy thông tin người dùng từ Facebook');
        return;
    }

    $user_data = json_decode(wp_remote_retrieve_body($user_response), true);

    // Create or login user
    $user = truyenqq_oauth_create_or_login_user($user_data, 'facebook');

    if (is_wp_error($user)) {
        truyenqq_oauth_error($user->get_error_message());
        return;
    }

    // Login success
    truyenqq_oauth_success();
}

/**
 * Create or Login User from OAuth Data
 */
function truyenqq_oauth_create_or_login_user($oauth_data, $provider)
{
    $email = isset($oauth_data['email']) ? sanitize_email($oauth_data['email']) : '';

    if (empty($email)) {
        return new WP_Error('no_email', 'Không thể lấy email từ ' . $provider);
    }

    // Check if user exists
    $user = get_user_by('email', $email);

    if ($user) {
        // User exists, login
        wp_set_auth_cookie($user->ID, true);
        return $user;
    }

    // Create new user
    $username = sanitize_user($email);
    $name = isset($oauth_data['name']) ? sanitize_text_field($oauth_data['name']) : '';

    // Generate unique username if needed
    $base_username = $username;
    $counter = 1;
    while (username_exists($username)) {
        $username = $base_username . $counter;
        $counter++;
    }

    $user_id = wp_create_user($username, wp_generate_password(), $email);

    if (is_wp_error($user_id)) {
        return $user_id;
    }

    // Update user meta
    wp_update_user(array(
        'ID' => $user_id,
        'display_name' => $name,
    ));

    update_user_meta($user_id, 'oauth_provider', $provider);
    update_user_meta($user_id, 'oauth_id', $oauth_data['id']);

    // Login
    $user = get_user_by('id', $user_id);
    wp_set_auth_cookie($user->ID, true);

    return $user;
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
    <title>Đăng nhập thành công</title>
</head>

<body>
    <script>
    window.opener.postMessage({
        type: '<?php echo esc_js(get_query_var('oauth_provider')); ?>-login-success',
        redirect: '<?php echo esc_url(home_url()); ?>'
    }, window.location.origin);
    window.close();
    </script>
    <p>Đăng nhập thành công! Đang đóng cửa sổ...</p>
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
    ?>
<!DOCTYPE html>
<html>

<head>
    <title>Lỗi đăng nhập</title>
</head>

<body>
    <script>
    window.opener.postMessage({
        type: '<?php echo esc_js(get_query_var('oauth_provider')); ?>-login-error',
        message: '<?php echo esc_js($message); ?>'
    }, window.location.origin);
    window.close();
    </script>
    <p>Có lỗi xảy ra:
        <?php echo esc_html($message); ?>
    </p>
    <p>Đang đóng cửa sổ...</p>
</body>

</html>
<?php
    exit;
}

// Don't forget to flush rewrite rules after adding this code
// Go to Settings → Permalinks and click "Save Changes"