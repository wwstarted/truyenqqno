/**
 * Top Ranking Comics (Daily/Weekly/Monthly)

 *
 * @package TruyenQQ
 * @version 1.0.1
 */

class TopRankingComics extends ComicsListingBase {
  constructor(config = {}) {
    super(config);
  }

  init() {
    super.init();

    if (this.elements.mainContainer) {
      const filterType = this.elements.mainContainer.dataset.filterType;
      if (filterType) {
        this.config.filterType = filterType;
        console.log("Top Ranking Type:", filterType);
      }
    }
  }

  /**
   * Override renderComicCard để thêm rank badge
   */
  renderComicCard(comic) {
    const hasRank = typeof comic.rank !== "undefined";

    let rankBadge = "";
    if (hasRank) {
      const iconHtml = comic.is_top3 ? '<i class="fa fa-trophy"></i> ' : "";
      rankBadge = `
        <span class="rank-badge ${comic.rank_class || ""}">
          ${iconHtml}#${comic.rank}
        </span>
      `;
    }

    return `
      <li>
        <div class="book_avatar">
          <a href="${comic.url}" title="${comic.title}">
            <img class="center" src="${comic.thumbnail}" alt="${comic.title}" loading="lazy">
          </a>
          
          ${rankBadge}
          
          <!-- FIXED: Bookmark badge -->
          <span class="bookmark-badge" title="Theo dõi" data-post-id="${comic.id}">
            <i class="fa fa-bookmark-o"></i>
          </span>
          
          <div class="top-notice">
            <span class="time-ago">${comic.time_ago}</span>
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
            <a href="${comic.url}" title="${comic.latest_chapter}">${comic.latest_chapter}</a>
          </div>
        </div>
        
        <div class="clear"></div>
      </li>
    `;
  }

  /**
   * ✅ Override renderComics để init global bookmarks
   */
  renderComics(comics) {
    super.renderComics(comics);

    if (typeof window.TruyenqqBookmarks !== "undefined") {
      window.TruyenqqBookmarks.init();
    }
  }

  /**
   * ✅ Override loadComics để check bookmark states
   */
  async loadComics() {
    await super.loadComics();

    if (typeof window.TruyenqqBookmarks !== "undefined") {
      window.TruyenqqBookmarks.check();
    }
  }
}

(function () {
  "use strict";

  function autoInit() {
    const mainContainer = document.querySelector("#main_homepage");
    if (!mainContainer) return;

    const filterType = mainContainer.dataset.filterType;

    if (!filterType || !filterType.startsWith("top-")) return;

    let apiEndpoint = "";
    if (typeof nettruyenData !== "undefined") {
      apiEndpoint = nettruyenData.restUrl;
    } else {
      switch (filterType) {
        case "top-ngay":
          apiEndpoint = "/wp-json/nettruyen/v1/comics/top-ngay";
          break;
        case "top-tuan":
          apiEndpoint = "/wp-json/nettruyen/v1/comics/top-tuan";
          break;
        case "top-thang":
          apiEndpoint = "/wp-json/nettruyen/v1/comics/top-thang";
          break;
        default:
          apiEndpoint = "/wp-json/nettruyen/v1/comics";
      }
    }

    new TopRankingComics({
      apiEndpoint: apiEndpoint,
      filterType: filterType,
      postsPerPage: 42,
    });

    console.log("🚀 Top Ranking initialized with global bookmarks");
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", autoInit);
  } else {
    autoInit();
  }
})();
