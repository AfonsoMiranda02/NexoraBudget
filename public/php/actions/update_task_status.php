<?php
require_once '../../../config.php';
require_once '../../php/db/functions.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDBConnection();
    $task_id = $_POST['task_id'] ?? null;
    if ($db && $task_id) {
        $stmt = $db->prepare("SELECT status FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        $task = $stmt->fetch();
        if ($task) {
            $newStatus = $task['status'] === 'completed' ? 'pending' : 'completed';
            $stmt = $db->prepare("UPDATE tasks SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $task_id]);
            echo json_encode(['success' => true]);
            exit;
        }
    }
}
echo json_encode(['success' => false]); 