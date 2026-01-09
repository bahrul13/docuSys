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

require "../db/db_conn.php";

// Check if user is admin
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

$query = "
    SELECT t.*, u.fullname 
    FROM transaction_logs t
    LEFT JOIN user u ON u.id = t.user_id
    ORDER BY t.log_time DESC
";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>System Transaction Logs</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="icon" type="image/png" href="/uploads/dms.png">
  <style>
      /* Optional: styles for print */
      @media print {
          body * { visibility: hidden; }
          #printableTable, #printableTable * { visibility: visible; }
          #printableTable { position: absolute; top: 0; left: 0; width: 100%; }
      }
  </style>
</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">

<!-- Print Heading (only appears when printing) -->
<h1 id="printHeading">System Transaction Logs</h1>

  <section class="table-section">
    <h2>System Transactions Logs</h2>

    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search programs..." onkeyup="filterTable()" />
    </div>

    <!-- Print Table Only Button -->
    <button id="printTableBtn" onclick="printTable()">
        <i class='bx bx-printer'></i> Print Table
    </button>

    <div class="table-container">
      <table id="printableTable">
        <thead>
          <tr>
            <th>Date & Time</th>
            <th>Full Name</th>
            <th>Module</th>
            <th>Action</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?= $row['log_time']; ?></td>
            <td><?= $row['fullname']; ?></td>
            <td><?= strtoupper($row['documents']); ?></td>
            <td><?= ucfirst($row['action']); ?></td>
            <td><?= $row['description']; ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>

  </section>
</section>

<script src="../js/script.js"></script>

</body>
</html>
