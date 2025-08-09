<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/validators.php';
require_once __DIR__ . '/../app/utils/helpers.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('student/dashboard.php');
    }
}

$errors = [];
$form_data = [];

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Store form data for repopulation
    $form_data = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone
    ];
    
    // Validate inputs
    $name_validation = validate_name($name);
    if (!$name_validation['valid']) {
        $errors[] = $name_validation['error'];
    }
    
    $email_validation = validate_email($email);
    if (!$email_validation['valid']) {
        $errors[] = $email_validation['error'];
    }
    
    $phone_validation = validate_phone($phone);
    if (!$phone_validation['valid']) {
        $errors[] = $phone_validation['error'];
    }
    
    $password_validation = validate_password($password);
    if (!$password_validation['valid']) {
        $errors[] = $password_validation['error'];
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
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
    
    // If no validation errors, attempt registration
    if (empty($errors)) {
        $result = register(
            $name_validation['value'],
            $email_validation['value'],
            $phone_validation['value'],
            $password_validation['value'],
            $avatar_filename
        );
        
        if ($result['success']) {
            redirect('student/dashboard.php');
        } else {
            $errors[] = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - eLearning Platform</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>eLearning Platform</h1>
            <nav>
                <a href="index.php">Login</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h2>Student Registration</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= sanitize($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required 
                        minlength="2" 
                        maxlength="100"
                        value="<?= sanitize($form_data['name'] ?? '') ?>"
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
                        value="<?= sanitize($form_data['email'] ?? '') ?>"
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
                        value="<?= sanitize($form_data['phone'] ?? '') ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        minlength="8"
                    >
                    <small style="color: #666; font-size: 0.9rem;">
                        Must contain at least 8 characters with uppercase, lowercase, digit, and special character.
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required 
                        minlength="8"
                    >
                </div>
                
                <div class="form-group">
                    <label for="avatar">Profile Picture (Optional)</label>
                    <input 
                        type="file" 
                        id="avatar" 
                        name="avatar" 
                        accept="image/jpeg,image/png,image/webp"
                    >
                    <small style="color: #666; font-size: 0.9rem;">
                        Maximum 2MB. Allowed formats: JPG, PNG, WebP.
                    </small>
                </div>
                
                <button type="submit" class="btn btn-primary">Register</button>
                <a href="index.php" class="btn btn-secondary">Back to Login</a>
            </form>
        </div>
    </div>
</body>
</html> 