(function () {
  "use strict";

  const CONFIG = {
    SCROLL_THROTTLE: 16,
    RESIZE_DEBOUNCE: 250,
    ANIMATION_DURATION: 300,
    INTERSECTION_THRESHOLD: 0.1,
  };

  const PROCESS_STEPS = [
    {
      title: "Tìm Hiểu & Phân Tích",
      subtitle: "Discovery & Analysis",
      emoji: "🔍",
      description:
        "Nghiên cứu sâu về thị trường, đối thủ, và khách hàng mục tiêu để xây dựng nền tảng vững chắc cho dự án.",
      time: "1-2 tuần",
      deliverables: [
        "Phân tích thị trường & đối thủ",
        "User research & personas",
        "Requirement documentation",
        "Project roadmap & timeline",
      ],
    },
    {
      title: "Chiến Lược",
      subtitle: "Strategy",
      emoji: "📋",
      description:
        "Xây dựng chiến lược tổng thể, định hình hướng đi và mục tiêu cụ thể cho từng giai đoạn của dự án.",
      time: "1-2 tuần",
      deliverables: [
        "Content strategy & sitemap",
        "Information architecture",
        "Technical architecture",
        "SEO & marketing strategy",
      ],
    },
    {
      title: "Thiết Kế UI/UX",
      subtitle: "UI/UX Design",
      emoji: "🎨",
      description:
        "Thiết kế theo hệ thống: component, typography, spacing, prototype để review nhanh, chuẩn và sáng.",
      time: "3-4 tuần",
      deliverables: [
        "Moodboard & visual direction",
        "Design system / UI kit",
        "Prototype tương tác",
        "Handoff chuẩn dev",
      ],
    },
    {
      title: "Phát Triển",
      subtitle: "Development",
      emoji: "⚡",
      description:
        "Code sạch, hiệu năng cao, SEO-friendly với công nghệ hiện đại nhất. Mọi dòng code đều được test kỹ lưỡng.",
      time: "4-6 tuần",
      deliverables: [
        "Frontend responsive development",
        "Backend & database integration",
        "CMS & admin dashboard",
        "Performance optimization",
      ],
    },
    {
      title: "Kiểm Tra",
      subtitle: "Testing",
      emoji: "🔬",
      description:
        "Kiểm tra toàn diện trên nhiều thiết bị và trình duyệt, đảm bảo mọi tính năng hoạt động hoàn hảo.",
      time: "1-2 tuần",
      deliverables: [
        "Cross-browser testing",
        "Mobile responsiveness test",
        "Performance & speed test",
        "Security & bug fixes",
      ],
    },
    {
      title: "Ra Mắt & Tối Ưu",
      subtitle: "Launch & Optimize",
      emoji: "🚀",
      description:
        "Deploy lên production, theo dõi và tối ưu hóa liên tục dựa trên dữ liệu thực tế và phản hồi người dùng.",
      time: "Ongoing",
      deliverables: [
        "Domain & hosting setup",
        "Production deployment",
        "Analytics & monitoring",
        "Continuous optimization",
      ],
    },
  ];

  const Utils = {
    throttle(func, delay) {
      let lastCall = 0;
      return function (...args) {
        const now = Date.now();
        if (now - lastCall >= delay) {
          lastCall = now;
          func.apply(this, args);
        }
      };
    },

    debounce(func, delay) {
      let timeoutId;
      return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
      };
    },

    qs(selector, parent = document) {
      return parent.querySelector(selector);
    },

    // Safe query selector all
    qsa(selector, parent = document) {
      return Array.from(parent.querySelectorAll(selector));
    },

    // Check if element is in viewport
    isInViewport(el) {
      const rect = el.getBoundingClientRect();
      return rect.bottom > 0 && rect.top < window.innerHeight;
    },

    // Email validation
    isValidEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },
  };

  const ObserverManager = {
    observers: {},

    create(name, callback, options = {}) {
      const defaultOptions = {
        threshold: CONFIG.INTERSECTION_THRESHOLD,
        rootMargin: "0px 0px -50px 0px",
      };

      this.observers[name] = new IntersectionObserver(callback, {
        ...defaultOptions,
        ...options,
      });

      return this.observers[name];
    },

    get(name) {
      return this.observers[name];
    },

    observe(name, elements) {
      const observer = this.get(name);
      if (!observer) return;

      elements.forEach((el) => observer.observe(el));
    },
  };

  // ===========================================
  // HERO & ABOUT SECTION
  // ===========================================

  const HeroSection = {
    init() {
      this.setupScrollIndicator();
      this.setupReadMoreToggle();
      this.setupFloatingLogosParallax();
      this.setupDevicesHoverEffect();
      this.setupVideoOptimization();
      this.setupCTAButton();
      this.setupPartnerLogos();
    },

    setupScrollIndicator() {
      const btn = Utils.qs(".pp-scroll-indicator");
      const target = Utils.qs("#ppAbout");

      if (btn && target) {
        btn.addEventListener("click", () => {
          target.scrollIntoView({ behavior: "smooth", block: "start" });
        });
      }
    },

    setupReadMoreToggle() {
      const btn = Utils.qs("#ppReadMoreBtn");
      const desc = Utils.qs(".pp-about-description");

      if (!btn || !desc) return;

      btn.addEventListener("click", () => {
        const isExpanded = desc.classList.toggle("expanded");
        btn.classList.toggle("active", isExpanded);
        btn.innerHTML = isExpanded
          ? 'Thu gọn <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'
          : "Khám phá thêm";
      });
    },

    setupFloatingLogosParallax() {
      const logos = Utils.qsa(".pp-logo-item");
      const hero = Utils.qs(".pp-hero-section");

      if (!logos.length || !hero) return;

      const handleScroll = Utils.throttle(() => {
        if (!Utils.isInViewport(hero)) return;

        const scrollY = window.scrollY;
        logos.forEach((logo, i) => {
          const speed = 0.3 + i * 0.1;
          logo.style.transform = `translateY(${scrollY * speed}px)`;
        });
      }, CONFIG.SCROLL_THROTTLE);

      window.addEventListener("scroll", handleScroll, { passive: true });
    },

    setupDevicesHoverEffect() {
      const img = Utils.qs(".pp-devices-image");
      const wrapper = Utils.qs(".pp-devices-wrapper");

      if (!img || !wrapper) return;

      wrapper.addEventListener("mousemove", (e) => {
        const rect = wrapper.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = (y - centerY) / 30;
        const rotateY = (centerX - x) / 30;

        img.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px) scale(1.02)`;
      });

      wrapper.addEventListener("mouseleave", () => {
        img.style.transform = "rotateX(0) rotateY(0) translateY(0) scale(1)";
      });
    },

    setupVideoOptimization() {
      const video = Utils.qs(".pp-hero-video");
      if (!video) return;

      const observer = ObserverManager.create("hero-video", (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            video.play().catch(() => {});
          } else {
            video.pause();
          }
        });
      });

      observer.observe(video);
    },

    setupCTAButton() {
      const btn = Utils.qs(".pp-cta-button");
      if (!btn || !btn.getAttribute("href")?.startsWith("#")) return;

      btn.addEventListener("click", (e) => {
        e.preventDefault();
        const targetId = btn.getAttribute("href").substring(1);
        const target = Utils.qs(`#${targetId}`);
        if (target) {
          target.scrollIntoView({ behavior: "smooth", block: "start" });
        }
      });
    },

    setupPartnerLogos() {
      const logos = Utils.qsa(".pp-about-logo-item");
      logos.forEach((logo) => {
        logo.addEventListener("mouseenter", function () {
          this.style.transform = "scale(1.1) translateY(-5px)";
        });
        logo.addEventListener("mouseleave", function () {
          this.style.transform = "scale(1) translateY(0)";
        });
      });
    },
  };

  // ===========================================
  // SERVICES SECTION
  // ===========================================

  const ServicesSection = {
    init() {
      const items = Utils.qsa(".pp-service-item");
      const images = Utils.qsa(".pp-service-image");

      if (!items.length || !images.length) return;

      items.forEach((item) => {
        item.addEventListener("click", () =>
          this.handleClick(item, items, images)
        );
      });
    },

    handleClick(clicked, allItems, allImages) {
      if (clicked.classList.contains("active")) return;

      const serviceType = clicked.getAttribute("data-service");

      allItems.forEach((item) => item.classList.remove("active"));
      allImages.forEach((img) => img.classList.remove("active"));

      clicked.classList.add("active");

      const targetImage = Utils.qs(
        `.pp-service-image[data-service="${serviceType}"]`
      );
      if (targetImage) {
        setTimeout(() => targetImage.classList.add("active"), 100);
      }

      if (window.innerWidth <= 968) {
        clicked.scrollIntoView({ behavior: "smooth", block: "nearest" });
      }
    },
  };

  // ===========================================
  // WHY CHOOSE US SECTION
  // ===========================================

  const WhyChooseUsSection = {
    init() {
      this.setupScrollAnimations();
      this.setupStatsParallax();
      this.setupStatNumbersHover();
      this.setupCounterAnimation();
    },

    setupScrollAnimations() {
      const observer = ObserverManager.create("why-choose", (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("pp-why-visible");
            observer.unobserve(entry.target);
          }
        });
      });

      const elements = Utils.qsa(
        ".pp-why-header, .pp-stat-card, .pp-why-image-wrapper, .pp-feature-item"
      );
      elements.forEach((el) => observer.observe(el));
    },

    setupStatsParallax() {
      const section = Utils.qs(".pp-why-stats-grid");
      if (!section) return;

      const handleScroll = Utils.throttle(() => {
        const scrolled = window.pageYOffset;
        const cards = Utils.qsa(".pp-stat-card");

        cards.forEach((card, i) => {
          const speed = 0.5 + i * 0.1;
          const yPos = -(scrolled * speed * 0.15);
          if (!card.style.transform.includes("translateY")) {
            card.style.transform = `translateY(${yPos}px)`;
          }
        });
      }, CONFIG.SCROLL_THROTTLE);

      window.addEventListener("scroll", handleScroll, { passive: true });
    },

    setupStatNumbersHover() {
      const numbers = Utils.qsa(".pp-stat-number");
      numbers.forEach((num) => {
        num.addEventListener("mouseenter", function () {
          this.style.transform = "scale(1.05)";
        });
        num.addEventListener("mouseleave", function () {
          this.style.transform = "scale(1)";
        });
      });
    },

    setupCounterAnimation() {
      const animateCounter = (el, target, duration = 1200) => {
        if (!/^\d+$/.test(target)) {
          el.textContent = target;
          return;
        }

        const targetNum = parseInt(target);
        const increment = targetNum / (duration / 16);
        let current = 0;

        const update = () => {
          current += increment;
          if (current < targetNum) {
            el.textContent = Math.floor(current) + "+";
            requestAnimationFrame(update);
          } else {
            el.textContent = target + "+";
          }
        };

        update();
      };

      const observer = ObserverManager.create(
        "counter",
        (entries) => {
          entries.forEach((entry) => {
            if (
              entry.isIntersecting &&
              !entry.target.classList.contains("counted")
            ) {
              const target = entry.target.textContent.trim().replace("+", "");
              entry.target.classList.add("counted");
              animateCounter(entry.target, target);
            }
          });
        },
        { threshold: 0.5 }
      );

      Utils.qsa(".pp-stat-number").forEach((num) => observer.observe(num));
    },
  };

  // ===========================================
  // USER REVIEWS SECTION
  // ===========================================

  const UserReviewsSection = {
    init() {
      const columns = Utils.qsa(".pp-reviews-column-content");
      if (!columns.length) return;

      columns.forEach((col) => {
        const contentHeight = col.offsetHeight;
        const containerHeight = col.parentElement.offsetHeight;

        if (contentHeight < containerHeight * 2) {
          col.insertAdjacentHTML("beforeend", col.innerHTML);
        }
      });
    },
  };

  // ===========================================
  // PROCESS SECTION
  // ===========================================

  const ProcessSection = {
    currentStep: 2,

    init() {
      const steps = Utils.qsa(".pp-process-step");
      if (!steps.length) return;

      this.updateAll(this.currentStep);

      steps.forEach((step, i) => {
        step.addEventListener("click", () => {
          if (this.currentStep !== i) {
            this.currentStep = i;
            this.updateAll(i);
          }
        });
      });

      const prevBtn = Utils.qs(".pp-nav-prev");
      const nextBtn = Utils.qs(".pp-nav-next");

      if (prevBtn) {
        prevBtn.addEventListener("click", () => {
          if (this.currentStep > 0) {
            this.currentStep--;
            this.updateAll(this.currentStep);
          }
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener("click", () => {
          if (this.currentStep < PROCESS_STEPS.length - 1) {
            this.currentStep++;
            this.updateAll(this.currentStep);
          }
        });
      }

      Utils.qsa(".pp-dot").forEach((dot, i) => {
        dot.addEventListener("click", () => {
          if (this.currentStep !== i) {
            this.currentStep = i;
            this.updateAll(i);
          }
        });
      });
    },

    updateAll(index) {
      this.updateStepStates(index);
      this.updateDetailPanel(index);
      this.updateProgressLine(index);
      this.updateProgressDots(index);
      this.updateNavigationButtons(index);
    },

    updateStepStates(activeIndex) {
      Utils.qsa(".pp-process-step").forEach((step, i) => {
        step.classList.remove(
          "state-completed",
          "state-active",
          "state-pending"
        );
        if (i < activeIndex) step.classList.add("state-completed");
        else if (i === activeIndex) step.classList.add("state-active");
        else step.classList.add("state-pending");
      });
    },

    updateDetailPanel(index) {
      const data = PROCESS_STEPS[index];
      if (!data) return;

      const updates = [
        [".pp-detail-emoji", data.emoji],
        [".pp-detail-title", data.title],
        [".pp-detail-subtitle", data.subtitle],
        [".pp-detail-description", data.description],
        [".pp-detail-time span", data.time],
        [".pp-detail-step-current", String(index + 1).padStart(2, "0")],
      ];

      updates.forEach(([selector, value]) => {
        const el = Utils.qs(selector);
        if (el) el.textContent = value;
      });

      const list = Utils.qs(".pp-deliverables-list");
      if (list && data.deliverables) {
        list.innerHTML = data.deliverables
          .map(
            (item) => `
          <div class="pp-deliverable-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 12h14"></path>
              <path d="m12 5 7 7-7 7"></path>
            </svg>
            <span>${item}</span>
          </div>
        `
          )
          .join("");
      }
    },

    updateProgressLine(index) {
      const fill = Utils.qs(".pp-process-progress-fill");
      if (fill) {
        fill.style.width = `${(index / (PROCESS_STEPS.length - 1)) * 100}%`;
      }
    },

    updateProgressDots(activeIndex) {
      Utils.qsa(".pp-dot").forEach((dot, i) => {
        dot.classList.remove("completed", "active");
        if (i < activeIndex) dot.classList.add("completed");
        else if (i === activeIndex) dot.classList.add("active");
      });
    },

    updateNavigationButtons(index) {
      const prevBtn = Utils.qs(".pp-nav-prev");
      const nextBtn = Utils.qs(".pp-nav-next");

      if (prevBtn) prevBtn.disabled = index === 0;
      if (nextBtn) nextBtn.disabled = index === PROCESS_STEPS.length - 1;
    },
  };

  // ===========================================
  // CONTACT SECTION
  // ===========================================

  const ContactSection = {
    init() {
      this.setupFloatingLabels();
      this.setupFormValidation();
      this.setupFormSubmit();
      this.injectStyles();
    },

    setupFloatingLabels() {
      const fields = Utils.qsa(".pp-form-field");

      fields.forEach((field) => {
        const input = field.querySelector(".pp-form-input");
        if (!input) return;

        const checkValue = () => {
          field.classList.toggle("has-value", input.value.trim() !== "");
        };

        input.addEventListener("focus", () => {
          field.classList.add("is-focused", "has-value");
        });

        input.addEventListener("blur", () => {
          field.classList.remove("is-focused");
          checkValue();
        });

        ["input", "change"].forEach((evt) => {
          input.addEventListener(evt, checkValue);
        });

        checkValue();
      });

      setTimeout(
        () =>
          fields.forEach((field) => {
            const input = field.querySelector(".pp-form-input");
            if (input)
              field.classList.toggle("has-value", input.value.trim() !== "");
          }),
        100
      );
    },

    setupFormValidation() {
      const form = Utils.qs("#ppContactForm");
      if (!form) return;

      Utils.qsa(".pp-form-input[required]", form).forEach((input) => {
        input.addEventListener("invalid", (e) => {
          e.preventDefault();
          this.showError(input);
        });

        input.addEventListener("input", () => this.clearError(input));
      });
    },

    showError(input) {
      const field = input.closest(".pp-form-field");
      if (!field) return;

      field.classList.add("has-error");
      input.style.borderBottomColor = "#ef4444";

      let errorMsg = field.querySelector(".pp-form-error");
      if (!errorMsg) {
        errorMsg = document.createElement("div");
        errorMsg.className = "pp-form-error";
        errorMsg.style.cssText =
          "color: #ef4444; font-size: 0.875rem; margin-top: 0.5rem; font-weight: 600;";
        errorMsg.textContent =
          input.type === "email"
            ? "Vui lòng nhập email hợp lệ"
            : "Vui lòng điền thông tin này";
        field.appendChild(errorMsg);
      }
    },

    clearError(input) {
      const field = input.closest(".pp-form-field");
      if (!field) return;

      field.classList.remove("has-error");
      input.style.borderBottomColor = "";

      const errorMsg = field.querySelector(".pp-form-error");
      if (errorMsg) errorMsg.remove();
    },

    setupFormSubmit() {
      const form = Utils.qs("#ppContactForm");
      if (!form) return;

      form.addEventListener("submit", (e) => {
        e.preventDefault();

        Utils.qsa(".pp-form-input", form).forEach((input) =>
          this.clearError(input)
        );

        let isValid = true;
        Utils.qsa(".pp-form-input[required]", form).forEach((input) => {
          if (
            !input.value.trim() ||
            (input.type === "email" && !Utils.isValidEmail(input.value))
          ) {
            this.showError(input);
            isValid = false;
          }
        });

        if (!isValid) {
          const firstError = form.querySelector(".has-error");
          if (firstError) {
            firstError.scrollIntoView({ behavior: "smooth", block: "center" });
          }
          return;
        }

        const formData = {
          name: form.querySelector("#pp-name").value.trim(),
          email: form.querySelector("#pp-email").value.trim(),
          organization: form.querySelector("#pp-organization").value.trim(),
          phone: form.querySelector("#pp-phone").value.trim(),
          message: form.querySelector("#pp-message").value.trim(),
        };

        this.handleSubmit(form, formData);
      });
    },

    handleSubmit(form, data) {
      const btn = form.querySelector(".pp-form-submit");
      const originalText = btn.querySelector("span:first-child").textContent;

      btn.disabled = true;
      btn.querySelector("span:first-child").textContent = "Đang gửi...";

      setTimeout(() => {
        console.log("Form submitted:", data);
        this.showSuccess(form);
        form.reset();
        Utils.qsa(".pp-form-field", form).forEach((field) =>
          field.classList.remove("has-value")
        );
        btn.disabled = false;
        btn.querySelector("span:first-child").textContent = originalText;
      }, 1500);
    },

    showSuccess(form) {
      const msg = document.createElement("div");
      msg.className = "pp-form-success";
      msg.style.cssText = `
        padding: 1.5rem;
        margin-top: 1.5rem;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(34, 197, 94, 0.05));
        border: 1px solid rgba(34, 197, 94, 0.2);
        color: #15803d;
        font-weight: 600;
        text-align: center;
        animation: slideIn 0.5s ease;
      `;
      msg.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
          <svg style="width: 1.5rem; height: 1.5rem; flex-shrink: 0;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
          <span>Cảm ơn bạn! Chúng tôi sẽ liên hệ trong vòng 24 giờ.</span>
        </div>
      `;

      form.appendChild(msg);

      setTimeout(() => {
        msg.style.animation = "slideOut 0.5s ease";
        setTimeout(() => msg.remove(), 500);
      }, 5000);
    },

    injectStyles() {
      const style = document.createElement("style");
      style.textContent = `
        @keyframes slideIn {
          from { opacity: 0; transform: translateY(-10px); }
          to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideOut {
          from { opacity: 1; transform: translateY(0); }
          to { opacity: 0; transform: translateY(-10px); }
        }
      `;
      document.head.appendChild(style);
    },
  };

  // ===========================================
  // BLOG SECTION
  // ===========================================

  const BlogSection = {
    currentPage: 1,
    totalPages: 0,

    init() {
      this.setupScrollAnimations();
      this.setupPagination();
      this.setupCardClickHandling();
    },

    setupScrollAnimations() {
      const observer = ObserverManager.create("blog", (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = "1";
            entry.target.style.transform = "translateY(0)";
          }
        });
      });

      const elements = Utils.qsa(
        ".badge-wrapper, .section-title, .section-description, .blog-grid, .view-all-wrapper"
      );
      elements.forEach((el) => observer.observe(el));
    },

    setupPagination() {
      const prevBtn = Utils.qs(".prev-btn");
      const nextBtn = Utils.qs(".next-btn");
      const dots = Utils.qsa(".dot");

      this.totalPages = dots.length;

      if (prevBtn) {
        prevBtn.addEventListener("click", () => {
          if (this.currentPage > 1) {
            this.currentPage--;
            this.updatePagination("prev");
          }
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener("click", () => {
          if (this.currentPage < this.totalPages) {
            this.currentPage++;
            this.updatePagination("next");
          }
        });
      }

      dots.forEach((dot, i) => {
        dot.addEventListener("click", () => {
          const newPage = i + 1;
          const direction = newPage > this.currentPage ? "next" : "prev";
          this.currentPage = newPage;
          this.updatePagination(direction);
        });
      });

      this.updatePagination("next");
    },

    updatePagination(direction) {
      const prevBtn = Utils.qs(".prev-btn");
      const nextBtn = Utils.qs(".next-btn");
      const dots = Utils.qsa(".dot");

      dots.forEach((dot, i) => {
        dot.classList.toggle("active", i === this.currentPage - 1);
      });

      if (prevBtn) prevBtn.disabled = this.currentPage === 1;
      if (nextBtn) nextBtn.disabled = this.currentPage === this.totalPages;

      this.showPage(this.currentPage, direction);
    },

    showPage(pageNum, direction) {
      const currentCards = Utils.qsa('.blog-card[style*="display: block"]');

      currentCards.forEach((card, i) => {
        card.style.opacity = "0";
        card.style.transform =
          direction === "next" ? "translateX(-30px)" : "translateX(30px)";
        setTimeout(
          () => (card.style.display = "none"),
          CONFIG.ANIMATION_DURATION
        );
      });

      setTimeout(() => {
        const newCards = Utils.qsa(`[data-page="${pageNum}"]`);
        newCards.forEach((card, i) => {
          card.style.display = "block";
          card.style.opacity = "0";
          card.style.transform =
            direction === "next" ? "translateX(30px)" : "translateX(-30px)";
          setTimeout(() => {
            card.style.opacity = "1";
            card.style.transform = "translateX(0)";
          }, i * 100);
        });
      }, CONFIG.ANIMATION_DURATION);
    },

    setupCardClickHandling() {
      Utils.qsa(".blog-card").forEach((card) => {
        card.addEventListener("click", (e) => {
          if (!e.target.closest(".read-more")) {
            const link = card.querySelector(".read-more");
            if (link) window.location.href = link.href;
          }
        });
      });
    },
  };

  // ===========================================
  // FAQ SECTION
  // ===========================================

  const FAQSection = {
    init() {
      this.setupTabSwitching();
      this.setupAccordion();
      this.setupScrollAnimations();
      this.initializeFirstFAQ();
    },

    setupTabSwitching() {
      const tabs = Utils.qsa(".tab-btn");

      tabs.forEach((btn) => {
        btn.addEventListener("click", () => {
          const targetTab = btn.getAttribute("data-tab");

          tabs.forEach((tab) => {
            tab.classList.remove("active");
            const underline = tab.querySelector(".tab-underline");
            if (underline) underline.remove();
          });

          btn.classList.add("active");
          const newUnderline = document.createElement("div");
          newUnderline.className = "tab-underline";
          btn.appendChild(newUnderline);

          const currentGroup = Utils.qs(".faq-group.active");
          if (currentGroup) {
            const items = Utils.qsa(".faq-item", currentGroup);
            items.forEach((item, i) => {
              setTimeout(() => {
                item.style.opacity = "0";
                item.style.transform = "translateY(-10px)";
              }, i * 50);
            });
          }

          setTimeout(() => {
            Utils.qsa(".faq-group").forEach((g) =>
              g.classList.remove("active")
            );

            const targetGroup = Utils.qs(`.faq-group[data-tab="${targetTab}"]`);
            if (targetGroup) {
              targetGroup.classList.add("active");

              const items = Utils.qsa(".faq-item", targetGroup);
              items.forEach((item) => {
                item.classList.remove("active");
                this.collapseAnswer(item);
              });

              if (items.length > 0) {
                items[0].classList.add("active");
                this.expandAnswer(items[0]);
              }

              items.forEach((item, i) => {
                item.style.opacity = "0";
                item.style.transform = "translateY(10px)";
                setTimeout(() => {
                  item.style.transition =
                    "opacity 0.4s ease, transform 0.4s ease";
                  item.style.opacity = "1";
                  item.style.transform = "translateY(0)";
                }, i * 80);
              });
            }

            Utils.qsa(".faq-image").forEach((img) => {
              img.style.transition = "opacity 0.3s ease";
              img.classList.remove("active");
            });

            const targetImage = Utils.qs(`.faq-image[data-tab="${targetTab}"]`);
            if (targetImage) {
              setTimeout(() => targetImage.classList.add("active"), 100);
            }
          }, CONFIG.ANIMATION_DURATION);
        });
      });
    },

    setupAccordion() {
      const container = Utils.qs(".faq-accordions");
      if (!container) return;

      container.addEventListener("click", (e) => {
        const questionBtn = e.target.closest(".faq-question");
        if (!questionBtn) return;

        e.preventDefault();

        const item = questionBtn.closest(".faq-item");
        const group = item.closest(".faq-group.active");
        if (!group) return;

        const wasActive = item.classList.contains("active");

        Utils.qsa(".faq-item", group).forEach((i) => {
          i.classList.remove("active");
          this.collapseAnswer(i);
        });

        if (!wasActive) {
          item.classList.add("active");
          this.expandAnswer(item);
        }
      });
    },

    expandAnswer(item) {
      const answer = item.querySelector(".faq-answer");
      if (answer) {
        answer.style.maxHeight = "1000px";
        answer.style.opacity = "1";
        answer.style.marginTop = "1rem";
        answer.style.transition =
          "max-height 0.4s ease, opacity 0.3s ease, margin-top 0.3s ease";
      }
    },

    collapseAnswer(item) {
      const answer = item.querySelector(".faq-answer");
      if (answer) {
        answer.style.maxHeight = "0";
        answer.style.opacity = "0";
        answer.style.marginTop = "0";
        answer.style.transition =
          "max-height 0.3s ease, opacity 0.2s ease, margin-top 0.3s ease";
      }
    },

    setupScrollAnimations() {
      const observer = ObserverManager.create("faq", (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = "1";
            entry.target.style.transform = "translateY(0)";
          }
        });
      });

      const elements = Utils.qsa(".faq-image-wrapper, .faq-content");
      elements.forEach((el) => observer.observe(el));
    },

    initializeFirstFAQ() {
      setTimeout(() => {
        const activeGroup = Utils.qs(".faq-group.active");
        if (!activeGroup) return;

        let firstItem = Utils.qs(".faq-item.active", activeGroup);
        if (!firstItem) {
          firstItem = Utils.qs(".faq-item", activeGroup);
          if (firstItem) firstItem.classList.add("active");
        }

        if (firstItem) {
          this.expandAnswer(firstItem);
          setTimeout(() => {
            const answer = firstItem.querySelector(".faq-answer");
            if (
              answer &&
              (answer.style.maxHeight === "0" || answer.style.opacity === "0")
            ) {
              this.expandAnswer(firstItem);
            }
          }, 100);
        }
      }, 200);
    },
  };

  // ===========================================
  // GLOBAL UTILITIES
  // ===========================================

  const GlobalUtils = {
    init() {
      this.setupSmoothScroll();
      this.setupGeneralAnimations();
    },

    setupSmoothScroll() {
      Utils.qsa('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
          const href = this.getAttribute("href");
          if (href !== "#" && Utils.qs(href)) {
            e.preventDefault();
            Utils.qs(href).scrollIntoView({
              behavior: "smooth",
              block: "start",
            });
          }
        });
      });
    },

    setupGeneralAnimations() {
      const observer = ObserverManager.create("general", (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("pp-visible");
            observer.unobserve(entry.target);
          }
        });
      });

      Utils.qsa(".animate-on-scroll").forEach((el) => observer.observe(el));
    },
  };

  // ===========================================
  // MAIN INITIALIZATION
  // ===========================================

  function init() {
    console.log("Pixel Perfect - Initializing...");

    // Initialize all sections
    HeroSection.init();
    ServicesSection.init();
    WhyChooseUsSection.init();
    UserReviewsSection.init();
    ProcessSection.init();
    ContactSection.init();
    BlogSection.init();
    FAQSection.init();
    GlobalUtils.init();

    console.log("Pixel Perfect - All sections initialized successfully");
  }

  // ===========================================
  // DOM READY CHECK
  // ===========================================

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
