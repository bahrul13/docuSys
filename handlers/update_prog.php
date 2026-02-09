<?php
session_start();

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/log_handler.php';

// Get logged-in user ID
$user_id = $_SESSION['user_id'] ?? null;

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/programs.php");
    exit();
}

// ✅ Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/programs.php");
    exit();
}

csrf_verify();

// Validate input
$id   = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name = trim($_POST['name'] ?? '');

if ($id <= 0 || $name === '') {
    $_SESSION['flash'] = "⚠️ All fields are required.";
    header("Location: ../users/programs.php");
    exit();
}

// ✅ Check duplicate program name (excluding current ID)
$stmt_check = $conn->prepare("SELECT id FROM programs WHERE name = ? AND id != ? LIMIT 1");
$stmt_check->bind_param("si", $name, $id);
$stmt_check->execute();
$check_result = $stmt_check->get_result();

if ($check_result && $check_result->num_rows > 0) {
    $stmt_check->close();
    $_SESSION['flash'] = "❌ Program name already exists.";
    header("Location: ../users/programs.php");
    exit();
}
$stmt_check->close();

// ✅ Update program
$stmt = $conn->prepare("UPDATE programs SET name = ? WHERE id = ?");
$stmt->bind_param("si", $name, $id);

if ($stmt->execute()) {

    $_SESSION['flash'] = "✅ Program updated successfully.";

    // ✅ Log the update action
    $logMessage = "Updated Program Name to: $name";
    logAction($conn, $user_id, 'programs', $id, 'Update Program', $logMessage);

} else {
    $_SESSION['flash'] = "❌ Failed to update the Program Name.";
}

$stmt->close();

// ❌ REMOVE this — PHP auto closes DB connection
// $conn->close();

header("Location: ../users/programs.php");
exit();
?>
