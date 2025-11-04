<?php
session_start();
require_once 'connection.php'; // Make sure $conn is your PDO connection

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
$workshop_datetime = trim($_POST['workshop_datetime'] ?? ''); // Format: yyyy-mm-ddTHH:MM

$errors = [];

// --- Simple Validation ---
if (empty($first_name)) { $errors['first_name'] = "First name is required."; }
if (empty($last_name)) { $errors['last_name'] = "Last name is required."; }
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = "Valid email is required."; }
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

// --- Split datetime into date and time ---
$date = null;
$time = null;
if (!empty($workshop_datetime)) {
    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $workshop_datetime);
    if ($dt) {
        $date = $dt->format('Y-m-d');
        $time = $dt->format('H:i:s');
    }
}

// --- Insert into database ---
try {
    $sql = "INSERT INTO workshop_table 
            (email, first_name, last_name, workshop_title, date, time, contact_number) 
            VALUES 
            (:email, :first_name, :last_name, :workshop_title, :date, :time, :contact_number)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':email'         => $email,
        ':first_name'    => $first_name,
        ':last_name'     => $last_name,
        ':workshop_title'=> $workshop_title,
        ':date'          => $date,
        ':time'          => $time,
        ':contact_number'=> $contact_number
    ]);

    $_SESSION['success_message'] = "Thank you, " . htmlspecialchars($first_name) . "! Your registration has been received.";
    header("Location: workshop_reg.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['errors'] = ['database' => 'Database error: ' . $e->getMessage()];
    $_SESSION['old'] = $_POST;
    header("Location: workshop_reg.php");
    exit;
}
