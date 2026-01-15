<?php
/**
 * Template Name: Register Page
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

get_header();
?>

<div class="auth-page-container">
    <div class="auth-page-wrapper">
        <div class="auth-page-content">
            <!-- Left Side - Branding -->
            <div class="auth-page-left">
                <div class="auth-branding">
                    <a href="<?php echo home_url(); ?>" class="brand-logo">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/logo.png"
                            alt="<?php bloginfo('name'); ?>">
                    </a>
                    <h1>Tham gia cộng đồng!</h1>
                    <p>Tạo tài khoản để trải nghiệm đầy đủ tính năng</p>

                    <div class="auth-illustration">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/login.jpg" alt="Register">
                    </div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="auth-page-right">
                <div class="auth-form-container">
                    <!-- Step Indicator -->
                    <div class="step-indicator">
                        <div class="step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-label">Thông tin</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-label">Xác thực OTP</div>
                        </div>
                    </div>

                    <!-- Step 1: Register Form -->
                    <div class="auth-step active" data-step="1">
                        <div class="auth-header">
                            <h2><i class="fa fa-user-plus"></i> Đăng Ký Tài Khoản</h2>
                            <p>Điền thông tin để tạo tài khoản mới</p>
                        </div>

                        <form id="register-form" class="auth-form">
                            <div class="form-group">
                                <label for="register-username">
                                    <i class="fa fa-user"></i> Tên đăng nhập
                                </label>
                                <input type="text" id="register-username" name="username"
                                    placeholder="Chữ, số và dấu gạch dưới" required>
                                <small class="form-hint">Chỉ sử dụng chữ cái, số và dấu gạch dưới (_)</small>
                            </div>

                            <div class="form-group">
                                <label for="register-email">
                                    <i class="fa fa-envelope"></i> Email
                                </label>
                                <input type="email" id="register-email" name="email" placeholder="email@example.com"
                                    required>
                                <small class="form-hint">Chúng tôi sẽ gửi mã OTP đến email này</small>
                            </div>

                            <div class="form-group">
                                <label for="register-password">
                                    <i class="fa fa-lock"></i> Mật khẩu
                                </label>
                                <div class="password-input">
                                    <input type="password" id="register-password" name="password"
                                        placeholder="Ít nhất 6 ký tự" required>
                                    <button type="button" class="toggle-password">
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
                                        placeholder="Nhập lại mật khẩu" required>
                                    <button type="button" class="toggle-password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="agree_terms" id="agree-terms" required>
                                    <span>Tôi đồng ý với <a href="#">Điều khoản sử dụng</a> và <a href="#">Chính sách
                                            bảo mật</a></span>
                                </label>
                            </div>

                            <!-- reCAPTCHA -->
                            <div class="form-group recaptcha-container">
                                <div class="g-recaptcha"
                                    data-sitekey="<?php echo get_option('truyenqq_recaptcha_site_key'); ?>"
                                    data-size="normal" data-theme="light"></div>
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
                            <h2><i class="fa fa-shield"></i> Xác Thực Email</h2>
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
                                <input type="text" id="register-otp" name="otp_code" placeholder="000000" maxlength="6"
                                    required pattern="[0-9]{6}">
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
    </div>
</div>
<?php get_footer(); ?>