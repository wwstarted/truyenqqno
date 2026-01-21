/**
 * Global Bookmark Handler (FIXED - NO DUPLICATE TOAST)
 * ✅ Fixed: Removed duplicate showToast
 * ✅ Fixed: Use global toast function only
 *
 * @package TruyenQQ
 * @version 1.0.2
 */

(function () {
  "use strict";

  /**
   * Get API base URL
   */
  function getApiBase() {
    if (typeof truyenqqConfig !== "undefined" && truyenqqConfig.apiBase) {
      return truyenqqConfig.apiBase;
    }

    const origin = window.location.origin;
    const pathname = window.location.pathname;

    let wpRoot = "/";
    if (pathname.includes("/wordpress/")) {
      wpRoot = pathname.substring(0, pathname.indexOf("/wordpress/") + 11);
    }

    return origin + wpRoot + "wp-json/nettruyen/v1";
  }

  const API_BASE = getApiBase();
  let isInitialized = false;

  /**
   * Get REST nonce
   */
  function getNonce() {
    if (typeof truyenqqConfig !== "undefined" && truyenqqConfig.nonce) {
      return truyenqqConfig.nonce;
    }
    return typeof wpApiSettings !== "undefined" ? wpApiSettings.nonce : "";
  }

  /**
   * Initialize bookmark buttons
   */
  function init() {
    if (isInitialized) return;

    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    console.log("📌 Bookmark system initializing...");
    initBookmarkButtons();
    checkBookmarkStates();
    isInitialized = true;
  }

  /**
   * Initialize all bookmark buttons on page
   */
  function initBookmarkButtons() {
    const buttons = document.querySelectorAll(".bookmark-badge");

    buttons.forEach((button) => {
      // Remove old listeners
      const newButton = button.cloneNode(true);
      button.parentNode.replaceChild(newButton, button);

      // Add new listener
      newButton.addEventListener("click", handleBookmarkClick);
    });

    console.log(`📌 Initialized ${buttons.length} bookmark buttons`);
  }

  /**
   * Check bookmark states for all comics on page
   */
  async function checkBookmarkStates() {
    // Only check if user is logged in
    if (typeof truyenqqConfig === "undefined" || !truyenqqConfig.isLoggedIn) {
      console.log("👤 User not logged in, skipping bookmark check");
      return;
    }

    const buttons = document.querySelectorAll(".bookmark-badge");
    const postIds = [];

    buttons.forEach((button) => {
      const postId = parseInt(button.getAttribute("data-post-id"));
      if (postId && !isNaN(postId)) {
        postIds.push(postId);
      }
    });

    if (postIds.length === 0) {
      console.log("⚠️ No post IDs found on bookmark badges");
      return;
    }

    console.log(`🔍 Checking bookmark status for ${postIds.length} posts...`);

    // Batch check all posts
    try {
      const response = await fetch(`${API_BASE}/bookmarks/check-batch`, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": getNonce(),
        },
        body: JSON.stringify({ post_ids: postIds }),
      });

      const result = await response.json();

      if (result.success && result.bookmarks) {
        Object.entries(result.bookmarks).forEach(([postId, isBookmarked]) => {
          updateBookmarkButton(postId, isBookmarked, false); // false = no animation
        });
        console.log("✅ Bookmark states updated");
      }
    } catch (error) {
      console.error("❌ Error checking bookmarks:", error);
    }
  }

  /**
   * Handle bookmark button click
   */
  async function handleBookmarkClick(e) {
    e.preventDefault();
    e.stopPropagation();

    const button = this;
    const postId = button.getAttribute("data-post-id");

    if (!postId) {
      console.error("No post ID found on bookmark button");
      // ✅ USE GLOBAL TOAST
      if (window.TruyenqqToast && window.TruyenqqToast.show) {
        window.TruyenqqToast.show("Lỗi: Không tìm thấy ID truyện", "error");
      }
      return;
    }

    // Check if user is logged in
    if (typeof truyenqqConfig === "undefined" || !truyenqqConfig.isLoggedIn) {
      // ✅ USE GLOBAL TOAST
      if (window.TruyenqqToast && window.TruyenqqToast.show) {
        window.TruyenqqToast.show(
          "Vui lòng đăng nhập để theo dõi truyện",
          "info",
        );
      }
      setTimeout(() => {
        window.location.href = "/dang-nhap";
      }, 1500);
      return;
    }

    // Disable button during request
    button.style.pointerEvents = "none";
    button.style.opacity = "0.6";

    try {
      const response = await fetch(`${API_BASE}/bookmarks/toggle`, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": getNonce(),
        },
        body: JSON.stringify({ post_id: parseInt(postId) }),
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const result = await response.json();

      if (result.guest_mode) {
        // ✅ USE GLOBAL TOAST
        if (window.TruyenqqToast && window.TruyenqqToast.show) {
          window.TruyenqqToast.show(result.message, "info");
        }
        setTimeout(() => {
          window.location.href = "/dang-nhap";
        }, 1500);
        return;
      }

      if (result.success) {
        // Update UI with animation
        updateBookmarkButton(postId, result.bookmarked, true);

        // ✅ USE GLOBAL TOAST
        if (window.TruyenqqToast && window.TruyenqqToast.show) {
          window.TruyenqqToast.show(result.message, "success");
        }

        // Update count if available
        if (result.bookmark_count !== undefined) {
          updateBookmarkCount(postId, result.bookmark_count);
        }
      } else {
        // ✅ USE GLOBAL TOAST
        if (window.TruyenqqToast && window.TruyenqqToast.show) {
          window.TruyenqqToast.show(
            result.message || "Lỗi khi thao tác",
            "error",
          );
        }
      }
    } catch (error) {
      console.error("Bookmark error:", error);
      // ✅ USE GLOBAL TOAST
      if (window.TruyenqqToast && window.TruyenqqToast.show) {
        window.TruyenqqToast.show("Lỗi kết nối. Vui lòng thử lại", "error");
      }
    } finally {
      button.style.pointerEvents = "";
      button.style.opacity = "";
    }
  }

  /**
   * Update bookmark button UI
   * @param {number} postId - Post ID
   * @param {boolean} bookmarked - Is bookmarked
   * @param {boolean} animate - Apply animation
   */
  function updateBookmarkButton(postId, bookmarked, animate = false) {
    const buttons = document.querySelectorAll(
      `.bookmark-badge[data-post-id="${postId}"]`,
    );

    buttons.forEach((button) => {
      const icon = button.querySelector("i");
      if (!icon) return;

      // Apply animation
      if (animate) {
        button.style.transform = "scale(1.2)";
        setTimeout(() => {
          button.style.transform = "";
        }, 200);
      }

      if (bookmarked) {
        // ACTIVE STATE: Đã theo dõi
        button.classList.add("active");
        icon.className = "fa fa-bookmark"; // Solid bookmark
        button.setAttribute("title", "Bỏ theo dõi");

        // Change color/background
        button.style.backgroundColor = "#ff6b6b"; // Red background
        button.style.color = "#fff";
      } else {
        // INACTIVE STATE: Chưa theo dõi
        button.classList.remove("active");
        icon.className = "fa fa-bookmark-o"; // Outline bookmark
        button.setAttribute("title", "Theo dõi");

        // Reset to default
        button.style.backgroundColor = "";
        button.style.color = "";
      }
    });
  }

  /**
   * Update bookmark count display
   */
  function updateBookmarkCount(postId, count) {
    // Find any bookmark count displays and update them
    const countElements = document.querySelectorAll(
      `[data-bookmark-count="${postId}"]`,
    );
    countElements.forEach((el) => {
      el.textContent = formatNumber(count);
    });
    console.log(`Post ${postId} now has ${count} bookmarks`);
  }

  /**
   * Format number with commas
   */
  function formatNumber(num) {
    if (!num) return "0";
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  // Public API
  window.TruyenqqBookmarks = {
    init: initBookmarkButtons,
    check: checkBookmarkStates,
    refresh: function () {
      isInitialized = false;
      init();
    },
  };

  // Auto-initialize
  init();

  // Re-check on page visibility change (user switches tabs)
  document.addEventListener("visibilitychange", function () {
    if (!document.hidden) {
      checkBookmarkStates();
    }
  });
})();
