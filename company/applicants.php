<?php
$page_title = "Applicants Review";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('company');

$company_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Application Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $app_id = intval($_POST['app_id'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? 'Applied');

    if (!verify_csrf_token($csrf_token)) {
        $error = "Security token error.";
    } elseif ($app_id > 0) {
        $stmt = $conn->prepare("
            UPDATE applications a 
            JOIN jobs j ON a.job_id = j.id 
            SET a.status = ? 
            WHERE a.id = ? AND j.company_id = ?
        ");
        $stmt->bind_param("sii", $status, $app_id, $company_id);
        if ($stmt->execute()) {
            $success = "Candidate application status updated to '{$status}'!";
        } else {
            $error = "Failed to update status.";
        }
        $stmt->close();
    }
}

// Fetch Applicants
$job_filter = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
$sql = "
    SELECT a.*, s.full_name AS student_name, s.email AS student_email, s.phone AS student_phone, s.cgpa, s.passing_year, s.resume_pdf, s.profile_pic, j.title AS job_title 
    FROM applications a 
    JOIN students s ON a.student_id = s.id 
    JOIN jobs j ON a.job_id = j.id 
    WHERE j.company_id = ?
";
if ($job_filter > 0) {
    $sql .= " AND a.job_id = {$job_filter}";
}
$sql .= " ORDER BY a.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$applicants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Candidate Applicants Review</h3>
                <p class="text-muted mb-0">Evaluate student resumes and update shortlisting status</p>
            </div>
            <input type="text" data-table-search="applicants-table" class="form-control rounded-pill" style="width:250px;" placeholder="Search candidate name...">
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small mb-3"><i class="fas fa-check-circle me-1"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small mb-3"><i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="glass-card p-4">
            <div class="table-responsive">
                <table class="table table-custom align-middle" id="applicants-table">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Applied Job</th>
                            <th>CGPA & Year</th>
                            <th>Resume PDF</th>
                            <th>Status</th>
                            <th>Update Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applicants)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No applicants found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($applicants as $app): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($app['student_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($app['student_email']); ?></small>
                                    </td>
                                    <td class="fw-semibold text-primary"><?php echo htmlspecialchars($app['job_title']); ?></td>
                                    <td>
                                        <div class="small fw-bold text-dark"><?php echo htmlspecialchars($app['cgpa']); ?> CGPA</div>
                                        <small class="text-muted">Batch: <?php echo htmlspecialchars($app['passing_year']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($app['resume_pdf']): ?>
                                            <a href="<?php echo APP_URL; ?>/uploads/resume/<?php echo htmlspecialchars($app['resume_pdf']); ?>" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill">
                                                <i class="fas fa-file-pdf me-1"></i> View Resume
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Not Uploaded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-applied';
                                        if ($app['status'] === 'Shortlisted') $badgeClass = 'badge-shortlisted';
                                        if ($app['status'] === 'Interview Scheduled') $badgeClass = 'badge-scheduled';
                                        if ($app['status'] === 'Selected') $badgeClass = 'badge-selected';
                                        if ($app['status'] === 'Rejected') $badgeClass = 'badge-rejected';
                                        ?>
                                        <span class="badge-status <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($app['status']); ?></span>
                                    </td>
                                    <td>
                                        <form method="POST" action="applicants.php" class="d-flex gap-2">
                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">

                                            <select name="status" class="form-select form-select-sm rounded-pill" style="width: 140px;">
                                                <option value="Applied" <?php echo $app['status'] === 'Applied' ? 'selected' : ''; ?>>Applied</option>
                                                <option value="Shortlisted" <?php echo $app['status'] === 'Shortlisted' ? 'selected' : ''; ?>>Shortlisted</option>
                                                <option value="Interview Scheduled" <?php echo $app['status'] === 'Interview Scheduled' ? 'selected' : ''; ?>>Interview</option>
                                                <option value="Selected" <?php echo $app['status'] === 'Selected' ? 'selected' : ''; ?>>Selected</option>
                                                <option value="Rejected" <?php echo $app['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary rounded-pill">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
