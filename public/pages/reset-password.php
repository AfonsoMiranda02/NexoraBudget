<?php
require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../php/db/functions.php';

$token = $_GET['token'] ?? '';
if (empty($token) || !validateResetToken($token)) {
    $_SESSION['error'] = "Invalid or expired reset token";
    header("Location: " . BASE_URL . "/forgot-password");
    exit();
}
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Reset Your Password</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <form action="<?php echo BASE_URL; ?>/php/auth/reset-password.php" method="POST" class="auth-form">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="form-group">
                <label for="password">New Password</label>
                <div style="position:relative;display:flex;align-items:center;">
                    <input type="password" id="password" name="password" required style="flex:1;">
                    <button type="button" id="togglePassword" tabindex="-1" style="background:none;border:none;position:absolute;right:8px;cursor:pointer;outline:none;">
                        <img src="<?php echo BASE_URL; ?>/public/imgs/eye.png" alt="Ver senha" id="eyeIcon" style="width:20px;height:20px;">
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div style="position:relative;display:flex;align-items:center;">
                    <input type="password" id="confirm_password" name="confirm_password" required style="flex:1;">
                    <button type="button" id="toggleConfirmPassword" tabindex="-1" style="background:none;border:none;position:absolute;right:8px;cursor:pointer;outline:none;">
                        <img src="<?php echo BASE_URL; ?>/public/imgs/eye.png" alt="Ver senha" id="eyeIconConfirm" style="width:20px;height:20px;">
                    </button>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="auth-btn">Reset Password</button>
            </div>
        </form>
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
    var toggleConfirm = document.getElementById('toggleConfirmPassword');
    var inputConfirm = document.getElementById('confirm_password');
    var iconConfirm = document.getElementById('eyeIconConfirm');
    if(toggleConfirm && inputConfirm && iconConfirm) {
        toggleConfirm.addEventListener('click', function() {
            if (inputConfirm.type === 'password') {
                inputConfirm.type = 'text';
                iconConfirm.src = '<?php echo BASE_URL; ?>/public/imgs/eye-off.png';
            } else {
                inputConfirm.type = 'password';
                iconConfirm.src = '<?php echo BASE_URL; ?>/public/imgs/eye.png';
            }
        });
    }
});
</script>

<?php
require_once __DIR__ . '/../components/footer.php';
?> 