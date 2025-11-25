<?php
session_start();
require '../db/db_conn.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
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

</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
  <h1>Add User</h1>

  <div class="form-container">
    <form action="../handlers/add_user.php" method="POST" enctype="multipart/form-data" class="form-box">
      <label for="fullname">Name</label>
      <input type="text" name="fullname" id="fullname" required>

      <label for="email">Email Address</label>
      <input type="text" name="email" id="email" required>

      <label for="password">Password</label>
      <input type="password" name="password" id="password" required>

      <label for="role">Role</label>
      <div class="select-wrapper">
        <select name="role" id="role" required>
          <option value="" disabled selected>Select Role</option>
          <option value="admin">Administrator</option>
          <option value="user">User</option>
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
</body>
</html>
