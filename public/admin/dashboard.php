<?php
require_once __DIR__ . '/../../app/guard.php';
require_once __DIR__ . '/../../app/db/UserRepository.php';
require_once __DIR__ . '/../../app/db/CourseRepository.php';
require_once __DIR__ . '/../../app/db/EnrollmentRepository.php';
require_once __DIR__ . '/../../app/utils/helpers.php';

// Require admin login
require_admin();

$user = current_user();
$userRepo = new UserRepository();
$courseRepo = new CourseRepository();
$enrollmentRepo = new EnrollmentRepository();

// Get statistics
$studentCount = $userRepo->getCount();
$courseCount = $courseRepo->getCount();
$enrollmentCount = $enrollmentRepo->getCount();

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - eLearning Platform</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>eLearning Platform - Admin</h1>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="courses.php">Courses</a>
                <a href="students.php">Students</a>
                <a href="enrollments.php">Enrollments</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
                <?= sanitize($flash['message']) ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Welcome back, <?= sanitize($user['name']) ?>!</h2>
            <p>Manage your eLearning platform from this admin dashboard.</p>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <h3><?= $studentCount ?></h3>
                <p>Total Students</p>
            </div>
            <div class="stat-card">
                <h3><?= $courseCount ?></h3>
                <p>Active Courses</p>
            </div>
            <div class="stat-card">
                <h3><?= $enrollmentCount ?></h3>
                <p>Total Enrollments</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-2">
            <div class="card">
                <h3>Course Management</h3>
                <p>Create, edit, and manage courses offered on the platform.</p>
                <a href="courses.php" class="btn btn-primary">Manage Courses</a>
            </div>
            
            <div class="card">
                <h3>Student Management</h3>
                <p>View, edit, and manage student accounts and profiles.</p>
                <a href="students.php" class="btn btn-primary">Manage Students</a>
            </div>
            
            <div class="card">
                <h3>Enrollment Management</h3>
                <p>Monitor and manage course enrollments across the platform.</p>
                <a href="enrollments.php" class="btn btn-primary">View Enrollments</a>
            </div>
            
            <div class="card">
                <h3>Platform Overview</h3>
                <p>Monitor platform activity and user engagement.</p>
                <div style="margin-top: 1rem;">
                    <p><strong>Platform Status:</strong> <span style="color: #27ae60;">Active</span></p>
                    <p><strong>Admin Since:</strong> <?= date('M Y', strtotime($user['created_at'])) ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 