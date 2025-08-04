<?php
// delete.php
require '../db/db_conn.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Get file name to delete from uploads folder
    $stmt = $conn->prepare("SELECT file_name FROM copc WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $filePath = '../uploads/copc/' . $row['file_name'];

        // Delete from DB
        $stmt = $conn->prepare("DELETE FROM copc WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}

header("Location: ../users/copc.php"); // Replace with your actual listing page
exit();
