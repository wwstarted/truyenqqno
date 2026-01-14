/**
 * Authentication Pages JavaScript
 * Handle auth interactions with reCAPTCHA
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function ($) {
  "use strict";

  // ==================== INIT ====================
  $(document).ready(function () {
    initAuthPages();
  });

  function initAuthPages() {
    // Toggle password visibility
    $(document).on("click", ".toggle-password", function () {
      const input = $(this).siblings("input");
      const icon = $(this).find("i");

      if (input.attr("type") === "password") {
        input.attr("type", "text");
        icon.removeClass("fa-eye").addClass("fa-eye-slash");
      } else {
        input.attr("type", "password");
        icon.removeClass("fa-eye-slash").addClass("fa-eye");
      }
    });

    // Password strength checker
    $("#register-password, #new-password").on("input", function () {
      checkPasswordStrength($(this));
    });

    // Form submissions
    $("#login-form").on("submit", handleLogin);
    $("#register-form").on("submit", handleRegisterSendOTP);
    $("#register-verify-form").on("submit", handleRegisterVerifyOTP);
    $("#forgot-password-form").on("submit", handleForgotPasswordSendOTP);
    $("#forgot-verify-form").on("submit", handleForgotPasswordVerifyOTP);
    $("#reset-password-form").on("submit", handleResetPassword);

    // Resend OTP
    $("#register-resend-otp").on("click", function (e) {
      e.preventDefault();
      resendOTP("register");
    });

    $("#forgot-resend-otp").on("click", function (e) {
      e.preventDefault();
      resendOTP("reset_password");
    });

    // Back to register button
    $("#back-to-register").on("click", function () {
      $(".auth-step").removeClass("active");
      $('.auth-step[data-step="1"]').addClass("active");
      $(".step").removeClass("active");
      $('.step[data-step="1"]').addClass("active");
    });

    // Password confirmation validation
    $("#register-confirm-password, #confirm-password").on("input", function () {
      validatePasswordMatch($(this));
    });
  }

  // ==================== PASSWORD STRENGTH ====================
  function checkPasswordStrength($input) {
    const password = $input.val();
    const $container = $input.closest(".form-group");
    const $strengthBar = $container.find(".strength-bar");
    const $strengthText = $container.find(".strength-text");

    if (password.length === 0) {
      $strengthBar.removeClass("weak medium strong");
      $strengthText.text("");
      return;
    }

    let strength = 0;

    // Length check
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;

    // Complexity checks
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;

    // Update UI
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
  }

  // ==================== PASSWORD MATCH VALIDATION ====================
  function validatePasswordMatch($input) {
    const password = $("#register-password").val() || $("#new-password").val();
    const confirmPassword = $input.val();

    if (confirmPassword.length === 0) {
      $input.css("border-color", "");
      return;
    }

    if (password === confirmPassword) {
      $input.css("border-color", "#4caf50");
    } else {
      $input.css("border-color", "#f44336");
    }
  }

  // ==================== LOGIN ====================
  function handleLogin(e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $form.find('button[type="submit"]');
    const $message = $form.find(".form-message");

    // Validate reCAPTCHA
    if (typeof grecaptcha === "undefined") {
      showMessage(
        $message,
        "error",
        "reCAPTCHA chưa tải xong. Vui lòng đợi vài giây."
      );
      return;
    }

    const recaptchaResponse = grecaptcha.getResponse();
    if (!recaptchaResponse) {
      showMessage($message, "error", "Vui lòng xác nhận bạn không phải robot");
      return;
    }

    const username = $("#login-username").val().trim();
    const password = $("#login-password").val();
    const remember = $("#login-remember").is(":checked");

    setLoadingState($btn, true);
    $message.removeClass("show");

    $.ajax({
      url: truyenqqAuth.ajax_url,
      type: "POST",
      data: {
        action: "login",
        nonce: truyenqqAuth.nonce,
        username: username,
        password: password,
        remember: remember,
        recaptcha_response: recaptchaResponse,
      },
      success: function (response) {
        setLoadingState($btn, false);

        if (response.success) {
          showMessage($message, "success", response.message);
          setTimeout(function () {
            window.location.href = response.redirect || window.location.href;
          }, 1000);
        } else {
          showMessage($message, "error", response.message);
          if (typeof grecaptcha !== "undefined") {
            grecaptcha.reset();
          }
        }
      },
      error: function (xhr, status, error) {
        setLoadingState($btn, false);
        console.error("AJAX Error:", status, error);
        console.error("Response:", xhr.responseText);
        showMessage($message, "error", "Có lỗi xảy ra. Vui lòng thử lại.");
        if (typeof grecaptcha !== "undefined") {
          grecaptcha.reset();
        }
      },
    });
  }

  // ==================== REGISTER - SEND OTP ====================
  function handleRegisterSendOTP(e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $form.find('button[type="submit"]');
    const $message = $form.find(".form-message");

    // Validate reCAPTCHA
    if (typeof grecaptcha === "undefined") {
      showMessage(
        $message,
        "error",
        "reCAPTCHA chưa tải xong. Vui lòng đợi vài giây."
      );
      return;
    }

    const recaptchaResponse = grecaptcha.getResponse();
    if (!recaptchaResponse) {
      showMessage($message, "error", "Vui lòng xác nhận bạn không phải robot");
      return;
    }

    const username = $("#register-username").val().trim();
    const email = $("#register-email").val().trim();
    const password = $("#register-password").val();
    const confirmPassword = $("#register-confirm-password").val();
    const agreeTerms = $("#agree-terms").is(":checked");

    // Validate password match
    if (password !== confirmPassword) {
      showMessage($message, "error", "Mật khẩu xác nhận không khớp");
      return;
    }

    // Validate terms
    if (!agreeTerms) {
      showMessage($message, "error", "Vui lòng đồng ý với điều khoản sử dụng");
      return;
    }

    setLoadingState($btn, true);
    $message.removeClass("show");

    $.ajax({
      url: truyenqqAuth.ajax_url,
      type: "POST",
      data: {
        action: "register_send_otp",
        nonce: truyenqqAuth.nonce,
        username: username,
        email: email,
        password: password,
        recaptcha_response: recaptchaResponse,
      },
      success: function (response) {
        setLoadingState($btn, false);

        if (response.success) {
          // Move to step 2
          $(".auth-step").removeClass("active");
          $('.auth-step[data-step="2"]').addClass("active");
          $(".step").removeClass("active");
          $('.step[data-step="1"], .step[data-step="2"]').addClass("active");

          $("#register-email-display").text(email);
          startOTPTimer("register", 60);
          showMessage(
            $('.auth-step[data-step="2"] .form-message'),
            "success",
            response.message
          );
        } else {
          showMessage($message, "error", response.message);
          if (typeof grecaptcha !== "undefined") {
            grecaptcha.reset();
          }
        }
      },
      error: function (xhr, status, error) {
        setLoadingState($btn, false);
        console.error("AJAX Error:", status, error);
        console.error("Response:", xhr.responseText);
        showMessage($message, "error", "Có lỗi xảy ra. Vui lòng thử lại.");
        if (typeof grecaptcha !== "undefined") {
          grecaptcha.reset();
        }
      },
    });
  }

  // ==================== REGISTER - VERIFY OTP ====================
  function handleRegisterVerifyOTP(e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $form.find('button[type="submit"]');
    const $message = $form.find(".form-message");

    const email = $("#register-email").val().trim();
    const otp_code = $("#register-otp").val().trim();

    // Validate OTP format
    if (!/^\d{6}$/.test(otp_code)) {
      showMessage($message, "error", "Mã OTP phải là 6 chữ số");
      return;
    }

    setLoadingState($btn, true);
    $message.removeClass("show");

    $.ajax({
      url: truyenqqAuth.ajax_url,
      type: "POST",
      data: {
        action: "register_verify_otp",
        nonce: truyenqqAuth.nonce,
        email: email,
        otp_code: otp_code,
      },
      success: function (response) {
        setLoadingState($btn, false);

        if (response.success) {
          showMessage($message, "success", response.message);
          setTimeout(function () {
            window.location.href = response.redirect || window.location.href;
          }, 1000);
        } else {
          showMessage($message, "error", response.message);
        }
      },
      error: function () {
        setLoadingState($btn, false);
        showMessage($message, "error", "Có lỗi xảy ra. Vui lòng thử lại.");
      },
    });
  }

  // ==================== FORGOT PASSWORD - SEND OTP ====================
  function handleForgotPasswordSendOTP(e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $form.find('button[type="submit"]');
    const $message = $form.find(".form-message");

    // Validate reCAPTCHA
    if (typeof grecaptcha === "undefined") {
      showMessage(
        $message,
        "error",
        "reCAPTCHA chưa tải xong. Vui lòng đợi vài giây."
      );
      return;
    }

    const recaptchaResponse = grecaptcha.getResponse();
    if (!recaptchaResponse) {
      showMessage($message, "error", "Vui lòng xác nhận bạn không phải robot");
      return;
    }

    const email = $("#forgot-email").val().trim();

    setLoadingState($btn, true);
    $message.removeClass("show");

    $.ajax({
      url: truyenqqAuth.ajax_url,
      type: "POST",
      data: {
        action: "forgot_password_send_otp",
        nonce: truyenqqAuth.nonce,
        email: email,
        recaptcha_response: recaptchaResponse,
      },
      success: function (response) {
        setLoadingState($btn, false);

        if (response.success) {
          // Move to step 2
          $(".auth-step").removeClass("active");
          $('.auth-step[data-step="2"]').addClass("active");
          $(".step").removeClass("active");
          $('.step[data-step="1"], .step[data-step="2"]').addClass("active");

          $("#forgot-email-display").text(email);
          startOTPTimer("forgot", 60);
          showMessage(
            $('.auth-step[data-step="2"] .form-message'),
            "success",
            response.message
          );
        } else {
          showMessage($message, "error", response.message);
          if (typeof grecaptcha !== "undefined") {
            grecaptcha.reset();
          }
        }
      },
      error: function (xhr, status, error) {
        setLoadingState($btn, false);
        console.error("AJAX Error:", status, error);
        console.error("Response:", xhr.responseText);
        showMessage($message, "error", "Có lỗi xảy ra. Vui lòng thử lại.");
        if (typeof grecaptcha !== "undefined") {
          grecaptcha.reset();
        }
      },
    });
  }

  // ==================== FORGOT PASSWORD - VERIFY OTP ====================
  function handleForgotPasswordVerifyOTP(e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $form.find('button[type="submit"]');
    const $message = $form.find(".form-message");

    const email = $("#forgot-email").val().trim();
    const otp_code = $("#forgot-otp").val().trim();

    // Validate OTP format
    if (!/^\d{6}$/.test(otp_code)) {
      showMessage($message, "error", "Mã OTP phải là 6 chữ số");
      return;
    }

    setLoadingState($btn, true);
    $message.removeClass("show");

    $.ajax({
      url: truyenqqAuth.ajax_url,
      type: "POST",
      data: {
        action: "forgot_password_verify_otp",
        nonce: truyenqqAuth.nonce,
        email: email,
        otp_code: otp_code,
      },
      success: function (response) {
        setLoadingState($btn, false);

        if (response.success) {
          // Move to step 3
          $(".auth-step").removeClass("active");
          $('.auth-step[data-step="3"]').addClass("active");
          $(".step").removeClass("active");
          $(".step").addClass("active");

          $("#reset-token").val(response.reset_token);
          showMessage(
            $('.auth-step[data-step="3"] .form-message'),
            "success",
            response.message
          );
        } else {
          showMessage($message, "error", response.message);
        }
      },
      error: function () {
        setLoadingState($btn, false);
        showMessage($message, "error", "Có lỗi xảy ra. Vui lòng thử lại.");
      },
    });
  }

  // ==================== RESET PASSWORD ====================
  function handleResetPassword(e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $form.find('button[type="submit"]');
    const $message = $form.find(".form-message");

    const reset_token = $("#reset-token").val();
    const new_password = $("#new-password").val();
    const confirm_password = $("#confirm-password").val();

    // Validate password match
    if (new_password !== confirm_password) {
      showMessage($message, "error", "Mật khẩu xác nhận không khớp");
      return;
    }

    setLoadingState($btn, true);
    $message.removeClass("show");

    $.ajax({
      url: truyenqqAuth.ajax_url,
      type: "POST",
      data: {
        action: "reset_password",
        nonce: truyenqqAuth.nonce,
        reset_token: reset_token,
        new_password: new_password,
      },
      success: function (response) {
        setLoadingState($btn, false);

        if (response.success) {
          showMessage($message, "success", response.message);
          setTimeout(function () {
            window.location.href = response.redirect || window.location.href;
          }, 1000);
        } else {
          showMessage($message, "error", response.message);
        }
      },
      error: function () {
        setLoadingState($btn, false);
        showMessage($message, "error", "Có lỗi xảy ra. Vui lòng thử lại.");
      },
    });
  }

  // ==================== RESEND OTP ====================
  function resendOTP(purpose) {
    let email;
    if (purpose === "register") {
      email = $("#register-email").val().trim();
    } else {
      email = $("#forgot-email").val().trim();
    }

    $.ajax({
      url: truyenqqAuth.ajax_url,
      type: "POST",
      data: {
        action: "resend_otp",
        nonce: truyenqqAuth.nonce,
        email: email,
        purpose: purpose,
      },
      success: function (response) {
        const prefix = purpose === "register" ? "register" : "forgot";
        const $message = $('.auth-step[data-step="2"] .form-message');

        if (response.success) {
          showMessage($message, "success", "Mã OTP mới đã được gửi!");
          startOTPTimer(prefix, 60);
        } else {
          showMessage($message, "error", response.message);
        }
      },
    });
  }

  // ==================== OTP TIMER ====================
  function startOTPTimer(prefix, seconds) {
    const $resendLink = $("#" + prefix + "-resend-otp");
    const $timer = $("#" + prefix + "-timer");
    const $timerContainer = $resendLink.parent().siblings(".otp-timer");

    $resendLink.parent().hide();
    $timerContainer.show();

    let remaining = seconds;
    $timer.text(remaining);

    const interval = setInterval(function () {
      remaining--;
      $timer.text(remaining);

      if (remaining <= 0) {
        clearInterval(interval);
        $timerContainer.hide();
        $resendLink.parent().show();
      }
    }, 1000);
  }

  // ==================== HELPERS ====================
  function setLoadingState($btn, loading) {
    if (loading) {
      $btn.prop("disabled", true);
      $btn.find(".btn-text").hide();
      $btn.find(".btn-loading").show();
    } else {
      $btn.prop("disabled", false);
      $btn.find(".btn-text").show();
      $btn.find(".btn-loading").hide();
    }
  }

  function showMessage($element, type, message) {
    $element
      .removeClass("success error")
      .addClass(type + " show")
      .html(
        '<i class="fa fa-' +
          (type === "success" ? "check-circle" : "exclamation-circle") +
          '"></i> ' +
          message
      );

    // Auto hide after 5 seconds
    setTimeout(function () {
      $element.removeClass("show");
    }, 5000);
  }
})(jQuery);
