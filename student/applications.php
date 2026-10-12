<?php
$page_title = "My Applications History";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('student');

$student_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT a.*, j.title AS job_title, j.salary_package, j.location, c.company_name 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    JOIN companies c ON j.company_id = c.id 
    WHERE a.student_id = ? 
    ORDER BY a.id DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Application History & Timeline</h3>
                <p class="text-muted mb-0">Track real-time status of your job applications</p>
            </div>
            <input type="text" data-table-search="apps-table" class="form-control rounded-pill" style="width: 250px;" placeholder="Filter applications...">
        </div>

        <div class="glass-card p-4">
            <div class="table-responsive">
                <table class="table table-custom align-middle" id="apps-table">
                    <thead>
                        <tr>
                            <th>Job Title & Company</th>
                            <th>Package & Location</th>
                            <th>Applied Date</th>
                            <th>Current Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No applications submitted yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($app['job_title']); ?></div>
                                        <small class="text-primary fw-semibold"><?php echo htmlspecialchars($app['company_name']); ?></small>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark"><?php echo htmlspecialchars($app['salary_package']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($app['location']); ?></small>
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
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
