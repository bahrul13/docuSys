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

<section class="dashboard-content">

  <!-- 🔷 Dashboard Cards
  <div class="cards">
    <div class="card">
        <i class='bx bx-file'></i>
        <div>
            <h3>120</h3>
            <p>Total Documents</p>
        </div>
    </div>
    <div class="card">
        <i class='bx bx-user'></i>
        <div>
            <h3>15</h3>
            <p>Total Recently Uploaded Documents</p>
        </div>
    </div>
  </div> -->

  <!-- 📄 Table Section -->
  <section class="table-section">
    <h2>List of Accreditation Document Files</h2>

    <!-- 🔍 Search Bar -->
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search documents..." onkeyup="filterTable()" />
    </div>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Document Name</th>
            <th>Uploaded By</th>
            <th>Date Uploaded</th>
            <?php if ($isAdmin): ?>
              <th>Action</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>Project Report 2024</td>
            <td>Admin</td>
            <td>2025-07-28</td>
            <?php if ($isAdmin): ?>
              <td>
                <a href="update.php?id=1" class="btn-update">Update</a>
                <a href="delete.php?id=1" class="btn-delete" onclick="return confirm('Are you sure you want to delete this file?');">Delete</a>
              </td>
            <?php endif; ?>
          </tr>
          <tr>
            <td>2</td>
            <td>Policy Draft</td>
            <td>Editor</td>
            <td>2025-07-25</td>
            <?php if ($isAdmin): ?>
              <td>
                <a href="update.php?id=2" class="btn-update">Update</a>
                <a href="delete.php?id=2" class="btn-delete" onclick="return confirm('Are you sure you want to delete this file?');">Delete</a>
              </td>
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
