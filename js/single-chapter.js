/**
 * Single Chapter Reading History Tracker
 * Auto-save reading progress when user reads chapter
 *
 * @package TruyenQQ
 * @version 1.0.1

 */

(function () {
  "use strict";

  function getApiBase() {
    if (typeof truyenqqConfig !== "undefined" && truyenqqConfig.apiBase) {
      return truyenqqConfig.apiBase;
    }

    const origin = window.location.origin;
    const pathname = window.location.pathname;

    let wpRoot = "/";
    if (pathname.includes("/wordpress/")) {
      wpRoot = pathname.substring(0, pathname.indexOf("/wordpress/") + 11);
    } else if (
      pathname.includes("/wp-admin/") ||
      pathname.includes("/wp-content/")
    ) {
      const parts = pathname.split("/");
      const wpIndex = parts.findIndex(
        (p) => p === "wp-admin" || p === "wp-content",
      );
      wpRoot = parts.slice(0, wpIndex).join("/") + "/";
    }

    return origin + wpRoot + "wp-json/nettruyen/v1";
  }

  const API_BASE = getApiBase();
  let saveTimeout = null;
  let lastSavedPage = -1;
  let isScrolling = false;

  const CONFIG = {
    saveDelay: 2000,
    minReadTime: 3000,
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

    window.addEventListener("scroll", handleScroll);

    window.addEventListener("beforeunload", saveCurrentProgress);

    setTimeout(() => {
      saveProgress(readerData, 0);
    }, CONFIG.minReadTime);

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

    const comicId = reader.getAttribute("data-comic-id");
    const chapterSlug = reader.getAttribute("data-chapter-slug");

    if (!comicId || !chapterSlug) {
      console.error("Missing comic-id or chapter-slug attributes");
      return null;
    }

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
    const titleElement = document.querySelector(".chapter-header h1");
    if (titleElement) {
      const match = titleElement.textContent.match(/Chương\s+([^\s]+)/i);
      if (match) return match[1];
    }

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

    const xhr = new XMLHttpRequest();
    xhr.open("POST", `${API_BASE}/reading-history`, false);
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
    if (typeof wpApiSettings !== "undefined" && wpApiSettings.nonce) {
      return wpApiSettings.nonce;
    }

    const nonceMeta = document.querySelector('meta[name="wp-rest-nonce"]');
    if (nonceMeta) {
      return nonceMeta.content;
    }

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
            `✅ Saved: Page ${currentPage + 1}/${readerData.total_pages}`,
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
        threshold: 0.5,
        rootMargin: "0px",
      },
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
