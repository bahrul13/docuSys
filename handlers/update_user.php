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

// Get logged-in user ID (admin actor)
$actor_id = (int)$_SESSION['user_id'];

// ✅ Admin only
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/user.php");
    exit();
}

// ✅ Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/user.php");
    exit();
}

// ✅ CSRF check
csrf_verify();

// Validate input fields
$id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$fullname = trim($_POST['fullname'] ?? '');
$email    = trim($_POST['email'] ?? '');
$role     = trim($_POST['role'] ?? '');
$rawPass  = $_POST['password'] ?? '';

if ($id <= 0 || $fullname === '' || $email === '' || ($role !== 'admin' && $role !== 'user')) {
    $_SESSION['flash'] = "⚠️ All fields are required (and role must be admin/user).";
    header("Location: ../users/user.php");
    exit();
}

// Optional: simple email format validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash'] = "⚠️ Invalid email format.";
    header("Location: ../users/user.php");
    exit();
}

// ✅ Prevent duplicate email (exclude current user)
$checkEmail = $conn->prepare("SELECT id FROM user WHERE email = ? AND id != ? LIMIT 1");
$checkEmail->bind_param("si", $email, $id);
$checkEmail->execute();
$checkRes = $checkEmail->get_result();

if ($checkRes && $checkRes->num_rows > 0) {
    $checkEmail->close();
    $_SESSION['flash'] = "❌ Email already exists. Please use a different email.";
    header("Location: ../users/user.php");
    exit();
}
$checkEmail->close();

// Handle password condition
if ($rawPass !== '') {
    // Optional: enforce password rules (recommended)
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9])[^\s]{8,20}$/', $rawPass)) {
        $_SESSION['flash'] = "⚠️ Password must be 8–20 characters and include letters, numbers, and special characters.";
        header("Location: ../users/user.php");
        exit();
    }

    $password = password_hash($rawPass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE user SET fullname = ?, email = ?, password = ?, role = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $fullname, $email, $password, $role, $id);
} else {
    $stmt = $conn->prepare("UPDATE user SET fullname = ?, email = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $fullname, $email, $role, $id);
}

if ($stmt->execute()) {

    $_SESSION['flash'] = "✅ User updated successfully.";

    logAction(
        $conn,
        $actor_id,
        'user',
        $id,
        'Update User',
        "Updated User: $fullname"
    );

} else {
    $_SESSION['flash'] = "❌ Failed to update the user.";
}

$stmt->close();

header("Location: ../users/user.php");
exit();
?>
