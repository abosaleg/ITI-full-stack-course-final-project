<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/validators.php';
require_once __DIR__ . '/../app/utils/helpers.php';

// Check if user is already logged in
if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('student/dashboard.php');
    }
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    $email_validation = validate_email($email);
    if (!$email_validation['valid']) {
        $error = $email_validation['error'];
    } else {
        // Attempt login
        $result = login($email_validation['value'], $password);
        if ($result['success']) {
            if ($result['user']['role'] === 'admin') {
                redirect('admin/dashboard.php');
            } else {
                redirect('student/dashboard.php');
            }
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eLearning Platform</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>eLearning Platform</h1>
            <nav>
                <a href="register.php">Register</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="grid grid-2">
            <div class="card">
                <h2>Welcome to eLearning</h2>
                <p>Your gateway to knowledge and skill development. Join thousands of students learning from expert instructors.</p>
                <h3>Features:</h3>
                <ul style="margin-left: 1.5rem; margin-top: 1rem;">
                    <li>Interactive courses</li>
                    <li>Expert instructors</li>
                    <li>Track your progress</li>
                    <li>Flexible learning schedule</li>
                </ul>
            </div>
            
            <div class="card">
                <h2>Login</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?= sanitize($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            maxlength="120"
                            value="<?= sanitize($_POST['email'] ?? '') ?>"
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
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                
                <p style="margin-top: 1rem;">
                    Don't have an account? <a href="register.php">Register here</a>
                </p>
                
                <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                    <h4>Demo Accounts:</h4>
                    <!-- <p><strong>Admin:</strong> admin@gmail.com / Admin@123</p> -->
                    <p><strong>Student:</strong> hamedd@gmail.com / Hamed123456@</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 