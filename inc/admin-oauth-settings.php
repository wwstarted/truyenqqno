<?php
/**
 * Admin OAuth Settings Page
 * Cấu hình Google & Facebook OAuth trong WordPress Admin
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add OAuth Settings Menu
 */
add_action('admin_menu', 'truyenqq_add_oauth_settings_menu');
function truyenqq_add_oauth_settings_menu()
{
    add_options_page(
        'Cài đặt OAuth',           // Page title
        'OAuth Settings',          // Menu title
        'manage_options',          // Capability
        'truyenqq-oauth-settings', // Menu slug
        'truyenqq_render_oauth_settings_page' // Callback
    );
}

/**
 * Register OAuth Settings
 */
add_action('admin_init', 'truyenqq_register_oauth_settings');
function truyenqq_register_oauth_settings()
{
    // Google OAuth
    register_setting('truyenqq_oauth_settings', 'truyenqq_google_client_id', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => ''
    ));

    register_setting('truyenqq_oauth_settings', 'truyenqq_google_client_secret', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => ''
    ));

    // Facebook OAuth
    register_setting('truyenqq_oauth_settings', 'truyenqq_facebook_app_id', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => ''
    ));

    register_setting('truyenqq_oauth_settings', 'truyenqq_facebook_app_secret', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => ''
    ));
}

/**
 * Render OAuth Settings Page
 */
function truyenqq_render_oauth_settings_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('Bạn không có quyền truy cập trang này.'));
    }

    // Get current values
    $google_client_id = get_option('truyenqq_google_client_id', '');
    $google_client_secret = get_option('truyenqq_google_client_secret', '');
    $facebook_app_id = get_option('truyenqq_facebook_app_id', '');
    $facebook_app_secret = get_option('truyenqq_facebook_app_secret', '');

    // Check if settings are saved
    $is_google_configured = !empty($google_client_id) && !empty($google_client_secret);
    $is_facebook_configured = !empty($facebook_app_id) && !empty($facebook_app_secret);

    ?>
<div class="wrap">
    <h1>
        <span class="dashicons dashicons-admin-network" style="font-size: 32px;"></span>
        Cài đặt OAuth - Social Login
    </h1>

    <?php settings_errors(); ?>

    <!-- Status Cards -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
        <!-- Google Status -->
        <div class="oauth-status-card"
            style="background: <?php echo $is_google_configured ? '#d4edda' : '#fff3cd'; ?>; padding: 20px; border-radius: 8px; border-left: 4px solid <?php echo $is_google_configured ? '#28a745' : '#ffc107'; ?>;">
            <h3 style="margin: 0 0 10px 0; display: flex; align-items: center; gap: 10px;">
                <span class="dashicons dashicons-google"
                    style="font-size: 24px; color: <?php echo $is_google_configured ? '#28a745' : '#ffc107'; ?>;"></span>
                Google OAuth
            </h3>
            <p style="margin: 0; color: #666;">
                Trạng thái:
                <strong style="color: <?php echo $is_google_configured ? '#28a745' : '#ffc107'; ?>;">
                    <?php echo $is_google_configured ? '✓ Đã cấu hình' : '⚠ Chưa cấu hình'; ?>
                </strong>
            </p>
        </div>

        <!-- Facebook Status -->
        <div class="oauth-status-card"
            style="background: <?php echo $is_facebook_configured ? '#d4edda' : '#fff3cd'; ?>; padding: 20px; border-radius: 8px; border-left: 4px solid <?php echo $is_facebook_configured ? '#28a745' : '#ffc107'; ?>;">
            <h3 style="margin: 0 0 10px 0; display: flex; align-items: center; gap: 10px;">
                <span class="dashicons dashicons-facebook"
                    style="font-size: 24px; color: <?php echo $is_facebook_configured ? '#28a745' : '#ffc107'; ?>;"></span>
                Facebook OAuth
            </h3>
            <p style="margin: 0; color: #666;">
                Trạng thái:
                <strong style="color: <?php echo $is_facebook_configured ? '#28a745' : '#ffc107'; ?>;">
                    <?php echo $is_facebook_configured ? '✓ Đã cấu hình' : '⚠ Chưa cấu hình'; ?>
                </strong>
            </p>
        </div>
    </div>

    <!-- Settings Form -->
    <form method="post" action="options.php"
        style="background: #fff; padding: 30px; border-radius: 8px; margin-top: 20px;">
        <?php settings_fields('truyenqq_oauth_settings'); ?>

        <!-- Google OAuth Section -->
        <div class="oauth-section" style="margin-bottom: 40px; padding-bottom: 40px; border-bottom: 1px solid #ddd;">
            <h2 style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                <span class="dashicons dashicons-google" style="font-size: 28px; color: #4285f4;"></span>
                Google OAuth 2.0
            </h2>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="google_client_id">Google Client ID</label>
                    </th>
                    <td>
                        <input type="text" id="google_client_id" name="truyenqq_google_client_id"
                            value="<?php echo esc_attr($google_client_id); ?>" class="regular-text"
                            placeholder="123456789-abcdefg.apps.googleusercontent.com">
                        <p class="description">
                            Lấy từ <a href="https://console.cloud.google.com/apis/credentials" target="_blank"
                                rel="noopener">Google Cloud Console</a>
                            | <a href="#google-guide" onclick="toggleGuide('google')">📖 Xem hướng dẫn</a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="google_client_secret">Google Client Secret</label>
                    </th>
                    <td>
                        <input type="password" id="google_client_secret" name="truyenqq_google_client_secret"
                            value="<?php echo esc_attr($google_client_secret); ?>" class="regular-text"
                            placeholder="GOCSPX-...">
                        <button type="button" class="button" onclick="togglePassword('google_client_secret')">
                            <span class="dashicons dashicons-visibility"></span> Hiện
                        </button>
                        <p class="description">Secret key để xác thực với Google API</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Redirect URI</th>
                    <td>
                        <code
                            style="background: #f0f0f0; padding: 8px 12px; border-radius: 4px; display: inline-block;"><?php echo home_url('/oauth/google/callback'); ?></code>
                        <button type="button" class="button"
                            onclick="copyToClipboard('<?php echo esc_js(home_url('/oauth/google/callback')); ?>')">
                            <span class="dashicons dashicons-clipboard"></span> Copy
                        </button>
                        <p class="description">Copy URL này vào <strong>Authorized redirect URIs</strong> trong Google
                            Console</p>
                    </td>
                </tr>
            </table>

            <!-- Google Guide (Hidden by default) -->
            <div id="google-guide" class="oauth-guide" style="display: none; margin-top: 20px;">
                <?php truyenqq_render_google_guide(); ?>
            </div>
        </div>

        <!-- Facebook OAuth Section -->
        <div class="oauth-section" style="margin-bottom: 30px;">
            <h2 style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                <span class="dashicons dashicons-facebook" style="font-size: 28px; color: #1877f2;"></span>
                Facebook OAuth
            </h2>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="facebook_app_id">Facebook App ID</label>
                    </th>
                    <td>
                        <input type="text" id="facebook_app_id" name="truyenqq_facebook_app_id"
                            value="<?php echo esc_attr($facebook_app_id); ?>" class="regular-text"
                            placeholder="1234567890123456">
                        <p class="description">
                            Lấy từ <a href="https://developers.facebook.com/apps/" target="_blank"
                                rel="noopener">Facebook Developers</a>
                            | <a href="#facebook-guide" onclick="toggleGuide('facebook')">📖 Xem hướng dẫn</a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="facebook_app_secret">Facebook App Secret</label>
                    </th>
                    <td>
                        <input type="password" id="facebook_app_secret" name="truyenqq_facebook_app_secret"
                            value="<?php echo esc_attr($facebook_app_secret); ?>" class="regular-text"
                            placeholder="a1b2c3d4e5f6...">
                        <button type="button" class="button" onclick="togglePassword('facebook_app_secret')">
                            <span class="dashicons dashicons-visibility"></span> Hiện
                        </button>
                        <p class="description">Secret key để xác thực với Facebook API</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Redirect URI</th>
                    <td>
                        <code
                            style="background: #f0f0f0; padding: 8px 12px; border-radius: 4px; display: inline-block;"><?php echo home_url('/oauth/facebook/callback'); ?></code>
                        <button type="button" class="button"
                            onclick="copyToClipboard('<?php echo esc_js(home_url('/oauth/facebook/callback')); ?>')">
                            <span class="dashicons dashicons-clipboard"></span> Copy
                        </button>
                        <p class="description">Copy URL này vào <strong>Valid OAuth Redirect URIs</strong> trong
                            Facebook App Settings</p>
                    </td>
                </tr>
            </table>

            <!-- Facebook Guide (Hidden by default) -->
            <div id="facebook-guide" class="oauth-guide" style="display: none; margin-top: 20px;">
                <?php truyenqq_render_facebook_guide(); ?>
            </div>
        </div>

        <?php submit_button('Lưu cài đặt', 'primary large'); ?>
    </form>

    <!-- Quick Actions -->
    <div class="oauth-actions" style="margin-top: 20px; display: flex; gap: 10px;">
        <a href="<?php echo home_url('/dang-nhap'); ?>" class="button button-secondary" target="_blank">
            <span class="dashicons dashicons-external"></span> Test đăng nhập
        </a>
        <button type="button" class="button button-secondary" onclick="flushRewriteRules()">
            <span class="dashicons dashicons-update"></span> Flush Rewrite Rules
        </button>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    field.type = field.type === 'password' ? 'text' : 'password';
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('✓ Đã copy: ' + text);
    });
}

function toggleGuide(provider) {
    const guide = document.getElementById(provider + '-guide');
    guide.style.display = guide.style.display === 'none' ? 'block' : 'none';
}

function flushRewriteRules() {
    if (!confirm('Bạn có chắc muốn flush rewrite rules?\nLàm điều này sau khi thay đổi cấu hình OAuth.')) {
        return;
    }

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=truyenqq_flush_rewrite_rules&nonce=<?php echo wp_create_nonce('flush_rewrite'); ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ Đã flush rewrite rules thành công!');
            } else {
                alert('✗ Có lỗi xảy ra: ' + data.data.message);
            }
        });
}
</script>

<style>
.oauth-guide {
    background: #f9f9f9;
    border-left: 4px solid #2271b1;
    padding: 20px;
    border-radius: 4px;
}

.oauth-guide h3 {
    margin-top: 0;
    color: #2271b1;
}

.oauth-guide ol {
    margin-left: 20px;
}

.oauth-guide code {
    background: #fff;
    padding: 2px 6px;
    border-radius: 3px;
    color: #d63638;
}
</style>
<?php
}

/**
 * Render Google Setup Guide
 */
function truyenqq_render_google_guide()
{
    ?>
<div class="oauth-guide-content">
    <h3>📖 Hướng dẫn cấu hình Google OAuth</h3>
    <ol>
        <li>Truy cập <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
        <li>Tạo project mới hoặc chọn project có sẵn</li>
        <li>Vào <strong>APIs & Services → Credentials</strong></li>
        <li>Click <strong>Create Credentials → OAuth 2.0 Client ID</strong></li>
        <li>Nếu chưa cấu hình OAuth consent screen:
            <ul>
                <li>Click <strong>Configure Consent Screen</strong></li>
                <li>Chọn <strong>External</strong></li>
                <li>Điền thông tin app</li>
                <li>Click <strong>Save and Continue</strong></li>
            </ul>
        </li>
        <li>Chọn <strong>Web application</strong></li>
        <li>Thêm <strong>Authorized redirect URIs</strong>:
            <code><?php echo home_url('/oauth/google/callback'); ?></code>
        </li>
        <li>Click <strong>Create</strong></li>
        <li>Copy <strong>Client ID</strong> và <strong>Client Secret</strong> vào form trên</li>
        <li>Click <strong>Lưu cài đặt</strong></li>
    </ol>
    <p><strong>Lưu ý:</strong> App phải ở chế độ <strong>Production</strong> hoặc thêm email test users trong
        Development
        mode.</p>
</div>
<?php
}

/**
 * Render Facebook Setup Guide
 */
function truyenqq_render_facebook_guide()
{
    ?>
<div class="oauth-guide-content">
    <h3>📖 Hướng dẫn cấu hình Facebook OAuth</h3>
    <ol>
        <li>Truy cập <a href="https://developers.facebook.com/" target="_blank">Facebook Developers</a></li>
        <li>Click <strong>My Apps → Create App</strong></li>
        <li>Chọn <strong>Consumer → Next</strong></li>
        <li>Điền thông tin app</li>
        <li>Trong dashboard app, vào <strong>Add Products</strong></li>
        <li>Tìm <strong>Facebook Login → Set Up</strong></li>
        <li>Chọn <strong>Web</strong></li>
        <li>Vào <strong>Facebook Login → Settings</strong></li>
        <li>Thêm <strong>Valid OAuth Redirect URIs</strong>:
            <code><?php echo home_url('/oauth/facebook/callback'); ?></code>
        </li>
        <li>Click <strong>Save Changes</strong></li>
        <li>Vào <strong>Settings → Basic</strong></li>
        <li>Copy <strong>App ID</strong> và <strong>App Secret</strong> vào form trên</li>
        <li><strong>Quan trọng:</strong> Chuyển app từ <strong>Development → Live</strong> mode</li>
        <li>Click <strong>Lưu cài đặt</strong></li>
    </ol>
    <p><strong>Lưu ý:</strong> App phải ở chế độ <strong>Live</strong> thì mới hoạt động với tất cả users.</p>
</div>
<?php
}

/**
 * AJAX: Flush Rewrite Rules
 */
add_action('wp_ajax_truyenqq_flush_rewrite_rules', 'truyenqq_ajax_flush_rewrite_rules');
function truyenqq_ajax_flush_rewrite_rules()
{
    check_ajax_referer('flush_rewrite', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Không có quyền'));
    }

    flush_rewrite_rules();

    wp_send_json_success(array('message' => 'Đã flush rewrite rules'));
}