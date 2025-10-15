<?php
// Start session and check if admin is logged in
session_start();

// ✅ Check if user is logged in and is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // Redirect to login if not admin
    header('Location: ../login.php');
    exit();
}

include('../db/db_conn.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard</title>

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
    <h1 style="
    padding: 20px;
    color: var(--primary-color);">Welcome, Admin</h1>

    <div class="cards">
        <div class="card">
            <i class='bx bx-file'></i>
            <div>
                <?php
                $countQuery = "SELECT COUNT(*) AS total FROM trba";
                $countResult = $conn->query($countQuery);

                $totalTrba = 0;
                if ($countResult && $row = $countResult->fetch_assoc()) {
                    $totalTrba = $row['total'];
                }
                ?>
                <h3><?= $totalTrba ?></h3>
                <p>Total number of TRBA</p>
            </div>
        </div>
        <div class="card">
            <i class='bx bx-file'></i>
            <div>
                <?php
                $countQuery = "SELECT COUNT(*) AS total from sfr";
                $countResult = $conn->query($countQuery);

                $totalSfr = 0;
                if ($countResult && $row = $countResult->fetch_assoc()) {
                    $totalSfr = $row['total'];
                }
                ?>
                <h3><?=$totalSfr ?></h3>
                <p>Total number of SFR</p>
            </div>
        </div>
        <div class="card">
            <i class='bx bx-file'></i>
            <div>
                <?php
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
            <i class='bx bx-folder'></i>
            <div>
                <?php
                $countQuery = "SELECT COUNT(*) AS total FROM documents";
                $countResult = $conn->query($countQuery);

                $totalDocu = 0;
                if ($countResult && $row = $countResult->fetch_assoc()) {
                    $totalDocu = $row['total'];
                }
                ?>
                <h3><?= $totalDocu ?></h3>
                <p>Total Documents</p>
            </div>
        </div>
        <div class="card">
            <i class='bx bx-cloud-upload'></i>
            <div>
                <?php
                // Query to count total rows from all four tables
                $countQuery = "
                    SELECT 
                        (SELECT COUNT(*) FROM documents) +
                        (SELECT COUNT(*) FROM copc) +
                        (SELECT COUNT(*) FROM sfr) +
                        (SELECT COUNT(*) FROM trba) AS total
                ";

                $countResult = $conn->query($countQuery);

                $totalUpload = 0;
                if ($countResult && $row = $countResult->fetch_assoc()) {
                    $totalUpload = $row['total'];
                }
                ?>
                <h3><?= $totalUpload ?></h3>
                <p>Total Uploaded Documents</p>
            </div>
        </div>
        <div class="card">
            <i class='bx bx-book-content'></i>
            <div>
                <?php
                $countQuery = "SELECT COUNT(*) AS total FROM programs";
                $countResult = $conn->query($countQuery);

                $totalProg = 0;
                if ($countResult && $row = $countResult->fetch_assoc()) {
                    $totalProg = $row['total'];
                }
                ?>
                <h3><?= $totalProg?></h3>
                <p>Total Programs</p>
            </div>
        </div>
    </div>
  </section>

  <!-- Script File -->
  <script src="../js/script.js"></script>
</body>
</html>
