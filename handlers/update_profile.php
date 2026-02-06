<?php
session_start();
require "../db/db_conn.php";
require "../function/log_handler.php";
require "../function/csrf.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/profile.php");
    exit();
}

csrf_verify();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$fullname = trim($_POST['fullname'] ?? '');
$dept     = trim($_POST['dept'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// validation
if ($fullname === '' || $dept === '') {
    $_SESSION['flash'] = "⚠️ Full name and department are required.";
    header("Location: ../users/profile.php");
    exit();
}

// Update with password
if ($password !== '') {

    // ✅ Confirm password check
    if ($password !== $confirm_password) {
        $_SESSION['flash'] = "❌ Passwords do not match.";
        header("Location: ../users/profile.php");
        exit();
    }

    // ✅ Prevent reusing the same password (fetch current hash then compare)
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

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9])[^\s]{8,20}$/', $password)) {
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
    logAction($conn, $user_id, 'user', $user_id, 'Update Profile', 'User updated profile settings: '. $fullname);
    $_SESSION['flash'] = "✅ Profile updated successfully.";
} else {
    $_SESSION['flash'] = "❌ Failed to update profile.";
}

$stmt->close();

// $conn->close();

header("Location: ../users/profile.php");
exit();
?>
