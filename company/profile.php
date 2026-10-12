<?php
$page_title = "Company Profile";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('company');

$company_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        $error = "Token error.";
    } else {
        $company_name = sanitize_input($_POST['company_name'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $website = sanitize_input($_POST['website'] ?? '');
        $industry = sanitize_input($_POST['industry'] ?? '');
        $location = sanitize_input($_POST['location'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');

        // Logo Upload
        $logo_filename = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['logo']['tmp_name'];
            $fileName = $_FILES['logo']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = 'logo_' . $company_id . '_' . time() . '.' . $fileExtension;
                $dest_path = UPLOAD_PATH_PROFILE . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $logo_filename = $newFileName;
                }
            } else {
                $error = "Invalid logo image format.";
            }
        }

        if (empty($error)) {
            $sql = "UPDATE companies SET company_name = ?, phone = ?, website = ?, industry = ?, location = ?, description = ?";
            $params = [$company_name, $phone, $website, $industry, $location, $description];
            $types = "ssssss";

            if ($logo_filename) {
                $sql .= ", logo = ?";
                $params[] = $logo_filename;
                $types .= "s";
            }

            $sql .= " WHERE id = ?";
            $params[] = $company_id;
            $types .= "i";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                $_SESSION['user_name'] = $company_name;
                $success = "Company profile updated successfully!";
            } else {
                $error = "Database update error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Fetch Company Details
$stmt = $conn->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$comp = $stmt->get_result()->fetch_assoc();
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-1">Company Profile Settings</h3>
            <p class="text-muted mb-0">Update corporate branding, office location, and contact information</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small mb-3"><i class="fas fa-check-circle me-1"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small mb-3"><i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="profile.php" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="glass-card p-4 text-center">
                        <img src="<?php echo APP_URL; ?>/uploads/profile/<?php echo htmlspecialchars($comp['logo'] ?? 'default-company.png'); ?>" 
                             class="rounded-3 img-thumbnail shadow-sm p-2 mb-3" style="max-width:140px; height:auto;" alt="Company Logo">
                        <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($comp['company_name']); ?></h5>
                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($comp['email']); ?></p>

                        <div class="text-start">
                            <label class="form-label small fw-semibold text-muted">Upload Company Logo</label>
                            <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="glass-card p-4">
                        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-building text-primary me-2"></i> Organization Information</h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Company Name</label>
                                <input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($comp['company_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Official HR Email (Read-only)</label>
                                <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($comp['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Contact Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($comp['phone']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Corporate Website</label>
                                <input type="url" name="website" class="form-control" value="<?php echo htmlspecialchars($comp['website']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Industry Sector</label>
                                <input type="text" name="industry" class="form-control" value="<?php echo htmlspecialchars($comp['industry']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Headquarters / Location</label>
                                <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($comp['location']); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-muted">About the Company</label>
                                <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($comp['description']); ?></textarea>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary-custom rounded-pill px-4">
                                Save Profile <i class="fas fa-save ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
