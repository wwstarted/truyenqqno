/**
 * Bookmarks Page (Theo Dõi) - JavaScript
 *
 * @package TruyenQQ
 * @version 1.0.0
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
    }

    return origin + wpRoot + "wp-json/nettruyen/v1";
  }

  const API_BASE = getApiBase();

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    const page = document.querySelector(".bookmarks-page");
    if (!page) return;

    console.log("📍 API Base:", API_BASE);
    loadBookmarks();
    initClearAllButton();
  }

  /**
   * Get WP nonce
   */
  function getNonce() {
    if (typeof truyenqqConfig !== "undefined" && truyenqqConfig.nonce) {
      return truyenqqConfig.nonce;
    }
    return typeof wpApiSettings !== "undefined" ? wpApiSettings.nonce : "";
  }

  /**
   * Load bookmarks from API
   */
  async function loadBookmarks() {
    const loading = document.getElementById("bookmarks-loading");
    const empty = document.getElementById("bookmarks-empty");
    const grid = document.getElementById("bookmarks-grid");

    loading.style.display = "block";
    empty.style.display = "none";
    grid.style.display = "none";

    try {
      const response = await fetch(`${API_BASE}/bookmarks`, {
        credentials: "same-origin",
        headers: {
          "X-WP-Nonce": getNonce(),
        },
      });

      if (!response.ok) {
        const errorText = await response.text();
        console.error("API Error:", response.status, errorText);
        throw new Error(
          `HTTP ${response.status}: ${response.statusText || "API Error"}`
        );
      }

      const result = await response.json();

      loading.style.display = "none";

      if (!result.success || !result.data || result.data.length === 0) {
        empty.innerHTML = `
          <i class="fa fa-bookmark-o"></i>
          <h3>Chưa có truyện theo dõi</h3>
          <p>Bắt đầu theo dõi truyện yêu thích của bạn ngay!</p>
          <a href="${window.location.origin}" class="btn btn-primary">
            <i class="fa fa-home"></i> Về Trang Chủ
          </a>
        `;
        empty.style.display = "block";
        return;
      }

      renderBookmarks(result.data);
      grid.style.display = "grid";
    } catch (error) {
      console.error("Failed to load bookmarks:", error);
      loading.style.display = "none";

      empty.innerHTML = `
        <i class="fa fa-exclamation-triangle"></i>
        <h3>Không thể tải truyện theo dõi</h3>
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
   * Render bookmarks
   */
  function renderBookmarks(items) {
    const grid = document.getElementById("bookmarks-grid");
    grid.innerHTML = "";

    items.forEach((item) => {
      const card = createBookmarkCard(item);
      grid.appendChild(card);
    });

    // Init remove buttons
    initRemoveButtons();

    // Init bookmark buttons (for global handler)
    if (window.TruyenqqBookmarks) {
      window.TruyenqqBookmarks.init();
    }
  }

  /**
   * Create bookmark card HTML
   */
  function createBookmarkCard(item) {
    const div = document.createElement("div");
    div.className = "comic-item";
    div.setAttribute("data-post-id", item.post_id);

    // Calculate time since added
    const addedDate = new Date(item.created_at);
    const now = new Date();
    const diffMs = now - addedDate;
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    let addedText = "Hôm nay";
    if (diffDays > 0) {
      addedText = `${diffDays} ngày trước`;
    }

    div.innerHTML = `
      <div class="comic-card">
        <div class="comic-avatar">
          <a href="${item.post_url}" title="${escapeHtml(item.post_title)}">
            <img src="${item.thumbnail}" alt="${escapeHtml(
      item.post_title
    )}" loading="lazy">
          </a>
          
          <span class="remove-bookmark" title="Bỏ theo dõi" data-post-id="${
            item.post_id
          }">
            <i class="fa fa-times-circle-o"></i>
          </span>
          
          <div class="top-notice">
            <span class="time-ago">${item.time_ago}</span>
            <span class="bookmark-badge active" data-post-id="${
              item.post_id
            }" title="Đã theo dõi">
              <i class="fa fa-bookmark"></i>
            </span>
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
            <a href="${item.post_url}" title="Đọc ${item.latest_chapter}">
              ${escapeHtml(item.latest_chapter)}
            </a>
          </div>
          
          <div class="bookmark-info">
            <small><i class="fa fa-clock-o"></i> Đã theo dõi ${addedText}</small>
          </div>
        </div>
      </div>
    `;

    return div;
  }

  /**
   * Init remove buttons
   */
  function initRemoveButtons() {
    const removeButtons = document.querySelectorAll(".remove-bookmark");

    removeButtons.forEach((btn) => {
      btn.addEventListener("click", async function (e) {
        e.preventDefault();
        e.stopPropagation();

        const postId = this.getAttribute("data-post-id");
        const card = this.closest(".comic-item");

        if (!confirm("Bạn có chắc muốn bỏ theo dõi truyện này?")) {
          return;
        }

        try {
          const response = await fetch(`${API_BASE}/bookmarks/${postId}`, {
            method: "DELETE",
            credentials: "same-origin",
            headers: {
              "X-WP-Nonce": getNonce(),
            },
          });

          if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
          }

          const result = await response.json();

          if (result.success) {
            // Remove card with animation
            card.style.opacity = "0";
            card.style.transform = "scale(0.9)";
            setTimeout(() => {
              card.remove();

              // Check if grid is empty
              const grid = document.getElementById("bookmarks-grid");
              if (grid.children.length === 0) {
                grid.style.display = "none";
                const emptyState = document.getElementById("bookmarks-empty");
                emptyState.innerHTML = `
                  <i class="fa fa-bookmark-o"></i>
                  <h3>Chưa có truyện theo dõi</h3>
                  <p>Bắt đầu theo dõi truyện yêu thích của bạn ngay!</p>
                  <a href="${window.location.origin}" class="btn btn-primary">
                    <i class="fa fa-home"></i> Về Trang Chủ
                  </a>
                `;
                emptyState.style.display = "block";
              }
            }, 300);

            showToast("Đã bỏ theo dõi", "success");
          } else {
            showToast(
              result.message || "Xóa thất bại. Vui lòng thử lại.",
              "error"
            );
          }
        } catch (error) {
          console.error("Delete failed:", error);
          showToast("Lỗi bỏ theo dõi", "error");
        }
      });
    });
  }

  /**
   * Init clear all button
   */
  function initClearAllButton() {
    const btn = document.getElementById("clear-all-bookmarks");
    if (!btn) return;

    btn.addEventListener("click", async function () {
      if (!confirm("Bạn có chắc muốn bỏ theo dõi TẤT CẢ truyện?")) {
        return;
      }

      const loading = document.getElementById("bookmarks-loading");
      const grid = document.getElementById("bookmarks-grid");
      const empty = document.getElementById("bookmarks-empty");

      try {
        loading.style.display = "block";
        grid.style.display = "none";

        const response = await fetch(`${API_BASE}/bookmarks/clear-all`, {
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
            <i class="fa fa-bookmark-o"></i>
            <h3>Đã xóa tất cả truyện theo dõi</h3>
            <p>Bắt đầu theo dõi truyện yêu thích của bạn ngay!</p>
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
        showToast("Lỗi xóa truyện theo dõi", "error");
      }
    });
  }

  /**
   * Utility functions
   */
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
