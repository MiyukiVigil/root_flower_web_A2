<?php
session_start();
require 'mail_function.php';

$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $file = __DIR__ . "/../../data/User/user.txt";
    $user_exists = false;

    if (file_exists($file)) {
        $users = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($users as $user) {
            if (strpos($user, "Email:$email") !== false) {
                $user_exists = true;
                break;
            }
        }
    }

    // Always show a generic message to prevent user enumeration
    $message = "If an account with that email exists, a verification code has been sent.";

    if ($user_exists) {
        $otp = rand(100000, 999999);
        send_otp_email($email, $otp);
        
        $_SESSION['reset_email'] = $email;
        $_SESSION['registration_otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + 600; // 10 minutes
        
        header("Location: verify_otp.php?action=reset");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Forgot Password</title>
        <meta name="author" content="Ivan">
        <meta name="keywords" content="Login">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" />
        <link href="./style/style.css" rel="stylesheet">
        <link rel="icon" href="./images/logo.svg">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    </head>
    <body class="reg-body">
        <header>
                <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm py-2">
                    <div class="container">
                        <a class="navbar-brand" href="index.php">
                            <img src="images/logo.svg" alt="Root Flowers Logo" class="navbar-logo">
                            <span class="brand-logo-text ms-2">Root Flowers</span>
                        </a>
                    </div>
                </nav>
        </header>
        <div class="reg-wrapper">
            <div class="container">
                <div class="col-lg-6 mx-auto">
                    <div class="card register-card">
                        <div class="card-body p-4">
                            <h2 class="text-center mb-3">Reset Your Password</h2>
                            <p class="text-center text-white-75 mb-4">Enter your email address and we will send you a code to reset your password.</p>
                            
                            <?php if ($message): ?>
                                <div class="alert alert-<?= $message_type ?> text-center"><?= $message ?></div>
                            <?php endif; ?>

                            <form method="POST" action="forgot_password.php">
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Send Reset Code</button>
                                </div>
                            </form>
                            <div class="mt-3 text-center">
                                    <a href="login.php" class="text-decoration-none"><i class="bi bi-box-arrow-in-right"></i> Back to Login Page
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>