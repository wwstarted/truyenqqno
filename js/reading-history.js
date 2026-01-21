/**
 * Reading History Page - JavaScript
 *
 * @package TruyenQQ
 * @version 1.0.3
 */

(function () {
  "use strict";
  function getApiBase() {
    if (typeof truyenqqConfig !== "undefined" && truyenqqConfig.apiBase) {
      return truyenqqConfig.apiBase;
    }

    const origin = window.location.origin;
    const pathname = window.location.pathname;

    let wpRoot = "/";
    if (pathname.includes("/wordpress/")) {
      wpRoot = pathname.substring(0, pathname.indexOf("/wordpress/") + 11);
    } else if (
      pathname.includes("/wp-admin/") ||
      pathname.includes("/wp-content/")
    ) {
      const parts = pathname.split("/");
      const wpIndex = parts.findIndex(
        (p) => p === "wp-admin" || p === "wp-content",
      );
      wpRoot = parts.slice(0, wpIndex).join("/") + "/";
    }

    return origin + wpRoot + "wp-json/nettruyen/v1";
  }

  const API_BASE = getApiBase();

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    const page = document.querySelector(".reading-history-page");
    if (!page) return;

    console.log("📍 API Base:", API_BASE);
    loadHistory();
    initClearAllButton();
  }

  /**
   * Get WP nonce for API requests
   */
  function getNonce() {
    if (typeof truyenqqConfig !== "undefined" && truyenqqConfig.nonce) {
      return truyenqqConfig.nonce;
    }
    return typeof wpApiSettings !== "undefined" ? wpApiSettings.nonce : "";
  }

  /**
   * Load reading history from API
   */
  async function loadHistory() {
    const loading = document.getElementById("history-loading");
    const empty = document.getElementById("history-empty");
    const grid = document.getElementById("history-grid");

    loading.style.display = "block";
    empty.style.display = "none";
    grid.style.display = "none";

    try {
      const response = await fetch(`${API_BASE}/reading-history`, {
        credentials: "same-origin",
        headers: {
          "X-WP-Nonce": getNonce(),
        },
      });

      if (!response.ok) {
        const errorText = await response.text();
        console.error("API Error:", response.status, errorText);
        throw new Error(
          `HTTP ${response.status}: ${response.statusText || "API Error"}`,
        );
      }

      const result = await response.json();

      loading.style.display = "none";

      if (!result.success || !result.data || result.data.length === 0) {
        empty.innerHTML = `
          <i class="fa fa-book"></i>
          <h3>Chưa có lịch sử đọc truyện</h3>
          <p>Bắt đầu đọc truyện yêu thích của bạn ngay!</p>
          <a href="${window.location.origin}" class="btn btn-primary">
            <i class="fa fa-home"></i> Về Trang Chủ
          </a>
        `;
        empty.style.display = "block";
        return;
      }

      renderHistory(result.data);
      grid.style.display = "grid";
    } catch (error) {
      console.error("Failed to load history:", error);
      loading.style.display = "none";

      empty.innerHTML = `
        <i class="fa fa-exclamation-triangle"></i>
        <h3>Không thể tải lịch sử</h3>
        <p style="color: #e74c3c; margin: 10px 0;">${error.message}</p>
        <p style="color: #777; font-size: 14px;">Vui lòng kiểm tra kết nối và thử lại</p>
        <a href="${window.location.href}" class="btn btn-primary">
          <i class="fa fa-refresh"></i> Thử Lại
        </a>
      `;
      empty.style.display = "block";
    }
  }

  /**
   * Render history items
   */
  function renderHistory(items) {
    const grid = document.getElementById("history-grid");
    grid.innerHTML = "";

    items.forEach((item) => {
      const card = createHistoryCard(item);
      grid.appendChild(card);
    });

    initDeleteButtons();
  }

  /**
   * Create history card HTML (giống homepage-new-update)
   */
  function createHistoryCard(item) {
    const div = document.createElement("div");
    div.className = "comic-item";
    div.setAttribute("data-history-id", item.id);

    const continueUrl = `${item.post_url}?chapter=${item.chapter_slug}&page=${item.current_page}`;

    div.innerHTML = `
  <div class="comic-card">
    <div class="comic-avatar">
      <a href="${item.post_url}" title="${escapeHtml(item.post_title)}">
        <img src="${item.thumbnail}" alt="${escapeHtml(item.post_title)}" loading="lazy">
      </a>
      
      <!-- ✅ CHỈ GIỮ remove-history button -->
      <span class="remove-history" title="Xóa lịch sử" data-id="${item.id}">
        <i class="fa fa-times-circle-o"></i>
      </span>
      
      <div class="top-notice">
        <span class="time-ago">${item.time_ago}</span>
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
          Đọc tiếp chương ${item.chapter_name}
        </a>
      </div>
    </div>
  </div>
`;

    return div;
  }

  /**
   * Init delete buttons
   */
  function initDeleteButtons() {
    const deleteButtons = document.querySelectorAll(".remove-history");

    deleteButtons.forEach((btn) => {
      btn.addEventListener("click", async function (e) {
        e.preventDefault();
        e.stopPropagation();

        const historyId = this.getAttribute("data-id");
        const card = this.closest(".comic-item");

        if (!confirm("Bạn có chắc muốn xóa lịch sử này?")) {
          return;
        }

        try {
          const response = await fetch(
            `${API_BASE}/reading-history/${historyId}`,
            {
              method: "DELETE",
              credentials: "same-origin",
              headers: {
                "X-WP-Nonce": getNonce(),
              },
            },
          );

          if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
          }

          const result = await response.json();

          if (result.success) {
            card.style.opacity = "0";
            card.style.transform = "scale(0.9)";
            setTimeout(() => {
              card.remove();

              const grid = document.getElementById("history-grid");
              if (grid.children.length === 0) {
                grid.style.display = "none";
                const emptyState = document.getElementById("history-empty");
                emptyState.innerHTML = `
                  <i class="fa fa-book"></i>
                  <h3>Chưa có lịch sử đọc truyện</h3>
                  <p>Bắt đầu đọc truyện yêu thích của bạn ngay!</p>
                  <a href="${window.location.origin}" class="btn btn-primary">
                    <i class="fa fa-home"></i> Về Trang Chủ
                  </a>
                `;
                emptyState.style.display = "block";
              }
            }, 300);

            showToast("Đã xóa lịch sử", "success");
          } else {
            showToast(
              result.message || "Xóa thất bại. Vui lòng thử lại.",
              "error",
            );
          }
        } catch (error) {
          console.error("Delete failed:", error);
          showToast("Lỗi xóa lịch sử", "error");
        }
      });
    });
  }

  /**
   * Init clear all button
   */
  function initClearAllButton() {
    const btn = document.getElementById("clear-all-history");
    if (!btn) return;

    btn.addEventListener("click", async function () {
      if (!confirm("Bạn có chắc muốn xóa TẤT CẢ lịch sử đọc truyện?")) {
        return;
      }

      const loading = document.getElementById("history-loading");
      const grid = document.getElementById("history-grid");
      const empty = document.getElementById("history-empty");

      try {
        loading.style.display = "block";
        grid.style.display = "none";

        const response = await fetch(`${API_BASE}/reading-history/clear`, {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "X-WP-Nonce": getNonce(),
          },
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const result = await response.json();

        loading.style.display = "none";

        if (result.success) {
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
          showToast(result.message, "success");
        } else {
          showToast(result.message || "Xóa thất bại", "error");
          grid.style.display = "grid";
        }
      } catch (error) {
        console.error("Clear all failed:", error);
        loading.style.display = "none";
        grid.style.display = "grid";
        showToast("Lỗi xóa lịch sử", "error");
      }
    });
  }

  /**
   * Utility: Format number
   */
  function formatNumber(num) {
    if (!num) return "0";
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  /**
   * Utility: Escape HTML
   */
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

  /**
   * Show toast notification
   */
  function showToast(message, type = "info") {
    let toastContainer = document.querySelector(".toast-container");

    if (!toastContainer) {
      toastContainer = document.createElement("div");
      toastContainer.className = "toast-container";
      document.body.appendChild(toastContainer);
    }

    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
      <i class="fa fa-${
        type === "success" ? "check-circle" : "exclamation-circle"
      }"></i>
      ${message}
    `;

    toastContainer.appendChild(toast);

    setTimeout(() => toast.classList.add("show"), 10);
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  init();
})();
