<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../components/header.php';

// Forçar step=2 se enterprise está presente mas step não está
if (isset($_GET['enterprise']) && !isset($_GET['step'])) {
    header('Location: register.php?enterprise&step=2');
    exit;
}

$step = isset($_GET['step']) ? intval($_GET['step']) : (isset($_GET['enterprise']) ? 2 : 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['enterprise'])) {
    $db = getDBConnection();
    // Dados pessoais
    $personal_name = $_POST['personal_name'] ?? '';
    $personal_email = $_POST['personal_email'] ?? '';
    $personal_phone = $_POST['personal_phone'] ?? '';
    $personal_role = $_POST['personal_role'] ?? '';
    $personal_username = $_POST['personal_username'] ?? '';
    $personal_password = $_POST['personal_password'] ?? '';
    $personal_profile = $_POST['personal_profile'] ?? '';
    $personal_nif = $_POST['personal_nif'] ?? '';
    $personal_cc = $_POST['personal_cc'] ?? '';
    $personal_consent = isset($_POST['personal_consent']) ? 1 : 0;
    // Dados empresa
    $enterprise_name = $_POST['enterprise_name'] ?? '';
    $enterprise_type = $_POST['enterprise_type'] ?? '';
    $enterprise_email = $_POST['enterprise_email'] ?? '';
    $enterprise_phone = $_POST['enterprise_phone'] ?? '';
    $enterprise_address = $_POST['enterprise_address'] ?? '';
    $enterprise_nif = $_POST['enterprise_nif'] ?? '';
    $enterprise_sector = $_POST['enterprise_sector'] ?? '';
    $enterprise_employees = $_POST['enterprise_employees'] ?? null;
    $enterprise_revenue = $_POST['enterprise_revenue'] ?? '';
    $enterprise_responsible = $_POST['enterprise_responsible'] ?? '';
    $enterprise_certificates = $_POST['enterprise_certificates'] ?? '';
    $enterprise_country = $_POST['enterprise_country'] ?? '';
    $enterprise_activity_date = $_POST['enterprise_activity_date'] ?? null;
    $enterprise_website = $_POST['enterprise_website'] ?? '';

    // Validação de email/username em ambas as tabelas
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$personal_email, $personal_username]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['error'] = "Email or username already exists in users table.";
        header('Location: register.php?enterprise&step=2');
        exit;
    }
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM enterprise_users WHERE email = ? OR username = ?");
    $stmt->execute([$personal_email, $personal_username]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['error'] = "Email or username already exists in enterprise users table.";
        header('Location: register.php?enterprise&step=2');
        exit;
    }

    try {
        $db->beginTransaction();

        // Inserir empresa
        $stmt = $db->prepare("INSERT INTO enterprises (name, type, email, phone, address, nif, sector, employees, revenue, responsible, certificates, country, activity_date, website) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $enterprise_name, $enterprise_type, $enterprise_email, $enterprise_phone, $enterprise_address, $enterprise_nif, $enterprise_sector, $enterprise_employees, $enterprise_revenue, $enterprise_responsible, $enterprise_certificates, $enterprise_country, $enterprise_activity_date, $enterprise_website
        ]);
        $enterprise_id = $db->lastInsertId();

        // Hash da password
        $password_hash = $personal_password ? password_hash($personal_password, PASSWORD_DEFAULT) : '';

        // Inserir utilizador principal
        $stmt = $db->prepare("INSERT INTO enterprise_users (enterprise_id, name, email, phone, role, username, password, profile, nif, cc, consent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $enterprise_id, $personal_name, $personal_email, $personal_phone, $personal_role, $personal_username, $password_hash, $personal_profile, $personal_nif, $personal_cc, $personal_consent
        ]);

        // Definir tipo de conta na sessão
        $_SESSION['user_id'] = $enterprise_id;
        $_SESSION['user_name'] = $personal_name;
        $_SESSION['user_email'] = $personal_email;
        $_SESSION['account_type'] = 'enterprise';
        $_SESSION['enterprise_id'] = $enterprise_id;

        $db->commit();
        header('Location: register.php?enterprise&step=3');
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = "An error occurred during registration. Please try again.";
        header('Location: register.php?enterprise&step=2');
        exit;
    }
}

// --- PROCESSAMENTO REGISTO CLIENTE ---
// Certifique-se de criar as tabelas auxiliares abaixo no seu banco de dados:
/*
CREATE TABLE client_previous_companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    website VARCHAR(255),
    description TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE client_previous_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    website VARCHAR(255),
    description TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['client'])) {
    $db = getDBConnection();
    $name = trim($_POST['client_name'] ?? '');
    $email = trim($_POST['client_email'] ?? '');
    $phone = trim($_POST['client_phone'] ?? '');
    $cc = trim($_POST['client_cc'] ?? '');
    $address = trim($_POST['client_address'] ?? '');
    $username = trim($_POST['client_username'] ?? '');
    $password = $_POST['client_password'] ?? '';
    $password_confirm = $_POST['client_password_confirm'] ?? '';
    $interests = trim($_POST['client_interests'] ?? '');
    $enterprise_id = isset($_POST['enterprise_id']) ? intval($_POST['enterprise_id']) : null;
    $register_errors = [];
    $profile_image = null;

    if (!$name || !$email || !$phone || !$cc || !$address || !$username || !$password || !$password_confirm) {
        $register_errors[] = "All required fields must be filled.";
    }
    if ($password !== $password_confirm) {
        $register_errors[] = "Passwords do not match.";
    }

    // Validação de email/username em ambas as tabelas
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetchColumn() > 0) {
        $register_errors[] = "Email or username already exists in users table.";
    }
    
    if ($enterprise_id) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM enterprise_users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetchColumn() > 0) {
            $register_errors[] = "Email or username already exists in enterprise users table.";
        }
    }

    // Processar upload de imagem
    if (isset($_FILES['client_profile_image']) && $_FILES['client_profile_image']['error'] === UPLOAD_ERR_OK) {
        $imgDir = __DIR__ . '/../imgs/users/';
        if (!is_dir($imgDir)) mkdir($imgDir, 0777, true);
        $ext = pathinfo($_FILES['client_profile_image']['name'], PATHINFO_EXTENSION);
        $baseName = preg_replace('/[^a-zA-Z0-9_]/', '', strtolower($name));
        $code = 1;
        do {
            $imgName = $baseName . "_" . $code . "." . $ext;
            $imgPath = $imgDir . $imgName;
            $code++;
        } while (file_exists($imgPath));
        if (move_uploaded_file($_FILES['client_profile_image']['tmp_name'], $imgPath)) {
            $profile_image = $imgName;
        } else {
            $register_errors[] = "Error uploading image.";
        }
    }

    if (empty($register_errors)) {
        try {
            $db->beginTransaction();
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Inserir na tabela users
            $stmt = $db->prepare("INSERT INTO users (name, email, password, phone, cc, address, username, interests, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password_hash, $phone, $cc, $address, $username, $interests, $profile_image]);
            $user_id = $db->lastInsertId();

            // Se tiver empresa, inserir também na tabela enterprise_users
            if ($enterprise_id) {
                $stmt = $db->prepare("INSERT INTO enterprise_users (enterprise_id, name, email, phone, role, username, password, profile, cc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $enterprise_id, $name, $email, $phone, 'client', $username, $password_hash, 'client', $cc
                ]);
            }

            // Empresas anteriores
            if (!empty($_POST['company_names'])) {
                foreach ($_POST['company_names'] as $i => $company_name) {
                    $company_name = trim($company_name);
                    $company_website = trim($_POST['company_websites'][$i] ?? '');
                    $company_desc = trim($_POST['company_descriptions'][$i] ?? '');
                    if ($company_name) {
                        $stmt = $db->prepare("INSERT INTO client_companies (user_id, name, website, description) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$user_id, $company_name, $company_website, $company_desc]);
                    }
                }
            }

            // Projetos anteriores
            if (!empty($_POST['project_names'])) {
                foreach ($_POST['project_names'] as $i => $project_name) {
                    $project_name = trim($project_name);
                    $project_website = trim($_POST['project_websites'][$i] ?? '');
                    $project_desc = trim($_POST['project_descriptions'][$i] ?? '');
                    if ($project_name) {
                        $stmt = $db->prepare("INSERT INTO client_previous_projects (user_id, name, website, description) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$user_id, $project_name, $project_website, $project_desc]);
                    }
                }
            }

            $db->commit();

            // Login automático
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['account_type'] = 'client';
            $_SESSION['profile_image'] = $profile_image;
            if ($enterprise_id) {
                $_SESSION['enterprise_id'] = $enterprise_id;
            }

            header('Location: dashboard_client.php');
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $register_errors[] = "An error occurred during registration. Please try again.";
        }
    }
}
?>

<?php if (!isset($_GET['enterprise']) && !isset($_GET['client'])): ?>
<div class="register-container">
    <div class="register-cards">
        <div class="register-card">
            <h2>I'm a Client</h2>
            <div class="card-image">
                <img src="<?php echo BASE_URL; ?>/public/imgs/Biguser-full.png" alt="Client Icon">
            </div>
            <p>If you're a client working with a company that uses NexoraHub, you can easily request and manage your project budgets in just a few clicks. Submit a budget request, provide key details, and follow the process — all in one secure and organized platform.</p>
            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?client" class="choose-btn">Choose</a>
        </div>

        <div class="register-card">
            <h2>I'm an Enterprise</h2>
            <div class="card-image">
                <img src="<?php echo BASE_URL; ?>/public/imgs/Bigbuilding-line.png" alt="Enterprise Icon">
            </div>
            <p>As a NexoraHub client, you can organize your company's structure, assign employees to teams or divisions, and manage projects and budgets — all through one powerful and easy-to-use platform.</p>
            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?enterprise&step=2" class="choose-btn">Choose</a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($step === 2 && isset($_GET['enterprise'])): ?>
    <div class="register-stepper">
        <div class="step step-detail">1</div>
        <div class="step-line"></div>
        <div class="step step-active">2</div>
        <div class="step-line"></div>
        <div class="step step-inactive">3</div>
    </div>
    <div class="enterprise-form-container">
        <form class="enterprise-form" method="POST" action="register.php?enterprise&step=2">
            <div class="form-section">
                <h2>Personal</h2>
                <p class="form-subtitle">Here is where you give us some information about you so we can know you better</p>
                <div class="form-group">
                    <label for="personal_name">Name<span class="required">*</span></label>
                    <input type="text" id="personal_name" name="personal_name" required>
                </div>
                <div class="form-group">
                    <label for="personal_email">Email<span class="required">*</span></label>
                    <input type="email" id="personal_email" name="personal_email" required>
                </div>
                <div class="form-group">
                    <label for="personal_phone">Phone<span class="required">*</span></label>
                    <input type="tel" id="personal_phone" name="personal_phone" required>
                </div>
                <div class="form-group">
                    <label for="personal_role">Role in the company<span class="required">*</span></label>
                    <input type="text" id="personal_role" name="personal_role" required>
                </div>
                <div class="form-group">
                    <label for="personal_username">Username<span class="required">*</span></label>
                    <input type="text" id="personal_username" name="personal_username" required>
                </div>
                <div class="form-group">
                    <label for="personal_password">Password</label>
                    <div style="position:relative;display:flex;align-items:center;">
                        <input type="password" id="personal_password" name="personal_password" style="flex:1;">
                        <button type="button" id="togglePassword" tabindex="-1" style="background:none;border:none;position:absolute;right:8px;cursor:pointer;outline:none;">
                            <img src="<?php echo BASE_URL; ?>/public/imgs/eye.png" alt="Ver senha" id="eyeIcon" style="width:20px;height:20px;">
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="personal_profile">Professional Profile</label>
                    <input type="text" id="personal_profile" name="personal_profile">
                </div>
                <div class="form-group consent-group">
                    <input type="checkbox" id="personal_consent" name="personal_consent" required>
                    <label for="personal_consent">I consent to the processing of my data and accept the <a href="<?php echo BASE_URL; ?>/public/pages/terms.php" target="_blank">terms and RGPD</a><span class="required">*</span></label>
                </div>
                <div class="form-group">
                    <label for="personal_nif">NIF</label>
                    <input type="text" id="personal_nif" name="personal_nif">
                </div>
                <div class="form-group">
                    <label for="personal_cc">CC</label>
                    <input type="text" id="personal_cc" name="personal_cc">
                </div>
            </div>
            <div class="form-divider"></div>
            <div class="form-section">
                <h2>Enterprise</h2>
                <p class="form-subtitle">Here is where you give us some information about you so we can know your enterprise better</p>
                <div class="form-group">
                    <label for="enterprise_name">Enterprise Name<span class="required">*</span></label>
                    <input type="text" id="enterprise_name" name="enterprise_name" required>
                </div>
                <div class="form-group">
                    <label for="enterprise_type">Type<span class="required">*</span></label>
                    <input type="text" id="enterprise_type" name="enterprise_type" required>
                </div>
                <div class="form-group">
                    <label for="enterprise_email">Enterprise Email<span class="required">*</span></label>
                    <input type="email" id="enterprise_email" name="enterprise_email" required>
                </div>
                <div class="form-group">
                    <label for="enterprise_phone">Enterprise Phone<span class="required">*</span></label>
                    <input type="tel" id="enterprise_phone" name="enterprise_phone" required>
                </div>
                <div class="form-group">
                    <label for="enterprise_address">Address<span class="required">*</span></label>
                    <input type="text" id="enterprise_address" name="enterprise_address" required>
                </div>
                <div class="form-group">
                    <label for="enterprise_nif">NIF<span class="required">*</span></label>
                    <input type="text" id="enterprise_nif" name="enterprise_nif" required>
                </div>
                <div class="form-group">
                    <label for="enterprise_sector">Sector<span class="required">*</span></label>
                    <input type="text" id="enterprise_sector" name="enterprise_sector" required>
                </div>
                <div class="form-group">
                    <label for="enterprise_employees">Number of Employees</label>
                    <input type="number" id="enterprise_employees" name="enterprise_employees" min="1">
                </div>
                <div class="form-group">
                    <label for="enterprise_revenue">Annual Revenue</label>
                    <input type="text" id="enterprise_revenue" name="enterprise_revenue">
                </div>
                <div class="form-group">
                    <label for="enterprise_responsible">Responsible<span class="required">*</span></label>
                    <input type="text" id="enterprise_responsible" name="enterprise_responsible" required>
                </div>
                <div class="form-group">
                    <label for="enterprise_certificates">Certificates</label>
                    <input type="text" id="enterprise_certificates" name="enterprise_certificates">
                </div>
                <div class="form-group">
                    <label for="enterprise_country">Country<span class="required">*</span></label>
                    <input type="text" id="enterprise_country" name="enterprise_country" required>
                </div>
                <div class="form-group">
                    <label for="enterprise_activity_date">Activity Date</label>
                    <input type="date" id="enterprise_activity_date" name="enterprise_activity_date">
                </div>
                <div class="form-group">
                    <label for="enterprise_website">Official Website</label>
                    <input type="url" id="enterprise_website" name="enterprise_website">
                </div>
            </div>
            <button type="button" class="register-btn" id="fill-fake-data" style="margin-bottom: 12px; background: #7c3aed;">Preencher com dados de exemplo</button>
            <button type="submit" class="register-btn">Register</button>
        </form>
    </div>
<?php elseif ($step === 3 && isset($_GET['enterprise'])): ?>
    <div class="register-stepper">
        <div class="step step-detail">1</div>
        <div class="step-line"></div>
        <div class="step step-detail">2</div>
        <div class="step-line"></div>
        <div class="step step-active">3</div>
    </div>
    <div class="choose-plan-container">
        <h2>Choose Your Plan</h2>
        <div class="pricing-toggle">
            <button class="toggle-btn active" data-period="monthly">Monthly</button>
            <button class="toggle-btn" data-period="annually">Annually</button>
        </div>
        <div class="pricing-cards">
            <form method="POST" action="<?php echo BASE_URL; ?>/public/php/auth/stripe_redirect.php" style="display:inline;">
                <input type="hidden" name="plan" value="standard">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['enterprise_email'] ?? $enterprise_email ?? ''); ?>">
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
                    <button class="get-btn" type="submit">Obter</button>
                </div>
            </form>
            <form method="POST" action="<?php echo BASE_URL; ?>/public/php/auth/stripe_redirect.php" style="display:inline;">
                <input type="hidden" name="plan" value="plus">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['enterprise_email'] ?? $enterprise_email ?? ''); ?>">
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
                    <button class="get-btn" type="submit">Obter</button>
                </div>
            </form>
            <form method="POST" action="<?php echo BASE_URL; ?>/public/php/auth/stripe_redirect.php" style="display:inline;">
                <input type="hidden" name="plan" value="premium">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['enterprise_email'] ?? $enterprise_email ?? ''); ?>">
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
                    <button class="get-btn" type="submit">Obter</button>
                </div>
            </form>
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
                toggleButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                const period = this.dataset.period;
                amounts.forEach(amount => {
                    amount.textContent = amount.dataset[period];
                });
                periods.forEach(periodElement => {
                    periodElement.textContent = periodElement.dataset[period];
                });
                discounts.forEach(discount => {
                    discount.style.display = period === 'annually' ? 'block' : 'none';
                });
            });
        });
    });
    </script>
<?php endif; ?>

<?php if (isset($_GET['client'])): ?>
<div class="register-stepper">
    <div class="step step-active">1</div>
    <div class="step-line"></div>
    <div class="step step-inactive">2</div>
    <div class="step-line"></div>
    <div class="step step-inactive">3</div>
</div>
<div class="enterprise-form-container">
    <form class="enterprise-form" method="POST" action="register.php?client" enctype="multipart/form-data">
        <div class="form-section">
            <h2>Personal</h2>
            <p class="form-subtitle">Tell us about yourself so we can know you better</p>
            <div class="form-group">
                <label for="client_name">Name<span class="required">*</span></label>
                <input type="text" id="client_name" name="client_name" required>
            </div>
            <div class="form-group">
                <label for="client_email">Email<span class="required">*</span></label>
                <input type="email" id="client_email" name="client_email" required>
            </div>
            <div class="form-group">
                <label for="client_phone">Phone<span class="required">*</span></label>
                <input type="tel" id="client_phone" name="client_phone" required>
            </div>
            <div class="form-group">
                <label for="client_cc">CC<span class="required">*</span></label>
                <input type="text" id="client_cc" name="client_cc" required>
            </div>
            <div class="form-group">
                <label for="client_address">Address<span class="required">*</span></label>
                <input type="text" id="client_address" name="client_address" required>
            </div>
            <div class="form-group">
                <label for="client_username">Username<span class="required">*</span></label>
                <input type="text" id="client_username" name="client_username" required>
            </div>
            <div class="form-group">
                <label for="client_password">Password<span class="required">*</span></label>
                <div style="position:relative;display:flex;align-items:center;">
                    <input type="password" id="client_password" name="client_password" required style="flex:1;">
                    <button type="button" id="togglePasswordClient" tabindex="-1" style="background:none;border:none;position:absolute;right:8px;cursor:pointer;outline:none;">
                        <img src="<?php echo BASE_URL; ?>/public/imgs/eye.png" alt="Ver senha" id="eyeIconClient" style="width:20px;height:20px;">
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label for="client_password_confirm">Confirm Password<span class="required">*</span></label>
                <input type="password" id="client_password_confirm" name="client_password_confirm" required>
            </div>
            <div class="form-group">
                <label for="client_interests">Interests</label>
                <input type="text" id="client_interests" name="client_interests">
            </div>
            <div class="form-group">
                <label for="enterprise_id">Associated Enterprise (Optional)</label>
                <select id="enterprise_id" name="enterprise_id">
                    <option value="">Select an enterprise (optional)</option>
                    <?php
                    $db = getDBConnection();
                    $stmt = $db->query("SELECT id, name FROM enterprises ORDER BY name");
                    while ($enterprise = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . htmlspecialchars($enterprise['id']) . "'>" . htmlspecialchars($enterprise['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="client_profile_image">Profile Image</label>
                <input type="file" id="client_profile_image" name="client_profile_image" accept="image/*">
            </div>
        </div>
        <div class="form-divider"></div>
        <div class="form-section">
            <h2>Professional Experience</h2>
            <p class="form-subtitle">Tell us about your previous companies and projects</p>
            <div class="form-group">
                <label>Previous Companies Worked At</label>
                <div class="table-scroll">
                <table id="companiesTable" class="editable-table">
                    <thead>
                        <tr><th>Company Name</th><th>Website</th><th>Description</th><th>Action</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
                </div>
                <button type="button" id="addCompanyBtn" class="register-btn" style="margin-top:8px;">Add Company</button>
            </div>
            <div class="form-group">
                <label>Previous Projects</label>
                <div class="table-scroll">
                <table id="projectsTable" class="editable-table">
                    <thead>
                        <tr><th>Project Name</th><th>Website</th><th>Description</th><th>Action</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
                </div>
                <button type="button" id="addProjectBtn" class="register-btn" style="margin-top:8px;">Add Project</button>
            </div>
        </div>
        <button type="submit" class="register-btn">Register</button>
    </form>
</div>
<script>
function addCompanyRow(name = '', website = '', desc = '') {
    const table = document.getElementById('companiesTable').querySelector('tbody');
    const row = document.createElement('tr');
    row.innerHTML = `<td><input type='text' name='company_names[]' value='${name}' required></td>
                     <td><input type='url' name='company_websites[]' value='${website}'></td>
                     <td><input type='text' name='company_descriptions[]' value='${desc}'></td>
                     <td><button type='button' class='remove-row-btn'>Remove</button></td>`;
    table.appendChild(row);
}
function addProjectRow(name = '', website = '', desc = '') {
    const table = document.getElementById('projectsTable').querySelector('tbody');
    const row = document.createElement('tr');
    row.innerHTML = `<td><input type='text' name='project_names[]' value='${name}' required></td>
                     <td><input type='url' name='project_websites[]' value='${website}'></td>
                     <td><input type='text' name='project_descriptions[]' value='${desc}'></td>
                     <td><button type='button' class='remove-row-btn'>Remove</button></td>`;
    table.appendChild(row);
}
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('addCompanyBtn').onclick = function() { addCompanyRow(); };
    document.getElementById('addProjectBtn').onclick = function() { addProjectRow(); };
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row-btn')) {
            e.target.closest('tr').remove();
        }
    });
    // Toggle password visibility for client
    var toggle = document.getElementById('togglePasswordClient');
    var input = document.getElementById('client_password');
    var icon = document.getElementById('eyeIconClient');
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
<style>
.table-scroll { max-width: 100%; overflow-x: auto; }
.editable-table { width:100%; min-width:700px; border-collapse:collapse; margin-bottom:8px; }
.editable-table th, .editable-table td { border:1px solid #e5e7eb; padding:6px; }
.editable-table th { background:#f9fafb; }
.remove-row-btn { background:#ef4444; color:#fff; border:none; border-radius:4px; padding:4px 10px; cursor:pointer; }
.remove-row-btn:hover { background:#b91c1c; }
</style>
<?php endif; ?>

<?php if (isset($_GET['client']) && $_GET['client'] === 'success'): ?>
    <div class="register-success" style="color:#22c55e;background:#f0fff4;padding:10px 16px;border-radius:6px;margin-bottom:16px;text-align:center;font-weight:500;">
        Registration successful! You can now log in.
    </div>
<?php endif; ?>
<?php if (!empty($register_errors)): ?>
    <div class="register-error" style="color:#ef4444;background:#fff0f0;padding:10px 16px;border-radius:6px;margin-bottom:16px;text-align:center;font-weight:500;">
        <?php foreach ($register_errors as $err) echo htmlspecialchars($err) . '<br>'; ?>
    </div>
<?php endif; ?>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('fill-fake-data');
    if (btn) {
        btn.addEventListener('click', function() {
            document.getElementById('personal_name').value = 'João Exemplo';
            document.getElementById('personal_email').value = 'joao@example.com';
            document.getElementById('personal_phone').value = '912345678';
            document.getElementById('personal_role').value = 'CEO';
            document.getElementById('personal_username').value = 'joaoexemplo';
            document.getElementById('personal_password').value = 'SenhaForte123';
            document.getElementById('personal_profile').value = 'Gestor de Projetos';
            document.getElementById('personal_consent').checked = true;
            document.getElementById('personal_nif').value = '123456789';
            document.getElementById('personal_cc').value = '987654321';
            document.getElementById('enterprise_name').value = 'Empresa Exemplo Lda';
            document.getElementById('enterprise_type').value = 'Tecnologia';
            document.getElementById('enterprise_email').value = 'empresa@example.com';
            document.getElementById('enterprise_phone').value = '211234567';
            document.getElementById('enterprise_address').value = 'Rua Exemplo, 123, Lisboa';
            document.getElementById('enterprise_nif').value = '501234567';
            document.getElementById('enterprise_sector').value = 'Informática';
            document.getElementById('enterprise_employees').value = '50';
            document.getElementById('enterprise_revenue').value = '1.000.000€';
            document.getElementById('enterprise_responsible').value = 'Maria Responsável';
            document.getElementById('enterprise_certificates').value = 'ISO 9001';
            document.getElementById('enterprise_country').value = 'Portugal';
            document.getElementById('enterprise_activity_date').value = '2010-05-20';
            document.getElementById('enterprise_website').value = 'https://empresaexemplo.pt';
        });
    }

    var toggle = document.getElementById('togglePassword');
    var input = document.getElementById('personal_password');
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
 