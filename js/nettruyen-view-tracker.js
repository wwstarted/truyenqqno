/**
 * NetTruyen View Tracker - Frontend
 * Auto-track views khi user đọc chapter
 *
 * @version 1.0.0
 */

(function () {
  "use strict";

  // Config từ PHP
  const config = window.NettruyenViewTracker || {};
  const restUrl = config.restUrl || "/wp-json/nettruyen/v1/track-view";
  const nonce = config.nonce || "";

  /**
   * Track view cho chapter hiện tại
   *
   * @param {number} postId - ID truyện
   * @param {string} chapterSlug - Slug chapter
   */
  function trackView(postId, chapterSlug) {
    if (!postId || !chapterSlug) {
      console.error("NetTruyen Tracker: Missing postId or chapterSlug");
      return;
    }

    // Prepare data
    const data = {
      post_id: postId,
      chapter_slug: chapterSlug,
    };

    // Track via fetch API
    fetch(restUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": nonce,
      },
      body: JSON.stringify(data),
    })
      .then((response) => response.json())
      .then((result) => {
        if (result.success) {
          console.log(
            "✅ View tracked:",
            result.counted ? "COUNTED" : "ALREADY COUNTED"
          );
        } else {
          console.warn("⚠️ Tracking failed:", result.message);
        }
      })
      .catch((error) => {
        console.error("❌ Tracking error:", error);
      });
  }

  /**
   * Auto-detect và track view khi page load
   * Tìm data attributes: data-comic-id và data-chapter-slug
   */
  function autoTrackOnLoad() {
    // Tìm element có attributes
    const readerElement = document.querySelector(
      "[data-comic-id][data-chapter-slug]"
    );

    if (!readerElement) {
      console.log("NetTruyen Tracker: No reader element found (no auto-track)");
      return;
    }

    const postId = parseInt(readerElement.getAttribute("data-comic-id"), 10);
    const chapterSlug = readerElement.getAttribute("data-chapter-slug");

    if (postId && chapterSlug) {
      // Delay 2s để user actually đọc (không spam)
      setTimeout(() => {
        trackView(postId, chapterSlug);
      }, 2000);
    }
  }

  /**
   * Manual tracking function (có thể gọi từ custom code)
   *
   * Usage: NettruyenViewTracker.track(7, '1')
   */
  window.NettruyenViewTracker = window.NettruyenViewTracker || {};
  window.NettruyenViewTracker.track = trackView;

  // Auto-track when DOM ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", autoTrackOnLoad);
  } else {
    autoTrackOnLoad();
  }
})();
