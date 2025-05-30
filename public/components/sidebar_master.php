<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar-master">
    <div class="sidebar-header">
        <img src="<?php echo BASE_URL; ?>/public/imgs/logo.png" alt="NexoraHub" class="sidebar-logo">
        <h2>Master Admin</h2>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <h3>Dashboard</h3>
            <a href="master_dashboard.php" class="<?php echo $current_page === 'master_dashboard.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                Dashboard
            </a>
        </div>

        <div class="nav-section">
            <h3>User Management</h3>
            <a href="users/list.php" class="<?php echo strpos($current_page, 'users/') === 0 ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Users
            </a>
            <a href="users/create.php" class="<?php echo $current_page === 'create.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add User
            </a>
        </div>

        <div class="nav-section">
            <h3>Enterprise Management</h3>
            <a href="enterprises/list.php" class="<?php echo strpos($current_page, 'enterprises/') === 0 ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 21h18"></path>
                    <path d="M3 7v1a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V7"></path>
                    <path d="M3 7h18"></path>
                    <path d="M8 7V3a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v4"></path>
                </svg>
                Enterprises
            </a>
            <a href="enterprises/create.php" class="<?php echo $current_page === 'create.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Enterprise
            </a>
        </div>

        <div class="nav-section">
            <h3>Project Management</h3>
            <a href="projects/list.php" class="<?php echo strpos($current_page, 'projects/') === 0 ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                </svg>
                Projects
            </a>
            <a href="projects/create.php" class="<?php echo $current_page === 'create.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Project
            </a>
        </div>

        <div class="nav-section">
            <h3>Budget Management</h3>
            <a href="budgets/list.php" class="<?php echo strpos($current_page, 'budgets/') === 0 ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 1v22"></path>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                Budgets
            </a>
            <a href="budgets/create.php" class="<?php echo $current_page === 'create.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Budget
            </a>
        </div>

        <div class="nav-section">
            <h3>System</h3>
            <a href="settings.php" class="<?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                Settings
            </a>
            <a href="logs.php" class="<?php echo $current_page === 'logs.php' ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                System Logs
            </a>
        </div>
    </nav>
</div>

<style>
.sidebar-master {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    width: 280px;
    background: white;
    border-right: 1px solid #e5e7eb;
    overflow-y: auto;
    z-index: 1000;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    text-align: center;
}

.sidebar-logo {
    width: 120px;
    height: auto;
    margin-bottom: 10px;
}

.sidebar-header h2 {
    color: #1a1a1a;
    font-size: 1.2rem;
    margin: 0;
}

.sidebar-nav {
    padding: 20px 0;
}

.nav-section {
    margin-bottom: 30px;
}

.nav-section h3 {
    color: #666;
    font-size: 0.9rem;
    text-transform: uppercase;
    padding: 0 20px;
    margin-bottom: 10px;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #4b5563;
    text-decoration: none;
    transition: all 0.2s;
}

.sidebar-nav a:hover {
    background: #f3f4f6;
    color: #4f46e5;
}

.sidebar-nav a.active {
    background: #4f46e5;
    color: white;
}

.sidebar-nav a svg {
    width: 20px;
    height: 20px;
    margin-right: 10px;
    stroke: currentColor;
}

.sidebar-nav a.active svg {
    stroke: white;
}

/* Ajuste para o conteúdo principal */
.dashboard-container {
    margin-left: 280px;
    padding: 40px;
    max-width: calc(100% - 280px);
}
</style> 