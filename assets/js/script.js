document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("sidebar");
  const mainContent = document.getElementById("mainContent");
  const toggleSidebarBtn = document.getElementById("toggleSidebarBtn");
  const closeSidebarBtn = document.getElementById("closeSidebarBtn");

  // Responsive sidebar handling
  function toggleSidebar() {
    if (window.innerWidth <= 992) {
      sidebar.classList.toggle("expanded");
    } else {
      sidebar.classList.toggle("collapsed");
      mainContent.classList.toggle("expanded");
    }
  }

  toggleSidebarBtn.addEventListener("click", toggleSidebar);

  closeSidebarBtn.addEventListener("click", () => {
    sidebar.classList.remove("expanded");
  });

  // Handle resize events
  window.addEventListener("resize", () => {
    if (window.innerWidth > 992) {
      sidebar.classList.remove("expanded");
    } else {
      sidebar.classList.remove("collapsed");
      mainContent.classList.remove("expanded");
    }
  });

  // Add active class toggling for menu items
  const menuItems = document.querySelectorAll(".sidebar-menu > ul > li");
  menuItems.forEach((item) => {
    const link = item.querySelector("a");
    if (item.querySelector(".submenu")) {
      link.addEventListener("click", (e) => {
        e.preventDefault();

        // Close other open menus
        menuItems.forEach((otherItem) => {
          if (otherItem !== item && otherItem.classList.contains("active")) {
            otherItem.classList.remove("active");
          }
        });

        item.classList.toggle("active");
      });
    }
  });

  // Loading Overlay Handling
  const loadingOverlay = document.getElementById("loading-overlay");
  function hideLoader() {
    if (loadingOverlay) {
      loadingOverlay.classList.add("hidden");
    }
  }

  function showLoader() {
    // Do not show global loader if SweetAlert is currently active
    if (document.body.classList.contains('swal2-shown')) {
      return;
    }
    if (loadingOverlay) {
      loadingOverlay.classList.remove("hidden");
    }
  }

  // Hide loader when page is fully loaded
  window.addEventListener("load", hideLoader);

  // Show loader when user clicks F5 or navigates away
  window.addEventListener("beforeunload", showLoader);

  // Show loader on form submission (filters)
  const filterForm = document.querySelector(".topbar-filter-form");
  if (filterForm) {
    filterForm.addEventListener("submit", showLoader);

    // Also show loader when filter values change (as they call form.submit())
    const filterInputs = filterForm.querySelectorAll("input");
    filterInputs.forEach((input) => {
      input.addEventListener("change", showLoader);
    });
  }

  // Notifications Logic
  const notificationBtn = document.getElementById("notificationBtn");
  const notificationDropdown = document.getElementById("notificationDropdown");
  const notificationBadge = document.getElementById("notificationBadge");
  const notificationList = document.getElementById("notificationList");
  const markAllRead = document.getElementById("markAllRead");
  let lastNotificationId = null;

  if (notificationBtn && notificationDropdown) {
    // Toggle Dropdown
    notificationBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      notificationDropdown.classList.toggle("hidden");
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
      if (
        !notificationDropdown.contains(e.target) &&
        !notificationBtn.contains(e.target)
      ) {
        notificationDropdown.classList.add("hidden");
      }
    });

    // Mark all as read
    if (markAllRead) {
      markAllRead.addEventListener("click", (e) => {
        e.preventDefault();
        fetch("../api/get_notifications.php?mark_read=1")
          .then((response) => response.json())
          .then((data) => {
            renderNotifications(data);
            updateBadge(data);
          });
      });
    }

    // Fetch Notifications periodically
    function fetchNotifications() {
      fetch("../api/get_notifications.php")
        .then((response) => response.json())
        .then((data) => {
          if (data && data.length > 0) {
            const latestId = data[0].id;

            // If we have a new notification and it's not the initial load
            if (lastNotificationId !== null && latestId !== lastNotificationId) {
              showReloadPrompt();
            }

            lastNotificationId = latestId;
          }
          renderNotifications(data);
          updateBadge(data);
        })
        .catch((err) => console.error("Error fetching notifications:", err));
    }

    function showReloadPrompt() {
      if (document.getElementById("reloadToast")) return; // Already showing

      const toast = document.createElement("div");
      toast.id = "reloadToast";
      toast.className = "reload-toast";
      toast.innerHTML = `
                <div class="toast-text">ມີຂໍ້ມູນໃໝ່ເຂົ້າມາແລ້ວ!</div>
                <button class="btn-reload" id="btnReloadToast">ອັບເດດດຽວນີ້</button>
            `;
      document.body.appendChild(toast);

      document
        .getElementById("btnReloadToast")
        .addEventListener("click", function () {
          this.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin" style="margin-right: 5px;"></i>ກຳລັງອັບເດດ...';
          this.style.pointerEvents = "none";
          showLoader();

          // Delay reload slightly to allow UI to render the loading overlay
          setTimeout(() => {
            window.location.reload();
          }, 150);
        });
    }

    function renderNotifications(data) {
      if (!data || data.length === 0) {
        notificationList.innerHTML =
          '<div class="notification-empty">No new notifications</div>';
        return;
      }

      notificationList.innerHTML = data
        .map(
          (n) => `
                <div class="notification-item ${n.read ? "" : "unread"}">
                    <div class="message">${n.message}</div>
                    <div class="time">${formatTimestamp(n.timestamp)}</div>
                </div>
            `,
        )
        .join("");
    }

    function updateBadge(data) {
      const unreadCount = data.filter((n) => !n.read).length;
      if (unreadCount > 0) {
        notificationBadge.classList.remove("hidden");
      } else {
        notificationBadge.classList.add("hidden");
      }
    }

    function formatTimestamp(timestamp) {
      const date = new Date(timestamp * 1000);
      return date.toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
      });
    }

    // Initial fetch and set interval (10 seconds)
    fetchNotifications();
    setInterval(fetchNotifications, 10000);
  }
});
