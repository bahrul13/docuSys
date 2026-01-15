<?php
session_start();
require '../db/db_conn.php';
require '../function/log_handler.php';

// Get logged-in user ID (admin actor)
$actor_id = $_SESSION['user_id'] ?? null;

// Check if admin
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

// Validate correct input fields
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

// ✅ Prevent duplicate email (exclude current user)
$checkEmail = $conn->prepare("SELECT id FROM user WHERE email = ? AND id != ? LIMIT 1");
$checkEmail->bind_param("si", $email, $id);
$checkEmail->execute();
$checkRes = $checkEmail->get_result();
$exists = ($checkRes && $checkRes->num_rows > 0);
$checkEmail->close();

if ($exists) {
    $_SESSION['flash'] = "❌ Email already exists. Please use a different email.";
    header("Location: ../users/user.php");
    exit();
}

// Handle password condition
if ($rawPass !== '') {
    $password = password_hash($rawPass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE user SET fullname=?, email=?, password=?, role=? WHERE id=?");
    $stmt->bind_param("ssssi", $fullname, $email, $password, $role, $id);
} else {
    $stmt = $conn->prepare("UPDATE user SET fullname=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi", $fullname, $email, $role, $id);
}

if ($stmt->execute()) {

    $_SESSION['flash'] = "✅ User updated successfully.";

    // ✅ Log action
    logAction(
        $conn,
        $actor_id,
        'user',
        $id,
        'Update User',
        "Updated User: $fullname (Email: $email, Role: $role)"
    );

} else {
    $_SESSION['flash'] = "❌ Failed to update the user.";
}

$stmt->close();

// ❌ REMOVE this — PHP auto closes DB connection
// $conn->close();

header("Location: ../users/user.php");
exit();
?>
