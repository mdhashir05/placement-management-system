<?php
$page_title = "Manage Interviews";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');

$success = '';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $int_id = intval($_GET['id']);
    $val = sanitize_input($_GET['val']);
    $stmt = $conn->prepare("UPDATE interviews SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $val, $int_id);
    if ($stmt->execute()) {
        $success = "Interview status updated to {$val}.";
    }
    $stmt->close();
}

$sql = "
    SELECT i.*, s.full_name AS student_name, s.email AS student_email,
           j.title AS job_title, c.company_name
    FROM interviews i
    JOIN applications a ON i.application_id = a.id
    JOIN students s ON a.student_id = s.id
    JOIN jobs j ON a.job_id = j.id
    JOIN companies c ON j.company_id = c.id
    ORDER BY i.interview_date DESC
";
$interviews = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Interview Schedules Overview</h3>
                <p class="text-muted mb-0">Monitor campus evaluation rounds across all companies and candidates</p>
            </div>
            <input type="text" data-table-search="interviews-table" class="form-control rounded-pill" style="width: 260px;" placeholder="Search candidate, company...">
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small mb-3"><i class="fas fa-check-circle me-1"></i> <?php echo $success; ?></div>
        <?php endif; ?>

        <div class="glass-card p-4">
            <div class="table-responsive">
                <table class="table table-custom align-middle" id="interviews-table">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Company & Role</th>
                            <th>Round</th>
                            <th>Interview Date & Time</th>
                            <th>Meeting Link</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($interviews)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No interviews scheduled.</td></tr>
                        <?php else: ?>
                            <?php foreach ($interviews as $inv): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($inv['student_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($inv['student_email']); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary"><?php echo htmlspecialchars($inv['company_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($inv['job_title']); ?></small>
                                    </td>
                                    <td><span class="badge bg-purple-subtle text-purple"><?php echo htmlspecialchars($inv['round_name']); ?></span></td>
                                    <td class="small text-muted"><?php echo date('M d, Y - h:i A', strtotime($inv['interview_date'])); ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($inv['location_link']); ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                            Join Room <i class="fas fa-external-link-alt ms-1"></i>
                                        </a>
                                    </td>
                                    <td><span class="badge-status badge-scheduled"><?php echo htmlspecialchars($inv['status']); ?></span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light rounded-pill" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 small">
                                                <li><a class="dropdown-item text-success" href="interviews.php?action=status&val=Completed&id=<?php echo $inv['id']; ?>"><i class="fas fa-check me-2"></i> Mark Completed</a></li>
                                                <li><a class="dropdown-item text-primary" href="interviews.php?action=status&val=Scheduled&id=<?php echo $inv['id']; ?>"><i class="fas fa-calendar me-2"></i> Mark Scheduled</a></li>
                                                <li><a class="dropdown-item text-danger" href="interviews.php?action=status&val=Cancelled&id=<?php echo $inv['id']; ?>"><i class="fas fa-times me-2"></i> Mark Cancelled</a></li>
                                            </ul>
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
