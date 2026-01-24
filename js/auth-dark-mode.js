/**
 * Auth Pages - Dark/Light Mode Toggle
 * TruyenQQ
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function () {
  "use strict";

  // =====================================================
  // CONSTANTS
  // =====================================================
  const THEME_KEY = "truyenqq_theme_mode";
  const THEME_DARK = "dark";
  const THEME_LIGHT = "light";

  // =====================================================
  // INIT ON DOM READY
  // =====================================================
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

  function init() {
    // Load saved theme or default to light
    loadTheme();

    // Setup toggle button
    setupToggleButton();

    console.log("✅ Auth Dark Mode initialized");
  }

  // =====================================================
  // LOAD THEME FROM LOCALSTORAGE
  // =====================================================
  function loadTheme() {
    const savedTheme = localStorage.getItem(THEME_KEY);
    const prefersDark = window.matchMedia(
      "(prefers-color-scheme: dark)",
    ).matches;

    if (savedTheme === THEME_DARK || (!savedTheme && prefersDark)) {
      setTheme(THEME_DARK);
    } else {
      setTheme(THEME_LIGHT);
    }
  }

  // =====================================================
  // SET THEME
  // =====================================================
  function setTheme(theme) {
    if (theme === THEME_DARK) {
      document.body.classList.add("dark-mode");
      localStorage.setItem(THEME_KEY, THEME_DARK);
    } else {
      document.body.classList.remove("dark-mode");
      localStorage.setItem(THEME_KEY, THEME_LIGHT);
    }
  }

  // =====================================================
  // TOGGLE THEME
  // =====================================================
  function toggleTheme() {
    const isDark = document.body.classList.contains("dark-mode");
    setTheme(isDark ? THEME_LIGHT : THEME_DARK);
  }

  // =====================================================
  // SETUP TOGGLE BUTTON
  // =====================================================
  function setupToggleButton() {
    const toggleBtn = document.getElementById("theme-toggle-btn");

    if (!toggleBtn) {
      console.warn("⚠️ Theme toggle button not found");
      return;
    }

    // Click handler
    toggleBtn.addEventListener("click", function (e) {
      e.preventDefault();
      toggleTheme();

      // Add click animation
      this.style.transform = "scale(0.9) rotate(180deg)";
      setTimeout(() => {
        this.style.transform = "";
      }, 200);
    });

    // Keyboard support
    toggleBtn.addEventListener("keypress", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        toggleTheme();
      }
    });
  }

  // =====================================================
  // LISTEN TO SYSTEM THEME CHANGES
  // =====================================================
  window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", (e) => {
      const savedTheme = localStorage.getItem(THEME_KEY);
      // Only auto-switch if user hasn't manually set a preference
      if (!savedTheme) {
        setTheme(e.matches ? THEME_DARK : THEME_LIGHT);
      }
    });
})();
