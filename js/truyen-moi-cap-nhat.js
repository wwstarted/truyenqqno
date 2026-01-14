/**
 * Comics Listing with AJAX
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function () {
  "use strict";

  const state = {
    currentPage: 1,
    status: "",
    country: "",
    isLoading: false,
  };

  let comicsGrid = null;
  let paginationContainer = null;
  let filterLinks = null;

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    const mainContainer = document.querySelector("#main_homepage");
    if (!mainContainer) return;

    comicsGrid = document.querySelector(".list_grid.grid");
    paginationContainer = document.querySelector(".page_redirect");
    filterLinks = document.querySelectorAll(".story-list-bl01 ul.choose a");

    if (!comicsGrid) return;

    const urlParams = new URLSearchParams(window.location.search);
    state.currentPage = parseInt(urlParams.get("paged")) || 1;
    state.status = urlParams.get("status") || "";
    state.country = urlParams.get("country") || "";

    initFilterListeners();
    initPaginationListeners();
    initBookmarkButtons();

    console.log("AJAX Comics Listing initialized");
  }

  function initFilterListeners() {
    filterLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();

        const url = new URL(this.href);
        const newStatus = url.searchParams.get("status") || "";
        const newCountry = url.searchParams.get("country") || "";

        state.status = newStatus;
        state.country = newCountry;
        state.currentPage = 1;
        updateFilterActiveClass(this);

        loadComics();
      });
    });

    initPaginationListeners();
  }

  function initPaginationListeners() {
    if (!paginationContainer) return;

    const paginationLinks = paginationContainer.querySelectorAll("a");

    paginationLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();

        const href = this.getAttribute("href");
        if (!href || href === "javascript:void(0)") return;

        let page = 1;

        const url = new URL(href, window.location.origin);
        const pagedParam = url.searchParams.get("paged");

        if (pagedParam) {
          page = parseInt(pagedParam);
        } else {
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
      const baseUrl =
        typeof nettruyenData !== "undefined"
          ? nettruyenData.restUrl
          : "/wp-json/nettruyen/v1/comics";

      const apiUrl = new URL(baseUrl, window.location.origin);
      apiUrl.searchParams.set("page", state.currentPage);
      if (state.status) apiUrl.searchParams.set("status", state.status);
      if (state.country) apiUrl.searchParams.set("country", state.country);

      console.log("Loading comics from:", apiUrl.toString());

      const response = await fetch(apiUrl.toString());

      console.log("Response status:", response.status);
      console.log("Response headers:", response.headers.get("content-type"));

      const responseText = await response.text();
      console.log("Response text:", responseText.substring(0, 200));

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

    initBookmarkButtons();
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
      html += `<a href="#" data-page="${
        current_page - 1
      }"><p><span>‹</span></p></a>`;
    }

    if (start > 1) {
      html += `<a href="#" data-page="1"><p>1</p></a>`;
      if (start > 2) {
        html += `<span class="dots">...</span>`;
      }
    }

    for (let i = start; i <= end; i++) {
      if (i === current_page) {
        html += `<a href="javascript:void(0)"><p class="active">${i}</p></a>`;
      } else {
        html += `<a href="#" data-page="${i}"><p>${i}</p></a>`;
      }
    }

    if (end < total_pages) {
      if (end < total_pages - 1) {
        html += `<span class="dots">...</span>`;
      }
      html += `<a href="#" data-page="${total_pages}"><p>${total_pages}</p></a>`;
    }

    if (current_page < total_pages) {
      html += `<a href="#" data-page="${
        current_page + 1
      }"><p><span>›</span></p></a>`;
      html += `<a href="#" data-page="${total_pages}"><p><span>»</span></p></a>`;
    }

    paginationContainer.innerHTML = html;

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
