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

$actor_id  = (int)$_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? '';

// ✅ Admin only
if ($user_role !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/user.php");
    exit();
}

// ✅ Must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/user.php");
    exit();
}

// ✅ CSRF verify
csrf_verify();

// ✅ Validate ID
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash'] = "⚠️ Invalid user ID.";
    header("Location: ../users/user.php");
    exit();
}

// ✅ Prevent deactivating yourself
if ($id === $actor_id) {
    $_SESSION['flash'] = "⚠️ You cannot deactivate your own account.";
    header("Location: ../users/user.php");
    exit();
}

// ✅ Fetch user info (fullname, role, status) for checks + logging
$stmt = $conn->prepare("SELECT fullname, role, status FROM user WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
    $_SESSION['flash'] = "⚠️ User not found.";
    header("Location: ../users/user.php");
    exit();
}

$fullname   = $row['fullname'] ?? 'Unknown';
$targetRole = $row['role'] ?? '';
$status     = $row['status'] ?? '';

// ✅ Prevent deactivating another admin
if ($targetRole === 'admin') {
    $_SESSION['flash'] = "⚠️ You cannot deactivate an admin account.";
    header("Location: ../users/user.php");
    exit();
}

// ✅ If already inactive, stop
if ($status === 'inactive') {
    $_SESSION['flash'] = "⚠️ User is already inactive.";
    header("Location: ../users/user.php");
    exit();
}

// ✅ Deactivate
$updateStmt = $conn->prepare("UPDATE user SET status = 'inactive' WHERE id = ? LIMIT 1");
$updateStmt->bind_param("i", $id);

if ($updateStmt->execute()) {

    logAction(
        $conn,
        $actor_id,
        'user',
        $id,
        'Deactivate User',
        "Deactivated user: {$fullname}"
    );

    $_SESSION['flash'] = "✅ User has been deactivated successfully.";
} else {
    $_SESSION['flash'] = "❌ Failed to deactivate the user.";
}

$updateStmt->close();

header("Location: ../users/user.php");
exit();
?>
