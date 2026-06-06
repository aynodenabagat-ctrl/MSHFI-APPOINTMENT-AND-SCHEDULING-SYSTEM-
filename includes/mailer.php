<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';

// Load PHPMailer (requires composer install)
// If not using Composer, include files manually:
// require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
// require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
// require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendAppointmentEmail($toEmail, $toName, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}

function sendAppointmentConfirmation($email, $name, $doctorName, $date, $time) {
    $subject = "Appointment Confirmed - Mindalano Specialist Hospital";
    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
        <h2 style='color: #0d6efd;'>Appointment Confirmed</h2>
        <p>Dear <strong>$name</strong>,</p>
        <p>Your appointment has been confirmed with the following details:</p>
        <table style='width: 100%; border-collapse: collapse;'>
            <tr><td style='padding: 8px; border: 1px solid #ddd;'><strong>Doctor:</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>$doctorName</td></tr>
            <tr><td style='padding: 8px; border: 1px solid #ddd;'><strong>Date:</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>$date</td></tr>
            <tr><td style='padding: 8px; border: 1px solid #ddd;'><strong>Time:</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>$time</td></tr>
        </table>
        <p style='margin-top: 20px;'>Thank you for choosing Mindalano Specialist Hospital.</p>
        <p style='color: #6c757d; font-size: 12px;'>This is an automated message. Please do not reply.</p>
    </div>";
    return sendAppointmentEmail($email, $name, $subject, $body);
}

function sendAppointmentCancellation($email, $name, $doctorName, $date, $time) {
    $subject = "Appointment Cancelled - Mindalano Specialist Hospital";
    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
        <h2 style='color: #dc3545;'>Appointment Cancelled</h2>
        <p>Dear <strong>$name</strong>,</p>
        <p>Your appointment with <strong>$doctorName</strong> on <strong>$date</strong> at <strong>$time</strong> has been cancelled.</p>
        <p>If this was unexpected, please log in to reschedule at your earliest convenience. We sincerely apologize for any inconvenience.</p>
        <p style='color: #6c757d; font-size: 12px;'>This is an automated message. Please do not reply.</p>
    </div>";
    return sendAppointmentEmail($email, $name, $subject, $body);
}

function sendAppointmentReminder($email, $name, $doctorName, $date, $time) {
    $subject = "Appointment Reminder - Mindalano Specialist Hospital";
    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
        <h2 style='color: #198754;'>Appointment Reminder</h2>
        <p>Dear <strong>$name</strong>,</p>
        <p>This is a friendly reminder of your upcoming appointment:</p>
        <table style='width: 100%; border-collapse: collapse;'>
            <tr><td style='padding: 8px; border: 1px solid #ddd;'><strong>Doctor:</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>$doctorName</td></tr>
            <tr><td style='padding: 8px; border: 1px solid #ddd;'><strong>Date:</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>$date</td></tr>
            <tr><td style='padding: 8px; border: 1px solid #ddd;'><strong>Time:</strong></td><td style='padding: 8px; border: 1px solid #ddd;'>$time</td></tr>
        </table>
        <p style='margin-top: 20px;'>Please arrive on time. If you need to reschedule, please log in to your account.</p>
        <p style='color: #6c757d; font-size: 12px;'>This is an automated message. Please do not reply.</p>
    </div>";
    return sendAppointmentEmail($email, $name, $subject, $body);
}
