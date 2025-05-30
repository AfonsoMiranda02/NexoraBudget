<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../php/db/functions.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}
require_once __DIR__ . '/../components/header_dashboard.php';
?>

<main class="main-content" id="mainContent">
    <div class="dashboard-container">
        <div class="dashboard-box">
            <h1>Employees Management</h1>
            <p>Manage your team members, assign roles, and track their performance.</p>
            <div class="employees-grid">
                <div class="employee-card">
                    <h3>Total Employees</h3>
                    <p class="employee-count">48</p>
                </div>
                <div class="employee-card">
                    <h3>Active Teams</h3>
                    <p class="employee-count">6</p>
                </div>
                <div class="employee-card">
                    <h3>Open Positions</h3>
                    <p class="employee-count">3</p>
                </div>
            </div>
            <div class="employee-list">
                <h2>Team Members</h2>
                <div class="employee-item">
                    <div class="employee-info">
                        <img src="<?php echo BASE_URL; ?>/public/imgs/user-full.png" alt="Employee" class="employee-avatar">
                        <div>
                            <h3>John Doe</h3>
                            <p>Senior Developer</p>
                        </div>
                    </div>
                    <div class="employee-actions">
                        <button class="btn-edit">Edit</button>
                        <button class="btn-view">View Profile</button>
                    </div>
                </div>
                <div class="employee-item">
                    <div class="employee-info">
                        <img src="<?php echo BASE_URL; ?>/public/imgs/user-full.png" alt="Employee" class="employee-avatar">
                        <div>
                            <h3>Jane Smith</h3>
                            <p>Project Manager</p>
                        </div>
                    </div>
                    <div class="employee-actions">
                        <button class="btn-edit">Edit</button>
                        <button class="btn-view">View Profile</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

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
    max-width: 1000px;
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
    margin-bottom: 32px;
}
.employees-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}
.employee-card {
    background: #f9fafb;
    border-radius: 12px;
    padding: 24px;
    text-align: center;
}
.employee-card h3 {
    color: #1B1B3E;
    font-size: 16px;
    margin-bottom: 12px;
}
.employee-count {
    font-size: 32px;
    font-weight: 600;
    color: #7c3aed;
}
.employee-list {
    margin-top: 40px;
}
.employee-list h2 {
    font-size: 20px;
    color: #1B1B3E;
    margin-bottom: 24px;
}
.employee-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f9fafb;
    border-radius: 12px;
    margin-bottom: 16px;
}
.employee-info {
    display: flex;
    align-items: center;
    gap: 16px;
}
.employee-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}
.employee-info h3 {
    font-size: 16px;
    color: #1B1B3E;
    margin-bottom: 4px;
}
.employee-info p {
    color: rgba(27, 27, 62, 0.8);
    font-size: 14px;
    margin: 0;
}
.employee-actions {
    display: flex;
    gap: 12px;
}
.btn-edit, .btn-view {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.btn-edit {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #1B1B3E;
}
.btn-view {
    background: #7c3aed;
    border: none;
    color: #fff;
}
.btn-edit:hover {
    border-color: #7c3aed;
    color: #7c3aed;
}
.btn-view:hover {
    background: #6d28d9;
}
</style> 