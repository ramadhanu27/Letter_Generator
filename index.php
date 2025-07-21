<?php
// Main entry point for Indonesian PDF Letter Generator
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/User.php';

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

// Always redirect to home page for better user experience
header('Location: home');
exit;
