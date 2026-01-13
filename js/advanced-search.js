/**
 * Advanced Search with AJAX
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function () {
  "use strict";

  // State management
  const state = {
    currentPage: 1,
    genresInclude: [], // Array of genre IDs with tick
    genresExclude: [], // Array of genre IDs with cross
    status: "",
    country: "",
    minchapter: 0,
    sort: 2,
    isLoading: false,
  };

  // DOM elements
  let comicsGrid = null;
  let paginationContainer = null;
  let searchForm = null;
  let toggleButton = null;
  let genreItems = null;
  let searchButton = null;

  /**
   * Initialize Advanced Search
   */
  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    const mainContainer = document.querySelector("#main_homepage");
    if (!mainContainer || !mainContainer.dataset.ajaxEnabled) return;

    // Get DOM elements
    comicsGrid = document.querySelector(".list_grid.grid");
    paginationContainer = document.querySelector(".page_redirect");
    searchForm = document.querySelector(".advsearch-form");
    toggleButton = document.querySelector(".btn-collapse");
    genreItems = document.querySelectorAll(".genre-item");
    searchButton = document.querySelector(".btn-search");

    if (!comicsGrid || !searchForm) return;

    // Get initial state from URL
    const urlParams = new URLSearchParams(window.location.search);
    state.currentPage = parseInt(urlParams.get("page")) || 1;
    state.genresInclude = urlParams.get("genres")
      ? urlParams.get("genres").split(",").map(Number)
      : [];
    state.genresExclude = urlParams.get("exclude")
      ? urlParams.get("exclude").split(",").map(Number)
      : [];
    state.status = urlParams.get("status") || "";
    state.country = urlParams.get("country") || "";
    state.minchapter = parseInt(urlParams.get("minchapter")) || 0;
    state.sort = parseInt(urlParams.get("sort")) || 2;

    // Setup event listeners
    initFormToggle();
    initGenreSelector();
    initSelectListeners();
    initSearchButton();
    initPaginationListeners();
    initBookmarkButtons();

    // Restore genre states from URL
    restoreGenreStates();

    console.log("Advanced Search initialized", state);
  }

  /**
   * Form Show/Hide Toggle with localStorage
   */
  function initFormToggle() {
    if (!toggleButton) return;

    // Get saved state from localStorage (default: true = visible)
    const savedState = localStorage.getItem("advSearchFormVisible");
    const isVisible = savedState === null ? true : savedState === "true";

    // Apply initial state
    if (!isVisible) {
      searchForm.classList.add("hidden");
      toggleButton.querySelector(".show-text").classList.remove("hidden");
      toggleButton.querySelector(".hide-text").classList.add("hidden");
    }

    // Toggle button click
    toggleButton.addEventListener("click", function () {
      const isCurrentlyVisible = !searchForm.classList.contains("hidden");

      if (isCurrentlyVisible) {
        // Hide form
        searchForm.classList.add("hidden");
        this.querySelector(".show-text").classList.remove("hidden");
        this.querySelector(".hide-text").classList.add("hidden");
        localStorage.setItem("advSearchFormVisible", "false");
      } else {
        // Show form
        searchForm.classList.remove("hidden");
        this.querySelector(".show-text").classList.add("hidden");
        this.querySelector(".hide-text").classList.remove("hidden");
        localStorage.setItem("advSearchFormVisible", "true");
      }
    });
  }

  /**
   * Genre 3-State Selector (checkbox → tick → cross → checkbox)
   */
  function initGenreSelector() {
    genreItems.forEach((item) => {
      const icon = item.querySelector("span");
      const genreId = parseInt(icon.getAttribute("data-id"));

      icon.addEventListener("click", function () {
        const currentClass = this.className;

        if (currentClass === "icon-checkbox") {
          // Change to tick (include)
          this.className = "icon-tick";
          state.genresInclude.push(genreId);
        } else if (currentClass === "icon-tick") {
          // Change to cross (exclude)
          this.className = "icon-cross";
          state.genresInclude = state.genresInclude.filter(
            (id) => id !== genreId
          );
          state.genresExclude.push(genreId);
        } else if (currentClass === "icon-cross") {
          // Change back to checkbox (ignore)
          this.className = "icon-checkbox";
          state.genresExclude = state.genresExclude.filter(
            (id) => id !== genreId
          );
        }

        console.log("Genres:", {
          include: state.genresInclude,
          exclude: state.genresExclude,
        });
      });
    });
  }

  /**
   * Restore genre states from URL parameters
   */
  function restoreGenreStates() {
    genreItems.forEach((item) => {
      const icon = item.querySelector("span");
      const genreId = parseInt(icon.getAttribute("data-id"));

      if (state.genresInclude.includes(genreId)) {
        icon.className = "icon-tick";
      } else if (state.genresExclude.includes(genreId)) {
        icon.className = "icon-cross";
      }
    });
  }

  /**
   * Setup select dropdown listeners
   */
  function initSelectListeners() {
    // Country
    const countrySelect = document.querySelector("#country");
    if (countrySelect) {
      countrySelect.value = state.country;
    }

    // Status
    const statusSelect = document.querySelector("#status");
    if (statusSelect) {
      statusSelect.value = state.status;
    }

    // Min Chapter
    const minchapterSelect = document.querySelector("#minchapter");
    if (minchapterSelect) {
      minchapterSelect.value = state.minchapter;
    }

    // Sort
    const sortSelect = document.querySelector("#sort");
    if (sortSelect) {
      sortSelect.value = state.sort;
    }
  }

  /**
   * Search button click
   */
  function initSearchButton() {
    if (!searchButton) return;

    searchButton.addEventListener("click", function () {
      // Update state from form
      state.status = document.querySelector("#status").value;
      state.country = document.querySelector("#country").value;
      state.minchapter = parseInt(document.querySelector("#minchapter").value);
      state.sort = parseInt(document.querySelector("#sort").value);
      state.currentPage = 1; // Reset to page 1

      // Load comics
      loadComics();
    });
  }

  /**
   * Initialize pagination listeners
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
   * Load comics via REST API
   */
  async function loadComics() {
    if (state.isLoading) return;

    state.isLoading = true;
    showLoadingState();

    try {
      // Build API URL
      const baseUrl =
        typeof nettruyenData !== "undefined"
          ? nettruyenData.restUrl.replace("/comics", "/advanced-search")
          : "/wp-json/nettruyen/v1/advanced-search";

      const apiUrl = new URL(baseUrl, window.location.origin);

      // Add parameters
      apiUrl.searchParams.set("page", state.currentPage);
      apiUrl.searchParams.set("sort", state.sort);

      if (state.genresInclude.length > 0) {
        apiUrl.searchParams.set("genres", state.genresInclude.join(","));
      }
      if (state.genresExclude.length > 0) {
        apiUrl.searchParams.set("exclude", state.genresExclude.join(","));
      }
      if (state.status) apiUrl.searchParams.set("status", state.status);
      if (state.country) apiUrl.searchParams.set("country", state.country);
      if (state.minchapter > 0)
        apiUrl.searchParams.set("minchapter", state.minchapter);

      console.log("Fetching:", apiUrl.toString());

      // Fetch data
      const response = await fetch(apiUrl.toString());

      console.log("Response status:", response.status);

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        renderComics(data.comics);
        renderPagination(data.pagination);
        updateURL();
        scrollToTop();
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

  /**
   * Render comics grid (tái sử dụng từ truyen-moi-cap-nhat.js)
   */
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
          
          <span class="subscribed-badge not-subscribed add-subscribe" title="Theo Dõi" data-id="${
            comic.id
          }">
            <i class="fa fa-bookmark-o" aria-hidden="true"></i>
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
    `
      )
      .join("");

    comicsGrid.innerHTML = html;

    // Re-initialize bookmark buttons
    initBookmarkButtons();
  }

  /**
   * Render pagination (tái sử dụng)
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

    // Previous button
    if (current_page > 1) {
      html += `<a href="javascript:void(0)" data-page="${
        current_page - 1
      }"><p><span>‹</span></p></a>`;
    }

    // First page
    if (start > 1) {
      html += `<a href="javascript:void(0)" data-page="1"><p>1</p></a>`;
      if (start > 2) {
        html += `<span class="dots">...</span>`;
      }
    }

    // Page numbers
    for (let i = start; i <= end; i++) {
      if (i === current_page) {
        html += `<a href="javascript:void(0)"><p class="active">${i}</p></a>`;
      } else {
        html += `<a href="javascript:void(0)" data-page="${i}"><p>${i}</p></a>`;
      }
    }

    // Last page
    if (end < total_pages) {
      if (end < total_pages - 1) {
        html += `<span class="dots">...</span>`;
      }
      html += `<a href="javascript:void(0)" data-page="${total_pages}"><p>${total_pages}</p></a>`;
    }

    // Next button
    if (current_page < total_pages) {
      html += `<a href="javascript:void(0)" data-page="${
        current_page + 1
      }"><p><span>›</span></p></a>`;
      html += `<a href="javascript:void(0)" data-page="${total_pages}"><p><span>»</span></p></a>`;
    }

    paginationContainer.innerHTML = html;

    // Re-initialize pagination listeners
    initPaginationListeners();
  }

  /**
   * Update URL without page reload
   */
  function updateURL() {
    const params = new URLSearchParams();

    if (state.genresInclude.length > 0)
      params.set("genres", state.genresInclude.join(","));
    if (state.genresExclude.length > 0)
      params.set("exclude", state.genresExclude.join(","));
    if (state.status) params.set("status", state.status);
    if (state.country) params.set("country", state.country);
    if (state.minchapter > 0) params.set("minchapter", state.minchapter);
    if (state.sort !== 2) params.set("sort", state.sort);
    if (state.currentPage > 1) params.set("page", state.currentPage);

    const newURL =
      window.location.pathname +
      (params.toString() ? "?" + params.toString() : "");

    window.history.pushState({ page: state.currentPage }, "", newURL);
  }

  /**
   * Helper functions
   */
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

  /**
   * Initialize bookmark buttons
   */
  function initBookmarkButtons() {
    const bookmarkButtons = document.querySelectorAll(
      ".list_grid .subscribed-badge"
    );

    bookmarkButtons.forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (this.classList.contains("not-subscribed")) {
          this.classList.remove("not-subscribed");
          this.classList.add("subscribed");
          this.querySelector("i").className = "fa fa-bookmark";
          showToast("Đã thêm vào theo dõi");
        } else {
          this.classList.remove("subscribed");
          this.classList.add("not-subscribed");
          this.querySelector("i").className = "fa fa-bookmark-o";
          showToast("Đã bỏ theo dõi");
        }
      });
    });
  }

  /**
   * Show toast notification
   */
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

  // Start initialization
  init();
})();
