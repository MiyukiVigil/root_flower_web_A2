<?php
session_start();

// Redirect if not a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: workshop_reg.php");
    exit;
}

// --- Retrieve and sanitize form data ---
$first_name        = trim($_POST['first_name'] ?? '');
$last_name         = trim($_POST['last_name'] ?? '');
$email             = trim($_POST['email'] ?? '');
$contact_number    = trim($_POST['contact_number'] ?? '');
$workshop_title    = trim($_POST['workshop_title'] ?? '');
$workshop_datetime = trim($_POST['workshop_datetime'] ?? '');

$errors = [];

// --- Simple Validation ---
if (empty($first_name)) { $errors['first_name'] = "First name is required."; }
if (empty($last_name)) { $errors['last_name'] = "Last name is required."; }
if (empty($email)) { $errors['email'] = "Email is required."; }
if (empty($contact_number)) { $errors['contact_number'] = "Contact number is required."; }
if (empty($workshop_title)) { $errors['workshop_title'] = "Workshop title is required."; }
if (empty($workshop_datetime)) { $errors['workshop_datetime'] = "Please select a date and time."; }

// --- Redirect back if there are errors ---
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $_POST;
    header("Location: workshop_reg.php");
    exit;
}

// --- Save to file ---
$dir = __DIR__ . "/../../data/Workshop";
$file = $dir . "/workshop_registrations.txt";

// Create directory if it doesn't exist
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

// Build the record string
$record = "FirstName:" . $first_name
        . "|LastName:" . $last_name
        . "|Email:" . $email
        . "|ContactNumber:" . $contact_number
        . "|WorkshopTitle:" . $workshop_title
        . "|DateTime:" . $workshop_datetime . "\n";

// Append the new registration to the file
file_put_contents($file, $record, FILE_APPEND);

// --- Redirect on success with a success message ---
$_SESSION['success_message'] = "Thank you, " . htmlspecialchars($first_name) . "! Your registration has been received.";
header("Location: workshop_reg.php");
exit;
?>