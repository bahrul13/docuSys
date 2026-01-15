<?php
session_start();
include 'db/db_conn.php';
include 'includes/register.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="icon" type="image/png" href="/uploads/dms.png">

    <title>Register</title>

    <style>
      /* Password indicator boxes */
      .password-strength {
        display: flex;
        gap: 6px;
        margin: 8px 0 2px;
        width: 100%;
        justify-content: center;
      }

      .strength-box {
        width: 28%;
        height: 6px;
        border-radius: 6px;
        background: #ddd;
        transition: 0.2s ease;
      }

      .strength-box.active.weak { background: #e74c3c; }
      .strength-box.active.medium { background: #f1c40f; }
      .strength-box.active.strong { background: #2ecc71; }

      .password-message {
        display: block;
        text-align: center;
        font-size: 12px;
        margin-bottom: 10px;
        color: #555;
      }
    </style>
</head>
<body>
<div class="container">
    <div class="forms">
        <div class="form login">
            <div class="logo">
                <img src="../uploads/dms.png" alt="Logo" />
            </div>

            <span class="title">Create Account</span>

            <form method="POST">
                <div class="input-field">
                    <input type="text" name="fullname" placeholder="Full Name" required>
                    <i class="uil uil-user"></i>
                </div>

                <div class="input-field">
                    <select name="department" required>
                        <option value="" disabled selected hidden>Select Department</option>
                        <option value="CETC">College of Engineering, Technology and Computing</option>
                        <option value="CTED">College of Teacher Education</option>
                        <option value="CBPA">College of Business and Public Administration</option>
                        <option value="CAS">College of Arts and Science</option>
                        <option value="CIS">College of Islamic Studies</option>
                        <option value="CAFi">College of Agriculture and Fisheries</option>
                        <option value="GS">Graduate School</option>
                    </select>
                    <i class="uil uil-building"></i>
                </div>

                <div class="input-field">
                    <input type="email" name="email" placeholder="Email Address" required>
                    <i class="uil uil-envelope"></i>
                </div>

                <!-- ✅ Password -->
                <div class="input-field">
                    <input
                      type="password"
                      name="password"
                      id="reg_password"
                      placeholder="8–12 characters (letters, numbers & symbols)"
                      required
                      autocomplete="new-password"
                    >
                    <i class="uil uil-lock icon"></i>
                </div>

                <!-- ✅ Strength Indicator -->
                <div class="password-strength">
                  <div class="strength-box"></div>
                  <div class="strength-box"></div>
                  <div class="strength-box"></div>
                </div>
                <small id="regPasswordMessage" class="password-message">
                  Must be 8–12 characters and include letters, numbers, and symbols
                </small>

                <div class="input-field button">
                    <input type="submit" value="Create Account">
                </div>
            </form>

            <div style="text-align:center; margin-top:15px;">
                <span class="text">Already have an account?</span>
                <a href="../index.php" style="color:#4070f4; font-weight:500;">
                    Login here
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($error) || !empty($success)): ?>
<div id="popupModal" class="modal">
    <div class="modal-content <?= !empty($error) ? 'error' : 'success' ?>">
        <h3>
            <?= !empty($error) ? '❌ Error' : '✅ Success' ?>
        </h3>
        <p>
            <?= htmlspecialchars(!empty($error) ? $error : $success) ?>
        </p>
        <button onclick="closePopup()">OK</button>
    </div>
</div>
<?php endif; ?>

<script>
function closePopup() {
    document.getElementById("popupModal").style.display = "none";
    <?php if (!empty($success)): ?>
        window.location.href = "index.php";
    <?php endif; ?>
}
</script>

<!-- ✅ Password Strength Script -->
<script>
const regPasswordInput = document.getElementById("reg_password");
const regStrengthBoxes = document.querySelectorAll(".password-strength .strength-box");
const regMsg = document.getElementById("regPasswordMessage");

// 8–12 chars, letter, number, special char, no spaces
const regStrongRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9])[^\s]{8,12}$/;

regPasswordInput.addEventListener("input", () => {
  const value = regPasswordInput.value;

  regStrengthBoxes.forEach(b => b.className = "strength-box");
  regMsg.style.color = "#555";

  if (value.length === 0) {
    regMsg.textContent = "Must be 8–12 characters and include letters, numbers, and symbols";
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
    regMsg.textContent = "❌ Invalid format (8–12 chars, no spaces)";
    regMsg.style.color = "#e74c3c";
  }
});
</script>

</body>
</html>
