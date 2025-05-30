<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../php/db/functions.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}
require_once __DIR__ . '/../components/header_dashboard.php';

$projectId = $_GET['id'] ?? null;
if (!$projectId) {
    header('Location: ' . BASE_URL . '/public/pages/projects.php');
    exit();
}

$userData = getUserData();
if (!$userData || !isset($userData['enterprise_id'])) {
    header('Location: ' . BASE_URL . '/public/pages/projects.php');
    exit();
}

$db = getDBConnection();
if (!$db) {
    header('Location: ' . BASE_URL . '/public/pages/projects.php');
    exit();
}

try {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ? AND enterprise_id = ?");
    $stmt->execute([$projectId, $userData['enterprise_id']]);
    $project = $stmt->fetch();
    if (!$project) {
        header('Location: ' . BASE_URL . '/public/pages/projects.php');
        exit();
    }

    // Buscar membros do projeto
    $stmt = $db->prepare("SELECT pm.*, eu.name FROM project_members pm JOIN enterprise_users eu ON pm.user_id = eu.id WHERE pm.project_id = ?");
    $stmt->execute([$projectId]);
    $members = $stmt->fetchAll();

    // Buscar tarefas do projeto
    $stmt = $db->prepare("SELECT * FROM tasks WHERE project_id = ?");
    $stmt->execute([$projectId]);
    $tasks = $stmt->fetchAll();

    // Calcular percentagem de conclusão
    $totalTasks = count($tasks);
    $completedTasks = count(array_filter($tasks, function($task) { return $task['status'] === 'completed'; }));
    $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

    // Atualizar a percentagem de conclusão no banco de dados
    $stmt = $db->prepare("UPDATE projects SET progress = ? WHERE id = ?");
    $stmt->execute([$progress, $projectId]);
    $project['progress'] = $progress;
} catch (PDOException $e) {
    error_log("Error fetching project details: " . $e->getMessage());
    header('Location: ' . BASE_URL . '/public/pages/projects.php');
    exit();
}
?>

<main class="main-content" id="mainContent">
    <div class="dashboard-container">
        <div class="dashboard-box">
            <h1>Project Details</h1>
            <p>View the details of your project below.</p>
            <div class="project-details">
                <div class="detail-group">
                    <label>Project Name</label>
                    <p><?php echo htmlspecialchars($project['name']); ?></p>
                </div>
                <div class="detail-group">
                    <label>Description</label>
                    <p><?php echo htmlspecialchars($project['description'] ?? 'No description provided.'); ?></p>
                </div>
                <div class="detail-group">
                    <label>Status</label>
                    <p><span class="<?php echo getProjectStatus($project['status']); ?>"><?php echo htmlspecialchars(ucfirst($project['status'])); ?></span></p>
                </div>
                <div class="detail-group">
                    <label>Progress</label>
                    <p><?php echo number_format($project['progress'], 2); ?>%</p>
                </div>
                <div class="detail-group">
                    <label>Start Date</label>
                    <p><?php echo htmlspecialchars($project['start_date']); ?></p>
                </div>
                <div class="detail-group">
                    <label>End Date</label>
                    <p><?php echo htmlspecialchars($project['end_date'] ?? 'Not set'); ?></p>
                </div>
            </div>

            <h2>Project Members</h2>
            <ul class="members-list">
                <?php if (empty($members)): ?>
                    <li>No members assigned.</li>
                <?php else: ?>
                    <?php foreach ($members as $member): ?>
                        <li style="display: flex; align-items: center; gap: 12px;">
                            <?php
                            $profileImage = !empty($member['profile_image'])
                                ? (strpos($member['profile_image'], 'http') === 0
                                    ? $member['profile_image']
                                    : BASE_URL . '/public/imgs/' . $member['profile_image'])
                                : (BASE_URL . '/public/imgs/user-full.png');
                            ?>
                            <img src="<?php echo $profileImage; ?>" alt="User" class="user-avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;background:#e5e7eb;">
                            <span><?php echo htmlspecialchars($member['name']); ?> - <?php echo htmlspecialchars($member['role']); ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>

            <h2>Tasks <span style="font-size:16px;color:#7c3aed;font-weight:500;margin-left:10px;">(<?php echo number_format($project['progress'], 2); ?>% complete)</span></h2>
            <form id="addTaskForm" style="margin-bottom:16px;display:flex;gap:8px;">
                <input type="text" id="newTaskTitle" name="title" placeholder="New task title" required style="flex:1;">
                <button type="submit" class="btn-edit" style="padding:8px 16px;">Add Task</button>
            </form>
            <ul class="tasks-list" id="tasksList">
                <?php if (empty($tasks)): ?>
                    <li>No tasks assigned.</li>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <li data-task-id="<?php echo $task['id']; ?>">
                            <span class="task-title <?php echo $task['status'] === 'completed' ? 'completed' : ''; ?>"><?php echo htmlspecialchars($task['title']); ?></span>
                            <span class="task-status <?php echo $task['status'] === 'completed' ? 'completed' : 'pending'; ?>">
                                <?php echo ucfirst($task['status']); ?>
                            </span>
                            <button class="toggle-status-btn btn-edit" style="padding:4px 10px;font-size:13px;">Toggle</button>
                            <button class="delete-task-btn btn-cancel" style="padding:4px 10px;font-size:13px;">Delete</button>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>

            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/public/pages/edit_project.php?id=<?php echo $projectId; ?>" class="btn-edit">Edit Project</a>
                <a href="<?php echo BASE_URL; ?>/public/pages/projects.php" class="btn-cancel">Back to Projects</a>
            </div>
        </div>
    </div>
</main>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

<style>
.dashboard-container {
    min-height: calc(100vh - 140px);
    background: #f8f9fa;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 40px 0;
}
.dashboard-box {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    padding: 40px 32px;
    max-width: 800px;
    width: 100%;
}
.dashboard-box h1 {
    font-size: 28px;
    margin-bottom: 24px;
    color: #1B1B3E;
}
.dashboard-box p {
    color: rgba(27, 27, 62, 0.8);
    font-size: 16px;
    line-height: 1.7;
    margin-bottom: 32px;
}
.project-details {
    margin-bottom: 32px;
}
.detail-group {
    margin-bottom: 20px;
}
.detail-group label {
    display: block;
    font-weight: 500;
    color: #1B1B3E;
    margin-bottom: 8px;
}
.detail-group p {
    margin: 0;
    color: rgba(27, 27, 62, 0.8);
}
.form-actions {
    display: flex;
    gap: 16px;
}
.btn-edit, .btn-cancel {
    padding: 10px 22px;
    border-radius: 6px;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}
.btn-edit {
    background: #7c3aed;
    color: #fff;
    border: none;
}
.btn-cancel {
    background: #fff;
    color: #1B1B3E;
    border: 1px solid #e5e7eb;
}
.btn-edit:hover {
    background: #6d28d9;
}
.btn-cancel:hover {
    border-color: #7c3aed;
    color: #7c3aed;
}
.members-list, .tasks-list {
    list-style: none;
    padding: 0;
    margin: 0 0 32px 0;
}
.members-list li, .tasks-list li {
    padding: 10px 0;
    border-bottom: 1px solid #f3f3f9;
}
.tasks-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.task-status {
    font-size: 14px;
    padding: 4px 8px;
    border-radius: 4px;
}
.task-status.completed {
    background: #22c55e;
    color: #fff;
}
.task-status.pending {
    background: #f59e42;
    color: #fff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar tarefa
    document.getElementById('addTaskForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var title = document.getElementById('newTaskTitle').value;
        var projectId = <?php echo json_encode($projectId); ?>;
        fetch('<?php echo BASE_URL; ?>/public/php/actions/add_task.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'project_id=' + encodeURIComponent(projectId) + '&title=' + encodeURIComponent(title)
        })
        .then(res => res.json())
        .then(data => { if(data.success) location.reload(); else alert('Error adding task'); });
    });
    // Marcar como completa/incompleta
    document.querySelectorAll('.toggle-status-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            var li = this.closest('li');
            var taskId = li.getAttribute('data-task-id');
            fetch('<?php echo BASE_URL; ?>/public/php/actions/update_task_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'task_id=' + encodeURIComponent(taskId)
            })
            .then(res => res.json())
            .then(data => { if(data.success) location.reload(); else alert('Error updating task'); });
        });
    });
    // Remover tarefa
    document.querySelectorAll('.delete-task-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if(!confirm('Delete this task?')) return;
            var li = this.closest('li');
            var taskId = li.getAttribute('data-task-id');
            fetch('<?php echo BASE_URL; ?>/public/php/actions/delete_task.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'task_id=' + encodeURIComponent(taskId)
            })
            .then(res => res.json())
            .then(data => { if(data.success) location.reload(); else alert('Error deleting task'); });
        });
    });
});
</script> 