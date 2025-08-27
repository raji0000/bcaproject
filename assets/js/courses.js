// Courses page JavaScript
document.addEventListener("DOMContentLoaded", () => {
  // Filter functionality
  const filterBtns = document.querySelectorAll(".filter-btn")
  const courseCards = document.querySelectorAll(".course-card")
  const searchInput = document.getElementById("courseSearch")
  const searchBtn = document.getElementById("searchBtn")
  const resultsCount = document.getElementById("resultsCount")
  const loadMoreBtn = document.getElementById("loadMoreBtn")

  const currentFilters = {
    difficulty: "all",
    duration: "all",
    search: "",
  }

  // Filter button event listeners
  filterBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const filterType = btn.dataset.type
      const filterValue = btn.dataset.filter

      // Update active state
      document.querySelectorAll(`[data-type="${filterType}"]`).forEach((b) => b.classList.remove("active"))
      btn.classList.add("active")

      // Update current filters
      currentFilters[filterType] = filterValue

      // Apply filters
      applyFilters()
    })
  })

  // Search functionality
  searchInput.addEventListener("input", () => {
    currentFilters.search = searchInput.value.toLowerCase()
    applyFilters()
  })

  searchBtn.addEventListener("click", () => {
    currentFilters.search = searchInput.value.toLowerCase()
    applyFilters()
  })

  // Apply filters function
  function applyFilters() {
    let visibleCount = 0

    courseCards.forEach((card) => {
      let isVisible = true

      // Difficulty filter
      if (currentFilters.difficulty !== "all") {
        const cardDifficulty = card.dataset.difficulty
        if (cardDifficulty !== currentFilters.difficulty) {
          isVisible = false
        }
      }

      // Duration filter
      if (currentFilters.duration !== "all" && isVisible) {
        const cardDuration = Number.parseInt(card.dataset.duration)
        switch (currentFilters.duration) {
          case "short":
            if (cardDuration >= 5) isVisible = false
            break
          case "medium":
            if (cardDuration < 5 || cardDuration > 15) isVisible = false
            break
          case "long":
            if (cardDuration <= 15) isVisible = false
            break
        }
      }

      // Search filter
      if (currentFilters.search && isVisible) {
        const cardTitle = card.querySelector(".course-title").textContent.toLowerCase()
        const cardDescription = card.querySelector(".course-description").textContent.toLowerCase()
        if (!cardTitle.includes(currentFilters.search) && !cardDescription.includes(currentFilters.search)) {
          isVisible = false
        }
      }

      // Show/hide card
      if (isVisible) {
        card.style.display = "block"
        visibleCount++
      } else {
        card.style.display = "none"
      }
    })

    // Update results count
    updateResultsCount(visibleCount)
  }

  function updateResultsCount(count) {
    const total = courseCards.length
    if (count === total) {
      resultsCount.textContent = "Showing all courses"
    } else {
      resultsCount.textContent = `Showing ${count} of ${total} courses`
    }
  }

  // Load more functionality (placeholder)
  loadMoreBtn.addEventListener("click", () => {
    loadMoreBtn.textContent = "Loading..."
    loadMoreBtn.disabled = true

    // Simulate loading delay
    setTimeout(() => {
      loadMoreBtn.textContent = "Load More Courses"
      loadMoreBtn.disabled = false
      // In a real implementation, you would load more courses here
    }, 1000)
  })

  // Course card hover effects
  courseCards.forEach((card) => {
    card.addEventListener("mouseenter", () => {
      card.style.transform = "translateY(-4px)"
    })

    card.addEventListener("mouseleave", () => {
      card.style.transform = "translateY(0)"
    })
  })

  // Animate course cards on scroll
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1"
        entry.target.style.transform = "translateY(0)"
      }
    })
  }, observerOptions)

  // Initially hide cards for animation
  courseCards.forEach((card, index) => {
    card.style.opacity = "0"
    card.style.transform = "translateY(20px)"
    card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`
    observer.observe(card)
  })

  // Initialize results count
  updateResultsCount(courseCards.length)
})
