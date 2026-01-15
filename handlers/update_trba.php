<?php
session_start();
require '../db/db_conn.php';
require '../function/log_handler.php';

// Get logged-in user ID
$user_id = $_SESSION['user_id'] ?? null;

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/trba.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/trba.php");
    exit();
}

$id         = (int)($_POST['id'] ?? 0);
$name       = trim($_POST['program_name'] ?? '');
$surveyType = $_POST['survey_type'] ?? '';
$surveyDate = $_POST['survey_date'] ?? '';

if ($id <= 0 || $name === '' || $surveyType === '' || $surveyDate === '') {
    $_SESSION['flash'] = "❌ Missing required fields.";
    header("Location: ../users/trba.php");
    exit();
}

// ✅ Get current file name (for delete if replaced)
$currentStmt = $conn->prepare("SELECT file_name FROM trba WHERE id = ?");
$currentStmt->bind_param("i", $id);
$currentStmt->execute();
$currentRes = $currentStmt->get_result();
$currentRow = $currentRes->fetch_assoc();
$currentStmt->close();

if (!$currentRow) {
    $_SESSION['flash'] = "⚠️ TRBA record not found.";
    header("Location: ../users/trba.php");
    exit();
}

$oldFileName = $currentRow['file_name'] ?? '';
$uploadDir   = "../uploads/trba/";

$newFileUploaded = (isset($_FILES['file_name']) && $_FILES['file_name']['error'] === UPLOAD_ERR_OK);

if ($newFileUploaded) {

    // ✅ Real PDF check
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $_FILES['file_name']['tmp_name']);
    finfo_close($finfo);

    if ($mime !== 'application/pdf') {
        $_SESSION['flash'] = "❌ Only PDF files are allowed.";
        header("Location: ../users/trba.php");
        exit();
    }

    // ✅ Safe filename
    $fileName = basename($_FILES['file_name']['name']);
    $fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $targetFile = $uploadDir . $fileName;

    // Optional: prevent duplicate file name (unless it's the same old file)
    if (file_exists($targetFile) && $fileName !== $oldFileName) {
        $_SESSION['flash'] = "❌ A file with the same name already exists. Please rename your file.";
        header("Location: ../users/trba.php");
        exit();
    }

    if (!move_uploaded_file($_FILES['file_name']['tmp_name'], $targetFile)) {
        $_SESSION['flash'] = "❌ File upload failed.";
        header("Location: ../users/trba.php");
        exit();
    }

    // Update with new file
    $stmt = $conn->prepare("
        UPDATE trba
        SET program_name = ?, survey_type = ?, survey_date = ?, file_name = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssssi", $name, $surveyType, $surveyDate, $fileName, $id);

} else {

    // Update without file change
    $stmt = $conn->prepare("
        UPDATE trba
        SET program_name = ?, survey_type = ?, survey_date = ?
        WHERE id = ?
    ");
    $stmt->bind_param("sssi", $name, $surveyType, $surveyDate, $id);
}

if ($stmt->execute()) {

    // ✅ Delete old file if replaced
    if ($newFileUploaded && !empty($oldFileName) && isset($fileName) && $fileName !== $oldFileName) {
        $oldPath = $uploadDir . $oldFileName;
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }

    $_SESSION['flash'] = "✅ TRBA record updated successfully.";

    $logMessage = "Updated TRBA record: $name";
    if ($newFileUploaded && isset($fileName)) {
        $logMessage .= " (Replaced file with: $fileName)";
    }

    logAction($conn, $user_id, 'trba', $id, 'Update TRBA', $logMessage);

} else {

    // If DB update failed but we uploaded a new file, delete it to avoid orphan files
    if ($newFileUploaded && isset($fileName)) {
        $newPath = $uploadDir . $fileName;
        if (file_exists($newPath)) {
            unlink($newPath);
        }
    }

    $_SESSION['flash'] = "❌ Failed to update TRBA record.";
}

$stmt->close();

// ❌ REMOVE this — PHP auto closes DB connection
// $conn->close();

header("Location: ../users/trba.php");
exit();
?>
