/**
 * TruyenQQ Homepage - Complete JavaScript Implementation
 * ✅ FIXED: Removed all duplicate showToast functions
 * ✅ Uses global window.TruyenqqToast instead
 *
 * @package TruyenQQ
 * @version 2.1.0
 * @requires Swiper.js 11.x, toast-utility.js
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
        390: { slidesPerView: 2, spaceBetween: 15 },
        768: { slidesPerView: 4, spaceBetween: 20 },
        1024: { slidesPerView: 6, spaceBetween: 20 },
      },
      lazy: { loadPrevNext: true, loadPrevNextAmount: 2 },
      keyboard: { enabled: true, onlyInViewport: true },
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

    return swiper;
  }

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    if (typeof Swiper === "undefined") {
      console.error("❌ Swiper.js not loaded!");
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
        390: { slidesPerView: 2, spaceBetween: 15 },
        768: { slidesPerView: 4, spaceBetween: 20 },
        1024: { slidesPerView: 6, spaceBetween: 20 },
      },
      lazy: { loadPrevNext: true, loadPrevNextAmount: 2 },
      keyboard: { enabled: true, onlyInViewport: true },
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

    return swiper;
  }

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    if (typeof Swiper === "undefined") {
      console.error("❌ Swiper.js not loaded!");
      return;
    }

    initExclusiveCarousel();
  }

  init();
})();

/* =====================================================
   SECTION 3: TRUYỆN MỚI CẬP NHẬT (GRID)
   ✅ REMOVED: initBookmarkButtons and showToast
   (Handled by global bookmarks.js)
===================================================== */

(function () {
  "use strict";

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

    btnReadMore.addEventListener("click", function () {
      content.classList.add("expanded");
      btnReadMore.style.display = "none";
      btnReadLess.style.display = "flex";
    });

    btnReadLess.addEventListener("click", function () {
      content.classList.remove("expanded");
      btnReadLess.style.display = "none";
      btnReadMore.style.display = "flex";
    });

    checkContentHeight();
    window.addEventListener("resize", checkContentHeight);

    function checkContentHeight() {
      const maxHeight = 600;
      const actualHeight = content.scrollHeight;

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
      loop: true,
      slidesPerView: 1,
      spaceBetween: 20,
      speed: 500,
      slidesPerGroup: 1,
      navigation: {
        nextEl: ".featured-articles-section .swiper-button-next",
        prevEl: ".featured-articles-section .swiper-button-prev",
      },
      pagination: {
        el: ".featured-articles-section .swiper-pagination",
        clickable: true,
        dynamicBullets: false,
      },
      breakpoints: {
        390: { slidesPerView: 1, spaceBetween: 20 },
        768: { slidesPerView: 2, spaceBetween: 25, slidesPerGroup: 2 },
        1024: { slidesPerView: 3, spaceBetween: 30, slidesPerGroup: 3 },
      },
      lazy: { loadPrevNext: true, loadPrevNextAmount: 2 },
      keyboard: { enabled: true, onlyInViewport: true },
      grabCursor: true,
      watchOverflow: true,
      preventClicksPropagation: true,
      preventClicks: true,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true,
      },
      on: {
        init: function () {
          console.log("✅ Articles carousel initialized");
          addHoverEffects();
        },
      },
    });

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

    if (typeof Swiper === "undefined") {
      console.error("❌ Swiper.js not loaded!");
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

    const blogSections = document.querySelectorAll(
      ".blog-detail-section, .featured-articles-section",
    );

    blogSections.forEach((section) => {
      section.style.opacity = "0";
      section.style.transform = "translateY(20px)";
      section.style.transition = "opacity 0.6s ease, transform 0.6s ease";

      observer.observe(section);
    });

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

    if ("IntersectionObserver" in window) {
      initScrollAnimations();
    }
  }

  init();
})();

console.log("🚀 TruyenQQ Homepage - All JavaScript modules loaded");
