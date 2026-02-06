<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require "../db/db_conn.php";
require_once __DIR__ . '/../function/csrf.php';

// login check
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// admin only
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
if (!$isAdmin) die("Access denied");

// ✅ Combine all archived docs into one list (FIXED)
$sql = "
  SELECT 'copc' AS module,
         id,
         program AS title,
         issuance_date AS doc_date,
         uploaded_at AS uploaded_at,
         file_name
  FROM copc
  WHERE is_archived = 1

  UNION ALL

  SELECT 'trba' AS module,
         id,
         program_name AS title,
         survey_date AS doc_date,
         uploaded_at AS uploaded_at,
         file_name
  FROM trba
  WHERE is_archived = 1

  UNION ALL

  SELECT 'sfr' AS module,
         id,
         program_name AS title,
         survey_date AS doc_date,
         uploaded_at AS uploaded_at,
         file_name
  FROM sfr
  WHERE is_archived = 1

  UNION ALL

  SELECT 'accreditation' AS module,
         id,
         document AS title,
         NULL AS doc_date,
         uploaded_at AS uploaded_at,
         file_name
  FROM documents
  WHERE is_archived = 1

  ORDER BY uploaded_at DESC
";
$result = $conn->query($sql);

function viewLink($module, $id) {
  $map = [
    'copc' => "../views/view_copc_page.php?id=",
    'trba' => "../views/view_trba_page.php?id=",
    'sfr'  => "../views/view_sfr_page.php?id=",
    'other'=> "../views/view_docu_page.php?id="
  ];
  return isset($map[$module]) ? ($map[$module] . $id) : "#";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Archived Documents</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="icon" type="image/png" href="/uploads/dms.png">
</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<?php if (isset($_SESSION['flash'])): ?>
  <div class="alert" id="flashMessage"><?= $_SESSION['flash']; unset($_SESSION['flash']); ?></div>
  <script>
    setTimeout(() => {
      const a = document.getElementById('flashMessage');
      if (a) a.remove();
    }, 3000);
  </script>
<?php endif; ?>

<section class="dashboard-content">
  <section class="table-section">
    <h2>Archived Documents</h2>

    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search archived..." onkeyup="filterTable()" />
    </div>

    <div class="table-container">
      <table id="archiveTable">
        <thead>
          <tr>
            <th>Title</th>
            <th>File Type</th>
            <th>Date (Issuance/Survey)</th>
            <th>Uploaded At</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['title'] ?? '') ?></td>
              <td><?= htmlspecialchars(strtoupper($row['module'])) ?></td>
              <td>
                <?= ($row['module'] === 'accreditation')
                    ? 'No date'
                    : htmlspecialchars($row['doc_date'] ?? '-') ?>
              </td>
              <td><?= htmlspecialchars($row['uploaded_at'] ?? '-') ?></td>
              <td>
                <button type="button" class="btn-view"
                  onclick="window.location.href='<?= viewLink($row['module'], (int)$row['id']) ?>'">
                  View
                </button>

                <button type="button" class="btn-update"
                  onclick="openRestoreConfirm('<?= htmlspecialchars($row['module'], ENT_QUOTES) ?>', <?= (int)$row['id'] ?>)">
                  Restore
                </button>

                <!-- ✅ ADD THIS DELETE BUTTON -->
                <button type="button" class="btn-delete"
                  onclick="openDeleteConfirm('<?= htmlspecialchars($row['module'], ENT_QUOTES) ?>', <?= (int)$row['id'] ?>)">
                  Delete
                </button>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5">No archived documents found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>

<!-- ✅ Unique Restore Confirmation Modal -->
<div id="archiveRestoreConfirmModal" class="modal">
  <div class="modal-content">
    <h1>Confirm Restore</h1>
    <p>Restore this archived document?</p>

    <form id="restoreForm" method="POST" action="../handlers/restore_archive.php">
      <?= csrf_field(); ?>
      <input type="hidden" name="module" id="restoreModule">
      <input type="hidden" name="id" id="restoreId">

      <div class="modal-buttons">
        <button type="submit" class="btn-add">Yes, Restore</button>
        <button type="button" class="btn-cancel" onclick="closeRestoreConfirm()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- ❌ Permanent Delete Confirmation Modal -->
<div id="archiveDeleteConfirmModal" class="modal">
  <div class="modal-content">
    <h1>Confirm Delete</h1>
    <p style="color:red;">
      ⚠️ This action is permanent. The file will be deleted and cannot be recovered.
    </p>

    <form id="deleteForm" method="POST" action="../handlers/delete_archive.php">
      <?= csrf_field(); ?>
      <input type="hidden" name="module" id="deleteModule">
      <input type="hidden" name="id" id="deleteId">

      <div class="modal-buttons">
        <button type="submit" class="btn-delete">Yes, Delete</button>
        <button type="button" class="btn-cancel" onclick="closeDeleteConfirm()">Cancel</button>
      </div>
    </form>
  </div>
</div>


<script>

  // Delete confirm modal
  const deleteModal = document.getElementById("archiveDeleteConfirmModal");

  function openDeleteConfirm(module, id) {
    document.getElementById("deleteModule").value = module;
    document.getElementById("deleteId").value = id;
    deleteModal.style.display = "block";
  }

  function closeDeleteConfirm() {
    deleteModal.style.display = "none";
  }

  window.addEventListener("click", function(e) {
    if (e.target === deleteModal) deleteModal.style.display = "none";
  });
  
  // Search filter
  function filterTable() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const rows = document.querySelectorAll("#archiveTable tbody tr");
    rows.forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(input) ? "" : "none";
    });
  }

  // Restore confirm modal
  const restoreModal = document.getElementById("archiveRestoreConfirmModal");
  function openRestoreConfirm(module, id) {
    document.getElementById("restoreModule").value = module;
    document.getElementById("restoreId").value = id;
    restoreModal.style.display = "block";
  }
  function closeRestoreConfirm() {
    restoreModal.style.display = "none";
  }

  window.addEventListener("click", function(e) {
    if (e.target === restoreModal) restoreModal.style.display = "none";
  });
</script>

</body>
</html>
<?php $conn->close(); ?>
