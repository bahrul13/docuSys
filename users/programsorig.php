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
  <title>Programs</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <style>
    .alert {
      position: fixed;
      top: 20px;
      right: 20px;
      background-color: #38a169;
      color: white;
      padding: 12px 20px;
      border-radius: 4px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      z-index: 1000;
      animation: fadeIn 0.5s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
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

<section class="dashboard-content">
  <h1>Programs</h1>

  <div class="cards">
    <div class="card">
      <i class='bx bx-file'></i>
      <div>
        <?php
        $countQuery = "SELECT COUNT(*) AS total FROM programs";
        $countResult = $conn->query($countQuery);
        $totalPrograms = $countResult && $row = $countResult->fetch_assoc() ? $row['total'] : 0;
        ?>
        <h3><?= $totalPrograms ?></h3>
        <p>Total number of Programs</p>
      </div>
    </div>
  </div>

  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="Search programs..." onkeyup="filterTable()" />
  </div>

  <section class="table-section">
    <h2>List of Programs</h2>

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
    <h2>Update Program</h2>
    <form action="../handlers/update_prog.php" method="POST">
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
    <h2>Delete Program</h2>
    <p>Are you sure you want to delete this program?</p>
    <form action="../handlers/delete_prog.php" method="POST">
      <input type="hidden" name="id" id="deleteProgramId">
      <button type="submit" class="btn-delete-confirm">Confirm</button>
      <button type="button" onclick="closeDeleteProgramModal()" style="background-color: gray; margin-left: 10px;">Cancel</button>
    </form>
  </div>
</div>
<?php endif; ?>

<script src="../js/script.js"></script>
</body>
</html>
