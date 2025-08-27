// Scoreboard JavaScript
document.addEventListener("DOMContentLoaded", () => {
  // Auto-refresh scoreboard every 30 seconds
  setInterval(refreshScoreboard, 30000)

  // Add smooth scrolling for better UX
  addSmoothScrolling()
})

function refreshScoreboard() {
  // Only refresh if user is still on the page
  if (document.visibilityState === "visible") {
    location.reload()
  }
}

function addSmoothScrolling() {
  // Add smooth scrolling to any anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute("href"))
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })
}
