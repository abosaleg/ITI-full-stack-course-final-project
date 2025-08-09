<?php
require_once __DIR__ . '/../app/auth.php';

// Logout user and redirect to login page
logout();

// Redirect to the login page
header("Location: index.php");
exit();
?>
