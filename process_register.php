<?php
    session_start();
    require './vendor/autoload.php';
    require 'mail_function.php';
    require 'connection.php';

    // --- Validation Logic ---
    function valid_password($password) {
        return preg_match('/^(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/', $password);
    }

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hometown = trim($_POST['hometown'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    // --- Field validation ---
    if (empty($first_name) || !preg_match("/^[a-zA-Z ]+$/", $first_name)) $errors['first_name'] = "First name must contain only letters and spaces.";
    if (empty($last_name) || !preg_match("/^[a-zA-Z ]+$/", $last_name)) $errors['last_name'] = "Last name must contain only letters and spaces.";
    if (empty($dob)) $errors['dob'] = "Date of birth is required.";
    if (empty($gender)) $errors['gender'] = "Gender is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Please provide a valid email address.";
    if (empty($hometown)) $errors['hometown'] = "Hometown is required.";
    if (empty($password) || !valid_password($password)) $errors['password'] = "Password must be at least 8 characters, include 1 number and 1 symbol.";
    if ($password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match.";

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header("Location: registration.php");
        exit;
    }

    // --- Check for duplicate email ---
    $stmt = $conn->prepare("SELECT email FROM user_table WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors['email'] = "This email address is already registered.";
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header("Location: registration.php");
        exit;
    }

    // --- Hash password ---
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // --- Generate OTP ---
    $otp = rand(100000, 999999);

    if (send_otp_email($email, $otp)) {
        // Temporarily store info until OTP verification
        $_SESSION['registration_data'] = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'dob' => $dob,
            'gender' => $gender,
            'email' => $email,
            'hometown' => $hometown,
            'password' => $hashedPassword
        ];
        $_SESSION['registration_otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + 600; // 10 minutes

        header("Location: verify_otp.php?action=register");
        exit;
    } else {
        $errors['email'] = "Could not send verification email. Please try again.";
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $_POST;
        header("Location: registration.php");
        exit;
    }
?>
