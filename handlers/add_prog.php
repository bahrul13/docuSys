<?php
session_start();
require '../db/db_conn.php';
require "../function/log_handler.php";

// ✅ Define user_id for logging
$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $program_name = trim($_POST['program_name'] ?? '');

    if (empty($program_name)) {
        $_SESSION['flash'] = "❌ All fields are required.";
        header("Location: ../users/programs.php");
        exit();
    }

    // ✅ Check if program name already exists
    $check_stmt = $conn->prepare("SELECT id FROM programs WHERE name = ?");
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

    // ✅ Insert new program
    $stmt = $conn->prepare("INSERT INTO programs (name) VALUES (?)");
    $stmt->bind_param("s", $program_name);

    if ($stmt->execute()) {

        $newRecordId = $stmt->insert_id;

        // ✅ Log the action
        logAction(
            $conn,
            $user_id,
            'programs',
            (int)$newRecordId,
            'Add Program',
            "Added Program: $program_name"
        );

        $_SESSION['flash'] = "✅ Program added successfully.";

    } else {
        $_SESSION['flash'] = "❌ Error: Could not save the program.";
    }

    $stmt->close();

    header("Location: ../users/programs.php");
    exit();

} else {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/programs.php");
    exit();
}
