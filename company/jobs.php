<?php
$page_title = "Manage Openings";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('company');

$company_id = $_SESSION['user_id'];

// Handle Job Status Toggle or Delete
if (isset($_GET['action']) && isset($_GET['id'])) {
    $job_id = intval($_GET['id']);
    if ($_GET['action'] === 'toggle') {
        $stmt = $conn->prepare("UPDATE jobs SET status = IF(status='Active','Closed','Active') WHERE id = ? AND company_id = ?");
        $stmt->bind_param("ii", $job_id, $company_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($_GET['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ? AND company_id = ?");
        $stmt->bind_param("ii", $job_id, $company_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: jobs.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT j.*, COUNT(a.id) AS total_applicants 
    FROM jobs j 
    LEFT JOIN applications a ON j.id = a.job_id 
    WHERE j.company_id = ? 
    GROUP BY j.id 
    ORDER BY j.id DESC
");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Company Job Drives</h3>
                <p class="text-muted mb-0">View, toggle active status, or review applicants for published openings</p>
            </div>
            <a href="post-job.php" class="btn btn-primary-custom rounded-pill">
                <i class="fas fa-plus-circle me-1"></i> Post Vacancy
            </a>
        </div>

        <div class="glass-card p-4">
            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Type & Location</th>
                            <th>Package</th>
                            <th>Applicants</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jobs)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No vacancies posted yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($job['title']); ?></div>
                                        <small class="text-muted">Min CGPA: <?php echo htmlspecialchars($job['min_cgpa']); ?></small>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?php echo htmlspecialchars($job['job_type']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($job['location']); ?></small>
                                    </td>
                                    <td class="fw-bold text-success"><?php echo htmlspecialchars($job['salary_package']); ?></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold">
                                            <?php echo $job['total_applicants']; ?> candidates
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?php echo date('M d, Y', strtotime($job['deadline'])); ?></td>
                                    <td>
                                        <span class="badge-status <?php echo $job['status'] === 'Active' ? 'badge-active' : 'badge-closed'; ?>">
                                            <?php echo htmlspecialchars($job['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="applicants.php?job_id=<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill" title="View Applicants">
                                                <i class="fas fa-users"></i>
                                            </a>
                                            <a href="jobs.php?action=toggle&id=<?php echo $job['id']; ?>" class="btn btn-sm btn-outline-warning rounded-pill" title="Toggle Status">
                                                <i class="fas fa-power-off"></i>
                                            </a>
                                            <a href="jobs.php?action=delete&id=<?php echo $job['id']; ?>" onclick="return confirm('Delete this job posting?');" class="btn btn-sm btn-outline-danger rounded-pill" title="Delete">
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
