// ===================================
// FOOTER SECTION - JAVASCRIPT
// ===================================

(function ($) {
  "use strict";

  console.log("Footer JavaScript loaded");

  function initFooter() {
    console.log("Initializing Footer");

    const newsletterForm = document.getElementById("newsletterForm");

    if (newsletterForm) {
      console.log("Newsletter form found");

      newsletterForm.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("Newsletter form submitted");

        const emailInput = this.querySelector('input[type="email"]');
        const submitBtn = this.querySelector('button[type="submit"]');
        const email = emailInput.value.trim();

        // Validate email
        if (!isValidEmail(email)) {
          console.warn("Invalid email address");
          showNotification("Vui lòng nhập email hợp lệ", "error");
          emailInput.focus();
          return;
        }

        // Disable button and show loading state
        submitBtn.disabled = true;
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = "<span>Đang xử lý...</span>";

        // Simulate API call (replace with your actual API endpoint)
        setTimeout(() => {
          // Success
          console.log("Email subscribed:", email);
          showNotification("Đăng ký thành công! Cảm ơn bạn.", "success");
          emailInput.value = "";

          // Reset button
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;

          // Here you would typically send data to your backend
          // Example:
          // fetch('/api/newsletter', {
          //   method: 'POST',
          //   headers: { 'Content-Type': 'application/json' },
          //   body: JSON.stringify({ email: email })
          // })
          // .then(response => response.json())
          // .then(data => {
          //   console.log('Success:', data);
          //   showNotification('Đăng ký thành công!', 'success');
          // })
          // .catch(error => {
          //   console.error('Error:', error);
          //   showNotification('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
          // });
        }, 1500);
      });
    } else {
      console.warn("Newsletter form not found");
    }

    const footerLinks = document.querySelectorAll(".footer-links a");
    footerLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        const href = this.getAttribute("href");

        if (href && href.startsWith("#") && href.length > 1) {
          e.preventDefault();
          const target = document.querySelector(href);

          if (target) {
            console.log("Scrolling to:", href);
            target.scrollIntoView({
              behavior: "smooth",
              block: "start",
            });
          }
        }
      });
    });

    // Intersection Observer for footer animations
    const observerOptions = {
      threshold: 0.1,
      rootMargin: "0px 0px -50px 0px",
    };

    const observer = new IntersectionObserver(function (entries) {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
        }
      });
    }, observerOptions);

    // Observe footer columns for fade-in animation
    const footerCols = document.querySelectorAll(".footer-col");
    footerCols.forEach((col, index) => {
      col.style.opacity = "0";
      col.style.transform = "translateY(20px)";
      col.style.transition = `opacity 0.6s ease ${
        index * 0.1
      }s, transform 0.6s ease ${index * 0.1}s`;

      observer.observe(col);
    });

    // Add visible class styles dynamically
    const style = document.createElement("style");
    style.textContent = `
      .footer-col.visible {
        opacity: 1 !important;
        transform: translateY(0) !important;
      }
    `;
    document.head.appendChild(style);

    console.log("Footer initialization complete");
  }

  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  function showNotification(message, type = "info") {
    let container = document.getElementById("notification-container");

    if (!container) {
      container = document.createElement("div");
      container.id = "notification-container";
      container.style.cssText = `
        position: fixed;
        top: 2rem;
        right: 2rem;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 1rem;
      `;
      document.body.appendChild(container);
    }

    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;

    const bgColor =
      type === "success"
        ? "linear-gradient(135deg, #95d3c5, #009fd3)"
        : type === "error"
        ? "linear-gradient(135deg, #ef4444, #dc2626)"
        : "linear-gradient(135deg, #6366f1, #4f46e5)";

    notification.style.cssText = `
      background: ${bgColor};
      color: white;
      padding: 1rem 1.5rem;
      border-radius: 0.75rem;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
      font-size: 0.875rem;
      font-weight: 500;
      animation: slideInRight 0.3s ease;
      max-width: 20rem;
    `;

    notification.textContent = message;
    container.appendChild(notification);

    const animationStyle = document.createElement("style");
    animationStyle.textContent = `
      @keyframes slideInRight {
        from {
          transform: translateX(100%);
          opacity: 0;
        }
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
      @keyframes slideOutRight {
        from {
          transform: translateX(0);
          opacity: 1;
        }
        to {
          transform: translateX(100%);
          opacity: 0;
        }
      }
    `;
    if (!document.getElementById("notification-animations")) {
      animationStyle.id = "notification-animations";
      document.head.appendChild(animationStyle);
    }

    setTimeout(() => {
      notification.style.animation = "slideOutRight 0.3s ease";
      setTimeout(() => {
        notification.remove();
        if (container.children.length === 0) {
          container.remove();
        }
      }, 300);
    }, 3000);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initFooter);
  } else {
    initFooter();
  }

  if (typeof $ !== "undefined") {
    $(document).ready(function () {
      console.log("jQuery ready, ensuring Footer init");
      if (!document.querySelector(".footer-section.initialized")) {
        const section = document.querySelector(".footer-section");
        if (section) {
          section.classList.add("initialized");
          initFooter();
        }
      }
    });
  }
})(typeof jQuery !== "undefined" ? jQuery : undefined);
