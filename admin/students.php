<?php
$page_title = "Manage Students";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');

$success = '';
$error = '';

// Handle Actions (Delete / Status Change)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $student_id = intval($_GET['id']);
    if ($_GET['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        if ($stmt->execute()) {
            $success = "Student record deleted successfully.";
            log_activity($conn, 'admin', $_SESSION['user_id'], $_SESSION['user_email'], "Deleted student ID: {$student_id}");
        } else {
            $error = "Failed to delete student.";
        }
        $stmt->close();
    } elseif ($_GET['action'] === 'status' && isset($_GET['val'])) {
        $val = sanitize_input($_GET['val']);
        $stmt = $conn->prepare("UPDATE students SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $val, $student_id);
        if ($stmt->execute()) {
            $success = "Student status updated to {$val}.";
        }
        $stmt->close();
    }
}

// Fetch Departments for filter
$dept_res = $conn->query("SELECT * FROM departments ORDER BY name ASC");
$departments = $dept_res ? $dept_res->fetch_all(MYSQLI_ASSOC) : [];

// Query Students with Department details
$sql = "
    SELECT s.*, d.name AS dept_name, d.code AS dept_code,
           COUNT(a.id) AS total_applied
    FROM students s
    LEFT JOIN departments d ON s.department_id = d.id
    LEFT JOIN applications a ON s.id = a.student_id
    GROUP BY s.id
    ORDER BY s.id DESC
";
$students = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Student Candidate Management</h3>
                <p class="text-muted mb-0">Review profiles, academic credentials, and placement statuses</p>
            </div>
            <input type="text" data-table-search="students-table" class="form-control rounded-pill" style="width: 260px;" placeholder="Search roll, name, dept...">
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small mb-3"><i class="fas fa-check-circle me-1"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small mb-3"><i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="glass-card p-4">
            <div class="table-responsive">
                <table class="table table-custom align-middle" id="students-table">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Roll Number</th>
                            <th>Department</th>
                            <th>CGPA</th>
                            <th>Passing Year</th>
                            <th>Applications</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">No registered students found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students as $s): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($s['full_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($s['email']); ?></small>
                                    </td>
                                    <td><code><?php echo htmlspecialchars($s['roll_number']); ?></code></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo htmlspecialchars($s['dept_code'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-primary"><?php echo htmlspecialchars($s['cgpa']); ?></td>
                                    <td><?php echo htmlspecialchars($s['passing_year'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2">
                                            <?php echo $s['total_applied']; ?> applied
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-active';
                                        if ($s['status'] === 'Placed') $badgeClass = 'badge-selected';
                                        if ($s['status'] === 'Inactive') $badgeClass = 'badge-rejected';
                                        ?>
                                        <span class="badge-status <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($s['status']); ?></span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light rounded-pill" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 small">
                                                <?php if ($s['resume_pdf']): ?>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/uploads/resume/<?php echo htmlspecialchars($s['resume_pdf']); ?>" target="_blank">
                                                            <i class="fas fa-file-pdf me-2"></i> View Resume PDF
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                <li><a class="dropdown-item" href="students.php?action=status&val=Active&id=<?php echo $s['id']; ?>"><i class="fas fa-check text-success me-2"></i> Mark Active</a></li>
                                                <li><a class="dropdown-item" href="students.php?action=status&val=Placed&id=<?php echo $s['id']; ?>"><i class="fas fa-trophy text-warning me-2"></i> Mark Placed</a></li>
                                                <li><a class="dropdown-item" href="students.php?action=status&val=Inactive&id=<?php echo $s['id']; ?>"><i class="fas fa-ban text-secondary me-2"></i> Mark Inactive</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="students.php?action=delete&id=<?php echo $s['id']; ?>" onclick="return confirm('Delete student permanently?');">
                                                        <i class="fas fa-trash me-2"></i> Delete Student
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
