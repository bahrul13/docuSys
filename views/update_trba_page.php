<?php
session_start();

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';

// 🔐 Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// ❌ No ID
if (!isset($_GET['id'])) {
    header("Location: ../users/trba.php");
    exit();
}

$id = intval($_GET['id']);

// 📄 Fetch trba record
$stmt = $conn->prepare("SELECT * FROM trba WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$trba = $result->fetch_assoc();
$stmt->close();

if (!$trba) {
    $_SESSION['flash'] = "❌ TRBA record not found.";
    header("Location: ../users/trba.php");
    exit();
}

// 📚 Fetch programs
$programs = [];
$res = $conn->query("SELECT name FROM programs ORDER BY name ASC");
while ($row = $res->fetch_assoc()) {
    $programs[] = $row['name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update SFR</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
<link rel="stylesheet" href="../css/style.css">
<link rel="icon" type="image/png" href="/uploads/dms.png">
</head>

<body>

<?php include('../includes/sidebar.php'); ?>

<section class="dashboard-content">
    <section class="table-section">
        <h2>Update TRBA</h2>
        <div class="form-container">
            <form action="../handlers/update_trba.php" method="POST" enctype="multipart/form-data" class="form-box">
            <?= csrf_field(); ?>
            <!-- Hidden ID -->
            <input type="hidden" name="id" value="<?= (int)$trba['id'] ?>">

            <!-- Program Name -->
            <label for="program_name">Program Name</label>
            <div class="select-wrapper">
                <select name="program_name" id="program_name" required>
                <option disabled>Select Program</option>
                <?php foreach ($programs as $prog): ?>
                    <option value="<?= htmlspecialchars($prog) ?>"
                    <?= $prog === $trba['program_name'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($prog) ?>
                    </option>
                <?php endforeach; ?>
                </select>
                <i class="bx bx-chevron-down select-icon"></i>
            </div>

            <!-- Survey Type -->
            <label for="survey_type">Type of Survey</label>
            <div class="select-wrapper">
                <select name="survey_type" id="survey_type" required>
                <?php
                $types = [
                    'PSV', 'Level 1', 'Level 2', 'Revisit Level 2',
                    'Level 3', 'Revisit Level 3',
                    'Level 4', 'Revisit Level 4'
                ];
                ?>
                <?php foreach ($types as $type): ?>
                    <option value="<?= $type ?>"
                    <?= $type === $trba['survey_type'] ? 'selected' : '' ?>>
                    <?= $type ?>
                    </option>
                <?php endforeach; ?>
                </select>
                <i class="bx bx-chevron-down select-icon"></i>
            </div>

            <!-- Survey Date -->
            <label for="survey_date">Survey Date</label>
            <input
                type="date"
                name="survey_date"
                id="survey_date"
                value="<?= htmlspecialchars($trba['survey_date']) ?>"
                required
            >

            <!-- Upload New PDF -->
            <label for="file_name">Upload New PDF (optional)</label>
            <input type="file" name="file_name" id="file_name" accept="application/pdf">

            <!-- Buttons -->
            <div class="form-buttons">
                <button type="button" class="btn-add" onclick="openTrbaUpdateConfirm()">Update</button>
                <button type="button" class="btn-cancel" onclick="openTrbaCancelConfirm()">Cancel</button>
            </div>


            </form>
        </div>
    </section>
</section>
    <div id="trbaUpdateConfirmModal" class="modal">
    <div class="modal-content">
        <h1>Confirm Update</h1>
        <p>Are you sure you want to update this TRBA record?</p>
        <div class="modal-actions">
        <button class="btn-add" onclick="submitTrbaUpdate()">Yes, Update</button>
        <button class="btn-cancel" onclick="closeTrbaUpdateConfirm()">Cancel</button>
        </div>
    </div>
    </div>

    <div id="trbaCancelConfirmModal" class="modal">
    <div class="modal-content">
        <h1>Cancel Update</h1>
        <p>Unsaved changes will be lost. Continue?</p>
        <div class="modal-actions">
        <button class="btn-delete-confirm" onclick="confirmTrbaCancel()">Yes, Leave</button>
        <button class="btn-cancel" onclick="closeTrbaCancelConfirm()">Stay</button>
        </div>
    </div>
    </div>

    <script>
        const trbaUpdateModal = document.getElementById('trbaUpdateConfirmModal');
        const trbaCancelModal = document.getElementById('trbaCancelConfirmModal');
        const trbaForm = document.querySelector('.form-box');

        function openTrbaUpdateConfirm() { trbaUpdateModal.style.display = 'block'; }
        function closeTrbaUpdateConfirm() { trbaUpdateModal.style.display = 'none'; }
        function submitTrbaUpdate() { trbaForm.submit(); }

        function openTrbaCancelConfirm() { trbaCancelModal.style.display = 'block'; }
        function closeTrbaCancelConfirm() { trbaCancelModal.style.display = 'none'; }
        function confirmTrbaCancel() { window.location.href = "../users/trba.php"; }

        window.onclick = e => {
            if (e.target === trbaUpdateModal) trbaUpdateModal.style.display = 'none';
            if (e.target === trbaCancelModal) trbaCancelModal.style.display = 'none';
        };
    </script>

<?php $conn->close(); ?>

</body>
</html>
