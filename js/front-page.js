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
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
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
          console.log("Truyen Hay carousel initialized");
        },
      },
    });

    initBookmarkButtons();

    return swiper;
  }

  function initBookmarkButtons() {
    const bookmarkButtons = document.querySelectorAll(
      ".homepage-suggest .bookmark-badge"
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
        "Swiper.js not loaded! Please enqueue Swiper library first."
      );
      return;
    }

    initTruyenHayCarousel();
  }

  init();
})();

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
          console.log("Exclusive carousel initialized");
        },
      },
    });

    initBookmarkButtons();

    return swiper;
  }

  function initBookmarkButtons() {
    const bookmarkButtons = document.querySelectorAll(
      ".homepage-exclusive .bookmark-badge"
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
        "Swiper.js not loaded! Please enqueue Swiper library first."
      );
      return;
    }

    initExclusiveCarousel();
  }

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

  function initBookmarkButtons() {
    const bookmarkButtons = document.querySelectorAll(
      ".homepage-new-update .bookmark-badge"
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

    console.log("Truyện Mới Cập Nhật initialized");
  }

  init();
})();
