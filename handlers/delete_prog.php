<?php
session_start();
require '../db/db_conn.php';
require '../function/log_handler.php';

// Get logged-in user ID and role
$user_id   = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {

    $id = (int)$_POST['id'];

    // Get program name for logging
    $stmt = $conn->prepare("SELECT name FROM programs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        $row = $result->fetch_assoc();
        $programName = $row['name'];

        // Delete from database
        $deleteStmt = $conn->prepare("DELETE FROM programs WHERE id = ?");
        $deleteStmt->bind_param("i", $id);

        if ($deleteStmt->execute()) {

            $logMessage = ($user_role === 'admin')
                ? "Deleted Program: {$programName}"
                : "Deleted a program";

            // ✅ Log action (logAction will fallback if user_id is invalid/null)
            logAction($conn, $user_id, 'programs', $id, 'Delete Program', $logMessage);

            $_SESSION['flash'] = "✅ Program deleted successfully.";

        } else {
            $_SESSION['flash'] = "❌ Failed to delete the program from database.";
        }

        $deleteStmt->close();

    } else {
        $_SESSION['flash'] = "⚠️ Program not found.";
    }

    $stmt->close();

} else {
    $_SESSION['flash'] = "⚠️ Invalid request.";
}

// ❌ Remove this (PHP auto closes the connection)
// $conn->close();

header("Location: ../users/programs.php");
exit();
?>
