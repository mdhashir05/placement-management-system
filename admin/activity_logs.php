<?php
$page_title = "Audit Activity Logs";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');

$logs = $conn->query("SELECT * FROM activity_logs ORDER BY id DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">System Audit & Activity Logs</h3>
                <p class="text-muted mb-0">Immutable tracking of logins, role updates, and system operations</p>
            </div>
            <input type="text" data-table-search="logs-table" class="form-control rounded-pill" style="width: 260px;" placeholder="Filter activities...">
        </div>

        <div class="glass-card p-4">
            <div class="table-responsive">
                <table class="table table-custom align-middle" id="logs-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Role</th>
                            <th>User Account</th>
                            <th>Action Description</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No activity records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>#<?php echo $log['id']; ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-uppercase">
                                            <?php echo htmlspecialchars($log['user_type']); ?>
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($log['user_email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td><code><?php echo htmlspecialchars($log['ip_address'] ?? '127.0.0.1'); ?></code></td>
                                    <td class="small text-muted"><?php echo date('M d, Y - H:i:s', strtotime($log['created_at'])); ?></td>
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
