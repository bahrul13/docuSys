<?php
session_start();
require '../db/db_conn.php';
require "../function/log_handler.php";

// Ensure user_id exists for logging
$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/trba.php");
    exit();
}

$programName = trim($_POST['program_name'] ?? '');
$surveyType  = $_POST['survey_type'] ?? '';
$surveyDate  = $_POST['survey_date'] ?? '';
$file        = $_FILES['file_name'] ?? null;

// ======= REQUIRED FIELDS VALIDATION =======
if (empty($programName) || empty($surveyType) || empty($surveyDate) || empty($file) || empty($file['name'])) {
    $_SESSION['flash'] = "❌ All fields are required.";
    header("Location: ../users/trba.php");
    exit();
}

// ======= UPLOAD ERROR CHECK =======
if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['flash'] = "❌ File upload failed. Error code: " . ($file['error'] ?? 'unknown');
    header("Location: ../users/trba.php");
    exit();
}

// ======= FILE TYPE VALIDATION (REAL MIME CHECK) =======
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if ($mime !== 'application/pdf') {
    $_SESSION['flash'] = "❌ Only PDF files are allowed.";
    header("Location: ../users/trba.php");
    exit();
}

// ======= SAFE FILE NAME =======
$originalFileName = basename($file['name']);
$originalFileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalFileName);

// ======= DUPLICATE PROGRAM VALIDATION =======
$checkDoc = $conn->prepare("SELECT id FROM trba WHERE program_name = ?");
$checkDoc->bind_param("s", $programName);
$checkDoc->execute();
$checkDoc->store_result();

if ($checkDoc->num_rows > 0) {
    $checkDoc->close();
    $_SESSION['flash'] = "❌ A document with the same program name already exists.";
    header("Location: ../users/trba.php");
    exit();
}
$checkDoc->close();

// ======= DUPLICATE FILE NAME VALIDATION =======
$checkFile = $conn->prepare("SELECT id FROM trba WHERE file_name = ?");
$checkFile->bind_param("s", $originalFileName);
$checkFile->execute();
$checkFile->store_result();

if ($checkFile->num_rows > 0) {
    $checkFile->close();
    $_SESSION['flash'] = "❌ A file with the same name already exists. Please rename your file.";
    header("Location: ../users/trba.php");
    exit();
}
$checkFile->close();

// ======= CREATE UPLOAD DIRECTORY IF NOT EXISTS =======
$uploadDir = '../uploads/trba/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ======= MOVE FILE =======
$uploadPath = $uploadDir . $originalFileName;

if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    $_SESSION['flash'] = "❌ File upload failed.";
    header("Location: ../users/trba.php");
    exit();
}

// ======= INSERT INTO DATABASE =======
$stmt = $conn->prepare("INSERT INTO trba (program_name, survey_type, survey_date, file_name) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $programName, $surveyType, $surveyDate, $originalFileName);

if ($stmt->execute()) {

    $newRecordId = $stmt->insert_id;

    // ======= LOG ACTION =======
    logAction(
        $conn,
        $user_id,
        'trba',
        (int)$newRecordId,
        'Add TRBA',
        "Added TRBA document for program: $programName"
    );

    $_SESSION['flash'] = "✅ Document uploaded successfully.";

} else {

    // If DB insert fails, remove uploaded file
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }

    $_SESSION['flash'] = "❌ Database error: Failed to save document.";
}

$stmt->close();

// ❌ REMOVE this — PHP auto closes DB connection
// $conn->close();

header("Location: ../users/trba.php");
exit();
?>
