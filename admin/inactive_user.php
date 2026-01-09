<?php
// ==================== SESSION CHECK ====================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "../db/db_conn.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Admin only
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
if (!$isAdmin) {
    die("Access denied");
}

// Fetch inactive users only
$sql = "
    SELECT id, fullname, dept, email, role, date_created
    FROM user
    WHERE status = 'inactive'
    ORDER BY date_created DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Inactive Users</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="icon" type="image/png" href="/uploads/dms.png">
</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<!-- FLASH MESSAGE -->
<?php if (isset($_SESSION['flash'])): ?>
  <div class="alert"><?= $_SESSION['flash']; unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<section class="dashboard-content">
<section class="table-section">

<h2>Inactive Users</h2>

<div class="table-container">
<table>
  <thead>
    <tr>
      <th>Name</th>
      <th>Department</th>
      <th>Email</th>
      <th>Role</th>
      <th>Date Created</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>

  <?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['fullname']) ?></td>
        <td><?= htmlspecialchars($row['dept']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['role']) ?></td>
        <td><?= htmlspecialchars($row['date_created']) ?></td>
        <td>
          <!-- 🔁 Reactivate Button -->
          <button 
            class="btn-update"
            onclick="openReactivateModal(<?= $row['id'] ?>)">
            Reactivate
          </button>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr>
      <td colspan="6">No inactive users found.</td>
    </tr>
  <?php endif; ?>

  </tbody>
</table>
</div>

</section>
</section>

<!-- ==================== REACTIVATE CONFIRM MODAL ==================== -->
<div id="reactivateConfirmModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeReactivateModal()">&times;</span>
    <h1>Confirm Reactivation</h1>
    <p>Are you sure you want to reactivate this user?</p>

    <form method="POST" action="reactivate_user.php">
      <input type="hidden" name="id" id="reactivateUserId">
      <div class="modal-buttons">
        <button type="submit" class="btn-update">Yes, Reactivate</button>
        <button type="button" onclick="closeReactivateModal()" style="background-color: gray;">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ==================== SCRIPT ==================== -->
<script>
function openReactivateModal(userId) {
    document.getElementById('reactivateUserId').value = userId;
    document.getElementById('reactivateConfirmModal').style.display = 'block';
}

function closeReactivateModal() {
    document.getElementById('reactivateConfirmModal').style.display = 'none';
}

// Close when clicking outside modal
window.onclick = function(event) {
    const modal = document.getElementById('reactivateConfirmModal');
    if (event.target === modal) {
        modal.style.display = "none";
    }
}
</script>

</body>
</html>
