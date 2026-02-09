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
    header("Location: ../users/programs.php");
    exit();
}

// ✅ Must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/programs.php");
    exit();
}

// ✅ CSRF verify
csrf_verify();

// ✅ Validate ID
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash'] = "⚠️ Invalid program ID.";
    header("Location: ../users/programs.php");
    exit();
}

// ✅ Get program name for logging
$stmt = $conn->prepare("SELECT name FROM programs WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
    $_SESSION['flash'] = "⚠️ Program not found.";
    header("Location: ../users/programs.php");
    exit();
}

$programName = $row['name'] ?? 'Unknown';

/*
  ✅ OPTIONAL SAFETY CHECK:
  Prevent deleting if program is used in other tables.
  (Uncomment if you want this protection)
*/
/*
$checkUse = $conn->prepare("
    SELECT
      (SELECT COUNT(*) FROM copc WHERE program = ?) +
      (SELECT COUNT(*) FROM trba WHERE program_name = ?) +
      (SELECT COUNT(*) FROM sfr  WHERE program_name = ?) AS total_used
");
$checkUse->bind_param("sss", $programName, $programName, $programName);
$checkUse->execute();
$usedRes = $checkUse->get_result();
$usedRow = $usedRes->fetch_assoc();
$checkUse->close();

if (!empty($usedRow['total_used']) && (int)$usedRow['total_used'] > 0) {
    $_SESSION['flash'] = "⚠️ Cannot delete. Program is being used by existing documents.";
    header("Location: ../users/programs.php");
    exit();
}
*/

// ✅ Delete from database
$deleteStmt = $conn->prepare("DELETE FROM programs WHERE id = ? LIMIT 1");
$deleteStmt->bind_param("i", $id);

if ($deleteStmt->execute()) {

    logAction(
        $conn,
        $user_id,
        'programs',
        $id,
        'Delete Program',
        "Deleted Program: {$programName}"
    );

    $_SESSION['flash'] = "✅ Program deleted successfully.";

} else {
    $_SESSION['flash'] = "❌ Failed to delete the program from database.";
}

$deleteStmt->close();

header("Location: ../users/programs.php");
exit();
?>
