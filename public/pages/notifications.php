<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../php/db/functions.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}
require_once __DIR__ . '/../components/header_dashboard.php';

$userData = getUserData();
$notifications = [];
if ($userData && isset($userData['enterprise_id'])) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM activities WHERE enterprise_id = ? ORDER BY id DESC");
    $stmt->execute([$userData['enterprise_id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<main class="main-content" id="mainContent">
    <div class="dashboard-container">
        <div class="dashboard-box">
            <h1>All Notifications</h1>
            <p>Here you can see all recent activities and notifications for your company.</p>
            <ul class="notification-list-full">
                <?php if (empty($notifications)): ?>
                    <li class="notification-item-full"><span>No notifications found.</span></li>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                    <li class="notification-item-full">
                        <span class="notification-icon-full"><i class="fas fa-<?php echo htmlspecialchars($notif['icon']); ?>"></i></span>
                        <span class="notification-content-full">
                            <span class="notification-title-full"><?php echo htmlspecialchars($notif['title']); ?></span><br>
                            <span class="notification-desc-full"><?php echo htmlspecialchars($notif['description']); ?></span>
                            <?php if (!empty($notif['date'])): ?>
                                <br><span class="notification-date-full"><?php echo date('M d, Y H:i', strtotime($notif['date'])); ?></span>
                            <?php endif; ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/../components/footer.php'; ?>
<style>
.dashboard-container { min-height: calc(100vh - 140px); background: #f8f9fa; display: flex; justify-content: center; align-items: flex-start; padding: 40px 0; }
.dashboard-box { background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding: 40px 32px; max-width: 800px; width: 100%; }
.dashboard-box h1 { font-size: 28px; margin-bottom: 24px; color: #1B1B3E; }
.dashboard-box p { color: rgba(27, 27, 62, 0.8); font-size: 16px; line-height: 1.7; margin-bottom: 32px; }
.notification-list-full { list-style: none; padding: 0; margin: 0; }
.notification-item-full { display: flex; align-items: flex-start; gap: 16px; padding: 20px 0; border-bottom: 1px solid #f3f3f9; }
.notification-item-full:last-child { border-bottom: none; }
.notification-icon-full { color: #7c3aed; font-size: 22px; margin-top: 2px; }
.notification-content-full { flex: 1; }
.notification-title-full { font-weight: 500; color: #1B1B3E; font-size: 16px; margin-bottom: 2px; }
.notification-desc-full { color: #555; font-size: 14px; margin-bottom: 2px; }
.notification-date-full { color: #aaa; font-size: 13px; }
</style> 