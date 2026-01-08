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

require "../db/db_conn.php";      // your database connection

// Check if user is admin
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Fetch updated user list
$result = $conn->query("SELECT id, fullname, dept, email, role FROM user ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Management</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/uploads/dms.png">
</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<!-- FLASH MESSAGE -->
  <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert" id="flashMessage"><?= $_SESSION['flash']; unset($_SESSION['flash']); ?></div>
    <script>
      setTimeout(() => {
        const alert = document.getElementById('flashMessage');
        if (alert) alert.remove();
      }, 3000);
    </script>
  <?php endif; ?>

<!-- DELETE FLASH MESSAGE -->
  <?php if (isset($_SESSION['delete_flash'])): ?>
    <div class="delete-alert" id="deleteFlash">
      <?= $_SESSION['delete_flash']; unset($_SESSION['delete_flash']); ?>
    </div>
    <script>
      setTimeout(() => {
        const alert = document.getElementById('deleteFlash');
        if (alert) alert.remove();
      }, 3000);
    </script>
  <?php endif; ?>

<section class="dashboard-content">

<section class="table-section">
    <h2>User Management</h2>

    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search User..." onkeyup="filterTable()" />
    </div>

    <?php if ($isAdmin): ?>
    <div class="add-button-container">
      <a href="../views/add_user_page.php" class="btn-add">Add User</a>
    </div>
    <?php endif; ?>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>User Name</th>
            <th>Department</th>
            <th>Email Address</th>
            <th>Role</th>
            <th>Date Created</th>
            <?php if ($isAdmin): ?><th>Action</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT * FROM user ORDER BY date_created DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0):
          while ($row = $result->fetch_assoc()):
        ?>
          <tr>
            <td><?= htmlspecialchars($row['fullname']) ?></td>
            <td><?= htmlspecialchars($row['dept']) ?> </td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td><?= htmlspecialchars($row['date_created']) ?></td>
            <td>
            <?php if ($isAdmin): ?>
              <button class="btn-update" onclick="openUserUpdateModal(
                <?= $row['id'] ?>,
                '<?= htmlspecialchars($row['fullname'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($row['dept']), ENT_QUOTES ?>',
                '<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($row['role'], ENT_QUOTES) ?>'
              )">Update</button>
              <button class="btn-delete" onclick="userDeleteModal(<?= $row['id'] ?>)">Delete</button>
            <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="<?= $isAdmin ? 5 : 4 ?>">No Users found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>

<script src="../js/script.js"></script>

<!-- Update Modal -->
<?php if ($isAdmin): ?>
<div id="updateUserModal" class="modal">
  <div class="modal-content"> 
    <span class="close" onclick="closeUserModal()">&times;</span>
    <h1>Update User</h1>
    <form action="../handlers/update_user.php" method="POST" enctype="multipart/form-data">
      
      <!-- Hidden field for ID -->
      <input type="hidden" name="id" id="updateUserId">

      <label for="updateUserName">Office Name</label>
      <input type="text" name="fullname" id="updateUserName">

      <label for="updateUserEmail">Email</label>
      <input type="text" name="email" id="updateUserEmail">

      <label for="updateUserPassword">Password</label>
      <input type="text" name="password" id="updateUserPassword">

      <div class="select-wrapper">
        <label for="updateUserRole">Role</label>
        <select name="role" id="updateUserRole">
          <option value="">Select Role</option>
          <option value="admin">Administrator</option>
          <option value="user">User</option>
        </select>
        <i class="bx bx-chevron-down select-icon"></i>
      </div>

      <!-- Submit Button -->
      <button type="submit">Update</button>
    </form>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteUserModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeUserDeleteModal()">&times;</span>
    <h1>Confirm Deletion</h1>
    <p>Are you sure you want to delete this User?</p>
    <form method="POST" action="../handlers/delete_user.php">
      <input type="hidden" name="id" id="deleteUserId">
      <button type="submit" class="btn-delete-confirm">Confirm</button>
      <button type="button" onclick="closeUserDeleteModal()" style="background-color: gray; margin-left: 10px;">Cancel</button>
    </form>
  </div>
</div>
<?php endif; ?>

</body>
</html>
