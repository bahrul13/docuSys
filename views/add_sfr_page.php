<?php
session_start();
require '../db/db_conn.php';

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
  <title>Add COPC</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
    <!-- Favicon -->
  <link rel="icon" type="image/png" href="/uploads/dms.png">

</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
  <section class="table-section">
    <h2>Add Summary of Findings and Recommendations (SFR)</h2>

    <div class="form-container">
      <form action="../handlers/add_sfr.php" method="POST" enctype="multipart/form-data" class="form-box">
        <label for="program">Program Name</label>
        <div class="select-wrapper">
          <select name="program_name" id="program" required>
            <option value="" disabled selected>Select Program Name</option>
            <?php foreach ($programs as $prog): ?>
              <option value="<?= htmlspecialchars($prog) ?>"><?= htmlspecialchars($prog) ?></option>
            <?php endforeach; ?>
          </select>
          <i class="bx bx-chevron-down select-icon"></i>
        </div>

        <div class="select-wrapper">
          <label for="survey_type">Type of Survey</label>
          <select name="survey_type" id="survey_type" required>
            <option value="">Select Type of Survey</option>
            <option value="PSV">PSV</option>
            <option value="Level 1">Level 1</option>
            <option value="Level 2">Level 2</option>
            <option value="Revisit Level 2">Revisit Level 2</option>
            <option value="Level 3">Level 3</option>
            <option value="Revisit Level 3">Revisit Level 3</option>
            <option value="Level 4">Level 4</option>
            <option value="Revisit Level 4">Revisit Level 4</option>
          </select>
          <i class="bx bx-chevron-down select-icon"></i>
        </div>

        <label for="survey_date">Date of Survey</label>
        <input type="date" name="survey_date" id="survey_date" required>

        <label for="file_name">PDF File</label>
        <input type="file" name="file_name" id="file_name" accept="application/pdf" required>

        <div class="form-buttons">
          <button type="submit" class="btn-add">Add SFR</button>
          <a href="../users/sfr.php" class="btn-cancel">Cancel</a>
        </div>
      </form>
    </div>
  </section>
</section>
</body>
</html>
