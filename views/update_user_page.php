<?php
session_start();
require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';

// 🔐 Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// ❌ No ID
if (!isset($_GET['id'])) {
    header("Location: ../users/user.php");
    exit();
}

$id = intval($_GET['id']);

// 📄 Fetch user record
$stmt = $conn->prepare("SELECT id, fullname, dept, email, role, status, date_created FROM user WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['flash'] = "❌ User not found.";
    header("Location: ../users/user.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update User</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
<link rel="stylesheet" href="../css/style.css">
<link rel="icon" type="image/png" href="/uploads/dms.png">
</head>

<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
  <section class="table-section">
    <h2>Update User</h2>

    <div class="form-container">
      <form action="../handlers/update_user.php" method="POST" enctype="multipart/form-data" class="form-box">

      <?= csrf_field(); ?>

        <!-- Hidden ID -->
        <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

        <!-- Fullname -->
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
            Offices
            </option>
        </select>

        <i class="bx bx-chevron-down select-icon"></i>
        </div>


        <!-- Email -->
        <label for="email">Email</label>
        <input
          type="email"
          name="email"
          id="email"
          value="<?= htmlspecialchars($user['email']) ?>"
          required
        >

        <!-- Password (optional) -->
        <label for="password">Password (optional)</label>
        <input
          type="password"
          name="password"
          id="password"
          placeholder="Leave blank to keep current password"
        >

        <!-- Role -->
        <label for="role">Role</label>
        <div class="select-wrapper">
          <select name="role" id="role" required>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
            <option value="user"  <?= $user['role'] === 'user'  ? 'selected' : '' ?>>User</option>
          </select>
          <i class="bx bx-chevron-down select-icon"></i>
        </div>

        <!-- Buttons (same as SFR template) -->
        <div class="form-buttons">
          <button type="button" class="btn-add" onclick="openUserUpdateConfirm()">Update</button>
          <button type="button" class="btn-cancel" onclick="openUserCancelConfirm()">Cancel</button>
        </div>

      </form>
    </div>
  </section>
</section>

<!-- ✅ Confirm Update Modal -->
<div id="userUpdateConfirmModal" class="modal">
  <div class="modal-content">
    <h1>Confirm Update</h1>
    <p>Are you sure you want to update this user?</p>
    <div class="modal-actions">
      <button class="btn-add" onclick="submitUserUpdate()">Yes, Update</button>
      <button class="btn-cancel" onclick="closeUserUpdateConfirm()">Cancel</button>
    </div>
  </div>
</div>

<!-- ✅ Confirm Cancel Modal -->
<div id="userCancelConfirmModal" class="modal">
  <div class="modal-content">
    <h1>Cancel Update</h1>
    <p>Unsaved changes will be lost. Continue?</p>
    <div class="modal-actions">
      <button class="btn-delete-confirm" onclick="confirmUserCancel()">Yes, Leave</button>
      <button class="btn-cancel" onclick="closeUserCancelConfirm()">Stay</button>
    </div>
  </div>
</div>

<script>
  const userUpdateModal = document.getElementById('userUpdateConfirmModal');
  const userCancelModal = document.getElementById('userCancelConfirmModal');
  const userForm = document.querySelector('.form-box');

  function openUserUpdateConfirm() { userUpdateModal.style.display = 'block'; }
  function closeUserUpdateConfirm() { userUpdateModal.style.display = 'none'; }
  function submitUserUpdate() { userForm.submit(); }

  function openUserCancelConfirm() { userCancelModal.style.display = 'block'; }
  function closeUserCancelConfirm() { userCancelModal.style.display = 'none'; }
  function confirmUserCancel() { window.location.href = "../users/user.php"; }

  window.onclick = e => {
    if (e.target === userUpdateModal) userUpdateModal.style.display = 'none';
    if (e.target === userCancelModal) userCancelModal.style.display = 'none';
  };
</script>

<?php $conn->close(); ?>

</body>
</html>
