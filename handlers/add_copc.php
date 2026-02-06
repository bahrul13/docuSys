<?php
session_start();
require '../db/db_conn.php';
require "../function/log_handler.php";
require "../function/csrf.php";

// ✅ Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Ensure user_id exists for logging
$user_id = (int)$_SESSION['user_id'];

// ✅ POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/copc.php");
    exit();
}

// ✅ CSRF check
csrf_verify();

$program       = trim($_POST['program'] ?? '');
$issuance_date = trim($_POST['issuance_date'] ?? '');
$file          = $_FILES['file_name'] ?? null;

// ======= REQUIRED FIELDS VALIDATION =======
if ($program === '' || $issuance_date === '' || empty($file) || empty($file['name'])) {
    $_SESSION['flash'] = "❌ All fields are required.";
    header("Location: ../users/copc.php");
    exit();
}

// ======= DATE FORMAT CHECK (YYYY-MM-DD) =======
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $issuance_date)) {
    $_SESSION['flash'] = "❌ Invalid issuance date format.";
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
$originalFileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalFileName);

// ✅ Unique stored filename to prevent collisions
$fileName = time() . "_" . $originalFileName;

// ======= DUPLICATE PROGRAM VALIDATION =======
$checkDoc = $conn->prepare("SELECT id FROM copc WHERE program = ? LIMIT 1");
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

// ======= DUPLICATE FILE NAME VALIDATION (stored name) =======
$checkFile = $conn->prepare("SELECT id FROM copc WHERE file_name = ? LIMIT 1");
$checkFile->bind_param("s", $fileName);
$checkFile->execute();
$checkFile->store_result();

if ($checkFile->num_rows > 0) {
    $_SESSION['flash'] = "❌ A file with the same name already exists. Please try again.";
    $checkFile->close();
    header("Location: ../users/copc.php");
    exit();
}
$checkFile->close();

// ======= CREATE UPLOAD DIRECTORY IF NOT EXISTS =======
$uploadDir = '../uploads/copc/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// ======= MOVE FILE =======
$uploadPath = $uploadDir . $fileName;

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
        "Added COPC document for program: $program"
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

header("Location: ../users/copc.php");
exit();
?>
