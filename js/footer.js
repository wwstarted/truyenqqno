/**
 * Footer JavaScript - TruyenQQ
 * Newsletter subscription & Back to top button
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function () {
  "use strict";

  /* =====================================================
     NEWSLETTER SUBSCRIPTION
  ===================================================== */

  function initNewsletterForm() {
    const form = document.getElementById("footerNewsletterForm");

    if (!form) {
      return;
    }

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      const emailInput = form.querySelector('input[name="email"]');
      const submitBtn = form.querySelector(".newsletter-btn");
      const email = emailInput.value.trim();

      // Validate email
      if (!isValidEmail(email)) {
        showToast("Vui lòng nhập email hợp lệ!", "error");
        return;
      }

      // Disable button & show loading
      const originalHTML = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML =
        '<i class="fa fa-spinner fa-spin"></i> Đang xử lý...';

      // Simulate AJAX call (replace with real API later)
      setTimeout(() => {
        // Success
        showToast("Đăng ký thành công! Cảm ơn bạn đã theo dõi.", "success");
        emailInput.value = "";
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;

        // TODO: Replace with real AJAX call
        // fetch('/wp-json/truyenqq/v1/newsletter', {
        //   method: 'POST',
        //   headers: { 'Content-Type': 'application/json' },
        //   body: JSON.stringify({ email: email })
        // })
        // .then(response => response.json())
        // .then(data => {
        //   if (data.success) {
        //     showToast(data.message, 'success');
        //     emailInput.value = '';
        //   } else {
        //     showToast(data.message, 'error');
        //   }
        // })
        // .catch(error => {
        //   showToast('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
        // })
        // .finally(() => {
        //   submitBtn.disabled = false;
        //   submitBtn.innerHTML = originalHTML;
        // });
      }, 1500);
    });
  }

  function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }

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
     BACK TO TOP BUTTON
  ===================================================== */

  function initBackToTop() {
    const backToTopBtn = document.getElementById("backToTop");

    if (!backToTopBtn) {
      return;
    }

    const button = backToTopBtn.querySelector("button");

    // Show/hide button based on scroll position
    window.addEventListener("scroll", function () {
      if (window.pageYOffset > 300) {
        backToTopBtn.classList.add("show");
      } else {
        backToTopBtn.classList.remove("show");
      }
    });

    // Scroll to top on click
    button.addEventListener("click", function () {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });
  }

  /* =====================================================
     SMOOTH SCROLL FOR FOOTER LINKS
  ===================================================== */

  function initSmoothScroll() {
    const footerLinks = document.querySelectorAll('.site-footer a[href^="#"]');

    footerLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        const targetId = this.getAttribute("href");

        if (targetId === "#" || targetId === "#top") {
          e.preventDefault();
          window.scrollTo({
            top: 0,
            behavior: "smooth",
          });
        } else {
          const targetElement = document.querySelector(targetId);
          if (targetElement) {
            e.preventDefault();
            const offsetTop = targetElement.offsetTop - 80; // 80px for fixed header
            window.scrollTo({
              top: offsetTop,
              behavior: "smooth",
            });
          }
        }
      });
    });
  }

  /* =====================================================
     ANIMATE STATS ON SCROLL
  ===================================================== */

  function animateStats() {
    const statNumbers = document.querySelectorAll(".stat-number");

    if (statNumbers.length === 0) {
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && !entry.target.dataset.animated) {
            entry.target.dataset.animated = "true";
            animateValue(entry.target);
          }
        });
      },
      { threshold: 0.5 },
    );

    statNumbers.forEach((stat) => observer.observe(stat));
  }

  function animateValue(element) {
    const text = element.textContent;
    const hasPlus = text.includes("+");
    const hasMSuffix = text.includes("M");
    const hasKSuffix = text.includes("K");

    let numberStr = text.replace(/[^0-9.]/g, "");
    let targetValue = parseFloat(numberStr);

    if (isNaN(targetValue)) return;

    const duration = 2000;
    const startValue = 0;
    const increment = targetValue / (duration / 16);
    let currentValue = startValue;

    const timer = setInterval(() => {
      currentValue += increment;
      if (currentValue >= targetValue) {
        currentValue = targetValue;
        clearInterval(timer);
      }

      let displayValue = Math.floor(currentValue);

      if (hasMSuffix) {
        displayValue = currentValue.toFixed(1) + "M";
      } else if (hasKSuffix) {
        displayValue = Math.floor(currentValue) + "K";
      } else {
        displayValue = Math.floor(currentValue).toLocaleString();
      }

      element.textContent = displayValue + (hasPlus ? "+" : "");
    }, 16);
  }

  /* =====================================================
     MAIN INITIALIZATION
  ===================================================== */

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    initNewsletterForm();
    initBackToTop();
    initSmoothScroll();

    if ("IntersectionObserver" in window) {
      animateStats();
    }

    console.log("✅ Footer initialized successfully");
  }

  init();
})();
