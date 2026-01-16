/**
 * Reading History Page - JavaScript
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function () {
  "use strict";

  const API_BASE = "/wp-json/nettruyen/v1";

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    const page = document.querySelector(".reading-history-page");
    if (!page) return;

    loadHistory();
    initClearAllButton();
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
      });

      const result = await response.json();

      loading.style.display = "none";

      if (!result.success || !result.data || result.data.length === 0) {
        empty.style.display = "block";
        return;
      }

      renderHistory(result.data);
      grid.style.display = "grid";
    } catch (error) {
      console.error("Failed to load history:", error);
      loading.style.display = "none";
      showToast("Lỗi tải lịch sử. Vui lòng thử lại.", "error");
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

    // Init delete buttons
    initDeleteButtons();
  }

  /**
   * Create history card HTML
   */
  function createHistoryCard(item) {
    const div = document.createElement("div");
    div.className = "comic-item";
    div.setAttribute("data-history-id", item.id);

    const continueUrl = `${item.post_url}?chapter=${item.chapter_slug}&page=${item.current_page}`;
    const progressPercent =
      item.total_pages > 0
        ? Math.round((item.current_page / item.total_pages) * 100)
        : 0;

    div.innerHTML = `
      <div class="comic-card">
        <div class="comic-avatar">
          <a href="${item.post_url}" title="${escapeHtml(item.post_title)}">
            <img src="${item.thumbnail}" alt="${escapeHtml(
      item.post_title
    )}" loading="lazy">
          </a>
          
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
          
          <div class="reading-progress">
            <div class="progress-bar">
              <div class="progress-fill" style="width: ${progressPercent}%"></div>
            </div>
            <span class="progress-text">${progressPercent}%</span>
          </div>
          
          <div class="latest-chapter">
            <a href="${continueUrl}" title="Đọc tiếp ${item.chapter_name}">
              Đọc Tiếp ${item.chapter_name}
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
            }
          );

          const result = await response.json();

          if (result.success) {
            // Remove card with animation
            card.style.opacity = "0";
            card.style.transform = "scale(0.9)";
            setTimeout(() => {
              card.remove();

              // Check if grid is empty
              const grid = document.getElementById("history-grid");
              if (grid.children.length === 0) {
                grid.style.display = "none";
                document.getElementById("history-empty").style.display =
                  "block";
              }
            }, 300);

            showToast("Đã xóa lịch sử", "success");
          } else {
            showToast("Xóa thất bại. Vui lòng thử lại.", "error");
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

      try {
        const response = await fetch(`${API_BASE}/reading-history/clear`, {
          method: "POST",
          credentials: "same-origin",
        });

        const result = await response.json();

        if (result.success) {
          document.getElementById("history-grid").style.display = "none";
          document.getElementById("history-empty").style.display = "block";
          showToast(result.message, "success");
        } else {
          showToast("Xóa thất bại", "error");
        }
      } catch (error) {
        console.error("Clear all failed:", error);
        showToast("Lỗi xóa lịch sử", "error");
      }
    });
  }

  /**
   * Utility: Format number
   */
  function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  /**
   * Utility: Escape HTML
   */
  function escapeHtml(text) {
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
