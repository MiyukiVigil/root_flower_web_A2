<?php
    session_start();
    require './vendor/autoload.php';
    require 'mail_function.php';
    require 'connection.php';

    $action = $_GET['action'] ?? 'register';

    if ($action === 'register') {
        if (!isset($_SESSION['registration_data'])) {
            header("Location: registration.php");
            exit;
        }

        $page_title = "Verify Your Account";
        $email = $_SESSION['registration_data']['email'];
        $otp_to_check = $_SESSION['registration_otp'] ?? null;
        $icon_class = 'bi bi-person-check';

    } elseif ($action === 'reset') {
        if (!isset($_SESSION['reset_email'])) {
            header("Location: forgot_password.php");
            exit;
        }

        $page_title = "Verify Password Reset";
        $email = $_SESSION['reset_email'];
        $otp_to_check = $_SESSION['registration_otp'] ?? null;
        $icon_class = 'bi bi-shield-lock';
    } else {
        header("Location: index.php");
        exit;
    }

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_otp = trim($_POST['otp']);

        if (time() > ($_SESSION['otp_expiry'] ?? 0)) {
            $error = "OTP has expired. Please try again.";
        } elseif ($user_otp == $otp_to_check) {

            if ($action === 'register') {
                // --- Finalize Registration ---
                $data = $_SESSION['registration_data'];

                try {
                    // Check if email already exists in user_table
                    $checkStmt = $conn->prepare("SELECT email FROM user_table WHERE email = ?");
                    $checkStmt->execute([$data['email']]);
                    if ($checkStmt->rowCount() > 0) {
                        $error = "This email is already registered. Please log in instead.";
                    } else {
                        // 1. Insert user info into user_table
                        $stmt = $conn->prepare("INSERT INTO user_table (first_name, last_name, dob, gender, email, hometown) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$data['first_name'], $data['last_name'], $data['dob'], $data['gender'], $data['email'], $data['hometown']]);

                        // 2. Insert hashed password into account_table
                        $stmt = $conn->prepare("INSERT INTO account_table (email, password, type) VALUES (?, ?, ?)");
                        $stmt->execute([$data['email'], $data['password'], 'user']);

                        // 3. Cleanup session
                        unset($_SESSION['registration_data'], $_SESSION['registration_otp'], $_SESSION['otp_expiry']);

                        $_SESSION['success_message'] = "Registration complete! You can now log in.";
                        header("Location: login.php");
                        exit;
                    }

                } catch (PDOException $e) {
                    $error = "Database error: " . $e->getMessage();
                }

            } else {
                // Password reset flow
                $_SESSION['otp_verified'] = true;
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
    <link href="./style/style.css" rel="stylesheet">
</head>
<body class="reg-body">
    <div class="reg-wrapper">
        <div class="container d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 120px);">
            <div class="col-lg-6 col-md-8 mx-auto">
                <div class="card otp-card text-center">
                    <div class="card-body p-4 p-md-5">
                        <i class="<?= htmlspecialchars($icon_class) ?> otp-icon mb-4"></i>
                        <h2 class="fw-bold mb-3"><?= $page_title ?></h2>
                        <p class="mb-4">An OTP has been sent to <strong><?= htmlspecialchars($email) ?></strong>. Please enter it below.</p>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="verify_otp.php?action=<?= $action ?>">
                            <div class="mb-3">
                                <input type="text" name="otp" class="form-control text-center" placeholder="Enter 6-Digit OTP" maxlength="6" required inputmode="numeric" pattern="[0-9]*">
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
</body>
</html>
