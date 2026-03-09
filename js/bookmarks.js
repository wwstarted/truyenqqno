/**
 * Bookmarks Global JS
 *
 * @package TruyenQQ
 * @version 1.1.0
 * - AUTH OFF: toggle localStorage, check state từ localStorage
 * - AUTH ON : gọi API như cũ
 */

(function () {
  "use strict";

  // ========================================
  // CONFIG
  // ========================================
  const AUTH_ENABLED =
    typeof truyenqqConfig !== "undefined" &&
    truyenqqConfig.authEnabled === true;

  const LS_KEY = "truyenqq_bookmarks";

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

  function getNonce() {
    if (typeof truyenqqConfig !== "undefined" && truyenqqConfig.nonce) {
      return truyenqqConfig.nonce;
    }
    return typeof wpApiSettings !== "undefined" ? wpApiSettings.nonce : "";
  }

  // ========================================
  // LOCALSTORAGE HELPERS
  // ========================================

  function lsGetAll() {
    try {
      const raw = localStorage.getItem(LS_KEY);
      return raw ? JSON.parse(raw) : [];
    } catch (e) {
      return [];
    }
  }

  function lsSave(items) {
    try {
      localStorage.setItem(LS_KEY, JSON.stringify(items));
    } catch (e) {
      console.error("localStorage save error:", e);
    }
  }

  function lsIsBookmarked(postId) {
    const items = lsGetAll();
    return items.some((item) => String(item.post_id) === String(postId));
  }

  /**
   * Thêm bookmark vào localStorage
   * Đọc data từ DOM context của button
   */
  function lsAddBookmark(postId, button) {
    const items = lsGetAll();

    // Tránh duplicate
    if (items.some((i) => String(i.post_id) === String(postId))) return;

    // Đọc data từ DOM (comic card context)
    const card = button.closest(".comic-card") || button.closest(".comic-item");

    let postTitle = "";
    let postUrl = "";
    let thumbnail = "";
    let latestChapter = "Đang cập nhật";
    let followCount = 0;
    let viewCount = 0;

    if (card) {
      const titleEl = card.querySelector(".comic-name a, h3 a");
      if (titleEl) {
        postTitle = titleEl.textContent.trim();
        postUrl = titleEl.href || "";
      }

      const imgEl = card.querySelector(".comic-avatar img");
      if (imgEl) {
        thumbnail = imgEl.src || "";
      }

      const chapterEl = card.querySelector(".latest-chapter a");
      if (chapterEl) {
        latestChapter = chapterEl.textContent.trim();
      }

      // Đọc stats nếu có
      const statItems = card.querySelectorAll(".stat-item");
      if (statItems.length >= 2) {
        followCount =
          parseInt(statItems[0].textContent.replace(/\D/g, "")) || 0;
        viewCount = parseInt(statItems[1].textContent.replace(/\D/g, "")) || 0;
      }
    }

    // Fallback: lấy từ current page nếu đang ở trang truyện
    if (!postUrl) {
      postUrl = window.location.href.split("?")[0];
    }

    const now = new Date().toISOString().replace("T", " ").substring(0, 19);

    items.push({
      id: Date.now(), // unique id cho item
      post_id: parseInt(postId),
      post_title: postTitle,
      post_url: postUrl,
      thumbnail: thumbnail,
      latest_chapter: latestChapter,
      follow_count: followCount,
      bookmark_count: 0,
      view_count: viewCount,
      time_ago: "Vừa xong",
      created_at: now,
      updated_at: now,
    });

    lsSave(items);
  }

  function lsRemoveBookmark(postId) {
    const items = lsGetAll().filter(
      (i) => String(i.post_id) !== String(postId),
    );
    lsSave(items);
  }

  // ========================================
  // INIT
  // ========================================

  function init() {
    if (isInitialized) return;

    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    console.log(
      "📌 Bookmark system initializing... AUTH_ENABLED:",
      AUTH_ENABLED,
    );
    initBookmarkButtons();
    checkBookmarkStates();
    isInitialized = true;
  }

  function initBookmarkButtons() {
    const buttons = document.querySelectorAll(".bookmark-badge");

    buttons.forEach((button) => {
      const newButton = button.cloneNode(true);
      button.parentNode.replaceChild(newButton, button);
      newButton.addEventListener("click", handleBookmarkClick);
    });

    console.log(`📌 Initialized ${buttons.length} bookmark buttons`);
  }

  // ========================================
  // CHECK STATES
  // ========================================

  async function checkBookmarkStates() {
    const buttons = document.querySelectorAll(".bookmark-badge");
    if (buttons.length === 0) return;

    // AUTH OFF → đọc từ localStorage
    if (!AUTH_ENABLED) {
      buttons.forEach((button) => {
        const postId = button.getAttribute("data-post-id");
        if (postId) {
          const bookmarked = lsIsBookmarked(postId);
          updateBookmarkButton(postId, bookmarked, false);
        }
      });
      console.log("✅ Bookmark states loaded from localStorage");
      return;
    }

    // AUTH ON nhưng chưa login → bỏ qua
    if (!truyenqqConfig.isLoggedIn) {
      console.log("👤 User not logged in, skipping bookmark check");
      return;
    }

    // AUTH ON + đã login → gọi API
    const postIds = [];
    buttons.forEach((button) => {
      const postId = parseInt(button.getAttribute("data-post-id"));
      if (postId && !isNaN(postId)) postIds.push(postId);
    });

    if (postIds.length === 0) return;

    console.log(`🔍 Checking bookmark status for ${postIds.length} posts...`);

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
          updateBookmarkButton(postId, isBookmarked, false);
        });
        console.log("✅ Bookmark states updated from API");
      }
    } catch (error) {
      console.error("❌ Error checking bookmarks:", error);
    }
  }

  // ========================================
  // HANDLE CLICK
  // ========================================

  async function handleBookmarkClick(e) {
    e.preventDefault();
    e.stopPropagation();

    const button = this;
    const postId = button.getAttribute("data-post-id");

    if (!postId) {
      showToast("Lỗi: Không tìm thấy ID truyện", "error");
      return;
    }

    // ── AUTH OFF: dùng localStorage ──────────────────────────────
    if (!AUTH_ENABLED) {
      const isBookmarked = lsIsBookmarked(postId);

      if (isBookmarked) {
        lsRemoveBookmark(postId);
        updateBookmarkButton(postId, false, true);
        showToast("Đã bỏ theo dõi", "success");
      } else {
        lsAddBookmark(postId, button);
        updateBookmarkButton(postId, true, true);
        showToast("Đã thêm vào theo dõi", "success");
      }
      return;
    }

    // ── AUTH ON nhưng chưa login: redirect ──────────────────────
    if (!truyenqqConfig.isLoggedIn) {
      showToast("Vui lòng đăng nhập để theo dõi truyện", "info");
      setTimeout(() => {
        window.location.href = "/dang-nhap";
      }, 1500);
      return;
    }

    // ── AUTH ON + đã login: gọi API ──────────────────────────────
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

      if (!response.ok) throw new Error(`HTTP ${response.status}`);

      const result = await response.json();

      if (result.guest_mode) {
        showToast(result.message, "info");
        setTimeout(() => {
          window.location.href = "/dang-nhap";
        }, 1500);
        return;
      }

      if (result.success) {
        updateBookmarkButton(postId, result.bookmarked, true);
        showToast(result.message, "success");

        if (result.bookmark_count !== undefined) {
          updateBookmarkCount(postId, result.bookmark_count);
        }
      } else {
        showToast(result.message || "Lỗi khi thao tác", "error");
      }
    } catch (error) {
      console.error("Bookmark error:", error);
      showToast("Lỗi kết nối. Vui lòng thử lại", "error");
    } finally {
      button.style.pointerEvents = "";
      button.style.opacity = "";
    }
  }

  // ========================================
  // UI HELPERS
  // ========================================

  function updateBookmarkButton(postId, bookmarked, animate = false) {
    const buttons = document.querySelectorAll(
      `.bookmark-badge[data-post-id="${postId}"]`,
    );

    buttons.forEach((button) => {
      const icon = button.querySelector("i");
      if (!icon) return;

      if (animate) {
        button.style.transform = "scale(1.2)";
        setTimeout(() => {
          button.style.transform = "";
        }, 200);
      }

      if (bookmarked) {
        button.classList.add("active");
        icon.className = "fa fa-bookmark";
        button.setAttribute("title", "Bỏ theo dõi");
        button.style.backgroundColor = "#ff6b6b";
        button.style.color = "#fff";
      } else {
        button.classList.remove("active");
        icon.className = "fa fa-bookmark-o";
        button.setAttribute("title", "Theo dõi");
        button.style.backgroundColor = "";
        button.style.color = "";
      }
    });
  }

  function updateBookmarkCount(postId, count) {
    const countElements = document.querySelectorAll(
      `[data-bookmark-count="${postId}"]`,
    );
    countElements.forEach((el) => {
      el.textContent = formatNumber(count);
    });
  }

  function formatNumber(num) {
    if (!num) return "0";
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  function showToast(message, type = "info") {
    if (window.TruyenqqToast && window.TruyenqqToast.show) {
      window.TruyenqqToast.show(message, type);
      return;
    }
    // Fallback nếu toast utility chưa load
    let container = document.querySelector(".toast-container");
    if (!container) {
      container = document.createElement("div");
      container.className = "toast-container";
      document.body.appendChild(container);
    }
    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<i class="fa fa-${type === "success" ? "check-circle" : "info-circle"}"></i> ${message}`;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add("show"), 10);
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 300);
    }, 2500);
  }

  // ========================================
  // PUBLIC API
  // ========================================

  window.TruyenqqBookmarks = {
    init: initBookmarkButtons,
    check: checkBookmarkStates,
    refresh: function () {
      isInitialized = false;
      init();
    },
  };

  init();

  document.addEventListener("visibilitychange", function () {
    if (!document.hidden) {
      checkBookmarkStates();
    }
  });
})();
