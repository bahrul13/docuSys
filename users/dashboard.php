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
  <title>Admin Dashboard</title>

  <!-- Boxicons CDN -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />

  <!-- CSS File -->
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
    <?php include('../includes/sidebar.php'); ?>

  <section class="dashboard-content">
    <h1>Welcome, Admin</h1>

    <div class="cards">
        <div class="card">
            <i class='bx bx-file'></i>
            <div>
                <?php
                $countQuery = "SELECT COUNT(*) AS total FROM programs";
                $countResult = $conn->query($countQuery);

                $totalPrograms = 0;
                if ($countResult && $row = $countResult->fetch_assoc()) {
                    $totalPrograms = $row['total'];
                }
                ?>
                <h3><?= $totalPrograms ?></h3>
                <p>Total number of Programs</p>
            </div>
        </div>
        <div class="card">
            <i class='bx bx-user'></i>
            <div>
                <h3>15</h3>
                <p>Total TRBA</p>
            </div>
        </div>
        <div class="card">
            <i class='bx bx-cloud-upload'></i>
            <div>
                <h3>8</h3>
                <p>Total SFR</p>
            </div>
        </div>
        <div class="card">
            <i class='bx bx-cloud-upload'></i>
            <div>
                <h3>8</h3>
                <p>Total COPC</p>
            </div>
        </div>
        <div class="card">
            <i class='bx bx-cloud-upload'></i>
            <div>
                <h3>8</h3>
                <p>Others</p>
            </div>
        </div>
        <div class="card">
            <i class='bx bx-cloud-upload'></i>
            <div>
                <h3>8</h3>
                <p>Recent Uploaded Documents</p>
            </div>
        </div>
    </div>
  </section>

  <!-- Script File -->
  <script src="../js/script.js"></script>
</body>
</html>
