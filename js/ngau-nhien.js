/**
 * Truyện Ngẫu Nhiên - Random Button Handler
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function () {
  "use strict";

  function init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", init);
      return;
    }

    const randomButton = document.getElementById("btn-random-reload");
    if (!randomButton) {
      console.warn("Random button not found");
      return;
    }

    initRandomButton(randomButton);
    console.log("✅ Random button initialized");
  }

  function initRandomButton(button) {
    button.addEventListener("click", function () {
      // Add loading state
      button.classList.add("loading");
      button.disabled = true;

      // Get current URL params
      const urlParams = new URLSearchParams(window.location.search);
      const status = urlParams.get("status") || "";
      const country = urlParams.get("country") || "";

      // Create new random param
      const randomParam = Date.now();

      // Build new URL
      const newParams = new URLSearchParams();
      if (status) newParams.set("status", status);
      if (country) newParams.set("country", country);
      newParams.set("r", randomParam); // Add random timestamp

      // Redirect to new URL
      const newUrl =
        window.location.pathname +
        (newParams.toString() ? "?" + newParams.toString() : "");

      window.location.href = newUrl;
    });
  }

  init();
})();

console.log("🎲 Ngẫu Nhiên - Random button script loaded");
