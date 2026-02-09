/**
 * Auth Pages - Dark/Light Mode Toggle
 * TruyenQQ
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function () {
  "use strict";

  const THEME_KEY = "truyenqq_theme_mode";
  const THEME_DARK = "dark";
  const THEME_LIGHT = "light";

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }

  function init() {
    loadTheme();

    setupToggleButton();

    console.log("Auth Dark Mode initialized");
  }

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

  function setTheme(theme) {
    if (theme === THEME_DARK) {
      document.body.classList.add("dark-mode");
      localStorage.setItem(THEME_KEY, THEME_DARK);
    } else {
      document.body.classList.remove("dark-mode");
      localStorage.setItem(THEME_KEY, THEME_LIGHT);
    }
  }

  function toggleTheme() {
    const isDark = document.body.classList.contains("dark-mode");
    setTheme(isDark ? THEME_LIGHT : THEME_DARK);
  }

  function setupToggleButton() {
    const toggleBtn = document.getElementById("theme-toggle-btn");

    if (!toggleBtn) {
      console.warn("Theme toggle button not found");
      return;
    }

    toggleBtn.addEventListener("click", function (e) {
      e.preventDefault();
      toggleTheme();

      this.style.transform = "scale(0.9) rotate(180deg)";
      setTimeout(() => {
        this.style.transform = "";
      }, 200);
    });

    toggleBtn.addEventListener("keypress", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        toggleTheme();
      }
    });
  }

  window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", (e) => {
      const savedTheme = localStorage.getItem(THEME_KEY);

      if (!savedTheme) {
        setTheme(e.matches ? THEME_DARK : THEME_LIGHT);
      }
    });
})();
