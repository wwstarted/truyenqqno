(function () {
  "use strict";

  const CONFIG = {
    searchAPI: TRUYENQQ_CONFIG.restUrl + "nettruyen/v1/search",
    genresAPI: TRUYENQQ_CONFIG.restUrl + "wp/v2/nettruyen_genre",
    searchDebounceDelay: 500,
    maxSearchResults: 20,
    localStorageKey: "truyenqq_dark_mode",
  };

  const elements = {
    darkModeToggle: document.getElementById("darkModeToggle"),
    body: document.body,

    searchInput: document.getElementById("searchInput"),
    searchResults: document.getElementById("searchResults"),

    mobileSearchToggle: document.getElementById("mobileSearchToggle"),
    mobileSearchExpand: document.getElementById("mobileSearchExpand"),
    mobileSearchInput: document.getElementById("mobileSearchInput"),
    mobileSearchResults: document.getElementById("mobileSearchResults"),

    mobileMenuToggle: document.getElementById("mobileMenuToggle"),
    mainMenu: document.getElementById("mainMenu"),

    genresList: document.getElementById("genresList"),

    // User menu elements
    userAvatar: document.getElementById("userAvatar"),
    userProfile: document.querySelector(".user-profile"),
    notificationBell: document.querySelector(".notification-bell"),
    iconNotification: document.querySelector(".icon-notification"),
  };

  function initDarkMode() {
    const savedMode = localStorage.getItem(CONFIG.localStorageKey);

    if (savedMode === "dark") {
      elements.body.classList.add("dark-mode");
    }

    if (elements.darkModeToggle) {
      elements.darkModeToggle.addEventListener("click", toggleDarkMode);
    }
  }

  function toggleDarkMode() {
    elements.body.classList.toggle("dark-mode");

    const isDark = elements.body.classList.contains("dark-mode");
    localStorage.setItem(CONFIG.localStorageKey, isDark ? "dark" : "light");

    elements.darkModeToggle.style.transform = "rotate(360deg)";
    setTimeout(() => {
      elements.darkModeToggle.style.transform = "";
    }, 300);
  }

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

      document.addEventListener("click", (e) => {
        if (!e.target.closest(".search-form")) {
          elements.searchResults.classList.remove("active");
        }
      });
    }

    if (elements.mobileSearchToggle) {
      elements.mobileSearchToggle.addEventListener("click", toggleMobileSearch);
    }

    if (elements.mobileSearchInput) {
      elements.mobileSearchInput.addEventListener("input", handleMobileSearch);
    }
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
        { signal: currentSearchController.signal }
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

  function getLoadingHTML() {
    return `
            <div class="search-loading">
                <i class="fa fa-spinner"></i>
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

        return `
                <a href="${item.link}" class="search-result-item">
                    <div class="search-result-avatar">
                        <img src="${thumbnail}" 
                             alt="${item.title}"
                             onerror="this.src='https://via.placeholder.com/60x80?text=No+Image'">
                    </div>
                    <div class="search-result-info">
                        <div class="search-result-title">${item.title}</div>
                        ${
                          altTitle
                            ? `<div class="search-result-alt-title">${altTitle}</div>`
                            : ""
                        }
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
      ".has-dropdown > .dropdown-toggle"
    );
    dropdownToggles.forEach((toggle) => {
      toggle.addEventListener("click", handleMobileDropdown);
    });
  }

  function toggleMobileMenu() {
    const isActive = elements.mainMenu.classList.toggle("active");

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

  let lastScroll = 0;

  function handleScroll() {
    const currentScroll = window.pageYOffset;
    const header = document.querySelector(".site-header");

    if (currentScroll > lastScroll && currentScroll > 100) {
      header.style.transform = "translateY(-100%)";
    } else {
      header.style.transform = "translateY(0)";
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

  // ============================================
  // USER MENU & NOTIFICATION HANDLERS
  // ============================================

  function initUserMenu() {
    // Chỉ thêm click handlers cho mobile/tablet
    if (elements.userAvatar && elements.userProfile) {
      elements.userAvatar.addEventListener("click", handleUserMenuToggle);
    }

    if (elements.iconNotification && elements.notificationBell) {
      elements.iconNotification.addEventListener(
        "click",
        handleNotificationToggle
      );
    }

    // Close dropdowns when clicking outside
    document.addEventListener("click", handleOutsideClick);
  }

  function handleUserMenuToggle(e) {
    e.preventDefault();
    e.stopPropagation();

    // Trên desktop (> 1024px), hover sẽ xử lý, không cần toggle
    if (window.innerWidth > 1024) return;

    // Close notification if open
    if (elements.notificationBell) {
      elements.notificationBell.classList.remove("active");
    }

    // Toggle user dropdown
    if (elements.userProfile) {
      elements.userProfile.classList.toggle("active");
    }
  }

  function handleNotificationToggle(e) {
    e.preventDefault();
    e.stopPropagation();

    // Trên desktop (> 1024px), hover sẽ xử lý, không cần toggle
    if (window.innerWidth > 1024) return;

    // Close user dropdown if open
    if (elements.userProfile) {
      elements.userProfile.classList.remove("active");
    }

    // Toggle notification dropdown
    if (elements.notificationBell) {
      elements.notificationBell.classList.toggle("active");
    }
  }

  function handleOutsideClick(e) {
    // Only handle on mobile/tablet
    if (window.innerWidth > 1024) return;

    // Check if click is outside user menu
    if (
      elements.userProfile &&
      !elements.userProfile.contains(e.target) &&
      elements.userProfile.classList.contains("active")
    ) {
      elements.userProfile.classList.remove("active");
    }

    // Check if click is outside notification
    if (
      elements.notificationBell &&
      !elements.notificationBell.contains(e.target) &&
      elements.notificationBell.classList.contains("active")
    ) {
      elements.notificationBell.classList.remove("active");
    }
  }

  // Close dropdowns on window resize
  function handleResize() {
    // If resizing to desktop, remove active classes
    if (window.innerWidth > 1024) {
      if (elements.userProfile) {
        elements.userProfile.classList.remove("active");
      }
      if (elements.notificationBell) {
        elements.notificationBell.classList.remove("active");
      }
    }
  }

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    initDarkMode();
    initSearch();
    initMobileMenu();
    initUserMenu(); // Initialize user menu handlers
    loadGenres();

    // Add resize listener
    window.addEventListener("resize", handleResize);

    console.log("TruyenQQ Header initialized successfully");
  }

  init();
})();
