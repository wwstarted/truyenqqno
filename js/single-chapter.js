/**
 * Single Chapter Reading History Tracker
 * Auto-save reading progress when user reads chapter
 *
 * @package TruyenQQ
 * @version 1.0.1
 * ✅ Fixed: Proper REST API authentication
 */

(function () {
  "use strict";

  const API_BASE =
    "http://localhost/truyen_qqno/wordpress-6.8.3-vi/wordpress/wp-json/nettruyen/v1";
  let saveTimeout = null;
  let lastSavedPage = -1;
  let isScrolling = false;

  // Configuration
  const CONFIG = {
    saveDelay: 2000, // Save after 2 seconds of no scrolling
    minReadTime: 3000, // Minimum 3 seconds on a page before saving
  };

  /**
   * Initialize reading tracker
   */
  function init() {
    if (!isChapterReaderPage()) return;

    const readerData = getReaderData();
    if (!readerData) {
      console.warn("Cannot initialize reader: missing data");
      return;
    }

    console.log("📖 Reading tracker initialized:", readerData);

    // Track current page on scroll
    window.addEventListener("scroll", handleScroll);

    // Track when user leaves page
    window.addEventListener("beforeunload", saveCurrentProgress);

    // Save initial load
    setTimeout(() => {
      saveProgress(readerData, 0);
    }, CONFIG.minReadTime);

    // Observe images loading to track page changes
    observeImageVisibility(readerData);
  }

  /**
   * Check if current page is chapter reader
   */
  function isChapterReaderPage() {
    return document.getElementById("chapter_reader") !== null;
  }

  /**
   * Get reader data from page
   */
  function getReaderData() {
    const reader = document.getElementById("chapter_reader");
    if (!reader) return null;

    // Try to get data from PHP-rendered attributes
    const comicId = reader.getAttribute("data-comic-id");
    const chapterSlug = reader.getAttribute("data-chapter-slug");

    if (!comicId || !chapterSlug) {
      console.error("Missing comic-id or chapter-slug attributes");
      return null;
    }

    // Get chapter info from page title or breadcrumb
    const chapterName = extractChapterName();
    const totalPages = document.querySelectorAll(".page-chapter").length;

    return {
      post_id: parseInt(comicId),
      chapter_slug: chapterSlug,
      chapter_name: chapterName,
      total_pages: totalPages,
    };
  }

  /**
   * Extract chapter name from page
   */
  function extractChapterName() {
    // Try multiple selectors
    const titleElement = document.querySelector(".chapter-header h1");
    if (titleElement) {
      const match = titleElement.textContent.match(/Chương\s+([^\s]+)/i);
      if (match) return match[1];
    }

    // Fallback: from breadcrumb
    const breadcrumb = document.querySelector(".breadcrumb li:last-child");
    if (breadcrumb) {
      const match = breadcrumb.textContent.match(/Chương\s+([^\s]+)/i);
      if (match) return match[1];
    }

    return "Unknown";
  }

  /**
   * Get current visible page index
   */
  function getCurrentVisiblePage() {
    const pages = document.querySelectorAll(".page-chapter");
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const windowHeight = window.innerHeight;
    const scrollCenter = scrollTop + windowHeight / 2;

    for (let i = 0; i < pages.length; i++) {
      const page = pages[i];
      const rect = page.getBoundingClientRect();
      const pageTop = rect.top + scrollTop;
      const pageBottom = pageTop + rect.height;

      if (scrollCenter >= pageTop && scrollCenter <= pageBottom) {
        return i;
      }
    }

    // Fallback: check if at bottom
    if (
      scrollTop + windowHeight >=
      document.documentElement.scrollHeight - 100
    ) {
      return pages.length - 1;
    }

    return 0;
  }

  /**
   * Handle scroll event
   */
  function handleScroll() {
    isScrolling = true;

    clearTimeout(saveTimeout);

    saveTimeout = setTimeout(() => {
      isScrolling = false;
      const readerData = getReaderData();
      if (!readerData) return;

      const currentPage = getCurrentVisiblePage();

      // Only save if page changed
      if (currentPage !== lastSavedPage) {
        console.log(`📄 Page changed: ${lastSavedPage} → ${currentPage}`);
        saveProgress(readerData, currentPage);
        lastSavedPage = currentPage;
      }
    }, CONFIG.saveDelay);
  }

  /**
   * Save current progress before leaving
   */
  function saveCurrentProgress() {
    const readerData = getReaderData();
    if (!readerData) return;

    const currentPage = getCurrentVisiblePage();

    // Use synchronous XHR for beforeunload
    const xhr = new XMLHttpRequest();
    xhr.open("POST", `${API_BASE}/reading-history`, false); // false = synchronous
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.setRequestHeader("X-WP-Nonce", getRestNonce());

    const data = {
      post_id: readerData.post_id,
      chapter_slug: readerData.chapter_slug,
      chapter_name: readerData.chapter_name,
      current_page: currentPage,
      total_pages: readerData.total_pages,
    };

    xhr.send(JSON.stringify(data));
    console.log("💾 Progress saved on page unload:", currentPage);
  }

  /**
   * Get WordPress REST API nonce
   */
  function getRestNonce() {
    // Try to get from wp object
    if (typeof wpApiSettings !== "undefined" && wpApiSettings.nonce) {
      return wpApiSettings.nonce;
    }

    // Try to get from meta tag
    const nonceMeta = document.querySelector('meta[name="wp-rest-nonce"]');
    if (nonceMeta) {
      return nonceMeta.content;
    }

    // Fallback: get from cookies
    const cookies = document.cookie.split(";");
    for (let cookie of cookies) {
      const [name, value] = cookie.trim().split("=");
      if (name === "wordpress_logged_in") {
        return value;
      }
    }

    return "";
  }

  /**
   * Save reading progress to API
   */
  async function saveProgress(readerData, currentPage) {
    try {
      const response = await fetch(`${API_BASE}/reading-history`, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": getRestNonce(),
        },
        body: JSON.stringify({
          post_id: readerData.post_id,
          chapter_slug: readerData.chapter_slug,
          chapter_name: readerData.chapter_name,
          current_page: currentPage,
          total_pages: readerData.total_pages,
        }),
      });

      const result = await response.json();

      if (result.success) {
        if (result.guest_mode) {
          console.log("ℹ️ Guest mode: Progress not saved (login required)");
        } else {
          console.log(
            `✅ Saved: Page ${currentPage + 1}/${readerData.total_pages}`
          );
        }
      } else {
        console.warn("❌ Save failed:", result.message);
      }
    } catch (error) {
      console.error("❌ Save error:", error);
    }
  }

  /**
   * Observe image visibility with IntersectionObserver
   */
  function observeImageVisibility(readerData) {
    const images = document.querySelectorAll(".page-chapter img.chapter-image");

    if (!images.length) {
      console.warn("No chapter images found");
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && entry.intersectionRatio > 0.5) {
            const pageDiv = entry.target.closest(".page-chapter");
            if (pageDiv) {
              const pageId = pageDiv.id;
              const pageIndex = parseInt(pageId.replace("page_", ""));

              if (!isNaN(pageIndex) && pageIndex !== lastSavedPage) {
                console.log(`👁️ Page ${pageIndex} is visible (>50%)`);

                // Save after minimum read time
                setTimeout(() => {
                  if (!isScrolling) {
                    saveProgress(readerData, pageIndex);
                    lastSavedPage = pageIndex;
                  }
                }, CONFIG.minReadTime);
              }
            }
          }
        });
      },
      {
        threshold: 0.5, // Trigger when 50% visible
        rootMargin: "0px",
      }
    );

    images.forEach((img) => observer.observe(img));
    console.log(`👀 Observing ${images.length} images`);
  }

  /**
   * Auto-initialize on DOM ready
   */
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
