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
    $stmt = $conn->prepare("SELECT file_name, program_name FROM sfr WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        $row      = $result->fetch_assoc();
        $fileName = $row['file_name'];
        $program  = $row['program_name'];

        $filePath = '../uploads/sfr/' . $fileName;

        // Delete from database
        $deleteStmt = $conn->prepare("DELETE FROM sfr WHERE id = ?");
        $deleteStmt->bind_param("i", $id);

        if ($deleteStmt->execute()) {

            // Delete physical file if it exists
            if (!empty($fileName) && file_exists($filePath)) {
                unlink($filePath);
            }

            // ✅ Prepare log message
            $logMessage = ($user_role === 'admin')
                ? "Deleted SFR: {$program} (File: {$fileName})"
                : "Deleted a SFR document";

            // ✅ Log action (logAction handles fallback user)
            logAction(
                $conn,
                $user_id,
                'sfr',
                $id,
                'Delete SFR',
                $logMessage
            );

            $_SESSION['flash'] = "✅ SFR deleted successfully.";

        } else {
            $_SESSION['flash'] = "❌ Failed to delete the SFR from database.";
        }

        $deleteStmt->close();

    } else {
        $_SESSION['flash'] = "⚠️ SFR not found.";
    }

    $stmt->close();

} else {
    $_SESSION['flash'] = "⚠️ Invalid request.";
}

// ❌ REMOVE this — PHP auto-closes the connection
// $conn->close();

header("Location: ../users/sfr.php");
exit();
?>
