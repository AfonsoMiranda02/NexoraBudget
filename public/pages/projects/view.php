<?php
require_once '../../../config.php';
require_once '../components/header.php';
require_once '../components/sidebar_master.php';

// Verificar se é master admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_master']) || $_SESSION['is_master'] != 1) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . '/public/pages/projects/list.php');
    exit();
}

$project_id = (int)$_GET['id'];

// Buscar dados do projeto
$stmt = $db->prepare("
    SELECT p.*, 
           e.name as enterprise_name,
           COUNT(DISTINCT pu.user_id) as total_users,
           COUNT(DISTINCT b.id) as total_budgets
    FROM projects p
    LEFT JOIN enterprises e ON p.enterprise_id = e.id
    LEFT JOIN project_users pu ON p.id = pu.project_id
    LEFT JOIN budgets b ON p.id = b.project_id
    WHERE p.id = ?
    GROUP BY p.id
");
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header('Location: ' . BASE_URL . '/public/pages/projects/list.php');
    exit();
}

// Buscar usuários do projeto
$stmt = $db->prepare("
    SELECT u.*, pu.role as project_role
    FROM users u
    JOIN project_users pu ON u.id = pu.user_id
    WHERE pu.project_id = ?
    ORDER BY u.name
");
$stmt->execute([$project_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar orçamentos do projeto
$stmt = $db->prepare("
    SELECT b.*, 
           COUNT(DISTINCT bu.user_id) as total_users
    FROM budgets b
    LEFT JOIN budget_users bu ON b.id = bu.budget_id
    WHERE b.project_id = ?
    GROUP BY b.id
    ORDER BY b.created_at DESC
");
$stmt->execute([$project_id]);
$budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-container">
    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1><?php echo htmlspecialchars($project['name']); ?></h1>
            <div class="header-actions">
                <a href="edit.php?id=<?php echo $project_id; ?>" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    Edit Project
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- Informações Básicas -->
            <div class="dashboard-card">
                <h2>Basic Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Name</label>
                        <span><?php echo htmlspecialchars($project['name']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Enterprise</label>
                        <span><?php echo htmlspecialchars($project['enterprise_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Status</label>
                        <span class="badge <?php echo $project['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo ucfirst($project['status']); ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <label>Created At</label>
                        <span><?php echo date('d/m/Y H:i', strtotime($project['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Descrição -->
            <div class="dashboard-card">
                <h2>Description</h2>
                <div class="info-grid">
                    <div class="info-item full-width">
                        <span><?php echo nl2br(htmlspecialchars($project['description'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="dashboard-card">
                <h2>Statistics</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Total Users</label>
                        <span><?php echo $project['total_users']; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Total Budgets</label>
                        <span><?php echo $project['total_budgets']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Usuários -->
            <div class="dashboard-card">
                <h2>Users</h2>
                <?php if (empty($users)): ?>
                    <p class="empty-state">No users found</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo ucfirst($user['project_role']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $user['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Orçamentos -->
            <div class="dashboard-card">
                <h2>Budgets</h2>
                <?php if (empty($budgets)): ?>
                    <p class="empty-state">No budgets found</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Users</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($budgets as $budget): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($budget['name']); ?></td>
                                        <td><?php echo htmlspecialchars($budget['description']); ?></td>
                                        <td><?php echo number_format($budget['amount'], 2); ?></td>
                                        <td><?php echo $budget['total_users']; ?></td>
                                        <td>
                                            <span class="badge <?php echo $budget['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo ucfirst($budget['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.dashboard-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.dashboard-card h2 {
    margin: 0 0 1rem 0;
    font-size: 1.25rem;
    color: #333;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-item label {
    font-size: 0.875rem;
    color: #666;
}

.info-item span {
    color: #333;
}

.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

th, td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

th {
    font-weight: 600;
    color: #666;
}

.empty-state {
    text-align: center;
    color: #666;
    padding: 2rem;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-success {
    background: #e6f4ea;
    color: #1e7e34;
}

.badge-danger {
    background: #fbe9e7;
    color: #d32f2f;
}

@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once '../components/footer.php'; ?> 