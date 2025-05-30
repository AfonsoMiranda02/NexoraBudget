<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/components/header.php';
?>

<div class="hero">
    <img src="<?php echo BASE_URL; ?>/public/imgs/BigLogo.png" alt="NexoraHub Big Logo" class="big-logo" style="display:block; margin:0 auto 24px; max-width:220px;">
    <div class="hero-overlay">
        <h1>Welcome to NexoraHub</h1>
        <p>Streamline your project management and budget control with our powerful platform</p>
    </div>
</div>

<div class="about">
    <div class="about-content">
        <div class="about-text">
            <h2>About NexoraHub</h2>
            <p>NexoraHub is a comprehensive project management platform designed to bridge the gap between enterprises and their clients. Our solution enables seamless collaboration, efficient budget management, and transparent project tracking.</p>
        </div>
        <div class="about-image">
            <img src="<?php echo BASE_URL; ?>/public/imgs/about-image.png" alt="About NexoraHub">
        </div>
    </div>
</div>

<div class="pricing">
    <h2>Pricing</h2>
    <div class="pricing-toggle">
        <button class="toggle-btn active" data-period="monthly">Monthly</button>
        <button class="toggle-btn" data-period="annually">Annually</button>
    </div>
    <div class="pricing-cards">
        <div class="pricing-card">
            <h3>Standard Enterprise Plan</h3>
            <div class="divider"></div>
            <div class="price">
                <div class="price-row">
                    <span class="amount" data-monthly="$39.99" data-annually="$399.99">$39.99</span>
                    <span class="period" data-monthly="/Month" data-annually="/Year">/Month</span>
                </div>
                <span class="discount" data-monthly="" data-annually="20% OFF">20% OFF</span>
            </div>
            <ul class="features">
                <li><i class="fas fa-check"></i> Access to the Budget management System</li>
                <li><i class="fas fa-check"></i> 1 Subsidiary Limit</li>
                <li><i class="fas fa-check"></i> 1 Branche Limit</li>
                <li><i class="fas fa-check"></i> 3 Division limit</li>
                <li><i class="fas fa-check"></i> 5 Department limit per Division</li>
                <li><i class="fas fa-check"></i> 5 Equips limit per Department</li>
                <li><i class="fas fa-check"></i> 5 Sectors limit per Division</li>
            </ul>
            <button class="get-btn">Obter</button>
        </div>
        <div class="pricing-card">
            <h3>Plus Enterprise Plan</h3>
            <div class="divider"></div>
            <div class="price">
                <div class="price-row">
                    <span class="amount" data-monthly="$99.99" data-annually="$999.99">$99.99</span>
                    <span class="period" data-monthly="/Month" data-annually="/Year">/Month</span>
                </div>
                <span class="discount" data-monthly="" data-annually="20% OFF">20% OFF</span>
            </div>
            <ul class="features">
                <li><i class="fas fa-check"></i> Access to the Budget management System</li>
                <li><i class="fas fa-check"></i> 3 Subsidiary Limit</li>
                <li><i class="fas fa-check"></i> 5 Branche Limit</li>
                <li><i class="fas fa-check"></i> 5 Division limit</li>
                <li><i class="fas fa-check"></i> 10 Department limit per Division</li>
                <li><i class="fas fa-check"></i> 10 Equips limit per Department</li>
                <li><i class="fas fa-check"></i> 10 Sectors limit per Division</li>
            </ul>
            <button class="get-btn">Obter</button>
        </div>
        <div class="pricing-card">
            <h3>Premium Enterprise Plan</h3>
            <div class="divider"></div>
            <div class="price">
                <div class="price-row">
                    <span class="amount" data-monthly="$249.99" data-annually="$2499.99">$249.99</span>
                    <span class="period" data-monthly="/Month" data-annually="/Year">/Month</span>
                </div>
                <span class="discount" data-monthly="" data-annually="20% OFF">20% OFF</span>
            </div>
            <ul class="features">
                <li><i class="fas fa-check"></i> Access to the Budget management System</li>
                <li><i class="fas fa-check"></i> Unlimited Subsidiaries</li>
                <li><i class="fas fa-check"></i> Unlimited Branches</li>
                <li><i class="fas fa-check"></i> Unlimited Divisions</li>
                <li><i class="fas fa-check"></i> Unlimited Departments</li>
                <li><i class="fas fa-check"></i> Unlimited Equips</li>
                <li><i class="fas fa-check"></i> Unlimited Sectors</li>
            </ul>
            <button class="get-btn">Obter</button>
        </div>
    </div>
</div>

<div class="offers">
    <h2>What do we have to offer?</h2>
    <p class="offers-description">If you have any question about our support or about the application that isn't answered here or on the NexoraHub page, feel free to contact our AI-powered support. You can also send us an email or submit a report by selecting the Questions category.</p>
    <div class="offers-grid">
        <div class="offer-card">
            <img src="<?php echo BASE_URL; ?>/public/imgs/wallet-3-line.png" alt="Budget Management" class="offer-icon">
            <h3>Budget Management System</h3>
            <p>Optimize your budget distribution with maximum efficiency.</p>
        </div>
        <div class="offer-card">
            <img src="<?php echo BASE_URL; ?>/public/imgs/id-card-line.png" alt="Employees Management" class="offer-icon">
            <h3>Employees Management System</h3>
            <p>Manage your employees by easily assigning them to teams, projects, and more.</p>
        </div>
        <div class="offer-card">
            <img src="<?php echo BASE_URL; ?>/public/imgs/building-line.png" alt="Enterprise Structure" class="offer-icon">
            <h3>Enterprise Structure Management</h3>
            <p>Organize your company by managing teams, projects, divisions, and more.</p>
        </div>
        <div class="offer-card">
            <img src="<?php echo BASE_URL; ?>/public/imgs/user-line.png" alt="Roles and Permissions" class="offer-icon">
            <h3>Roles and Permissions Management</h3>
            <p>Create custom roles and control what each user can access or manage.</p>
        </div>
        <div class="offer-card">
            <img src="<?php echo BASE_URL; ?>/public/imgs/line-chart-line.png" alt="Budget Analytics" class="offer-icon">
            <h3>Budget Analytics and Reports</h3>
            <p>Generate detailed reports and analytics about your project expenses.</p>
        </div>
        <div class="offer-card">
            <img src="<?php echo BASE_URL; ?>/public/imgs/notification-4-line.png" alt="Notifications" class="offer-icon">
            <h3>Notification and Reminder System</h3>
            <p>Stay informed with notifications about deadlines, project milestones, and more.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.toggle-btn');
    const amounts = document.querySelectorAll('.amount');
    const periods = document.querySelectorAll('.period');
    const discounts = document.querySelectorAll('.discount');

    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            toggleButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');

            const period = this.dataset.period;
            
            // Update prices and periods
            amounts.forEach(amount => {
                amount.textContent = amount.dataset[period];
            });

            periods.forEach(periodElement => {
                periodElement.textContent = periodElement.dataset[period];
            });

            // Update discount visibility
            discounts.forEach(discount => {
                discount.style.display = period === 'annually' ? 'block' : 'none';
            });
        });
    });
});
</script>

<?php
require_once __DIR__ . '/components/footer.php';
?> 