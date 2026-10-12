<?php
$page_title = "Notifications";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('student');

$student_id = $_SESSION['user_id'];

// Mark all as read
if (isset($_GET['mark_read'])) {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_type = 'student' AND user_id = {$student_id}");
    header("Location: notifications.php");
    exit();
}

// Fetch Student Notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_type = 'student' AND user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Your Notifications</h3>
                <p class="text-muted mb-0">Interview alerts, status updates, and placement announcements</p>
            </div>
            <a href="notifications.php?mark_read=1" class="btn btn-outline-primary rounded-pill btn-sm">
                <i class="fas fa-check-double me-1"></i> Mark All as Read
            </a>
        </div>

        <div class="glass-card p-4">
            <?php if (empty($notifications)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="far fa-bell fs-1 mb-3 opacity-50"></i>
                    <p>No notifications at the moment.</p>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($notifications as $n): ?>
                        <div class="p-3 rounded-3 border <?php echo $n['is_read'] ? 'bg-light' : 'bg-white border-primary shadow-sm'; ?>">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($n['title']); ?></h6>
                                <small class="text-muted"><?php echo date('M d, Y - H:i', strtotime($n['created_at'])); ?></small>
                            </div>
                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($n['message']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
