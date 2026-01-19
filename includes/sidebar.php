<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Always make sure sidebar has its own working $conn
require_once __DIR__ . '/../db/db_conn.php';

// ✅ Pending users count (admin only)
$pendingCount = 0;

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE status = 'pending'");
    if ($stmt) {
        $stmt->execute();
        $stmt->bind_result($pendingCount);
        $stmt->fetch();
        $stmt->close();
    }
}
?>



<nav class="sidebar">
  <header>
    <div class="image-text">
      <span class="image">
        <img src="../uploads/dms.png" alt="logo" />
      </span>

      <div class="text header-text">
        <span class="name">Document Management System</span>
      </div>
    </div>
  </header>

  <div class="menu-bar">
    <div class="menu">
      <ul class="menu-links">
          <li class="nav-link">
            <a href="../users/dashboard.php">
              <i class='bx bx-home icon'></i>
              <span class="text nav-text">Dashboard</span>
            </a>
          </li>

        <li class="nav-link">
          <a href="../users/trba.php">
            <i class='bx bx-task icon'></i>
            <span class="text nav-text">TRBA</span>
          </a>
        </li>
        <li class="nav-link">
          <a href="../users/sfr.php">
            <i class='bx bx-detail icon'></i>
            <span class="text nav-text">SFR</span>
          </a>
        </li>
        <li class="nav-link">
          <a href="../users/copc.php">
            <i class='bx bx-certification icon'></i>
            <span class="text nav-text">COPC</span>
          </a>
        </li>
        <li class="nav-link">
          <a href="../users/other.php">
            <i class='bx bx-award icon'></i>
            <span class="text nav-text">Accreditation-Related Documents</span>
          </a>
        </li>

        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <li>
          <a href="../admin/archived_documents.php">
            <i class='bx bx-archive icon'></i>
            <span class="text nav-text">Archived Documents</span>
          </a>
        </li>

        <li class="nav-link">
          <a href="../users/programs.php">
            <i class='bx bx-book icon'></i>
            <span class="text nav-text">Programs</span>
          </a>
        </li>

        <li class="nav-link dropdown">
        <a href="javascript:void(0);" class="dropdown-toggle">
          <i class='bx bx-user icon'></i>

          <span class="text nav-text">
            User Management
            <?php if ($pendingCount > 0): ?>
              <span class="notif-badge"><?= $pendingCount ?></span>
            <?php endif; ?>
          </span>

          <i class='bx bx-chevron-down arrow'></i>
        </a>

          <ul class="dropdown-menu">
            <li>
              <a href="../users/user.php">
                <i class='bx bx-group icon'></i>
                <span class="text nav-text">Active Users</span>
              </a>
            </li>

            <li>
              <a href="../admin/pending_user.php">
                <i class='bx bx-time-five icon'></i>
                <span class="text nav-text">
                  Pending Users
                  <?php if ($pendingCount > 0): ?>
                    <span class="notif-badge"><?= $pendingCount ?></span>
                  <?php endif; ?>
                </span>
              </a>
            </li>

            <li>
              <a href="../admin/inactive_user.php">
                <i class="bx bx-user-x icon"></i>
                <span class="text nav-text">Inactive Users</span>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-link">
          <a href="../users/logs.php">
            <i class='bx bx-book icon'></i>
            <span class="text nav-text">Logs</span>
          </a>
        </li>
        <?php endif; ?>

        <li class="nav-link">
          <a href="../users/profile.php">
            <i class='bx bx-user icon'></i>
            <span class="text nav-text">Profile Settings</span>
          </a>
        </li>
      </ul>
    </div>

    <div class="bottom-content">
        <li>
          <a href="javascript:void(0);" id="logoutLink" onclick="openLogoutConfirm()">
            <i class='bx bx-log-out icon'></i>
            <span class="text nav-text">Logout</span>
          </a>
        </li>
    </div>
  </div>
</nav>

<!-- Logout Confirmation Modal -->
<div id="logoutConfirmModal" class="modal">
  <div class="modal-content">
    <h1>Confirm Logout</h1>
    <p>Are you sure you want to log out?</p>

    <div class="modal-buttons">
      <button class="btn-delete-confirm" onclick="confirmLogout()">Yes, Logout</button>
      <button class="btn-cancel" onclick="closeLogoutConfirm()">Cancel</button>
    </div>
  </div>
</div>

<script>
  const logoutModal = document.getElementById("logoutConfirmModal");

  function openLogoutConfirm() {
    logoutModal.style.display = "block";
  }

  function closeLogoutConfirm() {
    logoutModal.style.display = "none";
  }

  function confirmLogout() {
    window.location.href = "../includes/logout.php";
  }

  // Close modal when clicking outside
  window.addEventListener("click", function (e) {
    if (e.target === logoutModal) {
      logoutModal.style.display = "none";
    }
  });
</script>
