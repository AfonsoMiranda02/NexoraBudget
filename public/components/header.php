<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__) . '/php/db/functions.php';
$userData = getUserData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexoraHub - Business Management Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <style>
    .header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 32px;
        min-height: 64px;
        background: #fff;
    }
    .logo {
        display: flex;
        align-items: center;
        margin-right: 32px;
    }
    .main-nav {
        display: flex;
        gap: 24px;
        flex: 1;
        justify-content: center;
    }
    .user-section {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .user-dropdown { position: relative; display: flex; align-items: center; gap: 8px; }
    .user-dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        background: #fff;
        min-width: 160px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.12);
        z-index: 100;
        border-radius: 8px;
        margin-top: 8px;
        padding: 8px 0;
    }
    .user-dropdown.open .user-dropdown-menu { display: block; }
    .user-dropdown-menu a {
        display: block;
        padding: 10px 20px;
        color: #1B1B3E;
        text-decoration: none;
        font-size: 15px;
        transition: background 0.2s;
    }
    .user-dropdown-menu a:hover { background: #f3f3f9; }
    .user-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; background: #e5e7eb; }
    .user-name { font-weight: 500; cursor: pointer; display: block; color: #1a1a1a; }
    .dropdown-arrow { display: inline-block; width: 16px; height: 16px; vertical-align: middle; transition: transform 0.2s; cursor: pointer; }
    .user-dropdown.open .dropdown-arrow { transform: rotate(180deg); }
    </style>
</head>
<body>
<header class="header">
    <div class="header-content">
        <a href="<?php 
            if (isLoggedIn() && isset($_SESSION['account_type'])) {
                if ($_SESSION['account_type'] === 'client') {
                    echo BASE_URL . '/public/pages/dashboard_client.php';
                } elseif ($_SESSION['account_type'] === 'enterprise') {
                    echo BASE_URL . '/public/pages/enterprise_dashboard.php';
                } elseif ($_SESSION['account_type'] === 'master') {
                    echo BASE_URL . '/public/pages/master_dashboard.php';
                } else {
                    echo BASE_URL . '/public/index.php';
                }
            } else {
                echo BASE_URL . '/public/index.php';
            }
        ?>" class="logo">
            <img src="<?php echo BASE_URL; ?>/public/imgs/logo.png" alt="NexoraHub Logo">
        </a>
        <nav class="main-nav">
            <a href="<?php echo BASE_URL; ?>/public/index.php" class="nav-btn">Home</a>
            <a href="<?php echo BASE_URL; ?>/public/pages/about.php" class="nav-btn">NexoraHub</a>
            <a href="<?php echo BASE_URL; ?>/public/pages/support.php" class="nav-btn">Support</a>
        </nav>
        <div class="user-section">
            <?php if (isLoggedIn() && $userData): ?>
                <div class="user-dropdown" id="userDropdown">
                    <?php
                    $isClientOrMaster = isset($_SESSION['account_type']) && in_array($_SESSION['account_type'], ['client', 'master']);
                    $profileImg = '';
                    if ($isClientOrMaster) {
                        $profileImg = !empty($userData['profile_image']) ? (strpos($userData['profile_image'], 'http') === 0 ? $userData['profile_image'] : BASE_URL . '/public/imgs/users/' . $userData['profile_image']) : (BASE_URL . '/public/imgs/user-full.png');
                    } else {
                        $profileImg = !empty($userData['profile_image']) ? (strpos($userData['profile_image'], 'http') === 0 ? $userData['profile_image'] : BASE_URL . '/public/imgs/' . $userData['profile_image']) : (BASE_URL . '/public/imgs/user-full.png');
                    }
                    ?>
                    <img src="<?php echo $profileImg; ?>" alt="User Profile" class="user-avatar">
                    <span class="user-name" id="userName">
                        <?php echo htmlspecialchars($userData['username'] ?? $userData['name'] ?? 'User'); ?>
                    </span>
                    <img src="<?php echo BASE_URL; ?>/public/imgs/arrow downpng.png" class="dropdown-arrow" id="dropdownArrow" alt="Dropdown Arrow">
                    <div class="user-dropdown-menu" id="userDropdownMenu">
                        <a href="<?php echo (isset($_SESSION['account_type']) && $_SESSION['account_type'] === 'client') ? (BASE_URL . '/public/pages/client_settings.php') : '#'; ?>">Defenitions</a>
                        <a href="<?php echo BASE_URL; ?>/public/php/auth/logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="login-section">
                    <a href="<?php echo BASE_URL; ?>/public/pages/login.php" class="login-link">Login</a>
                    <span>/</span>
                    <a href="<?php echo BASE_URL; ?>/public/pages/register.php" class="register-link">Register</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var dropdown = document.getElementById('userDropdown');
    var menu = document.getElementById('userDropdownMenu');
    var name = document.getElementById('userName');
    var arrow = document.getElementById('dropdownArrow');
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
});
</script> 