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
            <h1>Create New Project</h1>
            <p>Fill in the details below to create a new project.</p>
            <form id="createProjectForm" action="<?php echo BASE_URL; ?>/public/php/actions/create_project.php" method="post">
                <div class="form-group">
                    <label for="projectName">Project Name</label>
                    <input type="text" id="projectName" name="projectName" required>
                </div>
                <div class="form-group">
                    <label for="projectDescription">Description</label>
                    <textarea id="projectDescription" name="projectDescription" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="projectStatus">Status</label>
                    <select id="projectStatus" name="projectStatus" required>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="upcoming">Upcoming</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="startDate">Start Date</label>
                    <input type="date" id="startDate" name="startDate" required>
                </div>
                <div class="form-group">
                    <label for="endDate">End Date</label>
                    <input type="date" id="endDate" name="endDate">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Create Project</button>
                    <a href="<?php echo BASE_URL; ?>/public/pages/projects.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
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
    max-width: 800px;
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
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #1B1B3E;
}
.form-group input, .form-group textarea, .form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 15px;
}
.form-actions {
    display: flex;
    gap: 16px;
    margin-top: 32px;
}
.btn-submit, .btn-cancel {
    padding: 10px 22px;
    border-radius: 6px;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.btn-submit {
    background: #7c3aed;
    color: #fff;
    border: none;
}
.btn-cancel {
    background: #fff;
    color: #1B1B3E;
    border: 1px solid #e5e7eb;
    text-decoration: none;
}
.btn-submit:hover {
    background: #6d28d9;
}
.btn-cancel:hover {
    border-color: #7c3aed;
    color: #7c3aed;
}
</style> 