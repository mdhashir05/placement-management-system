<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    log_activity($conn, $_SESSION['role'], $_SESSION['user_id'], $_SESSION['user_email'] ?? '', "User logged out");
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

header("Location: " . APP_URL . "/login.php");
exit();
