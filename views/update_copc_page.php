<?php
session_start();
require '../db/db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../users/copc.php");
    exit();
}

$id = intval($_GET['id']);

// Fetch COPC record
$stmt = $conn->prepare("SELECT * FROM copc WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$copc = $result->fetch_assoc();

if (!$copc) {
    $_SESSION['flash'] = "❌ COPC record not found.";
    header("Location: ../users/copc.php");
    exit();
}

// Fetch programs
$programs = [];
$res = $conn->query("SELECT name FROM programs ORDER BY name ASC");
while ($row = $res->fetch_assoc()) {
    $programs[] = $row['name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update COPC</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
<link rel="stylesheet" href="../css/style.css">
<link rel="icon" type="image/png" href="/uploads/dms.png">
</head>

<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
  <h1>Update COPC</h1>

  <div class="form-container">
    <form action="../handlers/update_copc.php" method="POST" enctype="multipart/form-data" class="form-box">

      <input type="hidden" name="id" value="<?= $copc['id'] ?>">

      <!-- Program -->
      <label for="program">Program Name</label>
            <div class="select-wrapper">
                <select name="program" id="program" required>
                <option disabled>Select Program</option>
                <?php foreach ($programs as $prog): ?>
                    <option value="<?= htmlspecialchars($prog) ?>"
                    <?= $prog === $copc['program'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($prog) ?>
                    </option>
                <?php endforeach; ?>
                </select>
                <i class="bx bx-chevron-down select-icon"></i>
            </div>

      <!-- Issuance Date -->
      <label for="issuance_date">Survey Date</label>
        <input
          type="date"
          name="issuance_date"
          id="issuance_date"
          value="<?= htmlspecialchars($copc['issuance_date']) ?>"
          required
        >

      <!-- File -->
      <label>Upload New PDF (optional)</label>
      <input type="file" name="file_name" accept="application/pdf">

      <div class="form-buttons">
        <button type="button" class="btn-add" onclick="openCopcUpdateConfirm()">Update</button>
        <button type="button" class="btn-cancel" onclick="openCopcCancelConfirm()">Cancel</button>
      </div>

    </form>
  </div>
</section>
  <!-- ✅ COPC Update Confirmation Modal -->
  <div id="copcUpdateConfirmModal" class="modal">
    <div class="modal-content">
      <h1>Confirm Update</h1>
      <p>Are you sure you want to update this COPC record?</p>

      <div class="modal-actions">
        <button class="btn-add" onclick="submitCopcUpdate()">Yes, Update</button>
        <button class="btn-cancel" onclick="closeCopcUpdateConfirm()">Cancel</button>
      </div>
    </div>
  </div>

  <!-- ❌ COPC Cancel Confirmation Modal -->
  <div id="copcCancelConfirmModal" class="modal">
    <div class="modal-content">
      <h1>Cancel Update</h1>
      <p>Any unsaved changes will be lost. Continue?</p>

      <div class="modal-actions">
        <button class="btn-delete-confirm" onclick="confirmCopcCancel()">Yes, Leave</button>
        <button class="btn-cancel" onclick="closeCopcCancelConfirm()">Stay</button>
      </div>
    </div>
  </div>

  <script>
    const copcUpdateModal = document.getElementById('copcUpdateConfirmModal');
    const copcCancelModal = document.getElementById('copcCancelConfirmModal');
    const copcForm = document.querySelector('.form-box');

    function openCopcUpdateConfirm() {
      copcUpdateModal.style.display = 'block';
    }

    function closeCopcUpdateConfirm() {
      copcUpdateModal.style.display = 'none';
    }

    function submitCopcUpdate() {
      copcForm.submit();
    }

    function openCopcCancelConfirm() {
      copcCancelModal.style.display = 'block';
    }

    function closeCopcCancelConfirm() {
      copcCancelModal.style.display = 'none';
    }

    function confirmCopcCancel() {
      window.location.href = "../users/copc.php";
    }

    // Close modal when clicking outside
    window.onclick = function (e) {
      if (e.target === copcUpdateModal) copcUpdateModal.style.display = 'none';
      if (e.target === copcCancelModal) copcCancelModal.style.display = 'none';
    };
  </script>
  
<?php $conn->close(); ?>
</body>
</html>
