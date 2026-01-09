<?php
session_start();
require "../db/db_conn.php";
require "../function/log_handler.php";

// Admin only
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

$admin_id = $_SESSION['user_id'] ?? null;
$id = intval($_GET['id'] ?? 0);

// 1️⃣ Get user info (ONLY if still pending)
$stmt = $conn->prepare("
    SELECT fullname, email 
    FROM user 
    WHERE id = ? AND status = 'pending'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $_SESSION['flash'] = "⚠️ User not found or already processed.";
    header("Location: ../admin/pending_user.php");
    exit();
}

$user = $result->fetch_assoc();
$fullname = $user['fullname'];
$email = $user['email'];
$stmt->close();

/* ================= EMAIL FIRST ================= */
$mail = new PHPMailer(true);

try {
    // 🔍 Debugging (set to 2 only if needed)
    $mail->SMTPDebug = 0;

    // 📧 SMTP settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'noreply.qmso2026@gmail.com';
    $mail->Password   = 'kpvubjlvhwtpxyvt';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // 👤 Sender & Recipient
    $mail->setFrom('noreply.qmso2026@gmail.com', 'DocuSys Support');
    $mail->addAddress($email, $fullname);

    // ✉️ Email Content
    $mail->isHTML(true);
    $mail->Subject = 'Registration Rejected';
    $mail->Body = "
        <p>Hi <b>{$fullname}</b>,</p>
        <p>We regret to inform you that your registration has been <b>rejected</b>.</p>
        <p>If you believe this was a mistake, please contact the system administrator.</p>
        <br>
        <p>DocuSys Support</p>
    ";

    // 🚀 Send Email
    $mail->send();

    $_SESSION['message'] = "User rejected and email sent successfully.";

} catch (Exception $e) {
    $_SESSION['message'] = "Email failed: " . $mail->ErrorInfo;
}

/* ================= DELETE USER ================= */
$deleteStmt = $conn->prepare("DELETE FROM user WHERE id = ?");
$deleteStmt->bind_param("i", $id);

if ($deleteStmt->execute()) {

    logAction(
        $conn,
        $admin_id,
        'user',
        $id,
        'Rejected User',
        "Admin rejected: {$fullname}"
    );

    $_SESSION['flash'] = "✅ User rejected, notified, and deleted successfully.";

} else {
    $_SESSION['flash'] = "❌ Failed to delete rejected user.";
}

$deleteStmt->close();
$conn->close();

header("Location: ../admin/pending_user.php");
exit();
