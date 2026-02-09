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
    header("Location: ../users/trba.php");
    exit();
}

// ✅ Must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/trba.php");
    exit();
}

// ✅ CSRF verify
csrf_verify();

// ✅ Validate ID
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash'] = "⚠️ Invalid TRBA ID.";
    header("Location: ../users/trba.php");
    exit();
}

// ✅ Fetch record for file + log details
$stmt = $conn->prepare("SELECT file_name, program_name FROM trba WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
    $_SESSION['flash'] = "⚠️ TRBA not found.";
    header("Location: ../users/trba.php");
    exit();
}

$fileName = $row['file_name'] ?? '';
$program  = $row['program_name'] ?? 'Unknown';

// ✅ Delete from database first
$deleteStmt = $conn->prepare("DELETE FROM trba WHERE id = ? LIMIT 1");
$deleteStmt->bind_param("i", $id);

if ($deleteStmt->execute()) {

    // ✅ Delete physical file safely (prevent path tricks)
    if (!empty($fileName)) {
        $uploadDir = realpath(__DIR__ . '/../uploads/trba');
        $filePath  = realpath(__DIR__ . '/../uploads/trba/' . $fileName);

        if ($uploadDir && $filePath && str_starts_with($filePath, $uploadDir) && file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // ✅ Log
    logAction(
        $conn,
        $user_id,
        'trba',
        $id,
        'Delete TRBA',
        "Deleted TRBA: {$program} (File: {$fileName})"
    );

    $_SESSION['flash'] = "✅ TRBA deleted successfully.";
} else {
    $_SESSION['flash'] = "❌ Failed to delete the TRBA from database.";
}

$deleteStmt->close();

header("Location: ../users/trba.php");
exit();
?>
