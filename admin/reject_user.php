<?php
session_start();
require "../db/db_conn.php";
require "../function/log_handler.php";

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // make sure PHPMailer is installed via Composer

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

    // 2️⃣ Update status to rejected
    $updateStmt = $conn->prepare("UPDATE user SET status = 'rejected' WHERE id = ?");
    $updateStmt->bind_param("i", $id);
    $updateStmt->execute();

    // 3️⃣ Log action
    logAction(
        $conn,
        $_SESSION['id'],
        'user',
        $id,
        'reject',
        'Admin rejected user registration'
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
        $mail->Subject = 'Registration Rejected';
        $mail->Body    = "
            <p>Hi <b>{$fullname}</b>,</p>
            <p>We regret to inform you that your registration has been <b>rejected</b> by the admin.</p>
            <p>If you believe this was a mistake, please contact support.</p>
            <br>
            <p>Best regards,<br>Admin Team</p>
        ";

        $mail->send();
        // Optional: set a session message for success
        $_SESSION['message'] = "User rejected and email sent successfully.";
    } catch (Exception $e) {
        $_SESSION['message'] = "User rejected but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

header("Location: ../admin/pending_user.php");
exit();
