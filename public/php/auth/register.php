<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db/functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: " . BASE_URL . "/register");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: " . BASE_URL . "/register");
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long";
        header("Location: " . BASE_URL . "/register");
        exit();
    }

    // Here you would typically check if email already exists in database
    // and create the new user
    $user = registerUser($name, $email, $password);

    if ($user) {
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: " . BASE_URL . "/login");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: " . BASE_URL . "/register");
        exit();
    }
} else {
    header("Location: " . BASE_URL . "/register");
    exit();
} 