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
      <a href="../views/add_docu.php" class="btn-add">Add Document</a>
    </div>
    <?php endif; ?>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Document Name</th>
            <th>Date Uploaded</th>
            <?php if ($isAdmin): ?><th>Action</th><?php endif; ?>
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
            <td><?= htmlspecialchars($row['file_name']) ?></td>
            <?php if ($isAdmin): ?>
            <td>
              <button class="btn-update" onclick="openUpdateProgramModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['file_name'], ENT_QUOTES) ?>')">Update</button>
              <button class="btn-delete" onclick="openDeleteProgramModal(<?= $row['id'] ?>)">Delete</button>
            </td>
            <?php endif; ?>
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
<script>
function filterTable() {
  const input = document.getElementById("searchInput");
  const filter = input.value.toLowerCase();
  const table = document.querySelector("table");
  const trs = table.getElementsByTagName("tr");

  for (let i = 1; i < trs.length; i++) {
    const tds = trs[i].getElementsByTagName("td");
    let visible = false;

    for (let j = 0; j < tds.length; j++) {
      if (tds[j] && tds[j].innerText.toLowerCase().includes(filter)) {
        visible = true;
        break;
      }
    }

    trs[i].style.display = visible ? "" : "none";
  }
}
</script>

</body>
</html>
