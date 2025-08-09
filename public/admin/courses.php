<?php
require_once __DIR__ . '/../../app/guard.php';
require_once __DIR__ . '/../../app/db/CourseRepository.php';
require_once __DIR__ . '/../../app/validators.php';
require_once __DIR__ . '/../../app/utils/helpers.php';

// Require admin login
require_admin();

$courseRepo = new CourseRepository();
$errors = [];
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'active';
        
        // Validate inputs
        $title_validation = validate_course_title($title);
        if (!$title_validation['valid']) {
            $errors[] = $title_validation['error'];
        }
        
        $description_validation = validate_course_description($description);
        if (!$description_validation['valid']) {
            $errors[] = $description_validation['error'];
        }
        
        // Create course if no errors
        if (empty($errors)) {
            if ($courseRepo->create($title_validation['value'], $description_validation['value'], $status)) {
                set_flash('success', 'Course created successfully!');
                redirect('courses.php');
            } else {
                $errors[] = 'Failed to create course. Please try again.';
            }
        }
    }
    
    elseif ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'active';
        
        // Validate inputs
        $title_validation = validate_course_title($title);
        if (!$title_validation['valid']) {
            $errors[] = $title_validation['error'];
        }
        
        $description_validation = validate_course_description($description);
        if (!$description_validation['valid']) {
            $errors[] = $description_validation['error'];
        }
        
        // Update course if no errors
        if (empty($errors) && $id > 0) {
            if ($courseRepo->update($id, $title_validation['value'], $description_validation['value'], $status)) {
                set_flash('success', 'Course updated successfully!');
                redirect('courses.php');
            } else {
                $errors[] = 'Failed to update course. Please try again.';
            }
        }
    }
    
    elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            if ($courseRepo->delete($id)) {
                set_flash('success', 'Course deleted successfully!');
            } else {
                set_flash('error', 'Failed to delete course.');
            }
            redirect('courses.php');
        }
    }
}

// Get course for editing if edit_id is provided
$edit_course = null;
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $edit_course = $courseRepo->findById(intval($_GET['edit']));
}

$courses = $courseRepo->getAll();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - eLearning Platform</title>
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
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= sanitize($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2><?= $edit_course ? 'Edit Course' : 'Create New Course' ?></h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="<?= $edit_course ? 'update' : 'create' ?>">
                <?php if ($edit_course): ?>
                    <input type="hidden" name="id" value="<?= $edit_course['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Course Title</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        required 
                        minlength="2" 
                        maxlength="120"
                        value="<?= sanitize($edit_course['title'] ?? $_POST['title'] ?? '') ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="description">Course Description</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        required
                    ><?= sanitize($edit_course['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="active" <?= ($edit_course['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="archived" <?= ($edit_course['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?= $edit_course ? 'Update Course' : 'Create Course' ?>
                </button>
                
                <?php if ($edit_course): ?>
                    <a href="courses.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h2>All Courses</h2>
            
            <?php if (empty($courses)): ?>
                <div class="alert alert-info">
                    No courses found. Create your first course above.
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?= $course['id'] ?></td>
                                <td><?= sanitize($course['title']) ?></td>
                                <td><?= sanitize(substr($course['description'], 0, 100)) ?><?= strlen($course['description']) > 100 ? '...' : '' ?></td>
                                <td>
                                    <span style="color: <?= $course['status'] === 'active' ? '#27ae60' : '#e74c3c' ?>">
                                        <?= ucfirst($course['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($course['created_at'])) ?></td>
                                <td>
                                    <a href="courses.php?edit=<?= $course['id'] ?>" class="btn btn-small btn-secondary">Edit</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $course['id'] ?>">
                                        <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this course? This will also remove all enrollments.')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 