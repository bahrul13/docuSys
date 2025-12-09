<?php
require "../handlers/view_docu.php"; // Include PHP logic for data fetching
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
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/uploads/dms.png">

</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
  <h1>
    <?= isset($doc['document']) ? htmlspecialchars($doc['document']) : "Document Name Not Available"; ?>
  </h1>


  <div class="pdf-container">
    <iframe src="<?= htmlspecialchars($pdfFileUrl) ?>" width="100%" height="640px"></iframe>

    <div class="pdf-buttons">
      
      <a href="<?= htmlspecialchars($pdfFileUrl) ?>" class="download-btn" download>
        <i class='bx bx-download'></i> Download
      </a>

      <a href="../users/other.php" class="back-btn">
        <i class='bx bx-left-arrow-alt'></i> Back
      </a>
    </div>
  </div>
</section>

</body>
</html>
