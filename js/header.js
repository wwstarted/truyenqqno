/**
 * TruyenQQ Header JavaScript - COMPLETE FIXED VERSION
 * Features: Dark Mode, Search, Responsive Menu, User Authentication
 */

(function () {
  "use strict";

  // ==================== CONFIGURATION ====================
  const CONFIG = {
    searchAPI: TRUYENQQ_CONFIG.restUrl + "nettruyen/v1/search",
    genresAPI: TRUYENQQ_CONFIG.restUrl + "wp/v2/nettruyen_genre",
    userInfoAPI: TRUYENQQ_CONFIG.restUrl + "nettruyen/v1/user/info",
    notificationsAPI:
      TRUYENQQ_CONFIG.restUrl + "nettruyen/v1/user/notifications",
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

    // User Authentication
    authButtons: document.getElementById("authButtons"),
    userMenu: document.getElementById("userMenu"),
    userAvatarImg: document.getElementById("userAvatarImg"),
    userDropdownAvatar: document.getElementById("userDropdownAvatar"),
    userName: document.getElementById("userName"),
    userEmail: document.getElementById("userEmail"),
    logoutBtn: document.getElementById("logoutBtn"),
    notificationList: document.getElementById("notificationList"),
    notificationBadge: document.getElementById("notificationBadge"),
  };

  // ==================== DARK MODE ====================

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

  // ==================== SEARCH FUNCTIONALITY ====================

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

  // ==================== HTML TEMPLATES ====================

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

    const allDropdowns = document.querySelectorAll(".has-dropdown");
    allDropdowns.forEach((dropdown) => {
      if (dropdown !== parentLi) {
        dropdown.classList.remove("active");
      }
    });

    parentLi.classList.toggle("active");
  }

  // ==================== GENRES LOADER ====================

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

  // ==================== USER AUTHENTICATION ====================

  async function checkUserLoginStatus() {
    if (!elements.authButtons || !elements.userMenu) return;

    // Kiểm tra xem PHP đã cho hiển thị menu chưa
    const isAlreadyLoggedIn =
      elements.userMenu.style.display === "flex" ||
      window.getComputedStyle(elements.userMenu).display === "flex";

    try {
      const response = await fetch(CONFIG.userInfoAPI, {
        method: "GET",
        headers: {
          "X-WP-Nonce": TRUYENQQ_CONFIG.nonce, // Quan trọng: Phải có nonce từ bước 1
          "Content-Type": "application/json",
        },
        credentials: "same-origin",
      });

      if (!response.ok) return;

      const data = await response.json();

      if (data.is_logged_in && data.user) {
        // Cập nhật thông tin mượt mà
        updateUserInfo(data.user);
        loadUserNotifications();

        // Đảm bảo hiển thị đúng (trong trường hợp PHP chưa kịp render)
        elements.authButtons.style.setProperty("display", "none", "important");
        elements.userMenu.style.display = "flex";
      } else {
        // CHỈ ẩn menu nếu thực sự không có session (User đã thoát)
        if (isAlreadyLoggedIn) {
          elements.authButtons.style.display = "flex";
          elements.userMenu.style.setProperty("display", "none", "important");
        }
      }
    } catch (error) {
      console.error("Lỗi xác thực API:", error);
      // Nếu lỗi mạng, giữ nguyên trạng thái hiện tại, không nhảy menu
    }
  }

  function updateUserInfo(user) {
    if (elements.userAvatarImg) {
      elements.userAvatarImg.src = user.avatar;
      elements.userAvatarImg.alt = user.display_name;
    }

    if (elements.userDropdownAvatar) {
      elements.userDropdownAvatar.src = user.avatar;
      elements.userDropdownAvatar.alt = user.display_name;
    }

    if (elements.userName) {
      elements.userName.textContent = user.display_name || user.username;
    }

    if (elements.userEmail) {
      elements.userEmail.textContent = user.email;
    }

    if (elements.logoutBtn && user.links && user.links.logout) {
      elements.logoutBtn.href = user.links.logout;
    }
  }

  async function loadUserNotifications() {
    if (!elements.notificationList || !elements.notificationBadge) {
      return;
    }

    try {
      const response = await fetch(CONFIG.notificationsAPI);

      if (!response.ok) {
        throw new Error("Failed to fetch notifications");
      }

      const data = await response.json();

      if (data.success && data.notifications && data.notifications.length > 0) {
        displayNotifications(data.notifications);

        if (data.unread_count > 0) {
          elements.notificationBadge.textContent = data.unread_count;
          elements.notificationBadge.style.display = "block";
        } else {
          elements.notificationBadge.style.display = "none";
        }
      } else {
        elements.notificationList.innerHTML =
          '<li class="no-notification">Không có thông báo nào!</li>';
        elements.notificationBadge.style.display = "none";
      }
    } catch (error) {
      console.error("Error loading notifications:", error);
      elements.notificationList.innerHTML =
        '<li class="no-notification">Không thể tải thông báo</li>';
    }
  }

  function displayNotifications(notifications) {
    const notificationsHTML = notifications
      .map((notification) => {
        const unreadClass = notification.is_read ? "" : "unread";
        return `
          <li class="${unreadClass}">
            <a href="${notification.link}" class="notification-item">
              <div class="notification-title">${notification.title}</div>
              <div class="notification-time">${notification.time}</div>
            </a>
          </li>
        `;
      })
      .join("");

    elements.notificationList.innerHTML = notificationsHTML;
  }

  function handleLogout(e) {
    e.preventDefault();

    if (confirm("Bạn có chắc chắn muốn đăng xuất?")) {
      window.location.href = elements.logoutBtn.href;
    }

    // if (confirm("Bạn có chắc chắn muốn đăng xuất?")) {
    //   // Thay vì dùng href, ta có thể dùng trực tiếp link logout từ config hoặc ép redirect
    //   const logoutUrl = elements.logoutBtn.href;

    //   // Nếu trong link chưa có redirect_to, ta có thể nối thêm hoặc dùng filter PHP hỗ trợ
    //   window.location.href = logoutUrl;
    // }
  }

  function initUserMenu() {
    checkUserLoginStatus();

    if (elements.logoutBtn) {
      elements.logoutBtn.addEventListener("click", handleLogout);
    }

    // Mobile dropdown handling
    document.addEventListener("click", function (e) {
      if (window.innerWidth > 1024) return;

      const notificationBell = document.querySelector(".notification-bell");
      const userProfile = document.querySelector(".user-profile");
      const notificationDropdown = document.getElementById(
        "notificationDropdown"
      );
      const userDropdown = document.getElementById("userDropdown");

      // Close both dropdowns when clicking outside
      if (
        notificationBell &&
        !notificationBell.contains(e.target) &&
        notificationDropdown
      ) {
        notificationDropdown.style.opacity = "0";
        notificationDropdown.style.visibility = "hidden";
      }

      if (userProfile && !userProfile.contains(e.target) && userDropdown) {
        userDropdown.style.opacity = "0";
        userDropdown.style.visibility = "hidden";
      }
    });

    // Toggle dropdowns on mobile
    const notificationIcon = document.querySelector(".icon-notification");
    const userAvatar = document.querySelector(".user-avatar");

    if (notificationIcon) {
      notificationIcon.addEventListener("click", function (e) {
        if (window.innerWidth > 1024) return;

        e.stopPropagation();
        const dropdown = document.getElementById("notificationDropdown");
        const userDropdown = document.getElementById("userDropdown");

        if (!dropdown) return;

        const isVisible = dropdown.style.opacity === "1";

        // Close user dropdown
        if (userDropdown) {
          userDropdown.style.opacity = "0";
          userDropdown.style.visibility = "hidden";
        }

        // Toggle notification dropdown
        if (isVisible) {
          dropdown.style.opacity = "0";
          dropdown.style.visibility = "hidden";
        } else {
          dropdown.style.opacity = "1";
          dropdown.style.visibility = "visible";
        }
      });
    }

    if (userAvatar) {
      userAvatar.addEventListener("click", function (e) {
        if (window.innerWidth > 1024) return;

        e.stopPropagation();
        const dropdown = document.getElementById("userDropdown");
        const notificationDropdown = document.getElementById(
          "notificationDropdown"
        );

        if (!dropdown) return;

        const isVisible = dropdown.style.opacity === "1";

        // Close notification dropdown
        if (notificationDropdown) {
          notificationDropdown.style.opacity = "0";
          notificationDropdown.style.visibility = "hidden";
        }

        // Toggle user dropdown
        if (isVisible) {
          dropdown.style.opacity = "0";
          dropdown.style.visibility = "hidden";
        } else {
          dropdown.style.opacity = "1";
          dropdown.style.visibility = "visible";
        }
      });
    }
  }

  // ==================== INITIALIZATION ====================

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    initDarkMode();
    initSearch();
    initMobileMenu();
    initUserMenu();
    loadGenres();

    console.log("TruyenQQ Header initialized successfully");
  }

  init();
})();
