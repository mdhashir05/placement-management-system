<?php
$page_title = "Manage Companies";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');

$success = '';
$error = '';

// Handle Actions (Approve / Reject / Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $comp_id = intval($_GET['id']);
    if ($_GET['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM companies WHERE id = ?");
        $stmt->bind_param("i", $comp_id);
        if ($stmt->execute()) {
            $success = "Company deleted successfully.";
            log_activity($conn, 'admin', $_SESSION['user_id'], $_SESSION['user_email'], "Deleted company ID: {$comp_id}");
        } else {
            $error = "Failed to delete company.";
        }
        $stmt->close();
    } elseif (in_array($_GET['action'], ['Approved', 'Rejected', 'Pending'])) {
        $status = $_GET['action'];
        $stmt = $conn->prepare("UPDATE companies SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $comp_id);
        if ($stmt->execute()) {
            $success = "Company status changed to {$status}.";
            log_activity($conn, 'admin', $_SESSION['user_id'], $_SESSION['user_email'], "Changed company ID: {$comp_id} status to {$status}");
        }
        $stmt->close();
    }
}

// Fetch Companies with active job counts
$sql = "
    SELECT c.*, COUNT(j.id) AS total_jobs
    FROM companies c
    LEFT JOIN jobs j ON c.id = j.company_id
    GROUP BY c.id
    ORDER BY c.id DESC
";
$companies = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Recruiting Company Management</h3>
                <p class="text-muted mb-0">Review company verification requests and active corporate partnerships</p>
            </div>
            <input type="text" data-table-search="companies-table" class="form-control rounded-pill" style="width: 260px;" placeholder="Search company...">
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small mb-3"><i class="fas fa-check-circle me-1"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small mb-3"><i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="glass-card p-4">
            <div class="table-responsive">
                <table class="table table-custom align-middle" id="companies-table">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>Contact Email</th>
                            <th>Industry</th>
                            <th>Location</th>
                            <th>Jobs Posted</th>
                            <th>Verification Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($companies)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No companies found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($companies as $c): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($c['company_name']); ?></div>
                                        <?php if ($c['website']): ?>
                                            <a href="<?php echo htmlspecialchars($c['website']); ?>" target="_blank" class="small text-primary text-decoration-none">
                                                <i class="fas fa-external-link-alt me-1"></i> Visit Website
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($c['email']); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($c['industry'] ?? 'Technology'); ?></span></td>
                                    <td><?php echo htmlspecialchars($c['location'] ?? 'Remote'); ?></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2">
                                            <?php echo $c['total_jobs']; ?> drives
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-active';
                                        if ($c['status'] === 'Pending') $badgeClass = 'badge-shortlisted';
                                        if ($c['status'] === 'Rejected') $badgeClass = 'badge-rejected';
                                        ?>
                                        <span class="badge-status <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($c['status']); ?></span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light rounded-pill" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 small">
                                                <li><a class="dropdown-item text-success" href="companies.php?action=Approved&id=<?php echo $c['id']; ?>"><i class="fas fa-check-circle me-2"></i> Approve Company</a></li>
                                                <li><a class="dropdown-item text-warning" href="companies.php?action=Pending&id=<?php echo $c['id']; ?>"><i class="fas fa-clock me-2"></i> Mark Pending</a></li>
                                                <li><a class="dropdown-item text-danger" href="companies.php?action=Rejected&id=<?php echo $c['id']; ?>"><i class="fas fa-times-circle me-2"></i> Reject Company</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="companies.php?action=delete&id=<?php echo $c['id']; ?>" onclick="return confirm('Delete this company and all its job postings?');">
                                                        <i class="fas fa-trash me-2"></i> Delete Company
                                                    </a>
                                                </li>
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
