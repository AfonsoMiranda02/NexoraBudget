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
            <h1>Reports & Analytics</h1>
            <p>View detailed reports and analytics about your company's performance and activities.</p>
            <div class="reports-grid">
                <div class="report-card">
                    <h3>Project Success Rate</h3>
                    <p class="report-value">85%</p>
                </div>
                <div class="report-card">
                    <h3>Team Productivity</h3>
                    <p class="report-value">92%</p>
                </div>
                <div class="report-card">
                    <h3>Budget Efficiency</h3>
                    <p class="report-value">78%</p>
                </div>
            </div>
            <div class="reports-list">
                <h2>Available Reports</h2>
                <div class="report-item">
                    <div class="report-info">
                        <h3>Monthly Performance Report</h3>
                        <p>March 2024</p>
                    </div>
                    <div class="report-actions">
                        <button class="btn-download">Download PDF</button>
                        <button class="btn-view">View Online</button>
                    </div>
                </div>
                <div class="report-item">
                    <div class="report-info">
                        <h3>Team Productivity Analysis</h3>
                        <p>Q1 2024</p>
                    </div>
                    <div class="report-actions">
                        <button class="btn-download">Download PDF</button>
                        <button class="btn-view">View Online</button>
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
.reports-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}
.report-card {
    background: #f9fafb;
    border-radius: 12px;
    padding: 24px;
    text-align: center;
}
.report-card h3 {
    color: #1B1B3E;
    font-size: 16px;
    margin-bottom: 12px;
}
.report-value {
    font-size: 32px;
    font-weight: 600;
    color: #7c3aed;
}
.reports-list {
    margin-top: 40px;
}
.reports-list h2 {
    font-size: 20px;
    color: #1B1B3E;
    margin-bottom: 24px;
}
.report-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f9fafb;
    border-radius: 12px;
    margin-bottom: 16px;
}
.report-info h3 {
    font-size: 16px;
    color: #1B1B3E;
    margin-bottom: 4px;
}
.report-info p {
    color: rgba(27, 27, 62, 0.8);
    font-size: 14px;
    margin: 0;
}
.report-actions {
    display: flex;
    gap: 12px;
}
.btn-download, .btn-view {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.btn-download {
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #1B1B3E;
}
.btn-view {
    background: #7c3aed;
    border: none;
    color: #fff;
}
.btn-download:hover {
    border-color: #7c3aed;
    color: #7c3aed;
}
.btn-view:hover {
    background: #6d28d9;
}
</style> 