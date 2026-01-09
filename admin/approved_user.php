<?php
session_start();
require "../db/db_conn.php";
require "../function/log_handler.php";

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Make sure PHPMailer is installed via Composer

$id = $_GET['id'];

// 1️⃣ Get user's email and fullname first
$stmt = $conn->prepare("SELECT fullname, email FROM user WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $fullname = $user['fullname'];
    $email = $user['email'];

    // 2️⃣ Update status to approved
    $updateStmt = $conn->prepare("UPDATE user SET status = 'approved' WHERE id = ?");
    $updateStmt->bind_param("i", $id);
    $updateStmt->execute();

    // 3️⃣ Log action
    logAction(
        $conn,
        $_SESSION['id'],
        'user',
        $id,
        'approve',
        'Admin approved user registration'
    );

    // 4️⃣ Send email notification
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 0; // set to 2 for debugging
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply.qmso2026@gmail.com';
        $mail->Password   = 'kpvubjlvhwtpxyvt'; // NO SPACES
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('noreply.qmso2026@gmail.com', 'DocuSys Support');
        $mail->addAddress($email, $fullname);

        $mail->isHTML(true);
        $mail->Subject = 'Registration Approved';
        $mail->Body = "
            <p>Hi <b>{$fullname}</b>,</p>
            <p>Your registration has been <b>approved</b>.</p>
            <p>You may now log in.</p>
            <br>
            <p>DocuSys Support</p>
        ";

        $mail->send();
        $_SESSION['message'] = "User approved and email sent successfully.";

    } catch (Exception $e) {
        $_SESSION['message'] = "Email failed: " . $mail->ErrorInfo;
    }

}

header("Location: ../admin/pending_user.php");
exit();
