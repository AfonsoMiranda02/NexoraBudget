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

$projectName = $_POST['projectName'] ?? '';
$projectDescription = $_POST['projectDescription'] ?? '';
$projectStatus = $_POST['projectStatus'] ?? '';
$startDate = $_POST['startDate'] ?? '';
$endDate = $_POST['endDate'] ?? '';

if (empty($projectName) || empty($projectStatus) || empty($startDate)) {
    header('Location: ' . BASE_URL . '/public/pages/create_project.php?error=missing_fields');
    exit();
}

$db = getDBConnection();
if (!$db) {
    header('Location: ' . BASE_URL . '/public/pages/create_project.php?error=db_error');
    exit();
}

try {
    $stmt = $db->prepare("INSERT INTO projects (enterprise_id, name, description, status, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userData['enterprise_id'], $projectName, $projectDescription, $projectStatus, $startDate, $endDate]);
    header('Location: ' . BASE_URL . '/public/pages/projects.php?success=project_created');
} catch (PDOException $e) {
    error_log("Error creating project: " . $e->getMessage());
    header('Location: ' . BASE_URL . '/public/pages/create_project.php?error=db_error');
}
exit(); 