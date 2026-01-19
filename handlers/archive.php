<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require "../db/db_conn.php";
require "../function/log_handler.php";

// 🔐 Security checks
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['user_role'] !== 'admin') {
    die("Access denied");
}

// ✅ Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request.";
    header("Location: ../users/dashboard.php");
    exit();
}

$id    = (int)($_POST['id'] ?? 0);
$table = $_POST['table'] ?? '';
$redirect = $_POST['redirect'] ?? '../users/dashboard.php';

// ✅ Whitelist allowed tables (VERY IMPORTANT)
$allowedTables = [
    'sfr' => 'SFR',
    'copc' => 'COPC',
    'trba' => 'TRBA',
    'documents' => 'Document'
];

if (!$id || !array_key_exists($table, $allowedTables)) {
    $_SESSION['flash'] = "⚠️ Invalid archive request.";
    header("Location: $redirect");
    exit();
}

// ✅ Archive query
$sql = "UPDATE `$table` 
        SET is_archived = 1, archived_at = NOW() 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    // 🧾 Log action
    logAction(
        $conn,
        $_SESSION['user_id'],
        $table,
        $id,
        'Archive',
        "Archived {$allowedTables[$table]} record"
    );

    $_SESSION['flash'] = "✅ {$allowedTables[$table]} archived successfully.";
} else {
    $_SESSION['flash'] = "❌ Failed to archive {$allowedTables[$table]}.";
}

$stmt->close();
$conn->close();

header("Location: $redirect");
exit();
