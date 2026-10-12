<?php
$page_title = "Register Account - Placement Management System";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

redirect_if_logged_in();

$error = '';
$role = sanitize_input($_GET['role'] ?? 'student');

// Fetch Departments
$departments = [];
$dept_res = $conn->query("SELECT * FROM departments ORDER BY name ASC");
if ($dept_res) {
    $departments = $dept_res->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = sanitize_input($_POST['role'] ?? 'student');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitize_input($_POST['phone'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf_token($csrf_token)) {
        $error = "Invalid security token. Please try again.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        if ($role === 'student') {
            $full_name = sanitize_input($_POST['full_name'] ?? '');
            $roll_number = sanitize_input($_POST['roll_number'] ?? '');
            $department_id = intval($_POST['department_id'] ?? 0);
            $cgpa = floatval($_POST['cgpa'] ?? 0.0);
            $passing_year = intval($_POST['passing_year'] ?? date('Y'));

            // Check existing email or roll
            $stmt = $conn->prepare("SELECT id FROM students WHERE email = ? OR roll_number = ?");
            $stmt->bind_param("ss", $email, $roll_number);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "Email or Roll Number already registered.";
            } else {
                $stmt_ins = $conn->prepare("INSERT INTO students (roll_number, full_name, email, password, phone, department_id, cgpa, passing_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_ins->bind_param("sssssidi", $roll_number, $full_name, $email, $hashed_password, $phone, $department_id, $cgpa, $passing_year);
                if ($stmt_ins->execute()) {
                    header("Location: login.php?msg=registered");
                    exit();
                } else {
                    $error = "Registration failed. " . $conn->error;
                }
            }
        } elseif ($role === 'company') {
            $company_name = sanitize_input($_POST['company_name'] ?? '');
            $website = sanitize_input($_POST['website'] ?? '');
            $industry = sanitize_input($_POST['industry'] ?? '');
            $location = sanitize_input($_POST['location'] ?? '');
            $description = sanitize_input($_POST['description'] ?? '');

            // Check existing email
            $stmt = $conn->prepare("SELECT id FROM companies WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "Company Email already registered.";
            } else {
                $stmt_ins = $conn->prepare("INSERT INTO companies (company_name, email, password, phone, website, industry, location, description, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Approved')");
                $stmt_ins->bind_param("ssssssss", $company_name, $email, $hashed_password, $phone, $website, $industry, $location, $description);
                if ($stmt_ins->execute()) {
                    header("Location: login.php?msg=registered");
                    exit();
                } else {
                    $error = "Registration failed. " . $conn->error;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Account | Placement Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-container">

<div class="auth-card" style="max-width: 650px;">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-dark mb-1">Create an Account</h3>
        <p class="text-muted small">Join the Placement Portal as a Student or Hiring Partner</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

        <!-- Role Toggle -->
        <div class="mb-4">
            <label class="form-label small fw-semibold text-muted">Register As</label>
            <div class="btn-group w-100" role="group">
                <a href="register.php?role=student" class="btn btn-outline-primary <?php echo $role === 'student' ? 'active' : ''; ?>">
                    <i class="fas fa-user-graduate me-1"></i> Student Candidate
                </a>
                <a href="register.php?role=company" class="btn btn-outline-primary <?php echo $role === 'company' ? 'active' : ''; ?>">
                    <i class="fas fa-building me-1"></i> Recruiting Company
                </a>
            </div>
            <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
        </div>

        <?php if ($role === 'student'): ?>
            <!-- Student Form Fields -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required placeholder="John Doe">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Roll Number</label>
                    <input type="text" name="roll_number" class="form-control" required placeholder="CS2024-001">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Email Address</label>
                    <input type="email" name="email" class="form-control" required placeholder="student@university.edu">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="+1 555-0199">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Department</label>
                    <select name="department_id" class="form-select" required>
                        <option value="">Select Department...</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?> (<?php echo htmlspecialchars($dept['code']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">CGPA</label>
                    <input type="number" step="0.01" max="4.00" name="cgpa" class="form-control" required placeholder="3.80">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Passing Year</label>
                    <input type="number" name="passing_year" class="form-control" value="<?php echo date('Y'); ?>" required>
                </div>
            </div>

        <?php else: ?>
            <!-- Company Form Fields -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Company Name</label>
                    <input type="text" name="company_name" class="form-control" required placeholder="Acme Corp">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">HR / Official Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="hr@acme.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="+1 800-555-0100">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Official Website</label>
                    <input type="url" name="website" class="form-control" placeholder="https://acme.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Industry Sector</label>
                    <input type="text" name="industry" class="form-control" placeholder="Software / Cloud">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Headquarters / Location</label>
                    <input type="text" name="location" class="form-control" placeholder="Austin, TX">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold text-muted">Company Summary</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Brief overview of company business and tech stack..."></textarea>
                </div>
            </div>
        <?php endif; ?>

        <!-- Common Password Fields -->
        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Password</label>
                <input type="password" name="password" class="form-control" required minlength="6" placeholder="••••••••">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="6" placeholder="••••••••">
            </div>
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 py-2 rounded-pill mt-4">
            Complete Registration <i class="fas fa-arrow-right ms-1"></i>
        </button>
    </form>

    <div class="text-center mt-4">
        <span class="small text-muted">Already registered? </span>
        <a href="login.php" class="small fw-bold text-primary text-decoration-none">Sign In Here</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
