<?php
session_start();
require '../db/db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../users/other.php");
    exit();
}

$id = intval($_GET['id']);

// Fetch documents record
$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$other = $result->fetch_assoc();

if (!$other) {
    $_SESSION['flash'] = "❌ Document record not found.";
    header("Location: ../users/other.php");
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
  <h1>Update Document</h1>

  <div class="form-container">
    <form action="../handlers/update_docu.php" method="POST" enctype="multipart/form-data" class="form-box">

      <input type="hidden" name="id" value="<?= $other['id'] ?>">


      <!-- Document Name -->
      <label for="updateDocuName">Document Name</label>
      <input
        type="text"
        name="document"
        id="updateDocuName"
        value="<?= htmlspecialchars($other['document']) ?>"
        required
      >

      <!-- File -->
      <label>Upload New PDF (optional)</label>
      <input type="file" name="file_name" accept="application/pdf">

      <div class="form-buttons">
        <button type="button" class="btn-add" onclick="openOtherUpdateConfirm()">Update</button>
        <button type="button" class="btn-cancel" onclick="openOtherCancelConfirm()">Cancel</button>
      </div>
    </form>
  </div>
</section>
  <div id="otherUpdateConfirmModal" class="modal">
    <div class="modal-content">
      <h1>Confirm Update</h1>
      <p>Are you sure you want to update this document?</p>
      <div class="modal-actions">
        <button class="btn-add" onclick="submitOtherUpdate()">Yes, Update</button>
        <button class="btn-cancel" onclick="closeOtherUpdateConfirm()">Cancel</button>
      </div>
    </div>
  </div>

  <div id="otherCancelConfirmModal" class="modal">
    <div class="modal-content">
      <h1>Cancel Update</h1>
      <p>Unsaved changes will be lost. Continue?</p>
      <div class="modal-actions">
        <button class="btn-delete-confirm" onclick="confirmOtherCancel()">Yes, Leave</button>
        <button class="btn-cancel" onclick="closeOtherCancelConfirm()">Stay</button>
      </div>
    </div>
  </div>

  <script>
    const otherUpdateModal = document.getElementById('otherUpdateConfirmModal');
    const otherCancelModal = document.getElementById('otherCancelConfirmModal');
    const otherForm = document.querySelector('.form-box');

    function openOtherUpdateConfirm() { otherUpdateModal.style.display = 'block'; }
    function closeOtherUpdateConfirm() { otherUpdateModal.style.display = 'none'; }
    function submitOtherUpdate() { otherForm.submit(); }

    function openOtherCancelConfirm() { otherCancelModal.style.display = 'block'; }
    function closeOtherCancelConfirm() { otherCancelModal.style.display = 'none'; }
    function confirmOtherCancel() { window.location.href = "../users/other.php"; }

    window.onclick = e => {
      if (e.target === otherUpdateModal) otherUpdateModal.style.display = 'none';
      if (e.target === otherCancelModal) otherCancelModal.style.display = 'none';
    };
  </script>


<?php $conn->close(); ?>
</body>
</html>
