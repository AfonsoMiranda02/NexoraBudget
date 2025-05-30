<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__) . '/php/db/functions.php';
$userData = getUserData();
$enterpriseData = null;
if ($userData && isset($userData['enterprise_id'])) {
    $enterpriseData = getEnterpriseData($userData['enterprise_id']);
}
$planLabel = '';
if ($userData && isset($userData['plan'])) {
    if ($userData['plan'] == 0) $planLabel = 'Standard Plan Activated';
    elseif ($userData['plan'] == 1) $planLabel = 'Plus Plan Activated';
    elseif ($userData['plan'] == 2) $planLabel = 'Premium Plan Activated';
}
// Buscar últimas notificações (atividades)
$notifications = [];
if ($userData && isset($userData['enterprise_id'])) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM activities WHERE enterprise_id = ? ORDER BY id DESC LIMIT 5");
    $stmt->execute([$userData['enterprise_id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexoraHub - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <style>
    html, body {
        height: 100% !important;
        overflow-x: hidden !important;
        position: static !important;
    }
    body {
        min-height: 100vh !important;
        position: static !important;
    }
    .dashboard-header { background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 0 40px; height: 70px; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #e5e7eb; }
    .dashboard-logo img { height: 38px; }
    .header-center { display: flex; align-items: center; gap: 10px; margin-left: 40px; }
    .dashboard-search { display: flex; justify-content: center; }
    .dashboard-search input { width: 240px; padding: 8px 16px; border-radius: 8px; border: 1px solid #e5e7eb; font-size: 15px; }
    .company-info { display: flex; align-items: center; gap: 10px; margin: 0; }
    .company-info img { width: 22px; height: 22px; }
    .user-role { font-weight: 500; color: #7c3aed; font-size: 15px; }
    .company-name { font-weight: 500; color: #1B1B3E; font-size: 15px; }
    .dashboard-actions { display: flex; align-items: center; gap: 24px; margin-left: auto; }
    .dashboard-notification { font-size: 22px; color: #7c3aed; cursor: pointer; position: relative; }
    .user-dropdown { position: relative; display: inline-block; }
    .user-dropdown-menu { display: none; position: absolute; right: 0; background: #fff; min-width: 160px; box-shadow: 0 8px 16px rgba(0,0,0,0.12); z-index: 100; border-radius: 8px; margin-top: 65px; padding: 8px 0; }
    .user-dropdown.open .user-dropdown-menu { display: block; }
    .user-dropdown-menu a { display: block; padding: 10px 20px; color: #1B1B3E; text-decoration: none; font-size: 15px; transition: background 0.2s; }
    .user-dropdown-menu a:hover { background: #f3f3f9; }
    .user-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; background: #e5e7eb; margin-right: 10px; }
    .user-name { font-weight: 500; margin-right: 8px; cursor: pointer; }
    .user-plan-badge { background: #7c3aed; color: #fff; font-size: 13px; border-radius: 6px; padding: 3px 12px; margin-left: 8px; font-weight: 500; }
    .dropdown-arrow { display: inline-block; width: 16px; height: 16px; vertical-align: middle; transition: transform 0.2s; cursor: pointer; }
    .user-dropdown.open .dropdown-arrow { transform: rotate(180deg); }
    .hamburger-menu {
        position: fixed !important;
        left: 20px !important;
        top: 80px !important;
        z-index: 10001 !important;
        background: #fff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 8px !important;
        cursor: pointer !important;
        padding: 10px !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
        user-select: none;
    }
    .hamburger-menu.draggable {
        /* Remove transições para arrastar */
        transition: none !important;
    }
    .sidebar.open ~ .hamburger-menu,
    .hamburger-menu.fixed-position {
        left: 20px !important;
        top: 80px !important;
    }
    .hamburger-menu:hover { border-color: #7c3aed; }
    .hamburger-menu i { font-size: 24px; color: #1B1B3E; }
    .sidebar {
        position: fixed !important;
        left: 0 !important;
        top: 70px !important;
        width: 250px !important;
        height: calc(100vh - 70px) !important;
        z-index: 9999 !important;
        background: #fff !important;
        box-shadow: 2px 0 0 rgba(0,0,0,0.1) !important;
        padding: 20px !important;
        overflow-y: auto !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: flex-start !important;
        transform: translateX(-100%) !important;
        transition: transform 0.3s ease !important;
    }
    .sidebar.open {
        transform: translateX(0) !important;
    }
    .sidebar ul { list-style: none; padding: 0; margin-top: 60px; }
    .sidebar ul li { margin-bottom: 20px; }
    .sidebar ul li a { 
        color: #1B1B3E; 
        text-decoration: none; 
        font-size: 16px; 
        transition: all 0.3s ease;
        padding: 8px 12px;
        border-radius: 6px;
        display: block;
        position: relative;
    }
    .sidebar ul li a:hover { 
        color: #7c3aed;
        background: #f9fafb;
    }
    .sidebar ul li a::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 12px;
        right: 12px;
        height: 1px;
        background: #7c3aed;
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }
    .sidebar ul li a:hover::after {
        transform: scaleX(1);
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
        transition: margin-left 0.3s ease !important;
    }
    .main-content.sidebar-open {
        margin-left: 250px !important;
    }
    @media (max-width: 900px) {
        .sidebar {
            width: 200px !important;
        }
        .main-content.sidebar-open {
            margin-left: 200px !important;
        }
        .hamburger-menu,
        .sidebar.open ~ .hamburger-menu,
        .hamburger-menu.fixed-position {
            left: 10px !important;
            top: 80px !important;
        }
    }
    @media (max-width: 600px) {
        .sidebar {
            width: 100vw !important;
            left: 0 !important;
            top: 70px !important;
            height: calc(100vh - 70px) !important;
        }
        .main-content.sidebar-open {
            margin-left: 0 !important;
        }
        .hamburger-menu,
        .sidebar.open ~ .hamburger-menu,
        .hamburger-menu.fixed-position {
            left: 8px !important;
            top: 80px !important;
        }
    }
    .notification-dropdown { display: none; position: absolute; right: 0; top: 40px; background: #fff; min-width: 320px; box-shadow: 0 8px 16px rgba(0,0,0,0.12); z-index: 2000; border-radius: 8px; padding: 0; }
    .notification-dropdown.open { display: block; }
    .notification-list { max-height: 320px; overflow-y: auto; padding: 0; margin: 0; list-style: none; }
    .notification-item { display: flex; align-items: flex-start; gap: 12px; padding: 16px 20px; border-bottom: 1px solid #f3f3f9; }
    .notification-item:last-child { border-bottom: none; }
    .notification-icon { color: #7c3aed; font-size: 18px; margin-top: 2px; }
    .notification-content { flex: 1; }
    .notification-title { font-weight: 500; color: #1B1B3E; font-size: 15px; margin-bottom: 2px; }
    .notification-desc { color: #555; font-size: 13px; margin-bottom: 2px; }
    .notification-date { color: #aaa; font-size: 12px; }
    .see-more-link { display: block; text-align: center; padding: 12px 0; color: #7c3aed; font-weight: 500; text-decoration: none; border-top: 1px solid #f3f3f9; border-radius: 0 0 8px 8px; background: #fafaff; }
    .see-more-link:hover { background: #f3f3f9; }
    </style>
</head>
<body>
<header class="dashboard-header">
    <div class="dashboard-logo">
        <a href="<?php echo BASE_URL; ?>/public/pages/dashboard_enterprise.php"><img src="<?php echo BASE_URL; ?>/public/imgs/logo.png" alt="NexoraHub Logo"></a>
    </div>
    <div class="header-center">
        <div class="dashboard-search">
            <input type="text" id="searchInput" placeholder="Search...">
        </div>
        <div class="company-info">
            <img src="<?php echo BASE_URL; ?>/public/imgs/building-line.png" alt="Company">
            <span class="user-role"><?php echo $userData ? htmlspecialchars($userData['role']) : ''; ?></span>
            <span class="company-name"><?php echo $enterpriseData ? htmlspecialchars($enterpriseData['name']) : 'Company'; ?></span>
        </div>
    </div>
    <div class="dashboard-actions">
        <div class="user-dropdown" id="userDropdown" style="display: flex; flex-direction: column; align-items: center; min-width: 180px;">
            <div style="display: flex; align-items: center; gap: 8px; flex-direction: column; min-width: 0;">
                <?php if ($planLabel): ?>
                    <div class="user-plan-badge plan-below-name">
                        <?php echo $planLabel; ?>
                    </div>
                <?php endif; ?>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <img src="<?php echo ($userData && !empty($userData['profile_image'])) ? (strpos($userData['profile_image'], 'http') === 0 ? $userData['profile_image'] : BASE_URL . '/public/imgs/' . $userData['profile_image']) : (BASE_URL . '/public/imgs/user-full.png'); ?>" alt="User Profile" class="user-avatar">
                    <span class="user-name" id="userName" style="margin-right: 0; display: block; text-align: center;">
                        <?php echo $userData ? htmlspecialchars($userData['name'] ?? 'User') : 'User'; ?>
                    </span>
                    <img src="<?php echo BASE_URL; ?>/public/imgs/arrow downpng.png" class="dropdown-arrow" id="dropdownArrow" alt="Dropdown Arrow" style="width: 16px; height: 16px;">
                </div>
            </div>
            <div class="user-dropdown-menu" id="userDropdownMenu">
                <a href="#">Defenitions</a>
                <a href="<?php echo BASE_URL; ?>/public/pages/plan_upgrade.php">Upgrade Plan</a>
                <a href="<?php echo BASE_URL; ?>/public/php/auth/logout.php">Logout</a>
            </div>
        </div>
        <span class="dashboard-notification" id="notificationBell" style="margin-left: 16px; position: relative;">
            <i class="fas fa-bell"></i>
            <div class="notification-dropdown" id="notificationDropdown">
                <ul class="notification-list">
                    <?php if (empty($notifications)): ?>
                        <li class="notification-item"><span class="notification-content">No notifications.</span></li>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                        <li class="notification-item">
                            <span class="notification-icon"><i class="fas fa-<?php echo htmlspecialchars($notif['icon']); ?>"></i></span>
                            <span class="notification-content">
                                <span class="notification-title"><?php echo htmlspecialchars($notif['title']); ?></span><br>
                                <span class="notification-desc"><?php echo htmlspecialchars($notif['description']); ?></span><br>
                                <span class="notification-date"><?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?></span>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <a href="<?php echo BASE_URL; ?>/public/pages/notifications.php" class="see-more-link">See More</a>
            </div>
        </span>
    </div>
</header>

<button class="hamburger-menu" id="hamburgerMenu">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar" id="sidebar">
    <ul>
        <li><a href="<?php echo BASE_URL; ?>/public/pages/dashboard_enterprise.php">Dashboard</a></li>
        <li><a href="<?php echo BASE_URL; ?>/public/pages/projects.php">Projects</a></li>
        <li><a href="<?php echo BASE_URL; ?>/public/pages/employees.php">Employees</a></li>
        <li><a href="<?php echo BASE_URL; ?>/public/pages/budgets.php">Budgets</a></li>
        <li><a href="<?php echo BASE_URL; ?>/public/pages/reports.php">Reports</a></li>
    </ul>
</div>

<main class="main-content" id="mainContent">
    <!-- Main content will go here -->
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dropdown = document.getElementById('userDropdown');
    var menu = document.getElementById('userDropdownMenu');
    var name = document.getElementById('userName');
    var arrow = document.getElementById('dropdownArrow');
    var hamburger = document.getElementById('hamburgerMenu');
    var sidebar = document.getElementById('sidebar');
    var mainContent = document.getElementById('mainContent');
    var notificationBell = document.getElementById('notificationBell');
    var notificationDropdown = document.getElementById('notificationDropdown');
    
    if(dropdown && menu && name && arrow) {
        function toggleDropdown(e) {
            dropdown.classList.toggle('open');
        }
        name.addEventListener('click', toggleDropdown);
        arrow.addEventListener('click', toggleDropdown);
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });
    }
    
    if(hamburger && sidebar && mainContent) {
        // Drag logic
        let isDragging = false, offsetX = 0, offsetY = 0;
        hamburger.addEventListener('mousedown', function(e) {
            if (!sidebar.classList.contains('open')) {
                isDragging = true;
                hamburger.classList.add('draggable');
                offsetX = e.clientX - hamburger.offsetLeft;
                offsetY = e.clientY - hamburger.offsetTop;
            }
        });
        document.addEventListener('mousemove', function(e) {
            if (isDragging && !sidebar.classList.contains('open')) {
                let x = e.clientX - offsetX;
                let y = e.clientY - offsetY;
                // Limites da tela
                x = Math.max(0, Math.min(window.innerWidth - hamburger.offsetWidth, x));
                y = Math.max(0, Math.min(window.innerHeight - hamburger.offsetHeight, y));
                hamburger.style.left = x + 'px';
                hamburger.style.top = y + 'px';
            }
        });
        document.addEventListener('mouseup', function() {
            if (isDragging) {
                isDragging = false;
                hamburger.classList.remove('draggable');
            }
        });

        // Quando abrir a sidebar, reseta a posição do botão
        hamburger.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            mainContent.classList.toggle('sidebar-open');
            var icon = hamburger.querySelector('i');
            if (sidebar.classList.contains('open')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
                hamburger.style.left = '';
                hamburger.style.top = '';
                hamburger.classList.add('fixed-position');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
                hamburger.classList.remove('fixed-position');
            }
        });
    }
    
    if(notificationBell && notificationDropdown) {
        notificationBell.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('open');
        });
        document.addEventListener('click', function(e) {
            if (!notificationDropdown.contains(e.target) && !notificationBell.contains(e.target)) {
                notificationDropdown.classList.remove('open');
            }
        });
    }
});
</script> 
</body>
</html> 