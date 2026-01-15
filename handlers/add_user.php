<?php
session_start();
require '../db/db_conn.php';
require "../function/log_handler.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/user.php");
    exit();
}

// ✅ Actor (admin/user who is adding)
$actor_id = $_SESSION['user_id'] ?? null;

$fullname = trim($_POST['fullname'] ?? '');
$dept     = trim($_POST['department'] ?? '');
$email    = trim($_POST['email'] ?? '');
$rawPass  = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? 'user';

// ===== basic validation =====
if ($fullname === '' || $dept === '' || $email === '' || $rawPass === '' || ($role !== 'admin' && $role !== 'user')) {
    $_SESSION['flash'] = "❌ All fields are required.";
    header("Location: ../users/user.php");
    exit();
}

$password = password_hash($rawPass, PASSWORD_DEFAULT);

// Check for duplicate email
$check = $conn->prepare("SELECT id FROM user WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->close();
    $_SESSION['flash'] = "❌ Email already exists. Please use a different email.";
    header("Location: ../users/user.php");
    exit();
}
$check->close();

// Insert user into database
$stmt = $conn->prepare("INSERT INTO user (fullname, dept, email, password, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $fullname, $dept, $email, $password, $role);

if ($stmt->execute()) {

    $newUserId = $stmt->insert_id;

    // ✅ Log action (actor is the logged-in admin)
    logAction(
        $conn,
        $actor_id,
        'user',
        (int)$newUserId,
        'Add User',
        "Added user: $fullname (Role: $role, Email: $email)"
    );

    $_SESSION['flash'] = "✅ User added successfully!";

} else {
    $_SESSION['flash'] = "❌ Database error: Failed to add user.";
}

$stmt->close();

// ❌ REMOVE THIS (PHP auto closes connection)
// $conn->close();

header("Location: ../users/user.php");
exit();
?>
