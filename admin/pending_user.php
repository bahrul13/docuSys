<?php
session_start();
require "../db/db_conn.php";
require_once __DIR__ . '/../function/csrf.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Access denied");
}

$result = $conn->query("
    SELECT id, fullname,dept, email, role, date_created
    FROM user
    WHERE status = 'pending'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pending Users</title>

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

  <!-- 📄 Table Section -->
  <section class="table-section">
    <h2>Pending User Registrations</h2>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Department</th>
            <th>Email</th>
            <th>Role</th>
            <th>Registered At</th>
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
                <button class="btn-approve"
                  onclick="openUserConfirm(
                    'approve',
                    '../admin/approved_user.php',
                    <?= (int)$row['id'] ?>
                  )">
                  Approve
                </button>
                <button class="btn-reject"
                  onclick="openUserConfirm(
                    'reject',
                    '../admin/reject_user.php',
                    <?= (int)$row['id'] ?>
                  )">
                  Reject
                </button>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="5">No pending user registrations found.</td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>

<!-- Approve / Reject Confirmation Modal -->
<div id="userConfirmModal" class="user-confirm-modal" style="display:none;">
  <div class="user-confirm-content">
    <h3 id="userConfirmTitle">Confirm Action</h3>
    <p id="userConfirmMessage"></p>

    <form id="userConfirmForm" method="POST" action="">
    <?= csrf_field(); ?>
    <input type="hidden" name="id" id="userConfirmId" value="">

    <div style="margin-top: 20px;">
      <button id="userConfirmBtn" class="btn-approve">Confirm</button>
      <button onclick="closeUserConfirm()" class="btn-reject">Cancel</button>
    </div>
  </div>
</div>


<script>
function openUserConfirm(type, actionUrl, userId) {
  const title = document.getElementById('userConfirmTitle');
  const message = document.getElementById('userConfirmMessage');
  const confirmBtn = document.getElementById('userConfirmBtn');
  const form = document.getElementById('userConfirmForm');
  const idInput = document.getElementById('userConfirmId');

  // set form action + user id
  form.action = actionUrl;
  idInput.value = userId;

  if (type === 'approve') {
    title.textContent = 'Approve User';
    message.textContent = 'Are you sure you want to approve this user?';
    confirmBtn.className = 'btn-approve';
  } else {
    title.textContent = 'Reject User';
    message.textContent = 'Are you sure you want to reject this user? This action cannot be undone.';
    confirmBtn.className = 'btn-reject';
  }

  document.getElementById('userConfirmModal').style.display = 'flex';
}

function closeUserConfirm() {
  document.getElementById('userConfirmModal').style.display = 'none';
}
</script>


</body>

