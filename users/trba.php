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
  <title>TRBA</title>

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

  <!-- 📄 Table Section -->
  <section class="table-section">
    <h2>List of Technical Review and Board Action Files</h2>

      <!-- 🔍 Search Bar -->
      <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search documents..." onkeyup="filterTable()" />
      </div>

      <?php if ($isAdmin): ?>
    <div class="add-button-container">
      <a href="../views/add_trba_page.php" class="btn-add">Add TRBA</a>
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
        $sql = "SELECT * FROM trba ORDER BY date_uploaded DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0):
          while ($row = $result->fetch_assoc()):
        ?>
          <tr>
            <td><?= htmlspecialchars($row['program_name']) ?></td>
            <td><?= htmlspecialchars($row['survey_type']) ?></td>
            <td><?= htmlspecialchars($row['survey_date']) ?></td>
            <td><a href="../uploads/trba/<?= htmlspecialchars($row['file_name']) ?>" target="_blank">View PDF</a></td>
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
