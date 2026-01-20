/**
 * Social Login Handler - Google & Facebook OAuth
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function ($) {
  "use strict";

  $(document).ready(function () {
    console.log("TruyenQQ Social Login: Initializing...");
    initSocialLogin();
  });

  function initSocialLogin() {
    // Google Login
    $("#login-google").on("click", handleGoogleLogin);

    // Facebook Login
    $("#login-facebook").on("click", handleFacebookLogin);

    console.log("TruyenQQ Social Login: Initialization complete");
  }

  /**
   * Google OAuth Login
   */
  function handleGoogleLogin(e) {
    e.preventDefault();
    console.log("Google Login clicked");

    const $btn = $(this);
    const originalHTML = $btn.html();

    // Check if Google OAuth is configured
    if (typeof truyenqqAuth === "undefined" || !truyenqqAuth.google_client_id) {
      showToast(
        "error",
        "Đăng nhập Google chưa được cấu hình. Vui lòng liên hệ quản trị viên.",
      );
      console.error("Google Client ID not configured");
      return;
    }

    // Show loading state
    $btn.prop("disabled", true);
    $btn.html('<i class="fa fa-spinner fa-spin"></i> Đang kết nối...');

    // Construct Google OAuth URL
    const googleAuthUrl = buildGoogleAuthUrl();

    // Open popup window
    const popup = window.open(
      googleAuthUrl,
      "googleLogin",
      "width=600,height=700,left=100,top=100",
    );

    // Check if popup was blocked
    if (!popup || popup.closed || typeof popup.closed === "undefined") {
      $btn.prop("disabled", false);
      $btn.html(originalHTML);
      showToast("error", "Popup bị chặn. Vui lòng cho phép popup và thử lại.");
      return;
    }

    // Poll for popup closure
    const pollTimer = setInterval(function () {
      if (popup.closed) {
        clearInterval(pollTimer);
        $btn.prop("disabled", false);
        $btn.html(originalHTML);
      }
    }, 1000);

    // Listen for message from popup
    window.addEventListener("message", function (event) {
      if (event.origin !== window.location.origin) return;

      if (event.data.type === "google-login-success") {
        clearInterval(pollTimer);
        popup.close();
        handleSocialLoginSuccess(event.data);
      } else if (event.data.type === "google-login-error") {
        clearInterval(pollTimer);
        popup.close();
        $btn.prop("disabled", false);
        $btn.html(originalHTML);
        showToast("error", event.data.message || "Đăng nhập Google thất bại");
      }
    });
  }

  /**
   * Facebook OAuth Login
   */
  function handleFacebookLogin(e) {
    e.preventDefault();
    console.log("Facebook Login clicked");

    const $btn = $(this);
    const originalHTML = $btn.html();

    // Check if Facebook OAuth is configured
    if (typeof truyenqqAuth === "undefined" || !truyenqqAuth.facebook_app_id) {
      showToast(
        "error",
        "Đăng nhập Facebook chưa được cấu hình. Vui lòng liên hệ quản trị viên.",
      );
      console.error("Facebook App ID not configured");
      return;
    }

    // Show loading state
    $btn.prop("disabled", true);
    $btn.html('<i class="fa fa-spinner fa-spin"></i> Đang kết nối...');

    // Construct Facebook OAuth URL
    const facebookAuthUrl = buildFacebookAuthUrl();

    // Open popup window
    const popup = window.open(
      facebookAuthUrl,
      "facebookLogin",
      "width=600,height=700,left=100,top=100",
    );

    // Check if popup was blocked
    if (!popup || popup.closed || typeof popup.closed === "undefined") {
      $btn.prop("disabled", false);
      $btn.html(originalHTML);
      showToast("error", "Popup bị chặn. Vui lòng cho phép popup và thử lại.");
      return;
    }

    // Poll for popup closure
    const pollTimer = setInterval(function () {
      if (popup.closed) {
        clearInterval(pollTimer);
        $btn.prop("disabled", false);
        $btn.html(originalHTML);
      }
    }, 1000);

    // Listen for message from popup
    window.addEventListener("message", function (event) {
      if (event.origin !== window.location.origin) return;

      if (event.data.type === "facebook-login-success") {
        clearInterval(pollTimer);
        popup.close();
        handleSocialLoginSuccess(event.data);
      } else if (event.data.type === "facebook-login-error") {
        clearInterval(pollTimer);
        popup.close();
        $btn.prop("disabled", false);
        $btn.html(originalHTML);
        showToast("error", event.data.message || "Đăng nhập Facebook thất bại");
      }
    });
  }

  /**
   * Build Google OAuth URL
   */
  function buildGoogleAuthUrl() {
    const params = {
      client_id: truyenqqAuth.google_client_id,
      redirect_uri: truyenqqAuth.google_redirect_uri,
      response_type: "code",
      scope: "email profile",
      access_type: "online",
      prompt: "select_account",
    };

    const queryString = Object.keys(params)
      .map((key) => `${key}=${encodeURIComponent(params[key])}`)
      .join("&");

    return `https://accounts.google.com/o/oauth2/v2/auth?${queryString}`;
  }

  /**
   * Build Facebook OAuth URL
   */
  function buildFacebookAuthUrl() {
    const params = {
      client_id: truyenqqAuth.facebook_app_id,
      redirect_uri: truyenqqAuth.facebook_redirect_uri,
      scope: "email,public_profile",
      response_type: "code",
      state: generateRandomState(),
    };

    const queryString = Object.keys(params)
      .map((key) => `${key}=${encodeURIComponent(params[key])}`)
      .join("&");

    return `https://www.facebook.com/v18.0/dialog/oauth?${queryString}`;
  }

  /**
   * Handle successful social login
   */
  function handleSocialLoginSuccess(data) {
    console.log("Social login success:", data);

    showToast("success", "Đăng nhập thành công! Đang chuyển hướng...");

    // Redirect after short delay
    setTimeout(function () {
      window.location.href = data.redirect || window.location.href;
    }, 1000);
  }

  /**
   * Generate random state for CSRF protection
   */
  function generateRandomState() {
    const array = new Uint8Array(16);
    window.crypto.getRandomValues(array);
    return Array.from(array, (byte) => byte.toString(16).padStart(2, "0")).join(
      "",
    );
  }

  /**
   * Show toast notification
   */
  function showToast(type, message) {
    // Check if toast container exists
    let $container = $(".toast-container");
    if (!$container.length) {
      $container = $('<div class="toast-container"></div>');
      $("body").append($container);
    }

    // Create toast element
    const iconClass =
      type === "success" ? "fa-check-circle" : "fa-exclamation-circle";
    const $toast = $(
      `
      <div class="toast toast-${type}">
        <i class="fa ${iconClass}"></i>
        <span>${message}</span>
      </div>
    `,
    );

    // Add to container
    $container.append($toast);

    // Show toast
    setTimeout(function () {
      $toast.addClass("show");
    }, 100);

    // Remove after 5 seconds
    setTimeout(function () {
      $toast.removeClass("show");
      setTimeout(function () {
        $toast.remove();
      }, 300);
    }, 5000);
  }
})(jQuery);
