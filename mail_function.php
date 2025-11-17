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

function send_workshop_notification($recipient_email, $recipient_name, $workshop_title, $status) {
    $mail = new PHPMailer(true);

    try {
        // Server settings (copied from your function)
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['SMTP_PORT'];

        // Recipients
        $mail->setFrom($_ENV['SMTP_USER'], 'Root Flowers Admin');
        $mail->addAddress($recipient_email, $recipient_name);

        // Content
        $mail->isHTML(true);
        
        if ($status == 'approved') {
            $mail->Subject = 'Your Workshop Registration is Approved!';
            $mail->Body    = "
                <p>Dear $recipient_name,</p>
                <p>We are excited to confirm your registration for the <b>$workshop_title</b> workshop!</p>
                <p>We look forward to seeing you there.</p>
                <p>Best regards,<br>The Root Flowers Team</p>
            ";
        } else { // 'rejected'
            $mail->Subject = 'Your Workshop Registration Status';
            $mail->Body    = "
                <p>Dear $recipient_name,</p>
                <p>Thank you for your interest in the <b>$workshop_title</b> workshop.</p>
                <p>Unfortunately, we are unable to confirm your registration at this time (e.g., the workshop may be full).</p>
                <p>Please contact us for more details. We hope to see you at a future event.</p>
                <p>Best regards,<br>The Root Flowers Team</p>
            ";
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>