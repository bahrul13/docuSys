<?php
session_start();
require '../db/db_conn.php';
require "../function/log_handler.php";

// Ensure user_id exists for logging
$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $program = trim($_POST['documentName']);
    $file = $_FILES['file_name'];

    // ======= REQUIRED FIELDS VALIDATION =======
    if (empty($program) || empty($file['name'])) {
        $_SESSION['flash'] = "❌ Document name and file are required.";
        header("Location: ../users/other.php");
        exit();
    }

    // ======= FILE TYPE VALIDATION =======
    $allowedTypes = ['application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        $_SESSION['flash'] = "❌ Only PDF files are allowed.";
        header("Location: ../users/other.php");
        exit();
    }

    $originalFileName = basename($file['name']); // original file name

    // ======= DUPLICATE DOCUMENT NAME VALIDATION =======
    $checkDoc = $conn->prepare("SELECT id FROM documents WHERE document = ?");
    $checkDoc->bind_param("s", $program);
    $checkDoc->execute();
    $checkDoc->store_result();

    if ($checkDoc->num_rows > 0) {
        $_SESSION['flash'] = "❌ Document name already exists. Please choose a different name.";
        $checkDoc->close();
        header("Location: ../users/other.php");
        exit();
    }
    $checkDoc->close();

    // ======= DUPLICATE FILE NAME VALIDATION =======
    $checkFile = $conn->prepare("SELECT id FROM documents WHERE file_name = ?");
    $checkFile->bind_param("s", $originalFileName);
    $checkFile->execute();
    $checkFile->store_result();

    if ($checkFile->num_rows > 0) {
        $_SESSION['flash'] = "❌ A file with the same name already exists. Please rename your file.";
        $checkFile->close();
        header("Location: ../users/other.php");
        exit();
    }
    $checkFile->close();

    // ======= UPLOAD DIRECTORY =======
    $uploadDir = '../uploads/other/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // ======= STORE FILE WITH ORIGINAL NAME =======
    $uploadPath = $uploadDir . $originalFileName;

    // ======= MOVE FILE =======
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {

        // ======= INSERT INTO DATABASE =======
        $stmt = $conn->prepare("INSERT INTO documents (document, file_name) VALUES (?, ?)");
        $stmt->bind_param("ss", $program, $originalFileName);

        if ($stmt->execute()) {

            $_SESSION['flash'] = "✅ Document uploaded successfully.";

            // ======= LOG ACTION =======
            $newRecordId = $stmt->insert_id;
            logAction(
                $conn,
                $user_id,
                'documents',
                $newRecordId,
                'Add Accreditation-Related Document',
                "Uploaded Accreditation-Related document: $program"
            );

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
