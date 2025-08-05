<?php
session_start();
require '../db/db_conn.php';

// Restrict access to admin only
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch programs
$sql = "SELECT * FROM programs ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Programs</title>
  <link rel="stylesheet" href="../css/style.css">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    .container {
      padding: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }
    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: left;
    }
    th {
      background-color: #f4f4f4;
    }
    h2 {
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<div class="container">
  <h2>Program List</h2>
  
  <?php if ($result->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Program Name</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['programName']) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No programs found.</p>
  <?php endif; ?>
</div>

</body>
</html>
