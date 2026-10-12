<?php
$page_title = "Post Job Vacancy";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('company');

$company_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch Departments
$departments = [];
$res = $conn->query("SELECT * FROM departments ORDER BY name ASC");
if ($res) $departments = $res->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        $error = "Security token mismatch.";
    } else {
        $title = sanitize_input($_POST['title'] ?? '');
        $department_id = intval($_POST['department_id'] ?? 0);
        $job_type = sanitize_input($_POST['job_type'] ?? 'Full Time');
        $location = sanitize_input($_POST['location'] ?? '');
        $salary_package = sanitize_input($_POST['salary_package'] ?? '');
        $min_cgpa = floatval($_POST['min_cgpa'] ?? 0.0);
        $vacancies = intval($_POST['vacancies'] ?? 1);
        $deadline = sanitize_input($_POST['deadline'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $requirements = sanitize_input($_POST['requirements'] ?? '');

        if (empty($title) || empty($location) || empty($salary_package) || empty($deadline)) {
            $error = "Please fill in all mandatory job posting details.";
        } else {
            $stmt = $conn->prepare("INSERT INTO jobs (company_id, title, department_id, job_type, location, salary_package, min_cgpa, vacancies, deadline, description, requirements, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
            $stmt->bind_param("isissddisss", $company_id, $title, $department_id, $job_type, $location, $salary_package, $min_cgpa, $vacancies, $deadline, $description, $requirements);
            if ($stmt->execute()) {
                $success = "Job vacancy published successfully!";
                log_activity($conn, 'company', $company_id, $_SESSION['user_email'], "Posted new job: {$title}");
            } else {
                $error = "Error posting job: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Job | Company Portal</title>
</head>
<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-1">Post New Job Vacancy</h3>
            <p class="text-muted mb-0">Create a new placement drive listing for candidates</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small mb-3"><i class="fas fa-check-circle me-1"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small mb-3"><i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="glass-card p-4">
            <form method="POST" action="post-job.php" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold text-muted">Job Title / Designation</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Software Engineer Trainee">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Target Department</label>
                        <select name="department_id" class="form-select">
                            <option value="0">All Departments / Open</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Employment Type</label>
                        <select name="job_type" class="form-select" required>
                            <option value="Full Time">Full Time</option>
                            <option value="Internship">Internship</option>
                            <option value="Part Time">Part Time</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Job Location</label>
                        <input type="text" name="location" class="form-control" required placeholder="e.g. Seattle, WA / Remote">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Salary Package / Stipend</label>
                        <input type="text" name="salary_package" class="form-control" required placeholder="e.g. $85,000 / yr">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Minimum CGPA Cutoff</label>
                        <input type="number" step="0.01" max="4.00" name="min_cgpa" class="form-control" value="3.00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Number of Vacancies</label>
                        <input type="number" name="vacancies" class="form-control" value="5" min="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Application Deadline</label>
                        <input type="date" name="deadline" class="form-control" required value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Job Description & Responsibilities</label>
                        <textarea name="description" class="form-control" rows="4" required placeholder="Detailed role description..."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-muted">Skill Requirements & Eligibility</label>
                        <textarea name="requirements" class="form-control" rows="3" required placeholder="PHP, MySQL, Communication Skills..."></textarea>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">
                        Publish Job Opening <i class="fas fa-paper-plane ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
