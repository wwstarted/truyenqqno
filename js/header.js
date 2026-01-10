/**
 * TruyenQQ Header JavaScript
 * Features: Dark Mode Toggle, Search Functionality, Responsive Menu
 */

(function () {
  "use strict";

  // ==================== CONFIGURATION ====================
  const CONFIG = {
    searchAPI: TRUYENQQ_CONFIG.restUrl + "nettruyen/v1/search",
    genresAPI: TRUYENQQ_CONFIG.restUrl + "wp/v2/nettruyen_genre",
    searchDebounceDelay: 500,
    maxSearchResults: 20,
    localStorageKey: "truyenqq_dark_mode",
  };

  // ==================== DOM ELEMENTS ====================
  const elements = {
    darkModeToggle: document.getElementById("darkModeToggle"),
    body: document.body,

    // Desktop Search
    searchInput: document.getElementById("searchInput"),
    searchResults: document.getElementById("searchResults"),

    // Mobile Search
    mobileSearchToggle: document.getElementById("mobileSearchToggle"),
    mobileSearchExpand: document.getElementById("mobileSearchExpand"),
    mobileSearchInput: document.getElementById("mobileSearchInput"),
    mobileSearchResults: document.getElementById("mobileSearchResults"),

    // Menu
    mobileMenuToggle: document.getElementById("mobileMenuToggle"),
    mainMenu: document.getElementById("mainMenu"),

    // Genres
    genresList: document.getElementById("genresList"),
  };

  // ==================== DARK MODE ====================

  /**
   * Initialize Dark Mode
   */
  function initDarkMode() {
    // Check localStorage for saved preference
    const savedMode = localStorage.getItem(CONFIG.localStorageKey);

    if (savedMode === "dark") {
      elements.body.classList.add("dark-mode");
    }

    // Toggle on button click
    if (elements.darkModeToggle) {
      elements.darkModeToggle.addEventListener("click", toggleDarkMode);
    }
  }

  /**
   * Toggle Dark Mode
   */
  function toggleDarkMode() {
    elements.body.classList.toggle("dark-mode");

    // Save to localStorage
    const isDark = elements.body.classList.contains("dark-mode");
    localStorage.setItem(CONFIG.localStorageKey, isDark ? "dark" : "light");

    // Optional: Add animation effect
    elements.darkModeToggle.style.transform = "rotate(360deg)";
    setTimeout(() => {
      elements.darkModeToggle.style.transform = "";
    }, 300);
  }

  // ==================== SEARCH FUNCTIONALITY ====================

  let searchTimeout = null;
  let currentSearchController = null;

  /**
   * Initialize Search
   */
  function initSearch() {
    // Desktop search
    if (elements.searchInput) {
      elements.searchInput.addEventListener("input", handleDesktopSearch);
      elements.searchInput.addEventListener("focus", () => {
        if (elements.searchInput.value.trim()) {
          elements.searchResults.classList.add("active");
        }
      });

      // Close on click outside
      document.addEventListener("click", (e) => {
        if (!e.target.closest(".search-form")) {
          elements.searchResults.classList.remove("active");
        }
      });
    }

    // Mobile search toggle
    if (elements.mobileSearchToggle) {
      elements.mobileSearchToggle.addEventListener("click", toggleMobileSearch);
    }

    // Mobile search input
    if (elements.mobileSearchInput) {
      elements.mobileSearchInput.addEventListener("input", handleMobileSearch);
    }
  }

  /**
   * Handle Desktop Search
   */
  function handleDesktopSearch(e) {
    const query = e.target.value.trim();

    // Clear previous timeout
    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }

    // If empty, hide results
    if (!query) {
      elements.searchResults.classList.remove("active");
      return;
    }

    // Show loading state
    elements.searchResults.innerHTML = getLoadingHTML();
    elements.searchResults.classList.add("active");

    // Debounce search
    searchTimeout = setTimeout(() => {
      performSearch(query, elements.searchResults);
    }, CONFIG.searchDebounceDelay);
  }

  /**
   * Handle Mobile Search
   */
  function handleMobileSearch(e) {
    const query = e.target.value.trim();

    // Clear previous timeout
    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }

    // If empty, hide results
    if (!query) {
      elements.mobileSearchResults.classList.remove("active");
      return;
    }

    // Show loading state
    elements.mobileSearchResults.innerHTML = getLoadingHTML();
    elements.mobileSearchResults.classList.add("active");

    // Debounce search
    searchTimeout = setTimeout(() => {
      performSearch(query, elements.mobileSearchResults);
    }, CONFIG.searchDebounceDelay);
  }

  /**
   * Perform Search API Call
   */
  async function performSearch(query, resultsContainer) {
    // Cancel previous request
    if (currentSearchController) {
      currentSearchController.abort();
    }

    // Create new controller for this request
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

      // Display results
      if (results.length === 0) {
        resultsContainer.innerHTML = getNoResultsHTML();
      } else {
        resultsContainer.innerHTML = getResultsHTML(results);
      }
    } catch (error) {
      if (error.name === "AbortError") {
        // Request was cancelled, ignore
        return;
      }

      console.error("Search error:", error);
      resultsContainer.innerHTML = getErrorHTML();
    }
  }

  /**
   * Toggle Mobile Search Expand
   */
  function toggleMobileSearch() {
    const isActive = elements.mobileSearchExpand.classList.toggle("active");

    if (isActive) {
      // Focus input after animation
      setTimeout(() => {
        elements.mobileSearchInput.focus();
      }, 300);
    } else {
      // Clear search
      elements.mobileSearchInput.value = "";
      elements.mobileSearchResults.classList.remove("active");
      elements.mobileSearchResults.innerHTML = "";
    }
  }

  // ==================== HTML TEMPLATES ====================

  /**
   * Get Loading HTML
   */
  function getLoadingHTML() {
    return `
            <div class="search-loading">
                <i class="fa fa-spinner"></i>
                <p>Đang tìm kiếm...</p>
            </div>
        `;
  }

  /**
   * Get No Results HTML
   */
  function getNoResultsHTML() {
    return `
            <div class="search-no-results">
                <i class="fa fa-search"></i>
                <p>Không tìm thấy kết quả</p>
            </div>
        `;
  }

  /**
   * Get Error HTML
   */
  function getErrorHTML() {
    return `
            <div class="search-no-results">
                <i class="fa fa-exclamation-triangle"></i>
                <p>Có lỗi xảy ra, vui lòng thử lại</p>
            </div>
        `;
  }

  /**
   * Get Results HTML
   */
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

  // ==================== MOBILE MENU ====================

  /**
   * Initialize Mobile Menu
   */
  function initMobileMenu() {
    if (elements.mobileMenuToggle) {
      elements.mobileMenuToggle.addEventListener("click", toggleMobileMenu);
    }

    // Handle dropdown toggles on mobile
    const dropdownToggles = document.querySelectorAll(
      ".has-dropdown > .dropdown-toggle"
    );
    dropdownToggles.forEach((toggle) => {
      toggle.addEventListener("click", handleMobileDropdown);
    });
  }

  /**
   * Toggle Mobile Menu
   */
  function toggleMobileMenu() {
    const isActive = elements.mainMenu.classList.toggle("active");

    // Toggle icon
    const icon = elements.mobileMenuToggle.querySelector("i");
    if (icon) {
      if (isActive) {
        icon.className = "fa fa-window-close";
      } else {
        icon.className = "fa fa-bars";
      }
    }

    // Close all dropdowns when closing menu
    if (!isActive) {
      const activeDropdowns = document.querySelectorAll(".has-dropdown.active");
      activeDropdowns.forEach((dropdown) => {
        dropdown.classList.remove("active");
      });
    }
  }

  /**
   * Handle Mobile Dropdown (Accordion style)
   */
  function handleMobileDropdown(e) {
    // Only apply accordion on mobile/tablet
    if (window.innerWidth > 1024) return;

    e.preventDefault();

    const parentLi = e.currentTarget.closest(".has-dropdown");
    const isActive = parentLi.classList.contains("active");

    // Close other dropdowns (accordion behavior)
    const allDropdowns = document.querySelectorAll(".has-dropdown");
    allDropdowns.forEach((dropdown) => {
      if (dropdown !== parentLi) {
        dropdown.classList.remove("active");
      }
    });

    // Toggle current dropdown
    parentLi.classList.toggle("active");
  }

  // ==================== SCROLL BEHAVIOR ====================

  /**
   * Handle Header Scroll
   */
  let lastScroll = 0;

  function handleScroll() {
    const currentScroll = window.pageYOffset;
    const header = document.querySelector(".site-header");

    if (currentScroll > lastScroll && currentScroll > 100) {
      // Scrolling down
      header.style.transform = "translateY(-100%)";
    } else {
      // Scrolling up
      header.style.transform = "translateY(0)";
    }

    lastScroll = currentScroll;
  }

  /**
   * Initialize Scroll Behavior
   */
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

  // ==================== GENRES LOADER ====================

  /**
   * Load Genres from API
   */
  async function loadGenres() {
    if (!elements.genresList) return;

    try {
      const response = await fetch(CONFIG.genresAPI + "?per_page=100");

      if (!response.ok) {
        throw new Error("Failed to load genres");
      }

      const genres = await response.json();

      // Sort genres by name
      genres.sort((a, b) => a.name.localeCompare(b.name, "vi"));

      // Generate HTML
      const genresHTML = genres
        .map((genre) => {
          return `<a href="${genre.link}" title="${genre.name}">${genre.name}</a>`;
        })
        .join("");

      // Update DOM
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

  // ==================== INITIALIZATION ====================

  /**
   * Initialize All Features
   */
  function init() {
    // Wait for DOM to be ready
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    // Initialize features
    initDarkMode();
    initSearch();
    initMobileMenu();
    loadGenres();
    // initScrollBehavior(); // Optional: Uncomment if you want auto-hide header on scroll

    console.log("TruyenQQ Header initialized successfully");
  }

  // Start initialization
  init();
})();
