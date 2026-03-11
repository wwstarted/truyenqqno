/**
 * Country Listing — AJAX Pagination + Sort
 * taxonomy-nettruyen_country.php
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function () {
  "use strict";

  /* ── state ────────────────────────────────────── */
  const state = {
    currentPage: 1,
    sort: "2",
    countrySlug: "",
    isLoading: false,
  };

  let comicsGrid = null;
  let paginationContainer = null;
  let sortSelect = null;

  /* ── init ─────────────────────────────────────── */
  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    const mainContainer = document.querySelector("#main_homepage");
    if (!mainContainer || !mainContainer.dataset.ajaxEnabled) return;

    comicsGrid = document.querySelector(".list_grid.grid");
    paginationContainer = document.querySelector(".page_redirect");
    sortSelect = document.querySelector("#country-sort");

    if (!comicsGrid) return;

    state.countrySlug = getSlugFromURL();

    const urlParams = new URLSearchParams(window.location.search);
    state.currentPage = parseInt(urlParams.get("page")) || 1;
    state.sort = urlParams.get("sort") || "2";

    if (sortSelect) sortSelect.value = state.sort;

    initSortListener();
    initPaginationListeners();
  }

  /* ── helpers ──────────────────────────────────── */
  function getSlugFromURL() {
    const path = window.location.pathname.replace(/\/$/, "");
    const parts = path.split("/");
    return parts[parts.length - 1] || "";
  }

  /* ── listeners ────────────────────────────────── */
  function initSortListener() {
    if (!sortSelect) return;
    sortSelect.addEventListener("change", function () {
      state.sort = this.value;
      state.currentPage = 1;
      loadComics();
    });
  }

  function initPaginationListeners() {
    if (!paginationContainer) return;
    paginationContainer
      .querySelectorAll("a[data-page]")
      .forEach(function (link) {
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

  /* ── AJAX load ────────────────────────────────── */
  async function loadComics() {
    if (state.isLoading) return;

    state.isLoading = true;
    showLoadingState();

    try {
      const restBase =
        typeof nettruyenCountryData !== "undefined"
          ? nettruyenCountryData.restUrl
          : window.location.origin + "/wp-json/nettruyen/v1/comics/country";

      const apiUrl = new URL(restBase);
      apiUrl.searchParams.set("country", state.countrySlug);
      apiUrl.searchParams.set("page", state.currentPage);
      apiUrl.searchParams.set("sort", state.sort);

      const response = await fetch(apiUrl.toString());
      if (!response.ok) throw new Error("HTTP " + response.status);

      const data = await response.json();

      if (data.success) {
        renderComics(data.comics);
        renderPagination(data.pagination);
        updateURL();
        scrollToTop();

        if (typeof window.TruyenqqBookmarks !== "undefined") {
          window.TruyenqqBookmarks.init();
        }
      } else {
        showToast("Không thể tải dữ liệu truyện");
      }
    } catch (err) {
      console.error("Country AJAX error:", err);
      showToast("Đã xảy ra lỗi: " + err.message);
    } finally {
      state.isLoading = false;
      hideLoadingState();
    }
  }

  /* ── render comics ────────────────────────────── */
  function renderComics(comics) {
    if (!comics || comics.length === 0) {
      comicsGrid.innerHTML = `<li class="no-results"><p>Không tìm thấy truyện nào.</p></li>`;
      return;
    }

    comicsGrid.innerHTML = comics
      .map(function (c) {
        const badge = c.badge_type
          ? `<span class="type-label ${c.badge_type}">${c.badge_text}</span>`
          : "";

        return `
        <li>
          <div class="book_avatar">
            <a href="${c.url}" title="${c.title}">
              <img class="center" src="${c.thumbnail}" alt="${c.title}" loading="lazy">
            </a>
            <span class="bookmark-badge" title="Theo dõi" data-post-id="${c.id}">
              <i class="fa fa-bookmark-o"></i>
            </span>
            <div class="top-notice">
              <span class="time-ago">${c.time_ago}</span>
              ${badge}
            </div>
          </div>
          <div class="book_info">
            <div class="book_name">
              <h3><a title="${c.title}" href="${c.url}">${c.title}</a></h3>
            </div>
            <div class="clear"></div>
            <div class="text_detail">
              <span><i class="fa fa-bookmark"></i> ${c.follow_count}</span>
              <span><i class="fa fa-eye"></i> ${c.view_count}</span>
            </div>
            <div class="last_chapter">
              <a href="${c.url}" title="${c.latest_chapter}">${c.latest_chapter}</a>
            </div>
          </div>
          <div class="clear"></div>
        </li>`;
      })
      .join("");
  }

  /* ── render pagination ────────────────────────── */
  function renderPagination(pagination) {
    if (!paginationContainer) return;

    const { current_page, total_pages } = pagination;

    if (total_pages <= 1) {
      paginationContainer.innerHTML = "";
      return;
    }

    const range = 2;
    const start = Math.max(1, current_page - range);
    const end = Math.min(total_pages, current_page + range);
    let html = "";

    if (current_page > 1) html += btn(current_page - 1, "‹");

    if (start > 1) {
      html += btn(1, "1");
      if (start > 2) html += `<span class="dots">...</span>`;
    }

    for (let i = start; i <= end; i++) {
      html +=
        i === current_page
          ? `<a href="javascript:void(0)"><p class="active">${i}</p></a>`
          : btn(i, i);
    }

    if (end < total_pages) {
      if (end < total_pages - 1) html += `<span class="dots">...</span>`;
      html += btn(total_pages, total_pages);
    }

    if (current_page < total_pages) {
      html += btn(current_page + 1, "›");
      html += btn(total_pages, "»");
    }

    paginationContainer.innerHTML = html;
    initPaginationListeners();
  }

  function btn(page, label) {
    return `<a href="javascript:void(0)" data-page="${page}"><p>${label}</p></a>`;
  }

  /* ── URL sync ─────────────────────────────────── */
  function updateURL() {
    const params = new URLSearchParams();
    if (state.sort !== "2") params.set("sort", state.sort);
    if (state.currentPage > 1) params.set("page", state.currentPage);

    const newURL =
      window.location.pathname +
      (params.toString() ? "?" + params.toString() : "");
    window.history.pushState({ page: state.currentPage }, "", newURL);
  }

  /* ── UI helpers ───────────────────────────────── */
  function scrollToTop() {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  function showLoadingState() {
    comicsGrid.style.opacity = "0.45";
    comicsGrid.style.pointerEvents = "none";
  }

  function hideLoadingState() {
    comicsGrid.style.opacity = "1";
    comicsGrid.style.pointerEvents = "auto";
  }

  function showToast(message) {
    let container = document.querySelector(".toast-container");
    if (!container) {
      container = document.createElement("div");
      container.className = "toast-container";
      document.body.appendChild(container);
    }
    const toast = document.createElement("div");
    toast.className = "toast";
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add("show"), 10);
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => toast.remove(), 300);
    }, 2500);
  }

  init();
})();
