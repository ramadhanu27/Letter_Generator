<?php
// Main entry point for Indonesian PDF Letter Generator
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/User.php';

// Prevent redirect loops
if (!isset($_SESSION['redirect_count'])) {
    $_SESSION['redirect_count'] = 0;
}

$_SESSION['redirect_count']++;

if ($_SESSION['redirect_count'] > 3) {
    // Too many redirects, show error
    unset($_SESSION['redirect_count']);
    die('Redirect loop detected. Please clear your browser cache and cookies, then try again.');
}

// Check if user is logged in
if (User::isLoggedIn()) {
    // Reset redirect count on successful access
    unset($_SESSION['redirect_count']);
    // Redirect to dashboard
    header('Location: ../app/views/user/dashboard.php');
    exit;
} else {
    // Redirect to login
    header('Location: ../auth/login.php');
    exit;
}
