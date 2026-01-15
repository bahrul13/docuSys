<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
date_default_timezone_set('Asia/Manila');

$showModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require '../db/db_conn.php';

    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        // Don’t die() in production—use flash or modal
        $showModal = true;
    } else {

        // Always respond the same to avoid user enumeration
        $genericSuccess = true;

        // Check if user exists
        $stmt = $conn->prepare("SELECT id FROM user WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            // ✅ Generate reset token (store HASH in DB, email the raw token)
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);

            // ✅ 1 hour expiry
            $expiry = date("Y-m-d H:i:s", time() + 3600);

            $update = $conn->prepare("UPDATE user SET reset_token = ?, reset_expiry = ? WHERE email = ?");
            $update->bind_param("sss", $tokenHash, $expiry, $email);
            $update->execute();
            $update->close();

            // ✅ Reset link (URL encode token)
            $resetLink = "http://localhost:3000/includes/reset_password.php?token=" . urlencode($token);

            // Send email via PHPMailer
            $mail = new PHPMailer(true);

            try {
                $mail->SMTPDebug = 0;
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;

                // ✅ Use environment variables (set these in Apache/Nginx/.env)
                $mail->Username   = getenv('SMTP_USER'); // e.g. noreply.qmso2026@gmail.com
                $mail->Password   = getenv('SMTP_PASS'); // Gmail App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom($mail->Username, 'DocuSys Support');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "
                    <p>Hello,</p>
                    <p>We received a request to reset your password.</p>
                    <p><a href='{$resetLink}'>Click here to reset your password</a></p>
                    <p>This link will expire in <b>1 hour</b>.</p>
                    <br>
                    <p>- DocuSys Team</p>
                ";

                $mail->send();
            } catch (Exception $e) {
                // Log but don’t reveal error to users
                error_log("Mailer Error: " . $mail->ErrorInfo);
            }
        }

        // Always show success modal even if email not found
        $showModal = true;
    }

    // Optional: no need to manually close $conn (PHP auto closes)
    // $conn->close();
}
?>
