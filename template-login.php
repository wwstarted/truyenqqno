<?php
/**
 * Template Name: Login Page
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
                        <img src="https://st.truyenqqno.com/template/frontend/images/logo-icon.png"
                            alt="<?php bloginfo('name'); ?>">
                    </a>
                    <h1>Chào mừng trở lại!</h1>
                    <p>Đăng nhập để tiếp tục đọc truyện yêu thích</p>

                    <div class="auth-illustration">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/login.jpg" alt="Login">
                    </div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="auth-page-right">
                <div class="auth-form-container">
                    <div class="auth-header">
                        <h2><i class="fa fa-sign-in"></i> Đăng Nhập</h2>
                        <p>Nhập thông tin tài khoản của bạn</p>
                    </div>

                    <form id="login-form" class="auth-form">
                        <div class="form-group">
                            <label for="login-username">
                                <i class="fa fa-user"></i> Tên đăng nhập hoặc Email
                            </label>
                            <input type="text" id="login-username" name="username"
                                placeholder="Nhập tên đăng nhập hoặc email" required>
                        </div>

                        <div class="form-group">
                            <label for="login-password">
                                <i class="fa fa-lock"></i> Mật khẩu
                            </label>
                            <div class="password-input">
                                <input type="password" id="login-password" name="password" placeholder="Nhập mật khẩu"
                                    required>
                                <button type="button" class="toggle-password">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group form-group-inline">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remember" id="login-remember">
                                <span>Ghi nhớ đăng nhập</span>
                            </label>
                            <a href="<?php echo home_url('/quen-mat-khau'); ?>" class="link-text">
                                Quên mật khẩu?
                            </a>
                        </div>

                        <!-- reCAPTCHA -->
                        <div class="form-group recaptcha-container">
                            <div class="g-recaptcha"
                                data-sitekey="<?php echo get_option('truyenqq_recaptcha_site_key'); ?>"
                                data-size="normal" data-theme="light"></div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <span class="btn-text">
                                <i class="fa fa-sign-in"></i> Đăng Nhập
                            </span>
                            <span class="btn-loading" style="display:none;">
                                <i class="fa fa-spinner fa-spin"></i> Đang xử lý...
                            </span>
                        </button>

                        <div class="form-message"></div>
                    </form>

                    <div class="auth-footer">
                        <p>Chưa có tài khoản?
                            <a href="<?php echo home_url('/dang-ky'); ?>">Đăng ký ngay</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>