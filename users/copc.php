<?php

// ==================== SESSION CHECK & CACHE PREVENTION ====================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

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
  <title>COPC</title>
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
    <h2>List of Certificate of Program Compliance Files</h2>

    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search documents..." onkeyup="filterTable()" />
    </div>

    <?php if ($isAdmin): ?>
    <div class="add-button-container">
      <a href="../views/add_copc_page.php" class="btn-add">Add COPC</a>
    </div>
    <?php endif; ?>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Program Name</th>
            <th>Date of Issuance</th>
            <th>Date Uploaded</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT * FROM copc ORDER BY uploaded_at DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0):
          while ($row = $result->fetch_assoc()):
        ?>
          <tr>
            <td><?= htmlspecialchars($row['program']) ?></td>
            <td><?= htmlspecialchars($row['issuance_date']) ?></td>
            <td><?= htmlspecialchars($row['uploaded_at']) ?></td>
            <td>
            <button type="button" class="btn-view" onclick="window.location.href='../views/view_copc_page.php?id=<?= $row['id'] ?>'">View File</button>
            <?php if ($isAdmin): ?>
            <button
              type="button"
              class="btn-update"
              onclick="window.location.href='../views/update_copc_page.php?id=<?= $row['id'] ?>'">
              Update
            </button>
            <button class="btn-delete" onclick="openDeleteModal(<?= $row['id'] ?>)">Delete</button>
            <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="<?= $isAdmin ? 5 : 4 ?>">No documents found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>

<script src="../js/script.js"></script>

<!-- Delete Confirmation Modal -->
<?php if ($isAdmin): ?>
<div id="deleteModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeDeleteModal()">&times;</span>
    <h1>Confirm Deletion</h1>
    <p>Are you sure you want to delete this COPC?</p>
    <form id="deleteForm" method="POST" action="../handlers/delete_copc.php">
      <input type="hidden" name="id" id="deleteId">
      <button type="submit" class="btn-delete-confirm">Confirm</button>
      <button type="button" onclick="closeDeleteModal()" style="background-color: gray; margin-left: 10px;">Cancel</button>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Update Modal -->
<?php if ($isAdmin): ?>
<div id="updateModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeUpdateModal()">&times;</span>
    <h1>Update COPC</h1>
    <form action="../handlers/update_copc.php" method="POST" enctype="multipart/form-data">
      
      <!-- Hidden field for ID -->
      <input type="hidden" name="id" id="updateId">

      <!-- Program Dropdown -->
      <label for="updateProgram">Program Name</label>
      <div class="select-wrapper">
        <select name="program" id="updateProgram" required>
          <option value="" disabled selected>Select Program Name</option>
          <?php foreach ($programs as $prog): ?>
            <option value="<?= htmlspecialchars($prog) ?>"><?= htmlspecialchars($prog) ?></option>
          <?php endforeach; ?>
        </select>
        <i class="bx bx-chevron-down select-icon"></i>
      </div>

      <!-- Date Picker -->
      <label for="updateDate">Date of Issuance</label>
      <input type="date" name="issuance_date" id="updateDate" required>

      <!-- File Upload -->
      <label for="updateFile">Replace PDF File (optional)</label>
      <input type="file" name="file_name" id="updateFile" accept="application/pdf">

      <!-- Submit Button -->
      <button type="submit">Update</button>
    </form>
  </div>
</div>
<?php endif; ?>


</body>
</html>
