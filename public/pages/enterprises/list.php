<?php
require_once '../../../config.php';
require_once '../../components/header.php';
require_once '../../components/sidebar_master.php';

// Verificar se é master admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_master']) || $_SESSION['is_master'] != 1) {
    header('Location: ../../index.php');
    exit();
}

// Configuração da paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Configuração da busca e filtros
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'name';
$order = $_GET['order'] ?? 'asc';

// Construir a query base
$query = "
    SELECT e.*, 
           COUNT(DISTINCT eu.user_id) as total_users,
           COUNT(DISTINCT p.id) as total_projects,
           COUNT(DISTINCT b.id) as total_budgets
    FROM enterprises e
    LEFT JOIN enterprise_users eu ON e.id = eu.enterprise_id
    LEFT JOIN projects p ON e.id = p.enterprise_id
    LEFT JOIN budgets b ON e.id = b.enterprise_id
";

$where = [];
$params = [];

// Adicionar condições de busca
if (!empty($search)) {
    $where[] = "(e.name LIKE ? OR e.email LIKE ? OR e.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status)) {
    $where[] = "e.status = ?";
    $params[] = $status;
}

// Adicionar WHERE se houver condições
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

// Adicionar GROUP BY
$query .= " GROUP BY e.id";

// Adicionar ORDER BY
$query .= " ORDER BY e.$sort $order";

// Query para contar total de registros
$count_query = str_replace("SELECT e.*, COUNT(DISTINCT eu.user_id) as total_users, COUNT(DISTINCT p.id) as total_projects, COUNT(DISTINCT b.id) as total_budgets", "SELECT COUNT(DISTINCT e.id) as total", $query);
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_records = $stmt->fetch()['total'];
$total_pages = ceil($total_records / $per_page);

// Adicionar LIMIT
$query .= " LIMIT $offset, $per_page";

// Executar query principal
$stmt = $db->prepare($query);
$stmt->execute($params);
$enterprises = $stmt->fetchAll();
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Enterprises</h1>
        <a href="create.php" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            New Enterprise
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php
            switch ($_GET['success']) {
                case '1':
                    echo "Enterprise created successfully!";
                    break;
                case '2':
                    echo "Enterprise updated successfully!";
                    break;
                case '3':
                    echo "Enterprise deleted successfully!";
                    break;
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="filters">
        <form method="GET" class="search-form">
            <div class="search-group">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search enterprises...">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    Search
                </button>
            </div>

            <div class="filter-group">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>

                <select name="sort" onchange="this.form.submit()">
                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Sort by Name</option>
                    <option value="created_at" <?php echo $sort === 'created_at' ? 'selected' : ''; ?>>Sort by Date</option>
                    <option value="total_users" <?php echo $sort === 'total_users' ? 'selected' : ''; ?>>Sort by Users</option>
                    <option value="total_projects" <?php echo $sort === 'total_projects' ? 'selected' : ''; ?>>Sort by Projects</option>
                </select>

                <select name="order" onchange="this.form.submit()">
                    <option value="asc" <?php echo $order === 'asc' ? 'selected' : ''; ?>>Ascending</option>
                    <option value="desc" <?php echo $order === 'desc' ? 'selected' : ''; ?>>Descending</option>
                </select>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Users</th>
                    <th>Projects</th>
                    <th>Budgets</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($enterprises)): ?>
                    <tr>
                        <td colspan="9" class="text-center">No enterprises found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($enterprises as $enterprise): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($enterprise['name']); ?></td>
                            <td><?php echo htmlspecialchars($enterprise['email']); ?></td>
                            <td><?php echo htmlspecialchars($enterprise['phone']); ?></td>
                            <td><?php echo $enterprise['total_users']; ?></td>
                            <td><?php echo $enterprise['total_projects']; ?></td>
                            <td><?php echo $enterprise['total_budgets']; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $enterprise['status'] === 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($enterprise['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($enterprise['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="view.php?id=<?php echo $enterprise['id']; ?>" class="btn btn-sm btn-outline" title="View">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                    <a href="edit.php?id=<?php echo $enterprise['id']; ?>" class="btn btn-sm btn-outline" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                    <button onclick="deleteEnterprise(<?php echo $enterprise['id']; ?>)" class="btn btn-sm btn-outline btn-danger" title="Delete">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>" class="btn btn-outline">
                    Previous
                </a>
            <?php endif; ?>

            <span class="page-info">
                Page <?php echo $page; ?> of <?php echo $total_pages; ?>
            </span>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>" class="btn btn-outline">
                    Next
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.filters {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.search-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.search-group {
    display: flex;
    gap: 10px;
}

.search-group input {
    flex: 1;
    padding: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.95rem;
}

.filter-group {
    display: flex;
    gap: 10px;
}

.filter-group select {
    padding: 8px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.95rem;
    background: white;
}

.table-responsive {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-success {
    background: #dcfce7;
    color: #16a34a;
}

.badge-danger {
    background: #fee2e2;
    color: #dc2626;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.btn-sm svg {
    width: 16px;
    height: 16px;
}

.btn-danger {
    color: #dc2626;
}

.btn-danger:hover {
    background: #fee2e2;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
    margin-top: 20px;
}

.page-info {
    color: #6b7280;
    font-size: 0.95rem;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #dcfce7;
    border: 1px solid #bbf7d0;
    color: #16a34a;
}

.text-center {
    text-align: center;
}

@media (max-width: 768px) {
    .search-group,
    .filter-group {
        flex-direction: column;
    }

    .action-buttons {
        flex-direction: column;
    }
}
</style>

<script>
function deleteEnterprise(id) {
    if (confirm('Are you sure you want to delete this enterprise? This action cannot be undone.')) {
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
                alert(data.error || 'Error deleting enterprise');
            }
        })
        .catch(error => {
            alert('Error deleting enterprise');
        });
    }
}
</script>

<?php require_once '../../components/footer.php'; ?> 