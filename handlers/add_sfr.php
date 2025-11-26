<?php
session_start();
require '../db/db_conn.php';
require "../function/log_handler.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $programName = $_POST['program_name'];
    $surveyType = $_POST['survey_type'];
    $surveyDate = $_POST['survey_date'];
    $file = $_FILES['file_name'];

    // Validate file type
    if (!isset($file) || $file['type'] !== 'application/pdf') {
        $_SESSION['flash'] = "❌ Only PDF files are allowed.";
        header("Location: ../users/sfr.php");
        exit();
    }

    // Create the uploads/sfr directory if it doesn't exist
    $uploadDir = '../uploads/sfr/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique file name to avoid conflicts
    $fileName = uniqid() . '-' . basename($file['name']);
    $uploadPath = $uploadDir . $fileName;

    // Move uploaded file to uploads/sfr folder
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Insert file info into database
        $stmt = $conn->prepare("INSERT INTO sfr (program_name, survey_type, survey_date, file_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $programName, $surveyType, $surveyDate, $fileName);

        if ($stmt->execute()) {
            $_SESSION['flash'] = "✅ Document uploaded successfully.";

            // ✅ Log the action
            $newRecordId = $stmt->insert_id;
            logAction($conn, $user_id, 'sfr', $newRecordId, 'Add SFR', "Uploaded SFR document for program: $programName");

        } else {
            $_SESSION['flash'] = "❌ Database error: Failed to save document.";
        }

        $stmt->close();
    } else {
        $_SESSION['flash'] = "❌ File upload failed.";
    }

    $conn->close();
    header("Location: ../users/sfr.php");
    exit();
} else {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/sfr.php");
    exit();
}
?>
