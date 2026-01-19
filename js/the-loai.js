/**
 * The Loai (Genre) Listing with AJAX (FIXED)
 * ✅ Xóa logic bookmark cục bộ
 * ✅ Dùng global TruyenqqBookmarks
 *
 * @package TruyenQQ
 * @version 1.0.1
 */

(function () {
  "use strict";

  const state = {
    currentPage: 1,
    status: "",
    country: "",
    sort: "2",
    genreSlug: "",
    isLoading: false,
  };

  let comicsGrid = null;
  let paginationContainer = null;
  let filterLinks = null;
  let categorySelect = null;
  let sortSelect = null;

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    const mainContainer = document.querySelector("#main_homepage");
    if (!mainContainer || !mainContainer.dataset.ajaxEnabled) return;

    comicsGrid = document.querySelector(".list_grid.grid");
    paginationContainer = document.querySelector(".page_redirect");
    filterLinks = document.querySelectorAll(".story-list-bl01 ul.choose a");
    categorySelect = document.querySelector("#category");
    sortSelect = document.querySelector("#category-sort");

    if (!comicsGrid) return;

    state.genreSlug = getGenreSlugFromURL();

    const urlParams = new URLSearchParams(window.location.search);
    state.currentPage = parseInt(urlParams.get("page")) || 1;
    state.status = urlParams.get("status") || "";
    state.country = urlParams.get("country") || "";
    state.sort = urlParams.get("sort") || "2";

    initFilterListeners();
    initSelectListeners();
    initPaginationListeners();

    console.log("✅ Genre Listing initialized (with global bookmarks)", state);
  }

  function getGenreSlugFromURL() {
    const path = window.location.pathname;
    const match = path.match(/\/([^\/]+)\/?$/);
    return match ? match[1] : "";
  }

  function initFilterListeners() {
    filterLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();

        const filterType = this.getAttribute("data-filter");
        const filterValue = this.getAttribute("data-value");

        if (filterType) {
          if (filterType === "status") {
            state.status = filterValue;
          } else if (filterType === "country") {
            state.country = filterValue;
          }
        } else {
          const url = new URL(this.href, window.location.origin);
          state.status = url.searchParams.get("status") || "";
          state.country = url.searchParams.get("country") || "";
        }

        state.currentPage = 1;
        updateFilterActiveClass(this);

        loadComics();
      });
    });
  }

  function initSelectListeners() {
    if (categorySelect) {
      categorySelect.addEventListener("change", function () {
        const selectedURL = this.value;
        if (selectedURL) {
          window.location.href = selectedURL;
        }
      });
    }

    if (sortSelect) {
      sortSelect.addEventListener("change", function () {
        state.sort = this.value;
        state.currentPage = 1;
        loadComics();
      });
    }
  }

  function initPaginationListeners() {
    if (!paginationContainer) return;

    const paginationLinks = paginationContainer.querySelectorAll("a");

    paginationLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();

        let page = this.getAttribute("data-page");

        if (!page) {
          const href = this.getAttribute("href");
          if (href && href !== "javascript:void(0)") {
            const url = new URL(href, window.location.origin);
            page =
              url.searchParams.get("page") ||
              url.searchParams.get("paged") ||
              "1";
          }
        }

        page = parseInt(page);

        if (page && page !== state.currentPage) {
          state.currentPage = page;
          loadComics();
        }
      });
    });
  }

  function updateFilterActiveClass(clickedLink) {
    const parentTd = clickedLink.closest("td");
    if (parentTd) {
      parentTd
        .querySelectorAll("a")
        .forEach((a) => a.classList.remove("active"));
    }
    clickedLink.classList.add("active");
  }

  async function loadComics() {
    if (state.isLoading) return;

    state.isLoading = true;
    showLoadingState();

    try {
      const restBase =
        typeof nettruyenGenreData !== "undefined"
          ? nettruyenGenreData.restUrl
          : window.location.origin + "/wp-json/nettruyen/v1/comics/genre";

      const apiUrl = new URL(restBase);

      apiUrl.searchParams.set("genre", state.genreSlug);
      apiUrl.searchParams.set("page", state.currentPage);
      apiUrl.searchParams.set("sort", state.sort);

      if (state.status) apiUrl.searchParams.set("status", state.status);
      if (state.country) apiUrl.searchParams.set("country", state.country);

      console.log("Fetching:", apiUrl.toString());

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

        // ✅ Gọi global bookmark check sau khi render
        if (typeof window.TruyenqqBookmarks !== "undefined") {
          window.TruyenqqBookmarks.check();
        }
      } else {
        showError("Không thể tải dữ liệu truyện");
      }
    } catch (error) {
      console.error("AJAX Error:", error);
      showError("Đã xảy ra lỗi: " + error.message);
    } finally {
      state.isLoading = false;
      hideLoadingState();
    }
  }

  function renderComics(comics) {
    if (!comics || comics.length === 0) {
      comicsGrid.innerHTML = `
        <li class="no-results">
          <p>Không tìm thấy truyện nào.</p>
        </li>
      `;
      return;
    }

    const html = comics
      .map(
        (comic) => `
      <li>
        <div class="book_avatar">
          <a href="${comic.url}" title="${comic.title}">
            <img class="center" src="${comic.thumbnail}" alt="${
              comic.title
            }" loading="lazy">
          </a>
          
          <!-- ✅ FIXED: Sử dụng bookmark-badge với data-post-id -->
          <span class="bookmark-badge" title="Theo dõi" data-post-id="${
            comic.id
          }">
            <i class="fa fa-bookmark-o"></i>
          </span>
          
          <div class="top-notice">
            <span class="time-ago">${comic.time_ago}</span>
            ${
              comic.badge_type
                ? `<span class="type-label ${comic.badge_type}">${comic.badge_text}</span>`
                : ""
            }
          </div>
        </div>
        
        <div class="book_info">
          <div class="book_name">
            <h3>
              <a title="${comic.title}" href="${comic.url}">${comic.title}</a>
            </h3>
          </div>
          <div class="clear"></div>
          
          <div class="text_detail">
            <span><i class="fa fa-bookmark"></i> ${comic.follow_count}</span>
            <span><i class="fa fa-eye"></i> ${comic.view_count}</span>
          </div>
          
          <div class="last_chapter">
            <a href="${comic.url}" title="${comic.latest_chapter}">${
              comic.latest_chapter
            }</a>
          </div>
        </div>
        
        <div class="clear"></div>
      </li>
    `,
      )
      .join("");

    comicsGrid.innerHTML = html;

    // ✅ Gọi global bookmark init sau khi render HTML
    if (typeof window.TruyenqqBookmarks !== "undefined") {
      window.TruyenqqBookmarks.init();
    }
  }

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

    if (current_page > 1) {
      html += `<a href="javascript:void(0)" data-page="${
        current_page - 1
      }"><p><span>‹</span></p></a>`;
    }

    if (start > 1) {
      html += `<a href="javascript:void(0)" data-page="1"><p>1</p></a>`;
      if (start > 2) {
        html += `<span class="dots">...</span>`;
      }
    }

    for (let i = start; i <= end; i++) {
      if (i === current_page) {
        html += `<a href="javascript:void(0)"><p class="active">${i}</p></a>`;
      } else {
        html += `<a href="javascript:void(0)" data-page="${i}"><p>${i}</p></a>`;
      }
    }

    if (end < total_pages) {
      if (end < total_pages - 1) {
        html += `<span class="dots">...</span>`;
      }
      html += `<a href="javascript:void(0)" data-page="${total_pages}"><p>${total_pages}</p></a>`;
    }

    if (current_page < total_pages) {
      html += `<a href="javascript:void(0)" data-page="${
        current_page + 1
      }"><p><span>›</span></p></a>`;
      html += `<a href="javascript:void(0)" data-page="${total_pages}"><p><span>»</span></p></a>`;
    }

    paginationContainer.innerHTML = html;

    initPaginationListeners();
  }

  function updateURL() {
    const params = new URLSearchParams();

    if (state.status) params.set("status", state.status);
    if (state.country) params.set("country", state.country);
    if (state.sort !== "2") params.set("sort", state.sort);
    if (state.currentPage > 1) params.set("page", state.currentPage);

    const newURL =
      window.location.pathname +
      (params.toString() ? "?" + params.toString() : "");

    window.history.pushState({ page: state.currentPage }, "", newURL);
  }

  function scrollToTop() {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  function showLoadingState() {
    comicsGrid.style.opacity = "0.5";
    comicsGrid.style.pointerEvents = "none";
  }

  function hideLoadingState() {
    comicsGrid.style.opacity = "1";
    comicsGrid.style.pointerEvents = "auto";
  }

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

  init();
})();

/**
 * ✅ REMOVED: initBookmarkButtons() - Dùng global TruyenqqBookmarks
 */

console.log("🚀 Genre Listing - Script loaded (global bookmarks enabled)");
