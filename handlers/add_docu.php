<?php
session_start();
require '../db/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program = $_POST['documentName'];
    $file = $_FILES['file_name'];

    // Validate file type
    if (!isset($file) || $file['type'] !== 'application/pdf') {
        $_SESSION['flash'] = "❌ Only PDF files are allowed.";
        header("Location: ../users/other.php");
        exit();
    }

    // Create the uploads/copc directory if it doesn't exist
    $uploadDir = '../uploads/other/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique file name to avoid conflicts
    $fileName = uniqid() . '-' . basename($file['name']);
    $uploadPath = $uploadDir . $fileName;

    // Move uploaded file to uploads/copc folder
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Insert file info into database
        $stmt = $conn->prepare("INSERT INTO documents (document, file_name) VALUES ( ?, ?)");
        $stmt->bind_param("ss", $program, $fileName);

        if ($stmt->execute()) {
            $_SESSION['flash'] = "✅ Document uploaded successfully.";
        } else {
            $_SESSION['flash'] = "❌ Database error: Failed to save document.";
        }

        $stmt->close();
    } else {
        $_SESSION['flash'] = "❌ File upload failed.";
    }

    $conn->close();
    header("Location: ../users/other.php");
    exit();
} else {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/other.php");
    exit();
}
?>
