<?php
/**
 * Template Name: Register Page - 404 Style (Final)
 * 
 * @package TruyenQQ
 * @version 4.2.0
 */

if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

get_header('auth');
?>

<div class="auth-page-404">
    <!-- Video Background -->
    <video class="auth-video-bg" autoplay muted loop playsinline>
        <source src="<?php echo get_template_directory_uri(); ?>/images/login.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <!-- Video Overlay -->
    <div class="auth-video-overlay"></div>

    <!-- Dark/Light Mode Toggle -->
    <div class="theme-toggle-auth">
        <button id="theme-toggle-btn" class="theme-toggle-button" aria-label="Toggle theme">
            <i class="fa fa-moon-o dark-icon"></i>
            <i class="fa fa-sun-o light-icon"></i>
        </button>
    </div>

    <!-- Back to Home Button -->
    <div class="back-to-home">
        <a href="<?php echo home_url(); ?>" title="Về trang chủ" aria-label="Về trang chủ">
            <i class="fa fa-home"></i>
            <span>Trang Chủ</span>
        </a>
    </div>

    <!-- Form Container - Centered -->
    <div class="auth-container-404 auth-form-only">
        <div class="auth-form-wrapper-center">
            <!-- Step 1: Register Form -->
            <div class="auth-step active" data-step="1">
                <div class="auth-header">
                    <h2>
                        <i class="fa fa-user-plus"></i>
                        Đăng Ký Tài Khoản
                    </h2>
                    <p>Điền thông tin để tạo tài khoản mới</p>
                </div>

                <form id="register-form" class="auth-form">
                    <div class="form-group">
                        <label for="register-username">
                            <i class="fa fa-user"></i> Tên đăng nhập
                        </label>
                        <input type="text" id="register-username" name="username" placeholder="Chữ, số và dấu gạch dưới"
                            required autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="register-email">
                            <i class="fa fa-envelope"></i> Email
                        </label>
                        <input type="email" id="register-email" name="email" placeholder="email@example.com" required
                            autocomplete="email">
                    </div>

                    <div class="form-group">
                        <label for="register-password">
                            <i class="fa fa-lock"></i> Mật khẩu
                        </label>
                        <div class="password-input">
                            <input type="password" id="register-password" name="password" placeholder="Ít nhất 6 ký tự"
                                required autocomplete="new-password">
                            <button type="button" class="toggle-password" aria-label="Toggle password">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar"></div>
                            <span class="strength-text"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="register-confirm-password">
                            <i class="fa fa-lock"></i> Xác nhận mật khẩu
                        </label>
                        <div class="password-input">
                            <input type="password" id="register-confirm-password" name="confirm_password"
                                placeholder="Nhập lại mật khẩu" required autocomplete="new-password">
                            <button type="button" class="toggle-password" aria-label="Toggle password">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="agree_terms" id="agree-terms" required>
                            <span>Tôi đồng ý với <a href="#">Điều khoản sử dụng</a></span>
                        </label>
                    </div>

                    <!-- reCAPTCHA -->
                    <div class="form-group recaptcha-container">
                        <div class="g-recaptcha" data-sitekey="<?php echo get_option('truyenqq_recaptcha_site_key'); ?>"
                            data-size="normal" data-theme="dark"></div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="btn-text">
                            <i class="fa fa-paper-plane"></i> Gửi Mã OTP
                        </span>
                        <span class="btn-loading" style="display:none;">
                            <i class="fa fa-spinner fa-spin"></i> Đang gửi...
                        </span>
                    </button>

                    <div class="form-message"></div>
                </form>
            </div>

            <!-- Step 2: OTP Verification -->
            <div class="auth-step" data-step="2">
                <div class="auth-header">
                    <h2>
                        <i class="fa fa-shield"></i>
                        Xác Thực Email
                    </h2>
                </div>

                <div class="otp-info">
                    <i class="fa fa-envelope-o"></i>
                    <p>Chúng tôi đã gửi mã OTP (6 số) đến email:</p>
                    <strong id="register-email-display"></strong>
                </div>

                <form id="register-verify-form" class="auth-form">
                    <div class="form-group">
                        <label for="register-otp">
                            <i class="fa fa-key"></i> Nhập mã OTP
                        </label>
                        <input type="text" id="register-otp" name="otp_code" placeholder="000000" maxlength="6" required
                            pattern="[0-9]{6}" autocomplete="one-time-code">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="btn-text">
                            <i class="fa fa-check"></i> Xác Thực & Đăng Ký
                        </span>
                        <span class="btn-loading" style="display:none;">
                            <i class="fa fa-spinner fa-spin"></i> Đang xử lý...
                        </span>
                    </button>

                    <div class="form-message"></div>

                    <div class="otp-resend">
                        <p>Không nhận được mã? <a href="#" id="register-resend-otp">Gửi lại</a></p>
                        <p class="otp-timer" style="display:none;">
                            Gửi lại sau <span id="register-timer">60</span>s
                        </p>
                    </div>

                    <button type="button" class="btn btn-outline btn-block" id="back-to-register">
                        <i class="fa fa-arrow-left"></i> Quay lại
                    </button>
                </form>
            </div>

            <div class="auth-footer">
                <p>Đã có tài khoản?
                    <a href="<?php echo home_url('/dang-nhap'); ?>">Đăng nhập</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php get_footer('auth'); ?>