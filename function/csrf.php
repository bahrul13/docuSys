<?php
// function/csrf.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_verify(): void {
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $postedToken  = $_POST['csrf_token'] ?? '';

    if (!$sessionToken || !$postedToken || !hash_equals($sessionToken, $postedToken)) {
        $_SESSION['flash'] = "⚠️ Security check failed. Please try again.";
        // you can change redirect per page; default back:
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "../index.php"));
        exit();
    }
}
