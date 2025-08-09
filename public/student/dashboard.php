<?php
require_once __DIR__ . '/../../app/guard.php';
require_once __DIR__ . '/../../app/db/EnrollmentRepository.php';
require_once __DIR__ . '/../../app/utils/helpers.php';

// Require student login
require_student();

$user = current_user();
$enrollmentRepo = new EnrollmentRepository();
$enrollments = $enrollmentRepo->getUserEnrollments($user['id']);

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - eLearning Platform</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>eLearning Platform</h1>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="courses.php">Browse Courses</a>
                <a href="profile.php">Profile</a>
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
        
        <div class="stats">
            <div class="stat-card">
                <h3><?= count($enrollments) ?></h3>
                <p>Enrolled Courses</p>
            </div>
        </div>

        <div class="card">
            <h2>Welcome back, <?= sanitize($user['name']) ?>!</h2>
            <p>Continue your learning journey with your enrolled courses.</p>
        </div>

        <div class="card">
            <h2>My Enrolled Courses</h2>
            
            <?php if (empty($enrollments)): ?>
                <div class="alert alert-info">
                    You haven't enrolled in any courses yet. <a href="courses.php">Browse available courses</a> to get started!
                </div>
            <?php else: ?>
                <div class="grid grid-2">
                    <?php foreach ($enrollments as $enrollment): ?>
                        <div class="course-card">
                            <h3><?= sanitize($enrollment['title']) ?></h3>
                            <p><?= sanitize($enrollment['description']) ?></p>
                            <p><small>Enrolled on: <?= date('M j, Y', strtotime($enrollment['created_at'])) ?></small></p>
                            <?php if ($enrollment['status'] === 'archived'): ?>
                                <span style="color: #e74c3c; font-weight: bold;">Archived</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-2">
            <div class="card">
                <h3>Quick Actions</h3>
                <a href="courses.php" class="btn btn-primary">Browse All Courses</a>
                <a href="profile.php" class="btn btn-secondary">Edit Profile</a>
            </div>
            
            <div class="card">
                <h3>Learning Stats</h3>
                <p><strong>Total Enrollments:</strong> <?= count($enrollments) ?></p>
                <p><strong>Active Courses:</strong> <?= count(array_filter($enrollments, function($e) { return $e['status'] === 'active'; })) ?></p>
                <p><strong>Member Since:</strong> <?= date('M Y', strtotime($user['created_at'])) ?></p>
            </div>
        </div>
    </div>
</body>
</html> 