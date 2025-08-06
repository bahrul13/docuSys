<nav class="sidebar">
  <header>
    <div class="image-text">
      <span class="image">
        <img src="../uploads/R.png" alt="logo" />
      </span>

      <div class="text header-text">
        <span class="name">QMSO Document Management System</span>
      </div>
    </div>
  </header>

  <div class="menu-bar">
    <div class="menu">
      <ul class="menu-links">
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
          <li class="nav-link">
            <a href="../users/dashboard.php">
              <i class='bx bx-home icon'></i>
              <span class="text nav-text">Dashboard</span>
            </a>
          </li>
        <?php endif; ?>

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
            <span class="text nav-text">Accreditation Documents</span>
          </a>
        </li>

        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
          <li class="nav-link dropdown">
            <a href="" class="dropdown-toggle">
              <i class='bx bx-dots-horizontal-rounded icon'></i>
              <span class="text nav-text">Others</span>
              <i class='bx bx-chevron-down icon dropdown-icon'></i>
            </a>
            <ul class="dropdown-menu">
              <li>
                <a href="../users/programs.php">
                  <i class="bx bx-book icon"></i>
                  <span class="text nav-text">Programs</span>
                </a>
              </li>
              <li>
                <a href="../users/department.php">
                  <i class="bx bx-building icon"></i>
                  <span class="text nav-text">Departments</span>
                </a>
              </li>
            </ul>
          </li>
        <?php endif; ?>



        <!-- <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
          <li class="nav-link">
            <a href="../users/programs.php">
              <i class='bx bx-file icon'></i>
              <span class="text nav-text">Programs</span>
            </a>
          </li>
          <li class="nav-link">
            <a href="../users/department.php">
              <i class='bx bx-file icon'></i>
              <span class="text nav-text">Departments</span>
            </a>
          </li>
        <?php endif; ?> -->
      </ul>
    </div>

    <div class="bottom-content">
      <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <li>
          <a href="../includes/logout.php" id="logoutLink">
            <i class='bx bx-log-out icon'></i>
            <span class="text nav-text">Logout</span>
          </a>
        </li>

        <!-- Modal HTML -->
        <div id="logoutModal" class="modal">
          <div class="modal-content">
            <p>Are you sure you want to logout?</p>
            <div class="modal-buttons">
              <button id="confirmLogout">Yes</button>
              <button id="cancelLogout">Cancel</button>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const logoutLink = document.getElementById("logoutLink");
  const modal = document.getElementById("logoutModal");
  const confirmBtn = document.getElementById("confirmLogout");
  const cancelBtn = document.getElementById("cancelLogout");

  if (logoutLink) {
    logoutLink.addEventListener("click", function (event) {
      event.preventDefault();
      modal.style.display = "block";
    });

    confirmBtn.addEventListener("click", function () {
      window.location.href = logoutLink.href;
    });

    cancelBtn.addEventListener("click", function () {
      modal.style.display = "none";
    });

    window.addEventListener("click", function (event) {
      if (event.target === modal) {
        modal.style.display = "none";
      }
    });
  }
});
</script>
