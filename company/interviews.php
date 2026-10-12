<?php
$page_title = "Schedule Interviews";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('company');

$company_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle New Interview Schedule creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    $application_id = intval($_POST['application_id'] ?? 0);
    $interview_date = sanitize_input($_POST['interview_date'] ?? '');
    $location_link = sanitize_input($_POST['location_link'] ?? '');
    $round_name = sanitize_input($_POST['round_name'] ?? 'Technical Round');
    $instructions = sanitize_input($_POST['instructions'] ?? '');

    if (!verify_csrf_token($csrf_token)) {
        $error = "Token error.";
    } elseif ($application_id > 0 && !empty($interview_date) && !empty($location_link)) {
        // Insert Interview Record
        $stmt = $conn->prepare("INSERT INTO interviews (application_id, interview_date, location_link, round_name, instructions, status) VALUES (?, ?, ?, ?, ?, 'Scheduled')");
        $stmt->bind_param("issss", $application_id, $interview_date, $location_link, $round_name, $instructions);
        if ($stmt->execute()) {
            // Update application status to Interview Scheduled
            $conn->query("UPDATE applications SET status = 'Interview Scheduled' WHERE id = {$application_id}");
            $success = "Technical interview scheduled successfully!";
        } else {
            $error = "Error scheduling interview: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch Shortlisted Candidates eligible for interview
$shortlisted = [];
$stmt_s = $conn->prepare("
    SELECT a.id AS app_id, s.full_name AS student_name, j.title AS job_title 
    FROM applications a 
    JOIN students s ON a.student_id = s.id 
    JOIN jobs j ON a.job_id = j.id 
    WHERE j.company_id = ? AND a.status IN ('Applied', 'Shortlisted')
");
$stmt_s->bind_param("i", $company_id);
$stmt_s->execute();
$shortlisted = $stmt_s->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_s->close();

// Fetch Scheduled Interviews
$interviews = [];
$stmt_i = $conn->prepare("
    SELECT i.*, s.full_name AS student_name, j.title AS job_title 
    FROM interviews i 
    JOIN applications a ON i.application_id = a.id 
    JOIN students s ON a.student_id = s.id 
    JOIN jobs j ON a.job_id = j.id 
    WHERE j.company_id = ? 
    ORDER BY i.interview_date DESC
");
$stmt_i->bind_param("i", $company_id);
$stmt_i->execute();
$interviews = $stmt_i->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_i->close();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Interview Scheduler</h3>
                <p class="text-muted mb-0">Schedule and manage video evaluation rounds for candidates</p>
            </div>
            <button type="button" class="btn btn-primary-custom rounded-pill" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                <i class="fas fa-calendar-plus me-1"></i> Schedule New Interview
            </button>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small mb-3"><i class="fas fa-check-circle me-1"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small mb-3"><i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="glass-card p-4">
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-video text-primary me-2"></i> Upcoming & Past Scheduled Interviews</h5>

            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Job Role</th>
                            <th>Evaluation Round</th>
                            <th>Date & Time</th>
                            <th>Meeting Link</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($interviews)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No interview rounds scheduled yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($interviews as $interview): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($interview['student_name']); ?></td>
                                    <td class="text-primary fw-semibold"><?php echo htmlspecialchars($interview['job_title']); ?></td>
                                    <td><span class="badge bg-purple-subtle text-purple"><?php echo htmlspecialchars($interview['round_name']); ?></span></td>
                                    <td class="small text-muted"><?php echo date('M d, Y - h:i A', strtotime($interview['interview_date'])); ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($interview['location_link']); ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                            Open Link <i class="fas fa-external-link-alt ms-1"></i>
                                        </a>
                                    </td>
                                    <td><span class="badge-status badge-scheduled"><?php echo htmlspecialchars($interview['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Schedule Interview Modal -->
        <div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Schedule Candidate Interview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="interviews.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Select Applicant</label>
                                <select name="application_id" class="form-select" required>
                                    <option value="">Choose Candidate...</option>
                                    <?php foreach ($shortlisted as $cand): ?>
                                        <option value="<?php echo $cand['app_id']; ?>"><?php echo htmlspecialchars($cand['student_name']); ?> - <?php echo htmlspecialchars($cand['job_title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Round Name</label>
                                <input type="text" name="round_name" class="form-control" value="Technical Round 1" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Interview Date & Time</label>
                                <input type="datetime-local" name="interview_date" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Video Meeting Room Link (Google Meet / Zoom / Teams)</label>
                                <input type="url" name="location_link" class="form-control" placeholder="https://meet.google.com/xyz-abc-pqr" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">Special Instructions for Candidate</label>
                                <textarea name="instructions" class="form-control" rows="2" placeholder="e.g. Keep camera enabled and bring code sample..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary-custom rounded-pill">Confirm Schedule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
