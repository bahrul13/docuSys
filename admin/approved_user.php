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
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';        // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply.cotsu.qmso@gmail.com';  // Your SMTP email
        $mail->Password   = 'ebmr qpoq efic byyl';   // App password if Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('noreply.cotsu.qmso@gmail.com', 'DocuSys Support');
        $mail->addAddress($email, $fullname);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Registration Approved';
        $mail->Body    = "
            <p>Hi <b>{$fullname}</b>,</p>
            <p>Congratulations! Your registration has been <b>approved</b> by the admin.</p>
            <p>You can now log in using your registered email and password.</p>
            <br>
            <p>Best regards,<br>DocuSys Support</p>
        ";

        $mail->send();
        $_SESSION['message'] = "User approved and email sent successfully.";
    } catch (Exception $e) {
        $_SESSION['message'] = "User approved but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

header("Location: ../admin/pending_user.php");
exit();
