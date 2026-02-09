<?php
session_start();

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/log_handler.php';

// ✅ Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// ✅ Admin only
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/copc.php");
    exit();
}

// ✅ Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/copc.php");
    exit();
}

// ✅ CSRF verify
csrf_verify();

$id            = (int)($_POST['id'] ?? 0);
$program       = trim($_POST['program'] ?? '');
$issuance_date = $_POST['issuance_date'] ?? '';

if ($id <= 0 || $program === '' || $issuance_date === '') {
    $_SESSION['flash'] = "❌ Missing required fields.";
    header("Location: ../users/copc.php");
    exit();
}

// ✅ Get current file
$currentStmt = $conn->prepare("SELECT file_name FROM copc WHERE id = ? LIMIT 1");
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
$uploadDir   = __DIR__ . "/../uploads/copc/";

// ✅ Optional: prevent duplicate program name (exclude current record)
$dupProg = $conn->prepare("SELECT id FROM copc WHERE program = ? AND id != ? LIMIT 1");
$dupProg->bind_param("si", $program, $id);
$dupProg->execute();
$dupProg->store_result();
if ($dupProg->num_rows > 0) {
    $dupProg->close();
    $_SESSION['flash'] = "❌ Program name already exists. Please use another program name.";
    header("Location: ../views/update_copc_page.php?id=" . $id);
    exit();
}
$dupProg->close();

// ✅ New file uploaded?
$newFileUploaded = (isset($_FILES['file_name']) && $_FILES['file_name']['error'] === UPLOAD_ERR_OK);

$fileName = null; // for later use

if ($newFileUploaded) {

    // ✅ Real MIME check
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $_FILES['file_name']['tmp_name']);
    finfo_close($finfo);

    if ($mime !== 'application/pdf') {
        $_SESSION['flash'] = "❌ Only PDF files are allowed.";
        header("Location: ../views/update_copc_page.php?id=" . $id);
        exit();
    }

    // ✅ Safe filename
    $fileName = basename($_FILES['file_name']['name']);
    $fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName);

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $targetFile = $uploadDir . $fileName;

    // ✅ Prevent another record using same file_name (exclude current record)
    $dupFile = $conn->prepare("SELECT id FROM copc WHERE file_name = ? AND id != ? LIMIT 1");
    $dupFile->bind_param("si", $fileName, $id);
    $dupFile->execute();
    $dupFile->store_result();
    if ($dupFile->num_rows > 0) {
        $dupFile->close();
        $_SESSION['flash'] = "❌ A file with the same name already exists in records. Please rename your file.";
        header("Location: ../views/update_copc_page.php?id=" . $id);
        exit();
    }
    $dupFile->close();

    // ✅ Move upload
    if (!move_uploaded_file($_FILES['file_name']['tmp_name'], $targetFile)) {
        $_SESSION['flash'] = "❌ File upload failed.";
        header("Location: ../views/update_copc_page.php?id=" . $id);
        exit();
    }

    // ✅ Update DB with new file
    $stmt = $conn->prepare("UPDATE copc SET program = ?, issuance_date = ?, file_name = ? WHERE id = ?");
    $stmt->bind_param("sssi", $program, $issuance_date, $fileName, $id);

} else {

    // ✅ Update DB without file change
    $stmt = $conn->prepare("UPDATE copc SET program = ?, issuance_date = ? WHERE id = ?");
    $stmt->bind_param("ssi", $program, $issuance_date, $id);
}

if ($stmt->execute()) {

    // ✅ Delete old file if replaced
    if ($newFileUploaded && !empty($oldFileName) && $fileName && $fileName !== $oldFileName) {
        $oldPath = $uploadDir . $oldFileName;
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }

    $_SESSION['flash'] = "✅ COPC record updated successfully.";

    // ✅ Log
    $logMessage = "Updated COPC record: {$program}";
    if ($newFileUploaded && $fileName) {
        $logMessage .= " (Replaced file with: {$fileName})";
    }

    logAction($conn, $user_id, 'copc', $id, 'Update COPC', $logMessage);

} else {

    // ✅ If DB update failed but file uploaded, remove it
    if ($newFileUploaded && $fileName) {
        $newPath = $uploadDir . $fileName;
        if (file_exists($newPath)) {
            unlink($newPath);
        }
    }

    $_SESSION['flash'] = "❌ Failed to update COPC record.";
}

$stmt->close();

header("Location: ../users/copc.php");
exit();
?>
