<?php
$page_title = "My Profile & Resume";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('student');

$student_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Profile Update & File Uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        $error = "Security token mismatch.";
    } else {
        $full_name = sanitize_input($_POST['full_name'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');
        $cgpa = floatval($_POST['cgpa'] ?? 0.0);
        $passing_year = intval($_POST['passing_year'] ?? date('Y'));
        $bio = sanitize_input($_POST['bio'] ?? '');

        // Handle Profile Image Upload
        $profile_pic_filename = null;
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
            $fileName = $_FILES['profile_pic']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = 'avatar_' . $student_id . '_' . time() . '.' . $fileExtension;
                $dest_path = UPLOAD_PATH_PROFILE . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $profile_pic_filename = $newFileName;
                }
            } else {
                $error = "Invalid profile picture format. Only JPG, PNG, WEBP allowed.";
            }
        }

        // Handle PDF Resume Upload
        $resume_filename = null;
        if (isset($_FILES['resume_pdf']) && $_FILES['resume_pdf']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['resume_pdf']['tmp_name'];
            $fileName = $_FILES['resume_pdf']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($fileExtension === 'pdf') {
                $newFileName = 'resume_' . $student_id . '_' . time() . '.pdf';
                $dest_path = UPLOAD_PATH_RESUME . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $resume_filename = $newFileName;
                }
            } else {
                $error = "Resume must be in PDF format.";
            }
        }

        if (empty($error)) {
            $sql = "UPDATE students SET full_name = ?, phone = ?, cgpa = ?, passing_year = ?, bio = ?";
            $params = [$full_name, $phone, $cgpa, $passing_year, $bio];
            $types = "ssdis";

            if ($profile_pic_filename) {
                $sql .= ", profile_pic = ?";
                $params[] = $profile_pic_filename;
                $types .= "s";
            }
            if ($resume_filename) {
                $sql .= ", resume_pdf = ?";
                $params[] = $resume_filename;
                $types .= "s";
            }

            $sql .= " WHERE id = ?";
            $params[] = $student_id;
            $types .= "i";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            if ($stmt->execute()) {
                $_SESSION['user_name'] = $full_name;
                $success = "Profile updated successfully!";
            } else {
                $error = "Database update error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Fetch Current Student Details
$stmt = $conn->prepare("SELECT s.*, d.name AS dept_name FROM students s LEFT JOIN departments d ON s.department_id = d.id WHERE s.id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-1">Manage Profile & Resume</h3>
            <p class="text-muted mb-0">Keep your academic background and credentials up-to-date for recruiters</p>
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
                <!-- Left Column: Avatar & Resume Preview -->
                <div class="col-lg-4">
                    <div class="glass-card p-4 text-center">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="<?php echo APP_URL; ?>/uploads/profile/<?php echo htmlspecialchars($student['profile_pic'] ?? 'default-avatar.png'); ?>" 
                                 class="rounded-circle img-thumbnail shadow-sm" style="width: 140px; height: 140px; object-fit: cover;" alt="Avatar">
                        </div>
                        <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($student['full_name']); ?></h5>
                        <p class="text-muted small mb-2"><?php echo htmlspecialchars($student['roll_number']); ?></p>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 mb-3">
                            <?php echo htmlspecialchars($student['dept_name'] ?? 'N/A'); ?>
                        </span>

                        <hr class="my-3 text-secondary opacity-25">

                        <div class="mb-3 text-start">
                            <label class="form-label small fw-semibold text-muted">Upload New Avatar</label>
                            <input type="file" name="profile_pic" class="form-control form-control-sm" accept="image/*">
                        </div>

                        <div class="text-start">
                            <label class="form-label small fw-semibold text-muted">Upload Resume (PDF only)</label>
                            <input type="file" name="resume_pdf" class="form-control form-control-sm" accept="application/pdf">
                            <?php if ($student['resume_pdf']): ?>
                                <div class="mt-2">
                                    <a href="<?php echo APP_URL; ?>/uploads/resume/<?php echo htmlspecialchars($student['resume_pdf']); ?>" target="_blank" class="btn btn-sm btn-outline-danger w-100">
                                        <i class="fas fa-file-pdf me-1"></i> View Uploaded PDF Resume
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Profile Form Details -->
                <div class="col-lg-8">
                    <div class="glass-card p-4">
                        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-user-edit text-primary me-2"></i> Academic & Personal Information</h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Email Address (Read-only)</label>
                                <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($student['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-muted">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted">CGPA</label>
                                <input type="number" step="0.01" max="4.00" name="cgpa" class="form-control" value="<?php echo htmlspecialchars($student['cgpa']); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted">Graduation Year</label>
                                <input type="number" name="passing_year" class="form-control" value="<?php echo htmlspecialchars($student['passing_year']); ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-muted">Career Summary / Professional Bio</label>
                                <textarea name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($student['bio'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary-custom px-4 rounded-pill">
                                Save Profile Changes <i class="fas fa-save ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
