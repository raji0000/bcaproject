// Contact Form JavaScript
document.addEventListener("DOMContentLoaded", () => {
  const contactForm = document.getElementById("contactForm")
  const submitBtn = contactForm.querySelector(".submit-btn")
  const btnText = submitBtn.querySelector(".btn-text")
  const btnLoading = submitBtn.querySelector(".btn-loading")
  const formMessage = document.getElementById("formMessage")

  // Form submission
  contactForm.addEventListener("submit", async (e) => {
    e.preventDefault()

    // Clear previous errors
    clearErrors()

    // Show loading state
    setLoadingState(true)

    try {
      const formData = new FormData(contactForm)
      const response = await fetch("api/contact.php", {
        method: "POST",
        body: formData,
      })

      const data = await response.json()

      if (data.success) {
        showMessage(data.message, "success")
        contactForm.reset()
      } else {
        if (data.errors) {
          displayErrors(data.errors)
        } else {
          showMessage(data.message || "An error occurred. Please try again.", "error")
        }
      }
    } catch (error) {
      console.error("Form submission error:", error)
      showMessage("Network error. Please check your connection and try again.", "error")
    } finally {
      setLoadingState(false)
    }
  })

  // Real-time validation
  const inputs = contactForm.querySelectorAll("input, select, textarea")
  inputs.forEach((input) => {
    input.addEventListener("blur", () => validateField(input))
    input.addEventListener("input", () => clearFieldError(input))
  })

  function setLoadingState(loading) {
    submitBtn.disabled = loading
    btnText.style.display = loading ? "none" : "inline"
    btnLoading.style.display = loading ? "inline" : "none"
  }

  function showMessage(message, type) {
    formMessage.textContent = message
    formMessage.className = `form-message ${type}`
    formMessage.style.display = "block"

    // Auto-hide success messages after 5 seconds
    if (type === "success") {
      setTimeout(() => {
        formMessage.style.display = "none"
      }, 5000)
    }
  }

  function displayErrors(errors) {
    Object.keys(errors).forEach((field) => {
      const errorElement = document.getElementById(`${field}Error`)
      if (errorElement) {
        errorElement.textContent = errors[field]
        const input = document.getElementById(field) || document.querySelector(`[name="${field}"]`)
        if (input) {
          input.classList.add("error")
        }
      }
    })
  }

  function clearErrors() {
    const errorElements = contactForm.querySelectorAll(".error-message")
    errorElements.forEach((element) => {
      element.textContent = ""
    })

    const errorInputs = contactForm.querySelectorAll(".error")
    errorInputs.forEach((input) => {
      input.classList.remove("error")
    })

    formMessage.style.display = "none"
  }

  function clearFieldError(input) {
    const fieldName = input.name || input.id
    const errorElement = document.getElementById(`${fieldName}Error`)
    if (errorElement) {
      errorElement.textContent = ""
    }
    input.classList.remove("error")
  }

  function validateField(input) {
    const value = input.value.trim()
    const fieldName = input.name || input.id
    let error = ""

    switch (fieldName) {
      case "first_name":
      case "lastName":
        if (!value) {
          error = `${fieldName === "first_name" ? "First" : "Last"} name is required`
        } else if (value.length > 50) {
          error = `${fieldName === "first_name" ? "First" : "Last"} name must be less than 50 characters`
        }
        break

      case "email":
        if (!value) {
          error = "Email is required"
        } else if (!isValidEmail(value)) {
          error = "Please enter a valid email address"
        }
        break

      case "subject":
        if (!value) {
          error = "Subject is required"
        }
        break

      case "message":
        if (!value) {
          error = "Message is required"
        } else if (value.length < 10) {
          error = "Message must be at least 10 characters long"
        } else if (value.length > 2000) {
          error = "Message must be less than 2000 characters"
        }
        break
    }

    const errorElement = document.getElementById(`${fieldName}Error`)
    if (errorElement) {
      errorElement.textContent = error
    }

    if (error) {
      input.classList.add("error")
    } else {
      input.classList.remove("error")
    }

    return !error
  }

  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  // Character counter for message field
  const messageField = document.getElementById("message")
  if (messageField) {
    const counter = document.createElement("div")
    counter.className = "character-counter"
    counter.style.fontSize = "0.75rem"
    counter.style.color = "var(--muted-foreground)"
    counter.style.textAlign = "right"
    counter.style.marginTop = "0.25rem"

    messageField.parentNode.appendChild(counter)

    messageField.addEventListener("input", () => {
      const length = messageField.value.length
      counter.textContent = `${length}/2000 characters`

      if (length > 2000) {
        counter.style.color = "#dc2626"
      } else if (length > 1800) {
        counter.style.color = "#f59e0b"
      } else {
        counter.style.color = "var(--muted-foreground)"
      }
    })

    // Initialize counter
    messageField.dispatchEvent(new Event("input"))
  }
})
