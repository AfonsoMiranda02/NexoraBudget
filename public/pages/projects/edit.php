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
    header('Location: list.php');
    exit();
}

$project_id = (int)$_GET['id'];

// Buscar dados do projeto
$stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header('Location: list.php');
    exit();
}

// Buscar empresas para o select
$stmt = $db->query("SELECT id, name FROM enterprises WHERE status = 'active' ORDER BY name");
$enterprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $enterprise_id = (int)$_POST['enterprise_id'];
    $status = $_POST['status'];

    $errors = [];

    // Validações
    if (empty($name)) {
        $errors[] = 'Name is required';
    }

    if (empty($enterprise_id)) {
        $errors[] = 'Enterprise is required';
    }

    if (empty($status)) {
        $errors[] = 'Status is required';
    }

    // Se não houver erros, atualizar o projeto
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                UPDATE projects 
                SET name = ?, description = ?, enterprise_id = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $enterprise_id, $status, $project_id]);

            $_SESSION['success'] = 'Project updated successfully';
            header('Location: list.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = 'An error occurred while updating the project';
        }
    }
}
?>

<div class="dashboard-container">
    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1>Edit Project</h1>
            <div class="header-actions">
                <a href="list.php" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" class="form">
                <div class="form-section">
                    <h2>Basic Information</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($project['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="enterprise_id">Enterprise *</label>
                            <select id="enterprise_id" name="enterprise_id" required>
                                <option value="">Select an enterprise</option>
                                <?php foreach ($enterprises as $enterprise): ?>
                                    <option value="<?php echo $enterprise['id']; ?>" <?php echo $project['enterprise_id'] == $enterprise['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($enterprise['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="">Select a status</option>
                                <option value="active" <?php echo $project['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $project['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($project['description']); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Project</button>
                    <a href="list.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.form-container {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 2rem;
}

.form-section h2 {
    margin: 0 0 1rem 0;
    font-size: 1.25rem;
    color: #333;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    font-size: 0.875rem;
    color: #666;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.875rem;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
}

.alert-danger {
    background: #fbe9e7;
    color: #d32f2f;
    border: 1px solid #ffcdd2;
}

.alert ul {
    margin: 0;
    padding-left: 1.5rem;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .form-actions .btn {
        width: 100%;
    }
}
</style>

<?php require_once '../components/footer.php'; ?> 