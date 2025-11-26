<?php
session_start();
require '../db/db_conn.php';
require '../function/log_handler.php';

// Get logged-in user ID
$user_id = $_SESSION['user_id'] ?? null;

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/other.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $document = $_POST['document'];

    if (isset($_FILES['file_name']) && $_FILES['file_name']['error'] === 0) {
        $fileName = basename($_FILES['file_name']['name']);
        $targetDir = "../uploads/other/";
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['file_name']['tmp_name'], $targetFile)) {
            $stmt = $conn->prepare("UPDATE documents SET document = ?, file_name = ? WHERE id = ?");
            $stmt->bind_param("ssi", $document,  $fileName, $id);
        } else {
            $_SESSION['flash'] = "❌ File upload failed.";
            header("Location: ../users/other.php");
            exit();
        }
    } else {
        $stmt = $conn->prepare("UPDATE documents SET document = ? WHERE id = ?");
        $stmt->bind_param("si", $document, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['flash'] = "✅ Document record updated successfully.";

        // ✅ Log the update action
        $logMessage = "Updated Document record: $document.";
        if (isset($fileName)) {
            $logMessage .= " ";
        }

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

        logAction($conn, $user_id, 'documents', $id, 'Update Document', $logMessage);

    } else {
        $_SESSION['flash'] = "❌ Failed to update Document record.";
    }

    $stmt->close();
    $conn->close();
    
    header("Location: ../users/other.php");
    exit();
} else {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/other.php");
    exit();
}
?>
