<?php
session_start();
require '../db/db_conn.php';
require '../function/log_handler.php';

// ✅ Get logged-in user id (for logs)
$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    // Get file name to delete from uploads folder
    $stmt = $conn->prepare("SELECT file_name FROM copc WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $fileName = $row['file_name'];
        $filePath = '../uploads/copc/' . $fileName;

        // Delete from database
        $deleteStmt = $conn->prepare("DELETE FROM copc WHERE id = ?");
        $deleteStmt->bind_param("i", $id);

        if ($deleteStmt->execute()) {

            // Delete physical file if it exists
            if (!empty($fileName) && file_exists($filePath)) {
                unlink($filePath);
            }

            // ✅ Log the delete action
            logAction(
                $conn,
                $user_id,
                'copc',
                $id,
                'Delete COPC',
                "Deleted COPC document: {$fileName}"
            );

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

// ❌ Remove this line (PHP closes it automatically)
// $conn->close();

header("Location: ../users/copc.php");
exit();
?>
