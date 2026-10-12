<?php
$page_title = "Recruiting Companies";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('student');

$companies = [];
$res = $conn->query("SELECT * FROM companies WHERE status = 'Approved' ORDER BY company_name ASC");
if ($res) {
    $companies = $res->fetch_all(MYSQLI_ASSOC);
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Hiring Enterprise Partners</h3>
                <p class="text-muted mb-0">Explore approved corporate partners actively hiring from our campus</p>
            </div>
            <div style="min-width: 250px;">
                <input type="text" id="card-search-input" class="form-control rounded-pill" placeholder="Search companies...">
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($companies as $comp): ?>
                <div class="col-md-4 searchable-card">
                    <div class="glass-card p-4 h-100 text-center">
                        <div class="mb-3">
                            <img src="<?php echo APP_URL; ?>/uploads/profile/<?php echo htmlspecialchars($comp['logo'] ?? 'default-company.png'); ?>" 
                                 class="rounded-3 shadow-sm p-1 bg-white" style="width:70px; height:70px; object-fit:contain;" alt="Logo">
                        </div>
                        <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($comp['company_name']); ?></h5>
                        <p class="text-primary small fw-semibold mb-2"><i class="fas fa-industry me-1"></i> <?php echo htmlspecialchars($comp['industry'] ?? 'Technology'); ?></p>
                        <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($comp['location'] ?? 'Remote'); ?></p>

                        <div class="bg-light p-2 rounded-3 small text-muted text-start mb-3" style="max-height:60px; overflow-y:auto;">
                            <?php echo htmlspecialchars($comp['description'] ?? 'No description provided.'); ?>
                        </div>

                        <?php if ($comp['website']): ?>
                            <a href="<?php echo htmlspecialchars($comp['website']); ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill w-100">
                                Visit Website <i class="fas fa-external-link-alt ms-1"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
