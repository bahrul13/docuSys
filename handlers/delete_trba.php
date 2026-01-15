<?php
session_start();
require '../db/db_conn.php';
require '../function/log_handler.php';

// Get logged-in user ID and role
$user_id   = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;

// Check for valid POST request with document ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {

    $id = (int)$_POST['id'];

    // Get document info for logging and deleting
    $stmt = $conn->prepare("SELECT file_name, program_name FROM trba WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        $row      = $result->fetch_assoc();
        $fileName = $row['file_name'];
        $program  = $row['program_name'];

        $filePath = '../uploads/trba/' . $fileName;

        // Delete from database
        $deleteStmt = $conn->prepare("DELETE FROM trba WHERE id = ?");
        $deleteStmt->bind_param("i", $id);

        if ($deleteStmt->execute()) {

            // Delete physical file if it exists
            if (!empty($fileName) && file_exists($filePath)) {
                unlink($filePath);
            }

            // ✅ Prepare log message
            $logMessage = ($user_role === 'admin')
                ? "Deleted TRBA: {$program} (File: {$fileName})"
                : "Deleted a TRBA document";

            // ✅ Log action (logAction handles fallback user)
            logAction(
                $conn,
                $user_id,
                'trba',
                $id,
                'Delete TRBA',
                $logMessage
            );

            $_SESSION['flash'] = "✅ TRBA deleted successfully.";

        } else {
            $_SESSION['flash'] = "❌ Failed to delete the TRBA from database.";
        }

        $deleteStmt->close();

    } else {
        $_SESSION['flash'] = "⚠️ TRBA not found.";
    }

    $stmt->close();

} else {
    $_SESSION['flash'] = "⚠️ Invalid request.";
}

// ❌ REMOVE this — PHP auto-closes the connection
// $conn->close();

header("Location: ../users/trba.php");
exit();
?>
