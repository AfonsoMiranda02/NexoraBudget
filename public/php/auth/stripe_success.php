<?php
require_once '../../../config.php';
session_start();
// Exemplo: buscar o e-mail e plano salvos na sessão durante o registro
if (isset($_SESSION['pending_email']) && isset($_SESSION['pending_plan'])) {
    require_once '../db/functions.php';
    $user = buscarUsuarioPorEmail($_SESSION['pending_email']);
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['account_type'] = $user['account_type'];
        $_SESSION['plan'] = $_SESSION['pending_plan'];
        // Aqui você pode atualizar o plano no banco de dados se necessário
        header('Location: ' . BASE_URL . '/public/pages/dashboard_enterprise.php');
        exit;
    }
}
// Se não encontrar, redireciona para login
header('Location: ' . BASE_URL . '/public/pages/login.php');
exit;

// Função exemplo para buscar usuário por e-mail
function buscarUsuarioPorEmail($email) {
    $db = getDBConnection();
    $stmt = $db->prepare('SELECT eu.id, eu.name, eu.email, eu.enterprise_id, "enterprise" as account_type FROM enterprise_users eu WHERE eu.email = ?');
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
} 