/**
 * Search Results Page - AJAX Logic - FINAL VERSION
 * Based on The Loai (Genre) structure
 *
 * @package TruyenQQ
 * @version 1.0.2 - FIXED SORT OPTIONS (Removed Follow Count)
 */

(function () {
  "use strict";

  const state = {
    keyword: "",
    currentPage: 1,
    status: "",
    country: "",
    sort: "0",
    isLoading: false,
  };

  let comicsGrid = null;
  let paginationContainer = null;
  let filterToggleBtn = null;
  let filterBox = null;
  let filterLinks = null;
  let sortSelect = null;

  /**
   * Initialize
   */
  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    const mainContainer = document.querySelector("#main_homepage");
    if (!mainContainer || !mainContainer.dataset.ajaxEnabled) return;

    comicsGrid = document.querySelector(".list_grid.grid");
    paginationContainer = document.querySelector(".page_redirect");
    filterToggleBtn = document.querySelector("#filterToggleBtn");
    filterBox = document.querySelector("#filterBox");
    filterLinks = document.querySelectorAll(".story-list-bl01 ul.choose a");
    sortSelect = document.querySelector("#sort-select");

    if (!comicsGrid) return;

    // Get initial state from URL
    const urlParams = new URLSearchParams(window.location.search);
    state.keyword = urlParams.get("keyword") || "";
    state.currentPage = parseInt(urlParams.get("page")) || 1;
    state.status = urlParams.get("status") || "";
    state.country = urlParams.get("country") || "";
    state.sort = urlParams.get("sort") || "0";

    // Initialize event listeners
    initFilterToggle();
    initFilterListeners();
    initSortListener();
    initPaginationListeners();

    console.log("✅ Search Results initialized", state);
  }

  /**
   * Filter Toggle Show/Hide
   */
  function initFilterToggle() {
    if (!filterToggleBtn || !filterBox) {
      console.warn("⚠️ Filter toggle elements not found");
      return;
    }

    filterToggleBtn.addEventListener("click", function (e) {
      e.preventDefault();

      const isCurrentlyShown = filterBox.classList.contains("show");

      if (isCurrentlyShown) {
        // Hide filter
        filterBox.classList.remove("show");
        filterToggleBtn.classList.remove("active");
        filterToggleBtn.setAttribute("aria-expanded", "false");
        console.log("🔽 Filter hidden");
      } else {
        // Show filter
        filterBox.classList.add("show");
        filterToggleBtn.classList.add("active");
        filterToggleBtn.setAttribute("aria-expanded", "true");
        console.log("🔼 Filter shown");
      }
    });

    console.log("✅ Filter toggle initialized");
  }

  /**
   * Filter Links (Status, Country)
   */
  function initFilterListeners() {
    filterLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();

        const filterType = this.getAttribute("data-filter");
        const filterValue = this.getAttribute("data-value");

        if (filterType === "status") {
          state.status = filterValue;
        } else if (filterType === "country") {
          state.country = filterValue;
        }

        state.currentPage = 1;
        updateFilterActiveClass(this);
        loadComics();
      });
    });
  }

  /**
   * Sort Select
   */
  function initSortListener() {
    if (!sortSelect) return;

    sortSelect.addEventListener("change", function () {
      state.sort = this.value;
      state.currentPage = 1;
      loadComics();

      console.log("📊 Sort changed to:", this.options[this.selectedIndex].text);
    });
  }

  /**
   * Pagination Links
   */
  function initPaginationListeners() {
    if (!paginationContainer) return;

    const paginationLinks = paginationContainer.querySelectorAll("a");

    paginationLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();

        const page = parseInt(this.getAttribute("data-page"));

        if (page && page !== state.currentPage) {
          state.currentPage = page;
          loadComics();
        }
      });
    });
  }

  /**
   * Update active class on filter links
   */
  function updateFilterActiveClass(clickedLink) {
    const parentTd = clickedLink.closest("td");
    if (parentTd) {
      parentTd
        .querySelectorAll("a")
        .forEach((a) => a.classList.remove("active"));
    }
    clickedLink.classList.add("active");
  }

  /**
   * AJAX Load Comics
   *
   * ✅ UPDATED: Sort options mapping
   * 0 = Liên quan nhất (Most Relevant)
   * 1 = Lượt xem cao nhất (Views DESC)
   * 2 = Lượt xem thấp nhất (Views ASC)
   * 3 = Mới nhất (Newest)
   * 4 = Cũ nhất (Oldest)
   */
  async function loadComics() {
    if (state.isLoading) return;

    state.isLoading = true;
    showLoadingState();

    try {
      const restBase =
        typeof nettruyenSearchData !== "undefined"
          ? nettruyenSearchData.restUrl
          : window.location.origin + "/wp-json/nettruyen/v1/search-results";

      const apiUrl = new URL(restBase);

      // Build query params
      if (state.keyword) apiUrl.searchParams.set("keyword", state.keyword);
      apiUrl.searchParams.set("page", state.currentPage);
      apiUrl.searchParams.set("sort", state.sort);

      if (state.status) apiUrl.searchParams.set("status", state.status);
      if (state.country) apiUrl.searchParams.set("country", state.country);

      console.log("📡 Fetching:", apiUrl.toString());

      const response = await fetch(apiUrl.toString());

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        renderComics(data.comics);
        renderPagination(data.pagination);
        updateURL();
        scrollToTop();

        // Refresh bookmark badges if available
        if (typeof window.TruyenqqBookmarks !== "undefined") {
          window.TruyenqqBookmarks.check();
        }
      } else {
        showError("Không thể tải dữ liệu truyện");
      }
    } catch (error) {
      console.error("❌ AJAX Error:", error);
      showError("Đã xảy ra lỗi: " + error.message);
    } finally {
      state.isLoading = false;
      hideLoadingState();
    }
  }

  /**
   * Render Comics Grid
   */
  function renderComics(comics) {
    if (!comics || comics.length === 0) {
      comicsGrid.innerHTML = `
        <li class="no-results">
          <div class="no-results-content">
            <i class="fa fa-search"></i>
            <p>Không tìm thấy kết quả nào ${
              state.keyword
                ? 'cho từ khóa "<strong>' +
                  escapeHtml(state.keyword) +
                  '</strong>"'
                : ""
            }</p>
            <p class="suggestion">Thử tìm kiếm với từ khóa khác hoặc <a href="${
              window.location.origin
            }">quay về trang chủ</a></p>
          </div>
        </li>
      `;
      return;
    }

    const html = comics
      .map(
        (comic) => `
      <li itemscope itemtype="http://schema.org/Book">
        <div class="book_avatar">
          <a href="${escapeHtml(comic.url)}" title="${escapeHtml(
            comic.title,
          )}" itemprop="url">
            <img class="center" src="${escapeHtml(comic.thumbnail)}" 
                 alt="${escapeHtml(comic.title)}" width="190" height="247" 
                 loading="lazy" itemprop="image">
          </a>
          
          <span class="bookmark-badge" title="Theo dõi" data-post-id="${
            comic.id
          }">
            <i class="fa fa-bookmark-o"></i>
          </span>
          
          <div class="top-notice">
            <span class="time-ago">${escapeHtml(comic.time_ago)}</span>
            ${
              comic.badge_type
                ? `<span class="type-label ${escapeHtml(
                    comic.badge_type,
                  )}">${escapeHtml(comic.badge_text)}</span>`
                : ""
            }
          </div>
        </div>
        
        <div class="book_info">
          <div class="book_name">
            <h3 itemprop="name">
              <a title="${escapeHtml(comic.title)}" href="${escapeHtml(
                comic.url,
              )}">
                ${escapeHtml(comic.title)}
              </a>
            </h3>
          </div>
          <div class="clear"></div>
          
          <div class="text_detail">
            <span><i class="fa fa-bookmark"></i> ${escapeHtml(
              comic.follow_count,
            )}</span>
            <span><i class="fa fa-eye"></i> ${escapeHtml(
              comic.view_count,
            )}</span>
          </div>
          
          <div class="last_chapter">
            <a href="${escapeHtml(comic.url)}" title="${escapeHtml(
              comic.latest_chapter,
            )}">
              ${escapeHtml(comic.latest_chapter)}
            </a>
          </div>
        </div>
        
        <div class="clear"></div>
      </li>
    `,
      )
      .join("");

    comicsGrid.innerHTML = html;

    // Re-init bookmarks
    if (typeof window.TruyenqqBookmarks !== "undefined") {
      window.TruyenqqBookmarks.init();
    }
  }

  /**
   * Render Pagination
   */
  function renderPagination(pagination) {
    if (!paginationContainer) return;

    const { current_page, total_pages } = pagination;

    if (total_pages <= 1) {
      paginationContainer.innerHTML = "";
      return;
    }

    let html = "";
    const range = 2;
    const start = Math.max(1, current_page - range);
    const end = Math.min(total_pages, current_page + range);

    // Previous
    if (current_page > 1) {
      html += `<a href="javascript:void(0)" data-page="${
        current_page - 1
      }" aria-label="Trang trước"><p><span aria-hidden="true">‹</span></p></a>`;
    }

    // First + ...
    if (start > 1) {
      html += `<a href="javascript:void(0)" data-page="1" aria-label="Trang 1"><p>1</p></a>`;
      if (start > 2) {
        html += `<span class="dots">...</span>`;
      }
    }

    // Page numbers
    for (let i = start; i <= end; i++) {
      if (i === current_page) {
        html += `<a href="javascript:void(0)" aria-current="page"><p class="active">${i}</p></a>`;
      } else {
        html += `<a href="javascript:void(0)" data-page="${i}" aria-label="Trang ${i}"><p>${i}</p></a>`;
      }
    }

    // ... + Last
    if (end < total_pages) {
      if (end < total_pages - 1) {
        html += `<span class="dots">...</span>`;
      }
      html += `<a href="javascript:void(0)" data-page="${total_pages}" aria-label="Trang ${total_pages}"><p>${total_pages}</p></a>`;
    }

    // Next + Last
    if (current_page < total_pages) {
      html += `<a href="javascript:void(0)" data-page="${
        current_page + 1
      }" aria-label="Trang tiếp theo"><p><span aria-hidden="true">›</span></p></a>`;
      html += `<a href="javascript:void(0)" data-page="${total_pages}" aria-label="Trang cuối"><p><span aria-hidden="true">»</span></p></a>`;
    }

    paginationContainer.innerHTML = html;
    initPaginationListeners();
  }

  /**
   * Update URL (pushState)
   */
  function updateURL() {
    const params = new URLSearchParams();

    if (state.keyword) params.set("keyword", state.keyword);
    if (state.status) params.set("status", state.status);
    if (state.country) params.set("country", state.country);
    if (state.sort !== "0") params.set("sort", state.sort);
    if (state.currentPage > 1) params.set("page", state.currentPage);

    const newURL =
      window.location.pathname +
      (params.toString() ? "?" + params.toString() : "");

    window.history.pushState({ page: state.currentPage }, "", newURL);
  }

  /**
   * Scroll to top
   */
  function scrollToTop() {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  /**
   * Loading states
   */
  function showLoadingState() {
    const main = document.querySelector("#main_homepage");
    if (main) main.classList.add("loading");
    if (comicsGrid) {
      comicsGrid.style.opacity = "0.5";
      comicsGrid.style.pointerEvents = "none";
    }
  }

  function hideLoadingState() {
    const main = document.querySelector("#main_homepage");
    if (main) main.classList.remove("loading");
    if (comicsGrid) {
      comicsGrid.style.opacity = "1";
      comicsGrid.style.pointerEvents = "auto";
    }
  }

  /**
   * Error handling
   */
  function showError(message) {
    showToast(message);
  }

  function showToast(message) {
    let toastContainer = document.querySelector(".toast-container");

    if (!toastContainer) {
      toastContainer = document.createElement("div");
      toastContainer.className = "toast-container";
      document.body.appendChild(toastContainer);
    }

    const toast = document.createElement("div");
    toast.className = "toast";
    toast.textContent = message;

    toastContainer.appendChild(toast);

    setTimeout(() => toast.classList.add("show"), 10);

    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 300);
    }, 2000);
  }

  /**
   * Escape HTML to prevent XSS
   */
  function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  // Initialize
  init();
})();
