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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Accreditation Documents</title>

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
    <h2>List of Accreditation Document Files</h2>

    <!-- 🔍 Search Bar -->
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search documents..." onkeyup="filterTable()" />
    </div>

    <?php if ($isAdmin): ?>
    <div class="add-button-container">
      <a href="../views/add_docu_page.php" class="btn-add">Add Document</a>
    </div>
    <?php endif; ?>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Document Name</th>
            <th>Date Uploaded</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = "SELECT * FROM documents ORDER BY file_name DESC";
          $result = $conn->query($sql);

          if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
          ?>
          <tr>
            <td><?= htmlspecialchars($row['document']) ?></td>
            <td><?= htmlspecialchars($row['uploaded_at']) ?></td>
            <td>
            <button type="button" class="btn-view" onclick="window.location.href='../views/view_docu_page.php?id=<?= $row['id'] ?>'">View File</button>
            <?php if ($isAdmin): ?>
              <button class="btn-update" onclick="openUpdateDocuModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['document'], ENT_QUOTES) ?>')">Update</button>
              <button class="btn-delete" onclick="openDeleteDocuModal(<?= $row['id'] ?>)">Delete</button>
            <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; else: ?>
          <tr><td colspan="<?= $isAdmin ? 4 : 4 ?>">No documents found.</td></tr>
          <?php endif; ?>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</section>

<script src="../js/script.js"></script>

<!-- Delete Confirmation Modal -->
<?php if ($isAdmin): ?>
<div id="deleteDocuModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeDeleteDocuModal()">&times;</span>
    <h1>Confirm Deletion</h1>
    <p>Are you sure you want to delete this Document?</p>
    <form method="POST" action="../handlers/delete_docu.php">
      <input type="hidden" name="id" id="deleteDocuId">
      <button type="submit" class="btn-delete-confirm">Confirm</button>
      <button type="button" onclick="closeDeleteDocuModal()" style="background-color: gray; margin-left: 10px;">Cancel</button>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Update Modal -->
<?php if ($isAdmin): ?>
<div id="updateDocuModal" class="modal">
  <div class="modal-content"> 
    <span class="close" onclick="closeUpdateDocuModal()">&times;</span>
    <h1>Update Document</h1>
    <form action="../handlers/update_docu.php" method="POST" enctype="multipart/form-data">
      
      <!-- Hidden field for ID -->
      <input type="hidden" name="id" id="updateDocuId">

      <label for="updateDocuName">Document Name</label>
      <input type="text" name="document" id="updateDocuName">

      <!-- File Upload -->
      <label for="updateFile">Replace PDF File (optional)</label>
      <input type="file" name="file_name" id="updateDocuFile" accept="application/pdf">

      <!-- Submit Button -->
      <button type="submit">Update</button>
    </form>
  </div>
</div>
<?php endif; ?>

</body>
</html>
