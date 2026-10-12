<?php
$page_title = "Scheduled Interviews";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('student');

$student_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT i.*, j.title AS job_title, c.company_name 
    FROM interviews i 
    JOIN applications a ON i.application_id = a.id 
    JOIN jobs j ON a.job_id = j.id 
    JOIN companies c ON j.company_id = c.id 
    WHERE a.student_id = ? 
    ORDER BY i.interview_date DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$interviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-1">Technical Interview Schedules</h3>
            <p class="text-muted mb-0">View scheduled evaluation rounds and join virtual interview rooms</p>
        </div>

        <div class="row g-4">
            <?php if (empty($interviews)): ?>
                <div class="col-12 text-center py-5 text-muted glass-card">
                    <i class="fas fa-video-slash fs-1 mb-3 opacity-50"></i>
                    <p>No technical interviews scheduled yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($interviews as $interview): ?>
                    <div class="col-md-6">
                        <div class="glass-card p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-purple-subtle text-purple fw-semibold rounded-pill px-3 py-1">
                                    <?php echo htmlspecialchars($interview['round_name']); ?>
                                </span>
                                <span class="badge bg-success-subtle text-success fw-semibold">
                                    <?php echo htmlspecialchars($interview['status']); ?>
                                </span>
                            </div>
                            <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($interview['job_title']); ?></h5>
                            <p class="text-primary fw-semibold mb-3"><i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($interview['company_name']); ?></p>

                            <div class="bg-light p-3 rounded-3 mb-3">
                                <div class="mb-2"><strong>Date & Time:</strong> <i class="far fa-clock text-primary ms-1"></i> <?php echo date('F d, Y @ h:i A', strtotime($interview['interview_date'])); ?></div>
                                <div><strong>Instructions:</strong> <?php echo htmlspecialchars($interview['instructions'] ?? 'Bring updated resume PDF and valid ID.'); ?></div>
                            </div>

                            <a href="<?php echo htmlspecialchars($interview['location_link']); ?>" target="_blank" class="btn btn-primary-custom w-100 rounded-pill">
                                Join Interview Meeting Link <i class="fas fa-external-link-alt ms-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
