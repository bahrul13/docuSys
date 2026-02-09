<?php
session_start();

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../handlers/view_trba.php';

// Build PDF path safely
$pdfFileUrl = isset($doc['file_name']) ? "../uploads/trba/" . $doc['file_name'] : null;
$fileExists = ($pdfFileUrl && file_exists($pdfFileUrl));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View TRBA Document</title>

<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/pdf.css">
<link rel="icon" type="image/png" href="/uploads/dms.png">
</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
  <section class="table-section">
    <h2>
      <?= isset($doc['program_name']) 
        ? htmlspecialchars($doc['program_name']) 
        : "Document Name Not Available"; ?>
    </h2>

    <?php if ($fileExists): ?>
      <div class="pdf-container">
        <iframe 
          src="<?= htmlspecialchars($pdfFileUrl) ?>" 
          width="100%" 
          height="600px">
        </iframe>

        <div class="pdf-buttons">
          <a href="../handlers/download.php?folder=trba&file=<?= urlencode($doc['file_name']) ?>&doc_id=<?= (int)$doc['id'] ?>">
            Download
          </a>

          <a href="../users/trba.php" class="back-btn">
            <i class='bx bx-left-arrow-alt'></i> Back
          </a>
        </div>
      </div>
    <?php endif; ?>
  </section>
</section>

<!-- ❌ FILE NOT FOUND POPUP -->
<?php if (!$fileExists): ?>
<div id="fileErrorModal" class="modal" style="display:block;">
  <div class="modal-content">
    <span class="close" onclick="closeFileErrorModal()">&times;</span>
    <h1>Error</h1>
    <p>❌ The requested document was not found or may have been removed.</p>

    <div class="modal-buttons">
      <button onclick="closeFileErrorModal()">OK</button>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function closeFileErrorModal() {
  document.getElementById('fileErrorModal').style.display = 'none';
  window.location.href = "../users/trba.php";
}
</script>

</body>
</html>
