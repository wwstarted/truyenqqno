<?php
/**
 * Template Name: Forgot Password Page
 * 
 * @package TruyenQQ
 * @version 1.0.0
 */


if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

get_header('auth');
?>

<div class="auth-page-container">
    <div class="auth-page-wrapper">
        <div class="auth-page-content">
            <!-- Left Side - Branding -->
            <div class="auth-page-left">
                <div class="auth-branding">
                    <a href="<?php echo home_url(); ?>" class="brand-logo">
                        <img src="https://st.truyenqqno.com/template/frontend/images/logo-icon.png"
                            alt="<?php bloginfo('name'); ?>">
                    </a>
                    <h1>Khôi phục tài khoản</h1>
                    <p>Đặt lại mật khẩu của bạn một cách an toàn</p>

                    <div class="auth-illustration">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/login.jpg" alt="Forgot Password">
                    </div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="auth-page-right">
                <div class="auth-form-container">
                    <!-- Step Indicator -->
                    <!-- <div class="step-indicator">
                        <div class="step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-label">Nhập Email</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-label">Xác thực OTP</div>
                        </div>
                        <div class="step-line"></div>
                        <div class="step" data-step="3">
                            <div class="step-number">3</div>
                            <div class="step-label">Mật khẩu mới</div>
                        </div>
                    </div> -->

                    <!-- Step 1: Enter Email -->
                    <div class="auth-step active" data-step="1">
                        <div class="auth-header">
                            <h2><i class="fa fa-key"></i> Quên Mật Khẩu</h2>
                            <p>Nhập email để nhận mã xác thực</p>
                        </div>

                        <form id="forgot-password-form" class="auth-form">
                            <div class="form-group">
                                <label for="forgot-email">
                                    <i class="fa fa-envelope"></i> Email
                                </label>
                                <input type="email" id="forgot-email" name="email" placeholder="email@example.com"
                                    required>
                                <small class="form-hint">Email đã đăng ký tài khoản</small>
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
                            <p>Chúng tôi đã gửi mã OTP đến email:</p>
                            <strong id="forgot-email-display"></strong>
                        </div>

                        <form id="forgot-verify-form" class="auth-form">
                            <div class="form-group">
                                <label for="forgot-otp">
                                    <i class="fa fa-key"></i> Nhập mã OTP
                                </label>
                                <input type="text" id="forgot-otp" name="otp_code" placeholder="000000" maxlength="6"
                                    required pattern="[0-9]{6}">
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <span class="btn-text">
                                    <i class="fa fa-check"></i> Xác Thực
                                </span>
                                <span class="btn-loading" style="display:none;">
                                    <i class="fa fa-spinner fa-spin"></i> Đang xử lý...
                                </span>
                            </button>

                            <div class="form-message"></div>

                            <div class="otp-resend">
                                <p>Không nhận được mã? <a href="#" id="forgot-resend-otp">Gửi lại</a></p>
                                <p class="otp-timer" style="display:none;">
                                    Gửi lại sau <span id="forgot-timer">60</span>s
                                </p>
                            </div>
                        </form>
                    </div>

                    <!-- Step 3: Reset Password -->
                    <div class="auth-step" data-step="3">
                        <div class="auth-header">
                            <h2><i class="fa fa-lock"></i> Đặt Mật Khẩu Mới</h2>
                            <p>Tạo mật khẩu mới cho tài khoản của bạn</p>
                        </div>

                        <form id="reset-password-form" class="auth-form">
                            <input type="hidden" id="reset-token" name="reset_token">

                            <div class="form-group">
                                <label for="new-password">
                                    <i class="fa fa-lock"></i> Mật khẩu mới
                                </label>
                                <div class="password-input">
                                    <input type="password" id="new-password" name="new_password"
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
                                <label for="confirm-password">
                                    <i class="fa fa-lock"></i> Xác nhận mật khẩu
                                </label>
                                <div class="password-input">
                                    <input type="password" id="confirm-password" name="confirm_password"
                                        placeholder="Nhập lại mật khẩu" required>
                                    <button type="button" class="toggle-password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <span class="btn-text">
                                    <i class="fa fa-check-circle"></i> Đặt Lại Mật Khẩu
                                </span>
                                <span class="btn-loading" style="display:none;">
                                    <i class="fa fa-spinner fa-spin"></i> Đang xử lý...
                                </span>
                            </button>

                            <div class="form-message"></div>
                        </form>
                    </div>

                    <div class="auth-footer">
                        <p>Nhớ mật khẩu?
                            <a href="<?php echo home_url('/dang-nhap'); ?>">Đăng nhập</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer('auth'); ?>