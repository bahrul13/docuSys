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

// ✅ Admin only (recommended for delete)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/copc.php");
    exit();
}

// ✅ CSRF verify (MUST for delete)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/copc.php");
    exit();
}
csrf_verify();

// ✅ Validate ID
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash'] = "⚠️ Invalid ID.";
    header("Location: ../users/copc.php");
    exit();
}

// ✅ Actor for logs
$user_id = (int)$_SESSION['user_id'];

// ✅ Get file name first
$stmt = $conn->prepare("SELECT file_name, program FROM copc WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$row) {
    $_SESSION['flash'] = "⚠️ Document not found.";
    header("Location: ../users/copc.php");
    exit();
}

$fileName = $row['file_name'] ?? '';
$program  = $row['program'] ?? 'Unknown';
$filePath = __DIR__ . '/../uploads/copc/' . $fileName;

// ✅ Delete from DB
$deleteStmt = $conn->prepare("DELETE FROM copc WHERE id = ? LIMIT 1");
$deleteStmt->bind_param("i", $id);

if ($deleteStmt->execute()) {

    // ✅ Delete physical file (optional; only if exists)
    if (!empty($fileName) && file_exists($filePath)) {
        unlink($filePath);
    }

    // ✅ Log
    logAction(
        $conn,
        $user_id,
        'copc',
        $id,
        'Delete COPC',
        "Deleted COPC record: {$program}"
    );

    $_SESSION['flash'] = "✅ COPC deleted successfully.";

} else {
    $_SESSION['flash'] = "❌ Failed to delete COPC.";
}

$deleteStmt->close();

header("Location: ../users/copc.php");
exit();
?>
