<?php
// PHPMailer setup
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'path/to/PHPMailer/src/Exception.php';
require 'path/to/PHPMailer/src/PHPMailer.php';
require 'path/to/PHPMailer/src/SMTP.php';

function sendAppointmentConfirmation($to_email, $to_name, $appointment_details) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.yourprovider.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your@email.com';
        $mail->Password   = 'yourpassword';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('no-reply@hospitalms.com', 'Hospital Management System');
        $mail->addAddress($to_email, $to_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Appointment Confirmation';
        
        $mail->Body = "
            <h2>Appointment Confirmation</h2>
            <p>Dear $to_name,</p>
            <p>Your appointment has been successfully booked with the following details:</p>
            
            <table>
                <tr>
                    <td><strong>Doctor:</strong></td>
                    <td>Dr. {$appointment_details['doctor_name']}</td>
                </tr>
                <tr>
                    <td><strong>Specialization:</strong></td>
                    <td>{$appointment_details['specialization']}</td>
                </tr>
                <tr>
                    <td><strong>Date & Time:</strong></td>
                    <td>{$appointment_details['appointment_date']}</td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td>{$appointment_details['status']}</td>
                </tr>
            </table>
            
            <p>You can view or manage your appointment by logging into your account.</p>
            <p>Thank you for choosing our hospital!</p>
        ";
        
        $mail->AltBody = "Appointment Confirmation\n\n" .
                         "Dear $to_name,\n\n" .
                         "Your appointment has been booked with Dr. {$appointment_details['doctor_name']} ({$appointment_details['specialization']}) " .
                         "on {$appointment_details['appointment_date']}. Status: {$appointment_details['status']}\n\n" .
                         "Thank you!";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function sendPasswordResetLink($to_email, $to_name, $reset_link) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings (same as above)
        
        // Recipients
        $mail->setFrom('no-reply@hospitalms.com', 'Hospital Management System');
        $mail->addAddress($to_email, $to_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        
        $mail->Body = "
            <h2>Password Reset</h2>
            <p>Dear $to_name,</p>
            <p>We received a request to reset your password. Click the link below to proceed:</p>
            <p><a href='$reset_link'>$reset_link</a></p>
            <p>If you didn't request this, please ignore this email.</p>
            <p>This link will expire in 1 hour.</p>
        ";
        
        $mail->AltBody = "Password Reset\n\n" .
                         "Dear $to_name,\n\n" .
                         "Click this link to reset your password: $reset_link\n\n" .
                         "If you didn't request this, please ignore this email.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>