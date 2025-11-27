<?php
require "../handlers/view_trba.php"; // Include PHP logic for data fetching
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Document</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
<link rel="stylesheet" href="../css/style.css">
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/uploads/dms.png">
<style>
.pdf-container {
    position: relative;
    margin-top: 20px;
}

.pdf-buttons {
    display: flex;
    justify-content: center; /* Center the buttons */
    gap: 10px;
    margin-top: 10px;
}

.pdf-buttons a, .pdf-buttons button {
    background-color: #4CAF50;
    color: white;
    padding: 8px 16px;
    text-decoration: none;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px; /* spacing between icon and text */
}

.pdf-buttons a.back-btn {
    background-color: #f44336;
}

.pdf-buttons a.download-btn {
    background-color: #2196F3;
}
</style>
</head>
<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
  <h1>
    <?= isset($doc['program_name']) ? htmlspecialchars($doc['program_name']) : "Document Name Not Available"; ?>
  </h1>

  <div class="pdf-container">
    <iframe src="<?= htmlspecialchars($pdfFileUrl) ?>" width="100%" height="600px"></iframe>

    <div class="pdf-buttons">
      
      <a href="<?= htmlspecialchars($pdfFileUrl) ?>" class="download-btn" download>
        <i class='bx bx-download'></i> Download
      </a>

      <a href="../users/trba.php" class="back-btn">
        <i class='bx bx-left-arrow-alt'></i> Back
      </a>
    </div>
  </div>
</section>

</body>
</html>
