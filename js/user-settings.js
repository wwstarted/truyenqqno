/**
 * User Settings - SPA JavaScript (FIXED VERSION)
 * Single Page Application for User Info & Change Password
 *
 * @package TruyenQQ
 * @version 1.0.2 - FIXED: Loading, Data sync, OTP, Avatar upload
 */

(function ($) {
  "use strict";

  const UserSettings = {
    currentTab: "user-info",
    userData: null,
    isLoading: false,

    init: function () {
      console.log("TruyenQQ User Settings: Initializing...");

      this.bindEvents();

      // Wait for DOM ready before loading
      $(document).ready(() => {
        this.loadInitialTab();
        this.handleBrowserNavigation();
      });

      console.log("TruyenQQ User Settings: Ready");
    },

    bindEvents: function () {
      $(document).on("click", ".tab-link", this.handleTabClick.bind(this));
      $(document).on(
        "submit",
        "#user-info-form",
        this.handleUserInfoSubmit.bind(this),
      );
      $(document).on(
        "submit",
        "#change-password-form",
        this.handlePasswordSubmit.bind(this),
      );
      $(document).on(
        "submit",
        "#verify-otp-form",
        this.handleOTPVerify.bind(this),
      );
      $(document).on(
        "click",
        ".btn-avatar",
        this.triggerAvatarUpload.bind(this),
      );
      $(document).on(
        "change",
        "#avatar-upload",
        this.handleAvatarUpload.bind(this),
      );
      $(document).on(
        "input",
        "#new-password",
        this.checkPasswordStrength.bind(this),
      );
      $(document).on(
        "input",
        "#confirm-new-password",
        this.validatePasswordMatch.bind(this),
      );
      $(document).on(
        "click",
        "#resend-otp-btn",
        this.handleResendOTP.bind(this),
      );
    },

    loadInitialTab: function () {
      const hash = window.location.hash.substring(1);
      const tab = hash === "doi-mat-khau" ? "change-password" : "user-info";

      console.log("Loading initial tab:", tab);
      this.switchTab(tab, true); // Force load on init
    },

    handleBrowserNavigation: function () {
      const self = this;
      window.addEventListener("hashchange", function () {
        const hash = window.location.hash.substring(1);
        const tab = hash === "doi-mat-khau" ? "change-password" : "user-info";
        self.switchTab(tab);
      });
    },

    handleTabClick: function (e) {
      e.preventDefault();
      const $link = $(e.currentTarget);
      const tab = $link.data("tab");
      const hash = $link.attr("href");

      window.location.hash = hash;
      this.switchTab(tab);
    },

    switchTab: function (tab, forceLoad = false) {
      // Prevent duplicate loading
      if (this.currentTab === tab && !forceLoad && this.userData) {
        console.log("Tab already loaded, skipping...");
        return;
      }

      this.currentTab = tab;

      $(".tab-link").removeClass("active is-active");
      $(`.tab-link[data-tab="${tab}"]`).addClass("active is-active");

      this.loadContent(tab);
    },

    loadContent: function (tab) {
      if (this.isLoading) {
        console.log("Already loading, please wait...");
        return;
      }

      const $contentArea = $("#settings-content-area");

      this.isLoading = true;
      $contentArea.html(`
        <div class="loading-spinner">
          <i class="fa fa-spinner fa-spin"></i>
          <p>Đang tải...</p>
        </div>
      `);

      if (tab === "user-info") {
        this.loadUserInfo();
      } else if (tab === "change-password") {
        this.loadChangePassword();
      }
    },

    loadUserInfo: function () {
      const self = this;

      $.ajax({
        url: `${userSettingsData.restUrl}/user/info`,
        method: "GET",
        headers: {
          "X-WP-Nonce": userSettingsData.nonce,
        },
        timeout: 10000, // 10s timeout
        success: function (response) {
          console.log("User info loaded:", response);
          self.userData = response;
          self.renderUserInfo(response);
          self.isLoading = false;
        },
        error: function (xhr, status, error) {
          console.error("Load user info error:", status, error);
          self.isLoading = false;
          self.showError(
            "Không thể tải thông tin người dùng. Vui lòng tải lại trang.",
          );
        },
      });
    },

    renderUserInfo: function (data) {
      const user = data.user;
      const avatarUrl =
        user.avatar ||
        `${userSettingsData.templateUrl}/images/info-user-img01.png`;

      const levelProgress = user.level_progress || 0;
      const currentLevel = user.level || 1;
      const nextLevel = currentLevel + 1;

      const html = `
        <div class="user-info-content fade-in">
          <!-- Avatar Section -->
          <div class="user-right column">
            <div class="img">
              <img class="image-avatar" id="preview-avatar" src="${avatarUrl}" alt="Avatar">
            </div>
            <input type="file" id="avatar-upload" accept="image/*" style="display: none;">
            <button type="button" class="button is-danger btn-avatar">Chọn hình</button>
          </div>

          <div class="user-right column warning-text">
            Dùng hình 18+ sẽ bị khóa tài khoản vĩnh viễn.
          </div>

          <!-- Level System -->
          <div class="user-main column">
            <div class="level title user-title">
              <div class="skillbox">
                <span class="level-current">Cấp ${currentLevel}</span>
                <span class="level-next">Cấp ${nextLevel}</span>
                <div class="progress">
                  <span class="progress-bar" style="width: ${levelProgress}%">${levelProgress}% (Sơ Kỳ)</span>
                </div>
              </div>
            </div>

            <!-- Account Info (Read-only) -->
            <div class="level title">
              <p class="level-left has-text-weight-bold">Thông tin tài khoản</p>
            </div>

            <div class="form-change-pass">
              <div class="field">
                <p class="txt">Điểm:</p>
                <p class="control">
                  <input class="input" type="text" value="${user.points || 0}" disabled>
                </p>
              </div>
              <div class="field">
                <p class="txt">Email:</p>
                <p class="control">
                  <input class="input" type="email" value="${user.email}" disabled>
                </p>
              </div>
            </div>

            <!-- Personal Info Form -->
            <div class="level title user-title">
              <p class="level-left has-text-weight-bold">Thông tin cá nhân</p>
            </div>

            <form id="user-info-form" class="user-form">
              <div class="form-message"></div>

              <div class="field">
                <p class="txt">Họ</p>
                <p class="control">
                  <input class="input" type="text" name="last_name" id="last_name" 
                         value="${this.escapeHtml(user.last_name || "")}" placeholder="Nhập họ">
                </p>
              </div>

              <div class="field">
                <p class="txt">Tên</p>
                <p class="control">
                  <input class="input" type="text" name="first_name" id="first_name" 
                         value="${this.escapeHtml(user.first_name || "")}" placeholder="Nhập tên">
                </p>
              </div>

              <div class="field">
                <p class="txt">Ngày sinh (dd/mm/yyyy)</p>
                <p class="control">
                  <input class="input" type="text" name="birth_date" id="birth_date" 
                         value="${this.escapeHtml(user.birth_date || "")}" placeholder="01/01/2000">
                </p>
              </div>

              <div class="field">
                <p class="txt">Số điện thoại</p>
                <p class="control">
                  <input class="input" type="text" name="phone" id="phone" 
                         value="${this.escapeHtml(user.phone || "")}" placeholder="Nhập số điện thoại">
                </p>
              </div>

              <div class="field user-field">
                <span class="txt">Giới tính</span>
                <input type="radio" id="gender1" name="gender" value="1" ${user.gender == 1 ? "checked" : ""}>
                <label for="gender1">Nam</label>
                <input type="radio" id="gender2" name="gender" value="0" ${user.gender == 0 ? "checked" : ""}>
                <label for="gender2">Nữ</label>
              </div>

              <div class="level title user-title">
                <p class="level-left has-text-weight-bold">Chọn Loại Cấp Bậc</p>
              </div>

              <div class="field user-field">
                ${this.generateRankOptions(user.rank)}
              </div>

              <input type="hidden" name="avatar" id="avatar-input" value="${user.avatar_id || ""}">

              <div class="field">
                <p class="control">
                  <button type="submit" class="button is-danger">
                    <span class="btn-text">Lưu</span>
                    <span class="btn-loading" style="display:none;">
                      <i class="fa fa-spinner fa-spin"></i> Đang lưu...
                    </span>
                  </button>
                </p>
              </div>
            </form>
          </div>
        </div>
      `;

      $("#settings-content-area").html(html);
    },

    escapeHtml: function (text) {
      if (!text) return "";
      const map = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;",
      };
      return text.replace(/[&<>"']/g, (m) => map[m]);
    },

    generateRankOptions: function (currentRank) {
      const ranks = [
        { value: 0, label: "Không Chọn", desc: "" },
        { value: 1, label: "Tu Tiên", desc: "Luyện khí, trúc cơ...." },
        { value: 2, label: "Game", desc: "Bình thường, tinh anh, hiệp sĩ...." },
        {
          value: 3,
          label: "Ma Vương",
          desc: "Ma vật, binh lính, tù trưởng....",
        },
        {
          value: 4,
          label: "Pháp Sư",
          desc: "Học đồ, ma pháp sư, ma đạo sư....",
        },
        {
          value: 5,
          label: "Tinh Không",
          desc: "Học đồ - Hành tinh - Hằng tinh - Vũ trụ - Vực chủ....",
        },
      ];

      return ranks
        .map(
          (rank) => `
        <p class="control list-radio">
          <input type="radio" id="rank${rank.value}" name="rank" value="${rank.value}" 
                 ${currentRank == rank.value ? "checked" : ""}>
          <label for="rank${rank.value}" title="${rank.desc}">${rank.label}</label>
        </p>
      `,
        )
        .join("");
    },

    loadChangePassword: function () {
      const isOAuth = userSettingsData.isOAuthUser;
      const provider = userSettingsData.oauthProvider;

      let html;

      if (isOAuth) {
        const providerName = provider === "google" ? "Google" : "Facebook";
        html = `
          <div class="change-password-content fade-in">
            <div class="oauth-warning">
              <i class="fab fa-${provider}"></i>
              <h3>Tài khoản ${providerName}</h3>
              <p>Bạn đang đăng nhập bằng tài khoản ${providerName}. Không thể đổi mật khẩu.</p>
              <p style="margin-top: 10px;">Nếu muốn sử dụng mật khẩu, vui lòng liên hệ quản trị viên.</p>
            </div>
          </div>
        `;
      } else {
        html = `
          <div class="change-password-content fade-in">
            <div class="level title">
              <p class="level-left has-text-weight-bold">Đổi mật khẩu</p>
            </div>

            <!-- Step 1: Request OTP -->
            <div id="password-step-1" class="password-step active">
              <form id="change-password-form">
                <div class="form-message"></div>

                <div class="form-change-pass">
                  <div class="field">
                    <p class="txt">Mật khẩu hiện tại</p>
                    <p class="control">
                      <input class="input" type="password" name="current_password" 
                             id="current-password" placeholder="Nhập mật khẩu hiện tại" required>
                    </p>
                  </div>

                  <div class="field">
                    <p class="txt">Mật khẩu mới</p>
                    <p class="control">
                      <input class="input" type="password" name="new_password" 
                             id="new-password" placeholder="Nhập mật khẩu mới" required>
                    </p>
                    <div class="password-strength-container">
                      <div class="strength-bar-container">
                        <div class="strength-bar"></div>
                      </div>
                      <span class="strength-text"></span>
                    </div>
                  </div>

                  <div class="field">
                    <p class="txt">Xác nhận mật khẩu</p>
                    <p class="control">
                      <input class="input" type="password" name="confirm_password" 
                             id="confirm-new-password" placeholder="Nhập lại mật khẩu mới" required>
                    </p>
                  </div>

                  <div class="field">
                    <p class="control">
                      <button type="submit" class="button is-danger">
                        <span class="btn-text">Tiếp tục</span>
                        <span class="btn-loading" style="display:none;">
                          <i class="fa fa-spinner fa-spin"></i> Đang xử lý...
                        </span>
                      </button>
                    </p>
                  </div>
                </div>
              </form>
            </div>

            <!-- Step 2: Verify OTP -->
            <div id="password-step-2" class="password-step" style="display:none;">
              <form id="verify-otp-form">
                <div class="form-message"></div>

                <div class="form-change-pass">
                  <p style="margin-bottom: 20px;">
                    Mã OTP đã được gửi đến email <strong id="email-display"></strong>. 
                    Vui lòng kiểm tra hộp thư và nhập mã bên dưới.
                  </p>

                  <div class="field">
                    <p class="txt">Mã OTP (6 chữ số)</p>
                    <p class="control">
                      <input class="input" type="text" name="otp_code" id="otp-code" 
                             placeholder="Nhập mã OTP" maxlength="6" pattern="[0-9]{6}" required>
                    </p>
                  </div>

                  <div class="otp-timer" style="display:none; margin-bottom: 15px; color: #666;">
                    Mã OTP sẽ hết hạn sau: <strong id="timer-countdown">30:00</strong>
                  </div>

                  <div class="otp-resend" style="margin-bottom: 20px;">
                    <a href="#" id="resend-otp-btn" class="link-text">Gửi lại mã OTP</a>
                  </div>

                  <div class="field">
                    <p class="control">
                      <button type="submit" class="button is-danger">
                        <span class="btn-text">Xác nhận</span>
                        <span class="btn-loading" style="display:none;">
                          <i class="fa fa-spinner fa-spin"></i> Đang xác nhận...
                        </span>
                      </button>
                    </p>
                  </div>
                </div>
              </form>
            </div>
          </div>
        `;
      }

      $("#settings-content-area").html(html);
      this.isLoading = false;
    },

    triggerAvatarUpload: function (e) {
      e.preventDefault();
      $("#avatar-upload").trigger("click");
    },

    handleAvatarUpload: function (e) {
      const file = e.target.files[0];
      if (!file) return;

      if (!file.type.match("image.*")) {
        this.showMessage(
          $("#user-info-form .form-message"),
          "error",
          "Vui lòng chọn file ảnh",
        );
        return;
      }

      if (file.size > 5 * 1024 * 1024) {
        this.showMessage(
          $("#user-info-form .form-message"),
          "error",
          "Kích thước ảnh không được vượt quá 5MB",
        );
        return;
      }

      const reader = new FileReader();
      reader.onload = function (e) {
        $("#preview-avatar").attr("src", e.target.result);
      };
      reader.readAsDataURL(file);

      this.uploadToWordPress(file);
    },

    uploadToWordPress: function (file) {
      const self = this;
      const formData = new FormData();
      formData.append("file", file);

      $(".btn-avatar").text("Đang tải lên...").prop("disabled", true);

      $.ajax({
        url: `${userSettingsData.restUrl}/media`,
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        headers: {
          "X-WP-Nonce": userSettingsData.nonce,
        },
        success: function (response) {
          console.log("Avatar upload response:", response);

          if (response.success && response.id) {
            $("#avatar-input").val(response.id);
            $(".btn-avatar").text("Chọn hình").prop("disabled", false);

            // ✅ UPDATE HEADER AVATAR IMMEDIATELY
            if (response.url) {
              self.updateHeaderAvatar(response.url);
            }

            // Show success message in form
            self.showMessage(
              $("#user-info-form .form-message"),
              "success",
              "Tải ảnh thành công! Nhấn Lưu để cập nhật.",
            );
          } else {
            $(".btn-avatar").text("Chọn hình").prop("disabled", false);
            self.showMessage(
              $("#user-info-form .form-message"),
              "error",
              response.message || "Lỗi tải ảnh lên. Vui lòng thử lại.",
            );
          }
        },
        error: function (xhr) {
          console.error("Avatar upload error:", xhr);
          $(".btn-avatar").text("Chọn hình").prop("disabled", false);

          const response = xhr.responseJSON;
          self.showMessage(
            $("#user-info-form .form-message"),
            "error",
            response?.message || "Lỗi tải ảnh lên. Vui lòng thử lại.",
          );
        },
      });
    },

    /**
     * ✅ NEW: Update header avatar in real-time
     */
    updateHeaderAvatar: function (avatarUrl) {
      console.log("Updating header avatar to:", avatarUrl);

      // Update all avatar images in header
      const $headerAvatars = $("#userAvatarImg, #userDropdownAvatar");

      $headerAvatars.each(function () {
        $(this).attr("src", avatarUrl);
      });

      // Dispatch custom event for other scripts
      if (window.TruyenQQ_Events) {
        window.TruyenQQ_Events.trigger("avatar:updated", { url: avatarUrl });
      }

      console.log("Header avatar updated successfully");
    },

    handleUserInfoSubmit: function (e) {
      e.preventDefault();

      const $form = $(e.target);
      const $btn = $form.find('button[type="submit"]');
      const $message = $form.find(".form-message");

      const formData = {
        last_name: $("#last_name").val().trim(),
        first_name: $("#first_name").val().trim(),
        birth_date: $("#birth_date").val().trim(),
        phone: $("#phone").val().trim(),
        gender: $('input[name="gender"]:checked').val(),
        rank: $('input[name="rank"]:checked').val(),
        avatar: $("#avatar-input").val(),
      };

      if (formData.birth_date && !this.validateDate(formData.birth_date)) {
        this.showMessage(
          $message,
          "error",
          "Ngày sinh không đúng định dạng (dd/mm/yyyy)",
        );
        return;
      }

      this.setButtonLoading($btn, true);
      $message.removeClass("show");

      const self = this;

      $.ajax({
        url: `${userSettingsData.restUrl}/user/update-info`,
        method: "POST",
        data: JSON.stringify(formData),
        contentType: "application/json",
        headers: {
          "X-WP-Nonce": userSettingsData.nonce,
        },
        success: (response) => {
          self.setButtonLoading($btn, false);
          if (response.success) {
            self.showMessage(
              $message,
              "success",
              response.message || "Cập nhật thành công!",
            );

            // FORCE RELOAD USER DATA after 1 second
            setTimeout(() => {
              self.userData = null; // Clear cache
              self.loadUserInfo(); // Reload fresh data
            }, 1000);
          } else {
            self.showMessage(
              $message,
              "error",
              response.message || "Có lỗi xảy ra",
            );
          }
        },
        error: () => {
          self.setButtonLoading($btn, false);
          self.showMessage(
            $message,
            "error",
            "Có lỗi xảy ra. Vui lòng thử lại.",
          );
        },
      });
    },

    handlePasswordSubmit: function (e) {
      e.preventDefault();

      const $form = $(e.target);
      const $btn = $form.find('button[type="submit"]');
      const $message = $form.find(".form-message");

      const currentPassword = $("#current-password").val();
      const newPassword = $("#new-password").val();
      const confirmPassword = $("#confirm-new-password").val();

      if (newPassword !== confirmPassword) {
        this.showMessage($message, "error", "Mật khẩu xác nhận không khớp");
        return;
      }

      if (newPassword.length < 6) {
        this.showMessage(
          $message,
          "error",
          "Mật khẩu mới phải có ít nhất 6 ký tự",
        );
        return;
      }

      this.setButtonLoading($btn, true);
      $message.removeClass("show");

      const self = this;

      $.ajax({
        url: `${userSettingsData.restUrl}/user/send-password-otp`,
        method: "POST",
        data: JSON.stringify({
          current_password: currentPassword,
          new_password: newPassword,
        }),
        contentType: "application/json",
        headers: {
          "X-WP-Nonce": userSettingsData.nonce,
        },
        success: (response) => {
          self.setButtonLoading($btn, false);
          if (response.success) {
            // Switch to OTP step
            $("#password-step-1").hide();
            $("#password-step-2").show();
            $("#email-display").text(response.email);

            // Start OTP timer
            self.startOTPTimer(1800); // 30 minutes

            self.showMessage(
              $("#password-step-2 .form-message"),
              "success",
              response.message,
            );
          } else {
            self.showMessage(
              $message,
              "error",
              response.message || "Có lỗi xảy ra",
            );
          }
        },
        error: (xhr) => {
          self.setButtonLoading($btn, false);
          const response = xhr.responseJSON;
          self.showMessage(
            $message,
            "error",
            response?.message || "Có lỗi xảy ra. Vui lòng thử lại.",
          );
        },
      });
    },

    handleOTPVerify: function (e) {
      e.preventDefault();

      const $form = $(e.target);
      const $btn = $form.find('button[type="submit"]');
      const $message = $form.find(".form-message");

      const otpCode = $("#otp-code").val().trim();

      if (!/^\d{6}$/.test(otpCode)) {
        this.showMessage($message, "error", "Mã OTP phải là 6 chữ số");
        return;
      }

      this.setButtonLoading($btn, true);
      $message.removeClass("show");

      const self = this;

      $.ajax({
        url: `${userSettingsData.restUrl}/user/verify-password-otp`,
        method: "POST",
        data: JSON.stringify({
          otp_code: otpCode,
        }),
        contentType: "application/json",
        headers: {
          "X-WP-Nonce": userSettingsData.nonce,
        },
        success: (response) => {
          self.setButtonLoading($btn, false);
          if (response.success) {
            self.showMessage(
              $message,
              "success",
              response.message || "Đổi mật khẩu thành công!",
            );

            // Reset form after 2 seconds
            setTimeout(() => {
              self.loadChangePassword();
            }, 2000);
          } else {
            self.showMessage(
              $message,
              "error",
              response.message || "Mã OTP không đúng",
            );
          }
        },
        error: (xhr) => {
          self.setButtonLoading($btn, false);
          const response = xhr.responseJSON;
          self.showMessage(
            $message,
            "error",
            response?.message || "Có lỗi xảy ra. Vui lòng thử lại.",
          );
        },
      });
    },

    handleResendOTP: function (e) {
      e.preventDefault();

      const $link = $(e.currentTarget);
      const $message = $("#password-step-2 .form-message");

      $link.text("Đang gửi...").css("pointer-events", "none");

      const self = this;

      $.ajax({
        url: `${userSettingsData.restUrl}/user/resend-password-otp`,
        method: "POST",
        headers: {
          "X-WP-Nonce": userSettingsData.nonce,
        },
        success: (response) => {
          $link.text("Gửi lại mã OTP").css("pointer-events", "");
          if (response.success) {
            self.showMessage($message, "success", "Mã OTP mới đã được gửi!");
            self.startOTPTimer(1800);
          } else {
            self.showMessage(
              $message,
              "error",
              response.message || "Không thể gửi lại OTP",
            );
          }
        },
        error: () => {
          $link.text("Gửi lại mã OTP").css("pointer-events", "");
          self.showMessage(
            $message,
            "error",
            "Có lỗi xảy ra. Vui lòng thử lại.",
          );
        },
      });
    },

    startOTPTimer: function (seconds) {
      const $timer = $(".otp-timer");
      const $countdown = $("#timer-countdown");
      const $resendLink = $("#resend-otp-btn");

      $resendLink.parent().hide();
      $timer.show();

      let remaining = seconds;

      const interval = setInterval(() => {
        remaining--;

        const minutes = Math.floor(remaining / 60);
        const secs = remaining % 60;
        $countdown.text(`${minutes}:${secs.toString().padStart(2, "0")}`);

        if (remaining <= 0) {
          clearInterval(interval);
          $timer.hide();
          $resendLink.parent().show();
        }
      }, 1000);
    },

    checkPasswordStrength: function (e) {
      const password = $(e.target).val();
      const $container = $(e.target).closest(".field");
      const $strengthBar = $container.find(".strength-bar");
      const $strengthText = $container.find(".strength-text");

      if (password.length === 0) {
        $strengthBar.removeClass("weak medium strong").css("width", "0");
        $strengthText.text("");
        return;
      }

      let strength = 0;
      if (password.length >= 6) strength++;
      if (password.length >= 10) strength++;
      if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
      if (/\d/.test(password)) strength++;
      if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;

      $strengthBar.removeClass("weak medium strong");
      $strengthText.removeClass("weak medium strong");

      if (strength <= 2) {
        $strengthBar.addClass("weak");
        $strengthText.addClass("weak").text("Yếu");
      } else if (strength <= 4) {
        $strengthBar.addClass("medium");
        $strengthText.addClass("medium").text("Trung bình");
      } else {
        $strengthBar.addClass("strong");
        $strengthText.addClass("strong").text("Mạnh");
      }
    },

    validatePasswordMatch: function (e) {
      const $input = $(e.target);
      const newPassword = $("#new-password").val();
      const confirmPassword = $input.val();

      if (confirmPassword.length === 0) {
        $input.css("border-color", "");
        return;
      }

      if (newPassword === confirmPassword) {
        $input.css("border-color", "#27ae60");
      } else {
        $input.css("border-color", "#e74c3c");
      }
    },

    validateDate: function (dateString) {
      const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
      const match = dateString.match(regex);

      if (!match) return false;

      const day = parseInt(match[1], 10);
      const month = parseInt(match[2], 10);
      const year = parseInt(match[3], 10);

      if (month < 1 || month > 12) return false;
      if (day < 1 || day > 31) return false;
      if (year < 1900 || year > new Date().getFullYear()) return false;

      const date = new Date(year, month - 1, day);
      return (
        date.getFullYear() === year &&
        date.getMonth() === month - 1 &&
        date.getDate() === day
      );
    },

    setButtonLoading: function ($btn, loading) {
      if (loading) {
        $btn.prop("disabled", true);
        $btn.find(".btn-text").hide();
        $btn.find(".btn-loading").show();
      } else {
        $btn.prop("disabled", false);
        $btn.find(".btn-text").show();
        $btn.find(".btn-loading").hide();
      }
    },

    showMessage: function ($element, type, message) {
      $element
        .removeClass("success error")
        .addClass(type + " show")
        .html(
          '<i class="fa fa-' +
            (type === "success" ? "check-circle" : "exclamation-circle") +
            '"></i> ' +
            message,
        );

      setTimeout(function () {
        $element.removeClass("show");
      }, 5000);
    },

    showError: function (message) {
      $("#settings-content-area").html(`
        <div class="form-message error show" style="display:block;">
          <i class="fa fa-exclamation-circle"></i> ${message}
        </div>
      `);
    },
  };

  $(document).ready(function () {
    UserSettings.init();
  });
})(jQuery);
