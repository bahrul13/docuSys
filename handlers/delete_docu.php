<?php
session_start();
require '../db/db_conn.php';

// Ensure only admin can access this
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/other.php");
    exit();
}

// Check for valid POST request with program ID
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Prepare and execute delete statement
    $sql = "DELETE FROM documents WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['delete_flash'] = "✅ Program deleted successfully.";
        } else {
            $_SESSION['delete_flash'] = "❌ Failed to delete the program.";
        }
        $stmt->close();
    } else {
        $_SESSION['delete_flash'] = "❌ Failed to prepare delete statement.";
    }

} else {
    $_SESSION['delete_flash'] = "⚠️ Invalid request.";
}

// Close the connection and redirect
$conn->close();
header("Location: ../users/other.php");
exit();
