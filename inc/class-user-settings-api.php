<?php
/**
 * User Settings REST API (FIXED + OTP for Password Change)
 * 
 * @package TruyenQQ
 * @version 1.0.3 
 */

if (!defined('ABSPATH')) {
    exit;
}

class TruyenQQ_User_Settings_API
{
    /**
     * Max password change attempts per day
     */
    const MAX_PASSWORD_CHANGES_PER_DAY = 3;

    /**
     * Register REST API routes
     */
    public static function register_routes()
    {

        register_rest_route('nettruyen/v1', '/user/info', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_user_info'),
            'permission_callback' => array(__CLASS__, 'check_user_logged_in')
        ));


        register_rest_route('nettruyen/v1', '/user/update-info', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'update_user_info'),
            'permission_callback' => array(__CLASS__, 'check_user_logged_in')
        ));


        register_rest_route('nettruyen/v1', '/media', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'upload_media'),
            'permission_callback' => array(__CLASS__, 'check_user_logged_in')
        ));


        register_rest_route('nettruyen/v1', '/user/send-password-otp', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'send_password_otp'),
            'permission_callback' => array(__CLASS__, 'check_user_logged_in')
        ));


        register_rest_route('nettruyen/v1', '/user/verify-password-otp', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'verify_password_otp'),
            'permission_callback' => array(__CLASS__, 'check_user_logged_in')
        ));


        register_rest_route('nettruyen/v1', '/user/resend-password-otp', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'resend_password_otp'),
            'permission_callback' => array(__CLASS__, 'check_user_logged_in')
        ));
    }

    /**
     * Permission callback - Check if user is logged in

     */
    public static function check_user_logged_in($request)
    {

        if (!is_user_logged_in()) {
            error_log('TruyenQQ User Settings API: User not logged in');
            return false;
        }


        if ($request->get_method() === 'POST') {
            $nonce = $request->get_header('X-WP-Nonce');
            if (!wp_verify_nonce($nonce, 'wp_rest')) {
                error_log('TruyenQQ User Settings API: Invalid nonce');
                return false;
            }
        }

        return true;
    }

    /**
     * Get current user info
     * 
     * @return WP_REST_Response
     */
    public static function get_user_info()
    {
        $user_id = get_current_user_id();
        $current_user = wp_get_current_user();

        error_log('TruyenQQ: Loading user info for ID: ' . $user_id);


        $avatar_id = get_user_meta($user_id, 'avatar', true);
        $avatar_url = '';

        if ($avatar_id) {
            $avatar_url = wp_get_attachment_url($avatar_id);
            error_log('TruyenQQ: Avatar from upload ID ' . $avatar_id . ': ' . $avatar_url);
        }


        if (!$avatar_url) {
            $oauth_avatar = get_user_meta($user_id, 'oauth_avatar', true);
            if ($oauth_avatar) {
                $avatar_url = $oauth_avatar;
                error_log('TruyenQQ: Using OAuth avatar: ' . $avatar_url);
            }
        }


        if (!$avatar_url) {
            $avatar_url = get_avatar_url($user_id, array('size' => 150));
            error_log('TruyenQQ: Using Gravatar: ' . $avatar_url);
        }


        $first_name = get_user_meta($user_id, 'first_name', true);
        $last_name = get_user_meta($user_id, 'last_name', true);
        $birth_date = get_user_meta($user_id, 'birth_date', true);
        $phone = get_user_meta($user_id, 'phone', true);
        $gender = get_user_meta($user_id, 'gender', true);
        $rank = get_user_meta($user_id, 'rank', true);
        $points = get_user_meta($user_id, 'points', true);
        $level = get_user_meta($user_id, 'level', true);
        $level_progress = get_user_meta($user_id, 'level_progress', true);


        $user_data = array(
            'id' => $user_id,
            'username' => $current_user->user_login,
            'email' => $current_user->user_email,
            'display_name' => $current_user->display_name,
            'avatar' => $avatar_url,
            'avatar_id' => $avatar_id ?: '',


            'first_name' => $first_name !== false ? (string) $first_name : '',
            'last_name' => $last_name !== false ? (string) $last_name : '',
            'birth_date' => $birth_date !== false ? (string) $birth_date : '',
            'phone' => $phone !== false ? (string) $phone : '',
            'gender' => $gender !== false ? (string) $gender : '0',
            'rank' => $rank !== false ? (string) $rank : '0',


            'points' => $points !== false ? intval($points) : 0,
            'level' => $level !== false ? intval($level) : 1,
            'level_progress' => $level_progress !== false ? intval($level_progress) : 0,
        );

        error_log('TruyenQQ: User data prepared: ' . json_encode($user_data));

        return new WP_REST_Response(array(
            'success' => true,
            'user' => $user_data
        ), 200);
    }

    /**
     * Upload media/avatar
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public static function upload_media($request)
    {
        $user_id = get_current_user_id();

        error_log('TruyenQQ: Avatar upload started for user ID: ' . $user_id);


        if (empty($_FILES['file'])) {
            error_log('TruyenQQ: No file uploaded');
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Không có file được tải lên'
            ), 400);
        }

        $file = $_FILES['file'];


        $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');
        if (!in_array($file['type'], $allowed_types)) {
            error_log('TruyenQQ: Invalid file type: ' . $file['type']);
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WebP)'
            ), 400);
        }


        $max_size = 5 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            error_log('TruyenQQ: File too large: ' . $file['size']);
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Kích thước file không được vượt quá 5MB'
            ), 400);
        }


        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log('TruyenQQ: Upload error: ' . $file['error']);
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Lỗi tải file lên. Vui lòng thử lại.'
            ), 400);
        }


        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }


        $upload_overrides = array(
            'test_form' => false,
            'test_size' => true,
            'test_type' => true,
        );

        $uploaded_file = wp_handle_upload($file, $upload_overrides);

        if (isset($uploaded_file['error'])) {
            error_log('TruyenQQ: Upload failed: ' . $uploaded_file['error']);
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $uploaded_file['error']
            ), 400);
        }


        $file_path = $uploaded_file['file'];
        $file_url = $uploaded_file['url'];
        $file_type = $uploaded_file['type'];

        $attachment = array(
            'post_mime_type' => $file_type,
            'post_title' => sanitize_file_name(basename($file_path)),
            'post_content' => '',
            'post_status' => 'inherit',
            'post_author' => $user_id
        );

        $attachment_id = wp_insert_attachment($attachment, $file_path);

        if (is_wp_error($attachment_id)) {
            error_log('TruyenQQ: Failed to create attachment: ' . $attachment_id->get_error_message());
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Không thể tạo attachment'
            ), 500);
        }


        $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attachment_data);


        update_user_meta($user_id, 'avatar', $attachment_id);

        error_log('TruyenQQ: Avatar uploaded successfully. Attachment ID: ' . $attachment_id);

        return new WP_REST_Response(array(
            'success' => true,
            'id' => $attachment_id,
            'url' => $file_url,
            'message' => 'Tải ảnh thành công!'
        ), 200);
    }

    /**
     * Update user info
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public static function update_user_info($request)
    {
        $user_id = get_current_user_id();


        $params = $request->get_json_params();

        error_log('TruyenQQ: Updating user info for ID: ' . $user_id);
        error_log('TruyenQQ: Update params: ' . json_encode($params));


        $last_name = isset($params['last_name']) ? sanitize_text_field($params['last_name']) : '';
        $first_name = isset($params['first_name']) ? sanitize_text_field($params['first_name']) : '';
        $birth_date = isset($params['birth_date']) ? sanitize_text_field($params['birth_date']) : '';
        $phone = isset($params['phone']) ? sanitize_text_field($params['phone']) : '';
        $gender = isset($params['gender']) ? sanitize_text_field($params['gender']) : '0';
        $rank = isset($params['rank']) ? sanitize_text_field($params['rank']) : '0';
        $avatar = isset($params['avatar']) ? intval($params['avatar']) : 0;


        if (!empty($birth_date) && !self::validate_date($birth_date)) {
            error_log('TruyenQQ: Invalid birth date format: ' . $birth_date);
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Ngày sinh không đúng định dạng (dd/mm/yyyy)'
            ), 400);
        }


        if (!empty($phone) && !preg_match('/^[0-9+\-\s()]+$/', $phone)) {
            error_log('TruyenQQ: Invalid phone format: ' . $phone);
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Số điện thoại không hợp lệ'
            ), 400);
        }


        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $last_name);
        update_user_meta($user_id, 'birth_date', $birth_date);
        update_user_meta($user_id, 'phone', $phone);
        update_user_meta($user_id, 'gender', $gender);
        update_user_meta($user_id, 'rank', $rank);


        if ($avatar > 0) {

            if (wp_attachment_is_image($avatar)) {
                update_user_meta($user_id, 'avatar', $avatar);
                error_log('TruyenQQ: Avatar updated to ID: ' . $avatar);
            } else {
                error_log('TruyenQQ: Invalid avatar ID: ' . $avatar);
            }
        }


        if (!empty($first_name) || !empty($last_name)) {
            $display_name = trim($last_name . ' ' . $first_name);
            wp_update_user(array(
                'ID' => $user_id,
                'display_name' => $display_name
            ));
            error_log('TruyenQQ: Display name updated to: ' . $display_name);
        }

        error_log('TruyenQQ: User info updated successfully');

        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Cập nhật thông tin thành công!'
        ), 200);
    }

    /**
     * Send OTP for password change (Step 1)
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public static function send_password_otp($request)
    {
        $user_id = get_current_user_id();
        $current_user = get_userdata($user_id);


        $oauth_provider = get_user_meta($user_id, 'oauth_provider', true);
        if ($oauth_provider) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Tài khoản ' . ucfirst($oauth_provider) . ' không thể đổi mật khẩu'
            ), 403);
        }


        if (!self::check_password_change_limit($user_id)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Bạn đã đổi mật khẩu quá 3 lần trong ngày. Vui lòng thử lại vào ngày mai.'
            ), 429);
        }


        $params = $request->get_json_params();

        $current_password = isset($params['current_password']) ? $params['current_password'] : '';
        $new_password = isset($params['new_password']) ? $params['new_password'] : '';


        if (empty($current_password) || empty($new_password)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Vui lòng nhập đầy đủ thông tin'
            ), 400);
        }


        if (strlen($new_password) < 6) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự'
            ), 400);
        }


        if (!wp_check_password($current_password, $current_user->user_pass, $user_id)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Mật khẩu hiện tại không đúng'
            ), 400);
        }


        if (wp_check_password($new_password, $current_user->user_pass, $user_id)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Mật khẩu mới không được trùng với mật khẩu hiện tại'
            ), 400);
        }


        set_transient('password_change_' . $user_id, array(
            'new_password' => $new_password,
            'timestamp' => time()
        ), 1800);


        require_once get_template_directory() . '/inc/class-truyenqq-otp-manager.php';
        $otp_result = TruyenQQ_OTP_Manager::send_otp($current_user->user_email, 'change_password');

        if ($otp_result['success']) {
            return new WP_REST_Response(array(
                'success' => true,
                'message' => 'Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.',
                'email' => $current_user->user_email
            ), 200);
        } else {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $otp_result['message']
            ), 400);
        }
    }

    /**
     * Verify OTP and change password (Step 2)
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public static function verify_password_otp($request)
    {
        $user_id = get_current_user_id();
        $current_user = get_userdata($user_id);


        $params = $request->get_json_params();
        $otp_code = isset($params['otp_code']) ? sanitize_text_field($params['otp_code']) : '';

        if (empty($otp_code)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Vui lòng nhập mã OTP'
            ), 400);
        }


        require_once get_template_directory() . '/inc/class-truyenqq-otp-manager.php';
        $verify_result = TruyenQQ_OTP_Manager::verify_otp($current_user->user_email, $otp_code, 'change_password');

        if (!$verify_result['success']) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $verify_result['message']
            ), 400);
        }


        $password_data = get_transient('password_change_' . $user_id);

        if (!$password_data || !isset($password_data['new_password'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Phiên đổi mật khẩu đã hết hạn. Vui lòng thử lại.'
            ), 400);
        }


        wp_set_password($password_data['new_password'], $user_id);


        delete_transient('password_change_' . $user_id);


        self::log_password_change($user_id);


        self::increment_password_change_count($user_id);

        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Đổi mật khẩu thành công!'
        ), 200);
    }

    /**
     * Resend OTP for password change
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public static function resend_password_otp($request)
    {
        $user_id = get_current_user_id();
        $current_user = get_userdata($user_id);


        $password_data = get_transient('password_change_' . $user_id);

        if (!$password_data) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Không tìm thấy phiên đổi mật khẩu. Vui lòng bắt đầu lại.'
            ), 400);
        }


        require_once get_template_directory() . '/inc/class-truyenqq-otp-manager.php';
        $otp_result = TruyenQQ_OTP_Manager::send_otp($current_user->user_email, 'change_password');

        if ($otp_result['success']) {
            return new WP_REST_Response(array(
                'success' => true,
                'message' => 'Mã OTP mới đã được gửi!'
            ), 200);
        } else {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $otp_result['message']
            ), 400);
        }
    }

    /**
     * Check password change rate limit (max 3 per day)
     * 
     * @param int $user_id User ID
     * @return bool True if allowed, False if exceeded
     */
    private static function check_password_change_limit($user_id)
    {
        $today = date('Y-m-d');
        $count_key = 'password_change_count_' . $today;

        $count = get_user_meta($user_id, $count_key, true);

        return ($count < self::MAX_PASSWORD_CHANGES_PER_DAY);
    }

    /**
     * Increment password change counter
     * 
     * @param int $user_id User ID
     */
    private static function increment_password_change_count($user_id)
    {
        $today = date('Y-m-d');
        $count_key = 'password_change_count_' . $today;

        $count = intval(get_user_meta($user_id, $count_key, true));
        update_user_meta($user_id, $count_key, $count + 1);
    }

    /**
     * Validate date format (dd/mm/yyyy)
     * 
     * @param string $date Date string
     * @return bool
     */
    private static function validate_date($date)
    {
        if (!preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            return false;
        }

        $day = intval($matches[1]);
        $month = intval($matches[2]);
        $year = intval($matches[3]);

        if ($month < 1 || $month > 12)
            return false;
        if ($day < 1 || $day > 31)
            return false;
        if ($year < 1900 || $year > date('Y'))
            return false;

        return checkdate($month, $day, $year);
    }

    /**
     * Log password change to database
     * 
     * @param int $user_id User ID
     */
    private static function log_password_change($user_id)
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
                'login_status' => 'password_changed',
                'login_method' => 'password_change_with_otp'
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
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return '0.0.0.0';
    }
}


add_action('rest_api_init', array('TruyenQQ_User_Settings_API', 'register_routes'));