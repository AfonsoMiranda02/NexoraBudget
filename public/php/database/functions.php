<?php
require_once __DIR__ . '/../../../config.php';

function checkUserCredentials($email, $password) {
    global $pdo;
    // Procurar em users
    $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (password_verify($password, $user['password'])) {
            unset($user['password']);
            $user['account_type'] = 'user';
            return $user;
        }
    }
    // Procurar em enterprise_users (por email OU username)
    $stmt = $pdo->prepare("SELECT eu.id, eu.name, eu.email, eu.password, eu.username, eu.enterprise_id, e.name as enterprise_name FROM enterprise_users eu JOIN enterprises e ON eu.enterprise_id = e.id WHERE eu.email = ? OR eu.username = ?");
    $stmt->execute([$email, $email]);
    if ($eu = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (password_verify($password, $eu['password'])) {
            unset($eu['password']);
            $eu['account_type'] = 'enterprise';
            return $eu;
        }
    }
    return false;
}

function createNewUser($name, $email, $password) {
    global $pdo;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    return $stmt->execute([$name, $email, $hashed_password]);
}

function emailExists($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}

function createPasswordReset($email, $token, $expires) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    return $stmt->execute([$email, $token, $expires]);
}

function validateResetToken($token) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT email 
        FROM password_resets 
        WHERE token = ? 
        AND expires_at > NOW() 
        AND used = FALSE
    ");
    $stmt->execute([$token]);
    return $stmt->fetchColumn();
}

function markResetTokenUsed($token) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE password_resets SET used = TRUE WHERE token = ?");
    return $stmt->execute([$token]);
}

function updateUserPassword($email, $password) {
    global $pdo;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
    return $stmt->execute([$hashed_password, $email]);
}

function getUserData() {
    global $pdo;
    if (!isset($_SESSION)) session_start();
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['account_type'])) return null;
    if ($_SESSION['account_type'] === 'user') {
        $stmt = $pdo->prepare("SELECT id, name, email, profile_image FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($_SESSION['account_type'] === 'enterprise') {
        $stmt = $pdo->prepare("SELECT eu.id, eu.name, eu.email, eu.username, eu.profile, eu.nif, eu.cc, eu.enterprise_id, e.name as enterprise_name, eu.plan FROM enterprise_users eu JOIN enterprises e ON eu.enterprise_id = e.id WHERE eu.id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
} 