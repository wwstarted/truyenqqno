/**
 * Truyện Hay Carousel - Swiper.js Implementation
 * Responsive breakpoints: Desktop (6), Tablet (4), Mobile (2)
 *
 * @package TruyenQQ
 * @version 1.0.0
 * @requires Swiper.js 11.x
 */

(function () {
  "use strict";

  /**
   * Initialize Swiper Carousel
   */
  function initTruyenHayCarousel() {
    const swiperContainer = document.querySelector(".truyen-hay-swiper");

    if (!swiperContainer) {
      console.warn("Truyen Hay carousel not found");
      return;
    }

    // Swiper Configuration
    const swiper = new Swiper(".truyen-hay-swiper", {
      // Loop mode
      loop: true,

      // Slides per view (responsive)
      slidesPerView: 2, // Default: Mobile
      spaceBetween: 15,

      // Speed
      speed: 400,

      // Slides per group (scroll 1 item at a time)
      slidesPerGroup: 1,

      // Autoplay (optional)
      // autoplay: {
      //     delay: 5000,
      //     disableOnInteraction: false,
      //     pauseOnMouseEnter: true
      // },

      // Navigation
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },

      // Responsive breakpoints
      breakpoints: {
        // Mobile: ≥390px → 2 slides
        390: {
          slidesPerView: 2,
          spaceBetween: 15,
        },

        // Tablet: ≥768px → 4 slides
        768: {
          slidesPerView: 4,
          spaceBetween: 20,
        },

        // Desktop: ≥1024px → 6 slides
        1024: {
          slidesPerView: 6,
          spaceBetween: 20,
        },
      },

      // Lazy loading
      lazy: {
        loadPrevNext: true,
        loadPrevNextAmount: 2,
      },

      // Keyboard control
      keyboard: {
        enabled: true,
        onlyInViewport: true,
      },

      // Mousewheel (optional)
      // mousewheel: {
      //     forceToAxis: true,
      // },

      // Grab cursor
      grabCursor: true,

      // Watch overflow
      watchOverflow: true,

      // Prevent clicks on slide transition
      preventClicksPropagation: true,
      preventClicks: true,

      // On init
      on: {
        init: function () {
          console.log("Truyen Hay carousel initialized");
        },
      },
    });

    // Optional: Add bookmark functionality
    initBookmarkButtons();

    return swiper;
  }

  /**
   * Initialize Bookmark Buttons (UI Only - No Backend)
   */
  function initBookmarkButtons() {
    const bookmarkButtons = document.querySelectorAll(
      ".homepage-suggest .bookmark-badge"
    );

    bookmarkButtons.forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        // Toggle active class
        this.classList.toggle("active");

        // Change icon
        const icon = this.querySelector("i");
        if (this.classList.contains("active")) {
          icon.className = "fa fa-bookmark"; // Filled bookmark
          showToast("Đã thêm vào theo dõi");
        } else {
          icon.className = "fa fa-bookmark-o"; // Empty bookmark
          showToast("Đã bỏ theo dõi");
        }
      });
    });
  }

  /**
   * Show Toast Notification (Simple)
   */
  function showToast(message) {
    // Check if toast container exists
    let toastContainer = document.querySelector(".toast-container");

    if (!toastContainer) {
      toastContainer = document.createElement("div");
      toastContainer.className = "toast-container";
      document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toast = document.createElement("div");
    toast.className = "toast";
    toast.textContent = message;

    toastContainer.appendChild(toast);

    // Show toast
    setTimeout(() => {
      toast.classList.add("show");
    }, 10);

    // Hide and remove toast
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => {
        toast.remove();
      }, 300);
    }, 2000);
  }

  /**
   * Initialize on DOM Ready
   */
  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    // Wait for Swiper library to load
    if (typeof Swiper === "undefined") {
      console.error(
        "Swiper.js not loaded! Please enqueue Swiper library first."
      );
      return;
    }

    // Initialize carousel
    initTruyenHayCarousel();
  }

  // Start initialization
  init();
})();

/**
 * Toast CSS (Add to your global CSS or inline here)
 *
 * .toast-container {
 *   position: fixed;
 *   bottom: 30px;
 *   left: 50%;
 *   transform: translateX(-50%);
 *   z-index: 9999;
 *   display: flex;
 *   flex-direction: column;
 *   gap: 10px;
 *   pointer-events: none;
 * }
 *
 * .toast {
 *   background-color: rgba(0, 0, 0, 0.85);
 *   color: #fff;
 *   padding: 12px 24px;
 *   border-radius: 25px;
 *   font-size: 14px;
 *   opacity: 0;
 *   transform: translateY(20px);
 *   transition: all 0.3s ease;
 *   white-space: nowrap;
 *   box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
 * }
 *
 * body.dark-mode .toast {
 *   background-color: rgba(255, 255, 255, 0.95);
 *   color: #000;
 * }
 *
 * .toast.show {
 *   opacity: 1;
 *   transform: translateY(0);
 * }
 */

/**
 * Độc Quyền Truyện QQ - Swiper.js Implementation
 * Responsive breakpoints: Desktop (6), Tablet (4), Mobile (2)
 *
 * @package TruyenQQ
 * @version 1.0.0
 * @requires Swiper.js 11.x
 */

(function () {
  "use strict";

  /**
   * Initialize Swiper Carousel
   */
  function initExclusiveCarousel() {
    const swiperContainer = document.querySelector(".exclusive-swiper");

    if (!swiperContainer) {
      console.warn("Exclusive carousel not found");
      return;
    }

    // Swiper Configuration
    const swiper = new Swiper(".exclusive-swiper", {
      // Loop mode
      loop: true,

      // Slides per view (responsive)
      slidesPerView: 2, // Default: Mobile
      spaceBetween: 15,

      // Speed
      speed: 400,

      // Slides per group (scroll 1 item at a time)
      slidesPerGroup: 1,

      // Autoplay (optional)
      // autoplay: {
      //     delay: 5000,
      //     disableOnInteraction: false,
      //     pauseOnMouseEnter: true
      // },

      // Navigation
      navigation: {
        nextEl: ".homepage-exclusive .swiper-button-next",
        prevEl: ".homepage-exclusive .swiper-button-prev",
      },

      // Responsive breakpoints
      breakpoints: {
        // Mobile: ≥390px → 2 slides
        390: {
          slidesPerView: 2,
          spaceBetween: 15,
        },

        // Tablet: ≥768px → 4 slides
        768: {
          slidesPerView: 4,
          spaceBetween: 20,
        },

        // Desktop: ≥1024px → 6 slides
        1024: {
          slidesPerView: 6,
          spaceBetween: 20,
        },
      },

      // Lazy loading
      lazy: {
        loadPrevNext: true,
        loadPrevNextAmount: 2,
      },

      // Keyboard control
      keyboard: {
        enabled: true,
        onlyInViewport: true,
      },

      // Mousewheel (optional)
      // mousewheel: {
      //     forceToAxis: true,
      // },

      // Grab cursor
      grabCursor: true,

      // Watch overflow
      watchOverflow: true,

      // Prevent clicks on slide transition
      preventClicksPropagation: true,
      preventClicks: true,

      // On init
      on: {
        init: function () {
          console.log("Exclusive carousel initialized");
        },
      },
    });

    // Optional: Add bookmark functionality
    initBookmarkButtons();

    return swiper;
  }

  /**
   * Initialize Bookmark Buttons (UI Only - No Backend)
   */
  function initBookmarkButtons() {
    const bookmarkButtons = document.querySelectorAll(
      ".homepage-exclusive .bookmark-badge"
    );

    bookmarkButtons.forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        // Toggle active class
        this.classList.toggle("active");

        // Change icon
        const icon = this.querySelector("i");
        if (this.classList.contains("active")) {
          icon.className = "fa fa-bookmark"; // Filled bookmark
          showToast("Đã thêm vào theo dõi");
        } else {
          icon.className = "fa fa-bookmark-o"; // Empty bookmark
          showToast("Đã bỏ theo dõi");
        }
      });
    });
  }

  /**
   * Show Toast Notification (Simple)
   */
  function showToast(message) {
    // Check if toast container exists
    let toastContainer = document.querySelector(".toast-container");

    if (!toastContainer) {
      toastContainer = document.createElement("div");
      toastContainer.className = "toast-container";
      document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toast = document.createElement("div");
    toast.className = "toast";
    toast.textContent = message;

    toastContainer.appendChild(toast);

    // Show toast
    setTimeout(() => {
      toast.classList.add("show");
    }, 10);

    // Hide and remove toast
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => {
        toast.remove();
      }, 300);
    }, 2000);
  }

  /**
   * Initialize on DOM Ready
   */
  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    // Wait for Swiper library to load
    if (typeof Swiper === "undefined") {
      console.error(
        "Swiper.js not loaded! Please enqueue Swiper library first."
      );
      return;
    }

    // Initialize carousel
    initExclusiveCarousel();
  }

  // Start initialization
  init();
})();

/**
 * Truyện Mới Cập Nhật - Grid Layout (No Carousel)
 * Simple bookmark functionality
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function () {
  "use strict";

  /**
   * Initialize Bookmark Buttons (UI Only - No Backend)
   */
  function initBookmarkButtons() {
    const bookmarkButtons = document.querySelectorAll(
      ".homepage-new-update .bookmark-badge"
    );

    bookmarkButtons.forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        // Toggle active class
        this.classList.toggle("active");

        // Change icon
        const icon = this.querySelector("i");
        if (this.classList.contains("active")) {
          icon.className = "fa fa-bookmark"; // Filled bookmark
          showToast("Đã thêm vào theo dõi");
        } else {
          icon.className = "fa fa-bookmark-o"; // Empty bookmark
          showToast("Đã bỏ theo dõi");
        }
      });
    });
  }

  /**
   * Show Toast Notification (Simple)
   */
  function showToast(message) {
    // Check if toast container exists
    let toastContainer = document.querySelector(".toast-container");

    if (!toastContainer) {
      toastContainer = document.createElement("div");
      toastContainer.className = "toast-container";
      document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toast = document.createElement("div");
    toast.className = "toast";
    toast.textContent = message;

    toastContainer.appendChild(toast);

    // Show toast
    setTimeout(() => {
      toast.classList.add("show");
    }, 10);

    // Hide and remove toast
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => {
        toast.remove();
      }, 300);
    }, 2000);
  }

  /**
   * Initialize on DOM Ready
   */
  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    // Check if section exists
    const section = document.querySelector(".homepage-new-update");
    if (!section) {
      console.warn("Truyện Mới Cập Nhật section not found");
      return;
    }

    // Initialize bookmark buttons
    initBookmarkButtons();

    console.log("Truyện Mới Cập Nhật initialized");
  }

  // Start initialization
  init();
})();
