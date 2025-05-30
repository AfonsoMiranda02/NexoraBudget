<?php
require_once '../../../config.php';
require_once '../../php/db/functions.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDBConnection();
    $task_id = $_POST['task_id'] ?? null;
    if ($db && $task_id) {
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        echo json_encode(['success' => true]);
        exit;
    }
}
echo json_encode(['success' => false]); 