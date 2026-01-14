<?php
/**
 * OTP Manager Class - FIXED TIMEZONE ISSUE
 * Xử lý generate, send, verify OTP codes
 * 
 * @package TruyenQQ
 * @version 1.0.2 - FIXED TIMEZONE
 */

if (!defined('ABSPATH')) {
    exit;
}

class TruyenQQ_OTP_Manager
{
    /**
     * OTP expiration time (phút)
     */
    const OTP_EXPIRY_MINUTES = 30;

    /**
     * Maximum OTP requests per hour
     */
    const MAX_OTP_PER_HOUR = 5;

    /**
     * Generate OTP code (6 digits)
     * 
     * @return string 6-digit OTP
     */
    public static function generate_otp()
    {
        return sprintf('%06d', mt_rand(0, 999999));
    }

    /**
     * Send OTP via email
     * 
     * @param string $email Email address
     * @param string $purpose 'register' hoặc 'reset_password'
     * @return array ['success' => bool, 'message' => string, 'otp_id' => int]
     */
    public static function send_otp($email, $purpose = 'register')
    {
        global $wpdb;

        // Validate email
        if (!is_email($email)) {
            error_log('OTP SEND ERROR: Invalid email - ' . $email);
            return array(
                'success' => false,
                'message' => 'Email không hợp lệ'
            );
        }

        // Check rate limiting
        if (!self::check_rate_limit($email)) {
            error_log('OTP SEND ERROR: Rate limit exceeded for ' . $email);
            return array(
                'success' => false,
                'message' => 'Bạn đã gửi quá nhiều OTP. Vui lòng thử lại sau 1 giờ.'
            );
        }

        // Generate OTP
        $otp_code = self::generate_otp();

        // FIX: Dùng gmdate thay vì date để lưu theo UTC
        $expires_at = gmdate('Y-m-d H:i:s', strtotime('+' . self::OTP_EXPIRY_MINUTES . ' minutes'));
        $created_at = gmdate('Y-m-d H:i:s');
        $user_ip = self::get_user_ip();

        error_log('=== OTP SEND DEBUG ===');
        error_log('Email: ' . $email);
        error_log('OTP Code: ' . $otp_code);
        error_log('Purpose: ' . $purpose);
        error_log('Expires At (UTC): ' . $expires_at);
        error_log('Created At (UTC): ' . $created_at);

        // Save to database
        $otp_table = $wpdb->prefix . 'nettruyen_otp_codes';
        $inserted = $wpdb->insert(
            $otp_table,
            array(
                'email' => $email,
                'otp_code' => $otp_code,
                'purpose' => $purpose,
                'expires_at' => $expires_at,
                'created_at' => $created_at,
                'ip_address' => $user_ip
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );

        if (!$inserted) {
            error_log('OTP SEND ERROR: Database insert failed - ' . $wpdb->last_error);
            return array(
                'success' => false,
                'message' => 'Lỗi database. Vui lòng thử lại.'
            );
        }

        $otp_id = $wpdb->insert_id;
        error_log('OTP saved to database with ID: ' . $otp_id);

        // Send email
        $email_sent = self::send_otp_email($email, $otp_code, $purpose);

        if (!$email_sent) {
            error_log('OTP SEND ERROR: Email sending failed');
            return array(
                'success' => false,
                'message' => 'Không thể gửi email. Vui lòng kiểm tra lại địa chỉ email.'
            );
        }

        error_log('OTP email sent successfully');

        return array(
            'success' => true,
            'message' => 'Mã OTP đã được gửi đến email của bạn.',
            'otp_id' => $otp_id,
            'expires_in_minutes' => self::OTP_EXPIRY_MINUTES
        );
    }

    /**
     * Verify OTP code - FIXED TIMEZONE VERSION
     * 
     * @param string $email Email address
     * @param string $otp_code OTP code to verify
     * @param string $purpose Purpose of OTP
     * @return array ['success' => bool, 'message' => string]
     */
    public static function verify_otp($email, $otp_code, $purpose = 'register')
    {
        global $wpdb;

        error_log('=== OTP VERIFY DEBUG ===');
        error_log('Email: ' . $email);
        error_log('OTP Code: ' . $otp_code);
        error_log('Purpose: ' . $purpose);
        error_log('Current Time (UTC): ' . gmdate('Y-m-d H:i:s'));

        if (empty($email) || empty($otp_code)) {
            error_log('VERIFY ERROR: Empty email or OTP code');
            return array(
                'success' => false,
                'message' => 'Vui lòng nhập đầy đủ thông tin'
            );
        }

        $otp_table = $wpdb->prefix . 'nettruyen_otp_codes';

        // DEBUG: Kiểm tra tất cả OTP của email này
        $all_otps = $wpdb->get_results($wpdb->prepare(
            "SELECT id, email, otp_code, purpose, expires_at, verified_at, created_at 
            FROM {$otp_table} 
            WHERE email = %s 
            ORDER BY created_at DESC 
            LIMIT 5",
            $email
        ));
        error_log('All OTPs for this email:');
        error_log(print_r($all_otps, true));

        // FIX: Dùng UTC_TIMESTAMP() thay vì NOW()
        $sql = $wpdb->prepare(
            "SELECT * FROM {$otp_table} 
            WHERE email = %s 
            AND otp_code = %s 
            AND purpose = %s 
            AND expires_at > UTC_TIMESTAMP()
            AND verified_at IS NULL
            ORDER BY created_at DESC
            LIMIT 1",
            $email,
            $otp_code,
            $purpose
        );

        error_log('SQL Query: ' . $sql);

        $otp = $wpdb->get_row($sql);

        error_log('Query Result:');
        error_log(print_r($otp, true));

        if ($wpdb->last_error) {
            error_log('SQL Error: ' . $wpdb->last_error);
        }

        if (!$otp) {
            error_log('VERIFY ERROR: No valid OTP found');

            // Kiểm tra các trường hợp cụ thể
            $check_otp_code = $wpdb->get_row($wpdb->prepare(
                "SELECT *, 
                UTC_TIMESTAMP() as current_utc,
                CASE 
                    WHEN expires_at <= UTC_TIMESTAMP() THEN 'EXPIRED'
                    WHEN verified_at IS NOT NULL THEN 'ALREADY_USED'
                    WHEN purpose != %s THEN 'WRONG_PURPOSE'
                    ELSE 'UNKNOWN'
                END as error_reason
                FROM {$otp_table} 
                WHERE email = %s 
                AND otp_code = %s 
                ORDER BY created_at DESC 
                LIMIT 1",
                $purpose,
                $email,
                $otp_code
            ));

            if (!$check_otp_code) {
                error_log('Reason: OTP code not found in database');
            } else {
                error_log('OTP found but invalid. Reason: ' . $check_otp_code->error_reason);
                error_log('Current UTC: ' . $check_otp_code->current_utc);
                error_log('Expires At: ' . $check_otp_code->expires_at);
                error_log('Purpose: ' . $check_otp_code->purpose . ' (Expected: ' . $purpose . ')');
                error_log('Verified At: ' . ($check_otp_code->verified_at ?? 'NULL'));
            }

            return array(
                'success' => false,
                'message' => 'Mã OTP không đúng hoặc đã hết hạn'
            );
        }

        // Mark as verified - FIX: Dùng gmdate
        $updated = $wpdb->update(
            $otp_table,
            array('verified_at' => gmdate('Y-m-d H:i:s')),
            array('id' => $otp->id),
            array('%s'),
            array('%d')
        );

        if ($updated === false) {
            error_log('VERIFY ERROR: Failed to update verified_at - ' . $wpdb->last_error);
            return array(
                'success' => false,
                'message' => 'Lỗi cập nhật database'
            );
        }

        error_log('OTP verified successfully! ID: ' . $otp->id);

        return array(
            'success' => true,
            'message' => 'Xác thực thành công',
            'otp_id' => $otp->id
        );
    }

    /**
     * Check rate limiting (max 5 OTP/hour)
     * 
     * @param string $email Email address
     * @return bool True if allowed, False if exceeded
     */
    private static function check_rate_limit($email)
    {
        global $wpdb;

        $otp_table = $wpdb->prefix . 'nettruyen_otp_codes';

        // FIX: Dùng UTC_TIMESTAMP()
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$otp_table} 
            WHERE email = %s 
            AND created_at > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 HOUR)",
            $email
        ));

        error_log('Rate limit check for ' . $email . ': ' . $count . '/' . self::MAX_OTP_PER_HOUR);

        return ($count < self::MAX_OTP_PER_HOUR);
    }

    /**
     * Send OTP email
     * 
     * @param string $email Recipient email
     * @param string $otp_code OTP code
     * @param string $purpose Purpose
     * @return bool True if sent successfully
     */
    private static function send_otp_email($email, $otp_code, $purpose)
    {
        $site_name = get_bloginfo('name');

        // Subject based on purpose
        if ($purpose === 'register') {
            $subject = "[{$site_name}] Mã xác thực đăng ký tài khoản";
            $action = "đăng ký tài khoản";
        } else {
            $subject = "[{$site_name}] Mã xác thực đặt lại mật khẩu";
            $action = "đặt lại mật khẩu";
        }

        // Email body
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%); color: #fff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .otp-box { background: #fff; border: 2px dashed #2196f3; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
                .otp-code { font-size: 32px; font-weight: bold; color: #2196f3; letter-spacing: 5px; }
                .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$site_name}</h1>
                </div>
                <div class='content'>
                    <h2>Xin chào!</h2>
                    <p>Bạn đã yêu cầu {$action}. Vui lòng sử dụng mã OTP dưới đây để xác thực:</p>
                    
                    <div class='otp-box'>
                        <div class='otp-code'>{$otp_code}</div>
                        <p style='margin: 10px 0 0 0; color: #666;'>Mã này có hiệu lực trong " . self::OTP_EXPIRY_MINUTES . " phút</p>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Lưu ý:</strong> Không chia sẻ mã này với bất kỳ ai. Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này.
                    </div>
                    
                    <p>Trân trọng,<br><strong>{$site_name}</strong></p>
                </div>
                <div class='footer'>
                    <p>Email này được gửi tự động. Vui lòng không trả lời.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        // Headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <noreply@' . parse_url(home_url(), PHP_URL_HOST) . '>'
        );

        // Send email
        $sent = wp_mail($email, $subject, $message, $headers);

        error_log('Email send result: ' . ($sent ? 'SUCCESS' : 'FAILED'));

        return $sent;
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

    /**
     * Delete old OTP codes for an email
     * 
     * @param string $email Email address
     * @param string $purpose Purpose
     */
    public static function delete_old_otps($email, $purpose)
    {
        global $wpdb;
        $otp_table = $wpdb->prefix . 'nettruyen_otp_codes';

        $deleted = $wpdb->delete(
            $otp_table,
            array(
                'email' => $email,
                'purpose' => $purpose
            ),
            array('%s', '%s')
        );

        error_log('Deleted ' . $deleted . ' old OTP codes for ' . $email);
    }
}