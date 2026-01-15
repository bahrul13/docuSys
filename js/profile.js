const passwordInput = document.getElementById("password");
        const strengthBoxes = document.querySelectorAll(".strength-box");
        const message = document.getElementById("passwordMessage");

        // 8–20 chars, letter, number, special char, no spaces
        const strongRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9])[^\s]{8,20}$/;

        passwordInput.addEventListener("input", () => {
        const value = passwordInput.value;

        // Reset UI
        strengthBoxes.forEach(box => box.className = "strength-box");
        message.style.color = "#555";

        if (value.length === 0) {
            message.textContent = "Must be 8–20 characters and include letters, numbers, and symbols";
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
            return;
        }

        // Medium
        if (hasLetter && hasNumber && !hasSymbol) {
            strengthBoxes[0].classList.add("active", "medium");
            strengthBoxes[1].classList.add("active", "medium");
            message.textContent = "⚠️ Medium password (add a symbol)";
            message.style.color = "#f1c40f";
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
        });