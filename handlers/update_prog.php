<?php
session_start();
require '../db/db_conn.php';
require '../function/log_handler.php';

// Get logged-in user ID
$user_id = $_SESSION['user_id'] ?? null;

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/programs.php");
    exit();
}

// Validate input
if (isset($_POST['id'], $_POST['name']) && !empty(trim($_POST['name']))) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);

    // Check for duplicate program name (excluding current ID)
    $check_sql = "SELECT * FROM programs WHERE name = ? AND id != ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("si", $name, $id);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['flash'] = "Error: Program name already exists.";
        header("Location: ../users/programs.php");
        exit();
    }

    // Proceed to update
    $sql = "UPDATE programs SET name = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $name, $id);

    if ($stmt->execute()) {
        $_SESSION['flash'] = "✅ Program Updated successfully.";

        // ✅ Log the update action
        $logMessage = "Updated Program Name to: $name";

        // Safely check if user exists before logging to avoid foreign key errors
        if ($user_id) {
            $checkUser = $conn->prepare("SELECT id FROM user WHERE id = ?");
            $checkUser->bind_param("i", $user_id);
            $checkUser->execute();
            $checkResult = $checkUser->get_result();
            $checkUser->close();

            if ($checkResult->num_rows === 0) {
                $user_id = null; // fallback if user doesn't exist
            }
        }

        logAction($conn, $user_id, 'programs', $id, 'Update Program', $logMessage);

    } else {
        $_SESSION['flash'] = "❌ Failed to update the Program Name.";
    }

    $stmt->close();
} else {
    $_SESSION['flash'] = "⚠️ All fields are required.";
}

$conn->close();
header("Location: ../users/programs.php");
exit();
