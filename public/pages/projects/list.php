<?php
require_once '../../../config.php';
require_once '../components/header.php';
require_once '../components/sidebar_master.php';

// Verificar se é master admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_master']) || $_SESSION['is_master'] != 1) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

// Configuração da paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Configuração dos filtros
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$enterprise_id = isset($_GET['enterprise_id']) ? (int)$_GET['enterprise_id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Construir a query
$query = "
    SELECT p.*, 
           e.name as enterprise_name,
           COUNT(DISTINCT pu.user_id) as total_users,
           COUNT(DISTINCT b.id) as total_budgets
    FROM projects p
    LEFT JOIN enterprises e ON p.enterprise_id = e.id
    LEFT JOIN project_users pu ON p.id = pu.project_id
    LEFT JOIN budgets b ON p.id = b.project_id
    WHERE 1=1
";

$params = [];

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($enterprise_id) {
    $query .= " AND p.enterprise_id = ?";
    $params[] = $enterprise_id;
}

if ($status) {
    $query .= " AND p.status = ?";
    $params[] = $status;
}

$query .= " GROUP BY p.id";

// Ordenação
$allowed_sort = ['name', 'created_at', 'status', 'enterprise_name'];
$allowed_order = ['ASC', 'DESC'];

if (in_array($sort, $allowed_sort) && in_array($order, $allowed_order)) {
    $query .= " ORDER BY " . ($sort == 'enterprise_name' ? 'e.name' : "p.$sort") . " $order";
}

// Adicionar limite para paginação
$query .= " LIMIT $offset, $records_per_page";

// Executar a query
$stmt = $db->prepare($query);
$stmt->execute($params);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar total de registros para paginação
$count_query = "
    SELECT COUNT(DISTINCT p.id) as total
    FROM projects p
    LEFT JOIN enterprises e ON p.enterprise_id = e.id
    WHERE 1=1
";

$count_params = [];

if ($search) {
    $count_query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $count_params[] = "%$search%";
    $count_params[] = "%$search%";
}

if ($enterprise_id) {
    $count_query .= " AND p.enterprise_id = ?";
    $count_params[] = $enterprise_id;
}

if ($status) {
    $count_query .= " AND p.status = ?";
    $count_params[] = $status;
}

$stmt = $db->prepare($count_query);
$stmt->execute($count_params);
$total_records = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Buscar empresas para o filtro
$stmt = $db->query("SELECT id, name FROM enterprises ORDER BY name");
$enterprises = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-container">
    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1>Projects</h1>
            <div class="header-actions">
                <a href="create.php" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    New Project
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

        <!-- Filtros -->
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search projects...">
                </div>
                <div class="form-group">
                    <select name="enterprise_id">
                        <option value="">All Enterprises</option>
                        <?php foreach ($enterprises as $enterprise): ?>
                            <option value="<?php echo $enterprise['id']; ?>" <?php echo $enterprise_id == $enterprise['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($enterprise['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="sort">
                        <option value="created_at" <?php echo $sort == 'created_at' ? 'selected' : ''; ?>>Created At</option>
                        <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name</option>
                        <option value="status" <?php echo $sort == 'status' ? 'selected' : ''; ?>>Status</option>
                        <option value="enterprise_name" <?php echo $sort == 'enterprise_name' ? 'selected' : ''; ?>>Enterprise</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="order">
                        <option value="DESC" <?php echo $order == 'DESC' ? 'selected' : ''; ?>>Descending</option>
                        <option value="ASC" <?php echo $order == 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <?php if ($search || $enterprise_id || $status): ?>
                    <a href="list.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Lista de Projetos -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Enterprise</th>
                        <th>Description</th>
                        <th>Users</th>
                        <th>Budgets</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projects)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No projects found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['name']); ?></td>
                                <td><?php echo htmlspecialchars($project['enterprise_name']); ?></td>
                                <td><?php echo htmlspecialchars($project['description']); ?></td>
                                <td><?php echo $project['total_users']; ?></td>
                                <td><?php echo $project['total_budgets']; ?></td>
                                <td>
                                    <span class="badge <?php echo $project['status'] == 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo ucfirst($project['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($project['created_at'])); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="view.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-info" title="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
                                        <a href="edit.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </a>
                                        <button onclick="deleteProject(<?php echo $project['id']; ?>)" class="btn btn-sm btn-danger" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&enterprise_id=<?php echo $enterprise_id; ?>&status=<?php echo urlencode($status); ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>" class="btn btn-secondary">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&enterprise_id=<?php echo $enterprise_id; ?>&status=<?php echo urlencode($status); ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>" class="btn <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&enterprise_id=<?php echo $enterprise_id; ?>&status=<?php echo urlencode($status); ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>" class="btn btn-secondary">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.filters {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filter-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-size: 0.875rem;
    color: #666;
}

.form-group input,
.form-group select {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.875rem;
}

.table-responsive {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
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

.actions {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
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

.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .filter-form {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function deleteProject(id) {
    if (confirm('Are you sure you want to delete this project?')) {
        fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'An error occurred while deleting the project');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the project');
        });
    }
}
</script>

<?php require_once '../components/footer.php'; ?> 