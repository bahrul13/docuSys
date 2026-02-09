<?php
session_start();

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/log_handler.php';

// ✅ POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/profile.php");
    exit();
}

// ✅ Must be logged in first
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// ✅ CSRF verify
csrf_verify();

$user_id = (int)$_SESSION['user_id'];

$fullname         = trim($_POST['fullname'] ?? '');
$dept             = trim($_POST['dept'] ?? '');
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// ✅ Basic validation
if ($fullname === '' || $dept === '') {
    $_SESSION['flash'] = "⚠️ Full name and department are required.";
    header("Location: ../users/profile.php");
    exit();
}

// ✅ If user typed a password, confirm password must exist
if ($password !== '' && $confirm_password === '') {
    $_SESSION['flash'] = "⚠️ Please confirm your new password.";
    header("Location: ../users/profile.php");
    exit();
}

// ✅ Update with password
if ($password !== '') {

    if ($password !== $confirm_password) {
        $_SESSION['flash'] = "❌ Passwords do not match.";
        header("Location: ../users/profile.php");
        exit();
    }

    // ✅ Prevent reusing current password
    $pwStmt = $conn->prepare("SELECT password FROM user WHERE id = ? LIMIT 1");
    $pwStmt->bind_param("i", $user_id);
    $pwStmt->execute();
    $pwRes = $pwStmt->get_result();
    $pwRow = $pwRes->fetch_assoc();
    $pwStmt->close();

    if ($pwRow && !empty($pwRow['password']) && password_verify($password, $pwRow['password'])) {
        $_SESSION['flash'] = "⚠️ You cannot reuse your current password. Please choose a new one.";
        header("Location: ../users/profile.php");
        exit();
    }

    // ✅ Password rule: 8–20, at least 1 letter, 1 number, 1 symbol, no spaces
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9])[^\s]{8,20}$/', $password)) {
        $_SESSION['flash'] = "⚠️ Password must be 8–20 characters and include letters, numbers, and special characters.";
        header("Location: ../users/profile.php");
        exit();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE user SET fullname = ?, dept = ?, password = ? WHERE id = ?");
    $stmt->bind_param("sssi", $fullname, $dept, $hashed, $user_id);

} else {
    // ✅ Update without password
    $stmt = $conn->prepare("UPDATE user SET fullname = ?, dept = ? WHERE id = ?");
    $stmt->bind_param("ssi", $fullname, $dept, $user_id);
}

if ($stmt->execute()) {
    logAction($conn, $user_id, 'user', $user_id, 'Update Profile', "User updated profile settings: $fullname");
    $_SESSION['flash'] = "✅ Profile updated successfully.";
} else {
    $_SESSION['flash'] = "❌ Failed to update profile.";
}

$stmt->close();

header("Location: ../users/profile.php");
exit();
?>
