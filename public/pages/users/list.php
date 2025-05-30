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
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Construir a query base
$query = "SELECT u.*, eu.enterprise_id, e.name as enterprise_name 
          FROM users u 
          LEFT JOIN enterprise_users eu ON u.id = eu.user_id 
          LEFT JOIN enterprises e ON eu.enterprise_id = e.id 
          WHERE u.is_master = 0";

$count_query = "SELECT COUNT(*) FROM users u WHERE u.is_master = 0";
$params = [];

// Adicionar filtros
if ($search) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
    $count_query .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if ($role_filter) {
    $query .= " AND u.role = ?";
    $count_query .= " AND u.role = ?";
    $params[] = $role_filter;
}

if ($status_filter) {
    $query .= " AND u.status = ?";
    $count_query .= " AND u.status = ?";
    $params[] = $status_filter;
}

// Adicionar ordenação e paginação
$query .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

// Executar as queries
$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

$stmt = $db->prepare($count_query);
$stmt->execute(array_slice($params, 0, -2));
$total_users = $stmt->fetchColumn();
$total_pages = ceil($total_users / $per_page);
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>User Management</h1>
        <a href="create.php" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add New User
        </a>
    </div>

    <!-- Filtros e Busca -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="search-box">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search users...">
                <button type="submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </div>

            <div class="filter-group">
                <select name="role">
                    <option value="">All Roles</option>
                    <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>User</option>
                </select>

                <select name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>

                <button type="submit" class="btn btn-secondary">Apply Filters</button>
                <a href="list.php" class="btn btn-outline">Clear Filters</a>
            </div>
        </form>
    </div>

    <!-- Lista de Usuários -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Enterprise</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <div class="user-info">
                            <img src="<?php echo $user['profile_image'] ?: BASE_URL . '/public/imgs/default-avatar.png'; ?>" alt="Profile" class="user-avatar">
                            <span><?php echo htmlspecialchars($user['name']); ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'primary' : 'secondary'; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($user['enterprise_name']): ?>
                            <span class="enterprise-name"><?php echo htmlspecialchars($user['enterprise_name']); ?></span>
                        <?php else: ?>
                            <span class="text-muted">No enterprise</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-icon" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="btn btn-icon btn-danger" title="Delete">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>" class="btn btn-outline">Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>" 
               class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>" class="btn btn-outline">Next</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-header h1 {
    font-size: 2rem;
    color: #1a1a1a;
    margin: 0;
}

.filters-section {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filters-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.search-box {
    position: relative;
    max-width: 400px;
}

.search-box input {
    width: 100%;
    padding: 12px 45px 12px 15px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
}

.search-box button {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
}

.search-box button svg {
    width: 20px;
    height: 20px;
}

.filter-group {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

.filter-group select {
    padding: 8px 15px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.9rem;
    min-width: 150px;
}

.table-container {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.data-table th {
    font-weight: 600;
    color: #4b5563;
    background: #f9fafb;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.badge-primary {
    background: #4f46e5;
    color: white;
}

.badge-secondary {
    background: #6b7280;
    color: white;
}

.badge-success {
    background: #10b981;
    color: white;
}

.badge-danger {
    background: #ef4444;
    color: white;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-icon {
    padding: 6px;
    border-radius: 6px;
    color: #4b5563;
    transition: all 0.2s;
}

.btn-icon:hover {
    background: #f3f4f6;
    color: #4f46e5;
}

.btn-icon.btn-danger:hover {
    background: #fee2e2;
    color: #ef4444;
}

.btn-icon svg {
    width: 18px;
    height: 18px;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 30px;
}

.btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #4f46e5;
    color: white;
}

.btn-primary:hover {
    background: #4338ca;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-outline {
    border: 1px solid #e5e7eb;
    color: #4b5563;
}

.btn-outline:hover {
    background: #f3f4f6;
}

.text-muted {
    color: #9ca3af;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }

    .filter-group {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-group select {
        width: 100%;
    }

    .data-table {
        font-size: 0.9rem;
    }

    .action-buttons {
        flex-direction: column;
    }
}
</style>

<script>
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch(`delete.php?id=${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error deleting user: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the user');
        });
    }
}
</script>

<?php require_once '../../components/footer.php'; ?> 