<?php
require_once __DIR__ . '/auth.php';

/**
 * Get the correct path to the login page
 */
function get_login_path() {
    $script_path = $_SERVER['SCRIPT_NAME'];
    
    // If we're in admin or student subdirectory, go up one level to public
    if (strpos($script_path, '/admin/') !== false || strpos($script_path, '/student/') !== false) {
        // We're in a subdirectory, so go up one level to public directory
        $public_path = dirname(dirname($script_path));
        return $public_path . '/index.php';
    } else {
        // We're already in the public directory
        $public_path = dirname($script_path);
        return $public_path . '/index.php';
    }
}

/**
 * Require user to be logged in
 */
function require_login() {
    if (!is_logged_in()) {
        $login_path = get_login_path();
        redirect($login_path);
    }
}

/**
 * Require user to be admin
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        $login_path = get_login_path();
        redirect($login_path);
    }
}

/**
 * Require user to be student
 */
function require_student() {
    require_login();
    if (!is_student()) {
        $login_path = get_login_path();
        redirect($login_path);
    }
} 