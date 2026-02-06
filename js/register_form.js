const regPasswordInput = document.getElementById("reg_password");
const regConfirmInput  = document.getElementById("reg_confirm_password");
const regStrengthBoxes = document.querySelectorAll(".password-strength .strength-box");
const regMsg           = document.getElementById("regPasswordMessage");
const regConfirmMsg    = document.getElementById("regConfirmMessage");

// 8–20 chars, letter, number, special char, no spaces
const regStrongRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9])[^\s]{8,20}$/;

function updateConfirmIndicator() {
  if (!regConfirmInput || !regConfirmMsg) return;

  const pw  = regPasswordInput.value;
  const cpw = regConfirmInput.value;

  // Hide indicator when confirm is empty (clean UI)
  if (cpw.length === 0) {
    regConfirmMsg.textContent = "";
    regConfirmMsg.className = "password-message";
    return;
  }

  // If password is empty but confirm has value
  if (pw.length === 0) {
    regConfirmMsg.textContent = "✖ Please enter password first";
    regConfirmMsg.className = "password-message mismatch";
    return;
  }

  if (pw === cpw) {
    regConfirmMsg.textContent = "✔ Passwords match";
    regConfirmMsg.className = "password-message match";
  } else {
    regConfirmMsg.textContent = "✖ Passwords do not match";
    regConfirmMsg.className = "password-message mismatch";
  }
}

regPasswordInput.addEventListener("input", () => {
  const value = regPasswordInput.value;

  regStrengthBoxes.forEach(b => b.className = "strength-box");
  regMsg.style.color = "#555";

  if (value.length === 0) {
    regMsg.textContent = "Must be 8–20 characters and include letters, numbers, and symbols";
    updateConfirmIndicator(); // keep confirm indicator in sync
    return;
  }

  const hasLetter = /[A-Za-z]/.test(value);
  const hasNumber = /\d/.test(value);
  const hasSymbol = /[^A-Za-z0-9]/.test(value);

  // Weak
  if (value.length < 8 || !hasLetter) {
    regStrengthBoxes[0].classList.add("active", "weak");
    regMsg.textContent = "❌ Weak password";
    regMsg.style.color = "#e74c3c";
    updateConfirmIndicator();
    return;
  }

  // Medium: letter+number but missing symbol
  if (hasLetter && hasNumber && !hasSymbol) {
    regStrengthBoxes[0].classList.add("active", "medium");
    regStrengthBoxes[1].classList.add("active", "medium");
    regMsg.textContent = "⚠️ Medium password (add a symbol)";
    regMsg.style.color = "#f1c40f";
    updateConfirmIndicator();
    return;
  }

  // Strong
  if (regStrongRegex.test(value)) {
    regStrengthBoxes.forEach(b => b.classList.add("active", "strong"));
    regMsg.textContent = "✅ Strong password";
    regMsg.style.color = "#2ecc71";
  } else {
    regStrengthBoxes[0].classList.add("active", "weak");
    regMsg.textContent = "❌ Invalid format (8–20 chars, no spaces)";
    regMsg.style.color = "#e74c3c";
  }

  updateConfirmIndicator();
});

// Confirm password live indicator
if (regConfirmInput) {
  regConfirmInput.addEventListener("input", updateConfirmIndicator);
}
