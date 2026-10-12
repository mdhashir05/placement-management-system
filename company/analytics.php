<?php
$page_title = "Company Analytics";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('company');

$company_id = $_SESSION['user_id'];

// Get Application pipeline stats
$app_status_counts = [
    'Applied' => 0,
    'Shortlisted' => 0,
    'Interview Scheduled' => 0,
    'Selected' => 0,
    'Rejected' => 0
];

$stmt = $conn->prepare("
    SELECT a.status, COUNT(a.id) as cnt
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = ?
    GROUP BY a.status
");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    if (isset($app_status_counts[$row['status']])) {
        $app_status_counts[$row['status']] = (int)$row['cnt'];
    }
}
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-1">Company Recruitment Analytics</h3>
            <p class="text-muted mb-0">Applicant conversion funnel and drive evaluation insights</p>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-funnel-dollar text-primary me-2"></i> Applicant Funnel</h5>
                    <div style="max-height: 280px; position: relative;">
                        <canvas id="companyFunnelChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-percentage text-primary me-2"></i> Selection Ratio Breakdown</h5>
                    <div class="d-flex flex-column gap-3 mt-4">
                        <?php 
                        $total = array_sum($app_status_counts);
                        foreach ($app_status_counts as $status => $cnt): 
                            $pct = $total > 0 ? round(($cnt / $total) * 100) : 0;
                        ?>
                            <div>
                                <div class="d-flex justify-content-between small fw-bold mb-1">
                                    <span><?php echo $status; ?></span>
                                    <span><?php echo $cnt; ?> (<?php echo $pct; ?>%)</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $pct; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('companyFunnelChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($app_status_counts)); ?>,
                datasets: [{
                    label: 'Applicants',
                    data: <?php echo json_encode(array_values($app_status_counts)); ?>,
                    backgroundColor: ['#0d6efd', '#f59e0b', '#8b5cf6', '#10b981', '#ef4444'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
