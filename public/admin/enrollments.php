<?php
require_once __DIR__ . '/../../app/guard.php';
require_once __DIR__ . '/../../app/db/EnrollmentRepository.php';
require_once __DIR__ . '/../../app/db/UserRepository.php';
require_once __DIR__ . '/../../app/db/CourseRepository.php';
require_once __DIR__ . '/../../app/utils/helpers.php';

// Require admin login
require_admin();

$enrollmentRepo = new EnrollmentRepository();
$userRepo = new UserRepository();
$courseRepo = new CourseRepository();

// Handle enrollment deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        if ($enrollmentRepo->delete($id)) {
            set_flash('success', 'Enrollment removed successfully!');
        } else {
            set_flash('error', 'Failed to remove enrollment.');
        }
        redirect('enrollments.php');
    }
}

// Get filter parameters
$filter_user_id = intval($_GET['user_id'] ?? 0);
$filter_course_id = intval($_GET['course_id'] ?? 0);

// Get enrollments (filtered or all)
if ($filter_user_id > 0 || $filter_course_id > 0) {
    $enrollments = $enrollmentRepo->getFiltered(
        $filter_user_id > 0 ? $filter_user_id : null,
        $filter_course_id > 0 ? $filter_course_id : null
    );
} else {
    $enrollments = $enrollmentRepo->getAllWithDetails();
}

// Get students and courses for filter dropdowns
$students = $userRepo->getAll('student');
$courses = $courseRepo->getAll();

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Enrollments - eLearning Platform</title>
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
            <h2>Enrollment Filters</h2>
            <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="user_id">Filter by Student</label>
                    <select id="user_id" name="user_id">
                        <option value="">All Students</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= $student['id'] ?>" <?= $filter_user_id === $student['id'] ? 'selected' : '' ?>>
                                <?= sanitize($student['name']) ?> (<?= sanitize($student['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="course_id">Filter by Course</label>
                    <select id="course_id" name="course_id">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>" <?= $filter_course_id === $course['id'] ? 'selected' : '' ?>>
                                <?= sanitize($course['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                    <a href="enrollments.php" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>All Enrollments</h2>
            
            <?php if (empty($enrollments)): ?>
                <div class="alert alert-info">
                    No enrollments found<?= $filter_user_id > 0 || $filter_course_id > 0 ? ' with the applied filters' : '' ?>.
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Enrolled Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td><?= $enrollment['id'] ?></td>
                                <td><?= sanitize($enrollment['user_name']) ?></td>
                                <td><?= sanitize($enrollment['user_email']) ?></td>
                                <td><?= sanitize($enrollment['course_title']) ?></td>
                                <td><?= date('M j, Y g:i A', strtotime($enrollment['created_at'])) ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $enrollment['id'] ?>">
                                        <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to remove this enrollment?')">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 1rem; color: #666;">
                    <strong>Total Enrollments:</strong> <?= count($enrollments) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 