/**
 * Comics Listing with AJAX
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function () {
  "use strict";

  // State management
  const state = {
    currentPage: 1,
    status: "",
    country: "",
    isLoading: false,
  };

  // DOM elements
  let comicsGrid = null;
  let paginationContainer = null;
  let filterLinks = null;

  /**
   * Initialize AJAX Comics Listing
   */
  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    const mainContainer = document.querySelector("#main_homepage");
    if (!mainContainer) return;

    // Get DOM elements
    comicsGrid = document.querySelector(".list_grid.grid");
    paginationContainer = document.querySelector(".page_redirect");
    filterLinks = document.querySelectorAll(".story-list-bl01 ul.choose a");

    if (!comicsGrid) return;

    // Get initial state from URL
    const urlParams = new URLSearchParams(window.location.search);
    state.currentPage = parseInt(urlParams.get("paged")) || 1;
    state.status = urlParams.get("status") || "";
    state.country = urlParams.get("country") || "";

    // Setup event listeners
    initFilterListeners();
    initPaginationListeners();
    initBookmarkButtons();

    console.log("AJAX Comics Listing initialized");
  }

  /**
   * Setup filter listeners
   */
  function initFilterListeners() {
    // Filter links
    filterLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();

        const url = new URL(this.href);
        const newStatus = url.searchParams.get("status") || "";
        const newCountry = url.searchParams.get("country") || "";

        // Update state
        state.status = newStatus;
        state.country = newCountry;
        state.currentPage = 1; // Reset to page 1 on filter change

        // Update active class
        updateFilterActiveClass(this);

        // Load comics
        loadComics();
      });
    });

    // Initial pagination links (from server-rendered HTML)
    initPaginationListeners();
  }

  /**
   * Initialize pagination listeners - FIXED VERSION
   */
  function initPaginationListeners() {
    if (!paginationContainer) return;

    const paginationLinks = paginationContainer.querySelectorAll("a");

    paginationLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();

        const href = this.getAttribute("href");
        if (!href || href === "javascript:void(0)") return;

        // Extract page number từ URL
        let page = 1;

        // Thử extract từ query param ?paged=X
        const url = new URL(href, window.location.origin);
        const pagedParam = url.searchParams.get("paged");

        if (pagedParam) {
          page = parseInt(pagedParam);
        } else {
          // Thử extract từ permalink /page/X/
          const pageMatch = href.match(/\/page\/(\d+)\/?/);
          if (pageMatch) {
            page = parseInt(pageMatch[1]);
          }
        }

        console.log(
          "Pagination clicked - Page:",
          page,
          "Current:",
          state.currentPage
        );

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
    // Remove active from all links in the same row
    const parentTd = clickedLink.closest("td");
    if (parentTd) {
      parentTd
        .querySelectorAll("a")
        .forEach((a) => a.classList.remove("active"));
    }
    clickedLink.classList.add("active");
  }

  /**
   * Load comics via AJAX
   */
  async function loadComics() {
    if (state.isLoading) return;

    state.isLoading = true;
    showLoadingState();

    try {
      // Build API URL - Sử dụng URL từ PHP
      const baseUrl =
        typeof nettruyenData !== "undefined"
          ? nettruyenData.restUrl
          : "/wp-json/nettruyen/v1/comics";

      const apiUrl = new URL(baseUrl, window.location.origin);
      apiUrl.searchParams.set("page", state.currentPage);
      if (state.status) apiUrl.searchParams.set("status", state.status);
      if (state.country) apiUrl.searchParams.set("country", state.country);

      console.log("Loading comics from:", apiUrl.toString());

      // Fetch data
      const response = await fetch(apiUrl.toString());

      // Debug: Check response
      console.log("Response status:", response.status);
      console.log("Response headers:", response.headers.get("content-type"));

      const responseText = await response.text();
      console.log("Response text:", responseText.substring(0, 200));

      // Try to parse JSON
      let data;
      try {
        data = JSON.parse(responseText);
      } catch (e) {
        console.error("JSON Parse Error:", e);
        throw new Error("Invalid JSON response from server");
      }

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
      showError("Đã xảy ra lỗi khi tải dữ liệu");
    } finally {
      state.isLoading = false;
      hideLoadingState();
    }
  }

  /**
   * Render comics grid
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
   * Render pagination
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
      html += `<a href="#" data-page="${
        current_page - 1
      }"><p><span>‹</span></p></a>`;
    }

    // First page
    if (start > 1) {
      html += `<a href="#" data-page="1"><p>1</p></a>`;
      if (start > 2) {
        html += `<span class="dots">...</span>`;
      }
    }

    // Page numbers
    for (let i = start; i <= end; i++) {
      if (i === current_page) {
        html += `<a href="javascript:void(0)"><p class="active">${i}</p></a>`;
      } else {
        html += `<a href="#" data-page="${i}"><p>${i}</p></a>`;
      }
    }

    // Last page
    if (end < total_pages) {
      if (end < total_pages - 1) {
        html += `<span class="dots">...</span>`;
      }
      html += `<a href="#" data-page="${total_pages}"><p>${total_pages}</p></a>`;
    }

    // Next button
    if (current_page < total_pages) {
      html += `<a href="#" data-page="${
        current_page + 1
      }"><p><span>›</span></p></a>`;
      html += `<a href="#" data-page="${total_pages}"><p><span>»</span></p></a>`;
    }

    paginationContainer.innerHTML = html;

    // Add click listeners to pagination links
    paginationContainer.querySelectorAll("a[data-page]").forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();
        const page = parseInt(this.getAttribute("data-page"));
        console.log("Pagination clicked (AJAX) - Page:", page);
        if (page && page !== state.currentPage) {
          state.currentPage = page;
          loadComics();
        }
      });
    });
  }

  /**
   * Update URL without page reload
   */
  function updateURL() {
    const params = new URLSearchParams();
    if (state.status) params.set("status", state.status);
    if (state.country) params.set("country", state.country);
    if (state.currentPage > 1) params.set("paged", state.currentPage);

    const newURL =
      window.location.pathname +
      (params.toString() ? "?" + params.toString() : "");

    window.history.pushState({ page: state.currentPage }, "", newURL);
  }

  /**
   * Smooth scroll to top
   */
  function scrollToTop() {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  /**
   * Show loading state
   */
  function showLoadingState() {
    comicsGrid.style.opacity = "0.5";
    comicsGrid.style.pointerEvents = "none";
  }

  /**
   * Hide loading state
   */
  function hideLoadingState() {
    comicsGrid.style.opacity = "1";
    comicsGrid.style.pointerEvents = "auto";
  }

  /**
   * Show error message
   */
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
