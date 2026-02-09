<?php
session_start();
require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add User</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
    <!-- Favicon -->
  <link rel="icon" type="image/png" href="/uploads/dms.png">

</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
  <section class="table-section">
    <h2>Add User</h2>

    <div class="form-container">
      <form action="../handlers/add_user.php" method="POST" enctype="multipart/form-data" class="form-box">
        <?= csrf_field(); ?>
        <label for="fullname">Name</label>
        <input type="text" name="fullname" id="fullname" required>

        <label for="email">Email Address</label>
        <input type="text" name="email" id="email" required>

        <label for="department">Department</label>
        <div class="select-wrapper">
          <select name="department" id="department" required>
            <option value="" disabled selected hidden>Select Department</option>
            <option value="CETC">College of Engineering, Technology and Computing</option>
            <option value="CTED">College of Teacher Education</option>
            <option value="CBPA">College of Business and Public Administration</option>
            <option value="CAS">College of Arts and Science</option>
            <option value="CIS">College of Islamic Studies</option>
            <option value="CAFi">College of Agriculture and Fisheries</option>
            <option value="GS">Graduate School</option>
            <option value="Administration">Offices</option>
          </select>
          <i class="bx bx-chevron-down select-icon"></i>
        </div>

        <div class="input-wrapper">
          <label for="password">Password</label>
          <input type="password" name="password" id="password" required>
          <i class='bx bx-show' id="togglePassword"></i>
        </div>


        <label for="role">Role</label>
        <div class="select-wrapper">
          <select name="role" id="role" required>
            <option value="" disabled>Select Role</option>
            <option value="admin">Administrator</option>
            <option value="user" selected>User</option>
          </select>
          <i class="bx bx-chevron-down select-icon"></i>
        </div>


        <div class="form-buttons">
          <button type="submit" class="btn-add">Add User</button>
          <a href="../users/user.php" class="btn-cancel">Cancel</a>
        </div>
      </form>
    </div>
  </section>
</section>
<script src="../js/adduser.js"></script>
</body>
</html>
