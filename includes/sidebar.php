<?php
require_once __DIR__ . '/../config/config.php';

$role = $_SESSION['role'] ?? null;
$current_page = basename($_SERVER['PHP_SELF']);
?>
<?php if ($role): ?>
<aside class="app-sidebar">
    <div class="mb-4 px-2">
        <small class="text-uppercase fw-bold text-muted tracking-wider" style="font-size: 0.75rem;">Navigation Menu</small>
    </div>

    <nav class="nav flex-column gap-1">
        <?php if ($role === 'admin'): ?>
            <a href="<?php echo APP_URL; ?>/admin/index.php" class="sidebar-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
            <a href="<?php echo APP_URL; ?>/admin/students.php" class="sidebar-link <?php echo $current_page === 'students.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i> Students
            </a>
            <a href="<?php echo APP_URL; ?>/admin/companies.php" class="sidebar-link <?php echo $current_page === 'companies.php' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i> Companies
            </a>
            <a href="<?php echo APP_URL; ?>/admin/jobs.php" class="sidebar-link <?php echo $current_page === 'jobs.php' ? 'active' : ''; ?>">
                <i class="fas fa-briefcase"></i> Job Postings
            </a>
            <a href="<?php echo APP_URL; ?>/admin/applications.php" class="sidebar-link <?php echo $current_page === 'applications.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-signature"></i> Applications
            </a>
            <a href="<?php echo APP_URL; ?>/admin/interviews.php" class="sidebar-link <?php echo $current_page === 'interviews.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Interviews
            </a>
            <a href="<?php echo APP_URL; ?>/admin/departments.php" class="sidebar-link <?php echo $current_page === 'departments.php' ? 'active' : ''; ?>">
                <i class="fas fa-network-wired"></i> Departments & Skills
            </a>
            <a href="<?php echo APP_URL; ?>/admin/reports.php" class="sidebar-link <?php echo $current_page === 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-download"></i> Reports & Export
            </a>
            <a href="<?php echo APP_URL; ?>/admin/activity_logs.php" class="sidebar-link <?php echo $current_page === 'activity_logs.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i> Audit Logs
            </a>

        <?php elseif ($role === 'student'): ?>
            <a href="<?php echo APP_URL; ?>/student/index.php" class="sidebar-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="<?php echo APP_URL; ?>/student/profile.php" class="sidebar-link <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-edit"></i> Edit Profile & Resume
            </a>
            <a href="<?php echo APP_URL; ?>/student/jobs.php" class="sidebar-link <?php echo $current_page === 'jobs.php' ? 'active' : ''; ?>">
                <i class="fas fa-search"></i> Browse Jobs & Apply
            </a>
            <a href="<?php echo APP_URL; ?>/student/companies.php" class="sidebar-link <?php echo $current_page === 'companies.php' ? 'active' : ''; ?>">
                <i class="fas fa-city"></i> Target Companies
            </a>
            <a href="<?php echo APP_URL; ?>/student/applications.php" class="sidebar-link <?php echo $current_page === 'applications.php' ? 'active' : ''; ?>">
                <i class="fas fa-tasks"></i> My Applications
            </a>
            <a href="<?php echo APP_URL; ?>/student/interviews.php" class="sidebar-link <?php echo $current_page === 'interviews.php' ? 'active' : ''; ?>">
                <i class="fas fa-video"></i> Interview Schedules
            </a>

        <?php elseif ($role === 'company'): ?>
            <a href="<?php echo APP_URL; ?>/company/index.php" class="sidebar-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="<?php echo APP_URL; ?>/company/post-job.php" class="sidebar-link <?php echo $current_page === 'post-job.php' ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle"></i> Post New Vacancy
            </a>
            <a href="<?php echo APP_URL; ?>/company/jobs.php" class="sidebar-link <?php echo $current_page === 'jobs.php' ? 'active' : ''; ?>">
                <i class="fas fa-list-alt"></i> Manage Openings
            </a>
            <a href="<?php echo APP_URL; ?>/company/applicants.php" class="sidebar-link <?php echo $current_page === 'applicants.php' ? 'active' : ''; ?>">
                <i class="fas fa-users-cog"></i> Applicants Review
            </a>
            <a href="<?php echo APP_URL; ?>/company/interviews.php" class="sidebar-link <?php echo $current_page === 'interviews.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i> Schedule Interviews
            </a>
            <a href="<?php echo APP_URL; ?>/company/profile.php" class="sidebar-link <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-building"></i> Company Profile
            </a>
        <?php endif; ?>

        <hr class="my-3 text-secondary opacity-25">

        <a href="<?php echo APP_URL; ?>/logout.php" class="sidebar-link text-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</aside>
<?php endif; ?>
