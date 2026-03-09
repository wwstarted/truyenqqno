<?php
/**
 * Authentication Handler Class

 * 
 * @package TruyenQQ
 * @version 1.1.1 - OAUTH INTEGRATED 
 */

if (!defined('ABSPATH')) {
    exit;
}

class TruyenQQ_Auth_Handler
{
    /**
     * Register new user (Step 1: Send OTP)
     * 
     * @param string $username Username
     * @param string $email Email
     * @param string $password Password
     * @return array Response
     */
    public static function register_send_otp($username, $email, $password)
    {
        if (empty($username) || empty($email) || empty($password)) {
            return array(
                'success' => false,
                'message' => 'Vui lòng điền đầy đủ thông tin'
            );
        }

        if (username_exists($username)) {
            return array(
                'success' => false,
                'message' => 'Tên đăng nhập đã tồn tại'
            );
        }

        if (!validate_username($username)) {
            return array(
                'success' => false,
                'message' => 'Tên đăng nhập không hợp lệ. Chỉ sử dụng chữ, số và dấu gạch dưới.'
            );
        }

        if (!is_email($email)) {
            return array(
                'success' => false,
                'message' => 'Email không hợp lệ'
            );
        }

        if (email_exists($email)) {
            return array(
                'success' => false,
                'message' => 'Email đã được sử dụng'
            );
        }

        if (strlen($password) < 6) {
            return array(
                'success' => false,
                'message' => 'Mật khẩu phải có ít nhất 6 ký tự'
            );
        }

        $reg_data = array(
            'username' => $username,
            'email' => $email,
            'password' => $password
        );
        set_transient('truyenqq_reg_' . md5($email), $reg_data, 600);

        require_once get_template_directory() . '/inc/class-truyenqq-otp-manager.php';
        $otp_result = TruyenQQ_OTP_Manager::send_otp($email, 'register');

        return $otp_result;
    }

    /**
     * Register new user (Step 2: Verify OTP and create account)
     * 
     * @param string $email Email
     * @param string $otp_code OTP code
     * @return array Response
     */
    public static function register_verify_otp($email, $otp_code)
    {
        require_once get_template_directory() . '/inc/class-truyenqq-otp-manager.php';
        $verify_result = TruyenQQ_OTP_Manager::verify_otp($email, $otp_code, 'register');

        if (!$verify_result['success']) {
            return $verify_result;
        }

        $reg_data = get_transient('truyenqq_reg_' . md5($email));

        if (!$reg_data) {
            return array(
                'success' => false,
                'message' => 'Phiên đăng ký đã hết hạn. Vui lòng thử lại.'
            );
        }

        $user_id = wp_create_user(
            $reg_data['username'],
            $reg_data['password'],
            $reg_data['email']
        );

        if (is_wp_error($user_id)) {
            return array(
                'success' => false,
                'message' => 'Lỗi tạo tài khoản: ' . $user_id->get_error_message()
            );
        }

        $user = new WP_User($user_id);
        $user->set_role('subscriber');

        global $wpdb;
        $user_meta_table = $wpdb->prefix . 'nettruyen_user_meta';
        $wpdb->insert(
            $user_meta_table,
            array(
                'user_id' => $user_id,
                'email_verified' => 1,
                'email_verified_at' => current_time('mysql'),
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s')
        );

        delete_transient('truyenqq_reg_' . md5($email));

        wp_set_auth_cookie($user_id, true);
        do_action('wp_login', $reg_data['username'], $user);

        self::log_login_history($user_id, 'success');

        return array(
            'success' => true,
            'message' => 'Đăng ký thành công! Chào mừng bạn đến với ' . get_bloginfo('name'),
            'user_id' => $user_id,
            'redirect' => home_url()
        );
    }

    /**
     * Login user
     * 
     * @param string $username Username or email
     * @param string $password Password
     * @param bool $remember Remember me
     * @return array Response
     */
    public static function login($username, $password, $remember = false)
    {
        if (empty($username) || empty($password)) {
            return array(
                'success' => false,
                'message' => 'Vui lòng nhập tên đăng nhập và mật khẩu'
            );
        }

        $credentials = array(
            'user_login' => $username,
            'user_password' => $password,
            'remember' => $remember
        );

        $user = wp_signon($credentials, is_ssl());

        if (is_wp_error($user)) {
            self::log_login_history(0, 'failed');

            return array(
                'success' => false,
                'message' => 'Tên đăng nhập hoặc mật khẩu không đúng'
            );
        }

        self::log_login_history($user->ID, 'success');

        return array(
            'success' => true,
            'message' => 'Đăng nhập thành công! Chào mừng trở lại, ' . $user->display_name,
            'user_id' => $user->ID,
            'redirect' => home_url()
        );
    }

    /**
     * Logout user
     * 
     * @return array Response
     */
    public static function logout()
    {
        wp_logout();

        return array(
            'success' => true,
            'message' => 'Đăng xuất thành công',
            'redirect' => home_url()
        );
    }

    /**
     * Forgot Password (Step 1: Send OTP)
     * 
     * @param string $email Email
     * @return array Response
     */
    public static function forgot_password_send_otp($email)
    {
        if (empty($email) || !is_email($email)) {
            return array(
                'success' => false,
                'message' => 'Vui lòng nhập email hợp lệ'
            );
        }

        $user = get_user_by('email', $email);
        if (!$user) {
            return array(
                'success' => false,
                'message' => 'Email không tồn tại trong hệ thống'
            );
        }

        require_once get_template_directory() . '/inc/class-truyenqq-otp-manager.php';
        $otp_result = TruyenQQ_OTP_Manager::send_otp($email, 'reset_password');

        return $otp_result;
    }

    /**
     * Forgot Password (Step 2: Verify OTP)
     * 
     * @param string $email Email
     * @param string $otp_code OTP code
     * @return array Response with reset token
     */
    public static function forgot_password_verify_otp($email, $otp_code)
    {
        require_once get_template_directory() . '/inc/class-truyenqq-otp-manager.php';
        $verify_result = TruyenQQ_OTP_Manager::verify_otp($email, $otp_code, 'reset_password');

        if (!$verify_result['success']) {
            return $verify_result;
        }

        $reset_token = wp_generate_password(32, false);
        set_transient('truyenqq_reset_' . $reset_token, $email, 600);

        return array(
            'success' => true,
            'message' => 'Xác thực thành công. Vui lòng đặt mật khẩu mới.',
            'reset_token' => $reset_token
        );
    }

    /**
     * Reset Password (Step 3: Set new password)
     * 
     * @param string $reset_token Reset token
     * @param string $new_password New password
     * @return array Response
     */
    public static function reset_password($reset_token, $new_password)
    {
        if (empty($reset_token) || empty($new_password)) {
            return array(
                'success' => false,
                'message' => 'Vui lòng nhập đầy đủ thông tin'
            );
        }

        $email = get_transient('truyenqq_reset_' . $reset_token);
        if (!$email) {
            return array(
                'success' => false,
                'message' => 'Token không hợp lệ hoặc đã hết hạn'
            );
        }

        if (strlen($new_password) < 6) {
            return array(
                'success' => false,
                'message' => 'Mật khẩu phải có ít nhất 6 ký tự'
            );
        }

        $user = get_user_by('email', $email);
        if (!$user) {
            return array(
                'success' => false,
                'message' => 'Người dùng không tồn tại'
            );
        }

        wp_set_password($new_password, $user->ID);

        delete_transient('truyenqq_reset_' . $reset_token);

        wp_set_auth_cookie($user->ID, true);

        return array(
            'success' => true,
            'message' => 'Đặt lại mật khẩu thành công! Bạn đã được đăng nhập.',
            'redirect' => home_url()
        );
    }

    /* ========================================================================
       OAUTH METHODS - GOOGLE & FACEBOOK LOGIN
       ======================================================================== */

    /**
     * Create or login user from OAuth data - FIXED VERSION
     * 
     * @param array $oauth_data User data from OAuth provider
     * @param string $provider 'google' or 'facebook'
     * @return WP_User|WP_Error User object or error
     */
    public static function oauth_create_or_login_user($oauth_data, $provider)
    {
        $email = isset($oauth_data['email']) ? sanitize_email($oauth_data['email']) : '';


        if (empty($email)) {

            $email = $provider . '_' . $oauth_data['id'] . '@truyenqq.local';
        }


        $user = get_user_by('email', $email);

        if ($user) {

            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID, true);


            if (!get_user_meta($user->ID, 'oauth_provider', true)) {
                update_user_meta($user->ID, 'oauth_provider', $provider);
                update_user_meta($user->ID, 'oauth_id', $oauth_data['id']);
            }


            self::update_oauth_avatar($user->ID, $oauth_data, $provider);


            update_user_meta($user->ID, 'last_oauth_login', current_time('mysql'));


            self::log_login_history($user->ID, 'success', $provider . '_oauth');

            do_action('wp_login', $user->user_login, $user);

            return $user;
        }





        $name = isset($oauth_data['name']) ? sanitize_text_field($oauth_data['name']) : '';


        if (!empty($name)) {
            $username = sanitize_user(strtolower(str_replace(' ', '_', $name)));
        } else {
            $username = $provider . '_user_' . $oauth_data['id'];
        }


        $base_username = $username;
        $counter = 1;
        while (username_exists($username)) {
            $username = $base_username . $counter;
            $counter++;
        }


        $user_id = wp_create_user($username, wp_generate_password(20, true, true), $email);

        if (is_wp_error($user_id)) {
            return $user_id;
        }


        wp_update_user(array(
            'ID' => $user_id,
            'display_name' => $name,
            'first_name' => $name,
        ));


        $user = new WP_User($user_id);
        $user->set_role('subscriber');


        update_user_meta($user_id, 'oauth_provider', $provider);
        update_user_meta($user_id, 'oauth_id', $oauth_data['id']);
        update_user_meta($user_id, 'oauth_created_at', current_time('mysql'));


        self::update_oauth_avatar($user_id, $oauth_data, $provider);


        global $wpdb;
        $user_meta_table = $wpdb->prefix . 'nettruyen_user_meta';


        if ($wpdb->get_var("SHOW TABLES LIKE '$user_meta_table'") == $user_meta_table) {
            $wpdb->insert(
                $user_meta_table,
                array(
                    'user_id' => $user_id,
                    'email_verified' => 1,
                    'email_verified_at' => current_time('mysql'),
                    'created_at' => current_time('mysql')
                ),
                array('%d', '%d', '%s', '%s')
            );
        }


        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);


        self::log_login_history($user_id, 'success', $provider . '_oauth_register');

        do_action('wp_login', $user->user_login, $user);

        return $user;
    }

    /**
     * Update OAuth avatar - HELPER METHOD (NEW)
     * 
     * @param int $user_id User ID
     * @param array $oauth_data OAuth user data
     * @param string $provider 'google' or 'facebook'
     */
    private static function update_oauth_avatar($user_id, $oauth_data, $provider)
    {
        $avatar_url = '';

        if ($provider === 'google') {

            if (isset($oauth_data['picture']) && !empty($oauth_data['picture'])) {
                $avatar_url = $oauth_data['picture'];
            }
        } elseif ($provider === 'facebook') {

            if (isset($oauth_data['picture']['data']['url']) && !empty($oauth_data['picture']['data']['url'])) {
                $avatar_url = $oauth_data['picture']['data']['url'];
            }
        }


        if (!empty($avatar_url) && filter_var($avatar_url, FILTER_VALIDATE_URL)) {
            update_user_meta($user_id, 'oauth_avatar', esc_url_raw($avatar_url));
        }
    }

    /**
     * Get user's OAuth avatar (if exists)
     * 
     * @param int $user_id User ID
     * @return string|false Avatar URL or false
     */
    public static function get_oauth_avatar($user_id)
    {
        $avatar = get_user_meta($user_id, 'oauth_avatar', true);

        if (!empty($avatar)) {
            return esc_url($avatar);
        }

        return false;
    }

    /**
     * Check if user is OAuth user
     * 
     * @param int $user_id User ID
     * @return bool|string False or provider name ('google', 'facebook')
     */
    public static function is_oauth_user($user_id)
    {
        $provider = get_user_meta($user_id, 'oauth_provider', true);

        if (!empty($provider)) {
            return $provider;
        }

        return false;
    }

    /**
     * Unlink OAuth account
     * 
     * @param int $user_id User ID
     * @return array Response
     */
    public static function unlink_oauth_account($user_id)
    {
        $provider = self::is_oauth_user($user_id);

        if (!$provider) {
            return array(
                'success' => false,
                'message' => 'Tài khoản không liên kết với OAuth'
            );
        }


        $user = get_user_by('id', $user_id);
        if (empty($user->user_pass)) {
            return array(
                'success' => false,
                'message' => 'Vui lòng đặt mật khẩu trước khi hủy liên kết OAuth'
            );
        }


        delete_user_meta($user_id, 'oauth_provider');
        delete_user_meta($user_id, 'oauth_id');
        delete_user_meta($user_id, 'oauth_avatar');
        delete_user_meta($user_id, 'last_oauth_login');
        delete_user_meta($user_id, 'oauth_created_at');

        return array(
            'success' => true,
            'message' => 'Đã hủy liên kết tài khoản ' . ucfirst($provider)
        );
    }

    /* ========================================================================
       END OAUTH METHODS
       ======================================================================== */

    /**
     * Log login history
     * 
     * @param int $user_id User ID (0 for failed attempts)
     * @param string $status 'success' or 'failed'
     * @param string $login_method Method used (default: 'password', or 'google_oauth', 'facebook_oauth')
     */
    private static function log_login_history($user_id, $status, $login_method = 'password')
    {
        global $wpdb;

        $login_history_table = $wpdb->prefix . 'nettruyen_login_history';


        if ($wpdb->get_var("SHOW TABLES LIKE '$login_history_table'") != $login_history_table) {
            return;
        }

        $wpdb->insert(
            $login_history_table,
            array(
                'user_id' => $user_id,
                'login_time' => current_time('mysql'),
                'ip_address' => self::get_user_ip(),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '',
                'login_status' => $status,
                'login_method' => $login_method
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Get user IP address
     * 
     * @return string IP address
     */
    private static function get_user_ip()
    {
        $ip_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );

        foreach ($ip_keys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    public static function is_logged_in()
    {
        return is_user_logged_in();
    }

    /**
     * Get current user info (with OAuth avatar support)
     * 
     * @return array|null User info or null
     */
    public static function get_current_user()
    {
        if (!self::is_logged_in()) {
            return null;
        }

        $user = wp_get_current_user();


        $avatar = self::get_oauth_avatar($user->ID);
        if (!$avatar) {
            $avatar = get_avatar_url($user->ID);
        }


        $oauth_provider = self::is_oauth_user($user->ID);

        return array(
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'avatar' => $avatar,
            'is_oauth' => $oauth_provider ? true : false,
            'oauth_provider' => $oauth_provider ? $oauth_provider : null
        );
    }
}