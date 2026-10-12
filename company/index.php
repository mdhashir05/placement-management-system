<?php
$page_title = "Company Dashboard";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('company');

$company_id = $_SESSION['user_id'];

// Fetch Metrics
$active_jobs = 0;
$total_apps = 0;
$shortlisted_count = 0;
$selected_count = 0;

$stmt1 = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = ? AND status = 'Active'");
$stmt1->bind_param("i", $company_id);
$stmt1->execute();
$active_jobs = $stmt1->get_result()->fetch_row()[0];
$stmt1->close();

$stmt2 = $conn->prepare("
    SELECT a.status, COUNT(*) 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    WHERE j.company_id = ? 
    GROUP BY a.status
");
$stmt2->bind_param("i", $company_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($row = $res2->fetch_assoc()) {
    $total_apps += $row['COUNT(*)'];
    if ($row['status'] === 'Shortlisted') $shortlisted_count += $row['COUNT(*)'];
    if ($row['status'] === 'Selected') $selected_count += $row['COUNT(*)'];
}
$stmt2->close();

// Fetch Recent Job Applications Received
$recent_applicants = [];
$stmt3 = $conn->prepare("
    SELECT a.*, s.full_name AS student_name, s.email AS student_email, s.cgpa, s.resume_pdf, j.title AS job_title 
    FROM applications a 
    JOIN students s ON a.student_id = s.id 
    JOIN jobs j ON a.job_id = j.id 
    WHERE j.company_id = ? 
    ORDER BY a.id DESC LIMIT 5
");
$stmt3->bind_param("i", $company_id);
$stmt3->execute();
$recent_applicants = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt3->close();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Company Recruiter Dashboard</h3>
                <p class="text-muted mb-0">Managing hiring drives for <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></p>
            </div>
            <a href="post-job.php" class="btn btn-primary-custom rounded-pill">
                <i class="fas fa-plus-circle me-1"></i> Post New Job Drive
            </a>
        </div>

        <!-- Metric Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3 col-6">
                <div class="glass-card stat-card">
                    <div>
                        <span class="text-muted small fw-semibold">Active Openings</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo $active_jobs; ?></h3>
                    </div>
                    <div class="stat-icon primary"><i class="fas fa-briefcase"></i></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="glass-card stat-card">
                    <div>
                        <span class="text-muted small fw-semibold">Total Applications</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo $total_apps; ?></h3>
                    </div>
                    <div class="stat-icon purple"><i class="fas fa-users"></i></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="glass-card stat-card">
                    <div>
                        <span class="text-muted small fw-semibold">Shortlisted Candidates</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo $shortlisted_count; ?></h3>
                    </div>
                    <div class="stat-icon warning"><i class="fas fa-user-check"></i></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="glass-card stat-card">
                    <div>
                        <span class="text-muted small fw-semibold">Offers Extended</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo $selected_count; ?></h3>
                    </div>
                    <div class="stat-icon success"><i class="fas fa-trophy"></i></div>
                </div>
            </div>
        </div>

        <!-- Recent Applicants Received -->
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="fas fa-user-graduate text-primary me-2"></i> Recent Candidate Applications</h5>
                <a href="applicants.php" class="small text-primary fw-semibold text-decoration-none">Review All Applicants</a>
            </div>

            <?php if (empty($recent_applicants)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-user-clock fs-2 mb-2 opacity-50"></i>
                    <p class="mb-0 small">No candidate applications received yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Candidate Name</th>
                                <th>Position Applied</th>
                                <th>CGPA</th>
                                <th>Applied Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_applicants as $app): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($app['student_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($app['student_email']); ?></small>
                                    </td>
                                    <td class="fw-semibold text-primary"><?php echo htmlspecialchars($app['job_title']); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($app['cgpa']); ?> CGPA</span></td>
                                    <td class="small text-muted"><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                                    <td>
                                        <span class="badge-status badge-applied"><?php echo htmlspecialchars($app['status']); ?></span>
                                    </td>
                                    <td>
                                        <a href="applicants.php" class="btn btn-sm btn-outline-primary rounded-pill">Review</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
