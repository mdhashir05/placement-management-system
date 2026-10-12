<?php
$page_title = "Student Dashboard";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('student');

$student_id = $_SESSION['user_id'];

// Fetch Student Metrics
$total_applied = 0;
$shortlisted_count = 0;
$interviews_count = 0;
$selected_count = 0;

$stmt1 = $conn->prepare("SELECT status, COUNT(*) FROM applications WHERE student_id = ? GROUP BY status");
$stmt1->bind_param("i", $student_id);
$stmt1->execute();
$res1 = $stmt1->get_result();
while ($row = $res1->fetch_assoc()) {
    $total_applied += $row['COUNT(*)'];
    if ($row['status'] === 'Shortlisted') $shortlisted_count += $row['COUNT(*)'];
    if ($row['status'] === 'Interview Scheduled') $interviews_count += $row['COUNT(*)'];
    if ($row['status'] === 'Selected') $selected_count += $row['COUNT(*)'];
}
$stmt1->close();

// Fetch Recent Applications
$recent_apps = [];
$stmt2 = $conn->prepare("
    SELECT a.*, j.title AS job_title, c.company_name, c.logo 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    JOIN companies c ON j.company_id = c.id 
    WHERE a.student_id = ? 
    ORDER BY a.id DESC LIMIT 5
");
$stmt2->bind_param("i", $student_id);
$stmt2->execute();
$recent_apps = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

// Fetch Upcoming Interviews
$upcoming_interviews = [];
$stmt3 = $conn->prepare("
    SELECT i.*, j.title AS job_title, c.company_name 
    FROM interviews i 
    JOIN applications a ON i.application_id = a.id 
    JOIN jobs j ON a.job_id = j.id 
    JOIN companies c ON j.company_id = c.id 
    WHERE a.student_id = ? AND i.interview_date >= NOW() 
    ORDER BY i.interview_date ASC
");
$stmt3->bind_param("i", $student_id);
$stmt3->execute();
$upcoming_interviews = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt3->close();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Student Candidate Dashboard</h3>
                <p class="text-muted mb-0">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!</p>
            </div>
            <a href="jobs.php" class="btn btn-primary-custom rounded-pill">
                <i class="fas fa-search me-1"></i> Search Open Positions
            </a>
        </div>

        <!-- Metric Cards Grid -->
        <div class="row g-4 mb-4">
            <div class="col-md-3 col-6">
                <div class="glass-card stat-card">
                    <div>
                        <span class="text-muted small fw-semibold">Total Applications</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo $total_applied; ?></h3>
                    </div>
                    <div class="stat-icon primary"><i class="fas fa-paper-plane"></i></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="glass-card stat-card">
                    <div>
                        <span class="text-muted small fw-semibold">Shortlisted</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo $shortlisted_count; ?></h3>
                    </div>
                    <div class="stat-icon warning"><i class="fas fa-star"></i></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="glass-card stat-card">
                    <div>
                        <span class="text-muted small fw-semibold">Upcoming Interviews</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo $interviews_count; ?></h3>
                    </div>
                    <div class="stat-icon purple"><i class="fas fa-video"></i></div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="glass-card stat-card">
                    <div>
                        <span class="text-muted small fw-semibold">Job Offers</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1"><?php echo $selected_count; ?></h3>
                    </div>
                    <div class="stat-icon success"><i class="fas fa-award"></i></div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Upcoming Interviews List -->
            <div class="col-lg-5">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-calendar-alt text-primary me-2"></i> Scheduled Interviews</h5>
                    <?php if (empty($upcoming_interviews)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-check fs-2 mb-2 opacity-50"></i>
                            <p class="mb-0 small">No interviews scheduled right now.</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($upcoming_interviews as $interview): ?>
                                <div class="p-3 rounded-3 bg-light border">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($interview['job_title']); ?></h6>
                                        <span class="badge bg-purple-subtle text-purple small"><?php echo htmlspecialchars($interview['round_name']); ?></span>
                                    </div>
                                    <p class="text-primary small fw-semibold mb-2"><?php echo htmlspecialchars($interview['company_name']); ?></p>
                                    <div class="small text-muted mb-2">
                                        <i class="far fa-clock me-1"></i> <?php echo date('M d, Y - h:i A', strtotime($interview['interview_date'])); ?>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($interview['location_link']); ?>" target="_blank" class="btn btn-sm btn-primary rounded-pill">
                                        Join Video Room <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Applications Table -->
            <div class="col-lg-7">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="fas fa-history text-primary me-2"></i> Recent Application Status</h5>
                        <a href="applications.php" class="small text-primary text-decoration-none fw-semibold">View All</a>
                    </div>

                    <?php if (empty($recent_apps)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-folder-open fs-2 mb-2 opacity-50"></i>
                            <p class="mb-0 small">You haven't submitted any job applications yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-custom align-middle">
                                <thead>
                                    <tr>
                                        <th>Company & Role</th>
                                        <th>Applied Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_apps as $app): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($app['job_title']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($app['company_name']); ?></small>
                                            </td>
                                            <td class="small text-muted"><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
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
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
