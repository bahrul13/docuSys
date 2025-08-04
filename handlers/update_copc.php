<?php
session_start();
require '../db/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $program = $_POST['program'];
    $issuance_date = $_POST['issuance_date'];

    if (isset($_FILES['file_name']) && $_FILES['file_name']['error'] === 0) {
        $fileName = basename($_FILES['file_name']['name']);
        $targetDir = "../uploads/copc/";
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['file_name']['tmp_name'], $targetFile)) {
            $stmt = $conn->prepare("UPDATE copc SET program = ?, issuance_date = ?, file_name = ? WHERE id = ?");
            $stmt->bind_param("sssi", $program, $issuance_date, $fileName, $id);
        } else {
            die("File upload failed.");
        }
    } else {
        $stmt = $conn->prepare("UPDATE copc SET program = ?, issuance_date = ? WHERE id = ?");
        $stmt->bind_param("ssi", $program, $issuance_date, $id);
    }

    if ($stmt->execute()) {
        header("Location: ../users/copc.php?updated=0");
        exit();
    } else {
        echo "Update failed.";
    }
}
?>
