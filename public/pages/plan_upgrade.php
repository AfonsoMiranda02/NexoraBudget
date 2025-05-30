<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once __DIR__ . '/../php/db/functions.php';
$userData = getUserData();
$planLabel = '';
if (isset($userData['plan'])) {
    if ($userData['plan'] == 0) $planLabel = 'Standard Plan';
    elseif ($userData['plan'] == 1) $planLabel = 'Plus Plan';
    elseif ($userData['plan'] == 2) $planLabel = 'Premium Plan';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexoraHub - Plan Upgrade</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <style>
    .plan-upgrade-container { max-width: 600px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .plan-upgrade-container h1 { font-size: 28px; margin-bottom: 24px; color: #1B1B3E; }
    .plan-upgrade-container p { color: rgba(27, 27, 62, 0.8); font-size: 16px; line-height: 1.7; margin-bottom: 20px; }
    .plan-upgrade-container ul { color: #1B1B3E; font-size: 16px; margin-left: 20px; }
    .plan-upgrade-container a { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #7c3aed; color: #fff; text-decoration: none; border-radius: 8px; transition: background 0.2s; }
    .plan-upgrade-container a:hover { background: #6d28d9; }
    .toast { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #7c3aed; color: #fff; border-radius: 8px; display: none; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../components/header_dashboard.php'; ?>

<div class="plan-upgrade-container">
    <h1>Upgrade Your Plan</h1>
    <p>Your current plan is: <strong><?php echo $planLabel; ?></strong></p>
    <p>Upgrade or downgrade your plan to change your subscription for the next month.</p>
    <ul>
        <li>Standard Plan: $10/month or $100/year</li>
        <li>Plus Plan: $20/month or $200/year</li>
        <li>Premium Plan: $30/month or $300/year</li>
    </ul>
    <a href="https://stride.com/compare-plans" target="_blank">Compare Plans</a>
</div>

<div class="toast" id="toast">Plan updated successfully!</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toast = document.getElementById('toast');
    function showToast() {
        toast.style.display = 'block';
        setTimeout(function() {
            toast.style.display = 'none';
        }, 3000);
    }
    // Simulate a successful plan update
    showToast();
});
</script>

<?php require_once __DIR__ . '/../components/footer.php'; ?>
</body>
</html> 