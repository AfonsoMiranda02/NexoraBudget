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
    SELECT u.*, eu.enterprise_id 
    FROM users u 
    LEFT JOIN enterprise_users eu ON u.id = eu.user_id 
    WHERE u.id = ? AND u.is_master = 0
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: list.php');
    exit();
}

// Buscar todas as empresas para o select
$stmt = $db->query("SELECT id, name FROM enterprises ORDER BY name");
$enterprises = $stmt->fetchAll();

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';
    $enterprise_id = $_POST['enterprise_id'] ?? null;
    $cc = $_POST['cc'] ?? '';
    $address = $_POST['address'] ?? '';
    $interests = $_POST['interests'] ?? '';
    $password = $_POST['password'] ?? '';

    $errors = [];

    // Validações
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($username)) $errors[] = "Username is required";
    if (empty($phone)) $errors[] = "Phone is required";

    // Verificar se email já existe (exceto para o próprio usuário)
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) $errors[] = "Email already exists";

    // Verificar se username já existe (exceto para o próprio usuário)
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetch()) $errors[] = "Username already exists";

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // Atualizar usuário
            $query = "
                UPDATE users 
                SET name = ?, 
                    email = ?, 
                    username = ?, 
                    phone = ?, 
                    role = ?, 
                    status = ?, 
                    cc = ?, 
                    address = ?, 
                    interests = ?,
                    updated_at = NOW()
            ";
            $params = [
                $name,
                $email,
                $username,
                $phone,
                $role,
                $status,
                $cc,
                $address,
                $interests
            ];

            // Se uma nova senha foi fornecida, atualizar
            if (!empty($password)) {
                $query .= ", password = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }

            $query .= " WHERE id = ?";
            $params[] = $user_id;

            $stmt = $db->prepare($query);
            $stmt->execute($params);

            // Atualizar vínculo com empresa
            $stmt = $db->prepare("DELETE FROM enterprise_users WHERE user_id = ?");
            $stmt->execute([$user_id]);

            if ($enterprise_id) {
                $stmt = $db->prepare("
                    INSERT INTO enterprise_users (enterprise_id, user_id, role, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$enterprise_id, $user_id, $role]);
            }

            $db->commit();
            header('Location: list.php?success=2');
            exit();
        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = "Error updating user: " . $e->getMessage();
        }
    }
}
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Edit User</h1>
        <a href="list.php" class="btn btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Back to List
        </a>
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
        <form method="POST" class="user-form">
            <div class="form-grid">
                <!-- Informações Pessoais -->
                <div class="form-section">
                    <h2>Personal Information</h2>
                    
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone *</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="cc">CC</label>
                        <input type="text" id="cc" name="cc" value="<?php echo htmlspecialchars($user['cc']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="interests">Interests</label>
                        <textarea id="interests" name="interests" rows="3"><?php echo htmlspecialchars($user['interests']); ?></textarea>
                    </div>
                </div>

                <!-- Configurações da Conta -->
                <div class="form-section">
                    <h2>Account Settings</h2>

                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select id="role" name="role" required>
                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="enterprise_id">Associated Enterprise</label>
                        <select id="enterprise_id" name="enterprise_id">
                            <option value="">Select an enterprise</option>
                            <?php foreach ($enterprises as $enterprise): ?>
                                <option value="<?php echo $enterprise['id']; ?>" <?php echo $user['enterprise_id'] == $enterprise['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($enterprise['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="list.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-container {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.form-section {
    padding: 20px;
    background: #f9fafb;
    border-radius: 8px;
}

.form-section h2 {
    font-size: 1.2rem;
    color: #1a1a1a;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e7eb;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #4b5563;
    font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.95rem;
    transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-danger {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #b91c1c;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-section {
        padding: 15px;
    }
}
</style>

<?php require_once '../../components/footer.php'; ?> 