<?php
require_once '../../../config.php';

// Verificar se é master admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_master']) || $_SESSION['is_master'] != 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Verificar se o ID foi fornecido
if (!isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID is required']);
    exit();
}

$user_id = (int)$_POST['id'];

try {
    $db->beginTransaction();

    // Verificar se o usuário existe e não é master
    $stmt = $db->prepare("SELECT id FROM users WHERE id = ? AND is_master = 0");
    $stmt->execute([$user_id]);
    if (!$stmt->fetch()) {
        throw new Exception('User not found or cannot be deleted');
    }

    // Remover vínculos com empresas
    $stmt = $db->prepare("DELETE FROM enterprise_users WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Remover vínculos com projetos
    $stmt = $db->prepare("DELETE FROM project_users WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Remover vínculos com orçamentos
    $stmt = $db->prepare("DELETE FROM budget_users WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Remover o usuário
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    $db->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 