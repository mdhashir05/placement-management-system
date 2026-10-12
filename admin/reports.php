<?php
$page_title = "Placement Reports & Export";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $type = $_GET['type'] ?? 'placed';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=placement_report_' . $type . '_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    if ($type === 'placed') {
        fputcsv($output, ['Candidate Name', 'Roll Number', 'Email', 'Department', 'Company Placed', 'Designation', 'Package', 'Selected Date']);
        $q = "
            SELECT s.full_name, s.roll_number, s.email, d.name AS dept_name, c.company_name, j.title, j.salary_package, a.updated_at
            FROM applications a
            JOIN students s ON a.student_id = s.id
            JOIN jobs j ON a.job_id = j.id
            JOIN companies c ON j.company_id = c.id
            LEFT JOIN departments d ON s.department_id = d.id
            WHERE a.status = 'Selected'
        ";
        $res = $conn->query($q);
        while ($row = $res->fetch_assoc()) {
            fputcsv($output, $row);
        }
    } else {
        fputcsv($output, ['Candidate Name', 'Roll Number', 'Company', 'Job Role', 'Status', 'Applied At']);
        $q = "
            SELECT s.full_name, s.roll_number, c.company_name, j.title, a.status, a.applied_at
            FROM applications a
            JOIN students s ON a.student_id = s.id
            JOIN jobs j ON a.job_id = j.id
            JOIN companies c ON j.company_id = c.id
        ";
        $res = $conn->query($q);
        while ($row = $res->fetch_assoc()) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit();
}

// Placed Students Data
$placed_sql = "
    SELECT s.full_name, s.roll_number, s.cgpa, d.code AS dept_code, c.company_name, j.title AS job_title, j.salary_package, a.updated_at
    FROM applications a
    JOIN students s ON a.student_id = s.id
    JOIN jobs j ON a.job_id = j.id
    JOIN companies c ON j.company_id = c.id
    LEFT JOIN departments d ON s.department_id = d.id
    WHERE a.status = 'Selected'
    ORDER BY a.updated_at DESC
";
$placed_students = $conn->query($placed_sql)->fetch_all(MYSQLI_ASSOC);

// Department-wise Stats
$dept_stats = $conn->query("
    SELECT d.code, d.name,
           COUNT(DISTINCT s.id) AS total_students,
           COUNT(DISTINCT CASE WHEN a.status = 'Selected' THEN s.id END) AS placed_students
    FROM departments d
    LEFT JOIN students s ON d.id = s.department_id
    LEFT JOIN applications a ON s.id = a.student_id
    GROUP BY d.id
")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Campus Placement Reports</h3>
                <p class="text-muted mb-0">Audited reports on placed students, compensation packages, and departmental statistics</p>
            </div>
            <div class="d-flex gap-2">
                <a href="reports.php?export=csv&type=placed" class="btn btn-success rounded-pill">
                    <i class="fas fa-file-csv me-1"></i> Export Placed List (CSV)
                </a>
                <a href="reports.php?export=csv&type=all_apps" class="btn btn-outline-primary rounded-pill">
                    <i class="fas fa-download me-1"></i> Export All Applications
                </a>
            </div>
        </div>

        <!-- Department Summary Cards -->
        <div class="row g-4 mb-4">
            <?php foreach ($dept_stats as $ds): ?>
                <?php 
                $ratio = $ds['total_students'] > 0 ? round(($ds['placed_students'] / $ds['total_students']) * 100) : 0; 
                ?>
                <div class="col-md-4 col-sm-6">
                    <div class="glass-card p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary text-white"><?php echo htmlspecialchars($ds['code']); ?></span>
                            <span class="small fw-bold text-success"><?php echo $ratio; ?>% Placed</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($ds['name']); ?></h6>
                        <div class="small text-muted">
                            Placed: <strong><?php echo $ds['placed_students']; ?></strong> / Total: <?php echo $ds['total_students']; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Placed Students Table -->
        <div class="glass-card p-4">
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-award text-success me-2"></i> Selected / Placed Candidates Directory</h5>

            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Roll Number</th>
                            <th>Dept</th>
                            <th>CGPA</th>
                            <th>Placed Company</th>
                            <th>Designation</th>
                            <th>Offered Package</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($placed_students)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No placement offers recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($placed_students as $p): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($p['full_name']); ?></td>
                                    <td><code><?php echo htmlspecialchars($p['roll_number']); ?></code></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($p['dept_code']); ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($p['cgpa']); ?></strong></td>
                                    <td class="fw-semibold text-primary"><?php echo htmlspecialchars($p['company_name']); ?></td>
                                    <td><?php echo htmlspecialchars($p['title']); ?></td>
                                    <td class="fw-bold text-success"><?php echo htmlspecialchars($p['salary_package']); ?></td>
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
