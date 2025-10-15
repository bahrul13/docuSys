<?php
session_start();
require '../db/db_conn.php';

// Check if admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../users/other.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $document = $_POST['document'];

    if (isset($_FILES['file_name']) && $_FILES['file_name']['error'] === 0) {
        $fileName = basename($_FILES['file_name']['name']);
        $targetDir = "../uploads/other/";
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['file_name']['tmp_name'], $targetFile)) {
            $stmt = $conn->prepare("UPDATE documents SET document = ?, file_name = ? WHERE id = ?");
            $stmt->bind_param("ssi", $document,  $fileName, $id);
        } else {
            $_SESSION['flash'] = "❌ File upload failed.";
            header("Location: ../users/other.php");
            exit();
        }
    } else {
        $stmt = $conn->prepare("UPDATE documents SET document = ? WHERE id = ?");
        $stmt->bind_param("si", $document, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['flash'] = "✅ Document record updated successfully.";
    } else {
        $_SESSION['flash'] = "❌ Failed to update Document record.";
    }

    $stmt->close();
    $conn->close();
    
    header("Location: ../users/other.php");
    exit();
} else {
    $_SESSION['flash'] = "⚠️ Invalid request method.";
    header("Location: ../users/other.php");
    exit();
}
?>
