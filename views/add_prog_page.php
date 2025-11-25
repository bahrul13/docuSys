<?php
session_start();
require '../db/db_conn.php';

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
  <title>Add Program</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />

</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">

  <h1>Add Program</h1>

  <div class="form-container">
    <form action="../handlers/add_prog.php" method="POST" enctype="multipart/form-data" class="form-box">
      <label for="program_name">Program Name</label>
      <input type="text" name="program_name" id="program_name" required>

      <div class="form-buttons">
        <button type="submit" class="btn-add">Add Program</button>
        <a href="../users/programs.php" class="btn-cancel">Cancel</a>
      </div>
    </form>
  </div>
  
</section>
</body>
</html>
