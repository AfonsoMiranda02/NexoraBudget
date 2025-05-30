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
<!-- Adicionar Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Adicionar jQuery e Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
        <h2>Public Projects</h2>
        <div class="filters">
            <input type="text" id="searchProject" placeholder="Search projects..." onkeyup="filterProjects()">
            <select id="filterCompany" class="company-select" style="width: 300px;">
                <option value="">All Companies</option>
                <?php
                $stmt = $db->prepare("SELECT id, name FROM enterprises ORDER BY name ASC");
                $stmt->execute();
                while ($enterprise = $stmt->fetch()) {
                    echo '<option value="' . htmlspecialchars($enterprise['id']) . '">' . htmlspecialchars($enterprise['name']) . '</option>';
                }
                ?>
            </select>
        </div>
        <div id="projectsGrid" class="projects-amazon-grid">
            <?php
            // Buscar projetos
            $stmt = $db->prepare("
                SELECT p.*, e.name as enterprise_name 
                FROM projects p 
                LEFT JOIN enterprises e ON p.enterprise_id = e.id 
                ORDER BY p.created_at DESC
            ");
            $stmt->execute();
            $projects = $stmt->fetchAll();

            if (empty($projects)) {
                echo '<div style="width:100%;text-align:center;padding:40px;color:#666;">No projects available.</div>';
            } else {
                foreach ($projects as $project) {
                    $projectImage = !empty($project['image']) ? BASE_URL . '/public/imgs/projects/' . $project['image'] : BASE_URL . '/public/imgs/logo.png';
                    ?>
                    <div class="project-card-amazon" data-company="<?php echo htmlspecialchars($project['enterprise_id']); ?>">
                        <div class="project-image-container">
                            <img src="<?php echo $projectImage; ?>" alt="Project Image" class="project-img-amazon">
                            <div class="project-status"><?php echo ucfirst(htmlspecialchars($project['status'])); ?></div>
                        </div>
                        <div class="project-content">
                            <div class="project-title-amazon"><?php echo htmlspecialchars($project['name']); ?></div>
                            <div class="project-company-amazon"><?php echo htmlspecialchars($project['enterprise_name']); ?></div>
                            <div class="project-desc-amazon"><?php echo htmlspecialchars($project['description']); ?></div>
                            <div class="project-meta">
                                <span class="project-date">Started: <?php echo date('M Y', strtotime($project['start_date'])); ?></span>
                                <span class="project-date">Ends: <?php echo date('M Y', strtotime($project['end_date'])); ?></span>
                            </div>
                            <div class="project-progress">
                                <div class="progress-bar" style="width: <?php echo $project['progress']; ?>%"></div>
                                <span class="progress-text"><?php echo $project['progress']; ?>% Complete</span>
                            </div>
                            <button class="project-view-btn" onclick="window.location.href='<?php echo BASE_URL; ?>/public/pages/project_details.php?id=<?php echo $project['id']; ?>'">View Details</button>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.company-select').select2({
        placeholder: "Search for a company...",
        allowClear: true,
        width: '100%'
    }).on('change', function() {
        filterProjects();
    });
});

function filterProjects() {
    var searchText = document.getElementById('searchProject').value.toLowerCase();
    var companyFilter = document.getElementById('filterCompany').value;
    var projects = document.querySelectorAll('.project-card-amazon');
    projects.forEach(function(project) {
        var title = project.querySelector('.project-title-amazon').textContent.toLowerCase();
        var desc = project.querySelector('.project-desc-amazon').textContent.toLowerCase();
        var company = project.getAttribute('data-company');
        var matchSearch = title.includes(searchText) || desc.includes(searchText);
        var matchCompany = companyFilter === '' || company === companyFilter;
        if (matchSearch && matchCompany) {
            project.style.display = '';
        } else {
            project.style.display = 'none';
        }
    });
}
</script>

<style>
.filters {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
    align-items: center;
}
.filters input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    width: 300px;
}
.select2-container {
    min-width: 300px;
}
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ddd;
    border-radius: 6px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
    padding-left: 12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
.select2-dropdown {
    border: 1px solid #ddd;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.select2-search--dropdown .select2-search__field {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.select2-results__option {
    padding: 8px 12px;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #7c3aed;
}
.projects-amazon-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    margin-bottom: 32px;
}
.project-card-amazon {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    width: 300px;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    overflow: hidden;
}
.project-card-amazon:hover {
    box-shadow: 0 4px 16px rgba(124,58,237,0.12);
    transform: translateY(-2px);
}
.project-image-container {
    position: relative;
    width: 100%;
    height: 180px;
}
.project-img-amazon {
    width: 100%;
    height: 100%;
    object-fit: cover;
    background: #f3f3f9;
}
.project-status {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(124,58,237,0.9);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}
.project-content {
    padding: 20px;
}
.project-title-amazon {
    font-size: 18px;
    font-weight: 600;
    color: #1B1B3E;
    margin-bottom: 4px;
}
.project-company-amazon {
    font-size: 14px;
    color: #7c3aed;
    margin-bottom: 8px;
    font-weight: 500;
}
.project-desc-amazon {
    font-size: 14px;
    color: #555;
    margin-bottom: 16px;
    line-height: 1.5;
    min-height: 42px;
}
.project-meta {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #666;
    margin-bottom: 12px;
}
.project-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}
.tag {
    background: #f3f4f6;
    color: #4b5563;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
}
.project-view-btn {
    background: #7c3aed;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
    width: 100%;
}
.project-view-btn:hover {
    background: #6d28d9;
}
.project-progress {
    margin: 12px 0;
    background: #f3f4f6;
    border-radius: 6px;
    height: 8px;
    position: relative;
    overflow: hidden;
}
.progress-bar {
    background: #7c3aed;
    height: 100%;
    border-radius: 6px;
    transition: width 0.3s ease;
}
.progress-text {
    position: absolute;
    right: 0;
    top: -20px;
    font-size: 12px;
    color: #666;
}
</style>

<?php require_once '../components/footer.php'; ?> 