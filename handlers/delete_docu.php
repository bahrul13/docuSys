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

    // Get document name and file name for logging and deleting
    $stmt = $conn->prepare("SELECT file_name, document FROM documents WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        $row      = $result->fetch_assoc();
        $fileName = $row['file_name'];
        $docName  = $row['document'];

        $filePath = '../uploads/other/' . $fileName;

        // Delete from database
        $deleteStmt = $conn->prepare("DELETE FROM documents WHERE id = ?");
        $deleteStmt->bind_param("i", $id);

        if ($deleteStmt->execute()) {

            // Delete physical file if it exists
            if (!empty($fileName) && file_exists($filePath)) {
                unlink($filePath);
            }

            // ✅ Prepare log message
            $logMessage = ($user_role === 'admin')
                ? "Deleted Document: {$docName} (File: {$fileName})"
                : "Deleted a document";

            // ✅ Log action (logAction will fallback if user_id is invalid/null)
            logAction($conn, $user_id, 'documents', $id, 'Delete Document', $logMessage);

            $_SESSION['flash'] = "✅ Document deleted successfully.";

        } else {
            $_SESSION['flash'] = "❌ Failed to delete the document from database.";
        }

        $deleteStmt->close();

    } else {
        $_SESSION['flash'] = "⚠️ Document not found.";
    }

    $stmt->close();

} else {
    $_SESSION['flash'] = "⚠️ Invalid request.";
}

// ❌ Remove this (PHP auto closes connection)
// $conn->close();

header("Location: ../users/other.php");
exit();
?>
