/**
 * 404 Page JavaScript - TruyenQQ
 * Search functionality & Animations
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function () {
  "use strict";

  const CONFIG = {
    searchAPI: TRUYENQQ_CONFIG.restUrl + "nettruyen/v1/search",
    searchDebounceDelay: 500,
    maxSearchResults: 10,
  };

  const elements = {
    searchInput: document.getElementById("search404Input"),
    searchBtn: document.getElementById("search404Btn"),
    searchResults: document.getElementById("searchResults404"),
  };

  let searchTimeout = null;
  let currentSearchController = null;

  /* =====================================================
     SEARCH FUNCTIONALITY
  ===================================================== */

  function initSearch() {
    if (!elements.searchInput || !elements.searchBtn) {
      return;
    }

    elements.searchInput.addEventListener("input", handleSearchInput);

    elements.searchBtn.addEventListener("click", handleSearchClick);

    elements.searchInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        handleSearchClick();
      }
    });

    document.addEventListener("click", function (e) {
      if (!e.target.closest(".error-404-search") && elements.searchResults) {
        elements.searchResults.classList.remove("active");
      }
    });
  }

  function handleSearchInput(e) {
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
      performSearch(query);
    }, CONFIG.searchDebounceDelay);
  }

  function handleSearchClick() {
    const query = elements.searchInput.value.trim();

    if (!query) {
      elements.searchInput.focus();
      return;
    }

    window.location.href = `${TRUYENQQ_CONFIG.homeUrl}/tim-kiem?q=${encodeURIComponent(query)}`;
  }

  async function performSearch(query) {
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
        elements.searchResults.innerHTML = getNoResultsHTML();
      } else {
        elements.searchResults.innerHTML = getResultsHTML(results);
      }
    } catch (error) {
      if (error.name === "AbortError") {
        return;
      }

      console.error("Search error:", error);
      elements.searchResults.innerHTML = getErrorHTML();
    }
  }

  function getLoadingHTML() {
    return `
      <div class="search-loading" style="padding: 30px; text-align: center;">
        <i class="fa fa-spinner fa-spin" style="font-size: 30px; color: var(--color-blue-primary);"></i>
        <p style="margin-top: 10px; color: var(--text-muted);">Đang tìm kiếm...</p>
      </div>
    `;
  }

  function getNoResultsHTML() {
    return `
      <div class="search-no-results" style="padding: 30px; text-align: center;">
        <i class="fa fa-search" style="font-size: 40px; color: var(--text-muted); margin-bottom: 10px;"></i>
        <p style="color: var(--text-muted); font-size: 14px;">Không tìm thấy kết quả phù hợp</p>
      </div>
    `;
  }

  function getErrorHTML() {
    return `
      <div class="search-error" style="padding: 30px; text-align: center;">
        <i class="fa fa-exclamation-triangle" style="font-size: 40px; color: #ff6b6b; margin-bottom: 10px;"></i>
        <p style="color: var(--text-muted); font-size: 14px;">Có lỗi xảy ra, vui lòng thử lại</p>
      </div>
    `;
  }

  function getResultsHTML(results) {
    const limitedResults = results.slice(0, CONFIG.maxSearchResults);

    const itemsHTML = limitedResults
      .map((item) => {
        const thumbnail =
          item.thumbnail || "https://via.placeholder.com/60x80?text=No+Image";
        const chapter = item.latest_chapter || "Đang cập nhật";

        return `
          <a href="${item.link}" class="search-result-item-404" style="
            display: flex;
            gap: 15px;
            padding: 12px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
            text-decoration: none;
            color: inherit;
          " onmouseover="this.style.backgroundColor='rgba(74, 158, 255, 0.1)'" 
             onmouseout="this.style.backgroundColor='transparent'">
            <div class="search-result-avatar" style="
              width: 60px;
              height: 80px;
              border-radius: 5px;
              overflow: hidden;
              flex-shrink: 0;
              background-color: rgba(0, 0, 0, 0.1);
            ">
              <img src="${thumbnail}" 
                   alt="${item.title}"
                   style="width: 100%; height: 100%; object-fit: cover;"
                   onerror="this.src='https://via.placeholder.com/60x80?text=No+Image'">
            </div>
            <div class="search-result-info" style="
              flex: 1;
              display: flex;
              flex-direction: column;
              justify-content: center;
              gap: 5px;
              min-width: 0;
            ">
              <div class="search-result-title" style="
                font-size: 15px;
                font-weight: 600;
                color: var(--text-primary);
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
              ">${item.title}</div>
              <div class="search-result-chapter" style="
                font-size: 13px;
                color: var(--color-blue-primary);
                font-weight: 500;
              ">${chapter}</div>
            </div>
          </a>
        `;
      })
      .join("");

    return `<div class="search-results-list" style="padding: 10px;">${itemsHTML}</div>`;
  }

  /* =====================================================
     PARTICLE ANIMATION (Optional Enhancement)
  ===================================================== */

  function createParticles() {
    const decorations = document.querySelector(".error-404-decorations");
    if (!decorations) return;

    // Create subtle floating particles
    for (let i = 0; i < 10; i++) {
      const particle = document.createElement("div");
      particle.className = "particle";
      particle.style.cssText = `
        position: absolute;
        width: ${Math.random() * 4 + 2}px;
        height: ${Math.random() * 4 + 2}px;
        background: var(--color-blue-primary);
        border-radius: 50%;
        opacity: ${Math.random() * 0.3 + 0.1};
        left: ${Math.random() * 100}%;
        top: ${Math.random() * 100}%;
        animation: particleFloat ${Math.random() * 10 + 15}s linear infinite;
        animation-delay: ${Math.random() * -20}s;
      `;
      decorations.appendChild(particle);
    }
  }

  // Add particle animation CSS
  const particleStyle = document.createElement("style");
  particleStyle.textContent = `
    @keyframes particleFloat {
      0% {
        transform: translate(0, 0) scale(1);
        opacity: 0;
      }
      10% {
        opacity: 0.3;
      }
      90% {
        opacity: 0.3;
      }
      100% {
        transform: translate(${Math.random() * 200 - 100}px, -100vh) scale(0);
        opacity: 0;
      }
    }
  `;
  document.head.appendChild(particleStyle);

  /* =====================================================
     EASTER EGG - KONAMI CODE
  ===================================================== */

  const konamiCode = [
    "ArrowUp",
    "ArrowUp",
    "ArrowDown",
    "ArrowDown",
    "ArrowLeft",
    "ArrowRight",
    "ArrowLeft",
    "ArrowRight",
    "b",
    "a",
  ];
  let konamiIndex = 0;

  function initEasterEgg() {
    document.addEventListener("keydown", function (e) {
      if (e.key === konamiCode[konamiIndex]) {
        konamiIndex++;
        if (konamiIndex === konamiCode.length) {
          activateEasterEgg();
          konamiIndex = 0;
        }
      } else {
        konamiIndex = 0;
      }
    });
  }

  function activateEasterEgg() {
    const page = document.querySelector(".error-404-page");
    page.style.background =
      "linear-gradient(45deg, #ff0080, #ff8c00, #40e0d0, #ff0080)";
    page.style.backgroundSize = "400% 400%";
    page.style.animation = "rainbowGradient 5s ease infinite";

    const style = document.createElement("style");
    style.textContent = `
      @keyframes rainbowGradient {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
      }
    `;
    document.head.appendChild(style);

    showToast("🎉 Bạn đã tìm thấy Easter Egg! 🎉", "success");

    setTimeout(() => {
      page.style.background = "";
      page.style.animation = "";
      style.remove();
    }, 10000);
  }

  /* =====================================================
     TOAST NOTIFICATION
  ===================================================== */

  function showToast(message, type = "info") {
    let toastContainer = document.querySelector(".toast-container");

    if (!toastContainer) {
      toastContainer = document.createElement("div");
      toastContainer.className = "toast-container";
      document.body.appendChild(toastContainer);
    }

    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;

    const icon =
      type === "success"
        ? "check-circle"
        : type === "error"
          ? "exclamation-circle"
          : "info-circle";

    toast.innerHTML = `
      <i class="fa fa-${icon}"></i>
      <span>${message}</span>
    `;

    toastContainer.appendChild(toast);

    setTimeout(() => {
      toast.classList.add("show");
    }, 10);

    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => {
        toast.remove();
      }, 300);
    }, 3000);
  }

  /* =====================================================
     INITIALIZATION
  ===================================================== */

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    initSearch();
    createParticles();
    initEasterEgg();

    console.log("✅ 404 Page initialized successfully");
  }

  init();
})();
