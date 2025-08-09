<?php
require_once __DIR__ . '/../../app/guard.php';
require_once __DIR__ . '/../../app/db/UserRepository.php';
require_once __DIR__ . '/../../app/validators.php';
require_once __DIR__ . '/../../app/utils/helpers.php';

// Require admin login
require_admin();

$userRepo = new UserRepository();
$errors = [];
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Validate inputs
        $name_validation = validate_name($name);
        if (!$name_validation['valid']) {
            $errors[] = $name_validation['error'];
        }
        
        $email_validation = validate_email($email);
        if (!$email_validation['valid']) {
            $errors[] = $email_validation['error'];
        } elseif ($userRepo->emailExists($email_validation['value'])) {
            $errors[] = 'Email already exists.';
        }
        
        $phone_validation = validate_phone($phone);
        if (!$phone_validation['valid']) {
            $errors[] = $phone_validation['error'];
        } elseif ($userRepo->phoneExists($phone_validation['value'])) {
            $errors[] = 'Phone number already exists.';
        }
        
        $password_validation = validate_password($password);
        if (!$password_validation['valid']) {
            $errors[] = $password_validation['error'];
        }
        
        // Create student if no errors
        if (empty($errors)) {
            if ($userRepo->create($name_validation['value'], $email_validation['value'], $phone_validation['value'], $password_validation['value'], 'student')) {
                set_flash('success', 'Student created successfully!');
                redirect('students.php');
            } else {
                $errors[] = 'Failed to create student. Please try again.';
            }
        }
    }
    
    elseif ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        // Validate inputs
        $name_validation = validate_name($name);
        if (!$name_validation['valid']) {
            $errors[] = $name_validation['error'];
        }
        
        $email_validation = validate_email($email);
        if (!$email_validation['valid']) {
            $errors[] = $email_validation['error'];
        } elseif ($userRepo->emailExists($email_validation['value'], $id)) {
            $errors[] = 'Email already exists.';
        }
        
        $phone_validation = validate_phone($phone);
        if (!$phone_validation['valid']) {
            $errors[] = $phone_validation['error'];
        } elseif ($userRepo->phoneExists($phone_validation['value'], $id)) {
            $errors[] = 'Phone number already exists.';
        }
        
        // Update student if no errors
        if (empty($errors) && $id > 0) {
            if ($userRepo->update($id, $name_validation['value'], $email_validation['value'], $phone_validation['value'])) {
                set_flash('success', 'Student updated successfully!');
                redirect('students.php');
            } else {
                $errors[] = 'Failed to update student. Please try again.';
            }
        }
    }
    
    elseif ($action === 'reset_password') {
        $id = intval($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? '';
        
        $password_validation = validate_password($password);
        if (!$password_validation['valid']) {
            $errors[] = $password_validation['error'];
        }
        
        if (empty($errors) && $id > 0) {
            if ($userRepo->updatePassword($id, $password_validation['value'])) {
                set_flash('success', 'Password reset successfully!');
                redirect('students.php');
            } else {
                $errors[] = 'Failed to reset password. Please try again.';
            }
        }
    }
    
    elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $student = $userRepo->findById($id);
            if ($student && $student['role'] === 'student') {
                if ($userRepo->delete($id)) {
                    set_flash('success', 'Student deleted successfully!');
                } else {
                    set_flash('error', 'Failed to delete student.');
                }
            } else {
                set_flash('error', 'Invalid student or cannot delete admin accounts.');
            }
            redirect('students.php');
        }
    }
}

// Get student for editing if edit_id is provided
$edit_student = null;
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $edit_student = $userRepo->findById(intval($_GET['edit']));
    if ($edit_student && $edit_student['role'] !== 'student') {
        $edit_student = null; // Can only edit students
    }
}

$students = $userRepo->getAll('student');
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - eLearning Platform</title>
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
            <h2><?= $edit_student ? 'Edit Student' : 'Create New Student' ?></h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="<?= $edit_student ? 'update' : 'create' ?>">
                <?php if ($edit_student): ?>
                    <input type="hidden" name="id" value="<?= $edit_student['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required 
                        minlength="2" 
                        maxlength="100"
                        value="<?= sanitize($edit_student['name'] ?? $_POST['name'] ?? '') ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        maxlength="120"
                        value="<?= sanitize($edit_student['email'] ?? $_POST['email'] ?? '') ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        required 
                        minlength="8" 
                        maxlength="20"
                        value="<?= sanitize($edit_student['phone'] ?? $_POST['phone'] ?? '') ?>"
                    >
                </div>
                
                <?php if (!$edit_student): ?>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            minlength="8"
                        >
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-primary">
                    <?= $edit_student ? 'Update Student' : 'Create Student' ?>
                </button>
                
                <?php if ($edit_student): ?>
                    <a href="students.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($edit_student): ?>
            <div class="card">
                <h2>Reset Password</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="id" value="<?= $edit_student['id'] ?>">
                    
                    <div class="form-group">
                        <label for="reset_password">New Password</label>
                        <input 
                            type="password" 
                            id="reset_password" 
                            name="password" 
                            required 
                            minlength="8"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-danger">Reset Password</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>All Students</h2>
            
            <?php if (empty($students)): ?>
                <div class="alert alert-info">
                    No students found. Create your first student above.
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Avatar</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= $student['id'] ?></td>
                                <td><?= sanitize($student['name']) ?></td>
                                <td><?= sanitize($student['email']) ?></td>
                                <td><?= sanitize($student['phone']) ?></td>
                                <td>
                                    <?php if ($student['avatar_path']): ?>
                                        <img src="../assets/uploads/<?= sanitize($student['avatar_path']) ?>" alt="Avatar" class="avatar-small">
                                    <?php else: ?>
                                        <div class="avatar-small" style="background: #ddd; display: inline-flex; align-items: center; justify-content: center; color: #666;">
                                            <?= strtoupper(substr($student['name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M j, Y', strtotime($student['created_at'])) ?></td>
                                <td>
                                    <a href="students.php?edit=<?= $student['id'] ?>" class="btn btn-small btn-secondary">Edit</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $student['id'] ?>">
                                        <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this student? This will also remove all their enrollments.')">
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