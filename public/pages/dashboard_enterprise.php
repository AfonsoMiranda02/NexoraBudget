<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../php/db/functions.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}
require_once __DIR__ . '/../components/header_dashboard.php';

// Get dashboard statistics
$db = getDBConnection();
$enterprise_id = $_SESSION['enterprise_id'];

// Get total employees
$stmt = $db->prepare("SELECT COUNT(*) FROM enterprise_users WHERE enterprise_id = ?");
$stmt->execute([$enterprise_id]);
$total_employees = $stmt->fetchColumn();

// Get active projects
$stmt = $db->prepare("SELECT COUNT(*) FROM projects WHERE enterprise_id = ? AND status = 'active'");
$stmt->execute([$enterprise_id]);
$active_projects = $stmt->fetchColumn();

// Get total budget
$stmt = $db->prepare("SELECT SUM(amount) FROM budgets WHERE enterprise_id = ?");
$stmt->execute([$enterprise_id]);
$total_budget = $stmt->fetchColumn() ?: 0;

// Get monthly revenue data for the last 6 months
$stmt = $db->prepare("
    SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(amount) as total 
    FROM transactions 
    WHERE enterprise_id = ? AND type = 'revenue' 
    AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(date, '%Y-%m')
    ORDER BY month ASC
");
$stmt->execute([$enterprise_id]);
$revenue_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get monthly expenses data for the last 6 months
$stmt = $db->prepare("
    SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(amount) as total 
    FROM transactions 
    WHERE enterprise_id = ? AND type = 'expense' 
    AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(date, '%Y-%m')
    ORDER BY month ASC
");
$stmt->execute([$enterprise_id]);
$expense_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent activities
$stmt = $db->prepare("
    SELECT * FROM activities 
    WHERE enterprise_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$enterprise_id]);
$recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="main-content" id="mainContent">
    <div class="dashboard-container">
        <div class="dashboard-box">
            <h1>Enterprise Dashboard</h1>
            <p>Welcome back! Here's an overview of your company's performance.</p>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div class="stat-info">
                        <h3>Total Employees</h3>
                        <p class="stat-value"><?php echo $total_employees; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-project-diagram"></i>
                    <div class="stat-info">
                        <h3>Active Projects</h3>
                        <p class="stat-value"><?php echo $active_projects; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-dollar-sign"></i>
                    <div class="stat-info">
                        <h3>Total Budget</h3>
                        <p class="stat-value">$<?php echo number_format($total_budget, 2); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-chart-line"></i>
                    <div class="stat-info">
                        <h3>Revenue Growth</h3>
                        <p class="stat-value">+15%</p>
                    </div>
                </div>
            </div>

            <div class="charts-container">
                <div class="chart-box">
                    <h2>Financial Overview</h2>
                    <div id="financialChart" style="width: 100%; height: 300px;"></div>
                </div>
            </div>

            <div class="recent-activities">
                <h2>Recent Activities</h2>
                <div class="activity-list">
                    <?php foreach ($recent_activities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-<?php echo $activity['icon']; ?>"></i>
                        </div>
                        <div class="activity-info">
                            <h3><?php echo htmlspecialchars($activity['title']); ?></h3>
                            <p><?php echo htmlspecialchars($activity['description']); ?></p>
                            <span class="activity-date"><?php echo date('M d, Y', strtotime($activity['created_at'])); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
    var revenueData = <?php echo json_encode($revenue_data); ?>;
    var expenseData = <?php echo json_encode($expense_data); ?>;
    
    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Month');
    data.addColumn('number', 'Revenue');
    data.addColumn('number', 'Expenses');
    
    // Combine and sort the data
    var months = new Set();
    revenueData.forEach(item => months.add(item.month));
    expenseData.forEach(item => months.add(item.month));
    
    var sortedMonths = Array.from(months).sort();
    
    sortedMonths.forEach(month => {
        var revenue = revenueData.find(item => item.month === month)?.total || 0;
        var expense = expenseData.find(item => item.month === month)?.total || 0;
        data.addRow([month, parseFloat(revenue), parseFloat(expense)]);
    });
    
    var options = {
        title: 'Monthly Revenue vs Expenses',
        curveType: 'function',
        legend: { position: 'bottom' },
        hAxis: {
            title: 'Month',
            slantedText: true,
            slantedTextAngle: 45
        },
        vAxis: {
            title: 'Amount ($)',
            format: '$#,##0'
        },
        colors: ['#7c3aed', '#ef4444'],
        backgroundColor: '#ffffff',
        chartArea: {
            left: '10%',
            right: '5%',
            top: '15%',
            bottom: '20%'
        }
    };
    
    var chart = new google.visualization.LineChart(document.getElementById('financialChart'));
    chart.draw(data, options);
}
</script>

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
    max-width: 1200px;
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
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}
.stat-card {
    background: #f9fafb;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.stat-card i {
    font-size: 24px;
    color: #7c3aed;
    background: rgba(124, 58, 237, 0.1);
    padding: 12px;
    border-radius: 8px;
}
.stat-info h3 {
    font-size: 14px;
    color: rgba(27, 27, 62, 0.8);
    margin-bottom: 4px;
}
.stat-value {
    font-size: 24px;
    font-weight: 600;
    color: #1B1B3E;
    margin: 0;
}
.charts-container {
    margin-bottom: 40px;
}
.chart-box {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.chart-box h2 {
    font-size: 20px;
    color: #1B1B3E;
    margin-bottom: 24px;
}
.recent-activities {
    margin-top: 40px;
}
.recent-activities h2 {
    font-size: 20px;
    color: #1B1B3E;
    margin-bottom: 24px;
}
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 12px;
}
.activity-icon {
    background: rgba(124, 58, 237, 0.1);
    color: #7c3aed;
    padding: 12px;
    border-radius: 8px;
}
.activity-icon i {
    font-size: 20px;
}
.activity-info {
    flex: 1;
}
.activity-info h3 {
    font-size: 16px;
    color: #1B1B3E;
    margin-bottom: 4px;
}
.activity-info p {
    color: rgba(27, 27, 62, 0.8);
    font-size: 14px;
    margin-bottom: 8px;
}
.activity-date {
    font-size: 12px;
    color: rgba(27, 27, 62, 0.6);
}
</style> 