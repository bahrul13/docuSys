<?php
session_start();

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/log_handler.php';

// ✅ Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id   = (int)$_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? '';

// ✅ Admin only (recommended for delete)
if ($user_role !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/sfr.php");
    exit();
}

// ✅ Must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/sfr.php");
    exit();
}

// ✅ CSRF verify
csrf_verify();

// ✅ Validate ID
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash'] = "⚠️ Invalid SFR ID.";
    header("Location: ../users/sfr.php");
    exit();
}

// ✅ Get document info for logging and deleting
$stmt = $conn->prepare("SELECT file_name, program_name FROM sfr WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
    $_SESSION['flash'] = "⚠️ SFR not found.";
    header("Location: ../users/sfr.php");
    exit();
}

$fileName = $row['file_name'] ?? '';
$program  = $row['program_name'] ?? 'Unknown';

// ✅ Delete from database first
$deleteStmt = $conn->prepare("DELETE FROM sfr WHERE id = ? LIMIT 1");
$deleteStmt->bind_param("i", $id);

if ($deleteStmt->execute()) {

    // ✅ Delete physical file safely (prevent path tricks)
    if (!empty($fileName)) {
        $uploadDir = realpath(__DIR__ . '/../uploads/sfr');
        $filePath  = realpath(__DIR__ . '/../uploads/sfr/' . $fileName);

        if ($uploadDir && $filePath && str_starts_with($filePath, $uploadDir) && file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // ✅ Log
    logAction(
        $conn,
        $user_id,
        'sfr',
        $id,
        'Delete SFR',
        "Deleted SFR: {$program} (File: {$fileName})"
    );

    $_SESSION['flash'] = "✅ SFR deleted successfully.";
} else {
    $_SESSION['flash'] = "❌ Failed to delete the SFR from database.";
}

$deleteStmt->close();

header("Location: ../users/sfr.php");
exit();
?>
