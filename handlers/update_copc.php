<?php
session_start();
require '../db/db_conn.php';
require '../function/log_handler.php';

// Get logged-in user ID
$user_id = $_SESSION['user_id'] ?? null;

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/copc.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/copc.php");
    exit();
}

$id            = (int)($_POST['id'] ?? 0);
$program       = trim($_POST['program'] ?? '');
$issuance_date = $_POST['issuance_date'] ?? '';

if ($id <= 0 || $program === '' || $issuance_date === '') {
    $_SESSION['flash'] = "❌ Missing required fields.";
    header("Location: ../users/copc.php");
    exit();
}

// ✅ Get current file (so we can delete it if replaced)
$currentStmt = $conn->prepare("SELECT file_name FROM copc WHERE id = ?");
$currentStmt->bind_param("i", $id);
$currentStmt->execute();
$currentRes = $currentStmt->get_result();
$currentRow = $currentRes->fetch_assoc();
$currentStmt->close();

if (!$currentRow) {
    $_SESSION['flash'] = "⚠️ COPC record not found.";
    header("Location: ../users/copc.php");
    exit();
}

$oldFileName = $currentRow['file_name'] ?? '';
$uploadDir   = "../uploads/copc/";

// If new file uploaded
$newFileUploaded = (isset($_FILES['file_name']) && $_FILES['file_name']['error'] === UPLOAD_ERR_OK);

if ($newFileUploaded) {

    // ✅ Real PDF check
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $_FILES['file_name']['tmp_name']);
    finfo_close($finfo);

    if ($mime !== 'application/pdf') {
        $_SESSION['flash'] = "❌ Only PDF files are allowed.";
        header("Location: ../users/copc.php");
        exit();
    }

    // ✅ Safe filename
    $fileName = basename($_FILES['file_name']['name']);
    $fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $targetFile = $uploadDir . $fileName;

    // Optional: block duplicate file names
    // (If you want to allow overwrite, remove this)
    if (file_exists($targetFile) && $fileName !== $oldFileName) {
        $_SESSION['flash'] = "❌ A file with the same name already exists. Please rename your file.";
        header("Location: ../users/copc.php");
        exit();
    }

    if (!move_uploaded_file($_FILES['file_name']['tmp_name'], $targetFile)) {
        $_SESSION['flash'] = "❌ File upload failed.";
        header("Location: ../users/copc.php");
        exit();
    }

    // ✅ Update DB with new file
    $stmt = $conn->prepare("UPDATE copc SET program = ?, issuance_date = ?, file_name = ? WHERE id = ?");
    $stmt->bind_param("sssi", $program, $issuance_date, $fileName, $id);

} else {
    // ✅ Update DB without changing file
    $stmt = $conn->prepare("UPDATE copc SET program = ?, issuance_date = ? WHERE id = ?");
    $stmt->bind_param("ssi", $program, $issuance_date, $id);
}

if ($stmt->execute()) {

    // ✅ Delete old file if replaced (and different name)
    if ($newFileUploaded && !empty($oldFileName) && isset($fileName) && $fileName !== $oldFileName) {
        $oldPath = $uploadDir . $oldFileName;
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }

    $_SESSION['flash'] = "✅ COPC record updated successfully.";

    // ✅ Log message
    $logMessage = "Updated COPC record: $program";
    if ($newFileUploaded && isset($fileName)) {
        $logMessage .= " (Replaced file with: $fileName)";
    }

    logAction($conn, $user_id, 'copc', $id, 'Update COPC', $logMessage);

} else {

    // If DB update failed but we uploaded a new file, delete it to avoid orphan files
    if ($newFileUploaded && isset($fileName)) {
        $newPath = $uploadDir . $fileName;
        if (file_exists($newPath)) {
            unlink($newPath);
        }
    }

    $_SESSION['flash'] = "❌ Failed to update COPC record.";
}

$stmt->close();

// ❌ REMOVE this — PHP auto closes DB connection
// $conn->close();

header("Location: ../users/copc.php");
exit();
?>
