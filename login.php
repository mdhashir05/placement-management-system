<?php
$page_title = "Login - Placement Management System";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

redirect_if_logged_in();

$error = '';
$success = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'registered') {
    $success = "Registration successful! You can now log in.";
}
if (isset($_GET['msg']) && $_GET['msg'] === 'session_expired') {
    $error = "Session expired due to inactivity. Please log in again.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize_input($_POST['role'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        $error = "Invalid security token. Please refresh and try again.";
    } elseif (empty($email) || empty($password) || empty($role)) {
        $error = "Please fill in all required fields.";
    } else {
        $user = null;
        $table = '';

        if ($role === 'admin') {
            $table = 'admin';
        } elseif ($role === 'student') {
            $table = 'students';
        } elseif ($role === 'company') {
            $table = 'companies';
        }

        if ($table) {
            $stmt = $conn->prepare("SELECT * FROM {$table} WHERE email = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
            }
        }

        if ($user && password_verify($password, $user['password'])) {
            // Check Company Status approval
            if ($role === 'company' && $user['status'] === 'Pending') {
                $error = "Your company account is pending Admin approval.";
            } elseif ($role === 'company' && $user['status'] === 'Rejected') {
                $error = "Your company registration was rejected. Please contact placement cell.";
            } else {
                // Regenerate Session ID for Security
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $role;
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $role === 'company' ? $user['company_name'] : $user['full_name'];

                log_activity($conn, $role, $user['id'], $user['email'], "User logged in successfully");

                // Redirect to respective dashboard
                header("Location: " . APP_URL . "/{$role}/index.php");
                exit();
            }
        } else {
            $error = "Invalid email, password, or role selection.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Placement Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-container">

<div class="auth-card">
    <div class="text-center mb-4">
        <div class="bg-primary text-white d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:60px; height:60px;">
            <i class="fas fa-user-lock fs-3"></i>
        </div>
        <h3 class="fw-bold text-dark mb-1">Welcome Back</h3>
        <p class="text-muted small">Access your Placement System Portal</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show small py-2" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

        <!-- Role Selection Tabs -->
        <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Select Account Role</label>
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="role" id="role-student" value="student" checked>
                <label class="btn btn-outline-primary" for="role-student"><i class="fas fa-user-graduate me-1"></i> Student</label>

                <input type="radio" class="btn-check" name="role" id="role-company" value="company">
                <label class="btn btn-outline-primary" for="role-company"><i class="fas fa-building me-1"></i> Company</label>

                <input type="radio" class="btn-check" name="role" id="role-admin" value="admin">
                <label class="btn btn-outline-primary" for="role-admin"><i class="fas fa-user-shield me-1"></i> Admin</label>
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold text-muted">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" required placeholder="name@example.com">
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="form-label small fw-semibold text-muted">Password</label>
                <a href="forgot-password.php" class="small text-decoration-none text-primary">Forgot Password?</a>
            </div>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-key text-muted"></i></span>
                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" required placeholder="••••••••">
            </div>
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 py-2 rounded-pill mt-2">
            Sign In <i class="fas fa-sign-in-alt ms-1"></i>
        </button>
    </form>

    <div class="text-center mt-4">
        <span class="small text-muted">Don't have an account? </span>
        <a href="register.php" class="small fw-bold text-primary text-decoration-none">Register Here</a>
    </div>

    <div class="text-center mt-3">
        <a href="index.php" class="small text-muted text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Back to Homepage</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
