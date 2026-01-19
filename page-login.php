<?php
/**
 * Template Name: Modern Login Page
 * 
 * @package TruyenQQ
 * @version 2.1.0
 */

// Redirect if already logged in
if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

get_header('auth'); // Load header-auth.php
?>

<div class="modern-auth-container">
    <!-- Background Elements -->
    <div class="auth-background">
        <div class="bg-image"></div>
        <div class="bg-overlay"></div>
        <div class="stars-container">
            <div class="stars stars-small"></div>
            <div class="stars stars-medium"></div>
            <div class="stars stars-large"></div>
        </div>
    </div>

    <!-- Dark Mode Toggle -->
    <button class="theme-toggle" id="theme-toggle" aria-label="Toggle Dark Mode">
        <i class="fa fa-moon"></i>
    </button>

    <!-- Main Content -->
    <div class="auth-content">
        <!-- Login Form Card -->
        <div class="auth-card glass-card">
            <!-- Logo Inside Card - Link to Home -->
            <div class="auth-brand-inside">
                <a href="<?php echo home_url(); ?>" class="brand-link" title="Về trang chủ <?php bloginfo('name'); ?>">
                    <img src="https://st.truyenqqno.com/template/frontend/images/logo-icon.png"
                        alt="<?php bloginfo('name'); ?>" class="brand-logo">
                </a>
            </div>

            <!-- Header - Simple -->
            <div class="auth-card-header">
                <h1 class="auth-title">Đăng Nhập</h1>
            </div>

            <!-- Login Form -->
            <form id="login-form" class="auth-form">
                <!-- Username Field -->
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fa fa-user input-icon"></i>
                        <input type="text" id="login-username" name="username" class="form-input"
                            placeholder="Tên đăng nhập hoặc Email" autocomplete="username" required>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <div class="input-wrapper">
                        <i class="fa fa-lock input-icon"></i>
                        <input type="password" id="login-password" name="password" class="form-input"
                            placeholder="Mật khẩu" autocomplete="current-password" required>
                        <button type="button" class="toggle-password" aria-label="Toggle Password Visibility">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember & Forgot -->
                <div class="form-options">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="remember" id="login-remember">
                        <span class="checkbox-label">Ghi nhớ</span>
                    </label>
                    <a href="<?php echo home_url('/quen-mat-khau'); ?>" class="forgot-link">
                        Quên mật khẩu?
                    </a>
                </div>

                <!-- reCAPTCHA -->
                <div class="recaptcha-wrapper">
                    <div class="g-recaptcha" data-sitekey="<?php echo get_option('truyenqq_recaptcha_site_key'); ?>"
                        data-theme="light"></div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    <span class="btn-text">
                        <i class="fa fa-sign-in"></i>
                        Đăng Nhập
                    </span>
                    <span class="btn-loading" style="display:none;">
                        <i class="fa fa-spinner fa-spin"></i>
                        Đang xử lý...
                    </span>
                </button>

                <!-- Message -->
                <div class="form-message"></div>
            </form>

            <!-- Divider -->
            <div class="auth-divider">
                <span>hoặc tiếp tục với</span>
            </div>

            <!-- Social Login Buttons - Below Submit -->
            <div class="social-login-section">
                <button type="button" class="btn-social btn-google" id="google-login">
                    <svg width="18" height="18" viewBox="0 0 18 18">
                        <path fill="#4285F4"
                            d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" />
                        <path fill="#34A853"
                            d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z" />
                        <path fill="#FBBC05"
                            d="M3.964 10.71c-.18-.54-.282-1.117-.282-1.71s.102-1.17.282-1.71V4.958H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.042l3.007-2.332z" />
                        <path fill="#EA4335"
                            d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" />
                    </svg>
                    <span class="btn-social-text">Google</span>
                </button>

                <button type="button" class="btn-social btn-facebook" id="facebook-login">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff">
                        <path
                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                    </svg>
                    <span class="btn-social-text">Facebook</span>
                </button>
            </div>

            <!-- Footer -->
            <div class="auth-card-footer">
                <p>Chưa có tài khoản?
                    <a href="<?php echo home_url('/dang-ky'); ?>" class="register-link">
                        Đăng ký ngay
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer Text -->
        <div class="auth-footer-text">
            <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
        </div>
    </div>
</div>

<?php get_footer('auth'); // Load footer-auth.php ?>