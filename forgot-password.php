<?php
$page_title = "Forgot Password - Placement Management System";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $role = sanitize_input($_POST['role'] ?? 'student');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        $error = "Invalid security token.";
    } elseif (empty($email)) {
        $error = "Please enter your registered email address.";
    } else {
        $table = ($role === 'company') ? 'companies' : (($role === 'admin') ? 'admin' : 'students');
        $stmt = $conn->prepare("SELECT id FROM {$table} WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $message = "If your account exists in our placement records, password recovery instructions have been sent to your email address.";
        } else {
            $message = "If your account exists in our placement records, password recovery instructions have been sent to your email address.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | Placement Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-container">

<div class="auth-card">
    <div class="text-center mb-4">
        <div class="bg-primary text-white d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:60px; height:60px;">
            <i class="fas fa-key fs-3"></i>
        </div>
        <h3 class="fw-bold text-dark mb-1">Reset Password</h3>
        <p class="text-muted small">Enter your email to receive a password reset link</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-info py-2 small mb-3">
            <i class="fas fa-info-circle me-1"></i> <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small mb-3">
            <i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="forgot-password.php" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

        <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Account Role</label>
            <select name="role" class="form-select" required>
                <option value="student">Student Candidate</option>
                <option value="company">Recruiting Company</option>
                <option value="admin">System Administrator</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold text-muted">Registered Email</label>
            <input type="email" class="form-control" id="email" name="email" required placeholder="name@example.com">
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 py-2 rounded-pill mt-2">
            Send Reset Instructions <i class="fas fa-paper-plane ms-1"></i>
        </button>
    </form>

    <div class="text-center mt-4">
        <a href="login.php" class="small fw-bold text-primary text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
