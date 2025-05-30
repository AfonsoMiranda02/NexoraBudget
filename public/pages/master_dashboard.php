<?php
require_once '../../config.php';

// Verificação de segurança
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

require_once '../../public/php/db/functions.php';
$db = getDBConnection();

// Verificação de permissões
$stmt = $db->prepare("SELECT is_master FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['is_master'] != 1) {
    header('Location: ../index.php');
    exit();
}

// Buscar estatísticas com tratamento de erro
try {
    $stats = [
        'total_users' => $db->query("SELECT COUNT(*) FROM users WHERE is_master = 0")->fetchColumn(),
        'total_enterprises' => $db->query("SELECT COUNT(*) FROM enterprises")->fetchColumn(),
        'total_projects' => $db->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
        'total_budgets' => $db->query("SELECT COUNT(*) FROM budgets")->fetchColumn()
    ];
} catch (PDOException $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
    $stats = [
        'total_users' => 0,
        'total_enterprises' => 0,
        'total_projects' => 0,
        'total_budgets' => 0
    ];
}

// Buscar últimas atividades com tratamento de erro
try {
    $stmt = $db->query("
        SELECT 'user' as type, name, email, created_at 
        FROM users 
        WHERE is_master = 0 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recent_users = $stmt->fetchAll();

    $stmt = $db->query("
        SELECT name, email, created_at 
        FROM enterprises 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recent_enterprises = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar atividades recentes: " . $e->getMessage());
    $recent_users = [];
    $recent_enterprises = [];
}

// Incluir componentes
require_once '../components/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Admin Dashboard - NexoraHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/master-dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/dashboard-components.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="main-content">
        <?php require_once '../components/sidebar_master.php'; ?>
        
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1>Master Admin Dashboard</h1>
                <p>Welcome, Master Admin! Here you can manage everything on the platform.</p>
            </div>

            <!-- Estatísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <p class="stat-number"><?php echo number_format($stats['total_users']); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 21h18"></path>
                            <path d="M3 7v1a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V7"></path>
                            <path d="M3 7h18"></path>
                            <path d="M8 7V3a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v4"></path>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3>Total Enterprises</h3>
                        <p class="stat-number"><?php echo number_format($stats['total_enterprises']); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3>Total Projects</h3>
                        <p class="stat-number"><?php echo number_format($stats['total_projects']); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 1v22"></path>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3>Total Budgets</h3>
                        <p class="stat-number"><?php echo number_format($stats['total_budgets']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Atividades Recentes -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Recent Activities</h2>
                </div>
                <div class="activities-grid">
                    <!-- Usuários Recentes -->
                    <div class="activity-card">
                        <h3>Recent Users</h3>
                        <div class="activity-list">
                            <?php if (empty($recent_users)): ?>
                                <p class="no-data">No recent users found.</p>
                            <?php else: ?>
                                <?php foreach ($recent_users as $user): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                    </div>
                                    <div class="activity-info">
                                        <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                                        <span class="activity-date"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Empresas Recentes -->
                    <div class="activity-card">
                        <h3>Recent Enterprises</h3>
                        <div class="activity-list">
                            <?php if (empty($recent_enterprises)): ?>
                                <p class="no-data">No recent enterprises found.</p>
                            <?php else: ?>
                                <?php foreach ($recent_enterprises as $enterprise): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 21h18"></path>
                                            <path d="M3 7v1a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V7"></path>
                                            <path d="M3 7h18"></path>
                                            <path d="M8 7V3a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v4"></path>
                                        </svg>
                                    </div>
                                    <div class="activity-info">
                                        <h4><?php echo htmlspecialchars($enterprise['name']); ?></h4>
                                        <p><?php echo htmlspecialchars($enterprise['email']); ?></p>
                                        <span class="activity-date"><?php echo date('M d, Y', strtotime($enterprise['created_at'])); ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="quick-actions">
                    <a href="manage_users.php" class="action-card">
                        <div class="action-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <h3>Manage Users</h3>
                        <p>View and manage all platform users</p>
                    </a>
                    <a href="manage_enterprises.php" class="action-card">
                        <div class="action-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 21h18"></path>
                                <path d="M3 7v1a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V7"></path>
                                <path d="M3 7h18"></path>
                                <path d="M8 7V3a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v4"></path>
                            </svg>
                        </div>
                        <h3>Manage Enterprises</h3>
                        <p>View and manage all enterprises</p>
                    </a>
                    <a href="platform_settings.php" class="action-card">
                        <div class="action-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                        </div>
                        <h3>Platform Settings</h3>
                        <p>Configure platform-wide settings</p>
                    </a>
                    <a href="system_logs.php" class="action-card">
                        <div class="action-icon">
                            <img src="<?php echo BASE_URL; ?>/public/imgs/logs.png" alt="Logs">
                        </div>
                        <h3>System Logs</h3>
                        <p>View system activity and logs</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../components/footer.php'; ?>

    <script>
    // Adicionar interatividade aos cards
    document.querySelectorAll('.action-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    </script>
</body>
</html> 