<?php
/**
 * Template Name: Login Page - 404 Style (Final)
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
            <!-- Header -->
            <div class="auth-header">
                <h2>
                    <i class="fa fa-sign-in"></i>
                    Đăng Nhập
                </h2>
                <p>Nhập thông tin tài khoản để tiếp tục</p>
            </div>

            <!-- Login Form -->
            <form id="login-form" class="auth-form">
                <!-- Username/Email -->
                <div class="form-group">
                    <label for="login-username">
                        <i class="fa fa-user"></i>
                        Tên đăng nhập hoặc Email
                    </label>
                    <input type="text" id="login-username" name="username" placeholder="Nhập tên đăng nhập hoặc email"
                        required autocomplete="username">
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="login-password">
                        <i class="fa fa-lock"></i>
                        Mật khẩu
                    </label>
                    <div class="password-input">
                        <input type="password" id="login-password" name="password" placeholder="Nhập mật khẩu" required
                            autocomplete="current-password">
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

                <!-- reCAPTCHA -->
                <div class="form-group recaptcha-container">
                    <div class="g-recaptcha" data-sitekey="<?php echo get_option('truyenqq_recaptcha_site_key'); ?>"
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

            <!-- Social Login -->
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

            <!-- Footer -->
            <div class="auth-footer">
                <p>
                    Chưa có tài khoản?
                    <a href="<?php echo home_url('/dang-ky'); ?>">Đăng ký ngay</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php get_footer('auth'); ?>