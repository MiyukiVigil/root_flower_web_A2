<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// This line includes the Composer autoloader.
require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function send_otp_email($recipient_email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST']; // Get value from .env
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER']; // Get value from .env
        $mail->Password   = $_ENV['SMTP_PASS']; // Get value from .env
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['SMTP_PORT']; // Get value from .env

        // Recipients
        $mail->setFrom($_ENV['SMTP_USER'], 'Root Flowers');
        $mail->addAddress($recipient_email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code for Root Flowers';
        $mail->Body    = "Your One-Time Password (OTP) is: <b>$otp</b>. It is valid for 10 minutes.";
        $mail->AltBody = "Your One-Time Password (OTP) is: $otp. It is valid for 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>