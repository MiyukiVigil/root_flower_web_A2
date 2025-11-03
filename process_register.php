<?php
session_start();
require './vendor/autoload.php';
require 'mail_function.php';

// --- Validation Logic ---
function valid_password($password) {
    return preg_match('/^(?=.*[0-9])(?=.*[!@#$%^&*]).{8,}$/', $password);
}
// (All your other validation checks for name, email, etc. go here)
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$dob = trim($_POST['dob'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$email = trim($_POST['email'] ?? '');
$hometown = trim($_POST['hometown'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];

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

// Check for duplicate email
$file = __DIR__ . "/../../data/User/user.txt";
if (file_exists($file)) {
    $users = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($users as $user) {
        if (strpos($user, "Email:$email") !== false) {
            $errors['email'] = "This email address is already registered.";
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
            header("Location: registration.php");
            exit;
        }
    }
}

// --- New OTP Logic ---
$otp = rand(100000, 999999);

if (send_otp_email($email, $otp)) {
    // Store data temporarily in the session
    $_SESSION['registration_data'] = $_POST;
    $_SESSION['registration_otp'] = $otp;
    $_SESSION['otp_expiry'] = time() + 600; // OTP valid for 10 minutes

    // Redirect to the new verification page
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