<?php
/**
 * Global Configuration & Security Constants
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie security settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// App Information
define('APP_NAME', 'Placement Management System');
define('APP_VERSION', '1.0.0');

// Dynamic APP_URL Detection (Works on built-in PHP server, Apache subfolder, or EC2 root)
if (!defined('APP_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $baseSubdir = (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/placement-management-system') !== false) ? '/placement-management-system' : '';
    define('APP_URL', $protocol . $host . $baseSubdir);
}

// Paths
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH_PROFILE', BASE_PATH . '/uploads/profile/');
define('UPLOAD_PATH_RESUME', BASE_PATH . '/uploads/resume/');

// Session Timeout (30 minutes of inactivity)
define('SESSION_TIMEOUT', 1800);

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    header("Location: " . APP_URL . "/login.php?msg=session_expired");
    exit();
}
$_SESSION['last_activity'] = time();

/**
 * Generate CSRF Token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

/**
 * Sanitize User Inputs to prevent XSS
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Log System Activity
 */
function log_activity($conn, $user_type, $user_id, $user_email, $action) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_type, user_id, user_email, action, ip_address) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sisss", $user_type, $user_id, $user_email, $action, $ip);
        $stmt->execute();
        $stmt->close();
    }
}
