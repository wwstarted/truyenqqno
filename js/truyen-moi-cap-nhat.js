/**
 * Page: Truyện Mới Cập Nhật
 * Bookmark functionality
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function () {
  "use strict";

  /**
   * Initialize Bookmark Buttons (UI Only)
   */
  function initBookmarkButtons() {
    const bookmarkButtons = document.querySelectorAll(
      ".list_grid .subscribed-badge"
    );

    bookmarkButtons.forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        // Toggle subscribed class
        if (this.classList.contains("not-subscribed")) {
          this.classList.remove("not-subscribed");
          this.classList.add("subscribed");
          this.querySelector("i").className = "fa fa-bookmark"; // Filled
          showToast("Đã thêm vào theo dõi");
        } else {
          this.classList.remove("subscribed");
          this.classList.add("not-subscribed");
          this.querySelector("i").className = "fa fa-bookmark-o"; // Empty
          showToast("Đã bỏ theo dõi");
        }
      });
    });
  }

  /**
   * Show Toast Notification
   */
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

  /**
   * Smooth scroll to top on pagination click
   */
  function initPaginationScroll() {
    const paginationLinks = document.querySelectorAll(".page_redirect a");

    paginationLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        // Only scroll if not clicking current page
        if (!this.querySelector("p.active")) {
          window.scrollTo({
            top: 0,
            behavior: "smooth",
          });
        }
      });
    });
  }

  /**
   * Initialize on DOM Ready
   */
  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    // Check if we're on the comics listing page
    const mainContainer = document.querySelector("#main_homepage");
    if (!mainContainer) {
      return;
    }

    // Initialize features
    initBookmarkButtons();
    initPaginationScroll();

    console.log("Truyện Mới Cập Nhật page initialized");
  }

  // Start initialization
  init();
})();
