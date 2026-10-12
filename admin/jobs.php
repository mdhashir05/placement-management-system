<?php
$page_title = "Manage Job Postings";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');

$success = '';
$error = '';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $job_id = intval($_GET['id']);
    if ($_GET['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
        $stmt->bind_param("i", $job_id);
        if ($stmt->execute()) {
            $success = "Job posting removed.";
            log_activity($conn, 'admin', $_SESSION['user_id'], $_SESSION['user_email'], "Deleted job ID: {$job_id}");
        }
        $stmt->close();
    } elseif ($_GET['action'] === 'toggle') {
        $conn->query("UPDATE jobs SET status = IF(status='Active','Closed','Active') WHERE id = {$job_id}");
        $success = "Job status toggled.";
    }
}

$sql = "
    SELECT j.*, c.company_name, d.name AS dept_name,
           COUNT(a.id) AS applicants_count
    FROM jobs j
    JOIN companies c ON j.company_id = c.id
    LEFT JOIN departments d ON j.department_id = d.id
    GROUP BY j.id
    ORDER BY j.id DESC
";
$jobs = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Campus Job Drives</h3>
                <p class="text-muted mb-0">Oversee active campus placement openings across all corporate recruiters</p>
            </div>
            <input type="text" data-table-search="jobs-table" class="form-control rounded-pill" style="width: 260px;" placeholder="Search role, company...">
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small mb-3"><i class="fas fa-check-circle me-1"></i> <?php echo $success; ?></div>
        <?php endif; ?>

        <div class="glass-card p-4">
            <div class="table-responsive">
                <table class="table table-custom align-middle" id="jobs-table">
                    <thead>
                        <tr>
                            <th>Job Title & Company</th>
                            <th>Department</th>
                            <th>Package</th>
                            <th>Location</th>
                            <th>Applicants</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jobs)): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">No jobs posted.</td></tr>
                        <?php else: ?>
                            <?php foreach ($jobs as $j): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($j['title']); ?></div>
                                        <small class="text-primary fw-semibold"><?php echo htmlspecialchars($j['company_name']); ?></small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($j['dept_name'] ?? 'All'); ?></span></td>
                                    <td class="fw-bold text-success"><?php echo htmlspecialchars($j['salary_package']); ?></td>
                                    <td class="small text-muted"><?php echo htmlspecialchars($j['location']); ?></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2">
                                            <?php echo $j['applicants_count']; ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?php echo date('M d, Y', strtotime($j['deadline'])); ?></td>
                                    <td>
                                        <span class="badge-status <?php echo $j['status'] === 'Active' ? 'badge-active' : 'badge-closed'; ?>">
                                            <?php echo htmlspecialchars($j['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="jobs.php?action=toggle&id=<?php echo $j['id']; ?>" class="btn btn-sm btn-outline-warning rounded-pill" title="Toggle Active">
                                                <i class="fas fa-power-off"></i>
                                            </a>
                                            <a href="jobs.php?action=delete&id=<?php echo $j['id']; ?>" onclick="return confirm('Delete this job?');" class="btn btn-sm btn-outline-danger rounded-pill" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
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
