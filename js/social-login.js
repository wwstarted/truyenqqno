/**
 * Social Login Handler - Google & Facebook OAuth
 * COMPLETE VERSION with Error Handling & UI Feedback
 *
 * @package TruyenQQ
 * @version 1.0.1
 */

(function ($) {
  "use strict";

  let popupWindow = null;
  let pollTimer = null;

  $(document).ready(function () {
    console.log("TruyenQQ Social Login: Initializing...");
    initSocialLogin();
  });

  function initSocialLogin() {
    // Google Login
    $("#login-google, #register-google").on("click", handleGoogleLogin);

    // Facebook Login
    $("#login-facebook, #register-facebook").on("click", handleFacebookLogin);

    // Listen for messages from popup
    window.addEventListener("message", handleOAuthMessage);

    console.log("TruyenQQ Social Login: Ready");
  }

  /**
   * Handle Google OAuth Login
   */
  function handleGoogleLogin(e) {
    e.preventDefault();
    console.log("Google Login initiated");

    const $btn = $(this);
    const originalHTML = $btn.html();

    // Validate configuration
    if (
      typeof truyenqqOAuth === "undefined" ||
      !truyenqqOAuth.google_client_id
    ) {
      showMessage(
        "error",
        "Đăng nhập Google chưa được cấu hình. Vui lòng liên hệ quản trị viên.",
      );
      console.error("Google OAuth not configured");
      return;
    }

    // Show loading state
    setButtonLoading($btn, true, "Đang kết nối Google...");

    // Build OAuth URL
    const authUrl = buildGoogleAuthUrl();
    console.log("Opening Google OAuth popup...");

    // Open popup
    popupWindow = openOAuthPopup(authUrl, "googleLogin");

    if (!popupWindow) {
      setButtonLoading($btn, false, originalHTML);
      showMessage(
        "error",
        "Popup bị chặn. Vui lòng cho phép popup và thử lại.",
      );
      return;
    }

    // Poll popup status
    startPopupPolling($btn, originalHTML);
  }

  /**
   * Handle Facebook OAuth Login
   */
  function handleFacebookLogin(e) {
    e.preventDefault();
    console.log("Facebook Login initiated");

    const $btn = $(this);
    const originalHTML = $btn.html();

    // Validate configuration
    if (
      typeof truyenqqOAuth === "undefined" ||
      !truyenqqOAuth.facebook_app_id
    ) {
      showMessage(
        "error",
        "Đăng nhập Facebook chưa được cấu hình. Vui lòng liên hệ quản trị viên.",
      );
      console.error("Facebook OAuth not configured");
      return;
    }

    // Show loading state
    setButtonLoading($btn, true, "Đang kết nối Facebook...");

    // Build OAuth URL
    const authUrl = buildFacebookAuthUrl();
    console.log("Opening Facebook OAuth popup...");

    // Open popup
    popupWindow = openOAuthPopup(authUrl, "facebookLogin");

    if (!popupWindow) {
      setButtonLoading($btn, false, originalHTML);
      showMessage(
        "error",
        "Popup bị chặn. Vui lòng cho phép popup và thử lại.",
      );
      return;
    }

    // Poll popup status
    startPopupPolling($btn, originalHTML);
  }

  /**
   * Build Google OAuth URL
   */
  function buildGoogleAuthUrl() {
    const params = {
      client_id: truyenqqOAuth.google_client_id,
      redirect_uri: truyenqqOAuth.google_redirect_uri,
      response_type: "code",
      scope: "openid email profile",
      access_type: "online",
      prompt: "select_account",
      state: generateRandomState(),
    };

    return (
      "https://accounts.google.com/o/oauth2/v2/auth?" +
      new URLSearchParams(params).toString()
    );
  }

  /**
   * Build Facebook OAuth URL
   */
  function buildFacebookAuthUrl() {
    const params = {
      client_id: truyenqqOAuth.facebook_app_id,
      redirect_uri: truyenqqOAuth.facebook_redirect_uri,
      scope: "email,public_profile",
      response_type: "code",
      state: generateRandomState(),
    };

    return (
      "https://www.facebook.com/v18.0/dialog/oauth?" +
      new URLSearchParams(params).toString()
    );
  }

  /**
   * Open OAuth popup window
   */
  function openOAuthPopup(url, name) {
    const width = 600;
    const height = 700;
    const left = window.screen.width / 2 - width / 2;
    const top = window.screen.height / 2 - height / 2;

    const features =
      `width=${width},height=${height},left=${left},top=${top},` +
      `toolbar=no,menubar=no,scrollbars=yes,resizable=yes,status=no`;

    const popup = window.open(url, name, features);

    // Check if popup was blocked
    if (!popup || popup.closed || typeof popup.closed === "undefined") {
      console.error("Popup blocked by browser");
      return null;
    }

    // Focus popup
    if (popup.focus) {
      popup.focus();
    }

    return popup;
  }

  /**
   * Start polling popup status
   */
  function startPopupPolling($btn, originalHTML) {
    pollTimer = setInterval(function () {
      if (!popupWindow || popupWindow.closed) {
        console.log("Popup closed by user");
        clearInterval(pollTimer);
        setButtonLoading($btn, false, originalHTML);
        popupWindow = null;
      }
    }, 500);
  }

  /**
   * Handle messages from OAuth popup
   */
  function handleOAuthMessage(event) {
    // Verify origin
    if (event.origin !== window.location.origin) {
      console.warn("Message from unauthorized origin:", event.origin);
      return;
    }

    const data = event.data;

    if (!data || !data.type) {
      return;
    }

    console.log("OAuth message received:", data.type);

    // Clear polling
    if (pollTimer) {
      clearInterval(pollTimer);
      pollTimer = null;
    }

    // Handle success
    if (
      data.type === "google-login-success" ||
      data.type === "facebook-login-success"
    ) {
      handleOAuthSuccess(data);
    }

    // Handle error
    else if (
      data.type === "google-login-error" ||
      data.type === "facebook-login-error"
    ) {
      handleOAuthError(data);
    }
  }

  /**
   * Handle successful OAuth login
   */
  function handleOAuthSuccess(data) {
    console.log("OAuth login successful");

    // Close popup if still open
    if (popupWindow && !popupWindow.closed) {
      popupWindow.close();
    }

    // Show success message
    showMessage("success", "Đăng nhập thành công! Đang chuyển hướng...");

    // Redirect after short delay
    setTimeout(function () {
      const redirectUrl = data.redirect || window.location.href;
      window.location.href = redirectUrl;
    }, 1000);
  }

  /**
   * Handle OAuth error
   */
  function handleOAuthError(data) {
    console.error("OAuth login error:", data.message);

    // Close popup if still open
    if (popupWindow && !popupWindow.closed) {
      popupWindow.close();
    }

    // Reset all buttons
    $(".btn-social").each(function () {
      const $btn = $(this);
      const isGoogle = $btn.attr("id").includes("google");
      const originalText = isGoogle ? "Google" : "Facebook";
      const icon = isGoogle ? "fab fa-google" : "fab fa-facebook-f";
      setButtonLoading($btn, false, `<i class="${icon}"></i> ${originalText}`);
    });

    // Show error message
    const errorMessage =
      data.message || "Đăng nhập thất bại. Vui lòng thử lại.";
    showMessage("error", errorMessage);
  }

  /**
   * Set button loading state
   */
  function setButtonLoading($btn, loading, content) {
    if (loading) {
      $btn.prop("disabled", true);
      $btn.html(
        '<i class="fa fa-spinner fa-spin"></i> ' + (content || "Đang xử lý..."),
      );
    } else {
      $btn.prop("disabled", false);
      $btn.html(content);
    }
  }

  /**
   * Show message (toast notification)
   */
  function showMessage(type, message) {
    // Try to use form message if available
    const $formMessage = $(".form-message");
    if ($formMessage.length) {
      const iconClass =
        type === "success" ? "fa-check-circle" : "fa-exclamation-circle";
      $formMessage
        .removeClass("success error show")
        .addClass(type + " show")
        .html(`<i class="fa ${iconClass}"></i> ${message}`);

      setTimeout(function () {
        $formMessage.removeClass("show");
      }, 5000);

      return;
    }

    // Otherwise create toast
    showToast(type, message);
  }

  /**
   * Show toast notification
   */
  function showToast(type, message) {
    // Ensure toast container exists
    let $container = $(".toast-container");
    if (!$container.length) {
      $container = $('<div class="toast-container"></div>');
      $("body").append($container);
    }

    // Create toast
    const iconClass =
      type === "success" ? "fa-check-circle" : "fa-exclamation-circle";
    const $toast = $(`
      <div class="toast toast-${type}">
        <i class="fa ${iconClass}"></i>
        <span>${message}</span>
      </div>
    `);

    // Add to container
    $container.append($toast);

    // Show toast
    setTimeout(function () {
      $toast.addClass("show");
    }, 100);

    // Auto hide after 5 seconds
    setTimeout(function () {
      $toast.removeClass("show");
      setTimeout(function () {
        $toast.remove();
      }, 300);
    }, 5000);
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

  // Clean up on page unload
  $(window).on("beforeunload", function () {
    if (pollTimer) {
      clearInterval(pollTimer);
    }
    if (popupWindow && !popupWindow.closed) {
      popupWindow.close();
    }
  });
})(jQuery);
