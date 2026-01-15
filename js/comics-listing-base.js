/**
 * Comics Listing Base Class
 * Core logic dùng chung cho tất cả trang listing
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

class ComicsListingBase {
  constructor(config = {}) {
    this.state = {
      currentPage: 1,
      status: "",
      country: "",
      isLoading: false,
    };

    this.config = {
      apiEndpoint: config.apiEndpoint || "",
      filterType: config.filterType || "default",
      postsPerPage: config.postsPerPage || 42,
    };

    this.elements = {
      mainContainer: null,
      comicsGrid: null,
      paginationContainer: null,
      filterLinks: null,
    };

    this.init();
  }

  init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", () => this.init());
      return;
    }

    this.cacheElements();

    if (!this.elements.comicsGrid) {
      console.warn("Comics grid not found");
      return;
    }

    this.parseUrlParams();
    this.bindEvents();

    console.log(`Comics Listing initialized - Type: ${this.config.filterType}`);
  }

  cacheElements() {
    this.elements.mainContainer = document.querySelector("#main_homepage");
    this.elements.comicsGrid = document.querySelector(".list_grid.grid");
    this.elements.paginationContainer =
      document.querySelector(".page_redirect");
    this.elements.filterLinks = document.querySelectorAll(
      ".story-list-bl01 ul.choose a"
    );
  }

  parseUrlParams() {
    const urlParams = new URLSearchParams(window.location.search);
    this.state.currentPage = parseInt(urlParams.get("paged")) || 1;
    this.state.status = urlParams.get("status") || "";
    this.state.country = urlParams.get("country") || "";
  }

  bindEvents() {
    this.initFilterListeners();
    this.initPaginationListeners();
    this.initBookmarkButtons();
  }

  initFilterListeners() {
    this.elements.filterLinks.forEach((link) => {
      link.addEventListener("click", (e) => {
        e.preventDefault();

        const url = new URL(link.href);
        const newStatus = url.searchParams.get("status") || "";
        const newCountry = url.searchParams.get("country") || "";

        this.state.status = newStatus;
        this.state.country = newCountry;
        this.state.currentPage = 1;

        this.updateFilterActiveClass(link);
        this.loadComics();
      });
    });
  }

  initPaginationListeners() {
    if (!this.elements.paginationContainer) return;

    const paginationLinks =
      this.elements.paginationContainer.querySelectorAll("a");

    paginationLinks.forEach((link) => {
      link.addEventListener("click", (e) => {
        e.preventDefault();

        const href = link.getAttribute("href");
        if (!href || href === "javascript:void(0)") return;

        let page = this.extractPageFromUrl(href);

        if (page && page !== this.state.currentPage) {
          this.state.currentPage = page;
          this.loadComics();
        }
      });
    });
  }

  extractPageFromUrl(href) {
    const url = new URL(href, window.location.origin);
    const pagedParam = url.searchParams.get("paged");

    if (pagedParam) {
      return parseInt(pagedParam);
    }

    const pageMatch = href.match(/\/page\/(\d+)\/?/);
    if (pageMatch) {
      return parseInt(pageMatch[1]);
    }

    return 1;
  }

  updateFilterActiveClass(clickedLink) {
    const parentTd = clickedLink.closest("td");
    if (parentTd) {
      parentTd
        .querySelectorAll("a")
        .forEach((a) => a.classList.remove("active"));
    }
    clickedLink.classList.add("active");
  }

  async loadComics() {
    if (this.state.isLoading) return;

    this.state.isLoading = true;
    this.showLoadingState();

    try {
      const apiUrl = this.buildApiUrl();
      console.log("Loading comics from:", apiUrl.toString());

      const response = await fetch(apiUrl.toString());

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        this.renderComics(data.comics);
        this.renderPagination(data.pagination);
        this.updateURL();
        this.scrollToTop();
      } else {
        this.showError("Không thể tải dữ liệu truyện");
      }
    } catch (error) {
      console.error("AJAX Error:", error);
      this.showError("Đã xảy ra lỗi khi tải dữ liệu");
    } finally {
      this.state.isLoading = false;
      this.hideLoadingState();
    }
  }

  buildApiUrl() {
    const baseUrl =
      this.config.apiEndpoint ||
      (typeof nettruyenData !== "undefined"
        ? nettruyenData.restUrl
        : "/wp-json/nettruyen/v1/comics");

    const apiUrl = new URL(baseUrl, window.location.origin);
    apiUrl.searchParams.set("page", this.state.currentPage);

    if (this.state.status) {
      apiUrl.searchParams.set("status", this.state.status);
    }
    if (this.state.country) {
      apiUrl.searchParams.set("country", this.state.country);
    }

    return apiUrl;
  }

  renderComics(comics) {
    if (!comics || comics.length === 0) {
      this.elements.comicsGrid.innerHTML = `
        <li class="no-results">
          <p>Không tìm thấy truyện nào.</p>
        </li>
      `;
      return;
    }

    const html = comics.map((comic) => this.renderComicCard(comic)).join("");
    this.elements.comicsGrid.innerHTML = html;
    this.initBookmarkButtons();
  }

  renderComicCard(comic) {
    // Override this method in child classes
    return `
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
    `;
  }

  renderPagination(pagination) {
    if (!this.elements.paginationContainer) return;

    const { current_page, total_pages } = pagination;

    if (total_pages <= 1) {
      this.elements.paginationContainer.innerHTML = "";
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

    // Next buttons
    if (current_page < total_pages) {
      html += `<a href="#" data-page="${
        current_page + 1
      }"><p><span>›</span></p></a>`;
      html += `<a href="#" data-page="${total_pages}"><p><span>»</span></p></a>`;
    }

    this.elements.paginationContainer.innerHTML = html;

    // Re-bind pagination events
    this.elements.paginationContainer
      .querySelectorAll("a[data-page]")
      .forEach((link) => {
        link.addEventListener("click", (e) => {
          e.preventDefault();
          const page = parseInt(link.getAttribute("data-page"));
          if (page && page !== this.state.currentPage) {
            this.state.currentPage = page;
            this.loadComics();
          }
        });
      });
  }

  updateURL() {
    const params = new URLSearchParams();
    if (this.state.status) params.set("status", this.state.status);
    if (this.state.country) params.set("country", this.state.country);
    if (this.state.currentPage > 1) params.set("paged", this.state.currentPage);

    const newURL =
      window.location.pathname +
      (params.toString() ? "?" + params.toString() : "");
    window.history.pushState({ page: this.state.currentPage }, "", newURL);
  }

  scrollToTop() {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  showLoadingState() {
    if (this.elements.comicsGrid) {
      this.elements.comicsGrid.style.opacity = "0.5";
      this.elements.comicsGrid.style.pointerEvents = "none";
    }
  }

  hideLoadingState() {
    if (this.elements.comicsGrid) {
      this.elements.comicsGrid.style.opacity = "1";
      this.elements.comicsGrid.style.pointerEvents = "auto";
    }
  }

  showError(message) {
    this.showToast(message);
  }

  initBookmarkButtons() {
    const bookmarkButtons = document.querySelectorAll(
      ".list_grid .subscribed-badge"
    );

    bookmarkButtons.forEach((button) => {
      // Remove old listeners
      const newButton = button.cloneNode(true);
      button.parentNode.replaceChild(newButton, button);

      newButton.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();

        if (newButton.classList.contains("not-subscribed")) {
          newButton.classList.remove("not-subscribed");
          newButton.classList.add("subscribed");
          newButton.querySelector("i").className = "fa fa-bookmark";
          this.showToast("Đã thêm vào theo dõi");
        } else {
          newButton.classList.remove("subscribed");
          newButton.classList.add("not-subscribed");
          newButton.querySelector("i").className = "fa fa-bookmark-o";
          this.showToast("Đã bỏ theo dõi");
        }
      });
    });
  }

  showToast(message) {
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
}

// Export for use in other files
if (typeof module !== "undefined" && module.exports) {
  module.exports = ComicsListingBase;
}
