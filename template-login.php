<?php
/**
 * Template Name: Modern Login Page - Blue Dark Theme
 * 
 * @package TruyenQQ
 * @version 3.1.0 - FIXED LAYOUT
 */

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

get_header('auth'); // Load header-auth.php
?>

<div class="auth-page-container">

    <!-- Back to Home Button - Fixed Bottom Right -->
    <div class="back-to-home">
        <a href="<?php echo home_url(); ?>" title="Về trang chủ" aria-label="Về trang chủ">
            <i class="fa fa-home"></i>
        </a>
    </div>

    <div class="auth-page-wrapper">
        <div class="auth-page-content">

            <!-- LEFT SIDE - IMAGE ONLY (NO TEXT) -->
            <div class="auth-page-left">
                <div class="auth-branding">
                    <!-- Logo - HIDDEN -->
                    <a href="<?php echo home_url(); ?>" class="brand-logo" style="display: none;">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/logo.png"
                            alt="<?php bloginfo('name'); ?>">
                    </a>

                    <!-- Heading - HIDDEN -->
                    <h1 style="display: none;">Chào mừng trở lại!</h1>
                    <p style="display: none;">Đăng nhập để tiếp tục hành trình khám phá thế giới truyện tranh đầy màu
                        sắc</p>

                    <!-- Illustration - ONLY VISIBLE ELEMENT -->
                    <div class="auth-illustration">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/login.jpg" alt="Login Illustration"
                            loading="lazy">
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDE - LOGIN FORM (NO SCROLL) -->
            <div class="auth-page-right">
                <div class="auth-form-container">

                    <!-- Header - ICON BÊN TRÁI -->
                    <div class="auth-header">
                        <h2>
                            <i class="fa fa-sign-in"></i>
                            Đăng Nhập
                        </h2>
                        <p>Nhập thông tin tài khoản để tiếp tục</p>
                    </div>

                    <!-- Login Form - COMPACT SPACING -->
                    <form id="login-form" class="auth-form">

                        <!-- Username/Email -->
                        <div class="form-group">
                            <label for="login-username">
                                <i class="fa fa-user"></i>
                                Tên đăng nhập hoặc Email
                            </label>
                            <input type="text" id="login-username" name="username"
                                placeholder="Nhập tên đăng nhập hoặc email" required autocomplete="username">
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label for="login-password">
                                <i class="fa fa-lock"></i>
                                Mật khẩu
                            </label>
                            <div class="password-input">
                                <input type="password" id="login-password" name="password" placeholder="Nhập mật khẩu"
                                    required autocomplete="current-password">
                                <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember & Forgot -->
                        <div class="form-group form-group-inline">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remember" id="login-remember">
                                <span>Ghi nhớ đăng nhập</span>
                            </label>
                            <a href="<?php echo home_url('/quen-mat-khau'); ?>" class="link-text">
                                Quên mật khẩu?
                            </a>
                        </div>

                        <!-- reCAPTCHA - DARK THEME -->
                        <div class="form-group recaptcha-container">
                            <div class="g-recaptcha"
                                data-sitekey="<?php echo get_option('truyenqq_recaptcha_site_key'); ?>"
                                data-size="normal" data-theme="dark"></div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary btn-block">
                            <span class="btn-text">
                                <i class="fa fa-sign-in"></i>
                                Đăng Nhập
                            </span>
                            <span class="btn-loading" style="display:none;">
                                <i class="fa fa-spinner fa-spin"></i>
                                Đang xử lý...
                            </span>
                        </button>

                        <!-- Message Display -->
                        <div class="form-message"></div>
                    </form>

                    <!-- Divider -->
                    <div class="auth-divider">
                        <span>hoặc</span>
                    </div>

                    <!-- Social Login Buttons -->
                    <div class="social-login">
                        <button type="button" class="btn-social btn-google" id="login-google">
                            <i class="fab fa-google"></i>
                            Google
                        </button>
                        <button type="button" class="btn-social btn-facebook" id="login-facebook">
                            <i class="fab fa-facebook-f"></i>
                            Facebook
                        </button>
                    </div>

                    <!-- Footer - Register Link -->
                    <div class="auth-footer">
                        <p>
                            Chưa có tài khoản?
                            <a href="<?php echo home_url('/dang-ky'); ?>">Đăng ký ngay</a>
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?php get_footer('auth'); // Load footer-auth.php ?>