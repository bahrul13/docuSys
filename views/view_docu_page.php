<?php
require "../handlers/view_docu.php"; // Fetch document data from DB

// Make sure $doc exists and has file_name
$pdfFileUrl = isset($doc['file_name']) ? "../uploads/other/" . $doc['file_name'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Document</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/pdf.css">
<link rel="icon" type="image/png" href="/uploads/dms.png">
</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
  <h1>
    <?= isset($doc['document']) ? htmlspecialchars($doc['document']) : "Document Name Not Available"; ?>
  </h1>

  <?php if ($pdfFileUrl && file_exists($pdfFileUrl)): ?>
  <div class="pdf-container">
    <iframe src="<?= htmlspecialchars($pdfFileUrl) ?>" width="100%" height="600px"></iframe>

    <div class="pdf-buttons">
      <!-- Download button triggers download.php safely -->
      <a href="../handlers/download.php?folder=other&file=<?= urlencode($doc['file_name']) ?>" class="download-btn">
        <i class='bx bx-download'></i> Download
      </a>

      <a href="../users/other.php" class="back-btn">
        <i class='bx bx-left-arrow-alt'></i> Back
      </a>
    </div>
  </div>
  <?php else: ?>
    <p class="error-msg">❌ File not found.</p>
  <?php endif; ?>

</section>

</body>
</html>
