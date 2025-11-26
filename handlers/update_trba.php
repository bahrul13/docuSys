<?php
session_start();
require '../db/db_conn.php';
require '../function/log_handler.php'; // <-- Make sure this file is correct

// Get logged-in user ID
$user_id = $_SESSION['user_id'] ?? null;

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/trba.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['program_name'];
    $survey_type = $_POST['survey_type'];
    $survey_date = $_POST['survey_date'];

    if (isset($_FILES['file_name']) && $_FILES['file_name']['error'] === 0) {
        $fileName = basename($_FILES['file_name']['name']);
        $targetDir = "../uploads/trba/";
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['file_name']['tmp_name'], $targetFile)) {
            $stmt = $conn->prepare("UPDATE trba SET program_name = ?, survey_type = ?, survey_date = ?, file_name = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $survey_type, $survey_date, $fileName, $id);
        } else {
            $_SESSION['flash'] = "❌ File upload failed.";
            header("Location: ../users/trba.php");
            exit();
        }
    } else {
        $stmt = $conn->prepare("UPDATE trba SET program_name = ?, survey_type = ?, survey_date = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $survey_type, $survey_date, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['flash'] = "✅ TRBA record updated successfully.";

         // ✅ Log the update action
        $logMessage = "Updated TRBA record: $name.";
        if (isset($fileName)) {
            $logMessage .= "";
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

        logAction($conn, $user_id, 'trba', $id, 'Update TRBA', $logMessage);

    } else {
        $_SESSION['flash'] = "❌ Failed to update TRBA record.";
    }

    $stmt->close();
    $conn->close();
    
    header("Location: ../users/trba.php");
    exit();
} else {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/trba.php");
    exit();
}
?>
