<?php
    session_start();
    require 'connection.php';

    $errors = [];
    $email = "";
    $password = "";

    // Already logged in? Redirect to main menu
    if (isset($_SESSION['user_email'])) {
        header("Location: main_menu.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $errors['login'] = "Please fill in both fields.";
        } else {
            // Fetch account info from DB
            $stmt = $conn->prepare("SELECT * FROM account_table WHERE email = ?");
            $stmt->execute([$email]);
            $account = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($account && password_verify($password, $account['password'])) {
                // login success
                $_SESSION['user_email'] = $account['email'];
                $_SESSION['user_type'] = $account['type'];

                // Redirect based on type
                if ($account['type'] === 'admin') {
                    header("Location: main_menu_admin.php");
                } else {
                    header("Location: main_menu.php");
                }
                exit;
            } else {
                $errors['login'] = "Invalid email or password.";
            }

        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
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
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="text-center" data-aos="fade-down">
                         <div class="success-pill">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?= htmlspecialchars($_SESSION['success_message']); ?>
                         </div>
                    </div>
                    <?php unset($_SESSION['success_message']); // Clear the message so it doesn't show again ?>
                <?php endif; ?>

                <div class="row align-items-center justify-content-center">
                    
                    <div class="col-lg-6 register-info text-center text-lg-start mb-5 mb-lg-0" data-aos="fade-right">
                        <h1 class="display-4 text-white fw-bold">Welcome Back!</h1>
                        <p class="lead text-white-75 mt-3">
                            Sign in to access your account and continue your journey with RootFlowers. We're glad to see you again.
                        </p>
                        <img src="./images/logo.svg" alt="RootFlowers Logo" class="register-info-logo mt-4">
                    </div>

                    <div class="col-lg-6" data-aos="fade-left">
                        <div class="card register-card">
                            <div class="card-body p-4">
                                <h2 class="text-center mb-4">👋 Sign In</h2>

                                <form method="POST" action="login.php" novalidate>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" 
                                               name="email" 
                                               class="form-control <?= !empty($errors['login']) ? 'is-invalid' : '' ?>" 
                                               value="<?= htmlspecialchars($email) ?>" 
                                               required>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Password</label>
                                        <div class="password-wrapper">
                                            <input type="password" 
                                                    id="password"
                                                    name="password" 
                                                    class="form-control <?= !empty($errors['login']) ? 'is-invalid' : '' ?>" 
                                                    value="<?= htmlspecialchars($password) ?>" 
                                                    required>
                                            <i class="bi bi-eye-slash toggle-password-icon" id="togglePassword"></i>
                                        </div>
                                    </div>

                                    <?php if (!empty($errors['login'])): ?>
                                        <div class="mb-3 text-center">
                                            <div class="invalid-feedback d-block"><?= $errors['login'] ?></div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-grid mb-3">
                                        <button type="submit" class="btn btn-primary">Login</button>
                                    </div>
                                    
                                    <div class="mb-4 text-end">
                                        <a href="forgot_password.php" class="small">Forgot Password?</a>
                                    </div>
                                </form>

                                <div class="mt-4 text-center">
                                    <p class="mb-0" style="color: rgba(255,255,255,0.7);">Don't have an account? 
                                        <a href="registration.php">Register here</a>
                                    </p>
                                </div>
                                <div class="mt-3 text-center">
                                    <a href="index.php" class="text-decoration-none"><i class="bi bi-house-door-fill"></i> Back to Home</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- AOS (Animate on Scroll) JS -->
        <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

        <!-- Initialize AOS -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                AOS.init({
                    duration: 800,
                    easing: 'ease-in-out', 
                    once: true
                });

                // Password visibility toggle
                const togglePassword = document.getElementById('togglePassword');
                const passwordField = document.getElementById('password');
                togglePassword.addEventListener('click', function () {
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    this.classList.toggle('bi-eye');
                    this.classList.toggle('bi-eye-slash');
                });
            });
        </script>
    </body>
</html>

