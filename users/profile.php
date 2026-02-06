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

<!-- FLASH MESSAGE -->
  <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert" id="flashMessage"><?= $_SESSION['flash']; unset($_SESSION['flash']); ?></div>
    <script>
      setTimeout(() => {
        const alert = document.getElementById('flashMessage');
        if (alert) alert.remove();
      }, 3000);
    </script>
  <?php endif; ?>

<!-- DELETE FLASH MESSAGE -->
  <?php if (isset($_SESSION['delete_flash'])): ?>
    <div class="delete-alert" id="deleteFlash">
      <?= $_SESSION['delete_flash']; unset($_SESSION['delete_flash']); ?>
    </div>
    <script>
      setTimeout(() => {
        const alert = document.getElementById('deleteFlash');
        if (alert) alert.remove();
      }, 3000);
    </script>
  <?php endif; ?>

<section class="dashboard-content">
  <section class="table-section">
    <h2>Profile Settings</h2>

    <div class="form-container">
        <form action="../handlers/update_profile.php" method="POST" class="form-box">

        <?php require_once __DIR__ . '/../function/csrf.php'; ?>
        <?= csrf_field(); ?>

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

                <option value="Administration" <?= $user['dept'] === 'Administration' ? 'selected' : '' ?>>
                Office
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

        <label for="password">New Password</label>
        <!-- New Password -->
        <input
            type="password"
            name="password"
            id="password"
            placeholder="8–20 characters (letters, numbers & symbols)"
            autocomplete="new-password"
        >

        <!-- Password Strength Indicator -->
        <div class="password-strength">
        <div class="strength-box"></div>
        <div class="strength-box"></div>
        <div class="strength-box"></div>
        </div>

        <small id="passwordMessage" class="password-message">
            Must be 8–20 characters and include letters, numbers, and symbols
        </small>

        <br>

        <!-- Confirm New Password -->
        <label for="confirm_password">Confirm Password</label>
        <input
            type="password"
            name="confirm_password"
            id="confirm_password"
            placeholder="Confirm new password"
            autocomplete="new-password"
        >
        <small id="confirmPasswordIndicator" class="password-message"></small>


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
<script src="../js/profile.js"></script>

</body>
</html>
