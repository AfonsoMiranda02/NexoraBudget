<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db/functions.php';
require_once __DIR__ . '/../../vendor/autoload.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        $_SESSION['error'] = "Email is required";
        header("Location: " . BASE_URL . "/forgot-password");
        exit();
    }

    // Check if email exists
    if (!emailExists($email)) {
        $_SESSION['error'] = "No account found with this email";
        header("Location: " . BASE_URL . "/forgot-password");
        exit();
    }

    // Generate reset token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    if (createPasswordReset($email, $token, $expires)) {
        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password';
            
            $resetLink = BASE_URL . "/reset-password?token=" . $token;
            $mail->Body = "
                <h2>Password Reset Request</h2>
                <p>Click the link below to reset your password:</p>
                <p><a href='{$resetLink}'>{$resetLink}</a></p>
                <p>This link will expire in 1 hour.</p>
            ";

            $mail->send();
            $_SESSION['success'] = "Password reset link has been sent to your email";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to send reset email. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Failed to process reset request. Please try again.";
    }

    header("Location: " . BASE_URL . "/forgot-password");
    exit();
} else {
    header("Location: " . BASE_URL . "/forgot-password");
    exit();
} 