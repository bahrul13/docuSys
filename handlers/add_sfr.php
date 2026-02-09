<?php
session_start();
require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/log_handler.php';

// ✅ Must be logged in (for logs + security)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
$user_id = (int)$_SESSION['user_id'];

// ✅ Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/sfr.php");
    exit();
}

// ✅ CSRF verify
csrf_verify();

// Inputs
$programName = trim($_POST['program_name'] ?? '');
$surveyType  = trim($_POST['survey_type'] ?? '');
$surveyDate  = trim($_POST['survey_date'] ?? '');
$file        = $_FILES['file_name'] ?? null;

// ======= REQUIRED FIELDS VALIDATION =======
if ($programName === '' || $surveyType === '' || $surveyDate === '' || empty($file) || empty($file['name'])) {
    $_SESSION['flash'] = "❌ All fields are required.";
    header("Location: ../users/sfr.php");
    exit();
}

// ======= UPLOAD ERROR CHECK =======
if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['flash'] = "❌ File upload failed. Error code: " . ($file['error'] ?? 'unknown');
    header("Location: ../users/sfr.php");
    exit();
}

// ======= REAL MIME CHECK (SECURE) =======
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if ($mime !== 'application/pdf') {
    $_SESSION['flash'] = "❌ Only PDF files are allowed.";
    header("Location: ../users/sfr.php");
    exit();
}

// ======= SAFE FILE NAME =======
$originalFileName = basename($file['name']);
$originalFileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalFileName);

// ======= DUPLICATE PROGRAM VALIDATION =======
$checkDoc = $conn->prepare("SELECT id FROM sfr WHERE program_name = ? LIMIT 1");
$checkDoc->bind_param("s", $programName);
$checkDoc->execute();
$checkDoc->store_result();

if ($checkDoc->num_rows > 0) {
    $checkDoc->close();
    $_SESSION['flash'] = "❌ A document with the same program name already exists.";
    header("Location: ../users/sfr.php");
    exit();
}
$checkDoc->close();

// ======= DUPLICATE FILE NAME VALIDATION =======
$checkFile = $conn->prepare("SELECT id FROM sfr WHERE file_name = ? LIMIT 1");
$checkFile->bind_param("s", $originalFileName);
$checkFile->execute();
$checkFile->store_result();

if ($checkFile->num_rows > 0) {
    $checkFile->close();
    $_SESSION['flash'] = "❌ A file with the same name already exists. Please rename your file.";
    header("Location: ../users/sfr.php");
    exit();
}
$checkFile->close();

// ======= UPLOAD DIRECTORY =======
$uploadDir = __DIR__ . '/../uploads/sfr/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ======= MOVE FILE =======
$uploadPath = $uploadDir . $originalFileName;

if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    $_SESSION['flash'] = "❌ File upload failed.";
    header("Location: ../users/sfr.php");
    exit();
}

// ======= INSERT INTO DATABASE =======
$stmt = $conn->prepare("INSERT INTO sfr (program_name, survey_type, survey_date, file_name) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $programName, $surveyType, $surveyDate, $originalFileName);

if ($stmt->execute()) {
    $newRecordId = (int)$stmt->insert_id;

    logAction(
        $conn,
        $user_id,
        'sfr',
        $newRecordId,
        'Add SFR',
        "Added SFR document for program: $programName"
    );

    $_SESSION['flash'] = "✅ Document uploaded successfully.";
} else {
    // If DB fails, remove uploaded file (avoid orphan file)
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    $_SESSION['flash'] = "❌ Database error: Failed to save document.";
}

$stmt->close();

header("Location: ../users/sfr.php");
exit();
?>
