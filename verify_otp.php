<?php
session_start();
require './vendor/autoload.php'; // Ensure autoloader is included for Dotenv
require 'mail_function.php'; // Include our email function

$action = $_GET['action'] ?? 'register';

if ($action === 'register') {
    if (!isset($_SESSION['registration_data'])) {
        header("Location: registration.php");
        exit;
    }
    $page_title = "Verify Your Account";
    $email = $_SESSION['registration_data']['email'];
    $otp_to_check = $_SESSION['registration_otp'] ?? null;
    $icon_class = 'bi bi-person-check'; // Icon for registration

} elseif ($action === 'reset') {
    if (!isset($_SESSION['reset_email'])) {
        header("Location: forgot_password.php");
        exit;
    }
    $page_title = "Verify Password Reset";
    $email = $_SESSION['reset_email'];
    $otp_to_check = $_SESSION['registration_otp'] ?? null;
    $icon_class = 'bi bi-shield-lock'; // Icon for password reset
} else {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_otp = trim($_POST['otp']);
    
    // Check if OTP has expired
    if (time() > ($_SESSION['otp_expiry'] ?? 0)) { // Added null coalesce for robustness
        $error = "OTP has expired. Please try again.";
    } elseif ($user_otp == $otp_to_check) {
        
        if ($action === 'register') {
            // --- Success! Finalize Registration ---
            $data = $_SESSION['registration_data'];
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $dobFormatted = date("d-m-Y", strtotime($data['dob']));

            $record = "First Name:{$data['first_name']}|Last Name:{$data['last_name']}|DOB:{$dobFormatted}|Gender:{$data['gender']}|Email:{$data['email']}|Hometown:{$data['hometown']}|Password:{$hashed_password}\n";

            $dir = __DIR__ . "/../../data/User";
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $file = $dir . "/user.txt";
            file_put_contents($file, $record, FILE_APPEND);

            // Clean up session
            unset($_SESSION['registration_data'], $_SESSION['registration_otp'], $_SESSION['otp_expiry']);
            
            $_SESSION['success_message'] = "Registration complete! You can now log in.";
            header("Location: login.php");
            exit;
        } else { // Success for password reset
            $_SESSION['otp_verified'] = true; // Set flag for reset_password page
            // Clean up OTP related sessions specific to this flow, keep reset_email
            unset($_SESSION['registration_otp'], $_SESSION['otp_expiry']);
            header("Location: reset_password.php");
            exit;
        }

    } else {
        $error = "Invalid OTP. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?= $page_title ?></title>
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
            <div class="container d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 120px);"> <div class="col-lg-6 col-md-8 mx-auto">
                    <div class="card otp-card text-center"> <div class="card-body p-4 p-md-5">
                            <i class="<?= htmlspecialchars($icon_class) ?> otp-icon mb-4"></i> <h2 class="fw-bold mb-3" style="color: #333;"><?= $page_title ?></h2>
                            <p class="mb-4" style="color: #555;">An OTP has been sent to <strong><?= htmlspecialchars($email) ?></strong>. It is valid for 10 minutes. Please enter it below.</p>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger text-center"><?= $error ?></div>
                            <?php endif; ?>

                            <form method="POST" action="verify_otp.php?action=<?= $action ?>">
                                <div class="mb-3">
                                    <label for="otpInput" class="form-label visually-hidden">Enter 6-Digit OTP</label>
                                    <input type="text" id="otpInput" name="otp" class="form-control text-center" placeholder="● ● ● ● ● ●" maxlength="6" required autofocus inputmode="numeric" pattern="[0-9]*">
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Verify</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>