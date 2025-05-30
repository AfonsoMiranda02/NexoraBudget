<?php
require_once '../../../config.php';
require_once '../../components/header.php';
require_once '../../components/sidebar_master.php';

// Verificar se é master admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_master']) || $_SESSION['is_master'] != 1) {
    header('Location: ../../index.php');
    exit();
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit();
}

$user_id = (int)$_GET['id'];

// Buscar dados do usuário
$stmt = $db->prepare("
    SELECT u.*, eu.enterprise_id, e.name as enterprise_name
    FROM users u 
    LEFT JOIN enterprise_users eu ON u.id = eu.user_id 
    LEFT JOIN enterprises e ON eu.enterprise_id = e.id
    WHERE u.id = ? AND u.is_master = 0
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: list.php');
    exit();
}

// Buscar projetos do usuário
$stmt = $db->prepare("
    SELECT p.*, pu.role as user_role
    FROM projects p
    JOIN project_users pu ON p.id = pu.project_id
    WHERE pu.user_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll();

// Buscar orçamentos do usuário
$stmt = $db->prepare("
    SELECT b.*, bu.role as user_role
    FROM budgets b
    JOIN budget_users bu ON b.id = bu.budget_id
    WHERE bu.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$budgets = $stmt->fetchAll();
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>User Details</h1>
        <div class="header-actions">
            <a href="edit.php?id=<?php echo $user_id; ?>" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Edit User
            </a>
            <a href="list.php" class="btn btn-outline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <div class="content-grid">
        <!-- Informações Pessoais -->
        <div class="content-section">
            <h2>Personal Information</h2>
            <div class="info-grid">
                <div class="info-group">
                    <label>Full Name</label>
                    <div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div>
                </div>
                <div class="info-group">
                    <label>Email</label>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="info-group">
                    <label>Username</label>
                    <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                <div class="info-group">
                    <label>Phone</label>
                    <div class="info-value"><?php echo htmlspecialchars($user['phone']); ?></div>
                </div>
                <div class="info-group">
                    <label>CC</label>
                    <div class="info-value"><?php echo htmlspecialchars($user['cc']); ?></div>
                </div>
                <div class="info-group">
                    <label>Address</label>
                    <div class="info-value"><?php echo nl2br(htmlspecialchars($user['address'])); ?></div>
                </div>
                <div class="info-group">
                    <label>Interests</label>
                    <div class="info-value"><?php echo nl2br(htmlspecialchars($user['interests'])); ?></div>
                </div>
            </div>
        </div>

        <!-- Configurações da Conta -->
        <div class="content-section">
            <h2>Account Settings</h2>
            <div class="info-grid">
                <div class="info-group">
                    <label>Role</label>
                    <div class="info-value">
                        <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'primary' : 'secondary'; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </div>
                </div>
                <div class="info-group">
                    <label>Status</label>
                    <div class="info-value">
                        <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                    </div>
                </div>
                <div class="info-group">
                    <label>Associated Enterprise</label>
                    <div class="info-value">
                        <?php if ($user['enterprise_name']): ?>
                            <a href="../enterprises/view.php?id=<?php echo $user['enterprise_id']; ?>" class="link">
                                <?php echo htmlspecialchars($user['enterprise_name']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">None</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-group">
                    <label>Created At</label>
                    <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></div>
                </div>
                <div class="info-group">
                    <label>Last Update</label>
                    <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($user['updated_at'])); ?></div>
                </div>
            </div>
        </div>

        <!-- Projetos -->
        <div class="content-section">
            <h2>Projects</h2>
            <?php if (empty($projects)): ?>
                <p class="text-muted">No projects found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($project['name']); ?></td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?php echo ucfirst($project['user_role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $project['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($project['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($project['created_at'])); ?></td>
                                    <td>
                                        <a href="../projects/view.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Orçamentos -->
        <div class="content-section">
            <h2>Budgets</h2>
            <?php if (empty($budgets)): ?>
                <p class="text-muted">No budgets found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($budgets as $budget): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($budget['name']); ?></td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?php echo ucfirst($budget['user_role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $budget['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($budget['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($budget['created_at'])); ?></td>
                                    <td>
                                        <a href="../budgets/view.php?id=<?php echo $budget['id']; ?>" class="btn btn-sm btn-outline">
                                            View
                                        </a>
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

<style>
.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.content-section {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.content-section h2 {
    font-size: 1.2rem;
    color: #1a1a1a;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e7eb;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.info-group {
    margin-bottom: 15px;
}

.info-group label {
    display: block;
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 5px;
}

.info-value {
    font-size: 1rem;
    color: #1a1a1a;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-primary {
    background: #e0e7ff;
    color: #4f46e5;
}

.badge-secondary {
    background: #f3f4f6;
    color: #4b5563;
}

.badge-success {
    background: #dcfce7;
    color: #16a34a;
}

.badge-danger {
    background: #fee2e2;
    color: #dc2626;
}

.link {
    color: #4f46e5;
    text-decoration: none;
}

.link:hover {
    text-decoration: underline;
}

.text-muted {
    color: #6b7280;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.table th {
    font-weight: 500;
    color: #4b5563;
    background: #f9fafb;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .content-grid {
        grid-template-columns: 1fr;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once '../../components/footer.php'; ?> 