<?php
session_start();

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/log_handler.php';

// ✅ Login check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// ✅ Admin only (recommended for adding programs)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/programs.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// ✅ Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/programs.php");
    exit();
}

// ✅ CSRF verify
csrf_verify();

$program_name = trim($_POST['program_name'] ?? '');

if ($program_name === '') {
    $_SESSION['flash'] = "❌ Program name is required.";
    header("Location: ../users/programs.php");
    exit();
}

// ✅ Check duplicate
$check_stmt = $conn->prepare("SELECT id FROM programs WHERE name = ? LIMIT 1");
$check_stmt->bind_param("s", $program_name);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    $check_stmt->close();
    $_SESSION['flash'] = "❌ Program name already exists.";
    header("Location: ../users/programs.php");
    exit();
}
$check_stmt->close();

// ✅ Insert
$stmt = $conn->prepare("INSERT INTO programs (name) VALUES (?)");
$stmt->bind_param("s", $program_name);

if ($stmt->execute()) {
    $newRecordId = (int)$stmt->insert_id;

    logAction(
        $conn,
        $user_id,
        'programs',
        $newRecordId,
        'Add Program',
        "Added program: {$program_name}"
    );

    $_SESSION['flash'] = "✅ Program added successfully.";
} else {
    $_SESSION['flash'] = "❌ Error: Could not save the program.";
}

$stmt->close();

header("Location: ../users/programs.php");
exit();
