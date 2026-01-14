<div id="auth-modals-container">
    <!-- Login Modal -->
    <div class="auth-modal" id="login-modal">
        <div class="auth-modal-overlay"></div>
        <div class="auth-modal-content">
            <button class="auth-modal-close">
                <i class="fa fa-times"></i>
            </button>

            <div class="auth-modal-header">
                <h2><i class="fa fa-sign-in"></i> Đăng Nhập</h2>
                <p>Đăng nhập để theo dõi truyện yêu thích</p>
            </div>

            <div class="auth-modal-body">
                <form id="login-form">
                    <div class="form-group">
                        <label for="login-username">Tên đăng nhập hoặc Email</label>
                        <input type="text" id="login-username" name="username"
                            placeholder="Nhập tên đăng nhập hoặc email" required>
                    </div>

                    <div class="form-group">
                        <label for="login-password">Mật khẩu</label>
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
                        <a href="#" class="link-text" data-modal="forgot-password">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="btn-text">Đăng Nhập</span>
                        <span class="btn-loading" style="display:none;">
                            <i class="fa fa-spinner fa-spin"></i> Đang xử lý...
                        </span>
                    </button>

                    <div class="form-message"></div>
                </form>

                <div class="auth-modal-footer">
                    <p>Chưa có tài khoản? <a href="#" data-modal="register">Đăng ký ngay</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="auth-modal" id="register-modal">
        <div class="auth-modal-overlay"></div>
        <div class="auth-modal-content">
            <button class="auth-modal-close">
                <i class="fa fa-times"></i>
            </button>

            <div class="auth-modal-header">
                <h2><i class="fa fa-user-plus"></i> Đăng Ký Tài Khoản</h2>
                <p>Tạo tài khoản để trải nghiệm đầy đủ tính năng</p>
            </div>

            <div class="auth-modal-body">
                <!-- Step 1: Register Form -->
                <form id="register-form" class="auth-step active" data-step="1">
                    <div class="form-group">
                        <label for="register-username">Tên đăng nhập</label>
                        <input type="text" id="register-username" name="username" placeholder="Chữ, số và dấu gạch dưới"
                            required>
                        <small class="form-hint">Chỉ sử dụng chữ cái, số và dấu gạch dưới (_)</small>
                    </div>

                    <div class="form-group">
                        <label for="register-email">Email</label>
                        <input type="email" id="register-email" name="email" placeholder="email@example.com" required>
                        <small class="form-hint">Chúng tôi sẽ gửi mã OTP đến email này</small>
                    </div>

                    <div class="form-group">
                        <label for="register-password">Mật khẩu</label>
                        <div class="password-input">
                            <input type="password" id="register-password" name="password" placeholder="Ít nhất 6 ký tự"
                                required>
                            <button type="button" class="toggle-password">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="btn-text">Gửi Mã OTP</span>
                        <span class="btn-loading" style="display:none;">
                            <i class="fa fa-spinner fa-spin"></i> Đang gửi...
                        </span>
                    </button>

                    <div class="form-message"></div>
                </form>

                <!-- Step 2: OTP Verification -->
                <div id="register-otp-form" class="auth-step" data-step="2">
                    <div class="otp-info">
                        <i class="fa fa-envelope-o"></i>
                        <p>Chúng tôi đã gửi mã OTP (6 số) đến email:</p>
                        <strong id="register-email-display"></strong>
                    </div>

                    <form id="register-verify-form">
                        <div class="form-group">
                            <label for="register-otp">Nhập mã OTP</label>
                            <input type="text" id="register-otp" name="otp_code" placeholder="000000" maxlength="6"
                                required pattern="[0-9]{6}">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <span class="btn-text">Xác Thực & Đăng Ký</span>
                            <span class="btn-loading" style="display:none;">
                                <i class="fa fa-spinner fa-spin"></i> Đang xử lý...
                            </span>
                        </button>

                        <div class="form-message"></div>

                        <div class="otp-resend">
                            <p>Không nhận được mã? <a href="#" id="register-resend-otp">Gửi lại</a></p>
                            <p class="otp-timer" style="display:none;">Gửi lại sau <span id="register-timer">60</span>s
                            </p>
                        </div>
                    </form>
                </div>

                <div class="auth-modal-footer">
                    <p>Đã có tài khoản? <a href="#" data-modal="login">Đăng nhập</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="auth-modal" id="forgot-password-modal">
        <div class="auth-modal-overlay"></div>
        <div class="auth-modal-content">
            <button class="auth-modal-close">
                <i class="fa fa-times"></i>
            </button>

            <div class="auth-modal-header">
                <h2><i class="fa fa-key"></i> Quên Mật Khẩu</h2>
                <p>Nhập email để nhận mã xác thực</p>
            </div>

            <div class="auth-modal-body">
                <!-- Step 1: Enter Email -->
                <form id="forgot-password-form" class="auth-step active" data-step="1">
                    <div class="form-group">
                        <label for="forgot-email">Email</label>
                        <input type="email" id="forgot-email" name="email" placeholder="email@example.com" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="btn-text">Gửi Mã OTP</span>
                        <span class="btn-loading" style="display:none;">
                            <i class="fa fa-spinner fa-spin"></i> Đang gửi...
                        </span>
                    </button>

                    <div class="form-message"></div>
                </form>

                <!-- Step 2: OTP Verification -->
                <div id="forgot-otp-form" class="auth-step" data-step="2">
                    <div class="otp-info">
                        <i class="fa fa-envelope-o"></i>
                        <p>Chúng tôi đã gửi mã OTP đến email:</p>
                        <strong id="forgot-email-display"></strong>
                    </div>

                    <form id="forgot-verify-form">
                        <div class="form-group">
                            <label for="forgot-otp">Nhập mã OTP</label>
                            <input type="text" id="forgot-otp" name="otp_code" placeholder="000000" maxlength="6"
                                required pattern="[0-9]{6}">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <span class="btn-text">Xác Thực</span>
                            <span class="btn-loading" style="display:none;">
                                <i class="fa fa-spinner fa-spin"></i> Đang xử lý...
                            </span>
                        </button>

                        <div class="form-message"></div>

                        <div class="otp-resend">
                            <p>Không nhận được mã? <a href="#" id="forgot-resend-otp">Gửi lại</a></p>
                            <p class="otp-timer" style="display:none;">Gửi lại sau <span id="forgot-timer">60</span>s
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Step 3: Reset Password -->
                <div id="reset-password-form-container" class="auth-step" data-step="3">
                    <form id="reset-password-form">
                        <input type="hidden" id="reset-token" name="reset_token">

                        <div class="form-group">
                            <label for="new-password">Mật khẩu mới</label>
                            <div class="password-input">
                                <input type="password" id="new-password" name="new_password"
                                    placeholder="Ít nhất 6 ký tự" required>
                                <button type="button" class="toggle-password">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm-password">Xác nhận mật khẩu</label>
                            <div class="password-input">
                                <input type="password" id="confirm-password" name="confirm_password"
                                    placeholder="Nhập lại mật khẩu" required>
                                <button type="button" class="toggle-password">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <span class="btn-text">Đặt Lại Mật Khẩu</span>
                            <span class="btn-loading" style="display:none;">
                                <i class="fa fa-spinner fa-spin"></i> Đang xử lý...
                            </span>
                        </button>

                        <div class="form-message"></div>
                    </form>
                </div>

                <div class="auth-modal-footer">
                    <p>Nhớ mật khẩu? <a href="#" data-modal="login">Đăng nhập</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Menu (when logged in) -->
<div id="user-menu" style="display:none;">
    <button class="user-menu-trigger">
        <img src="" alt="Avatar" class="user-avatar">
        <span class="user-display-name"></span>
        <i class="fa fa-chevron-down"></i>
    </button>
    <div class="user-menu-dropdown">
        <a href="#" class="user-menu-item">
            <i class="fa fa-user"></i> Trang cá nhân
        </a>
        <a href="#" class="user-menu-item">
            <i class="fa fa-bookmark"></i> Truyện theo dõi
        </a>
        <a href="#" class="user-menu-item">
            <i class="fa fa-history"></i> Lịch sử đọc
        </a>
        <a href="#" class="user-menu-item">
            <i class="fa fa-cog"></i> Cài đặt
        </a>
        <div class="user-menu-divider"></div>
        <a href="#" class="user-menu-item" id="logout-btn">
            <i class="fa fa-sign-out"></i> Đăng xuất
        </a>
    </div>
</div>

<!-- Auth Buttons (when not logged in) -->
<div id="auth-buttons">
    <button class="btn btn-outline" data-modal="login">
        <i class="fa fa-sign-in"></i> Đăng nhập
    </button>
    <button class="btn btn-primary" data-modal="register">
        <i class="fa fa-user-plus"></i> Đăng ký
    </button>
</div>

<?php get_footer() ?>