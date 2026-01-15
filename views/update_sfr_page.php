<?php
session_start();
require '../db/db_conn.php';

// 🔐 Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// ❌ No ID
if (!isset($_GET['id'])) {
    header("Location: ../users/sfr.php");
    exit();
}

$id = intval($_GET['id']);

// 📄 Fetch SFR record
$stmt = $conn->prepare("SELECT * FROM sfr WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$sfr = $result->fetch_assoc();
$stmt->close();

if (!$sfr) {
    $_SESSION['flash'] = "❌ SFR record not found.";
    header("Location: ../users/sfr.php");
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
        <h2>Update SFR</h2>
        <div class="form-container">
            <form action="../handlers/update_sfr.php" method="POST" enctype="multipart/form-data" class="form-box">

            <!-- Hidden ID -->
            <input type="hidden" name="id" value="<?= (int)$sfr['id'] ?>">

            <!-- Program Name -->
            <label for="program_name">Program Name</label>
            <div class="select-wrapper">
                <select name="program_name" id="program_name" required>
                <option disabled>Select Program</option>
                <?php foreach ($programs as $prog): ?>
                    <option value="<?= htmlspecialchars($prog) ?>"
                    <?= $prog === $sfr['program_name'] ? 'selected' : '' ?>>
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
                    <?= $type === $sfr['survey_type'] ? 'selected' : '' ?>>
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
                value="<?= htmlspecialchars($sfr['survey_date']) ?>"
                required
            >

            <!-- Upload New PDF -->
            <label for="file_name">Upload New PDF (optional)</label>
            <input type="file" name="file_name" id="file_name" accept="application/pdf">

            <!-- Buttons -->
            <div class="form-buttons">
                <button type="button" class="btn-add" onclick="openSfrUpdateConfirm()">Update</button>
                <button type="button" class="btn-cancel" onclick="openSfrCancelConfirm()">Cancel</button>
            </div>
            </form>
        </div>
    </section>
</section>

    <div id="sfrUpdateConfirmModal" class="modal">
    <div class="modal-content">
        <h1>Confirm Update</h1>
        <p>Are you sure you want to update this SFR record?</p>
        <div class="modal-actions">
        <button class="btn-add" onclick="submitSfrUpdate()">Yes, Update</button>
        <button class="btn-cancel" onclick="closeSfrUpdateConfirm()">Cancel</button>
        </div>
    </div>
    </div>

    <div id="sfrCancelConfirmModal" class="modal">
    <div class="modal-content">
        <h1>Cancel Update</h1>
        <p>Unsaved changes will be lost. Continue?</p>
        <div class="modal-actions">
        <button class="btn-delete-confirm" onclick="confirmSfrCancel()">Yes, Leave</button>
        <button class="btn-cancel" onclick="closeSfrCancelConfirm()">Stay</button>
        </div>
    </div>
    </div>

    <script>
        const sfrUpdateModal = document.getElementById('sfrUpdateConfirmModal');
        const sfrCancelModal = document.getElementById('sfrCancelConfirmModal');
        const sfrForm = document.querySelector('.form-box');

        function openSfrUpdateConfirm() { sfrUpdateModal.style.display = 'block'; }
        function closeSfrUpdateConfirm() { sfrUpdateModal.style.display = 'none'; }
        function submitSfrUpdate() { sfrForm.submit(); }

        function openSfrCancelConfirm() { sfrCancelModal.style.display = 'block'; }
        function closeSfrCancelConfirm() { sfrCancelModal.style.display = 'none'; }
        function confirmSfrCancel() { window.location.href = "../users/sfr.php"; }

        window.onclick = e => {
            if (e.target === sfrUpdateModal) sfrUpdateModal.style.display = 'none';
            if (e.target === sfrCancelModal) sfrCancelModal.style.display = 'none';
        };
    </script>

<?php $conn->close(); ?>

</body>
</html>
