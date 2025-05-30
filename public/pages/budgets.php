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
            <h1>Budget Management</h1>
            <p>Track and manage your company's budgets, expenses, and financial resources.</p>
            <div class="budgets-grid">
                <div class="budget-card">
                    <h3>Total Budget</h3>
                    <p class="budget-amount">$250,000</p>
                </div>
                <div class="budget-card">
                    <h3>Spent</h3>
                    <p class="budget-amount">$120,000</p>
                </div>
                <div class="budget-card">
                    <h3>Remaining</h3>
                    <p class="budget-amount">$130,000</p>
                </div>
            </div>
            <div class="budget-list">
                <h2>Recent Transactions</h2>
                <div class="budget-item">
                    <div class="budget-info">
                        <h3>Website Development</h3>
                        <p>Project Expenses</p>
                    </div>
                    <div class="budget-details">
                        <span class="budget-date">Mar 15, 2024</span>
                        <span class="budget-value">-$5,000</span>
                    </div>
                </div>
                <div class="budget-item">
                    <div class="budget-info">
                        <h3>Team Training</h3>
                        <p>Professional Development</p>
                    </div>
                    <div class="budget-details">
                        <span class="budget-date">Mar 14, 2024</span>
                        <span class="budget-value">-$2,500</span>
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
.budgets-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}
.budget-card {
    background: #f9fafb;
    border-radius: 12px;
    padding: 24px;
    text-align: center;
}
.budget-card h3 {
    color: #1B1B3E;
    font-size: 16px;
    margin-bottom: 12px;
}
.budget-amount {
    font-size: 32px;
    font-weight: 600;
    color: #7c3aed;
}
.budget-list {
    margin-top: 40px;
}
.budget-list h2 {
    font-size: 20px;
    color: #1B1B3E;
    margin-bottom: 24px;
}
.budget-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f9fafb;
    border-radius: 12px;
    margin-bottom: 16px;
}
.budget-info h3 {
    font-size: 16px;
    color: #1B1B3E;
    margin-bottom: 4px;
}
.budget-info p {
    color: rgba(27, 27, 62, 0.8);
    font-size: 14px;
    margin: 0;
}
.budget-details {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}
.budget-date {
    font-size: 14px;
    color: rgba(27, 27, 62, 0.6);
}
.budget-value {
    font-size: 16px;
    font-weight: 600;
    color: #ef4444;
}
</style> 