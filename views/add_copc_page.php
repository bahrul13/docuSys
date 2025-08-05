<?php
session_start();
require '../db/db_conn.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch existing programs from the database
$programs = [];
$result = $conn->query("SELECT name FROM programs ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
    $programs[] = $row['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add COPC</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <!-- <style>
    .select-wrapper {
      position: relative;
    }
    .select-wrapper select {
      width: 100%;
      padding: 10px;
      padding-right: 30px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    .select-wrapper .select-icon {
      position: absolute;
      top: 50%;
      right: 10px;
      transform: translateY(-50%);
      pointer-events: none;
      color: #666;
    }
    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 100;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background: #fff;
      padding: 20px;
      width: 400px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }
    .modal-content h2 {
      margin-top: 0;
    }
    .modal-content input[type="text"] {
      width: 100%;
      padding: 8px;
      margin-top: 10px;
      margin-bottom: 20px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    .modal-buttons {
      text-align: right;
    }
    .btn-add-program {
      background: #007bff;
      color: white;
      padding: 6px 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .btn-cancel {
      margin-left: 10px;
    }
  </style> -->
</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
  <h1>Add Certificate of Program Compliance (COPC)</h1>

  <div class="form-container">
    <form action="../handlers/add_copc.php" method="POST" enctype="multipart/form-data" class="form-box">
      <label for="program">Program</label>
      <div class="select-wrapper">
        
        <select name="program" id="program" required>
          <option value="" disabled selected>Select Program</option>
          <?php foreach ($programs as $prog): ?>
            <option value="<?= htmlspecialchars($prog) ?>"><?= htmlspecialchars($prog) ?></option>
          <?php endforeach; ?>
        </select>
        <i class="bx bx-chevron-down select-icon"></i>
      </div>

      <label for="issuance_date">Date of Issuance</label>
      <input type="date" name="issuance_date" id="issuance_date" required>

      <label for="file_name">PDF File</label>
      <input type="file" name="file_name" id="file_name" accept="application/pdf" required>

      <div class="form-buttons">
        <button type="submit" class="btn-add">Add COPC</button>
        <a href="../users/copc.php" class="btn-cancel">Cancel</a>
      </div>
    </form>
  </div>
</section>

<!-- Modal
<div id="programModal" class="modal">
  <div class="modal-content">
    <h2>Add New Program</h2>
    <form id="addProgramForm">
      <input type="text" id="newProgram" placeholder="Enter new program name" required />
      <div class="modal-buttons">
        <button type="submit" class="btn-add-program">Save</button>
        <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
      </div>
    </form>
  </div>
</div> -->

</body>
</html>
