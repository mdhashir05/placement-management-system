<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$unread_count = 0;
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $user_type = $_SESSION['role'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_type = ? AND user_id = ? AND is_read = 0");
    if ($stmt) {
        $stmt->bind_param("si", $user_type, $user_id);
        $stmt->execute();
        $stmt->bind_result($unread_count);
        $stmt->fetch();
        $stmt->close();
    }
}
?>
<header class="app-header d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <?php if (isset($_SESSION['user_id'])): ?>
            <button id="sidebar-toggle" class="btn btn-light d-md-none" type="button">
                <i class="fas fa-bars"></i>
            </button>
        <?php endif; ?>
        <a href="<?php echo APP_URL; ?>/index.php" class="text-decoration-none d-flex align-items-center gap-2">
            <div class="bg-primary text-white rounded-3 px-2 py-1 font-weight-bold fs-5">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <span class="fs-5 fw-bold text-dark tracking-tight"><?php echo APP_NAME; ?></span>
        </a>
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- Dark Mode Toggle Button -->
        <button id="theme-toggle-btn" class="btn btn-icon btn-light rounded-circle shadow-sm" title="Toggle Dark/Light Mode">
            <i class="fas fa-moon text-secondary"></i>
        </button>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Notifications Bell -->
            <div class="dropdown">
                <button class="btn btn-light rounded-circle position-relative shadow-sm" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bell text-secondary"></i>
                    <?php if ($unread_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $unread_count; ?>
                        </span>
                    <?php endif; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2" style="width: 300px;">
                    <li class="dropdown-header fw-bold text-dark d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <span class="badge bg-primary rounded-pill"><?php echo $unread_count; ?> New</span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <?php if ($unread_count > 0): ?>
                        <li>
                            <a class="dropdown-item small text-wrap text-muted py-2" href="#">
                                <i class="fas fa-info-circle text-info me-1"></i> You have new updates on your applications/interviews.
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="dropdown-item small text-muted text-center py-2">No unread notifications</li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Profile Dropdown -->
            <div class="dropdown">
                <button class="btn btn-light d-flex align-items-center gap-2 rounded-pill px-3 py-1 shadow-sm border" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle fs-5 text-primary"></i>
                    <span class="fw-semibold small text-dark d-none d-sm-inline">
                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                    </span>
                    <i class="fas fa-chevron-down small text-muted"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                    <li class="dropdown-header small text-uppercase text-muted">
                        Role: <strong><?php echo ucfirst($_SESSION['role']); ?></strong>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <?php if ($_SESSION['role'] === 'student'): ?>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/student/profile.php"><i class="fas fa-id-card me-2 text-primary"></i> My Profile</a></li>
                    <?php elseif ($_SESSION['role'] === 'company'): ?>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/company/profile.php"><i class="fas fa-building me-2 text-primary"></i> Company Profile</a></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
        <?php else: ?>
            <a href="<?php echo APP_URL; ?>/login.php" class="btn btn-outline-primary fw-semibold rounded-pill px-4">Login</a>
            <a href="<?php echo APP_URL; ?>/register.php" class="btn btn-primary-custom rounded-pill px-4">Register</a>
        <?php endif; ?>
    </div>
</header>
