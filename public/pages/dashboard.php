<?php
require_once __DIR__ . '/../../config.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}
require_once __DIR__ . '/../components/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-box">
        <h1>Welcome to Your Dashboard</h1>
        <p>Your login was successful!<br>
        Here you can manage your projects, budgets, and profile as a client.</p>
        <ul>
            <li>Request and manage project budgets</li>
            <li>View your project status</li>
            <li>Update your profile information</li>
            <li>Contact support</li>
        </ul>
    </div>
</div>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

<style>
.dashboard-container {
    min-height: calc(100vh - 140px);
    background: #f8f9fa;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 40px 0;
}
.dashboard-box {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    padding: 40px 32px;
    max-width: 700px;
    width: 100%;
}
.dashboard-box h1 {
    font-size: 28px;
    margin-bottom: 24px;
    color: #1B1B3E;
}
.dashboard-box p {
    color: rgba(27, 27, 62, 0.8);
    font-size: 16px;
    line-height: 1.7;
    margin-bottom: 20px;
}
.dashboard-box ul {
    color: #1B1B3E;
    font-size: 16px;
    margin-left: 20px;
}
</style> 