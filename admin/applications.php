<?php
$page_title = "Manage Applications";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');

$success = '';

if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $app_id = intval($_POST['app_id']);
    $new_status = sanitize_input($_POST['status']);
    $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $app_id);
    if ($stmt->execute()) {
        $success = "Application status updated.";
        log_activity($conn, 'admin', $_SESSION['user_id'], $_SESSION['user_email'], "Updated application ID: {$app_id} to {$new_status}");
    }
    $stmt->close();
}

$sql = "
    SELECT a.*, s.full_name AS student_name, s.roll_number, s.cgpa, s.resume_pdf,
           j.title AS job_title, c.company_name
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN jobs j ON a.job_id = j.id
    JOIN companies c ON j.company_id = c.id
    ORDER BY a.id DESC
";
$applications = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Student Applications Tracking</h3>
                <p class="text-muted mb-0">System-wide view of all student applications and recruitment pipelines</p>
            </div>
            <input type="text" data-table-search="apps-table" class="form-control rounded-pill" style="width: 260px;" placeholder="Search candidate, job...">
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small mb-3"><i class="fas fa-check-circle me-1"></i> <?php echo $success; ?></div>
        <?php endif; ?>

        <div class="glass-card p-4">
            <div class="table-responsive">
                <table class="table table-custom align-middle" id="apps-table">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Job Role & Company</th>
                            <th>CGPA</th>
                            <th>Applied Date</th>
                            <th>Resume</th>
                            <th>Status</th>
                            <th>Quick Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No applications recorded.</td></tr>
                        <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($app['student_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($app['roll_number']); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary"><?php echo htmlspecialchars($app['job_title']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($app['company_name']); ?></small>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($app['cgpa']); ?></strong></td>
                                    <td class="small text-muted"><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                                    <td>
                                        <?php if ($app['resume_pdf']): ?>
                                            <a href="<?php echo APP_URL; ?>/uploads/resume/<?php echo htmlspecialchars($app['resume_pdf']); ?>" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill">
                                                <i class="fas fa-file-pdf"></i> Resume
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
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
                                        <form method="POST" action="applications.php" class="d-flex gap-1">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                            <select name="status" class="form-select form-select-sm rounded-pill" style="width:130px;" onchange="this.form.submit()">
                                                <option value="Applied" <?php echo $app['status'] === 'Applied' ? 'selected' : ''; ?>>Applied</option>
                                                <option value="Shortlisted" <?php echo $app['status'] === 'Shortlisted' ? 'selected' : ''; ?>>Shortlisted</option>
                                                <option value="Interview Scheduled" <?php echo $app['status'] === 'Interview Scheduled' ? 'selected' : ''; ?>>Interview</option>
                                                <option value="Selected" <?php echo $app['status'] === 'Selected' ? 'selected' : ''; ?>>Selected</option>
                                                <option value="Rejected" <?php echo $app['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
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
