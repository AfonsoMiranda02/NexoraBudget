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
    echo json_encode(['error' => 'Project ID is required']);
    exit();
}

$project_id = (int)$_POST['id'];

try {
    $db->beginTransaction();

    // Verificar se o projeto existe
    $stmt = $db->prepare("SELECT id FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Project not found');
    }

    // Remover vínculos com usuários
    $stmt = $db->prepare("DELETE FROM project_users WHERE project_id = ?");
    $stmt->execute([$project_id]);

    // Remover vínculos com orçamentos
    $stmt = $db->prepare("DELETE FROM budget_users WHERE budget_id IN (SELECT id FROM budgets WHERE project_id = ?)");
    $stmt->execute([$project_id]);

    $stmt = $db->prepare("DELETE FROM budgets WHERE project_id = ?");
    $stmt->execute([$project_id]);

    // Remover o projeto
    $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);

    $db->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 