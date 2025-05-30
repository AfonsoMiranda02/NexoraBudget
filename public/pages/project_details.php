<?php
require_once '../../config.php';
require_once __DIR__ . '/../php/db/functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['account_type'] !== 'client') {
    header('Location: ../pages/login.php');
    exit();
}
$db = getDBConnection();

// Processar exclusão de comentário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $commentId = intval($_POST['delete_comment_id']);
    // Só permite deletar se o comentário for do próprio usuário
    $stmt = $db->prepare("DELETE FROM project_comments WHERE id = ? AND user_id = ?");
    $stmt->execute([$commentId, $_SESSION['user_id']]);
    header('Location: project_details.php?id=' . intval($_GET['id']));
    exit();
}

// Fetch project details using the project ID from the URL
$projectId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $db->prepare("SELECT p.*, e.name as enterprise_name FROM projects p LEFT JOIN enterprises e ON p.enterprise_id = e.id WHERE p.id = ?");
$stmt->execute([$projectId]);
$project = $stmt->fetch();

if (!$project) {
    echo '<div style="text-align: center; padding: 40px;">Project not found.</div>';
    exit();
}

// Usar BigLogo.png como imagem principal
$projectImage = BASE_URL . '/public/imgs/BigLogo.png';

// Comentários: inserir novo comentário
$commentError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_comment'])) {
    $newComment = trim($_POST['new_comment']);
    if ($newComment !== '') {
        $stmt = $db->prepare("INSERT INTO project_comments (project_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$projectId, $_SESSION['user_id'], $newComment]);
        header('Location: project_details.php?id=' . $projectId); // Evita repost
        exit();
    } else {
        $commentError = 'Comment cannot be empty.';
    }
}
// Buscar comentários (agora também busca profile_image)
$stmt = $db->prepare("SELECT c.*, u.name, u.profile_image FROM project_comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.project_id = ? ORDER BY c.created_at DESC");
$stmt->execute([$projectId]);
$comments = $stmt->fetchAll();

require_once '../components/header.php';
?>

<div class="project-details-profile-container">
    <div class="project-details-profile-box">
        <div class="project-profile-flex">
            <div class="project-profile-image">
                <img src="<?php echo $projectImage; ?>" alt="Project Image">
            </div>
            <div class="project-profile-info">
                <h1><?php echo htmlspecialchars($project['name']); ?></h1>
                <p><strong>Company:</strong> <?php echo htmlspecialchars($project['enterprise_name']); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($project['status'])); ?></p>
                <p><strong>Start Date:</strong> <?php echo date('M Y', strtotime($project['start_date'])); ?></p>
                <p><strong>End Date:</strong> <?php echo date('M Y', strtotime($project['end_date'])); ?></p>
                <p><strong>Progress:</strong> <?php echo $project['progress']; ?>%</p>
                <div class="project-profile-desc">
                    <strong>Description:</strong>
                    <div><?php echo nl2br(htmlspecialchars($project['description'])); ?></div>
                </div>
                <button class="back-btn" onclick="window.location.href='<?php echo BASE_URL; ?>/public/pages/dashboard_client.php'">Back to Dashboard</button>
            </div>
        </div>
        <div class="project-comments-section">
            <h2>Comments</h2>
            <?php if ($commentError): ?>
                <div class="comment-error"><?php echo $commentError; ?></div>
            <?php endif; ?>
            <form method="post" class="comment-form">
                <textarea name="new_comment" rows="3" placeholder="Add a comment..."></textarea>
                <button type="submit">Post</button>
            </form>
            <div class="comments-list">
                <?php if (empty($comments)): ?>
                    <div class="no-comments">No comments yet.</div>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item">
                            <div class="comment-header">
                                <div class="comment-user-info">
                                    <?php
                                    $img = !empty($comment['profile_image']) ? (strpos($comment['profile_image'], 'http') === 0 ? $comment['profile_image'] : BASE_URL . '/public/imgs/users/' . $comment['profile_image']) : (BASE_URL . '/public/imgs/user-full.png');
                                    ?>
                                    <img src="<?php echo $img; ?>" class="comment-user-avatar" alt="User Avatar">
                                    <span class="comment-author"><?php echo htmlspecialchars($comment['name'] ?? 'User'); ?></span>
                                </div>
                                <span class="comment-date"><?php echo date('d M Y H:i', strtotime($comment['created_at'])); ?></span>
                                <?php if ($comment['user_id'] == $_SESSION['user_id']): ?>
                                    <form method="post" style="display:inline; margin-left:12px;">
                                        <input type="hidden" name="delete_comment_id" value="<?php echo $comment['id']; ?>">
                                        <button type="submit" class="delete-comment-btn" onclick="return confirm('Are you sure you want to delete this comment?')">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="comment-body"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.project-details-profile-container {
    padding: 32px 0;
    max-width: 1000px;
    margin: 0 auto;
}
.project-details-profile-box {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    padding: 32px;
}
.project-profile-flex {
    display: flex;
    gap: 40px;
    align-items: flex-start;
}
.project-profile-image img {
    width: 180px;
    height: 180px;
    object-fit: contain;
    border-radius: 16px;
    background: #f3f3f9;
    border: 1px solid #eee;
}
.project-profile-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.project-profile-desc {
    margin: 16px 0 0 0;
    color: #444;
    font-size: 15px;
}
.back-btn {
    background: #7c3aed;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 18px;
    align-self: flex-start;
}
.back-btn:hover {
    background: #6d28d9;
}
.project-comments-section {
    margin-top: 48px;
}
.comment-form {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 24px;
}
.comment-form textarea {
    resize: vertical;
    border-radius: 6px;
    border: 1px solid #ddd;
    padding: 10px;
    font-size: 15px;
}
.comment-form button {
    align-self: flex-end;
    background: #7c3aed;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 7px 18px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}
.comment-form button:hover {
    background: #6d28d9;
}
.comments-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.comment-item {
    background: #f8f8fc;
    border-radius: 8px;
    padding: 14px 18px;
    box-shadow: 0 1px 2px rgba(124,58,237,0.04);
}
.comment-header {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    color: #7c3aed;
    margin-bottom: 6px;
}
.comment-body {
    color: #222;
    font-size: 15px;
    white-space: pre-line;
}
.no-comments {
    color: #888;
    font-size: 15px;
    padding: 16px 0;
}
.comment-error {
    color: #b91c1c;
    margin-bottom: 8px;
    font-size: 14px;
}
.comment-user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.comment-user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    background: #e5e7eb;
    border: 1px solid #ddd;
}
.delete-comment-btn {
    background: #e11d48;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 3px 10px;
    font-size: 13px;
    cursor: pointer;
    margin-left: 8px;
    transition: background 0.2s;
}
.delete-comment-btn:hover {
    background: #be123c;
}
</style>

<?php require_once '../components/footer.php'; ?> 