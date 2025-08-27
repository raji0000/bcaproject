// Admin Panel JavaScript
document.addEventListener("DOMContentLoaded", () => {
  // Initialize admin panel
  initializeAdmin()

  // Auto-refresh dashboard stats every 30 seconds
  if (window.location.pathname.includes("admin/index.php")) {
    setInterval(refreshDashboard, 30000)
  }
})

function initializeAdmin() {
  // Add mobile menu toggle for responsive design
  addMobileMenuToggle()

  // Add confirmation dialogs for destructive actions
  addConfirmationDialogs()

  // Add keyboard shortcuts
  addKeyboardShortcuts()
}

function addMobileMenuToggle() {
  const sidebar = document.querySelector(".admin-sidebar")
  const main = document.querySelector(".admin-main")

  // Create mobile menu button
  const menuButton = document.createElement("button")
  menuButton.className = "mobile-menu-toggle"
  menuButton.innerHTML = "☰"
  menuButton.style.display = "none"

  // Add to header
  const header = document.querySelector(".admin-header")
  if (header) {
    header.insertBefore(menuButton, header.firstChild)
  }

  // Toggle sidebar on mobile
  menuButton.addEventListener("click", () => {
    sidebar.classList.toggle("mobile-open")
  })

  // Show/hide menu button based on screen size
  function checkScreenSize() {
    if (window.innerWidth <= 768) {
      menuButton.style.display = "block"
    } else {
      menuButton.style.display = "none"
      sidebar.classList.remove("mobile-open")
    }
  }

  window.addEventListener("resize", checkScreenSize)
  checkScreenSize()
}

function addConfirmationDialogs() {
  // Add confirmation for delete actions
  const deleteButtons = document.querySelectorAll('[data-action="delete"]')
  deleteButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      const itemType = button.dataset.type || "item"
      const itemName = button.dataset.name || "this item"

      if (!confirm(`Are you sure you want to delete ${itemName}? This action cannot be undone.`)) {
        e.preventDefault()
      }
    })
  })

  // Add confirmation for bulk actions
  const bulkActionButtons = document.querySelectorAll('[data-action="bulk-delete"]')
  bulkActionButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      const selectedItems = document.querySelectorAll('input[name="selected[]"]:checked')
      if (selectedItems.length === 0) {
        alert("Please select items to delete.")
        e.preventDefault()
        return
      }

      if (
        !confirm(
          `Are you sure you want to delete ${selectedItems.length} selected items? This action cannot be undone.`,
        )
      ) {
        e.preventDefault()
      }
    })
  })
}

function addKeyboardShortcuts() {
  document.addEventListener("keydown", (e) => {
    // Ctrl/Cmd + K for quick search
    if ((e.ctrlKey || e.metaKey) && e.key === "k") {
      e.preventDefault()
      const searchInput = document.querySelector(".search-input")
      if (searchInput) {
        searchInput.focus()
      }
    }

    // Escape to close modals
    if (e.key === "Escape") {
      const modals = document.querySelectorAll(".modal.open")
      modals.forEach((modal) => {
        modal.classList.remove("open")
      })
    }
  })
}

function refreshDashboard() {
  // Only refresh if user is still on the page and tab is visible
  if (document.visibilityState === "visible") {
    // Refresh stats without full page reload
    fetch(window.location.href)
      .then((response) => response.text())
      .then((html) => {
        const parser = new DOMParser()
        const newDoc = parser.parseFromString(html, "text/html")

        // Update stats cards
        const currentStats = document.querySelectorAll(".stat-number")
        const newStats = newDoc.querySelectorAll(".stat-number")

        currentStats.forEach((stat, index) => {
          if (newStats[index] && stat.textContent !== newStats[index].textContent) {
            stat.textContent = newStats[index].textContent
            stat.parentElement.classList.add("updated")
            setTimeout(() => {
              stat.parentElement.classList.remove("updated")
            }, 2000)
          }
        })
      })
      .catch((error) => {
        console.error("Failed to refresh dashboard:", error)
      })
  }
}

// Utility functions for admin operations
function showNotification(message, type = "info") {
  const notification = document.createElement("div")
  notification.className = `notification ${type}`
  notification.textContent = message

  // Style the notification
  Object.assign(notification.style, {
    position: "fixed",
    top: "20px",
    right: "20px",
    padding: "1rem 1.5rem",
    borderRadius: "var(--radius)",
    color: "white",
    fontWeight: "500",
    zIndex: "9999",
    transform: "translateX(100%)",
    transition: "transform 0.3s ease",
  })

  // Set background color based on type
  const colors = {
    success: "#059669",
    error: "#dc2626",
    warning: "#f59e0b",
    info: "#3b82f6",
  }
  notification.style.backgroundColor = colors[type] || colors.info

  document.body.appendChild(notification)

  // Animate in
  setTimeout(() => {
    notification.style.transform = "translateX(0)"
  }, 100)

  // Auto-remove after 5 seconds
  setTimeout(() => {
    notification.style.transform = "translateX(100%)"
    setTimeout(() => {
      document.body.removeChild(notification)
    }, 300)
  }, 5000)
}

// Export functions for use in other admin pages
window.adminUtils = {
  showNotification,
  refreshDashboard,
}
