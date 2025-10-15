<?php
session_start();
require '../db/db_conn.php';

// Check if user is admin
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Fetch programs from database
$programs = [];
$result = $conn->query("SELECT * FROM programs ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
    $programs[] = $row['name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SFR</title>

  <!-- Boxicons CDN -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />

  <!-- CSS File -->
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

  <!-- 📄 Table Section -->
  <section class="table-section">
    <h2>List of Summary of Findings and Recommendation Files</h2>

    <!-- 🔍 Search Bar -->
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search documents..." onkeyup="filterTable()" />
    </div>

    <?php if ($isAdmin): ?>
    <div class="add-button-container">
      <a href="../views/add_sfr_page.php" class="btn-add">Add SFR</a>
    </div>
    <?php endif; ?>
    
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Program Name</th>
            <th>Survey Type</th>
            <th>Survey Date</th>
            <th>File</th>
            <th>Date Uploaded</th>
            <?php if ($isAdmin): ?>
              <th>Action</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT * FROM sfr ORDER BY date_uploaded DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0):
          while ($row = $result->fetch_assoc()):
        ?>
          <tr>
            <td><?= htmlspecialchars($row['program_name']) ?></td>
            <td><?= htmlspecialchars($row['survey_type']) ?></td>
            <td><?= htmlspecialchars($row['survey_date']) ?></td>
            <td><a href="../uploads/sfr/<?= htmlspecialchars($row['file_name']) ?>" target="_blank">View PDF</a></td>
            <td><?= htmlspecialchars($row['date_uploaded']) ?></td>
            <?php if ($isAdmin): ?>
            <td>
              <button class="btn-update" onclick="openUpdateSfrModal(
                <?= $row['id'] ?>,
                '<?= htmlspecialchars($row['program_name'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($row['survey_type'], ENT_QUOTES) ?>',
                '<?= $row['survey_date'] ?>'
              )">Update</button>
              <button class="btn-delete" onclick="openDeleteSfrModal(<?= $row['id'] ?>)">Delete</button>
            </td>
            <?php endif; ?>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="<?= $isAdmin ? 6 : 5 ?>">No documents found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>

<script src="../js/script.js"></script>

<?php if ($isAdmin): ?>
<!-- UPDATE SFR MODAL -->
<div id="updateSfrModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeUpdateSfrModal()">&times;</span>
    <h1>Update SFR</h1>
    <form action="../handlers/update_sfr.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" id="updateSfrId">

      <label for="updateSfrProgramName">Program Name</label>
      <div class="select-wrapper">
        <select name="program_name" id="updateSfrProgramName" required>
          <option value="" disabled selected>Select Program</option>
          <?php foreach ($programs as $prog): ?>
            <option value="<?= htmlspecialchars($prog) ?>"><?= htmlspecialchars($prog) ?></option>
          <?php endforeach; ?>
        </select>
        <i class="bx bx-chevron-down select-icon"></i>
      </div>

      <div class="select-wrapper">
        <label for="updateSfrSurveyType">Type of Survey</label>
        <select name="survey_type" id="updateSfrSurveyType" required>
          <option value="">Select Type of Survey</option>
          <option value="Level 1">Level 1</option>
          <option value="Level 2">Level 2</option>
          <option value="Level 3">Level 3</option>
          <option value="Level 4">Level 4</option>
        </select>
        <i class="bx bx-chevron-down select-icon"></i>
      </div>

      <label for="updateSfrSurveyDate">Survey Date</label>
      <input type="date" name="survey_date" id="updateSfrSurveyDate" required>

      <label for="updateSfrFile">Upload New PDF (optional)</label>
      <input type="file" name="file_name" id="updateSfrFile" accept="application/pdf">

      <button type="submit">Update</button>
    </form>
  </div>
</div>

<!-- DELETE SFR MODAL -->
<div id="deleteSfrModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeDeleteSfrModal()">&times;</span>
    <h1>Delete SFR</h1>
    <p>Are you sure you want to delete this SFR record?</p>
    <form action="../handlers/delete_sfr.php" method="POST">
      <input type="hidden" name="id" id="deleteSfrId">
      <button type="submit" class="btn-delete-confirm">Confirm</button>
      <button type="button" onclick="closeDeleteSfrModal()" style="background-color: gray; margin-left: 10px;">Cancel</button>
    </form>
  </div>
</div>
<?php endif; ?>

</body>
</html>
