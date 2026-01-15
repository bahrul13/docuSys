<?php
session_start();
require "../db/db_conn.php";
require "../function/log_handler.php";

// ✅ Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/profile.php");
    exit();
}

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$fullname = trim($_POST['fullname'] ?? '');
$dept     = trim($_POST['dept'] ?? '');
$password = $_POST['password'] ?? '';

// Basic validation
if ($fullname === '' || $dept === '') {
    $_SESSION['flash'] = "⚠️ Full name and department are required.";
    header("Location: ../users/profile.php");
    exit();
}

// Update with password
if ($password !== '') {

    // Password strength (8–12, at least 1 letter, 1 number, 1 special, no spaces)
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9])[^\s]{8,12}$/', $password)) {
        $_SESSION['flash'] = "⚠️ Password must be 8–12 characters and include letters, numbers, and special characters.";
        header("Location: ../users/profile.php");
        exit();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        UPDATE user
        SET fullname = ?, dept = ?, password = ?
        WHERE id = ?
    ");
    $stmt->bind_param("sssi", $fullname, $dept, $hashed, $user_id);

} else {

    // Update without password
    $stmt = $conn->prepare("
        UPDATE user
        SET fullname = ?, dept = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssi", $fullname, $dept, $user_id);
}

if ($stmt->execute()) {
    logAction($conn, $user_id, 'user', $user_id, 'Update Profile', 'User updated profile settings');
    $_SESSION['flash'] = "✅ Profile updated successfully.";
} else {
    $_SESSION['flash'] = "❌ Failed to update profile.";
}

$stmt->close();

// ❌ REMOVE this — PHP auto closes DB connection
// $conn->close();

header("Location: ../users/profile.php");
exit();
?>
