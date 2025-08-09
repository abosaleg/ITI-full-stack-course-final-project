<?php
require_once __DIR__ . '/../../app/guard.php';
require_once __DIR__ . '/../../app/db/UserRepository.php';
require_once __DIR__ . '/../../app/validators.php';
require_once __DIR__ . '/../../app/utils/helpers.php';

// Require student login
require_student();

$user = current_user();
$userRepo = new UserRepository();
$errors = [];
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
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
        } elseif ($userRepo->emailExists($email_validation['value'], $user['id'])) {
            $errors[] = 'Email already exists.';
        }
        
        $phone_validation = validate_phone($phone);
        if (!$phone_validation['valid']) {
            $errors[] = $phone_validation['error'];
        } elseif ($userRepo->phoneExists($phone_validation['value'], $user['id'])) {
            $errors[] = 'Phone number already exists.';
        }
        
        // Handle avatar upload if provided
        $avatar_filename = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_result = upload_avatar($_FILES['avatar']);
            if ($upload_result['success']) {
                $avatar_filename = $upload_result['filename'];
            } else {
                $errors[] = $upload_result['error'];
            }
        }
        
        // Update if no errors
        if (empty($errors)) {
            if ($userRepo->update($user['id'], $name_validation['value'], $email_validation['value'], $phone_validation['value'], $avatar_filename)) {
                $success = 'Profile updated successfully!';
                $user = $userRepo->findById($user['id']); // Refresh user data
            } else {
                $errors[] = 'Failed to update profile. Please try again.';
            }
        }
    }
    
    // Handle password change
    elseif ($_POST['action'] === 'change_password') {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Verify old password
        if (!password_verify($old_password, $user['password_hash'])) {
            $errors[] = 'Current password is incorrect.';
        }
        
        // Validate new password
        $password_validation = validate_password($new_password);
        if (!$password_validation['valid']) {
            $errors[] = $password_validation['error'];
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'New passwords do not match.';
        }
        
        // Update password if no errors
        if (empty($errors)) {
            if ($userRepo->updatePassword($user['id'], $password_validation['value'])) {
                $success = 'Password changed successfully!';
            } else {
                $errors[] = 'Failed to change password. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - eLearning Platform</title>
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
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= sanitize($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= sanitize($success) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-2">
            <!-- Profile Information -->
            <div class="card">
                <h2>Profile Information</h2>
                
                <div style="text-align: center; margin-bottom: 2rem;">
                    <?php if ($user['avatar_path']): ?>
                        <img src="../assets/uploads/<?= sanitize($user['avatar_path']) ?>" alt="Avatar" class="avatar">
                    <?php else: ?>
                        <div class="avatar" style="background: #ddd; display: flex; align-items: center; justify-content: center; color: #666; font-size: 2rem;">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            required 
                            minlength="2" 
                            maxlength="100"
                            value="<?= sanitize($user['name']) ?>"
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
                            value="<?= sanitize($user['email']) ?>"
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
                            value="<?= sanitize($user['phone']) ?>"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="avatar">Change Profile Picture</label>
                        <input 
                            type="file" 
                            id="avatar" 
                            name="avatar" 
                            accept="image/jpeg,image/png,image/webp"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
            
            <!-- Change Password -->
            <div class="card">
                <h2>Change Password</h2>
                
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="old_password">Current Password</label>
                        <input 
                            type="password" 
                            id="old_password" 
                            name="old_password" 
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            required 
                            minlength="8"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required 
                            minlength="8"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-danger">Change Password</button>
                </form>
            </div>
        </div>
        
        <!-- Account Information -->
        <div class="card">
            <h2>Account Information</h2>
            <div class="grid grid-3">
                <div>
                    <strong>Role:</strong> Student
                </div>
                <div>
                    <strong>Member Since:</strong> <?= date('M j, Y', strtotime($user['created_at'])) ?>
                </div>
                <div>
                    <strong>Account ID:</strong> #<?= $user['id'] ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 