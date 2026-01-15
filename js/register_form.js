const regPasswordInput = document.getElementById("reg_password");
const regStrengthBoxes = document.querySelectorAll(".password-strength .strength-box");
const regMsg = document.getElementById("regPasswordMessage");

// 8–12 chars, letter, number, special char, no spaces
const regStrongRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9])[^\s]{8,20}$/;

regPasswordInput.addEventListener("input", () => {
  const value = regPasswordInput.value;

  regStrengthBoxes.forEach(b => b.className = "strength-box");
  regMsg.style.color = "#555";

  if (value.length === 0) {
    regMsg.textContent = "Must be 8–20 characters and include letters, numbers, and symbols";
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
    return;
  }

  // Medium: letter+number but missing symbol
  if (hasLetter && hasNumber && !hasSymbol) {
    regStrengthBoxes[0].classList.add("active", "medium");
    regStrengthBoxes[1].classList.add("active", "medium");
    regMsg.textContent = "⚠️ Medium password (add a symbol)";
    regMsg.style.color = "#f1c40f";
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
});