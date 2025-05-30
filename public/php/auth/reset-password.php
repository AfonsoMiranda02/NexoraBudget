<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db/functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: " . BASE_URL . "/reset-password?token=" . $token);
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: " . BASE_URL . "/reset-password?token=" . $token);
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long";
        header("Location: " . BASE_URL . "/reset-password?token=" . $token);
        exit();
    }

    $email = validateResetToken($token);
    if (!$email) {
        $_SESSION['error'] = "Invalid or expired reset token";
        header("Location: " . BASE_URL . "/forgot-password");
        exit();
    }

    if (updateUserPassword($email, $password) && markResetTokenUsed($token)) {
        $_SESSION['success'] = "Password has been reset successfully";
        header("Location: " . BASE_URL . "/login");
        exit();
    } else {
        $_SESSION['error'] = "Failed to reset password. Please try again.";
        header("Location: " . BASE_URL . "/reset-password?token=" . $token);
        exit();
    }
} else {
    header("Location: " . BASE_URL . "/forgot-password");
    exit();
} 