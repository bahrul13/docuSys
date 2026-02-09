<?php
session_start();
require '../db/db_conn.php';
require '../function/csrf.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
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
  <title>Add Document</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
    <!-- Favicon -->
  <link rel="icon" type="image/png" href="/uploads/dms.png">

</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
  <section class="table-section">
    <h2>Add Accreditation Document</h2>

    <div class="form-container">
      <form action="../handlers/add_docu.php" method="POST" enctype="multipart/form-data" class="form-box">
        <?= csrf_field(); ?>
        <label for="documentName">Document Name</label>
        <input type="text" name="documentName" id="documentID" required>

        <label for="file_name">PDF File</label>
        <input type="file" name="file_name" id="file_name" accept="application/pdf" required>

        <div class="form-buttons">
          <button type="submit" class="btn-add">Add Document</button>
          <a href="../users/other.php" class="btn-cancel">Cancel</a>
        </div>
      </form>
    </div>
  </section>
</section>
</body>
</html>
