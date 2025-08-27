// CTF Platform JavaScript
document.addEventListener("DOMContentLoaded", () => {
  // Initialize CTF platform
  initializeCTF()

  // Load challenges if on challenges page
  if (document.querySelector(".challenges-section")) {
    loadChallenges()
  }
})

function initializeCTF() {
  // Category card click handlers
  const categoryCards = document.querySelectorAll(".category-card")
  categoryCards.forEach((card) => {
    card.addEventListener("click", function () {
      const category = this.dataset.category
      showChallenges(category)
    })
  })

  // Search functionality
  const searchInput = document.querySelector(".search-input")
  if (searchInput) {
    searchInput.addEventListener("input", debounce(filterChallenges, 300))
  }

  // Difficulty filter
  const difficultyFilter = document.querySelector(".difficulty-filter")
  if (difficultyFilter) {
    difficultyFilter.addEventListener("change", filterChallenges)
  }

  // Back button
  const backBtn = document.querySelector(".back-btn")
  if (backBtn) {
    backBtn.addEventListener("click", showCategories)
  }
}

function showChallenges(category) {
  // Hide categories section
  const categoriesSection = document.querySelector(".categories-section")
  const challengesSection = document.querySelector(".challenges-section")

  if (categoriesSection && challengesSection) {
    categoriesSection.style.display = "none"
    challengesSection.style.display = "block"

    // Update challenges title
    const challengesTitle = document.querySelector(".challenges-title")
    if (challengesTitle) {
      challengesTitle.textContent = `${category} Challenges`
    }

    // Load challenges for category
    loadChallenges(category)
  }
}

function showCategories() {
  const categoriesSection = document.querySelector(".categories-section")
  const challengesSection = document.querySelector(".challenges-section")

  if (categoriesSection && challengesSection) {
    categoriesSection.style.display = "block"
    challengesSection.style.display = "none"
  }
}

async function loadChallenges(category = null) {
  try {
    const url = category ? `api/get-challenges.php?category=${encodeURIComponent(category)}` : "api/get-challenges.php"
    const response = await fetch(url)
    const data = await response.json()

    if (data.success) {
      displayChallenges(data.challenges)
    } else {
      console.error("Failed to load challenges:", data.message)
    }
  } catch (error) {
    console.error("Error loading challenges:", error)
  }
}

function displayChallenges(challenges) {
  const challengesGrid = document.querySelector(".challenges-grid")
  if (!challengesGrid) return

  challengesGrid.innerHTML = ""

  challenges.forEach((challenge) => {
    const challengeCard = createChallengeCard(challenge)
    challengesGrid.appendChild(challengeCard)
  })
}

function createChallengeCard(challenge) {
  const card = document.createElement("div")
  card.className = "challenge-card card"
  card.dataset.challengeId = challenge.id
  card.dataset.difficulty = challenge.difficulty.toLowerCase()
  card.dataset.title = challenge.title.toLowerCase()

  card.innerHTML = `
        <div class="challenge-card-header">
            <div>
                <h3 class="challenge-card-title">${escapeHtml(challenge.title)}</h3>
                <div class="challenge-card-meta">
                    <span class="category-tag ${challenge.category.toLowerCase()}">${challenge.category}</span>
                    <span class="difficulty-tag ${challenge.difficulty.toLowerCase()}">${challenge.difficulty}</span>
                    <span class="challenge-card-points">${challenge.points} pts</span>
                </div>
            </div>
        </div>
        <p class="challenge-card-description">${escapeHtml(challenge.description)}</p>
        <div class="challenge-card-footer">
            <span class="challenge-card-solves">${challenge.solve_count} solves</span>
            ${challenge.is_solved ? '<span class="solved-indicator">✓ Solved</span>' : ""}
        </div>
    `

  card.addEventListener("click", () => {
    window.location.href = `challenge.php?id=${challenge.id}`
  })

  return card
}

function filterChallenges() {
  const searchTerm = document.querySelector(".search-input")?.value.toLowerCase() || ""
  const difficultyFilter = document.querySelector(".difficulty-filter")?.value || ""

  const challengeCards = document.querySelectorAll(".challenge-card")

  challengeCards.forEach((card) => {
    const title = card.dataset.title
    const difficulty = card.dataset.difficulty

    const matchesSearch = title.includes(searchTerm)
    const matchesDifficulty = !difficultyFilter || difficulty === difficultyFilter

    if (matchesSearch && matchesDifficulty) {
      card.style.display = "block"
    } else {
      card.style.display = "none"
    }
  })
}

// Utility functions
function debounce(func, wait) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

function escapeHtml(text) {
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  }
  return text.replace(/[&<>"']/g, (m) => map[m])
}
