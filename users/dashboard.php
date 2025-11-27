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
                <p>Total number of Accreditation-Related Documents</p>
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

        <div class="card" data-href="logs.php">
            <i class='bx bx-show'></i>
            <div>
                <?php
                // Query: Most viewed document
                $query = "
                    SELECT d.document, COUNT(t.id) AS views
                    FROM transaction_logs t
                    JOIN documents d ON d.id = t.record_id
                    WHERE t.action = 'View Document'
                    AND t.documents = 'documents'
                    GROUP BY t.record_id
                    ORDER BY views DESC
                    LIMIT 1
                ";

                $result = $conn->query($query);

                $topDocName = "No views yet";
                $topDocViews = 0;

                if ($result && $row = $result->fetch_assoc()) {
                    $topDocName = $row['document'];
                    $topDocViews = $row['views'];
                }
                ?>
                <h3><?= htmlspecialchars($topDocViews) ?></h3>
                <p>Most Viewed: <?= htmlspecialchars($topDocName) ?></p>
            </div>
        </div>

        <div class="card" data-href="user.php">
            <i class='bx bx-user'></i>
            <div>
                <?php
                // Query: Count all users
                $userQuery = "SELECT COUNT(*) AS total_users FROM user";
                $userResult = $conn->query($userQuery);

                $totalUsers = 0;
                if ($userResult && $row = $userResult->fetch_assoc()) {
                    $totalUsers = $row['total_users'];
                }
                ?>
                <h3><?= $totalUsers ?></h3>
                <p>Total Users</p>
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
