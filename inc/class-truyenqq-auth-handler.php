<?php
/**
 * Authentication Handler Class
 * Xử lý Register, Login, Logout, Reset Password
 * 
 * @package TruyenQQ
 * @version 1.0.0
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

    /**
     * Log login history
     * 
     * @param int $user_id User ID (0 for failed attempts)
     * @param string $status 'success' or 'failed'
     */
    private static function log_login_history($user_id, $status)
    {
        global $wpdb;

        $login_history_table = $wpdb->prefix . 'nettruyen_login_history';

        $wpdb->insert(
            $login_history_table,
            array(
                'user_id' => $user_id,
                'login_time' => current_time('mysql'),
                'ip_address' => self::get_user_ip(),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
                'login_status' => $status
            ),
            array('%d', '%s', '%s', '%s', '%s')
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
     * Get current user info
     * 
     * @return array|null User info or null
     */
    public static function get_current_user()
    {
        if (!self::is_logged_in()) {
            return null;
        }

        $user = wp_get_current_user();

        return array(
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'avatar' => get_avatar_url($user->ID)
        );
    }
}