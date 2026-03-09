/**
 * Bookmarks Page (Theo Dõi) - JavaScript
 *
 * @package TruyenQQ
 * @version 1.1.1
 * - AUTH OFF: đọc/ghi từ localStorage
 * - AUTH ON : gọi API như cũ
 * - FIX: bookmark-badge đúng vị trí top-right, bỏ bookmark-info
 */

(function () {
  "use strict";

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

  function clearLocalStorage() {
    try {
      localStorage.removeItem(LS_KEY);
      return true;
    } catch (e) {
      return false;
    }
  }

  function deleteFromLocalStorage(postId) {
    try {
      const items = lsGetAll().filter(
        (i) => String(i.post_id) !== String(postId),
      );
      localStorage.setItem(LS_KEY, JSON.stringify(items));
      return true;
    } catch (e) {
      return false;
    }
  }

  // ========================================
  // INIT
  // ========================================

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    const page = document.querySelector(".bookmarks-page");
    if (!page) return;

    console.log("📌 Bookmarks page init. AUTH_ENABLED:", AUTH_ENABLED);

    if (AUTH_ENABLED) {
      loadBookmarksFromAPI();
    } else {
      loadBookmarksFromLocalStorage();
    }

    initClearAllButton();
  }

  // ========================================
  // LOAD DATA
  // ========================================

  function loadBookmarksFromLocalStorage() {
    const loading = document.getElementById("bookmarks-loading");
    const empty = document.getElementById("bookmarks-empty");
    const grid = document.getElementById("bookmarks-grid");

    loading.style.display = "block";
    empty.style.display = "none";
    grid.style.display = "none";

    try {
      const items = lsGetAll();
      loading.style.display = "none";

      if (!items || items.length === 0) {
        showEmptyState(empty);
        return;
      }

      items.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      items.forEach((item) => {
        item.time_ago = computeTimeAgo(item.updated_at || item.created_at);
      });

      renderBookmarks(items, true);
      grid.style.display = "grid";
    } catch (err) {
      loading.style.display = "none";
      showErrorState(
        document.getElementById("bookmarks-empty"),
        "Không thể đọc dữ liệu",
      );
    }
  }

  async function loadBookmarksFromAPI() {
    const loading = document.getElementById("bookmarks-loading");
    const empty = document.getElementById("bookmarks-empty");
    const grid = document.getElementById("bookmarks-grid");

    loading.style.display = "block";
    empty.style.display = "none";
    grid.style.display = "none";

    try {
      const response = await fetch(`${API_BASE}/bookmarks`, {
        credentials: "same-origin",
        headers: { "X-WP-Nonce": getNonce() },
      });

      if (!response.ok) throw new Error(`HTTP ${response.status}`);

      const result = await response.json();
      loading.style.display = "none";

      if (!result.success || !result.data || result.data.length === 0) {
        showEmptyState(empty);
        return;
      }

      renderBookmarks(result.data, false);
      grid.style.display = "grid";
    } catch (error) {
      loading.style.display = "none";
      showErrorState(document.getElementById("bookmarks-empty"), error.message);
    }
  }

  // ========================================
  // RENDER
  // ========================================

  function renderBookmarks(items, isLocalStorage) {
    const grid = document.getElementById("bookmarks-grid");
    grid.innerHTML = "";

    items.forEach((item) => {
      const card = createBookmarkCard(item);
      grid.appendChild(card);
    });

    initRemoveButtons(isLocalStorage);

    if (window.TruyenqqBookmarks) {
      window.TruyenqqBookmarks.init();
    }
  }

  /**
   * Card structure giống hệt homepage-exclusive:
   * - bookmark-badge là sibling của top-notice (không nằm trong top-notice)
   * - remove-bookmark ở top-right, ẩn mặc định, hiện khi hover
   * - KHÔNG có bookmark-info
   */
  function createBookmarkCard(item) {
    const div = document.createElement("div");
    div.className = "comic-item";
    div.setAttribute("data-post-id", item.post_id);

    const thumbnail =
      item.thumbnail || "https://via.placeholder.com/190x247?text=No+Image";

    div.innerHTML = `
      <div class="comic-card">
        <div class="comic-avatar">
          <a href="${item.post_url}" title="${escapeHtml(item.post_title)}">
            <img src="${thumbnail}" alt="${escapeHtml(item.post_title)}" loading="lazy">
          </a>

          <!-- ✅ bookmark-badge: sibling của top-notice, nằm top-right giống homepage -->
          <span class="bookmark-badge active" data-post-id="${item.post_id}" title="Đã theo dõi">
            <i class="fa fa-bookmark"></i>
          </span>

          <!-- remove button: ẩn mặc định, hiện khi hover -->
          <span class="remove-bookmark" title="Bỏ theo dõi" data-post-id="${item.post_id}">
            <i class="fa fa-times-circle-o"></i>
          </span>

          <div class="top-notice">
            <span class="time-ago">${item.time_ago || ""}</span>
          </div>
        </div>

        <div class="comic-info">
          <h3 class="comic-name">
            <a href="${item.post_url}" title="${escapeHtml(item.post_title)}">
              ${escapeHtml(item.post_title)}
            </a>
          </h3>
          <div class="comic-stats">
            <span class="stat-item">
              <i class="fa fa-bookmark"></i>
              ${formatNumber(item.bookmark_count)}
            </span>
            <span class="stat-item">
              <i class="fa fa-eye"></i>
              ${formatNumber(item.view_count)}
            </span>
          </div>
          <div class="latest-chapter">
            <a href="${item.post_url}" title="Đọc ${escapeHtml(item.latest_chapter || "")}">
              ${escapeHtml(item.latest_chapter || "Đang cập nhật")}
            </a>
          </div>
        </div>
      </div>
    `;

    return div;
  }

  // ========================================
  // REMOVE BUTTONS
  // ========================================

  function initRemoveButtons(isLocalStorage) {
    const removeButtons = document.querySelectorAll(".remove-bookmark");

    removeButtons.forEach((btn) => {
      btn.addEventListener("click", async function (e) {
        e.preventDefault();
        e.stopPropagation();

        const postId = this.getAttribute("data-post-id");
        const card = this.closest(".comic-item");

        if (!confirm("Bạn có chắc muốn bỏ theo dõi truyện này?")) return;

        let success = false;

        if (isLocalStorage) {
          success = deleteFromLocalStorage(postId);
        } else {
          try {
            const response = await fetch(`${API_BASE}/bookmarks/${postId}`, {
              method: "DELETE",
              credentials: "same-origin",
              headers: { "X-WP-Nonce": getNonce() },
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const result = await response.json();
            success = result.success;
            if (!success) showToast(result.message || "Xóa thất bại", "error");
          } catch (err) {
            showToast("Lỗi bỏ theo dõi", "error");
            return;
          }
        }

        if (success) {
          card.style.opacity = "0";
          card.style.transform = "scale(0.9)";
          setTimeout(() => {
            card.remove();
            const grid = document.getElementById("bookmarks-grid");
            if (grid.children.length === 0) {
              grid.style.display = "none";
              showEmptyState(document.getElementById("bookmarks-empty"));
            }
          }, 300);
          showToast("Đã bỏ theo dõi", "success");
        }
      });
    });
  }

  // ========================================
  // CLEAR ALL
  // ========================================

  function initClearAllButton() {
    const btn = document.getElementById("clear-all-bookmarks");
    if (!btn) return;

    btn.addEventListener("click", async function () {
      if (!confirm("Bạn có chắc muốn bỏ theo dõi TẤT CẢ truyện?")) return;

      const loading = document.getElementById("bookmarks-loading");
      const grid = document.getElementById("bookmarks-grid");
      const empty = document.getElementById("bookmarks-empty");

      loading.style.display = "block";
      grid.style.display = "none";

      let success = false;

      if (!AUTH_ENABLED) {
        success = clearLocalStorage();
        loading.style.display = "none";
      } else {
        try {
          const response = await fetch(`${API_BASE}/bookmarks/clear-all`, {
            method: "POST",
            credentials: "same-origin",
            headers: { "X-WP-Nonce": getNonce() },
          });
          if (!response.ok) throw new Error(`HTTP ${response.status}`);
          const result = await response.json();
          loading.style.display = "none";
          success = result.success;
          if (!success) {
            showToast(result.message || "Xóa thất bại", "error");
            grid.style.display = "grid";
          }
        } catch (err) {
          loading.style.display = "none";
          grid.style.display = "grid";
          showToast("Lỗi xóa truyện theo dõi", "error");
          return;
        }
      }

      if (success) {
        grid.innerHTML = "";
        grid.style.display = "none";
        empty.innerHTML = `
          <i class="fa fa-bookmark-o"></i>
          <h3>Đã xóa tất cả truyện theo dõi</h3>
          <p>Bắt đầu theo dõi truyện yêu thích của bạn ngay!</p>
          <a href="${window.location.origin}" class="btn btn-primary">
            <i class="fa fa-home"></i> Về Trang Chủ
          </a>
        `;
        empty.style.display = "block";
        showToast("Đã xóa tất cả", "success");
      }
    });
  }

  // ========================================
  // STATE HELPERS
  // ========================================

  function showEmptyState(el) {
    el.innerHTML = `
      <i class="fa fa-bookmark-o"></i>
      <h3>Chưa có truyện theo dõi</h3>
      <p>Bắt đầu theo dõi truyện yêu thích của bạn ngay!</p>
      <a href="${window.location.origin}" class="btn btn-primary">
        <i class="fa fa-home"></i> Về Trang Chủ
      </a>
    `;
    el.style.display = "block";
  }

  function showErrorState(el, message) {
    el.innerHTML = `
      <i class="fa fa-exclamation-triangle"></i>
      <h3>Không thể tải truyện theo dõi</h3>
      <p style="color: #e74c3c; margin: 10px 0;">${message}</p>
      <a href="${window.location.href}" class="btn btn-primary">
        <i class="fa fa-refresh"></i> Thử Lại
      </a>
    `;
    el.style.display = "block";
  }

  // ========================================
  // UTILITIES
  // ========================================

  function computeTimeAgo(dateStr) {
    if (!dateStr) return "";
    const past = new Date(dateStr.replace(" ", "T"));
    const now = new Date();
    const seconds = Math.floor((now - past) / 1000);
    if (seconds < 60) return "Vừa xong";
    if (seconds < 3600) return Math.floor(seconds / 60) + " phút trước";
    if (seconds < 86400) return Math.floor(seconds / 3600) + " giờ trước";
    if (seconds < 2592000) return Math.floor(seconds / 86400) + " ngày trước";
    if (seconds < 31536000)
      return Math.floor(seconds / 2592000) + " tháng trước";
    return Math.floor(seconds / 31536000) + " năm trước";
  }

  function formatNumber(num) {
    if (!num) return "0";
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  function escapeHtml(text) {
    if (!text) return "";
    const map = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };
    return text.replace(/[&<>"']/g, (m) => map[m]);
  }

  function showToast(message, type = "info") {
    let container = document.querySelector(".toast-container");
    if (!container) {
      container = document.createElement("div");
      container.className = "toast-container";
      document.body.appendChild(container);
    }
    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
      <i class="fa fa-${type === "success" ? "check-circle" : "exclamation-circle"}"></i>
      ${message}
    `;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add("show"), 10);
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  init();
})();
