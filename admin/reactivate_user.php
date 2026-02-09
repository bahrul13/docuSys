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

// ✅ Admin only
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: inactive_user.php");
    exit();
}

// ✅ POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request.";
    header("Location: inactive_user.php");
    exit();
}

// ✅ CSRF verify
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
$admin_id = (int)$_SESSION['user_id'];

if ($id <= 0) {
    $_SESSION['flash'] = "⚠️ Invalid user ID.";
    header("Location: inactive_user.php");
    exit();
}

// Get user name (and confirm user exists)
$stmt = $conn->prepare("SELECT fullname FROM user WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $_SESSION['flash'] = "⚠️ User not found.";
    $stmt->close();
    header("Location: inactive_user.php");
    exit();
}

$user = $result->fetch_assoc();
$fullname = $user['fullname'] ?? 'Unknown';
$stmt->close();

// Reactivate user
$update = $conn->prepare("UPDATE user SET status = 'approved' WHERE id = ?");
$update->bind_param("i", $id);

if ($update->execute()) {

    logAction(
        $conn,
        $admin_id,
        'user',
        $id,
        'Reactivate User',
        "Reactivated user: {$fullname}"
    );

    $_SESSION['flash'] = "✅ User reactivated successfully.";
} else {
    $_SESSION['flash'] = "❌ Failed to reactivate user.";
}

$update->close();
$conn->close();

header("Location: inactive_user.php");
exit();
?>
