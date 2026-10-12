<?php
$page_title = "Manage Departments & Skills";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_role('admin');

$success = '';
$error = '';

// Handle Add Department
if (isset($_POST['action']) && $_POST['action'] === 'add_dept') {
    $code = strtoupper(sanitize_input($_POST['code']));
    $name = sanitize_input($_POST['name']);
    if (!empty($code) && !empty($name)) {
        $stmt = $conn->prepare("INSERT INTO departments (code, name) VALUES (?, ?)");
        $stmt->bind_param("ss", $code, $name);
        if ($stmt->execute()) {
            $success = "Department added successfully.";
        } else {
            $error = "Department code already exists or error occurred.";
        }
        $stmt->close();
    }
}

// Handle Add Skill
if (isset($_POST['action']) && $_POST['action'] === 'add_skill') {
    $name = sanitize_input($_POST['skill_name']);
    $category = sanitize_input($_POST['category'] ?? 'Technical');
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO skills (name, category) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $category);
        if ($stmt->execute()) {
            $success = "Skill master added.";
        } else {
            $error = "Skill already exists.";
        }
        $stmt->close();
    }
}

// Handle Delete
if (isset($_GET['delete_dept'])) {
    $id = intval($_GET['delete_dept']);
    $conn->query("DELETE FROM departments WHERE id = {$id}");
    $success = "Department deleted.";
}
if (isset($_GET['delete_skill'])) {
    $id = intval($_GET['delete_skill']);
    $conn->query("DELETE FROM skills WHERE id = {$id}");
    $success = "Skill deleted.";
}

$departments = $conn->query("SELECT * FROM departments ORDER BY code ASC")->fetch_all(MYSQLI_ASSOC);
$skills = $conn->query("SELECT * FROM skills ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="app-main">
        <div class="mb-4">
            <h3 class="fw-bold text-dark mb-1">Academic Departments & Skills Master</h3>
            <p class="text-muted mb-0">Configure university branches and curriculum skill tags</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success py-2 small mb-3"><i class="fas fa-check-circle me-1"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small mb-3"><i class="fas fa-exclamation-circle me-1"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Departments Column -->
            <div class="col-lg-6">
                <div class="glass-card p-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-network-wired text-primary me-2"></i> University Departments</h5>

                    <form method="POST" action="departments.php" class="row g-2 mb-4">
                        <input type="hidden" name="action" value="add_dept">
                        <div class="col-4">
                            <input type="text" name="code" class="form-control" placeholder="Code (e.g. AI)" required>
                        </div>
                        <div class="col-5">
                            <input type="text" name="name" class="form-control" placeholder="Department Name" required>
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-primary-custom w-100 rounded-pill">Add</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Department Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($departments as $d): ?>
                                    <tr>
                                        <td><span class="badge bg-primary-subtle text-primary fw-bold"><?php echo htmlspecialchars($d['code']); ?></span></td>
                                        <td><?php echo htmlspecialchars($d['name']); ?></td>
                                        <td>
                                            <a href="departments.php?delete_dept=<?php echo $d['id']; ?>" onclick="return confirm('Delete department?');" class="btn btn-sm btn-outline-danger rounded-pill">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Skills Column -->
            <div class="col-lg-6">
                <div class="glass-card p-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-tools text-primary me-2"></i> Skill Tags Directory</h5>

                    <form method="POST" action="departments.php" class="row g-2 mb-4">
                        <input type="hidden" name="action" value="add_skill">
                        <div class="col-5">
                            <input type="text" name="skill_name" class="form-control" placeholder="Skill Name" required>
                        </div>
                        <div class="col-4">
                            <input type="text" name="category" class="form-control" placeholder="Category" value="Technical">
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-primary-custom w-100 rounded-pill">Add</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Skill</th>
                                    <th>Category</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($skills as $sk): ?>
                                    <tr>
                                        <td class="fw-bold text-dark"><?php echo htmlspecialchars($sk['name']); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($sk['category']); ?></span></td>
                                        <td>
                                            <a href="departments.php?delete_skill=<?php echo $sk['id']; ?>" onclick="return confirm('Delete skill?');" class="btn btn-sm btn-outline-danger rounded-pill">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
