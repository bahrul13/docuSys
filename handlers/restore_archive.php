<?php
session_start();
require "../db/db_conn.php";
require "../function/log_handler.php";

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

$module = $_POST['module'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// ✅ whitelist modules -> table
$tables = [
  'copc'  => 'copc',
  'trba'  => 'trba',
  'sfr'   => 'sfr',
  'other' => 'documents',
];

if (!isset($tables[$module]) || $id <= 0) {
  $_SESSION['flash'] = "⚠️ Invalid module or ID.";
  header("Location: ../admin/archived_documents.php");
  exit();
}

$table = $tables[$module];

// Restore (set is_archived = 0)
$sql = "UPDATE `$table` SET is_archived = 0 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
  logAction($conn, $_SESSION['user_id'], $module, $id, 'restore', "Restored archived {$module} record");
  $_SESSION['flash'] = "✅ Document restored successfully.";
} else {
  $_SESSION['flash'] = "❌ Restore failed.";
}

$stmt->close();
$conn->close();

header("Location: ../admin/archived_documents.php");
exit();
