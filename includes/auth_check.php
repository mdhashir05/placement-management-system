<?php
/**
 * Role-Based Authentication & Authorization Helpers
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Require User to be Logged In
 */
function require_login() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: " . APP_URL . "/login.php?error=please_login");
        exit();
    }
}

/**
 * Require Specific Role (admin, student, company)
 */
function require_role($allowed_roles) {
    require_login();
    if (is_string($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: " . APP_URL . "/login.php?error=unauthorized_access");
        exit();
    }
}

/**
 * Redirect if Already Logged In
 */
function redirect_if_logged_in() {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
        switch ($_SESSION['role']) {
            case 'admin':
                header("Location: " . APP_URL . "/admin/index.php");
                break;
            case 'student':
                header("Location: " . APP_URL . "/student/index.php");
                break;
            case 'company':
                header("Location: " . APP_URL . "/company/index.php");
                break;
        }
        exit();
    }
}
