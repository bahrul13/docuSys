<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
date_default_timezone_set('Asia/Manila'); // Set PHP timezone

$showModal = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../db/db_conn.php';

    $email = trim($_POST['email']);
    if (empty($email)) {
        die("Email is required.");
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "No account found with that email.";
        exit;
    }

    // Generate reset token and expiry
    $token = bin2hex(random_bytes(32));
    $expiry = date("Y-m-d H:i:s", time() + 86400); // 1 Day

    // Store token and expiry
    $update = $conn->prepare("UPDATE user SET reset_token = ?, reset_expiry = ? WHERE email = ?");
    $update->bind_param("sss", $token, $expiry, $email);
    $update->execute();

    // Reset link
    $resetLink = "http://localhost:3000/includes/reset_password.php?token=$token";

    // Send email via PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 0; // set to 2 if debugging
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply.qmso2026@gmail.com';
        $mail->Password   = 'kpvubjlvhwtpxyvt'; // ✅ NO SPACES
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('noreply.qmso2026@gmail.com', 'DocuSys Support');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "
            <p>Hello,</p>
            <p>We received a request to reset your password.</p>
            <p><a href='{$resetLink}'>Click here to reset your password</a></p>
            <p>This link will expire in 24 hours.</p>
            <br>
            <p>- DocuSys Team</p>
        ";

        $mail->send();
        $showModal = true;

    } catch (Exception $e) {
        error_log($mail->ErrorInfo);
    }


    $stmt->close();
    $update->close();
    $conn->close();
}
?>

<!-- 🔽 Include Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="../css/login.css">

<!-- Style -->
<style>
  /* Custom Modal Overrides */
.modal-content {
  background-color: #fff !important;
  padding: 20px !important;
  width: 300px;
  border-radius: 8px !important;
  text-align: center;
  margin: auto;
}

.modal-header {
  background-color: #1E5A94 !important;
  color: white !important;
  border-bottom: none !important;
  justify-content: center !important;
}

.modal-title {
  font-size: 20px;
  font-weight: bold;
}

.modal-body {
  margin-top: 10px;
  margin-bottom: 20px;
  font-size: 16px;
}

.modal-footer {
  justify-content: center !important;
  border-top: none !important;
}

.modal-footer button {
  margin: 10px;
  padding: 8px 16px;
  border: none;
  background-color: #1E5A94;
  color: white;
  border-radius: 4px;
  cursor: pointer;
  width: 100px;
}

.modal-footer button:hover {
  background-color: #1E5A94;
}

</style>

<!-- ✅ Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="successModalLabel">Success</h5>
      </div>
      <div class="modal-body">
        Password reset link has been sent to your email.
      </div>
      <div class="modal-footer">
        <button type="button" id="modalOkBtn" class="btn btn-primary">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- ✅ Modal Script -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    const okBtn = document.getElementById('modalOkBtn');

    <?php if ($showModal): ?>
      modal.show();
      okBtn.addEventListener('click', function () {
        window.location.href = '../index.php';
      });
    <?php endif; ?>
  });
</script>
