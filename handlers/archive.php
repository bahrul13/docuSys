<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/log_handler.php';

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

csrf_verify();

$id    = (int)($_POST['id'] ?? 0);
$table = $_POST['table'] ?? '';
$redirect = $_POST['redirect'] ?? '../users/dashboard.php';

// ✅ Whitelist allowed tables (VERY IMPORTANT)
$allowedTables = [
    'sfr' => 'SFR',
    'copc' => 'COPC',
    'trba' => 'TRBA',
    'documents' => 'Accreditation'
];

// ✅ Map table -> module alias (for logs / archived module name)
$tableToModule = [
    'sfr'       => 'sfr',
    'copc'      => 'copc',
    'trba'      => 'trba',
    'documents' => 'accreditation' // ✅ change this to your desired module name
];


if (!$id || !array_key_exists($table, $allowedTables)) {
    $_SESSION['flash'] = "⚠️ Invalid archive request.";
    header("Location: $redirect");
    exit();
}

$getStmt = $conn->prepare("SELECT * FROM `$table` WHERE id = ? LIMIT 1");
$getStmt->bind_param("i", $id);
$getStmt->execute();
$getRes = $getStmt->get_result();
$row = $getRes->fetch_assoc();
$getStmt->close();

$programName =
    $row['program']
    ?? $row['program_name']
    ?? $row['document']
    ?? 'Unknown';

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
        ($tableToModule[$table] ?? $table), // ✅ module saved in logs becomes "accreditation"
        $id,
        'Archive',
        "Archived " . ($tableToModule[$table] ?? $table) . " record: {$programName}"
    );



    $_SESSION['flash'] = "✅ {$allowedTables[$table]} archived successfully.";
} else {
    $_SESSION['flash'] = "❌ Failed to archive {$allowedTables[$table]}.";
}

$stmt->close();
$conn->close();

header("Location: $redirect");
exit();
