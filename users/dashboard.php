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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
        <div class="card">
            <h3>Most Viewed Documents</h3>
            <canvas id="mostViewedPieChart"></canvas>
        </div>

        <?php
        // Fetch top 5 most viewed documents from transaction logs
        $query = "
            SELECT d.document, COUNT(t.id) AS views
            FROM transaction_logs t
            JOIN documents d ON d.id = t.record_id
            WHERE t.action = 'View Document'
            AND t.documents = 'documents'
            GROUP BY t.record_id
            ORDER BY views DESC
            LIMIT 5
        ";

        $result = $conn->query($query);

        $docNames = [];
        $docViews = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $docNames[] = $row['document'];
                $docViews[] = (int)$row['views'];
            }
        } else {
            $docNames = ["No views"];
            $docViews = [1];
        }
        ?>

        <div class="card">
            <h3>TRBA, SFR, And COPC</h3>
            <div>
                <canvas id="documentsDonutChart" width="200" height="200"></canvas>
            </div>
        </div>

        <?php
        // Fetch counts from database
        $totalTrba = 0;
        $totalSfr = 0;
        $totalCopc = 0;

        // TRBA
        $countQuery = "SELECT COUNT(*) AS total FROM trba";
        $countResult = $conn->query($countQuery);
        if ($countResult && $row = $countResult->fetch_assoc()) {
            $totalTrba = $row['total'];
        }

        // SFR
        $countQuery = "SELECT COUNT(*) AS total FROM sfr";
        $countResult = $conn->query($countQuery);
        if ($countResult && $row = $countResult->fetch_assoc()) {
            $totalSfr = $row['total'];
        }

        // COPC
        $countQuery = "SELECT COUNT(*) AS total FROM copc";
        $countResult = $conn->query($countQuery);
        if ($countResult && $row = $countResult->fetch_assoc()) {
            $totalCopc = $row['total'];
        }
        ?>

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

    const ctx = document.getElementById('mostViewedPieChart').getContext('2d');

    const mostViewedPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($docNames); ?>,
            datasets: [{
                data: <?php echo json_encode($docViews); ?>,
                backgroundColor: [
                    'rgba(30, 90, 148, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)'
                ],
                borderColor: [
                    'rgba(30, 90, 148, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw + ' views';
                        }
                    }
                }
            }
        }
    });

    const ctx2 = document.getElementById('documentsDonutChart').getContext('2d');
    const documentsDonutChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['TRBA', 'SFR', 'COPC'],
            datasets: [{
                label: 'Total Documents',
                data: [<?= $totalTrba ?>, <?= $totalSfr ?>, <?= $totalCopc ?>],
                backgroundColor: [
                    '#1E5A94', // TRBA
                    '#28a745', // SFR
                    '#ffc107'  // COPC
                ],
                borderColor: '#fff',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#000',
                        font: { size: 14 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            return `${label}: ${value}`;
                        }
                    }
                }
            },
            cutout: '60%', // donut hole size
        }
    });
  </script>
</body>
</html>
