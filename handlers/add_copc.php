<?php
session_start();
require '../db/db_conn.php';
require "../function/log_handler.php";

// Ensure user_id exists for logging
$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/copc.php");
    exit();
}

$program       = trim($_POST['program'] ?? '');
$issuance_date = $_POST['issuance_date'] ?? '';
$file          = $_FILES['file_name'] ?? null;

// ======= REQUIRED FIELDS VALIDATION =======
if (empty($program) || empty($issuance_date) || empty($file) || empty($file['name'])) {
    $_SESSION['flash'] = "❌ All fields are required.";
    header("Location: ../users/copc.php");
    exit();
}

// ======= UPLOAD ERROR CHECK =======
if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['flash'] = "❌ File upload failed. Error code: " . ($file['error'] ?? 'unknown');
    header("Location: ../users/copc.php");
    exit();
}

// ======= FILE TYPE VALIDATION (REAL MIME CHECK) =======
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if ($mime !== 'application/pdf') {
    $_SESSION['flash'] = "❌ Only PDF files are allowed.";
    header("Location: ../users/copc.php");
    exit();
}

// ======= SAFE ORIGINAL FILE NAME =======
$originalFileName = basename($file['name']);
// optional sanitize for Windows + safety
$originalFileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalFileName);

// ======= DUPLICATE PROGRAM VALIDATION =======
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
$fileName   = $originalFileName;
$uploadPath = $uploadDir . $fileName;

// ======= MOVE FILE =======
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    $_SESSION['flash'] = "❌ File upload failed.";
    header("Location: ../users/copc.php");
    exit();
}

// ======= INSERT INTO DATABASE =======
$stmt = $conn->prepare("INSERT INTO copc (program, issuance_date, file_name) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $program, $issuance_date, $fileName);

if ($stmt->execute()) {

    $newRecordId = $stmt->insert_id;

    // ✅ LOG ACTION
    logAction(
        $conn,
        $user_id,
        'copc',
        (int)$newRecordId,
        'Add COPC',
        "Added COPC document for program: $program (File: $fileName)"
    );

    $_SESSION['flash'] = "✅ Document uploaded successfully.";

} else {
    // If DB insert fails, remove the uploaded file to avoid orphan files
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    $_SESSION['flash'] = "❌ Database error: Failed to save document.";
}

$stmt->close();

// ❌ REMOVE this — PHP auto closes DB connection
// $conn->close();

header("Location: ../users/copc.php");
exit();
?>
