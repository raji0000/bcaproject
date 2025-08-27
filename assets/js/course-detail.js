// Course detail page JavaScript
document.addEventListener("DOMContentLoaded", () => {
  const enrollBtn = document.getElementById("enrollBtn")
  const previewBtn = document.getElementById("previewBtn")
  const downloadCertificateBtn = document.getElementById("downloadCertificate")

  // Enrollment functionality
  if (enrollBtn) {
    enrollBtn.addEventListener("click", async () => {
      const courseId = enrollBtn.dataset.courseId
      const originalText = enrollBtn.textContent

      // Show loading state
      enrollBtn.textContent = "Enrolling..."
      enrollBtn.disabled = true

      try {
        const response = await fetch("api/enroll-course.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `course_id=${courseId}`,
        })

        const data = await response.json()

        if (data.success) {
          // Show success message
          showAlert("success", data.message)

          // Redirect after a short delay
          setTimeout(() => {
            window.location.reload()
          }, 1500)
        } else {
          showAlert("error", data.message)
          enrollBtn.textContent = originalText
          enrollBtn.disabled = false
        }
      } catch (error) {
        showAlert("error", "An error occurred. Please try again.")
        enrollBtn.textContent = originalText
        enrollBtn.disabled = false
      }
    })
  }

  // Preview functionality
  if (previewBtn) {
    previewBtn.addEventListener("click", () => {
      // In a real implementation, this would open a modal or redirect to a preview
      showAlert("info", "Course preview coming soon!")
    })
  }

  // Certificate download
  if (downloadCertificateBtn) {
    downloadCertificateBtn.addEventListener("click", () => {
      if (downloadCertificateBtn.disabled) {
        showAlert("error", "Complete the course to download your certificate.")
        return
      }

      // In a real implementation, this would generate and download a certificate
      showAlert("success", "Certificate download started!")
    })
  }

  // Lesson item interactions
  const lessonItems = document.querySelectorAll(".lesson-item")
  lessonItems.forEach((item) => {
    const lessonLink = item.querySelector(".lesson-link")
    const isLocked = item.classList.contains("locked")

    if (isLocked) {
      item.addEventListener("click", (e) => {
        e.preventDefault()
        showAlert("info", "Enroll in the course to access this lesson.")
      })
    }

    if (lessonLink) {
      lessonLink.addEventListener("click", (e) => {
        // Add loading state
        lessonLink.style.opacity = "0.7"
        lessonLink.style.pointerEvents = "none"
      })
    }
  })

  // Smooth scroll for curriculum section
  const curriculumSection = document.querySelector(".curriculum-section")
  if (curriculumSection) {
    const lessonHeaders = curriculumSection.querySelectorAll(".lesson-header")
    lessonHeaders.forEach((header) => {
      header.addEventListener("click", () => {
        const lessonItem = header.parentElement
        const lessonPreview = lessonItem.querySelector(".lesson-preview")

        if (lessonPreview) {
          const isExpanded = lessonPreview.style.display === "block"
          lessonPreview.style.display = isExpanded ? "none" : "block"
        }
      })
    })
  }

  // Progress bar animation
  const progressFill = document.querySelector(".progress-fill")
  if (progressFill) {
    const targetWidth = progressFill.style.width
    progressFill.style.width = "0%"

    setTimeout(() => {
      progressFill.style.width = targetWidth
    }, 500)
  }
})

function showAlert(type, message) {
  // Create alert element
  const alert = document.createElement("div")
  alert.className = `alert alert-${type}`
  alert.innerHTML = `
        <span class="alert-message">${message}</span>
        <button class="alert-close">&times;</button>
    `

  // Add styles
  alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        padding: 1rem;
        border-radius: var(--radius);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        animation: slideIn 0.3s ease;
    `

  // Add type-specific styles
  if (type === "success") {
    alert.style.backgroundColor = "#f0fdf4"
    alert.style.color = "#166534"
    alert.style.border = "1px solid #bbf7d0"
  } else if (type === "error") {
    alert.style.backgroundColor = "#fef2f2"
    alert.style.color = "#991b1b"
    alert.style.border = "1px solid #fecaca"
  } else if (type === "info") {
    alert.style.backgroundColor = "#eff6ff"
    alert.style.color = "#1e40af"
    alert.style.border = "1px solid #bfdbfe"
  }

  // Add to page
  document.body.appendChild(alert)

  // Close functionality
  const closeBtn = alert.querySelector(".alert-close")
  closeBtn.addEventListener("click", () => {
    alert.remove()
  })

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (alert.parentElement) {
      alert.remove()
    }
  }, 5000)
}

// Add CSS animation
const style = document.createElement("style")
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .lesson-item {
        cursor: pointer;
    }
    
    .lesson-item.locked {
        cursor: not-allowed;
    }
    
    .lesson-preview {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
`
document.head.appendChild(style)
