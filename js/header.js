(function () {
  "use strict";

  // ========================================
  // EXTRACT BASE PATH FROM REST URL
  // ========================================
  function getBasePath() {
    try {
      const restUrl = TRUYENQQ_CONFIG.restUrl;
      const url = new URL(restUrl);

      let pathname = url.pathname;

      pathname = pathname.replace(/\/wp-json\/?$/, "");

      return pathname || "/";
    } catch (error) {
      console.error("Error extracting base path:", error);
      return "/";
    }
  }

  const BASE_PATH = getBasePath();

  const CONFIG = {
    searchAPI: TRUYENQQ_CONFIG.restUrl + "nettruyen/v1/search",
    genresAPI: TRUYENQQ_CONFIG.restUrl + "wp/v2/nettruyen_genre",
    userInfoAPI: TRUYENQQ_CONFIG.restUrl + "nettruyen/v1/user/info",
    searchDebounceDelay: 500,
    maxSearchResults: 20,
    minSearchLength: 6,
    localStorageKey: "truyenqq_dark_mode",
    searchResultsPage: "/ket-qua-tim-kiem/",
    basePath: BASE_PATH,
  };

  const elements = {
    darkModeToggle: document.getElementById("darkModeToggle"),
    body: document.body,
    searchInput: document.getElementById("searchInput"),
    searchResults: document.getElementById("searchResults"),
    searchSubmit: document.querySelector(".search-form .search-submit"),
    mobileSearchToggle: document.getElementById("mobileSearchToggle"),
    mobileSearchExpand: document.getElementById("mobileSearchExpand"),
    mobileSearchInput: document.getElementById("mobileSearchInput"),
    mobileSearchResults: document.getElementById("mobileSearchResults"),
    mobileSearchSubmit: document.querySelector(
      ".search-mobile-expand .search-submit",
    ),
    mobileMenuToggle: document.getElementById("mobileMenuToggle"),
    mainMenu: document.getElementById("mainMenu"),
    genresList: document.getElementById("genresList"),
    userAvatar: document.getElementById("userAvatar"),
    userProfile: document.querySelector(".user-profile"),
    notificationBell: document.querySelector(".notification-bell"),
    iconNotification: document.querySelector(".icon-notification"),
    userAvatarImg: document.getElementById("userAvatarImg"),
    userDropdownAvatar: document.getElementById("userDropdownAvatar"),
    headerBottom: document.querySelector(".header-bottom"),
  };

  // ========================================
  // DARK MODE
  // ========================================

  /**
   * initDarkMode — chạy ngay khi DOM sẵn sàng.
   *
   * Flow chống FOUC (UPDATED):
   * 1. Inline script trong <head> (blocking, sync) đã add 'dark-mode' vào <html>
   *    → CSS html.dark-mode { --bg-primary, --bg-secondary... } active ngay từ paint đầu tiên
   *    → Toàn bộ content (card, section, dropdown) dùng đúng vars tối — KHÔNG flash.
   * 2. Hàm này sync 'dark-mode' từ <html> xuống <body> để các selector body.dark-mode hoạt động.
   *    KHÔNG remove class khỏi <html> — cần giữ để vars html.dark-mode luôn hoạt động.
   */
  function initDarkMode() {
    const savedMode = localStorage.getItem(CONFIG.localStorageKey);
    const prefersDark = window.matchMedia(
      "(prefers-color-scheme: dark)",
    ).matches;
    const shouldBeDark = savedMode === "dark" || (!savedMode && prefersDark);

    if (shouldBeDark) {
      elements.body.classList.add("dark-mode");
      // Đảm bảo html cũng có class (trường hợp inline script bị block)
      document.documentElement.classList.add("dark-mode");
    } else {
      elements.body.classList.remove("dark-mode");
      document.documentElement.classList.remove("dark-mode");
    }

    // Chỉ remove dark-mode-loading (class cũ không còn dùng nữa, giữ để backward compat)
    document.documentElement.classList.remove("dark-mode-loading");

    if (elements.darkModeToggle) {
      elements.darkModeToggle.addEventListener("click", toggleDarkMode);
    }
  }

  // function toggleDarkMode() {
  //   const isDark = elements.body.classList.toggle("dark-mode");

  //   // Sync html.dark-mode để vars html.dark-mode luôn đúng
  //   document.documentElement.classList.toggle("dark-mode", isDark);

  //   // Lưu nhất quán với giá trị 'dark' / 'light'
  //   localStorage.setItem(CONFIG.localStorageKey, isDark ? "dark" : "light");

  //   elements.darkModeToggle.style.transform = "rotate(360deg)";
  //   setTimeout(() => {
  //     elements.darkModeToggle.style.transform = "";
  //   }, 300);
  // }

  function toggleDarkMode() {
    document.documentElement.classList.add("theme-switching");

    const isDark = elements.body.classList.toggle("dark-mode");
    document.documentElement.classList.toggle("dark-mode", isDark);
    localStorage.setItem(CONFIG.localStorageKey, isDark ? "dark" : "light");
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        document.documentElement.classList.remove("theme-switching");
      });
    });

    elements.darkModeToggle.style.transform = "rotate(360deg)";
    setTimeout(() => {
      elements.darkModeToggle.style.transform = "";
    }, 300);
  }

  // ========================================
  // SEARCH FUNCTIONALITY
  // ========================================
  let searchTimeout = null;
  let currentSearchController = null;

  function initSearch() {
    if (elements.searchInput) {
      elements.searchInput.addEventListener("input", handleDesktopSearch);
      elements.searchInput.addEventListener("focus", () => {
        if (elements.searchInput.value.trim()) {
          elements.searchResults.classList.add("active");
        }
      });

      elements.searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          redirectToSearchResults(elements.searchInput.value.trim());
        }
      });

      document.addEventListener("click", (e) => {
        if (!e.target.closest(".search-form")) {
          elements.searchResults.classList.remove("active");
        }
      });
    }

    if (elements.searchSubmit) {
      elements.searchSubmit.addEventListener("click", (e) => {
        e.preventDefault();
        const query = elements.searchInput.value.trim();
        redirectToSearchResults(query);
      });
    }

    if (elements.mobileSearchToggle) {
      elements.mobileSearchToggle.addEventListener("click", toggleMobileSearch);
    }

    if (elements.mobileSearchInput) {
      elements.mobileSearchInput.addEventListener("input", handleMobileSearch);

      elements.mobileSearchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          redirectToSearchResults(elements.mobileSearchInput.value.trim());
        }
      });
    }

    if (elements.mobileSearchSubmit) {
      elements.mobileSearchSubmit.addEventListener("click", (e) => {
        e.preventDefault();
        const query = elements.mobileSearchInput.value.trim();
        redirectToSearchResults(query);
      });
    }
  }

  function redirectToSearchResults(query) {
    if (!query) {
      showToast("Vui lòng nhập từ khóa tìm kiếm");
      return;
    }

    let basePath = CONFIG.basePath;

    if (basePath.endsWith("/")) {
      basePath = basePath.slice(0, -1);
    }

    const searchURL =
      basePath +
      CONFIG.searchResultsPage +
      "?keyword=" +
      encodeURIComponent(query);

    window.location.href = searchURL;
  }

  function handleDesktopSearch(e) {
    const query = e.target.value.trim();

    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }

    if (!query) {
      elements.searchResults.classList.remove("active");
      return;
    }

    if (query.length < CONFIG.minSearchLength) {
      elements.searchResults.innerHTML = getMinLengthHintHTML(query.length);
      elements.searchResults.classList.add("active");
      return;
    }

    elements.searchResults.innerHTML = getLoadingHTML();
    elements.searchResults.classList.add("active");

    searchTimeout = setTimeout(() => {
      performSearch(query, elements.searchResults);
    }, CONFIG.searchDebounceDelay);
  }

  function handleMobileSearch(e) {
    const query = e.target.value.trim();

    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }

    if (!query) {
      elements.mobileSearchResults.classList.remove("active");
      return;
    }

    if (query.length < CONFIG.minSearchLength) {
      elements.mobileSearchResults.innerHTML = getMinLengthHintHTML(
        query.length,
      );
      elements.mobileSearchResults.classList.add("active");
      return;
    }

    elements.mobileSearchResults.innerHTML = getLoadingHTML();
    elements.mobileSearchResults.classList.add("active");

    searchTimeout = setTimeout(() => {
      performSearch(query, elements.mobileSearchResults);
    }, CONFIG.searchDebounceDelay);
  }

  async function performSearch(query, resultsContainer) {
    if (currentSearchController) {
      currentSearchController.abort();
    }

    currentSearchController = new AbortController();

    try {
      const response = await fetch(
        `${CONFIG.searchAPI}?q=${encodeURIComponent(query)}`,
        { signal: currentSearchController.signal },
      );

      if (!response.ok) {
        throw new Error("Search failed");
      }

      const results = await response.json();

      if (results.length === 0) {
        resultsContainer.innerHTML = getNoResultsHTML();
      } else {
        resultsContainer.innerHTML = getResultsHTML(results);
      }
    } catch (error) {
      if (error.name === "AbortError") {
        return;
      }

      console.error("Search error:", error);
      resultsContainer.innerHTML = getErrorHTML();
    }
  }

  function toggleMobileSearch() {
    const isActive = elements.mobileSearchExpand.classList.toggle("active");

    if (isActive) {
      setTimeout(() => {
        elements.mobileSearchInput.focus();
      }, 300);
    } else {
      elements.mobileSearchInput.value = "";
      elements.mobileSearchResults.classList.remove("active");
      elements.mobileSearchResults.innerHTML = "";
    }
  }

  // ========================================
  // HTML TEMPLATES
  // ========================================
  function getMinLengthHintHTML(currentLength) {
    const remaining = CONFIG.minSearchLength - currentLength;
    return `
      <div class="search-hint">
        <i class="fa fa-info-circle"></i>
        <p>Nhập thêm <strong>${remaining}</strong> ký tự nữa để tìm kiếm</p>
        <small>Tối thiểu ${CONFIG.minSearchLength} ký tự</small>
      </div>
    `;
  }

  function getLoadingHTML() {
    return `
      <div class="search-loading">
        <i class="fa fa-spinner fa-spin"></i>
        <p>Đang tìm kiếm...</p>
      </div>
    `;
  }

  function getNoResultsHTML() {
    return `
      <div class="search-no-results">
        <i class="fa fa-search"></i>
        <p>Không tìm thấy kết quả</p>
      </div>
    `;
  }

  function getErrorHTML() {
    return `
      <div class="search-no-results">
        <i class="fa fa-exclamation-triangle"></i>
        <p>Có lỗi xảy ra, vui lòng thử lại</p>
      </div>
    `;
  }

  function getResultsHTML(results) {
    const limitedResults = results.slice(0, CONFIG.maxSearchResults);

    const itemsHTML = limitedResults
      .map((item) => {
        const thumbnail =
          item.thumbnail || "https://via.placeholder.com/60x80?text=No+Image";
        const altTitle = item.alternative_title || "";
        const chapter = item.latest_chapter || "Đang cập nhật";

        const followCount = (item.follow_count || 0).toLocaleString("vi-VN");
        const viewCount = (item.view_count || 0).toLocaleString("vi-VN");

        return `
          <a href="${item.link}" class="search-result-item">
            <div class="search-result-avatar">
              <img src="${thumbnail}" 
                   alt="${item.title}"
                   onerror="this.src='https://via.placeholder.com/60x80?text=No+Image'">
            </div>
            <div class="search-result-info">
              <div class="search-result-title">${item.title}</div>
              ${altTitle ? `<div class="search-result-alt-title">${altTitle}</div>` : ""}
              <div class="comic-stats">
                <span class="stat-item">
                  <i class="fa fa-bookmark"></i>
                  ${followCount}
                </span>
                <span class="stat-item">
                  <i class="fa fa-eye"></i>
                  ${viewCount}
                </span>
              </div>
              <div class="search-result-chapter">${chapter}</div>
            </div>
          </a>
        `;
      })
      .join("");

    return `<div class="search-results-list">${itemsHTML}</div>`;
  }

  function initMobileMenu() {
    if (elements.mobileMenuToggle) {
      elements.mobileMenuToggle.addEventListener("click", toggleMobileMenu);
    }

    const dropdownToggles = document.querySelectorAll(
      ".has-dropdown > .dropdown-toggle",
    );
    dropdownToggles.forEach((toggle) => {
      toggle.addEventListener("click", handleMobileDropdown);
    });
  }

  function toggleMobileMenu() {
    const isActive = elements.mainMenu.classList.toggle("active");
    if (isActive && elements.headerBottom) {
      elements.headerBottom.style.marginTop = "0";
    }

    const icon = elements.mobileMenuToggle.querySelector("i");
    if (icon) {
      if (isActive) {
        icon.className = "fa fa-window-close";
      } else {
        icon.className = "fa fa-bars";
      }
    }

    if (!isActive) {
      const activeDropdowns = document.querySelectorAll(".has-dropdown.active");
      activeDropdowns.forEach((dropdown) => {
        dropdown.classList.remove("active");
      });
    }
  }

  function handleMobileDropdown(e) {
    if (window.innerWidth > 1024) return;

    e.preventDefault();

    const parentLi = e.currentTarget.closest(".has-dropdown");
    const isActive = parentLi.classList.contains("active");

    const allDropdowns = document.querySelectorAll(".has-dropdown");
    allDropdowns.forEach((dropdown) => {
      if (dropdown !== parentLi) {
        dropdown.classList.remove("active");
      }
    });

    parentLi.classList.toggle("active");
  }

  // ========================================
  // SCROLL BEHAVIOR
  // ========================================
  let lastScroll = 0;
  let scrollAccumulator = 0;
  const SCROLL_THRESHOLD = 60;

  function handleScroll() {
    const currentScroll = Math.max(0, window.pageYOffset);
    const headerBottom = elements.headerBottom;

    if (!headerBottom) return;

    if (elements.mainMenu && elements.mainMenu.classList.contains("active")) {
      lastScroll = currentScroll;
      scrollAccumulator = 0;
      return;
    }

    const scrollDiff = currentScroll - lastScroll;
    scrollAccumulator += scrollDiff;

    if (scrollAccumulator > SCROLL_THRESHOLD && currentScroll > 100) {
      headerBottom.style.marginTop = `-${headerBottom.offsetHeight}px`;
      scrollAccumulator = 0;
    } else if (scrollAccumulator < -SCROLL_THRESHOLD || currentScroll <= 50) {
      headerBottom.style.marginTop = "0";
      scrollAccumulator = 0;
    }

    lastScroll = currentScroll;
  }

  function initScrollBehavior() {
    let ticking = false;

    window.addEventListener("scroll", () => {
      if (!ticking) {
        window.requestAnimationFrame(() => {
          handleScroll();
          ticking = false;
        });

        ticking = true;
      }
    });
  }

  async function loadGenres() {
    if (!elements.genresList) return;

    try {
      const response = await fetch(CONFIG.genresAPI + "?per_page=100");

      if (!response.ok) {
        throw new Error("Failed to load genres");
      }

      const genres = await response.json();

      genres.sort((a, b) => a.name.localeCompare(b.name, "vi"));

      const genresHTML = genres
        .map((genre) => {
          return `<a href="${genre.link}" title="${genre.name}">${genre.name}</a>`;
        })
        .join("");

      elements.genresList.innerHTML = genresHTML;
    } catch (error) {
      console.error("Error loading genres:", error);
      elements.genresList.innerHTML = `
        <div class="loading-genres">
          <i class="fa fa-exclamation-triangle"></i> Không thể tải thể loại
        </div>
      `;
    }
  }

  function initUserMenu() {
    if (elements.userAvatar && elements.userProfile) {
      elements.userAvatar.addEventListener("click", handleUserMenuToggle);
    }

    if (elements.iconNotification && elements.notificationBell) {
      elements.iconNotification.addEventListener(
        "click",
        handleNotificationToggle,
      );
    }

    document.addEventListener("click", handleOutsideClick);
  }

  function handleUserMenuToggle(e) {
    e.preventDefault();
    e.stopPropagation();

    if (window.innerWidth > 1024) return;

    if (elements.notificationBell) {
      elements.notificationBell.classList.remove("active");
    }

    if (elements.userProfile) {
      elements.userProfile.classList.toggle("active");
    }
  }

  function handleNotificationToggle(e) {
    e.preventDefault();
    e.stopPropagation();

    if (window.innerWidth > 1024) return;

    if (elements.userProfile) {
      elements.userProfile.classList.remove("active");
    }

    if (elements.notificationBell) {
      elements.notificationBell.classList.toggle("active");
    }
  }

  function handleOutsideClick(e) {
    if (window.innerWidth > 1024) return;

    if (
      elements.userProfile &&
      !elements.userProfile.contains(e.target) &&
      elements.userProfile.classList.contains("active")
    ) {
      elements.userProfile.classList.remove("active");
    }

    if (
      elements.notificationBell &&
      !elements.notificationBell.contains(e.target) &&
      elements.notificationBell.classList.contains("active")
    ) {
      elements.notificationBell.classList.remove("active");
    }
  }

  function handleResize() {
    if (window.innerWidth > 1024) {
      if (elements.userProfile) {
        elements.userProfile.classList.remove("active");
      }
      if (elements.notificationBell) {
        elements.notificationBell.classList.remove("active");
      }
    }
  }

  function updateHeaderAvatar(avatarUrl) {
    if (elements.userAvatarImg) {
      elements.userAvatarImg.src = avatarUrl;
      elements.userAvatarImg.onerror = function () {
        this.src =
          "https://th.bing.com/th/id/OIP.ItvA9eX1ZIYT8NHePqeuCgHaHa?w=159&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3";
      };
    }

    if (elements.userDropdownAvatar) {
      elements.userDropdownAvatar.src = avatarUrl;
      elements.userDropdownAvatar.onerror = function () {
        this.src =
          "https://th.bing.com/th/id/OIP.ItvA9eX1ZIYT8NHePqeuCgHaHa?w=159&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3";
      };
    }
  }

  async function refreshUserAvatar() {
    try {
      const response = await fetch(CONFIG.userInfoAPI, {
        method: "GET",
        headers: {
          "X-WP-Nonce": TRUYENQQ_CONFIG.nonce,
        },
      });

      if (!response.ok) {
        throw new Error("Failed to fetch user info");
      }

      const data = await response.json();

      if (data.success && data.user && data.user.avatar) {
        updateHeaderAvatar(data.user.avatar);
      }
    } catch (error) {
      console.error("TruyenQQ Header: Error refreshing avatar:", error);
    }
  }

  function initGlobalEvents() {
    if (!window.TruyenQQ_Events) {
      window.TruyenQQ_Events = {
        listeners: {},

        on: function (event, callback) {
          if (!this.listeners[event]) {
            this.listeners[event] = [];
          }
          this.listeners[event].push(callback);
        },

        trigger: function (event, data) {
          if (this.listeners[event]) {
            this.listeners[event].forEach((callback) => callback(data));
          }
        },
      };
    }

    window.TruyenQQ_Events.on("avatar:updated", function (data) {
      if (data && data.url) {
        updateHeaderAvatar(data.url);
      }
    });
  }

  // ========================================
  // TOAST UTILITY
  // ========================================
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

  // ========================================
  // INITIALIZATION
  // ========================================
  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    initDarkMode();
    initSearch();
    initMobileMenu();
    initUserMenu();
    initGlobalEvents();
    loadGenres();
    initScrollBehavior();

    window.addEventListener("resize", handleResize);

    window.TruyenQQ_Header = {
      updateAvatar: updateHeaderAvatar,
      refreshAvatar: refreshUserAvatar,
    };
  }

  init();
})();
