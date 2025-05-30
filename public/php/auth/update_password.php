<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../db/functions.php';

$db = getDBConnection();
if (!$db) {
    die("Database connection failed");
}

$email = 'john@nexoratech.com';
$newPassword = 'john123';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
    $result = $stmt->execute([$hashedPassword, $email]);
    
    if ($result) {
        echo "Password updated successfully for " . $email;
    } else {
        echo "Failed to update password";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} 