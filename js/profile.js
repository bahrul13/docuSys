document.addEventListener("DOMContentLoaded", () => {

  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirm_password");
  const strengthBoxes = document.querySelectorAll(".strength-box");
  const message = document.getElementById("passwordMessage");
  const confirmMessage = document.getElementById("confirmPasswordIndicator");

  if (!passwordInput || strengthBoxes.length === 0 || !message) return;

  // 8–20 chars, letter, number, special char, no spaces
  const strongRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9])[^\s]{8,20}$/;

  function updateStrength() {
    const value = passwordInput.value;

    // Reset UI
    strengthBoxes.forEach(box => box.className = "strength-box");
    message.style.color = "#555";

    if (value.length === 0) {
      message.textContent =
        "Must be 8–20 characters and include letters, numbers, and symbols";
      updateConfirm(); // also reset confirm indicator
      return;
    }

    const hasLetter = /[A-Za-z]/.test(value);
    const hasNumber = /\d/.test(value);
    const hasSymbol = /[^A-Za-z0-9]/.test(value);

    // Weak
    if (value.length < 8 || !hasLetter) {
      strengthBoxes[0].classList.add("active", "weak");
      message.textContent = "❌ Weak password";
      message.style.color = "#e74c3c";
      updateConfirm();
      return;
    }

    // Medium
    if (hasLetter && hasNumber && !hasSymbol) {
      strengthBoxes[0].classList.add("active", "medium");
      strengthBoxes[1].classList.add("active", "medium");
      message.textContent = "⚠️ Medium password (add a symbol)";
      message.style.color = "#f1c40f";
      updateConfirm();
      return;
    }

    // Strong
    if (strongRegex.test(value)) {
      strengthBoxes.forEach(box => box.classList.add("active", "strong"));
      message.textContent = "✅ Strong password";
      message.style.color = "#2ecc71";
    } else {
      strengthBoxes[0].classList.add("active", "weak");
      message.textContent = "❌ Invalid format (8–20 chars, no spaces)";
      message.style.color = "#e74c3c";
    }

    updateConfirm();
  }

  function updateConfirm() {
    if (!confirmPasswordInput || !confirmMessage) return;

    // No password change → hide confirm indicator
    if (passwordInput.value === "" && confirmPasswordInput.value === "") {
      confirmMessage.textContent = "";
      confirmMessage.className = "password-message";
      return;
    }

    // User hasn't typed confirm yet
    if (confirmPasswordInput.value === "") {
      confirmMessage.textContent = "";
      confirmMessage.className = "password-message";
      return;
    }

    if (passwordInput.value === confirmPasswordInput.value) {
      confirmMessage.textContent = "✔ Passwords match";
      confirmMessage.className = "password-message match";
    } else {
      confirmMessage.textContent = "✖ Passwords do not match";
      confirmMessage.className = "password-message mismatch";
    }
  }

  passwordInput.addEventListener("input", updateStrength);
  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener("input", updateConfirm);
  }

});
