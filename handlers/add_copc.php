<?php
session_start();
require '../db/db_conn.php';
require "../function/log_handler.php";

// Ensure user_id exists for logging
$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $program = trim($_POST['program']);
    $issuance_date = $_POST['issuance_date'];
    $file = $_FILES['file_name'];

    // ======= REQUIRED FIELDS VALIDATION =======
    if (empty($program) || empty($file['name']) || empty($issuance_date)) {
        $_SESSION['flash'] = "❌ All fields are required.";
        header("Location: ../users/copc.php"); //add_copc_page.php
        exit();
    }

    // ======= FILE TYPE VALIDATION =======
    $allowedTypes = ['application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        $_SESSION['flash'] = "❌ Only PDF files are allowed.";
        header("Location: ../users/copc.php");
        exit();
    }

    $originalFileName = basename($file['name']);

    // ======= DUPLICATE DOCUMENT NAME VALIDATION =======
    $checkDoc = $conn->prepare("SELECT id FROM copc WHERE program = ?");
    $checkDoc->bind_param("s", $program);
    $checkDoc->execute();
    $checkDoc->store_result();

    if ($checkDoc->num_rows > 0) {
        $_SESSION['flash'] = "❌ A document with the same program name already exists.";
        $checkDoc->close();
        header("Location: ../users/copc.php");
        exit();
    }
    $checkDoc->close();

    // ======= DUPLICATE FILE NAME VALIDATION =======
    $checkFile = $conn->prepare("SELECT id FROM copc WHERE file_name = ?");
    $checkFile->bind_param("s", $originalFileName);
    $checkFile->execute();
    $checkFile->store_result();

    if ($checkFile->num_rows > 0) {
        $_SESSION['flash'] = "❌ A file with the same name already exists. Please rename your file.";
        $checkFile->close();
        header("Location: ../users/copc.php");
        exit();
    }
    $checkFile->close();

    // ======= CREATE UPLOAD DIRECTORY IF NOT EXISTS =======
    $uploadDir = '../uploads/copc/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // ======= STORE FILE WITH ORIGINAL NAME =======
    $fileName = $originalFileName;
    $uploadPath = $uploadDir . $fileName;

    // ======= MOVE FILE =======
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {

        // ======= INSERT INTO DATABASE =======
        $stmt = $conn->prepare("INSERT INTO copc (program, issuance_date, file_name) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $program, $issuance_date, $fileName);

        if ($stmt->execute()) {
            $_SESSION['flash'] = "✅ Document uploaded successfully.";

            // ======= LOG ACTION =======
            $newRecordId = $stmt->insert_id;
            logAction(
                $conn,
                $user_id,
                'copc',
                $newRecordId,
                'Add COPC',
                "Added COPC document for program: $program"
            );

        } else {
            $_SESSION['flash'] = "❌ Database error: Failed to save document.";
        }

        $stmt->close();

    } else {
        $_SESSION['flash'] = "❌ File upload failed.";
    }

    $conn->close();
    header("Location: ../users/copc.php");
    exit();

} else {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/copc.php");
    exit();
}
?>
