// Dashboard JavaScript
document.addEventListener("DOMContentLoaded", () => {
  // User menu toggle
  const userMenuToggle = document.getElementById("userMenuToggle")
  const userMenuDropdown = document.getElementById("userMenuDropdown")

  if (userMenuToggle && userMenuDropdown) {
    userMenuToggle.addEventListener("click", (e) => {
      e.stopPropagation()
      userMenuToggle.classList.toggle("active")
      userMenuDropdown.classList.toggle("active")
    })

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
      if (!userMenuToggle.contains(e.target) && !userMenuDropdown.contains(e.target)) {
        userMenuToggle.classList.remove("active")
        userMenuDropdown.classList.remove("active")
      }
    })
  }

  // Animate stats on scroll
  const observerOptions = {
    threshold: 0.5,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const statNumber = entry.target.querySelector(".stat-number")
        if (statNumber && !statNumber.classList.contains("animated")) {
          animateNumber(statNumber)
          statNumber.classList.add("animated")
        }
      }
    })
  }, observerOptions)

  // Observe stat cards
  document.querySelectorAll(".stat-card").forEach((card) => {
    observer.observe(card)
  })

  // Add loading states for action cards
  document.querySelectorAll(".action-card").forEach((card) => {
    card.addEventListener("click", (e) => {
      // Add loading state
      card.style.opacity = "0.7"
      card.style.pointerEvents = "none"

      // Reset after navigation (in case of same-page navigation)
      setTimeout(() => {
        card.style.opacity = "1"
        card.style.pointerEvents = "auto"
      }, 2000)
    })
  })
})

function animateNumber(element) {
  const finalNumber = Number.parseInt(element.textContent)
  const duration = 1000
  const steps = 30
  const increment = finalNumber / steps
  let current = 0

  const timer = setInterval(() => {
    current += increment
    if (current >= finalNumber) {
      element.textContent = finalNumber
      clearInterval(timer)
    } else {
      element.textContent = Math.floor(current)
    }
  }, duration / steps)
}

// Add smooth transitions for better UX
const style = document.createElement("style")
style.textContent = `
    .stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .action-card {
        transition: all 0.3s ease;
    }
    
    .activity-item {
        transition: background-color 0.2s ease;
    }
    
    .activity-item:hover {
        background-color: var(--muted);
    }
`
document.head.appendChild(style)
