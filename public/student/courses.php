<?php
require_once __DIR__ . '/../../app/guard.php';
require_once __DIR__ . '/../../app/db/CourseRepository.php';
require_once __DIR__ . '/../../app/db/EnrollmentRepository.php';
require_once __DIR__ . '/../../app/utils/helpers.php';

// Require student login
require_student();

$user = current_user();
$courseRepo = new CourseRepository();
$enrollmentRepo = new EnrollmentRepository();

// Handle enrollment/unenrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $course_id = intval($_POST['course_id'] ?? 0);
    
    if ($action === 'enroll' && $course_id > 0) {
        if ($enrollmentRepo->enroll($user['id'], $course_id)) {
            set_flash('success', 'Successfully enrolled in the course!');
        } else {
            set_flash('error', 'Failed to enroll. You may already be enrolled in this course.');
        }
    } elseif ($action === 'unenroll' && $course_id > 0) {
        if ($enrollmentRepo->unenroll($user['id'], $course_id)) {
            set_flash('success', 'Successfully unenrolled from the course.');
        } else {
            set_flash('error', 'Failed to unenroll from the course.');
        }
    }
    
    redirect('courses.php');
}

$courses = $courseRepo->getCoursesWithEnrollmentStatus($user['id']);
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Courses - eLearning Platform</title>
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

        <div class="card">
            <h2>Available Courses</h2>
            <p>Explore our course catalog and enroll in courses that interest you.</p>
        </div>

        <?php if (empty($courses)): ?>
            <div class="card">
                <div class="alert alert-info">
                    No courses are currently available. Please check back later.
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-2">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <h3><?= sanitize($course['title']) ?></h3>
                        <p><?= sanitize($course['description']) ?></p>
                        <p><small>Created: <?= date('M j, Y', strtotime($course['created_at'])) ?></small></p>
                        
                        <div style="margin-top: 1rem;">
                            <?php if ($course['is_enrolled']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="unenroll">
                                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to unenroll from this course?')">
                                        Unenroll
                                    </button>
                                </form>
                                <span style="color: #27ae60; font-weight: bold; margin-left: 1rem;">✓ Enrolled</span>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="enroll">
                                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-small">
                                        Enroll Now
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 