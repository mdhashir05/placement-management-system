<?php
$page_title = "Browse Jobs & Apply";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('student');

$student_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Application Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'apply') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $job_id = intval($_POST['job_id'] ?? 0);
    $cover_note = sanitize_input($_POST['cover_note'] ?? '');

    if (!verify_csrf_token($csrf_token)) {
        $error = "Invalid token.";
    } elseif ($job_id > 0) {
        // Check if student already applied
        $stmt_chk = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND student_id = ?");
        $stmt_chk->bind_param("ii", $job_id, $student_id);
        $stmt_chk->execute();
        if ($stmt_chk->get_result()->num_rows > 0) {
            $error = "You have already applied for this position.";
        } else {
            $stmt_app = $conn->prepare("INSERT INTO applications (job_id, student_id, cover_note, status) VALUES (?, ?, ?, 'Applied')");
            $stmt_app->bind_param("iis", $job_id, $student_id, $cover_note);
            if ($stmt_app->execute()) {
                $success = "Application submitted successfully!";
                log_activity($conn, 'student', $student_id, $_SESSION['user_email'], "Applied for job ID: {$job_id}");
            } else {
                $error = "Failed to submit application: " . $conn->error;
            }
            $stmt_app->close();
        }
        $stmt_chk->close();
    }
}

// Fetch Active Jobs with Application Status for this student
$sql = "
    SELECT j.*, c.company_name, c.logo, d.name AS dept_name,
           a.id AS applied_app_id, a.status AS application_status
    FROM jobs j
    JOIN companies c ON j.company_id = c.id
    LEFT JOIN departments d ON j.department_id = d.id
    LEFT JOIN applications a ON j.id = a.job_id AND a.student_id = ?
    WHERE j.status = 'Active'
    ORDER BY j.id DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">Explore Open Vacancies</h3>
                <p class="text-muted mb-0">Browse and apply to hiring company drives matching your career path</p>
            </div>

            <!-- Live Filter Input -->
            <div style="min-width: 280px;">
                <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                    <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="card-search-input" class="form-control border-0" placeholder="Search by title, skill, company...">
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small mb-3"><i class="fas fa-check-circle me-1"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small mb-3"><i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (empty($jobs)): ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fas fa-briefcase fs-1 mb-3 opacity-50"></i>
                    <p>No job drives are active right now. Please check back later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="col-md-6 col-lg-4 searchable-card">
                        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-semibold">
                                        <?php echo htmlspecialchars($job['job_type']); ?>
                                    </span>
                                    <small class="text-muted"><i class="far fa-clock me-1"></i> Deadline: <?php echo date('M d', strtotime($job['deadline'])); ?></small>
                                </div>
                                <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($job['title']); ?></h5>
                                <p class="text-primary fw-semibold mb-3"><i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($job['company_name']); ?></p>

                                <div class="d-flex flex-column gap-2 mb-3 text-muted small">
                                    <div><i class="fas fa-map-marker-alt text-secondary width-20 me-1"></i> <?php echo htmlspecialchars($job['location']); ?></div>
                                    <div><i class="fas fa-dollar-sign text-success width-20 me-1"></i> <strong>Package:</strong> <?php echo htmlspecialchars($job['salary_package']); ?></div>
                                    <div><i class="fas fa-graduation-cap text-info width-20 me-1"></i> <strong>Eligible CGPA:</strong> <?php echo htmlspecialchars($job['min_cgpa']); ?></div>
                                </div>

                                <div class="bg-light p-2 rounded-3 small text-muted mb-3" style="max-height: 80px; overflow-y: auto;">
                                    <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                                </div>
                            </div>

                            <div>
                                <hr class="my-3 text-secondary opacity-25">
                                <?php if ($job['applied_app_id']): ?>
                                    <button class="btn btn-secondary w-100 rounded-pill" disabled>
                                        <i class="fas fa-check-circle me-1"></i> Applied (<?php echo htmlspecialchars($job['application_status']); ?>)
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-primary-custom w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#applyModal<?php echo $job['id']; ?>">
                                        Apply for Position <i class="fas fa-paper-plane ms-1"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Application Modal -->
                    <div class="modal fade" id="applyModal<?php echo $job['id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Apply: <?php echo htmlspecialchars($job['title']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST" action="jobs.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="action" value="apply">
                                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">

                                    <div class="modal-body">
                                        <p class="small text-muted mb-3">You are submitting your profile & uploaded PDF resume to <strong><?php echo htmlspecialchars($job['company_name']); ?></strong>.</p>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted">Cover Note / Remarks (Optional)</label>
                                            <textarea name="cover_note" class="form-control" rows="3" placeholder="Briefly describe why you are a great fit for this position..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary-custom rounded-pill">Submit Application</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
