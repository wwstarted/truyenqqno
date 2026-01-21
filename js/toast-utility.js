/**
 * Global Toast Notification System
 * Single source of truth for all toast notifications
 *
 * @package TruyenQQ
 * @version 1.0.0
 */

(function () {
  "use strict";

  /**
   * Global Toast Manager
   */
  const TruyenqqToast = {
    /**
     * Show toast notification
     * @param {string} message - Message to display
     * @param {string} type - Type: success, error, info, warning
     * @param {number} duration - Duration in ms (default: 3000)
     */
    show: function (message, type = "info", duration = 3000) {
      let toastContainer = document.querySelector(".toast-container");

      if (!toastContainer) {
        toastContainer = document.createElement("div");
        toastContainer.className = "toast-container";
        document.body.appendChild(toastContainer);
      }

      const iconMap = {
        success: "check-circle",
        error: "exclamation-circle",
        info: "info-circle",
        warning: "exclamation-triangle",
      };

      const toast = document.createElement("div");
      toast.className = `toast toast-${type}`;
      toast.innerHTML = `
        <i class="fa fa-${iconMap[type] || "info-circle"}"></i>
        ${message}
      `;

      toastContainer.appendChild(toast);

      setTimeout(() => toast.classList.add("show"), 10);
      setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => toast.remove(), 300);
      }, duration);
    },

    /**
     * Show success toast
     */
    success: function (message, duration = 3000) {
      this.show(message, "success", duration);
    },

    /**
     * Show error toast
     */
    error: function (message, duration = 3000) {
      this.show(message, "error", duration);
    },

    /**
     * Show info toast
     */
    info: function (message, duration = 3000) {
      this.show(message, "info", duration);
    },

    /**
     * Show warning toast
     */
    warning: function (message, duration = 3000) {
      this.show(message, "warning", duration);
    },
  };

  // Expose globally
  window.TruyenqqToast = TruyenqqToast;

  console.log("✅ Global Toast System initialized");
})();
