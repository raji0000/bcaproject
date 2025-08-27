// Authentication JavaScript
document.addEventListener("DOMContentLoaded", () => {
  // Password toggle functionality
  const passwordToggles = document.querySelectorAll(".password-toggle")
  passwordToggles.forEach((toggle) => {
    toggle.addEventListener("click", () => {
      const input = toggle.parentElement.querySelector("input")
      const icon = toggle.querySelector(".password-toggle-icon")

      if (input.type === "password") {
        input.type = "text"
        icon.textContent = "🙈"
      } else {
        input.type = "password"
        icon.textContent = "👁️"
      }
    })
  })

  // Password strength checker
  const passwordInput = document.getElementById("password")
  const strengthIndicator = document.getElementById("passwordStrength")
  const strengthFill = document.getElementById("strengthFill")
  const strengthText = document.getElementById("strengthText")

  if (passwordInput && strengthIndicator) {
    passwordInput.addEventListener("input", () => {
      const password = passwordInput.value
      const strength = calculatePasswordStrength(password)

      // Remove all strength classes
      strengthIndicator.classList.remove("strength-weak", "strength-fair", "strength-good", "strength-strong")

      if (password.length === 0) {
        strengthText.textContent = "Password strength"
        return
      }

      // Add appropriate strength class
      if (strength.score <= 1) {
        strengthIndicator.classList.add("strength-weak")
        strengthText.textContent = "Weak password"
      } else if (strength.score <= 2) {
        strengthIndicator.classList.add("strength-fair")
        strengthText.textContent = "Fair password"
      } else if (strength.score <= 3) {
        strengthIndicator.classList.add("strength-good")
        strengthText.textContent = "Good password"
      } else {
        strengthIndicator.classList.add("strength-strong")
        strengthText.textContent = "Strong password"
      }
    })
  }

  // Form validation
  const loginForm = document.getElementById("loginForm")
  const registerForm = document.getElementById("registerForm")

  if (loginForm) {
    loginForm.addEventListener("submit", handleLogin)
  }

  if (registerForm) {
    registerForm.addEventListener("submit", handleRegister)
  }

  // Alert close functionality
  const alertCloses = document.querySelectorAll(".alert-close")
  alertCloses.forEach((close) => {
    close.addEventListener("click", () => {
      close.parentElement.style.display = "none"
    })
  })

  // Show alerts from URL parameters or session
  const urlParams = new URLSearchParams(window.location.search)
  const error = urlParams.get("error")
  const success = urlParams.get("success")

  if (error) {
    showAlert("error", decodeURIComponent(error))
  }
  if (success) {
    showAlert("success", decodeURIComponent(success))
  }
})

function calculatePasswordStrength(password) {
  let score = 0
  const checks = {
    length: password.length >= 8,
    lowercase: /[a-z]/.test(password),
    uppercase: /[A-Z]/.test(password),
    numbers: /\d/.test(password),
    symbols: /[^A-Za-z0-9]/.test(password),
  }

  // Calculate score
  Object.values(checks).forEach((check) => {
    if (check) score++
  })

  return { score, checks }
}

function handleLogin(e) {
  e.preventDefault()

  const form = e.target
  const formData = new FormData(form)
  const submitButton = document.getElementById("loginButton")
  const buttonText = submitButton.querySelector(".button-text")
  const buttonSpinner = submitButton.querySelector(".button-spinner")

  // Clear previous errors
  clearFormErrors()

  // Basic validation
  const email = formData.get("email")
  const password = formData.get("password")

  if (!email || !password) {
    showAlert("error", "Please fill in all required fields.")
    return
  }

  if (!isValidEmail(email)) {
    showFieldError("email", "Please enter a valid email address.")
    return
  }

  // Show loading state
  submitButton.disabled = true
  buttonText.style.display = "none"
  buttonSpinner.style.display = "block"

  // Submit form via AJAX
  fetch(form.action, {
    method: "POST",
    body: formData,
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert("success", data.message)
        setTimeout(() => {
          window.location.href = data.redirect || "dashboard.php"
        }, 1500)
      } else {
        showAlert("error", data.message)
      }
    })
    .catch((error) => {
      showAlert("error", "An error occurred. Please try again.")
      console.error("Login error:", error)
    })
    .finally(() => {
      // Reset loading state
      submitButton.disabled = false
      buttonText.style.display = "block"
      buttonSpinner.style.display = "none"
    })
}

function handleRegister(e) {
  e.preventDefault()

  const form = e.target
  const formData = new FormData(form)
  const submitButton = document.getElementById("registerButton")
  const buttonText = submitButton.querySelector(".button-text")
  const buttonSpinner = submitButton.querySelector(".button-spinner")

  // Clear previous errors
  clearFormErrors()

  // Validation
  const firstName = formData.get("first_name")
  const lastName = formData.get("last_name")
  const username = formData.get("username")
  const email = formData.get("email")
  const password = formData.get("password")
  const confirmPassword = formData.get("confirm_password")
  const terms = formData.get("terms")

  let hasErrors = false

  if (!firstName) {
    showFieldError("firstName", "First name is required.")
    hasErrors = true
  }

  if (!lastName) {
    showFieldError("lastName", "Last name is required.")
    hasErrors = true
  }

  if (!username) {
    showFieldError("username", "Username is required.")
    hasErrors = true
  } else if (!/^[a-zA-Z0-9_]{3,20}$/.test(username)) {
    showFieldError(
      "username",
      "Username must be 3-20 characters long and contain only letters, numbers, and underscores.",
    )
    hasErrors = true
  }

  if (!email) {
    showFieldError("email", "Email is required.")
    hasErrors = true
  } else if (!isValidEmail(email)) {
    showFieldError("email", "Please enter a valid email address.")
    hasErrors = true
  }

  if (!password) {
    showFieldError("password", "Password is required.")
    hasErrors = true
  } else {
    const strength = calculatePasswordStrength(password)
    if (strength.score < 3) {
      showFieldError(
        "password",
        "Password must be at least 8 characters long and contain uppercase, lowercase, and numbers.",
      )
      hasErrors = true
    }
  }

  if (!confirmPassword) {
    showFieldError("confirmPassword", "Please confirm your password.")
    hasErrors = true
  } else if (password !== confirmPassword) {
    showFieldError("confirmPassword", "Passwords do not match.")
    hasErrors = true
  }

  if (!terms) {
    showFieldError("terms", "You must agree to the Terms of Service and Privacy Policy.")
    hasErrors = true
  }

  if (hasErrors) {
    return
  }

  // Show loading state
  submitButton.disabled = true
  buttonText.style.display = "none"
  buttonSpinner.style.display = "block"

  // Submit form via AJAX
  fetch(form.action, {
    method: "POST",
    body: formData,
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert("success", data.message)
        setTimeout(() => {
          window.location.href = data.redirect || "dashboard.php"
        }, 1500)
      } else {
        showAlert("error", data.message)
      }
    })
    .catch((error) => {
      showAlert("error", "An error occurred. Please try again.")
      console.error("Registration error:", error)
    })
    .finally(() => {
      // Reset loading state
      submitButton.disabled = false
      buttonText.style.display = "block"
      buttonSpinner.style.display = "none"
    })
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

function showAlert(type, message) {
  const alertId = type === "error" ? "errorAlert" : "successAlert"
  const messageId = type === "error" ? "errorMessage" : "successMessage"

  const alert = document.getElementById(alertId)
  const messageElement = document.getElementById(messageId)

  if (alert && messageElement) {
    messageElement.textContent = message
    alert.style.display = "flex"

    // Auto-hide success alerts after 5 seconds
    if (type === "success") {
      setTimeout(() => {
        alert.style.display = "none"
      }, 5000)
    }
  }
}

function showFieldError(fieldName, message) {
  const errorElement = document.getElementById(fieldName + "Error")
  if (errorElement) {
    errorElement.textContent = message
  }
}

function clearFormErrors() {
  const errorElements = document.querySelectorAll(".form-error")
  errorElements.forEach((element) => {
    element.textContent = ""
  })

  const alerts = document.querySelectorAll(".alert")
  alerts.forEach((alert) => {
    alert.style.display = "none"
  })
}
