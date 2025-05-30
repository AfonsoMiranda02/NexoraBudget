<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../db/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "All fields are required.";
        header("Location: " . BASE_URL . "/public/pages/login.php");
        exit();
    }

    $db = getDBConnection();
    if (!$db) {
        $_SESSION['login_error'] = "Database connection error.";
        header("Location: " . BASE_URL . "/public/pages/login.php");
        exit();
    }

    try {
        // First, try enterprise_users
        $stmt = $db->prepare("SELECT eu.*, e.id as enterprise_id, e.name as enterprise_name 
                             FROM enterprise_users eu 
                             LEFT JOIN enterprises e ON eu.enterprise_id = e.id 
                             WHERE eu.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['account_type'] = 'enterprise';
                $_SESSION['enterprise_id'] = $user['enterprise_id'];
                $_SESSION['enterprise_name'] = $user['enterprise_name'];
                header("Location: " . BASE_URL . "/public/pages/dashboard_enterprise.php");
                exit();
            } else {
                $_SESSION['login_error'] = "Incorrect password.";
                header("Location: " . BASE_URL . "/public/pages/login.php");
                exit();
            }
        } else {
            // Try master user or client in users table
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $master = $stmt->fetch();
            if ($master) {
                if (password_verify($password, $master['password'])) {
                    $_SESSION['user_id'] = $master['id'];
                    $_SESSION['user_name'] = $master['name'];
                    $_SESSION['user_email'] = $master['email'];
                    $_SESSION['profile_image'] = $master['profile_image'];
                    if (isset($master['is_master']) && $master['is_master'] == 1) {
                        $_SESSION['is_master'] = true;
                        $_SESSION['account_type'] = 'master';
                        header("Location: " . BASE_URL . "/public/pages/master_dashboard.php");
                    } else {
                        $_SESSION['is_master'] = false;
                        $_SESSION['account_type'] = 'client';
                        header("Location: " . BASE_URL . "/public/pages/dashboard_client.php");
                    }
                    exit();
                } else {
                    $_SESSION['login_error'] = "Incorrect password.";
                    header("Location: " . BASE_URL . "/public/pages/login.php");
                    exit();
                }
            } else {
                $_SESSION['login_error'] = "User not found.";
                header("Location: " . BASE_URL . "/public/pages/login.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['login_error'] = "An error occurred during login.";
        header("Location: " . BASE_URL . "/public/pages/login.php");
        exit();
    }
} else {
    header("Location: " . BASE_URL . "/public/pages/login.php");
    exit();
} 