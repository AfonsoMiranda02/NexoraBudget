<?php
require_once '../../../config.php';
require_once '../../php/db/functions.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDBConnection();
    $project_id = $_POST['project_id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    if ($db && $project_id && $title) {
        $stmt = $db->prepare("INSERT INTO tasks (project_id, title, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$project_id, $title]);
        echo json_encode(['success' => true]);
        exit;
    }
}
echo json_encode(['success' => false]); 