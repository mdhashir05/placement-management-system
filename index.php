<?php
$page_title = "Home - Campus Placement Portal";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Fetch Live System Counters
$students_count = 0;
$companies_count = 0;
$jobs_count = 0;
$placements_count = 0;

$res1 = $conn->query("SELECT COUNT(*) FROM students");
if ($res1) $students_count = $res1->fetch_row()[0];

$res2 = $conn->query("SELECT COUNT(*) FROM companies WHERE status = 'Approved'");
if ($res2) $companies_count = $res2->fetch_row()[0];

$res3 = $conn->query("SELECT COUNT(*) FROM jobs WHERE status = 'Active'");
if ($res3) $jobs_count = $res3->fetch_row()[0];

$res4 = $conn->query("SELECT COUNT(*) FROM applications WHERE status = 'Selected'");
if ($res4) $placements_count = $res4->fetch_row()[0];

// Fetch Featured Active Jobs
$featured_jobs = [];
$stmt = $conn->prepare("
    SELECT j.*, c.company_name, c.logo, d.name AS dept_name 
    FROM jobs j 
    JOIN companies c ON j.company_id = c.id 
    LEFT JOIN departments d ON j.department_id = d.id 
    WHERE j.status = 'Active' 
    ORDER BY j.id DESC LIMIT 6
");
if ($stmt) {
    $stmt->execute();
    $featured_jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!-- Hero Section -->
<section class="hero-section text-center position-relative">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-3 fw-semibold fs-6">
                    <i class="fas fa-rocket me-1"></i> Campus Placement Drive 2024-2025
                </span>
                <h1 class="hero-title mb-4">Empowering Campus Placements with Automated Workflows</h1>
                <p class="lead text-muted mb-5">
                    Connect talented students with top global enterprise recruiters. Manage job postings, applications, technical interviews, and real-time statistics in one unified platform.
                </p>
                
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="register.php?role=student" class="btn btn-primary-custom btn-lg rounded-pill px-4">
                            <i class="fas fa-user-graduate me-2"></i> Register as Student
                        </a>
                        <a href="register.php?role=company" class="btn btn-outline-primary btn-lg rounded-pill px-4">
                            <i class="fas fa-building me-2"></i> Hire Students (Company)
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $_SESSION['role']; ?>/index.php" class="btn btn-primary-custom btn-lg rounded-pill px-5">
                            <i class="fas fa-tachometer-alt me-2"></i> Go to My Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Live Stats Counters -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="glass-card stat-card text-center flex-column justify-content-center p-4">
                    <div class="stat-icon primary mx-auto mb-3"><i class="fas fa-users"></i></div>
                    <h2 class="fw-bold text-dark mb-1"><?php echo number_format($students_count); ?>+</h2>
                    <span class="text-muted fw-medium">Registered Candidates</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="glass-card stat-card text-center flex-column justify-content-center p-4">
                    <div class="stat-icon success mx-auto mb-3"><i class="fas fa-building"></i></div>
                    <h2 class="fw-bold text-dark mb-1"><?php echo number_format($companies_count); ?>+</h2>
                    <span class="text-muted fw-medium">Hiring Partners</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="glass-card stat-card text-center flex-column justify-content-center p-4">
                    <div class="stat-icon purple mx-auto mb-3"><i class="fas fa-briefcase"></i></div>
                    <h2 class="fw-bold text-dark mb-1"><?php echo number_format($jobs_count); ?>+</h2>
                    <span class="text-muted fw-medium">Active Openings</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="glass-card stat-card text-center flex-column justify-content-center p-4">
                    <div class="stat-icon warning mx-auto mb-3"><i class="fas fa-trophy"></i></div>
                    <h2 class="fw-bold text-dark mb-1"><?php echo number_format($placements_count); ?>+</h2>
                    <span class="text-muted fw-medium">Successful Placements</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Job Openings Grid -->
<section class="py-5 bg-white-50">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Featured Campus Openings</h3>
                <p class="text-muted mb-0">Apply directly to top hiring companies active today</p>
            </div>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
                <a href="student/jobs.php" class="btn btn-outline-primary rounded-pill">View All Jobs <i class="fas fa-arrow-right ms-1"></i></a>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <?php if (empty($featured_jobs)): ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fas fa-briefcase fs-1 mb-3 opacity-50"></i>
                    <p>No active job postings right now. Check back soon!</p>
                </div>
            <?php else: ?>
                <?php foreach ($featured_jobs as $job): ?>
                    <div class="col-md-4">
                        <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-semibold">
                                        <?php echo htmlspecialchars($job['job_type']); ?>
                                    </span>
                                    <small class="text-muted"><i class="far fa-calendar-alt me-1"></i> Deadline: <?php echo htmlspecialchars($job['deadline']); ?></small>
                                </div>
                                <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($job['title']); ?></h5>
                                <p class="text-primary fw-semibold mb-3"><i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($job['company_name']); ?></p>
                                
                                <div class="d-flex flex-column gap-2 mb-3 text-muted small">
                                    <div><i class="fas fa-map-marker-alt text-secondary width-20 me-1"></i> <?php echo htmlspecialchars($job['location']); ?></div>
                                    <div><i class="fas fa-dollar-sign text-success width-20 me-1"></i> <strong>Package:</strong> <?php echo htmlspecialchars($job['salary_package']); ?></div>
                                    <div><i class="fas fa-graduation-cap text-info width-20 me-1"></i> <strong>Min CGPA:</strong> <?php echo htmlspecialchars($job['min_cgpa']); ?></div>
                                </div>
                            </div>

                            <div>
                                <hr class="my-3 text-secondary opacity-25">
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
                                    <a href="student/jobs.php?id=<?php echo $job['id']; ?>" class="btn btn-primary-custom w-100 rounded-pill">
                                        Apply Now <i class="fas fa-paper-plane ms-1"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-outline-primary w-100 rounded-pill">
                                        Login to Apply <i class="fas fa-lock ms-1"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
