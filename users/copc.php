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
require_once __DIR__ . '/../function/log_handler.php';

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
        $sql = "SELECT * FROM copc WHERE is_archived = 0 ORDER BY uploaded_at DESC";
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
            <button
                type="button"
                class="btn-delete"
                onclick="openArchiveModal(<?= $row['id'] ?>, 'copc')">
                Archive
            </button>
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

<?php if ($isAdmin): ?>

<div id="archiveModal" class="modal">
  <div class="modal-content">
    <h1>Archive Document</h1>
    <p>Are you sure you want to archive this document?</p>

    <form method="POST" action="../handlers/archive.php">
      <?= csrf_field(); ?>
      <input type="hidden" name="id" id="archiveId">
      <input type="hidden" name="table" id="archiveTable">
      <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">

      <div class="modal-actions">
        <button type="submit" class="btn-delete-confirm">
          Yes, Archive
        </button>
        <button type="button" class="btn-cancel" onclick="closeArchiveModal()">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<?php endif; ?>
<script>
  const archiveModal = document.getElementById('archiveModal');
  const archiveIdInput = document.getElementById('archiveId');
  const archiveTableInput = document.getElementById('archiveTable');

  function openArchiveModal(id, table) {
    archiveIdInput.value = id;
    archiveTableInput.value = table;
    archiveModal.style.display = 'block';
  }

  function closeArchiveModal() {
    archiveModal.style.display = 'none';
  }

  window.onclick = function (e) {
    if (e.target === archiveModal) {
      archiveModal.style.display = 'none';
    }
  };
</script>
</body>
</html>
