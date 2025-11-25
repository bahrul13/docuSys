<?php
// Start session
session_start();
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

    <?php
      // Default display name
      $displayName = "User";

      if (isset($_SESSION['user_id'])) {
          $userId = $_SESSION['user_id'];

          // Fetch fullname and role from database
          $query = "SELECT fullname, role FROM user WHERE id = ?";
          $stmt = $conn->prepare($query);
          $stmt->bind_param("i", $userId);
          $stmt->execute();
          $result = $stmt->get_result();

          if ($result && $row = $result->fetch_assoc()) {
              if ($row['role'] === 'admin') {
                  $displayName = "Administrator";
              } else {
                  $displayName = $row['fullname'];
              }
          }
          $stmt->close();
      }
    ?>

    <h1 style="padding: 20px; color: var(--primary-color);">
        Welcome, <?= $displayName ?>
    </h1>

    <div class="cards">
        <div class="card" data-href="trba.php">
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

        <div class="card" data-href="sfr.php">
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

        <div class="card" data-href="copc.php">
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

        <div class="card" data-href="other.php">
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

        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <div class="card">
            <i class='bx bx-cloud-upload'></i>
            <div>
                <?php
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

        <div class="card" data-href="programs.php">
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
        <?php endif; ?>

    </div>
  </section>

  <script src="../js/script.js"></script>
  <script>
    document.querySelectorAll('.card').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('click', () => {
            const url = card.getAttribute('data-href');
            if (url) {
                window.location.href = url;
            }
        });
    });
  </script>
</body>
</html>
