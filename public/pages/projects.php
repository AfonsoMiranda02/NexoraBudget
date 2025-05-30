<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../php/db/functions.php';
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}
require_once __DIR__ . '/../components/header_dashboard.php';

$userData = getUserData();
$projects = [];
$activeProjects = 0;
$completedProjects = 0;
$upcomingProjects = 0;

if ($userData && isset($userData['enterprise_id'])) {
    $projects = getAllProjectsByEnterprise($userData['enterprise_id']);
    foreach ($projects as $proj) {
        if ($proj['status'] === 'active') {
            $activeProjects++;
        } elseif ($proj['status'] === 'completed') {
            $completedProjects++;
        } elseif ($proj['status'] === 'upcoming') {
            $upcomingProjects++;
        }
    }
}
?>

<main class="main-content" id="mainContent">
    <div class="dashboard-container">
        <div class="dashboard-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h1>Projects Management</h1>
                <a href="<?php echo BASE_URL; ?>/public/pages/create_project.php" class="btn-create-project">+ Create Project</a>
            </div>
            <p>Here you can manage all your company's projects, track their progress, and assign team members.</p>
            <div class="projects-grid">
                <div class="project-card">
                    <h3>Active Projects</h3>
                    <p class="project-count"><?php echo $activeProjects; ?></p>
                </div>
                <div class="project-card">
                    <h3>Completed Projects</h3>
                    <p class="project-count"><?php echo $completedProjects; ?></p>
                </div>
                <div class="project-card">
                    <h3>Upcoming Projects</h3>
                    <p class="project-count"><?php echo $upcomingProjects; ?></p>
                </div>
            </div>

            <!-- Filtros para os projetos -->
            <div class="projects-filters" style="margin-top: 40px; margin-bottom: 20px; display: flex; gap: 16px; align-items: center;">
                <input type="text" id="projectSearch" placeholder="Search by project name..." style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 6px; flex: 1;">
                <select id="statusFilter" style="padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 6px; background: #fff;">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="upcoming">Upcoming</option>
                </select>
            </div>
            <!-- Fim dos filtros -->

            <!-- Tabela de todos os projetos -->
            <div class="projects-table-section">
                <h2 style="margin-top:40px; margin-bottom: 16px;">All Projects</h2>
                <div style="overflow-x:auto;">
                <table class="projects-table" id="projectsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($projects)): ?>
                            <tr><td colspan="7" style="text-align:center;">No projects found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($projects as $proj): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($proj['id']); ?></td>
                                    <td><?php echo htmlspecialchars($proj['name']); ?></td>
                                    <td><span class="<?php echo getProjectStatus($proj['status']); ?>"><?php echo htmlspecialchars(ucfirst($proj['status'])); ?></span></td>
                                    <td><?php echo number_format($proj['progress'], 2); ?>%</td>
                                    <td><?php echo htmlspecialchars($proj['start_date']); ?></td>
                                    <td><?php echo htmlspecialchars($proj['end_date'] ?? '-'); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/public/pages/edit_project.php?id=<?php echo $proj['id']; ?>" class="btn-edit">Edit</a>
                                        <a href="<?php echo BASE_URL; ?>/public/pages/view_project.php?id=<?php echo $proj['id']; ?>" class="btn-view">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
            <!-- Fim da tabela -->

            <div class="project-list">
                <h2>Recent Projects</h2>
                <div class="project-item">
                    <div class="project-info">
                        <h3>Website Redesign</h3>
                        <p>In Progress - 75% Complete</p>
                    </div>
                    <div class="project-actions">
                        <button class="btn-edit">Edit</button>
                        <button class="btn-view">View Details</button>
                    </div>
                </div>
                <div class="project-item">
                    <div class="project-info">
                        <h3>Mobile App Development</h3>
                        <p>Planning Phase - 25% Complete</p>
                    </div>
                    <div class="project-actions">
                        <button class="btn-edit">Edit</button>
                        <button class="btn-view">View Details</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const projectSearch = document.getElementById('projectSearch');
    const statusFilter = document.getElementById('statusFilter');
    const projectsTable = document.getElementById('projectsTable');
    const rows = projectsTable.querySelectorAll('tbody tr');

    function filterProjects() {
        const searchTerm = projectSearch.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();

        rows.forEach(row => {
            const name = row.cells[1].textContent.toLowerCase();
            const status = row.cells[2].textContent.toLowerCase();
            const nameMatch = name.includes(searchTerm);
            const statusMatch = statusValue === '' || status.includes(statusValue);
            row.style.display = nameMatch && statusMatch ? '' : 'none';
        });
    }

    projectSearch.addEventListener('input', filterProjects);
    statusFilter.addEventListener('change', filterProjects);
});
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
.projects-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}
.project-card {
    background: #f9fafb;
    border-radius: 12px;
    padding: 24px;
    text-align: center;
}
.project-card h3 {
    color: #1B1B3E;
    font-size: 16px;
    margin-bottom: 12px;
}
.project-count {
    font-size: 32px;
    font-weight: 600;
    color: #7c3aed;
}
.project-list {
    margin-top: 40px;
}
.project-list h2 {
    font-size: 20px;
    color: #1B1B3E;
    margin-bottom: 24px;
}
.project-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f9fafb;
    border-radius: 12px;
    margin-bottom: 16px;
}
.project-info h3 {
    font-size: 16px;
    color: #1B1B3E;
    margin-bottom: 8px;
}
.project-info p {
    color: rgba(27, 27, 62, 0.8);
    font-size: 14px;
    margin: 0;
}
.project-actions {
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
.btn-create-project {
    background: #7c3aed;
    color: #fff;
    padding: 10px 22px;
    border-radius: 6px;
    font-size: 15px;
    text-decoration: none;
    transition: background 0.2s;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(124, 58, 237, 0.08);
}
.btn-create-project:hover {
    background: #6d28d9;
}
.projects-table-section {
    margin-bottom: 40px;
}
.projects-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.projects-table th, .projects-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #f3f3f9;
}
.projects-table th {
    background: #f9fafb;
    color: #1B1B3E;
    font-weight: 600;
    font-size: 15px;
}
.projects-table tr:last-child td {
    border-bottom: none;
}
.status-active { color: #22c55e; font-weight: 500; }
.status-completed { color: #7c3aed; font-weight: 500; }
.status-upcoming { color: #f59e42; font-weight: 500; }
.status-default { color: #aaa; font-weight: 500; }
</style> 