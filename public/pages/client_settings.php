<?php
require_once '../../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'client') {
    header('Location: ../pages/login.php');
    exit();
}
require_once '../components/header.php';
$db = getDBConnection();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$profileImg = (!empty($user['profile_image']) && file_exists(__DIR__ . '/../imgs/users/' . $user['profile_image']))
    ? BASE_URL . '/public/imgs/users/' . $user['profile_image']
    : BASE_URL . '/public/imgs/user-full.png';
?>
<div class="dashboard-container">
    <div class="dashboard-box">
        <div style="display:flex;align-items:center;gap:24px;">
            <img src="<?php echo $profileImg; ?>" alt="Profile" style="width:80px;height:80px;border-radius:50%;object-fit:cover;background:#e5e7eb;">
            <div>
                <h1 style="margin-bottom:8px;">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <div style="color:#7c3aed;font-weight:500;">Email: <?php echo htmlspecialchars($user['email']); ?></div>
                <div style="color:#555;">Interests: <?php echo htmlspecialchars($user['interests'] ?? ''); ?></div>
            </div>
        </div>
        <hr style="margin:32px 0;">
        <h2>Your Experience</h2>
        <h3 style="margin-top:24px;">Previous Companies</h3>
        <ul>
        <?php
        $stmt = $db->prepare("SELECT * FROM client_companies WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        foreach ($stmt->fetchAll() as $company) {
            echo '<li><b>' . htmlspecialchars($company['name']) . '</b> - <a href="' . htmlspecialchars($company['website']) . '" target="_blank">' . htmlspecialchars($company['website']) . '</a><br><span style="color:#555;">' . htmlspecialchars($company['description']) . '</span></li>';
        }
        ?>
        </ul>
        <h3 style="margin-top:24px;">Previous Projects</h3>
        <ul>
        <?php
        $stmt = $db->prepare("SELECT * FROM client_previous_projects WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        foreach ($stmt->fetchAll() as $project) {
            echo '<li><b>' . htmlspecialchars($project['name']) . '</b> - <a href="' . htmlspecialchars($project['website']) . '" target="_blank">' . htmlspecialchars($project['website']) . '</a><br><span style="color:#555;">' . htmlspecialchars($project['description']) . '</span></li>';
        }
        ?>
        </ul>
    </div>
</div>
<?php require_once '../components/footer.php'; ?> 