<?php
session_start();

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/log_handler.php';

//  Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/user.php");
    exit();
}

//  CSRF protection (IMPORTANT)
csrf_verify();

// ✅ Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

//  Admin only (recommended for adding users)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/user.php");
    exit();
}

//  Actor (admin who is adding)
$actor_id = (int)$_SESSION['user_id'];

$fullname = trim($_POST['fullname'] ?? '');
$dept     = trim($_POST['department'] ?? '');
$email    = trim($_POST['email'] ?? '');
$rawPass  = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? 'user';

// ===== basic validation =====
if ($fullname === '' || $dept === '' || $email === '' || $rawPass === '') {
    $_SESSION['flash'] = "❌ All fields are required.";
    header("Location: ../users/user.php");
    exit();
}

//  validate role
if ($role !== 'admin' && $role !== 'user') {
    $_SESSION['flash'] = "❌ Invalid role selected.";
    header("Location: ../users/user.php");
    exit();
}

//  validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash'] = "❌ Invalid email format.";
    header("Location: ../users/user.php");
    exit();
}

//  optional: password rules (match your system rules)
if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9])[^\s]{8,20}$/', $rawPass)) {
    $_SESSION['flash'] = "❌ Password must be 8–20 characters and include letters, numbers, and symbols (no spaces).";
    header("Location: ../users/user.php");
    exit();
}

$password = password_hash($rawPass, PASSWORD_DEFAULT);

//  Check for duplicate email
$check = $conn->prepare("SELECT id FROM user WHERE email = ? LIMIT 1");
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

//  Insert user into database
// also set status if your system uses it
$stmt = $conn->prepare("
    INSERT INTO user (fullname, dept, email, password, role, status)
    VALUES (?, ?, ?, ?, ?, 'approved')
");
$stmt->bind_param("sssss", $fullname, $dept, $email, $password, $role);

if ($stmt->execute()) {

    $newUserId = (int)$stmt->insert_id;

    //  Log action
    logAction(
        $conn,
        $actor_id,
        'user',
        $newUserId,
        'Add User',
        "Added user: {$fullname} ({$email})"
    );

    $_SESSION['flash'] = "✅ User added successfully!";

} else {
    $_SESSION['flash'] = "❌ Database error: Failed to add user.";
}

$stmt->close();

header("Location: ../users/user.php");
exit();
?>
