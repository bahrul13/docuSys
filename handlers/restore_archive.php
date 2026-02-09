<?php
session_start();
require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/log_handler.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
if (!$isAdmin) die("Access denied");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $_SESSION['flash'] = "⚠️ Invalid request.";
  header("Location: ../admin/archived_documents.php");
  exit();
}

csrf_verify();

$module = $_POST['module'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// ✅ whitelist modules -> table
$tables = [
  'copc'  => 'copc',
  'trba'  => 'trba',
  'sfr'   => 'sfr',
  'accreditation' => 'documents',
];

if (!isset($tables[$module]) || $id <= 0) {
  $_SESSION['flash'] = "⚠️ Invalid module or ID.";
  header("Location: ../admin/archived_documents.php");
  exit();
}

$table = $tables[$module];

$titleColMap = [
  'copc'  => 'program',
  'trba'  => 'program_name',
  'sfr'   => 'program_name',
  'accreditation' => 'document',
];

$titleCol = $titleColMap[$module];

$getStmt = $conn->prepare("SELECT `$titleCol` AS title FROM `$table` WHERE id = ? LIMIT 1");
$getStmt->bind_param("i", $id);
$getStmt->execute();
$getRes = $getStmt->get_result();
$row = $getRes->fetch_assoc();
$getStmt->close();

if (!$row) {
  $_SESSION['flash'] = "⚠️ Record not found.";
  header("Location: ../admin/archived_documents.php");
  exit();
}

$programName = $row['title'] ?? 'Unknown';

// Restore (set is_archived = 0)
$sql = "UPDATE `$table` SET is_archived = 0 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);


if ($stmt->execute()) {
   logAction(
    $conn,
    $_SESSION['user_id'],
    $module,
    $id,
    'restore',
    "Restored archived " . strtoupper($module) . " record: {$programName}"
  );

  $_SESSION['flash'] = "✅ Document restored successfully.";
} else {
  $_SESSION['flash'] = "❌ Restore failed.";
}

$stmt->close();
$conn->close();

header("Location: ../admin/archived_documents.php");
exit();
