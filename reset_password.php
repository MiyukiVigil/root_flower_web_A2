<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['otp_verified'], $_SESSION['reset_email']) || !$_SESSION['otp_verified']) {
    header("Location: forgot_password.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    $stmt = $conn->prepare("SELECT password FROM account_table WHERE email = ?");
    $stmt->execute([$_SESSION['reset_email']]);
    $current_hash = $stmt->fetchColumn();

    if ($current_hash && password_verify($password, $current_hash)) {
        $errors[] = "New password cannot be the same as your old password.";
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE account_table SET password = ? WHERE email = ?");
        if ($stmt->execute([$hashed, $_SESSION['reset_email']])) {
            unset($_SESSION['otp_verified'], $_SESSION['reset_email'], $_SESSION['registration_otp'], $_SESSION['otp_expiry']);
            $_SESSION['success_message'] = "Password has been updated! Please log in.";
            header("Location: login.php");
            exit;
        } else {
            $errors[] = "Failed to update password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Set New Password</title>
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
                            <h2 class="text-center mb-4">Set a New Password</h2>
                            
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $error): ?>
                                        <p class="mb-0"><?= $error ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="reset_password.php">
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="right" title="Password must be at least 8 characters long and contain a mix of letters, numbers, and symbols."></i></label>
                                    <div class="password-wrapper">
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <i class="bi bi-eye-slash toggle-password-icon" id="togglePassword"></i>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <i class="bi bi-eye-slash toggle-password-icon" id="toggleConfirmPassword"></i>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            document.getElementById('togglePassword').addEventListener('click', function() {
                const passwordField = document.getElementById('password');
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                this.classList.toggle('bi-eye-slash');
                this.classList.toggle('bi-eye');
            });

            document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
                const confirmPasswordField = document.getElementById('confirm_password');
                const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordField.setAttribute('type', type);
                this.classList.toggle('bi-eye-slash');
                this.classList.toggle('bi-eye');
            });

            // Initialize AOS animations
            AOS.init();

            // Initialize Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        </script>
    </body>
</html>