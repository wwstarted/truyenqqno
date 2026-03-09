/**
 * Reading History Page - JavaScript
 *
 * @package TruyenQQ
 * @version 1.1.0
 * - AUTH OFF: đọc/ghi từ localStorage
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

  const LS_KEY = "truyenqq_history"; // localStorage key

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
  // INIT
  // ========================================
  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    const page = document.querySelector(".reading-history-page");
    if (!page) return;

    console.log("📍 Auth Enabled:", AUTH_ENABLED);
    console.log("📍 API Base:", API_BASE);

    if (AUTH_ENABLED) {
      loadHistoryFromAPI();
    } else {
      loadHistoryFromLocalStorage();
    }

    initClearAllButton();
  }

  // ========================================
  // LOCALSTORAGE MODE (AUTH OFF)
  // ========================================

  /**
   * Đọc lịch sử từ localStorage
   */
  function loadHistoryFromLocalStorage() {
    const loading = document.getElementById("history-loading");
    const empty = document.getElementById("history-empty");
    const grid = document.getElementById("history-grid");

    loading.style.display = "block";
    empty.style.display = "none";
    grid.style.display = "none";

    try {
      const raw = localStorage.getItem(LS_KEY);
      const items = raw ? JSON.parse(raw) : [];

      loading.style.display = "none";

      if (!items || items.length === 0) {
        showEmptyState(empty, "history");
        return;
      }

      // Sắp xếp mới nhất trước, recalculate time_ago
      items.sort((a, b) => new Date(b.last_read_at) - new Date(a.last_read_at));
      items.forEach((item) => {
        item.time_ago = computeTimeAgo(item.last_read_at);
      });

      renderHistory(items, true); // true = localStorage mode
      grid.style.display = "grid";
    } catch (err) {
      console.error("localStorage read error:", err);
      const loading = document.getElementById("history-loading");
      loading.style.display = "none";
      showErrorState(
        document.getElementById("history-empty"),
        "Không thể đọc lịch sử",
        "history",
      );
    }
  }

  /**
   * Xóa 1 item khỏi localStorage
   */
  function deleteFromLocalStorage(id) {
    try {
      const raw = localStorage.getItem(LS_KEY);
      let items = raw ? JSON.parse(raw) : [];
      items = items.filter((item) => String(item.id) !== String(id));
      localStorage.setItem(LS_KEY, JSON.stringify(items));
      return true;
    } catch (err) {
      console.error("localStorage delete error:", err);
      return false;
    }
  }

  /**
   * Xóa toàn bộ lịch sử khỏi localStorage
   */
  function clearLocalStorage() {
    try {
      localStorage.removeItem(LS_KEY);
      return true;
    } catch (err) {
      console.error("localStorage clear error:", err);
      return false;
    }
  }

  // ========================================
  // API MODE (AUTH ON)
  // ========================================

  async function loadHistoryFromAPI() {
    const loading = document.getElementById("history-loading");
    const empty = document.getElementById("history-empty");
    const grid = document.getElementById("history-grid");

    loading.style.display = "block";
    empty.style.display = "none";
    grid.style.display = "none";

    try {
      const response = await fetch(`${API_BASE}/reading-history`, {
        credentials: "same-origin",
        headers: { "X-WP-Nonce": getNonce() },
      });

      if (!response.ok) {
        throw new Error(
          `HTTP ${response.status}: ${response.statusText || "API Error"}`,
        );
      }

      const result = await response.json();
      loading.style.display = "none";

      if (!result.success || !result.data || result.data.length === 0) {
        showEmptyState(empty, "history");
        return;
      }

      renderHistory(result.data, false);
      grid.style.display = "grid";
    } catch (error) {
      console.error("Failed to load history:", error);
      loading.style.display = "none";
      showErrorState(
        document.getElementById("history-empty"),
        error.message,
        "history",
      );
    }
  }

  // ========================================
  // RENDER
  // ========================================

  function renderHistory(items, isLocalStorage) {
    const grid = document.getElementById("history-grid");
    grid.innerHTML = "";

    items.forEach((item) => {
      const card = createHistoryCard(item, isLocalStorage);
      grid.appendChild(card);
    });

    initDeleteButtons(isLocalStorage);
  }

  function createHistoryCard(item, isLocalStorage) {
    const div = document.createElement("div");
    div.className = "comic-item";
    div.setAttribute("data-history-id", item.id);

    // localStorage mode: không có current_page nên bỏ qua ?page=
    let continueUrl = item.post_url;
    if (item.chapter_slug) {
      continueUrl = `${item.post_url}?chapter=${item.chapter_slug}`;
      if (!isLocalStorage && item.current_page) {
        continueUrl += `&page=${item.current_page}`;
      }
    }

    const thumbnail =
      item.thumbnail || "https://via.placeholder.com/190x247?text=No+Image";

    div.innerHTML = `
      <div class="comic-card">
        <div class="comic-avatar">
          <a href="${item.post_url}" title="${escapeHtml(item.post_title)}">
            <img src="${thumbnail}" alt="${escapeHtml(item.post_title)}" loading="lazy">
          </a>
          <span class="remove-history" title="Xóa lịch sử" data-id="${item.id}">
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
              ${formatNumber(item.follow_count)}
            </span>
            <span class="stat-item">
              <i class="fa fa-eye"></i>
              ${formatNumber(item.view_count)}
            </span>
          </div>
          <div class="latest-chapter">
            <a href="${continueUrl}" title="Đọc tiếp ${item.chapter_name}">
              Đọc tiếp chương ${escapeHtml(String(item.chapter_name || ""))}
            </a>
          </div>
        </div>
      </div>
    `;

    return div;
  }

  // ========================================
  // DELETE BUTTONS
  // ========================================

  function initDeleteButtons(isLocalStorage) {
    const deleteButtons = document.querySelectorAll(".remove-history");

    deleteButtons.forEach((btn) => {
      btn.addEventListener("click", async function (e) {
        e.preventDefault();
        e.stopPropagation();

        const historyId = this.getAttribute("data-id");
        const card = this.closest(".comic-item");

        if (!confirm("Bạn có chắc muốn xóa lịch sử này?")) return;

        let success = false;

        if (isLocalStorage) {
          success = deleteFromLocalStorage(historyId);
        } else {
          try {
            const response = await fetch(
              `${API_BASE}/reading-history/${historyId}`,
              {
                method: "DELETE",
                credentials: "same-origin",
                headers: { "X-WP-Nonce": getNonce() },
              },
            );
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const result = await response.json();
            success = result.success;
            if (!success) showToast(result.message || "Xóa thất bại", "error");
          } catch (err) {
            console.error("Delete failed:", err);
            showToast("Lỗi xóa lịch sử", "error");
            return;
          }
        }

        if (success) {
          card.style.opacity = "0";
          card.style.transform = "scale(0.9)";
          setTimeout(() => {
            card.remove();
            const grid = document.getElementById("history-grid");
            if (grid.children.length === 0) {
              grid.style.display = "none";
              showEmptyState(
                document.getElementById("history-empty"),
                "history",
              );
            }
          }, 300);
          showToast("Đã xóa lịch sử", "success");
        }
      });
    });
  }

  // ========================================
  // CLEAR ALL
  // ========================================

  function initClearAllButton() {
    const btn = document.getElementById("clear-all-history");
    if (!btn) return;

    btn.addEventListener("click", async function () {
      if (!confirm("Bạn có chắc muốn xóa TẤT CẢ lịch sử đọc truyện?")) return;

      const loading = document.getElementById("history-loading");
      const grid = document.getElementById("history-grid");
      const empty = document.getElementById("history-empty");

      loading.style.display = "block";
      grid.style.display = "none";

      let success = false;

      if (!AUTH_ENABLED) {
        success = clearLocalStorage();
        loading.style.display = "none";
      } else {
        try {
          const response = await fetch(`${API_BASE}/reading-history/clear`, {
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
          console.error("Clear all failed:", err);
          loading.style.display = "none";
          grid.style.display = "grid";
          showToast("Lỗi xóa lịch sử", "error");
          return;
        }
      }

      if (success) {
        grid.innerHTML = "";
        grid.style.display = "none";
        empty.innerHTML = `
          <i class="fa fa-book"></i>
          <h3>Đã xóa tất cả lịch sử</h3>
          <p>Bắt đầu đọc truyện yêu thích của bạn ngay!</p>
          <a href="${window.location.origin}" class="btn btn-primary">
            <i class="fa fa-home"></i> Về Trang Chủ
          </a>
        `;
        empty.style.display = "block";
        showToast("Đã xóa tất cả lịch sử", "success");
      }
    });
  }

  // ========================================
  // STATE HELPERS
  // ========================================

  function showEmptyState(el, type) {
    const icon = type === "history" ? "fa-book" : "fa-bookmark-o";
    const text =
      type === "history"
        ? "Chưa có lịch sử đọc truyện"
        : "Chưa có truyện theo dõi";
    el.innerHTML = `
      <i class="fa ${icon}"></i>
      <h3>${text}</h3>
      <p>Bắt đầu đọc truyện yêu thích của bạn ngay!</p>
      <a href="${window.location.origin}" class="btn btn-primary">
        <i class="fa fa-home"></i> Về Trang Chủ
      </a>
    `;
    el.style.display = "block";
  }

  function showErrorState(el, message) {
    el.innerHTML = `
      <i class="fa fa-exclamation-triangle"></i>
      <h3>Không thể tải lịch sử</h3>
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

  /**
   * Tính time ago bằng tiếng Việt (mirror PHP truyenqq_time_ago_vietnamese)
   */
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
