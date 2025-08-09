<?php
// Server-side validation functions

/**
 * Validate name
 */
function validate_name($name) {
    $name = trim($name);
    if (empty($name)) {
        return ['valid' => false, 'error' => 'Name is required.'];
    }
    if (strlen($name) < 2 || strlen($name) > 100) {
        return ['valid' => false, 'error' => 'Name must be between 2 and 100 characters.'];
    }
    return ['valid' => true, 'value' => $name];
}

/**
 * Validate email
 */
function validate_email($email) {
    $email = trim($email);
    if (empty($email)) {
        return ['valid' => false, 'error' => 'Email is required.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'error' => 'Invalid email format.'];
    }
    return ['valid' => true, 'value' => $email];
}

/**
 * Validate phone
 */
function validate_phone($phone) {
    $phone = trim($phone);
    if (empty($phone)) {
        return ['valid' => false, 'error' => 'Phone is required.'];
    }
    if (strlen($phone) < 8 || strlen($phone) > 20) {
        return ['valid' => false, 'error' => 'Phone must be between 8 and 20 characters.'];
    }
    if (!preg_match('/^[\d+\-\s()]+$/', $phone)) {
        return ['valid' => false, 'error' => 'Phone contains invalid characters.'];
    }
    return ['valid' => true, 'value' => $phone];
}

/**
 * Validate password
 */
function validate_password($password) {
    if (empty($password)) {
        return ['valid' => false, 'error' => 'Password is required.'];
    }
    if (strlen($password) < 8) {
        return ['valid' => false, 'error' => 'Password must be at least 8 characters.'];
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'error' => 'Password must contain at least one uppercase letter.'];
    }
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'error' => 'Password must contain at least one lowercase letter.'];
    }
    if (!preg_match('/\d/', $password)) {
        return ['valid' => false, 'error' => 'Password must contain at least one digit.'];
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        return ['valid' => false, 'error' => 'Password must contain at least one special character.'];
    }
    return ['valid' => true, 'value' => $password];
}

/**
 * Validate course title
 */
function validate_course_title($title) {
    $title = trim($title);
    if (empty($title)) {
        return ['valid' => false, 'error' => 'Course title is required.'];
    }
    if (strlen($title) < 2 || strlen($title) > 120) {
        return ['valid' => false, 'error' => 'Course title must be between 2 and 120 characters.'];
    }
    return ['valid' => true, 'value' => $title];
}

/**
 * Validate course description
 */
function validate_course_description($description) {
    $description = trim($description);
    if (empty($description)) {
        return ['valid' => false, 'error' => 'Course description is required.'];
    }
    return ['valid' => true, 'value' => $description];
} 