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
        <li class="nav-link">
          <a href="../users/programs.php">
            <i class='bx bx-book icon'></i>
            <span class="text nav-text">Programs</span>
          </a>
        </li>
        <li class="nav-link">
          <a href="../users/user.php">
            <i class='bx bx-user icon'></i>
            <span class="text nav-text">User Management</span>
          </a>
        </li>
        <li class="nav-link">
          <a href="../users/logs.php">
            <i class='bx bx-book icon'></i>
            <span class="text nav-text">Logs</span>
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </div>

    <div class="bottom-content">
        <li>
          <a href="../includes/logout.php" id="logoutLink">
            <i class='bx bx-log-out icon'></i>
            <span class="text nav-text">Logout</span>
          </a>
        </li>
    </div>
  </div>
</nav>

