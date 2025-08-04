<?php
session_start();
require '../db/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program = $_POST['program'];
    $issuance_date = $_POST['issuance_date'];
    $file = $_FILES['file_name'];

    // Validate file type
    if (!isset($file) || $file['type'] !== 'application/pdf') {
        die("Only PDF files are allowed.");
    }

    // Create the uploads/copc directory if it doesn't exist
    $uploadDir = '../uploads/copc/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique file name to avoid conflicts
    $fileName = uniqid() . '-' . basename($file['name']);
    $uploadPath = $uploadDir . $fileName;

    // Move uploaded file to uploads/copc folder
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Insert file info into database
        $stmt = $conn->prepare("INSERT INTO copc (program, issuance_date, file_name) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $program, $issuance_date, $fileName);
        $stmt->execute();

        $_SESSION['upload_success'] = "Document uploaded successfully!";
    } else {
        $_SESSION['upload_error'] = "Upload failed.";
    }

    header("Location: ../users/copc.php");
    exit();
}
?>
