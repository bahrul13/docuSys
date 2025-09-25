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

<!-- 
  <div class="cards">
    <div class="card">
      <i class='bx bx-file'></i>
      <div>
        <?php
        // Query to count the total number of programs
        $countQuery = "SELECT COUNT(*) AS total FROM copc";
        $countResult = $conn->query($countQuery);
        $totalCopc = 0;
        if ($countResult && $row = $countResult->fetch_assoc()) {
            $totalCopc = $row['total'];
        }
        ?>
        <h3><?= $totalCopc ?></h3>
        <p>Total number of COPC</p>
      </div>
    </div>
    <div class="card">
      <i class='bx bx-file'></i>
      <div>
      <?php
        // Get current date minus 7 days
        $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));

        // SQL query to count recent uploads
        $recentQuery = "SELECT COUNT(*) AS recent_total FROM copc WHERE uploaded_at >= ?";
        $stmt = $conn->prepare($recentQuery);
        $stmt->bind_param("s", $sevenDaysAgo);
        $stmt->execute();
        $result = $stmt->get_result();
        $recentTotal = 0;
        if ($result && $row = $result->fetch_assoc()) {
            $recentTotal = $row['recent_total'];
        }
        ?>
        <h3><?= $recentTotal ?></h3>
        <p>Files Uploaded in Last 7 Days</p>
      </div>
    </div>
  </div> -->

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
