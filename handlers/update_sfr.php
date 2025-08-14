<?php
session_start();
require '../db/db_conn.php';

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/sfr.php");
    exit();
}

// Validate input
if (isset($_POST['id'], $_POST['program_name']) && !empty(trim($_POST['program_name']))) {
    $id = intval($_POST['id']);
    $name = trim($_POST['program_name']);

    // Check for duplicate name (excluding current ID)
    $check_sql = "SELECT * FROM sfr WHERE program_name = ? AND id != ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("si", $name, $id);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['flash'] = "Error: Program name already exists.";
        header("Location: ../users/sfr.php");
        exit();
    }

    // Proceed to update
    $sql = "UPDATE sfr SET program_name = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $name, $id);

    if ($stmt->execute()) {
        $_SESSION['flash'] = "✅ Program Updated successfully.";
    } else {
        $_SESSION['flash'] = "❌ Failed to update the Program Name.";
    }

    $stmt->close();
} else {
    $_SESSION['flash'] = "⚠️ All fields are required.";
}

$conn->close();
header("Location: ../users/sfr.php");
exit();
