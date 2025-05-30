<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../db/functions.php';
$userData = getUserData();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Salva o email e plano na sessão
if (isset($_POST['email']) && isset($_POST['plan'])) {
    $_SESSION['pending_email'] = $_POST['email'];
    $_SESSION['pending_plan'] = $_POST['plan'];
    // Login automático do usuário
    $user = buscarUsuarioPorEmail($_POST['email']);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['account_type'] = $user['account_type'];
    }
    // Redireciona para o Stripe
    if ($_POST['plan'] === 'standard') {
        header('Location: https://buy.stripe.com/test_7sY00jgLJaOn7tUczFdQQ00');
    } elseif ($_POST['plan'] === 'plus') {
        header('Location: https://buy.stripe.com/test_4gMdR9brp7Cb8xY0QXdQQ01');
    } else {
        header('Location: https://buy.stripe.com/test_28E5kD675g8H8xYczFdQQ02');
    }
    exit;
}
header('Location: ' . BASE_URL . '/public/pages/register.php?enterprise&step=3');
exit;

function buscarUsuarioPorEmail($email) {
    $db = getDBConnection();
    $stmt = $db->prepare('SELECT eu.id, eu.name, eu.email, eu.enterprise_id, "enterprise" as account_type FROM enterprise_users eu WHERE eu.email = ?');
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
} 