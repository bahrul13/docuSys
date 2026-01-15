<?php
// ================= SESSION CHECK =================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "../db/db_conn.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("
    SELECT fullname, dept, email 
    FROM user 
    WHERE id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profile Settings</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
    <!-- Favicon -->
  <link rel="icon" type="image/png" href="/uploads/dms.png">

</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
  <section class="table-section">
    <h2>Profile Settings</h2>

    <div class="form-container">
        <form action="../handlers/update_profile.php" method="POST" class="form-box">

        <!-- Full Name -->
        <label for="fullname">Full Name</label>
        <input
            type="text"
            name="fullname"
            id="fullname"
            value="<?= htmlspecialchars($user['fullname']) ?>"
            required
        >

        <!-- Department -->
        <label for="dept">Department</label>
        <div class="select-wrapper">
            <select name="dept" id="dept" required>
                <option value="" disabled hidden>Select Department</option>

                <option value="CETC" <?= $user['dept'] === 'CETC' ? 'selected' : '' ?>>
                College of Engineering, Technology and Computing
                </option>

                <option value="CTED" <?= $user['dept'] === 'CTED' ? 'selected' : '' ?>>
                College of Teacher Education
                </option>

                <option value="CBPA" <?= $user['dept'] === 'CBPA' ? 'selected' : '' ?>>
                College of Business and Public Administration
                </option>

                <option value="CAS" <?= $user['dept'] === 'CAS' ? 'selected' : '' ?>>
                College of Arts and Science
                </option>

                <option value="CIS" <?= $user['dept'] === 'CIS' ? 'selected' : '' ?>>
                College of Islamic Studies
                </option>

                <option value="CAFi" <?= $user['dept'] === 'CAFi' ? 'selected' : '' ?>>
                College of Agriculture and Fisheries
                </option>

                <option value="GS" <?= $user['dept'] === 'GS' ? 'selected' : '' ?>>
                Graduate School
                </option>
            </select>
            <i class="bx bx-chevron-down select-icon"></i>
        </div>

        <!-- Email (Read-only) -->
        <label for="email">Email Address</label>
        <input
            type="email"
            name="email"
            id="email"
            value="<?= htmlspecialchars($user['email']) ?>"
            readonly
        >

        <!-- New Password -->
        <input
            type="password"
            name="password"
            id="password"
            placeholder="8–12 characters (letters, numbers & symbols)"
            autocomplete="new-password"
        >

        <!-- Password Strength Indicator -->
        <div class="password-strength">
        <div class="strength-box"></div>
        <div class="strength-box"></div>
        <div class="strength-box"></div>
        </div>

        <small id="passwordMessage" class="password-message">
            Must be 8–12 characters and include letters, numbers, and symbols
        </small>

        <!-- Buttons -->
        <div class="form-buttons">
            <button type="button" class="btn-add" onclick="openProfileUpdateConfirm()">
                Save Changes
            </button>

            <a href="../users/dashboard.php" class="btn-cancel">Cancel</a>
        </div>

        </form>
    </div>
  </section>
</section>
    <!-- CONFIRM PROFILE UPDATE MODAL -->
    <div id="profileUpdateConfirmModal" class="modal">
    <div class="modal-content">
        <h1>Confirm Update</h1>
        <p>Are you sure you want to update your profile information?</p>

        <div class="modal-actions">
        <button class="btn-add" onclick="submitProfileUpdate()">
            Yes, Update
        </button>
        <button class="btn-cancel" onclick="closeProfileUpdateConfirm()">
            Cancel
        </button>
        </div>
    </div>
    </div>

    <script>
        const profileUpdateModal = document.getElementById('profileUpdateConfirmModal');
        const profileForm = document.querySelector('.form-box');

        function openProfileUpdateConfirm() {
            profileUpdateModal.style.display = 'block';
        }

        function closeProfileUpdateConfirm() {
            profileUpdateModal.style.display = 'none';
        }

        function submitProfileUpdate() {
            profileForm.submit();
        }

        window.onclick = function (e) {
            if (e.target === profileUpdateModal) {
            profileUpdateModal.style.display = 'none';
            }
        };
    </script>
    <script>
        const passwordInput = document.getElementById("password");
        const strengthBoxes = document.querySelectorAll(".strength-box");
        const message = document.getElementById("passwordMessage");

        // 8–12 chars, letter, number, special char, no spaces
        const strongRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9])[^\s]{8,12}$/;

        passwordInput.addEventListener("input", () => {
        const value = passwordInput.value;

        // Reset UI
        strengthBoxes.forEach(box => box.className = "strength-box");
        message.style.color = "#555";

        if (value.length === 0) {
            message.textContent = "Must be 8–12 characters and include letters, numbers, and symbols";
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
            message.textContent = "❌ Invalid format (8–12 chars, no spaces)";
            message.style.color = "#e74c3c";
        }
        });
    </script>


</body>
</html>
