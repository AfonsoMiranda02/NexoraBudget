<?php
require_once __DIR__ . '/../components/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Reset Password</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <form action="<?php echo BASE_URL; ?>/php/auth/forgot-password.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <button type="submit" class="auth-btn">Send Reset Link</button>
            </div>
            <div class="auth-links">
                <a href="<?php echo BASE_URL; ?>/login">Back to Login</a>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../components/footer.php';
?> 