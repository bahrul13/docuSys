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

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';

// Check if user is admin
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Programs</title>
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
    <h2>List of Programs</h2>

    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search programs..." onkeyup="filterTable()" />
    </div>

    <?php if ($isAdmin): ?>
    <div class="add-button-container">
      <a href="../views/add_prog_page.php" class="btn-add">Add Program</a>
    </div>
    <?php endif; ?>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Program Name</th>
            <?php if ($isAdmin): ?><th>Action</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT * FROM programs ORDER BY name DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0):
          while ($row = $result->fetch_assoc()):
        ?>
          <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <?php if ($isAdmin): ?>
            <td>
              <button class="btn-update" onclick="openUpdateProgramModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>')">Update</button>
              <button class="btn-delete" onclick="openDeleteProgramModal(<?= $row['id'] ?>)">Delete</button>
            </td>
            <?php endif; ?>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="<?= $isAdmin ? 2 : 1 ?>">No programs found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>

<?php if ($isAdmin): ?>
<!-- UPDATE MODAL -->
<div id="updateProgramModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeUpdateProgramModal()">&times;</span>
    <h1>Update Program</h1>
    <form action="../handlers/update_prog.php" method="POST">
      <?= csrf_field(); ?>
      <input type="hidden" name="id" id="updateProgramId">
      <label for="updateProgramName">Program Name</label>
      <input type="text" name="name" id="updateProgramName" required>
      <button type="submit">Update</button>
    </form>

  </div>
</div>

<!-- DELETE MODAL -->
<div id="deleteProgramModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeDeleteProgramModal()">&times;</span>
    <h1>Delete Program</h1>
    <p>Are you sure you want to delete this program?</p>
    <form action="../handlers/delete_prog.php" method="POST">
      <?= csrf_field(); ?>
      <input type="hidden" name="id" id="deleteProgramId">
      <button type="submit" class="btn-delete-confirm">Confirm</button>
      <button type="button" onclick="closeDeleteProgramModal()" style="background-color: gray; margin-left: 10px;">Cancel</button>
    </form>
  </div>
</div>
<?php endif; ?>
<style>

</style>
<script src="../js/script.js"></script>
</body>
</html>
