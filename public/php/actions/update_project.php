<?php
require_once dirname(__DIR__, 3) . '/config.php';
require_once dirname(__DIR__, 2) . '/php/db/functions.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/public/pages/projects.php');
    exit();
}

$userData = getUserData();
if (!$userData || !isset($userData['enterprise_id'])) {
    header('Location: ' . BASE_URL . '/public/pages/projects.php');
    exit();
}

$projectId = $_POST['projectId'] ?? null;
$projectName = $_POST['projectName'] ?? '';
$projectDescription = $_POST['projectDescription'] ?? '';
$projectStatus = $_POST['projectStatus'] ?? '';
$startDate = $_POST['startDate'] ?? '';
$endDate = $_POST['endDate'] ?? '';

if (empty($projectId) || empty($projectName) || empty($projectStatus) || empty($startDate)) {
    header('Location: ' . BASE_URL . '/public/pages/edit_project.php?id=' . $projectId . '&error=missing_fields');
    exit();
}

$db = getDBConnection();
if (!$db) {
    header('Location: ' . BASE_URL . '/public/pages/edit_project.php?id=' . $projectId . '&error=db_error');
    exit();
}

try {
    $stmt = $db->prepare("UPDATE projects SET name = ?, description = ?, status = ?, start_date = ?, end_date = ? WHERE id = ? AND enterprise_id = ?");
    $stmt->execute([$projectName, $projectDescription, $projectStatus, $startDate, $endDate, $projectId, $userData['enterprise_id']]);
    header('Location: ' . BASE_URL . '/public/pages/projects.php?success=project_updated');
} catch (PDOException $e) {
    error_log("Error updating project: " . $e->getMessage());
    header('Location: ' . BASE_URL . '/public/pages/edit_project.php?id=' . $projectId . '&error=db_error');
}
exit(); 