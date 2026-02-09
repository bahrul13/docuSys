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
    header("Location: ../users/other.php");
    exit();
}

// ✅ Must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/other.php");
    exit();
}

// ✅ CSRF verify
csrf_verify();

// ✅ Validate ID
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash'] = "⚠️ Invalid document ID.";
    header("Location: ../users/other.php");
    exit();
}

// ✅ Get document info first
$stmt = $conn->prepare("SELECT file_name, document FROM documents WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
    $_SESSION['flash'] = "⚠️ Document not found.";
    header("Location: ../users/other.php");
    exit();
}

$fileName = $row['file_name'] ?? '';
$docName  = $row['document'] ?? 'Unknown';

// ✅ Delete from database
$deleteStmt = $conn->prepare("DELETE FROM documents WHERE id = ? LIMIT 1");
$deleteStmt->bind_param("i", $id);

if ($deleteStmt->execute()) {

    // ✅ Delete physical file (use absolute path)
    if (!empty($fileName)) {
        $filePath = __DIR__ . '/../uploads/other/' . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // ✅ Log action
    logAction(
        $conn,
        $user_id,
        'accreditation',
        $id,
        'Delete Document',
        "Deleted Document: {$docName})"
    );

    $_SESSION['flash'] = "✅ Document deleted successfully.";

} else {
    $_SESSION['flash'] = "❌ Failed to delete the document from database.";
}

$deleteStmt->close();

header("Location: ../users/other.php");
exit();
?>
