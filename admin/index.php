<?php
$page_title = "Admin Dashboard";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');

// 1. Metric Counters
$total_students = 0;
$total_companies = 0;
$total_jobs = 0;
$total_applications = 0;
$total_interviews = 0;
$total_placements = 0;

$r1 = $conn->query("SELECT COUNT(*) FROM students");
if ($r1) $total_students = $r1->fetch_row()[0];

$r2 = $conn->query("SELECT COUNT(*) FROM companies");
if ($r2) $total_companies = $r2->fetch_row()[0];

$r3 = $conn->query("SELECT COUNT(*) FROM jobs");
if ($r3) $total_jobs = $r3->fetch_row()[0];

$r4 = $conn->query("SELECT COUNT(*) FROM applications");
if ($r4) $total_applications = $r4->fetch_row()[0];

$r5 = $conn->query("SELECT COUNT(*) FROM interviews");
if ($r5) $total_interviews = $r5->fetch_row()[0];

$r6 = $conn->query("SELECT COUNT(*) FROM applications WHERE status = 'Selected'");
if ($r6) $total_placements = $r6->fetch_row()[0];

// 2. Department-wise Placement Statistics for Chart
$dept_labels = [];
$dept_counts = [];
$dept_query = "
    SELECT d.code, COUNT(a.id) AS placed_count
    FROM departments d
    LEFT JOIN students s ON d.id = s.department_id
    LEFT JOIN applications a ON s.id = a.student_id AND a.status = 'Selected'
    GROUP BY d.id
";
$dept_res = $conn->query($dept_query);
if ($dept_res) {
    while ($row = $dept_res->fetch_assoc()) {
        $dept_labels[] = $row['code'];
        $dept_counts[] = (int)$row['placed_count'];
    }
}

// 3. Recent Placements / Applications
$recent_apps = [];
$recent_q = "
    SELECT a.*, s.full_name AS student_name, s.roll_number, j.title AS job_title, c.company_name
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN jobs j ON a.job_id = j.id
    JOIN companies c ON j.company_id = c.id
    ORDER BY a.id DESC LIMIT 5
";
$res_apps = $conn->query($recent_q);
if ($res_apps) {
    $recent_apps = $res_apps->fetch_all(MYSQLI_ASSOC);
}

// 4. Upcoming Interviews
$upcoming_interviews = [];
$ui_q = "
    SELECT i.*, s.full_name AS student_name, j.title AS job_title, c.company_name
    FROM interviews i
    JOIN applications a ON i.application_id = a.id
    JOIN students s ON a.student_id = s.id
    JOIN jobs j ON a.job_id = j.id
    JOIN companies c ON j.company_id = c.id
    WHERE i.interview_date >= NOW()
    ORDER BY i.interview_date ASC LIMIT 5
";
$res_ui = $conn->query($ui_q);
if ($res_ui) {
    $upcoming_interviews = $res_ui->fetch_all(MYSQLI_ASSOC);
}

// 5. Recent Activity Logs
$recent_logs = [];
$rl_q = "SELECT * FROM activity_logs ORDER BY id DESC LIMIT 5";
$res_logs = $conn->query($rl_q);
if ($res_logs) {
    $recent_logs = $res_logs->fetch_all(MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Administrative Control Center</h3>
                <p class="text-muted mb-0">Overview of campus placement drives, recruiters, and statistics</p>
            </div>
            <div class="d-flex gap-2">
                <a href="reports.php" class="btn btn-outline-primary rounded-pill">
                    <i class="fas fa-file-export me-1"></i> Export Reports
                </a>
                <a href="students.php" class="btn btn-primary-custom rounded-pill">
                    <i class="fas fa-user-plus me-1"></i> Manage Candidates
                </a>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-2 col-6">
                <div class="glass-card stat-card flex-column align-items-start">
                    <div class="stat-icon primary mb-2"><i class="fas fa-user-graduate"></i></div>
                    <span class="text-muted small fw-semibold">Students</span>
                    <h3 class="fw-bold text-dark mb-0"><?php echo number_format($total_students); ?></h3>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="glass-card stat-card flex-column align-items-start">
                    <div class="stat-icon success mb-2"><i class="fas fa-building"></i></div>
                    <span class="text-muted small fw-semibold">Companies</span>
                    <h3 class="fw-bold text-dark mb-0"><?php echo number_format($total_companies); ?></h3>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="glass-card stat-card flex-column align-items-start">
                    <div class="stat-icon purple mb-2"><i class="fas fa-briefcase"></i></div>
                    <span class="text-muted small fw-semibold">Jobs Posted</span>
                    <h3 class="fw-bold text-dark mb-0"><?php echo number_format($total_jobs); ?></h3>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="glass-card stat-card flex-column align-items-start">
                    <div class="stat-icon warning mb-2"><i class="fas fa-file-signature"></i></div>
                    <span class="text-muted small fw-semibold">Applications</span>
                    <h3 class="fw-bold text-dark mb-0"><?php echo number_format($total_applications); ?></h3>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="glass-card stat-card flex-column align-items-start">
                    <div class="stat-icon danger mb-2"><i class="fas fa-calendar-check"></i></div>
                    <span class="text-muted small fw-semibold">Interviews</span>
                    <h3 class="fw-bold text-dark mb-0"><?php echo number_format($total_interviews); ?></h3>
                </div>
            </div>
            <div class="col-md-2 col-6">
                <div class="glass-card stat-card flex-column align-items-start">
                    <div class="stat-icon success mb-2"><i class="fas fa-trophy"></i></div>
                    <span class="text-muted small fw-semibold">Placed Offers</span>
                    <h3 class="fw-bold text-success mb-0"><?php echo number_format($total_placements); ?></h3>
                </div>
            </div>
        </div>

        <!-- Graphs Section -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-chart-pie text-primary me-2"></i> Department-Wise Placements</h5>
                    <div style="max-height: 280px; position: relative;">
                        <canvas id="deptPlacementChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-chart-bar text-primary me-2"></i> Placement Offers by Month</h5>
                    <div style="max-height: 280px; position: relative;">
                        <canvas id="monthlyPlacementChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Grid: Recent Applications & System Audit Logs -->
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="fas fa-tasks text-primary me-2"></i> Recent Applications</h5>
                        <a href="applications.php" class="small text-primary fw-semibold text-decoration-none">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Candidate</th>
                                    <th>Company & Role</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_apps)): ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted">No applications found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recent_apps as $app): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($app['student_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($app['roll_number']); ?></small>
                                            </td>
                                            <td>
                                                <div class="text-primary fw-semibold"><?php echo htmlspecialchars($app['job_title']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($app['company_name']); ?></small>
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
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="fas fa-history text-primary me-2"></i> Audit Activity Logs</h5>
                        <a href="activity_logs.php" class="small text-primary fw-semibold text-decoration-none">View Full Log</a>
                    </div>
                    <?php if (empty($recent_logs)): ?>
                        <p class="text-muted small">No activity recorded yet.</p>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($recent_logs as $log): ?>
                                <div class="p-2 border-start border-3 border-primary bg-light rounded-end">
                                    <div class="small fw-semibold text-dark"><?php echo htmlspecialchars($log['action']); ?></div>
                                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                                        <span><i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($log['user_email'] ?? 'System'); ?></span>
                                        <span><?php echo date('M d, H:i', strtotime($log['created_at'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var deptLabels = <?php echo json_encode($dept_labels); ?>;
    var deptData = <?php echo json_encode($dept_counts); ?>;
    initAdminDashboardCharts(deptLabels, deptData);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
