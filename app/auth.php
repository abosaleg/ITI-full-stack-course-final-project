<?php
require_once __DIR__ . '/db/UserRepository.php';
require_once __DIR__ . '/utils/helpers.php';

/**
 * Start session if not already started
 */
function ensure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Login user
 */
function login($email, $password) {
    ensure_session();
    
    $userRepo = new UserRepository();
    $user = $userRepo->findByEmail($email);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        return ['success' => true, 'user' => $user];
    }
    
    return ['success' => false, 'error' => 'Invalid email or password.'];
}

/**
 * Register new user
 */
function register($name, $email, $phone, $password, $avatar_path = null) {
    ensure_session();
    
    $userRepo = new UserRepository();
    
    // Check if email already exists
    if ($userRepo->emailExists($email)) {
        return ['success' => false, 'error' => 'Email already exists.'];
    }
    
    // Check if phone already exists
    if ($userRepo->phoneExists($phone)) {
        return ['success' => false, 'error' => 'Phone number already exists.'];
    }
    
    // Create user
    if ($userRepo->create($name, $email, $phone, $password, 'student', $avatar_path)) {
        // Auto-login after registration
        $user = $userRepo->findByEmail($email);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        return ['success' => true, 'user' => $user];
    }
    
    return ['success' => false, 'error' => 'Registration failed. Please try again.'];
}

/**
 * Logout user
 */
function logout() {
    ensure_session();
    
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Get current user
 */
function current_user() {
    ensure_session();
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $userRepo = new UserRepository();
    return $userRepo->findById($_SESSION['user_id']);
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    ensure_session();
    return isset($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 */
function is_admin() {
    ensure_session();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if current user is student
 */
function is_student() {
    ensure_session();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
} 