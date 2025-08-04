<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    body {
      background-color: #800000;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .forgot-container {
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 5px 10px rgba(0,0,0,0.1);
      width: 400px;
      text-align: center;
    }

    .forgot-container h2 {
      margin-bottom: 20px;
      color: #800000;
    }

    .forgot-container input[type="email"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
    }

    .forgot-container input[type="submit"] {
      width: 100%;
      padding: 12px;
      border: none;
      background-color: #800000;
      color: white;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
    }

    .forgot-container input[type="submit"]:hover {
      background-color: #a00000;
    }

    .forgot-container a {
      display: inline-block;
      margin-top: 15px;
      color: #800000;
      text-decoration: none;
      font-size: 14px;
    }

    .forgot-container a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="forgot-container">
    <h2>Forgot Password</h2>
    <form action="../handlers/forgot_password_handler.php" method="POST">
      <input type="email" name="email" placeholder="Enter your email" required>
      <input type="submit" value="Reset Password">
    </form>
    <a href="../login.php">← Back to Login</a>
  </div>
</body>
</html>
