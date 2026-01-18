/**
 * TruyenQQ Homepage - Complete JavaScript Implementation
 * Includes: Truyện Hay, Độc Quyền, Truyện Mới, Blog Detail, Featured Articles
 *
 * @package TruyenQQ
 * @version 2.0.0
 * @requires Swiper.js 11.x
 */

/* =====================================================
   SECTION 1: TRUYỆN HAY CAROUSEL
===================================================== */

(function () {
  "use strict";

  function initTruyenHayCarousel() {
    const swiperContainer = document.querySelector(".truyen-hay-swiper");

    if (!swiperContainer) {
      console.warn("Truyen Hay carousel not found");
      return;
    }

    const swiper = new Swiper(".truyen-hay-swiper", {
      loop: true,

      slidesPerView: 2,
      spaceBetween: 15,

      speed: 400,

      slidesPerGroup: 1,

      navigation: {
        nextEl: ".homepage-suggest .swiper-button-next",
        prevEl: ".homepage-suggest .swiper-button-prev",
      },

      breakpoints: {
        390: {
          slidesPerView: 2,
          spaceBetween: 15,
        },

        768: {
          slidesPerView: 4,
          spaceBetween: 20,
        },

        1024: {
          slidesPerView: 6,
          spaceBetween: 20,
        },
      },

      lazy: {
        loadPrevNext: true,
        loadPrevNextAmount: 2,
      },

      keyboard: {
        enabled: true,
        onlyInViewport: true,
      },

      grabCursor: true,

      watchOverflow: true,

      preventClicksPropagation: true,
      preventClicks: true,

      on: {
        init: function () {
          console.log("✅ Truyen Hay carousel initialized");
        },
      },
    });

    initBookmarkButtons(".homepage-suggest");

    return swiper;
  }

  function initBookmarkButtons(sectionClass) {
    const bookmarkButtons = document.querySelectorAll(
      `${sectionClass} .bookmark-badge`,
    );

    bookmarkButtons.forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        this.classList.toggle("active");

        const icon = this.querySelector("i");
        if (this.classList.contains("active")) {
          icon.className = "fa fa-bookmark";
          showToast("Đã thêm vào theo dõi");
        } else {
          icon.className = "fa fa-bookmark-o";
          showToast("Đã bỏ theo dõi");
        }
      });
    });
  }

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

    setTimeout(() => {
      toast.classList.add("show");
    }, 10);

    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => {
        toast.remove();
      }, 300);
    }, 2000);
  }

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    if (typeof Swiper === "undefined") {
      console.error(
        "❌ Swiper.js not loaded! Please enqueue Swiper library first.",
      );
      return;
    }

    initTruyenHayCarousel();
  }

  init();
})();

/* =====================================================
   SECTION 2: ĐỘC QUYỀN TRUYỆN QQ CAROUSEL
===================================================== */

(function () {
  "use strict";

  function initExclusiveCarousel() {
    const swiperContainer = document.querySelector(".exclusive-swiper");

    if (!swiperContainer) {
      console.warn("Exclusive carousel not found");
      return;
    }

    const swiper = new Swiper(".exclusive-swiper", {
      loop: true,

      slidesPerView: 2,
      spaceBetween: 15,

      speed: 400,

      slidesPerGroup: 1,

      navigation: {
        nextEl: ".homepage-exclusive .swiper-button-next",
        prevEl: ".homepage-exclusive .swiper-button-prev",
      },

      breakpoints: {
        390: {
          slidesPerView: 2,
          spaceBetween: 15,
        },

        768: {
          slidesPerView: 4,
          spaceBetween: 20,
        },

        1024: {
          slidesPerView: 6,
          spaceBetween: 20,
        },
      },

      lazy: {
        loadPrevNext: true,
        loadPrevNextAmount: 2,
      },

      keyboard: {
        enabled: true,
        onlyInViewport: true,
      },

      grabCursor: true,

      watchOverflow: true,

      preventClicksPropagation: true,
      preventClicks: true,

      on: {
        init: function () {
          console.log("✅ Exclusive carousel initialized");
        },
      },
    });

    initBookmarkButtons(".homepage-exclusive");

    return swiper;
  }

  function initBookmarkButtons(sectionClass) {
    const bookmarkButtons = document.querySelectorAll(
      `${sectionClass} .bookmark-badge`,
    );

    bookmarkButtons.forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        this.classList.toggle("active");

        const icon = this.querySelector("i");
        if (this.classList.contains("active")) {
          icon.className = "fa fa-bookmark";
          showToast("Đã thêm vào theo dõi");
        } else {
          icon.className = "fa fa-bookmark-o";
          showToast("Đã bỏ theo dõi");
        }
      });
    });
  }

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

    setTimeout(() => {
      toast.classList.add("show");
    }, 10);

    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => {
        toast.remove();
      }, 300);
    }, 2000);
  }

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    if (typeof Swiper === "undefined") {
      console.error(
        "❌ Swiper.js not loaded! Please enqueue Swiper library first.",
      );
      return;
    }

    initExclusiveCarousel();
  }

  init();
})();

/* =====================================================
   SECTION 3: TRUYỆN MỚI CẬP NHẬT (GRID)
===================================================== */

(function () {
  "use strict";

  function initBookmarkButtons() {
    const bookmarkButtons = document.querySelectorAll(
      ".homepage-new-update .bookmark-badge",
    );

    bookmarkButtons.forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        this.classList.toggle("active");

        const icon = this.querySelector("i");
        if (this.classList.contains("active")) {
          icon.className = "fa fa-bookmark";
          showToast("Đã thêm vào theo dõi");
        } else {
          icon.className = "fa fa-bookmark-o";
          showToast("Đã bỏ theo dõi");
        }
      });
    });
  }

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

    setTimeout(() => {
      toast.classList.add("show");
    }, 10);

    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => {
        toast.remove();
      }, 300);
    }, 2000);
  }

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    const section = document.querySelector(".homepage-new-update");
    if (!section) {
      console.warn("Truyện Mới Cập Nhật section not found");
      return;
    }

    initBookmarkButtons();

    console.log("✅ Truyện Mới Cập Nhật initialized");
  }

  init();
})();

/* =====================================================
   SECTION 4: BLOG DETAIL - EXPAND/COLLAPSE
===================================================== */

(function () {
  "use strict";

  function initBlogDetailExpandCollapse() {
    const content = document.getElementById("blogDetailContent");
    const btnReadMore = document.getElementById("btnReadMore");
    const btnReadLess = document.getElementById("btnReadLess");

    if (!content || !btnReadMore || !btnReadLess) {
      console.warn("Blog detail elements not found");
      return;
    }

    // Read More - Expand (NO SCROLL)
    btnReadMore.addEventListener("click", function () {
      content.classList.add("expanded");
      btnReadMore.style.display = "none";
      btnReadLess.style.display = "flex";
    });

    // Read Less - Collapse (NO SCROLL)
    btnReadLess.addEventListener("click", function () {
      content.classList.remove("expanded");
      btnReadLess.style.display = "none";
      btnReadMore.style.display = "flex";
    });

    // Check if content exceeds max-height on load
    checkContentHeight();
    window.addEventListener("resize", checkContentHeight);

    function checkContentHeight() {
      const maxHeight = 600; // match CSS max-height
      const actualHeight = content.scrollHeight;

      // If content is shorter than max-height, hide Read More button
      if (actualHeight <= maxHeight) {
        btnReadMore.style.display = "none";
      } else if (!content.classList.contains("expanded")) {
        btnReadMore.style.display = "flex";
      }
    }

    console.log("✅ Blog Detail expand/collapse initialized");
  }

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    initBlogDetailExpandCollapse();
  }

  init();
})();

/* =====================================================
   SECTION 5: FEATURED ARTICLES CAROUSEL
===================================================== */

(function () {
  "use strict";

  function initArticlesCarousel() {
    const swiperContainer = document.querySelector(".articles-swiper");

    if (!swiperContainer) {
      console.warn("Articles carousel not found");
      return;
    }

    const swiper = new Swiper(".articles-swiper", {
      // Loop
      loop: true,

      // Default slides
      slidesPerView: 1,
      spaceBetween: 20,

      // Speed
      speed: 500,

      // Slides per group
      slidesPerGroup: 1,

      // Navigation
      navigation: {
        nextEl: ".featured-articles-section .swiper-button-next",
        prevEl: ".featured-articles-section .swiper-button-prev",
      },

      // Pagination
      pagination: {
        el: ".featured-articles-section .swiper-pagination",
        clickable: true,
        dynamicBullets: false,
      },

      // Responsive breakpoints
      breakpoints: {
        // Mobile (390px+)
        390: {
          slidesPerView: 1,
          spaceBetween: 20,
        },

        // Tablet (768px+)
        768: {
          slidesPerView: 2,
          spaceBetween: 25,
          slidesPerGroup: 2,
        },

        // Desktop (1024px+)
        1024: {
          slidesPerView: 3,
          spaceBetween: 30,
          slidesPerGroup: 3,
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

      // Grab cursor
      grabCursor: true,

      // Watch overflow
      watchOverflow: true,

      // Prevent clicks
      preventClicksPropagation: true,
      preventClicks: true,

      // Autoplay (optional)
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },

      // Events
      on: {
        init: function () {
          console.log("✅ Articles carousel initialized");
          addHoverEffects();
        },
      },
    });

    // Add hover effects to cards
    function addHoverEffects() {
      const cards = document.querySelectorAll(".article-card");

      cards.forEach((card) => {
        card.addEventListener("mouseenter", function () {
          this.style.zIndex = "10";
        });

        card.addEventListener("mouseleave", function () {
          this.style.zIndex = "1";
        });
      });
    }

    return swiper;
  }

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    // Check if Swiper is loaded
    if (typeof Swiper === "undefined") {
      console.error(
        "❌ Swiper.js not loaded! Please enqueue Swiper library first.",
      );
      return;
    }

    initArticlesCarousel();
  }

  init();
})();

/* =====================================================
   OPTIONAL: SCROLL ANIMATIONS FOR BLOG SECTIONS
===================================================== */

(function () {
  "use strict";

  function initScrollAnimations() {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: "0px 0px -50px 0px",
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("fade-in-up");
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);

    // Observe blog sections
    const blogSections = document.querySelectorAll(
      ".blog-detail-section, .featured-articles-section",
    );

    blogSections.forEach((section) => {
      section.style.opacity = "0";
      section.style.transform = "translateY(20px)";
      section.style.transition = "opacity 0.6s ease, transform 0.6s ease";

      observer.observe(section);
    });

    // Add fade-in-up effect
    const style = document.createElement("style");
    style.textContent = `
      .fade-in-up {
        opacity: 1 !important;
        transform: translateY(0) !important;
      }
    `;
    document.head.appendChild(style);
  }

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    // Only init if Intersection Observer is supported
    if ("IntersectionObserver" in window) {
      initScrollAnimations();
    }
  }

  init();
})();

/* =====================================================
   ✅ ALL SECTIONS INITIALIZED
===================================================== */

console.log("🚀 TruyenQQ Homepage - All JavaScript modules loaded");
