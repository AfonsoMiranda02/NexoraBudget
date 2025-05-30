<?php
require_once dirname(__DIR__, 3) . '/config.php';

function getDBConnection() {
    try {
        $db = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $db;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserData() {
    if (!isLoggedIn()) {
        return null;
    }
    $db = getDBConnection();
    if (!$db) {
        return null;
    }
    try {
        if (isset($_SESSION['account_type']) && ($_SESSION['account_type'] === 'client' || $_SESSION['account_type'] === 'master')) {
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } else {
            $stmt = $db->prepare("SELECT * FROM enterprise_users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        }
    } catch (PDOException $e) {
        error_log("Error fetching user data: " . $e->getMessage());
        return null;
    }
}

function getEnterpriseData($enterprise_id) {
    $db = getDBConnection();
    if (!$db) {
        return null;
    }

    try {
        $stmt = $db->prepare("SELECT * FROM enterprises WHERE id = ?");
        $stmt->execute([$enterprise_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching enterprise data: " . $e->getMessage());
        return null;
    }
}

function getEnterpriseUserData($user_id) {
    $db = getDBConnection();
    if (!$db) {
        return null;
    }

    try {
        $stmt = $db->prepare("SELECT * FROM enterprise_users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching enterprise user data: " . $e->getMessage());
        return null;
    }
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function getProjectStatus($status) {
    $statusClasses = [
        'active' => 'status-active',
        'completed' => 'status-completed',
        'upcoming' => 'status-upcoming'
    ];
    return $statusClasses[$status] ?? 'status-default';
}

function getTransactionType($type) {
    $typeClasses = [
        'revenue' => 'type-revenue',
        'expense' => 'type-expense'
    ];
    return $typeClasses[$type] ?? 'type-default';
}

// Funções de autenticação
function loginUser($email, $password) {
    $db = getDBConnection();
    if (!$db) {
        return false;
    }

    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

function registerUser($name, $email, $password, $phone = null) {
    $db = getDBConnection();
    if (!$db) {
        return false;
    }

    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$name, $email, $hashedPassword, $phone]);
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}

function logoutUser() {
    session_destroy();
    return true;
}

function getAllProjectsByEnterprise($enterprise_id) {
    $db = getDBConnection();
    if (!$db) {
        return [];
    }
    try {
        $stmt = $db->prepare("SELECT * FROM projects WHERE enterprise_id = ? ORDER BY id DESC");
        $stmt->execute([$enterprise_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching projects: " . $e->getMessage());
        return [];
    }
}

function isMasterUser($userId) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT is_master FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    return $user && $user['is_master'] == 1;
} 