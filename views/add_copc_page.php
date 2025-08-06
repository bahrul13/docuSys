<?php
session_start();
require '../db/db_conn.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch existing programs from the database
$programs = [];
$result = $conn->query("SELECT name FROM programs ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
    $programs[] = $row['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add COPC</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />

</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
  <h1>Add Certificate of Program Compliance (COPC)</h1>

  <div class="form-container">
    <form action="../handlers/add_copc.php" method="POST" enctype="multipart/form-data" class="form-box">
      <label for="program">Program</label>
      <div class="select-wrapper">
        
        <select name="program" id="program" required>
          <option value="" disabled selected>Select Program</option>
          <?php foreach ($programs as $prog): ?>
            <option value="<?= htmlspecialchars($prog) ?>"><?= htmlspecialchars($prog) ?></option>
          <?php endforeach; ?>
        </select>
        <i class="bx bx-chevron-down select-icon"></i>
      </div>

      <label for="issuance_date">Date of Issuance</label>
      <input type="date" name="issuance_date" id="issuance_date" required>

      <label for="file_name">PDF File</label>
      <input type="file" name="file_name" id="file_name" accept="application/pdf" required>

      <div class="form-buttons">
        <button type="submit" class="btn-add">Add COPC</button>
        <a href="../users/copc.php" class="btn-cancel">Cancel</a>
      </div>
    </form>
  </div>
</section>
</body>
</html>
