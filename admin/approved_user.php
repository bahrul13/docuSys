<?php
session_start();

require_once __DIR__ . '/../db/db_conn.php';
require_once __DIR__ . '/../function/csrf.php';
require_once __DIR__ . '/../function/log_handler.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

// must be logged in + admin (recommended)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['flash'] = "Access denied.";
    header("Location: ../admin/pending_user.php");
    exit();
}

// POST only (approve changes DB)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = "⚠️ Invalid request.";
    header("Location: ../admin/pending_user.php");
    exit();
}

csrf_verify();

//  Get id from POST, not GET
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash'] = "⚠️ Invalid user ID.";
    header("Location: ../admin/pending_user.php");
    exit();
}

// Get user's email and fullname first
$stmt = $conn->prepare("SELECT fullname, email FROM user WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $fullname = $user['fullname'];
    $email = $user['email'];

    // Update status to approved
    $updateStmt = $conn->prepare("UPDATE user SET status = 'approved' WHERE id = ?");
    $updateStmt->bind_param("i", $id);
    $updateStmt->execute();
    $updateStmt->close();

    //  Log action
    logAction(
        $conn,
        $_SESSION['user_id'], // ✅ fix: use user_id
        'user',
        $id,
        'approve',
        "Admin approved user registration: {$fullname}"
    );

    //  Send email notification
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply.qmso2026@gmail.com';
        $mail->Password   = 'kpvubjlvhwtpxyvt';
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

} else {
    $_SESSION['flash'] = "⚠️ User not found.";
}

$stmt->close();

header("Location: ../admin/pending_user.php");
exit();
