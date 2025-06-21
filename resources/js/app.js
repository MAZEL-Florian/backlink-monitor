import "./bootstrap"

document.addEventListener("DOMContentLoaded", () => {
  setTimeout(() => {
    const flashMessages = document.querySelectorAll('[data-flash-message="true"]')

    flashMessages.forEach((message) => {
      message.style.transition = "opacity 0.5s"
      message.style.opacity = "0"
      setTimeout(() => {
        if (message.parentNode) {
          message.parentNode.removeChild(message)
        }
      }, 500)
    })
  }, 5000)

  const userMenuButton = document.getElementById("user-menu-button")
  if (userMenuButton) {
    userMenuButton.addEventListener("click", () => {
      console.log("User menu clicked")
    })
  }
})
