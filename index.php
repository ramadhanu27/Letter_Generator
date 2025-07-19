<?php
// Main entry point for Indonesian PDF Letter Generator
require_once 'config/database.php';
require_once 'classes/User.php';

// Check if user is logged in
if (User::isLoggedIn()) {
    // Redirect to dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // Redirect to login
    header('Location: login.php');
    exit;
}
?>
