<?php
session_start();
require '../db/db_conn.php';

// Check if user is admin
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>COPC</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
  <h1>Certificate Of Program Compliance Report</h1>

  <div class="cards">
    <div class="card">
      <i class='bx bx-file'></i>
      <div>
        <h3>120</h3>
        <p>Total COPC Documents</p>
      </div>
    </div>
    <div class="card">
      <i class='bx bx-user'></i>
      <div>
        <h3>15</h3>
        <p>Total Recently Uploaded Documents</p>
      </div>
    </div>
  </div>

  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="Search documents..." onkeyup="filterTable()" />
  </div>

  <section class="table-section">
    <h2>COPC List</h2>

    <?php if ($isAdmin): ?>
    <div class="add-button-container">
      <button class="btn-add" onclick="openModal()">Add COPC</button>
    </div>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
    <div id="addModal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Add New COPC</h3>
        <form action="../handlers/add_copc.php" method="POST" enctype="multipart/form-data">
          <label>Program</label>
          <input type="text" name="program" required>

          <label>Date of Issuance</label>
          <input type="date" name="issuance_date" required>

          <label>PDF File</label>
          <input type="file" name="file_name" accept="application/pdf" required>

          <button type="submit">Upload</button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Program</th>
            <th>Date of Issuance</th>
            <th>PDF File</th>
            <th>Date Uploaded</th>
            <?php if ($isAdmin): ?><th>Action</th><?php endif; ?>
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
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['program']) ?></td>
            <td><?= htmlspecialchars($row['issuance_date']) ?></td>
            <td><a href="../uploads/copc/<?= htmlspecialchars($row['file_name']) ?>" target="_blank">View PDF</a></td>
            <td><?= htmlspecialchars($row['uploaded_at']) ?></td>
            <?php if ($isAdmin): ?>
            <td>
              <button class="btn-update" onclick="openUpdateModal(
                <?= $row['id'] ?>,
                '<?= htmlspecialchars($row['program'], ENT_QUOTES) ?>',
                '<?= $row['issuance_date'] ?>'
              )">Update</button>
              <button class="btn-delete" onclick="openDeleteModal(<?= $row['id'] ?>)">Delete</button>
            </td>
            <?php endif; ?>
          </tr>
        <?php
          endwhile;
        else:
        ?>
          <tr><td colspan="<?= $isAdmin ? 6 : 5 ?>">No documents found.</td></tr>
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
    <h3>Confirm Deletion</h3>
    <p>Are you sure you want to delete this file?</p>
    <form id="deleteForm" method="GET" action="../handlers/delete_copc.php">
      <input type="hidden" name="id" id="deleteId">
      <button type="submit">Confirm</button>
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
    <h3>Update COPC</h3>
    <form action="../handlers/update_copc.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" id="updateId">
      <label>Program</label>
      <input type="text" name="program" id="updateProgram" required>

      <label>Date of Issuance</label>
      <input type="date" name="issuance_date" id="updateDate" required>

      <label>Replace PDF File (optional)</label>
      <input type="file" name="file_name" accept="application/pdf">

      <button type="submit">Update</button>
    </form>
  </div>
</div>
<?php endif; ?>

<?php
$updateMessage = '';
if (isset($_GET['updated'])) {
    if ($_GET['updated'] == 1) {
        $updateMessage = "File updated successfully!";
    } elseif ($_GET['updated'] == 0) {
        $updateMessage = "Failed to update the file.";
    }
}
?>

<div id="messageModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" onclick="closeMessageModal()">&times;</span>
    <p id="messageText"><?= htmlspecialchars($updateMessage) ?></p>
  </div>
</div>


</body>
</html>
