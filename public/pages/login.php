<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../components/header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Captura mensagem de erro de login, se existir
$login_error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Login</h2>
        <?php if (!empty($login_error)): ?>
            <div class="login-error" style="color:#ef4444;background:#fff0f0;padding:10px 16px;border-radius:6px;margin-bottom:16px;text-align:center;font-weight:500;">
                <?php echo htmlspecialchars($login_error); ?>
            </div>
        <?php endif; ?>
        <form class="auth-form" action="<?php echo BASE_URL; ?>/public/php/auth/login.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div style="position:relative;display:flex;align-items:center;">
                    <input type="password" id="password" name="password" required style="flex:1;">
                    <button type="button" id="togglePassword" tabindex="-1" style="background:none;border:none;position:absolute;right:8px;cursor:pointer;outline:none;">
                        <img src="<?php echo BASE_URL; ?>/public/imgs/eye.png" alt="Ver senha" id="eyeIcon" style="width:20px;height:20px;">
                    </button>
                </div>
            </div>
            <button type="submit" class="auth-btn">Login</button>
        </form>
        <div class="auth-links">
            <a href="<?php echo BASE_URL; ?>/public/pages/forgot_password.php">Forgot Password?</a>
            <span>|</span>
            <a href="<?php echo BASE_URL; ?>/public/pages/register.php">Create Account</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('togglePassword');
    var input = document.getElementById('password');
    var icon = document.getElementById('eyeIcon');
    if(toggle && input && icon) {
        toggle.addEventListener('click', function() {
            if (input.type === 'password') {
                input.type = 'text';
                icon.src = '<?php echo BASE_URL; ?>/public/imgs/eye-off.png';
            } else {
                input.type = 'password';
                icon.src = '<?php echo BASE_URL; ?>/public/imgs/eye.png';
            }
        });
    }
});
</script>

<?php if (!empty($_SESSION['plan'])): ?>
    <div class="user-plan">Plano: <?php echo ucfirst($_SESSION['plan']); ?></div>
<?php endif; ?>

<?php
require_once __DIR__ . '/../components/footer.php';
?> 